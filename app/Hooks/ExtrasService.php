<?php

namespace LaqiraPay\Hooks;

use LaqiraPay\Helpers\JwtHelper;
use LaqiraPay\Helpers\TransactionDetailsRenderer;
use LaqiraPay\Helpers\WooCommerceHelper;
use LaqiraPay\Support\TxRepairForm;
use LaqiraPay\Domain\Services\LaqiraLogger;

class ExtrasService {
	private $wooCommerceService;
	private $jwtService;
	public function __construct(
		WooCommerceHelper $wooCommerceService,
		JwtHelper $jwtService
	) {
		$this->wooCommerceService = $wooCommerceService;
		$this->jwtService         = $jwtService;
	}

	public function register() {
		add_action( 'woocommerce_order_status_changed', array( $this, 'custom_empty_cart_on_status_change' ), 10, 4 );
		add_action( 'woocommerce_thankyou', array( $this, 'custom_display_order_data' ), 9 );
		add_action( 'add_meta_boxes', array( $this, 'order_custom_metabox' ) );
		add_action( 'add_meta_boxes', array( $this, 'recovery_order_custom_metabox' ) );
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'add_tx_check_section_to_view_order' ), 10, 1 );
		add_shortcode( 'lqr_recovery', array( $this, 'recovery_txHash_shortcode' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_tx_assets' ) );
		add_action( 'update_option_woocommerce_currency', array( $this, 'check_wc_currency_with_rates_after_change' ) );
		add_action( 'admin_init', array( $this, 'check_wc_currency_with_rates' ) );
		add_filter( 'woocommerce_email_order_meta_fields', array( $this, 'woocommerce_email_order_meta_fields' ), 10, 3 );
		// LaqiraLogger::log(200, 'hooks', 'extras_service_registered');
	}

	public function custom_empty_cart_on_status_change( $order_id, $old_status, $new_status, $order ) {
		if ( $old_status !== 'pending' ) {
			return;
		}

		if ( ! $order instanceof \WC_Order ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order instanceof \WC_Order || $order->get_payment_method() !== 'WC_laqirapay' ) {
			return;
		}

		$this->reset_cart_session( $order );
		LaqiraLogger::log(
			200,
			'hooks',
			'cart_reset_on_status_change',
			array(
				'order_id'   => $order_id,
				'old_status' => $old_status,
				'new_status' => $new_status,
			)
		);
	}

	public function custom_display_order_data( $order_id ) {
		$order = wc_get_order( intval( $order_id ) );
		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		if ( $order->get_payment_method() === 'WC_laqirapay' ) {
			$this->reset_cart_session( $order );
			echo wp_kses_post( $this->render_tx_details( $order ) );
			LaqiraLogger::log( 200, 'hooks', 'display_order_data', array( 'order_id' => $order_id ) );
		}
	}

	public function order_custom_metabox() {
		$screen = wc_get_page_screen_id( 'shop_order' );
		if ( $screen && 'woocommerce_page_wc-orders' === $screen ) {
			$order_id = isset( $_GET['id'] ) ? intval( sanitize_text_field( wp_unslash( $_GET['id'] ) ) ) : 0;
			$order    = wc_get_order( $order_id );
			if ( $order && $order->get_payment_method() === 'WC_laqirapay' ) {
				add_meta_box( 'laqirapay_metabox', 'LaqiraPay Details', array( $this, 'metabox_content' ), $screen, 'advanced', 'high' );
				if ( ! $order->get_meta( 'tx_hash' ) ) {
					add_meta_box( 'laqirapay_recovery_faild_metabox', 'LaqiraPay Recovery Faild Transaction', array( $this, 'recovery_faild_transaction' ), $screen, 'advanced', 'high' );
				}
				LaqiraLogger::log( 200, 'hooks', 'order_metabox_added', array( 'order_id' => $order_id ) );
			}
		}
	}

	public function recovery_order_custom_metabox() {
		$screen = wc_get_page_screen_id( 'shop_order' );
		if ( $screen && 'woocommerce_page_wc-orders' === $screen ) {
			$order_id = isset( $_GET['id'] ) ? intval( sanitize_text_field( wp_unslash( $_GET['id'] ) ) ) : 0;
			$order    = wc_get_order( $order_id );
			if ( $order && $order->get_payment_method() === 'WC_laqirapay' ) {
				add_meta_box( 'laqirapay_order_recovery_metabox', 'LaqiraPay Order Recovery', array( $this, 'order_recovery_metabox_content' ), $screen, 'side', 'high' );
				LaqiraLogger::log( 200, 'hooks', 'order_recovery_metabox_added', array( 'order_id' => $order_id ) );
			}
		}
	}

	public function enqueue_admin_tx_assets( $hook ) {
		$screen = wc_get_page_screen_id( 'shop_order' );
		if ( $screen && $hook === $screen ) {
			$order_id = isset( $_GET['id'] ) ? intval( sanitize_text_field( wp_unslash( $_GET['id'] ) ) ) : 0;
			$order    = wc_get_order( $order_id );
			if ( $order && $order->get_payment_method() === 'WC_laqirapay' ) {
				TxRepairForm::enqueue_assets( 'admin' );
				LaqiraLogger::log( 200, 'hooks', 'enqueue_admin_tx_assets', array( 'order_id' => $order_id ) );
			}
		}
	}

	public function metabox_content( $object ) {
		$order         = is_a( $object, 'WP_Post' ) ? wc_get_order( $object->ID ) : $object;
		$paymentType   = esc_html( $order->get_meta( 'payment_type' ) );
		$walletAddress = esc_html( $order->get_meta( 'CustomerWalletAddress' ) );
		$tokenAmount   = esc_html( $order->get_meta( 'TokenAmount' ) );
		$tokenName     = esc_html( $order->get_meta( 'TokenName' ) );
		$exchangeRate  = esc_html( $order->get_meta( 'exchange_rate' ) );

		$txHash          = (string) $order->get_meta( 'tx_hash' );
		$txHashText      = esc_html( $txHash );
		$explorerUrl     = TransactionDetailsRenderer::buildExplorerUrl( $order->get_meta( 'network_explorer' ), $txHash );
		$transactionLink = $explorerUrl
			? sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( $explorerUrl ), $txHashText )
			: $txHashText;

		echo '<p>' . esc_html__( 'Customer Selected Payment Type:', 'laqirapay' ) . '<strong> ' . esc_html( $paymentType ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Customer Wallet Address:', 'laqirapay' ) . '<strong> ' . esc_html( $walletAddress ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Token Amount:', 'laqirapay' ) . '<strong> ' . esc_html( $tokenAmount ) . ' ' . esc_html( $tokenName ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Exchange Rate:', 'laqirapay' ) . '<strong> ' . esc_html( $exchangeRate ) . '</strong></p>';
		$allowed_link_tags = array(
			'a' => array(
				'href'   => array(),
				'target' => array(),
				'rel'    => array(),
			),
		);
		echo '<p>' . esc_html__( 'Transaction Hash:', 'laqirapay' ) . '<strong> ' . wp_kses( $transactionLink, $allowed_link_tags ) . '</strong></p>';
	}

	public function recovery_faild_transaction( $object ) {
		$order = is_a( $object, 'WP_Post' ) ? wc_get_order( $object->ID ) : $object;
		$this->recovery_txHash_form_in_admin( $order );
	}

	public function order_recovery_metabox_content( $object ) {
		$order = is_a( $object, 'WP_Post' ) ? wc_get_order( $object->ID ) : $object;
		if ( $order->get_meta( 'tx_hash' ) ) {
			$this->view_confirmation_tx_hash_automation( $order );
		}
	}

	public function add_tx_check_section_to_view_order( $order_id ) {
		$order = wc_get_order( $order_id );
		if (
			$order->get_status() !== 'pending' ||
			$order->get_payment_method() !== 'WC_laqirapay' ||
			$order->get_meta( 'tx_status', true ) !== 'failed'
		) {
			return;
		}
		TxRepairForm::enqueue_assets( 'repair' );
		echo wp_kses_post( TxRepairForm::render_form( 'repair', $order_id ) );
		LaqiraLogger::log( 200, 'hooks', 'tx_repair_form_displayed', array( 'order_id' => $order_id ) );
	}

	public function recovery_txHash_shortcode() {
		TxRepairForm::enqueue_assets( 'view' );
		LaqiraLogger::log( 200, 'hooks', 'recovery_shortcode_rendered' );
		return TxRepairForm::render_form( 'view' );
	}

	private function recovery_txHash_form_in_admin( $order ) {
		$order_id = $order->get_id();
		echo wp_kses_post( TxRepairForm::render_form( 'admin', $order_id ) );
	}

	private function view_confirmation_tx_hash_automation( $order ) {
		// legacy automation logic removed during refactor
	}

	private function reset_cart_session( $order ) {
		if ( ! function_exists( 'WC' ) ) {
			$this->logCartResetSkipped( $order, 'wc_function_unavailable' );
			return;
		}

		$woocommerce = WC();
		if ( ! $woocommerce ) {
			$this->logCartResetSkipped( $order, 'wc_instance_unavailable' );
			return;
		}

		$cart = isset( $woocommerce->cart ) ? $woocommerce->cart : null;
		if ( ! is_object( $cart ) || ! method_exists( $cart, 'empty_cart' ) ) {
			$this->logCartResetSkipped( $order, 'cart_unavailable' );
			return;
		}

		$session = isset( $woocommerce->session ) ? $woocommerce->session : null;
		if ( ! is_object( $session ) || ! method_exists( $session, 'set' ) ) {
			$this->logCartResetSkipped( $order, 'session_unavailable' );
			return;
		}

		$cart->empty_cart();
		$session->set( 'cart', array() );
		$session->set( 'last_order_id', '' );
	}

	private function logCartResetSkipped( $order, string $reason, array $context = array() ): void {
		$context = array_merge( array( 'reason' => $reason ), $context );
		$orderId = $this->resolveOrderId( $order );
		if ( $orderId !== null ) {
			$context['order_id'] = $orderId;
		}

		LaqiraLogger::log( 200, 'hooks', 'cart_reset_skipped', $context );
	}

	private function resolveOrderId( $order ): ?int {
		if ( is_object( $order ) && method_exists( $order, 'get_id' ) ) {
			return (int) $order->get_id();
		}

		if ( is_numeric( $order ) ) {
			return (int) $order;
		}

		return null;
	}

	private function render_tx_details( $order ) {
		ob_start();
		$allowed_table_tags = array(
			'tr' => array(),
			'th' => array(),
			'td' => array(),
			'a'  => array(
				'href'   => array(),
				'target' => array(),
				'rel'    => array(),
			),
		);
		?>
		<h2 class="woocommerce-order-details__title"><?php echo esc_html__( 'LaqiraPay Transaction Details:', 'laqirapay' ); ?></h2>
		<table class="shop_table shop_table_responsive additional_info"><tbody>
			<?php echo wp_kses( TransactionDetailsRenderer::renderRows( TransactionDetailsRenderer::buildTransactionRows( $order ) ), $allowed_table_tags ); ?>
		</tbody></table>
		<?php
		return ob_get_clean();
	}

	public function check_wc_currency_with_rates_after_change() {
		$current_currency = get_woocommerce_currency();

		if ( 'USD' !== $current_currency ) {
			$saved = get_option( 'laqirapay_exchange_rate_' . $current_currency, '' );

			if ( $saved ) {
				add_action(
					'admin_notices',
					function () use ( $current_currency, $saved ) {
												$link = admin_url( 'admin.php?page=laqirapay-settings' );

						$message = sprintf(
							/* translators: 1: currency code (e.g. IRR), 2: saved exchange rate value */
							esc_html__( 'WooCommerce currency changed. You set currency exchange rate for %1$s to %2$s. If you need to change it, please click', 'laqirapay' ),
							$current_currency,
							$saved
						);

						printf(
							'<div class="notice notice-warning is-dismissible"><p>%1$s <a href="%2$s">%3$s</a>.</p></div>',
							esc_html( $message ),
							esc_url( $link ),
							esc_html__( 'here', 'laqirapay' )
						);
					}
				);

				LaqiraLogger::log( 200, 'hooks', 'currency_rate_exists', array( 'currency' => $current_currency ) );
			}
		}
	}


	public function check_wc_currency_with_rates() {
		if ( class_exists( 'WooCommerce' ) && version_compare( WC()->version, '8.2', '>' ) ) {
			$currencies       = get_woocommerce_currencies();
			$current_currency = get_woocommerce_currency();
			if ( $current_currency !== 'USD' ) {
				$saved = get_option( 'laqirapay_exchange_rate_' . $current_currency, '' );
				if ( ! $saved ) {
					add_action(
						'admin_notices',
						function () {
													$link = admin_url( 'admin.php?page=laqirapay-settings' );
							echo '<div class="notice notice-error"><p>' . esc_html__( 'WooCommerce currency changed. Please set your exchange rate for LaqiraPay from ', 'laqirapay' ) . '<a href="' . esc_url( $link ) . '">' . esc_html__( 'here', 'laqirapay' ) . '</a>.</p></div>';
						}
					);
					LaqiraLogger::log( 300, 'hooks', 'currency_rate_missing', array( 'currency' => $current_currency ) );
				}
			}
		}
	}

	public function woocommerce_email_order_meta_fields( $fields, $sent_to_admin, $order ) {
		if ( $order->get_payment_method() === 'WC_laqirapay' ) {
			$fields = array_merge( $fields, TransactionDetailsRenderer::buildEmailFields( $order ) );
		}
		return $fields;
	}
}
