<?php

namespace LaqiraPayments\Http\Controllers\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



use LaqiraPayments\Domain\Models\Settings;
use LaqiraPayments\Services\BlockchainService;
use LaqiraPayments\Helpers\JwtHelper;
use LaqiraPayments\Helpers\WooCommerceHelper;
use LaqiraPayments\Domain\Services\UtilityService;
use LaqiraPayments\Domain\Services\LaqiraLogger;
use LaqiraPayments\Support\LaqiraPaymentsTranslations;
use function LaqiraPayments\Support\laqira_payments_cookie_options;
use Web3\Utils;

/**
 * Handles payment operations on the frontend.
 */
class PaymentController {

	private WooCommerceHelper $wooCommerceService;
	private JwtHelper $jwtService;
	private BlockchainService $blockchainService;
	private UtilityService $utilityService;

	/**
	 * Allowed frontend payment views and their permitted data keys.
	 *
	 * @var array<string,array{path:string,allowed_keys:string[]}>
	 */
	private const VIEW_CONFIG = array(
		'public/checkout' => array(
			'path'         => 'public/checkout',
			'allowed_keys' => array( 'nets', 'assets', 'provider', 'valid', 'errors', 'message' ),
		),
	);

	/**
	 * Initialize dependencies for payment processing.
	 *
	 * @param WooCommerceHelper|null $wooCommerceService WooCommerce helper.
	 * @param JwtHelper|null         $jwtService         JWT helper.
	 * @param BlockchainService|null $blockchainService Blockchain service.
	 * @param UtilityService|null    $utilityService    Utility service.
	 */
	public function __construct(
		?WooCommerceHelper $wooCommerceService = null,
		?JwtHelper $jwtService = null,
		?BlockchainService $blockchainService = null,
		?UtilityService $utilityService = null
	) {
		$this->wooCommerceService = $wooCommerceService ?: new WooCommerceHelper();
		$this->jwtService         = $jwtService ?: new JwtHelper();
		$this->blockchainService  = $blockchainService ?: new BlockchainService();
		$this->utilityService     = $utilityService ?: new UtilityService();
	}

	/**
	 * Process a WooCommerce payment and return redirect details.
	 *
	 * @param int $order_id WooCommerce order ID.
	 *
	 * @return array Result data for WooCommerce.
	 */
	public function process_payment( int $order_id ): array {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			LaqiraLogger::log( 400, 'checkout', 'process_payment_order_missing', array( 'order_id' => $order_id ) );
			return array( 'result' => 'failure' );
		}

		$order->update_status( 'on-hold', esc_html__( 'Awaiting payment', 'laqira-payments' ) );
		$order->reduce_order_stock();
		WC()->cart->empty_cart();

		LaqiraLogger::log( 200, 'checkout', 'process_payment_success', array( 'order_id' => $order_id ) );

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_order_received_url(),
		);
	}

	/**
	 * Render payment fields and enqueue assets when required.
	 *
	 * @return void
	 */
	public function payment_fields(): void {
		$requires_login = Settings::get( 'laqira_payments_only_logged_in_user' );
		if ( $requires_login && ! is_user_logged_in() ) {
			LaqiraLogger::log( 300, 'checkout', 'payment_fields_login_required' );
			$this->render( 'public/checkout', array( 'message' => esc_html__( 'Please login to make a payment!', 'laqira-payments' ) ) );
			return;
		}

		$configuration    = $this->blockchainService->getConfigurationReadiness();
		$hasConfiguration = ! in_array( false, $configuration, true );
		$nets             = array();
		$assets           = array();
		$provider         = '';

		if ( $hasConfiguration ) {
			$nets     = $this->blockchainService->getNetworks();
			$assets   = $this->blockchainService->getNetworksAssets();
			$provider = $this->blockchainService->getProvider();
		}

		$valid = (
			$hasConfiguration &&
			is_array( $nets ) && $nets !== array() &&
			is_array( $assets ) && $assets !== array() &&
			Utils::isAddress( (string) $provider )
		); // Ensure all blockchain data is present before loading UI.

		$error_messages = array();

		if ( $valid ) {
			LaqiraLogger::log( 200, 'checkout', 'payment_fields_assets_loaded' );
			$this->enqueue_scripts();
		} else {
			if ( $hasConfiguration ) {
				$error_messages = $this->collectCheckoutErrors( $nets, $assets, $provider );
				LaqiraLogger::log( 300, 'checkout', 'payment_fields_assets_missing', array( 'errors' => $error_messages ) );
			} else {
				$error_messages = array(
					esc_html__( 'LaqiraPayments is not fully configured yet. Please choose another payment method.', 'laqira-payments' ),
				);
				LaqiraLogger::log(
					300,
					'checkout',
					'payment_fields_missing_configuration',
					array_merge( $configuration, array( 'errors' => $error_messages ) )
				);
			}

			$timestamp = current_time( 'timestamp' );
			wp_register_script( 'laqira-paymentsJS', ( LAQIRA_PLUGINS_URL . '/assets/public/js/laqira-payments-first.js' ), array( 'jquery' ), $timestamp, true );
			$primary_error = $error_messages[0] ?? esc_html__( 'LaqiraPayments is temporarily unavailable. Please choose another payment method.', 'laqira-payments' );
			$this->localize_bootstrap_script(
				array(
					'orderData' => array(
						'error'               => $primary_error,
						'errors'              => array_values( $error_messages ),
						'laqiraAajaxUrl'      => admin_url( 'admin-ajax.php' ),
						'laqiraAjaxnonce'     => '',
						'originalOrderAmount' => 0,
						'cartTotal'           => '0',
					),
				)
			);
			wp_enqueue_script( 'laqira-paymentsJS' );
		}

		$this->render(
			'public/checkout',
			array(
				'nets'     => $nets,
				'assets'   => $assets,
				'provider' => $provider,
				'valid'    => $valid,
				'errors'   => $error_messages,
			)
		);
	}

	private function render( string $view, array $data = array() ): void {
		$normalized_view = strtolower( str_replace( '\\', '/', $view ) );
		if ($normalized_view === null) {
   			 $normalized_view = '';
			}

		$normalized_view = preg_replace( '/[^a-z0-9_\/-]/', '', $normalized_view ?? '' );

		if ( $normalized_view === '' || ! isset( self::VIEW_CONFIG[ $normalized_view ] ) ) {
			LaqiraLogger::log(
				400,
				'checkout',
				'render_invalid_view',
				array(
					'view' => sanitize_text_field( (string) $view ),
				)
			);
			return;
		}

		$config = self::VIEW_CONFIG[ $normalized_view ];
		$path   = LAQIRAPAYMENTS_PLUGIN_DIR . 'app/Http/Views/' . $config['path'] . '.php';

		if ( ! is_readable( $path ) ) {
			LaqiraLogger::log( 500, 'checkout', 'render_missing_view', array( 'view' => $normalized_view ) );
			return;
		}

		$allowed_keys = $config['allowed_keys'];
		$view_data    = $allowed_keys === array()
			? array()
			: array_intersect_key( $data, array_flip( $allowed_keys ) );

		( static function ( array $safe_data ) use ( $path ): void {
			include $path;
		} )( $view_data );
	}

	private function enqueue_scripts(): void {
		$timestamp = current_time( 'timestamp' );

		wp_enqueue_script( 'laqira-paymentsJS', ( LAQIRA_PLUGINS_URL . '/assets/public/js/laqira-payments-first.js' ), array( 'jquery' ), $timestamp, true );

		$asset_file = LAQIRAPAYMENTS_PLUGIN_DIR . '/build/laqiraPaymentsMain.asset.php';
		$assetjs    = include $asset_file;

		wp_enqueue_script(
			'laqira-payments-block-script',
			( LAQIRA_PLUGINS_URL . '/build/laqiraPaymentsMain.js' ),
			$assetjs['dependencies'],
			$timestamp,
			array(
				'in_footer' => true,
			)
		);
		if ( ! is_admin() ) {
			wp_register_style(
				'laqira-payments-block-style',
				( LAQIRA_PLUGINS_URL . 'build/laqiraPaymentsMain.css' ),
				array(),
				$timestamp
			);
			wp_enqueue_style( 'laqira-payments-block-style' );
		}

		global $woocommerce, $wp;
		$order_id    = isset( $wp->query_vars['order-pay'] ) ? absint( $wp->query_vars['order-pay'] ) : null;
		$order       = $order_id ? wc_get_order( $order_id ) : null;
		$order_total = $order instanceof \WC_Order ? (float) $order->get_total( 'edit' ) : (float) $woocommerce->cart->get_total( 'edit' );

		$currencies       = get_woocommerce_currencies();
		$current_currency = get_woocommerce_currency();
		if ( $current_currency !== 'USD' ) {
			$saved_exchange_rate = Settings::get( 'laqira_payments_exchange_rate_' . $current_currency, '' );
			if ( ! $saved_exchange_rate ) {
				$saved_exchange_rate = 1;
			}
			$final_amount           = $order_total / $saved_exchange_rate;
			$final_amount_formatted = number_format( $final_amount, 2 );
		} else {
			$saved_exchange_rate    = 1;
			$final_amount           = $order_total / $saved_exchange_rate;
			$final_amount_formatted = $final_amount;
		}

		$translations = LaqiraPaymentsTranslations::get_translations();
		$provider     = $this->blockchainService->getProviderLocal();
		$order_data   = array(
			'paymentType'            => 'Classic',
			'pluginUrl'              => LAQIRA_PLUGINS_URL,
			'homeUrl'                => get_home_url(),
			'shopUrl'                => get_permalink( wc_get_page_id( 'shop' ) ),
			'myAccountUrl'           => get_permalink( wc_get_page_id( 'myaccount' ) ),
			'currencySymbol'         => 'USD',
			'exchangeRate'           => $saved_exchange_rate,
			'originalCurrencySymbol' => $current_currency,
			'originalOrderAmount'    => $order_total,
			'cartTotal'              => $final_amount_formatted,
			'providerAddress'        => $provider,
			'laqiraAajaxUrl'         => admin_url( 'admin-ajax.php' ),
			'laqiraAjaxnonce'        => wp_create_nonce( 'laqira_nonce' ),
			'mainContractAddress'    => LAQIRAPAYMENTS_MAIN_CONTRACT_ADDRESS,
			'originalOrderID'        => $order_id ?: null,
			'walletConnectProjectID' => Settings::get( 'laqira_payments_walletconnect_project_id' ),
			'wcpi'                   => $this->wooCommerceService->getWcpi(),
			'translation'            => $translations,
			'isRTL'                  => $this->utilityService->detectRtl(),
			'isGuest'                => Settings::get( 'laqira_payments_only_logged_in_user' ),
		);

		$order_data['error']  = '';
		$order_data['errors'] = array();

		$cookieOptions = laqira_payments_cookie_options( time() + 3600 );
		setcookie(
			'laqira_jwt',
			$this->jwtService->create_access_token( $provider )['token'],
			$cookieOptions
		); // Store short-lived JWT for authenticated AJAX calls.

		$this->localize_bootstrap_script(
			array(
				'orderData' => $order_data,
			)
		);

		wp_localize_script(
			'laqira-payments-block-script',
			'LaqiraData',
			array(
				'availableNetworks' => $this->blockchainService->getNetworks(),
				'availableAssets'   => $this->blockchainService->getNetworksAssets(),
				'stableCoins'       => $this->blockchainService->getStableCoins(),
				'orderData'         => $order_data,
			)
		);
	}

	private function localize_bootstrap_script( array $context ): void {
		wp_localize_script(
			'laqira-paymentsJS',
			'LaqiraData',
			$context
		);
	}

	/**
	 * Generate user-facing error messages when the checkout widget cannot be rendered.
	 *
	 * @param mixed $nets     Available blockchain networks.
	 * @param mixed $assets   Available blockchain assets.
	 * @param mixed $provider Configured provider address.
	 */
	private function collectCheckoutErrors( $nets, $assets, $provider ): array {
		$errors = array();

		if ( ! is_array( $nets ) || $nets === array() ) {
			$errors[] = esc_html__( 'Required network information is unavailable.', 'laqira-payments' );
		}

		if ( ! is_array( $assets ) || $assets === array() ) {
			$errors[] = esc_html__( 'Digital asset configuration could not be loaded.', 'laqira-payments' );
		}

		if ( ! Utils::isAddress( (string) $provider ) ) {
			$errors[] = esc_html__( 'Merchant wallet address is misconfigured.', 'laqira-payments' );
		}

		if ( $errors === array() ) {
			$errors[] = esc_html__( 'LaqiraPayments is temporarily unavailable. Please choose another payment method.', 'laqira-payments' );
		}

		return $errors;
	}
}
