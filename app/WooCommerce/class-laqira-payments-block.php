<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use LaqiraPayments\Domain\Services\UtilityService;
use LaqiraPayments\Helpers\JwtHelper;
use LaqiraPayments\Helpers\WooCommerceHelper;
use LaqiraPayments\Services\BlockchainService;
use LaqiraPayments\Support\LaqiraPaymentsTranslations;
use LaqiraPayments\WooCommerce\Gateway;
use function LaqiraPayments\Support\laqira_payments_cookie_options;

/**
 * LaqiraPayments block checkout integration.
 */
final class LaqiraPaymentsBlock extends AbstractPaymentMethodType {

	/**
	 * The gateway instance.
	 *
	 * @var Gateway
	 */
	private $gateway;

	/**
	 * @var WooCommerceHelper
	 */
	private $wooCommerceService;

	/**
	 * @var JwtHelper
	 */
	private $jwtService;

	/**
	 * @var BlockchainService
	 */
	private $blockchainService;

	/**
	 * @var UtilityService
	 */
	private $utilityService;

	/**
	 * Payment method identifier for Blocks.
	 *
	 * @var string
	 */
	protected $name = Gateway::GATEWAY_ID;

	public function __construct( ?WooCommerceHelper $wooCommerceService = null, ?JwtHelper $jwtService = null, ?BlockchainService $blockchainService = null, ?UtilityService $utilityService = null ) {
		$this->wooCommerceService = $wooCommerceService ?: new WooCommerceHelper();
		$this->jwtService         = $jwtService ?: new JwtHelper();
		$this->blockchainService  = $blockchainService ?: new BlockchainService();
		$this->utilityService     = $utilityService ?: new UtilityService();

		if ( function_exists( 'did_action' ) && did_action( 'wp' ) ) {
			$this->set_laqira_jwt_cookie();
		} else {
			add_action( 'wp', array( $this, 'set_laqira_jwt_cookie' ) );
		}
	}

	/**
	 * Set the laqira_jwt cookie when visiting checkout-related pages.
	 */
	public function set_laqira_jwt_cookie() {
		if ( ! is_checkout() && ! is_wc_endpoint_url( 'order-pay' ) ) {
			return;
		}

		$provider = $this->blockchainService->getProviderLocal();

		$existing_cookie = filter_input( INPUT_COOKIE, 'laqira_jwt', FILTER_UNSAFE_RAW );
		if ( is_string( $existing_cookie ) && '' !== $existing_cookie ) {
			$expired_cookie = laqira_payments_cookie_options( time() - 3600 );
			setcookie( 'laqira_jwt', '', $expired_cookie );
		}

		$cookie_options = laqira_payments_cookie_options( time() + 3600 );
		setcookie(
			'laqira_jwt',
			$this->jwtService->create_access_token( $provider )['token'],
			$cookie_options
		);
	}

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( Gateway::SETTINGS_OPTION_KEY, get_option( Gateway::LEGACY_SETTINGS_OPTION_KEY, array() ) );
		$this->gateway  = new Gateway( null, $this->jwtService, $this->blockchainService );
	}

	public function is_active() {
		return $this->gateway->is_available();
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$timestamp = current_time( 'timestamp' );

		if ( is_checkout() || is_wc_endpoint_url( 'order-pay' ) ) {
			wp_enqueue_script(
				'laqira-payments-bootstrap-script',
				LAQIRA_PLUGINS_URL . '/assets/public/js/laqira-payments-first.js',
				array( 'jquery' ),
				$timestamp,
				true
			);
		}

		$asset_file = plugin_dir_path( __FILE__ ) . '../../build/laqiraPaymentsMain.asset.php';
		$assetjs    = include $asset_file;

		wp_enqueue_script(
			'laqira-payments-block-script',
			LAQIRA_PLUGINS_URL . '/build/laqiraPaymentsMain.js',
			$assetjs['dependencies'],
			$timestamp,
			true
		);

		if ( ! is_admin() ) {
			wp_register_style(
				'laqira-payments-block-style',
				LAQIRA_PLUGINS_URL . 'build/laqiraPaymentsMain.css',
				array(),
				$timestamp
			);
			wp_enqueue_style( 'laqira-payments-block-style' );
		}

		global $wp;

		$order_id   = isset( $wp->query_vars['order-pay'] ) ? absint( $wp->query_vars['order-pay'] ) : null;
		$order      = $order_id ? wc_get_order( $order_id ) : null;
		$cart_total = $order instanceof \WC_Order ? (float) $order->get_total( 'edit' ) : $this->wooCommerceService->getTotal();

		$currencies       = get_woocommerce_currencies();
		$current_currency = get_woocommerce_currency();
		$currency_label   = isset( $currencies[ $current_currency ] ) ? $currencies[ $current_currency ] : $current_currency;

		if ( 'USD' !== $current_currency ) {
			$saved_exchange_rate = get_option( 'laqira_payments_exchange_rate_' . $current_currency, '' );
			if ( ! $saved_exchange_rate ) {
				$saved_exchange_rate = 1;
			}
			$final_amount           = $cart_total / $saved_exchange_rate;
			$final_amount_formatted = number_format( $final_amount, 2 );
		} else {
			$saved_exchange_rate    = 1;
			$final_amount           = $cart_total / $saved_exchange_rate;
			$final_amount_formatted = $final_amount;
		}

		$translations = LaqiraPaymentsTranslations::get_translations();
		$provider     = $this->blockchainService->getProviderLocal();

		$order_data = array(
			'paymentType'            => 'Block',
			'pluginUrl'              => LAQIRA_PLUGINS_URL,
			'homeUrl'                => get_home_url(),
			'shopUrl'                => get_permalink( wc_get_page_id( 'shop' ) ),
			'myAccountUrl'           => get_permalink( wc_get_page_id( 'myaccount' ) ),
			'currencySymbol'         => 'USD',
			'exchangeRate'           => $saved_exchange_rate,
			'originalCurrencySymbol' => $current_currency,
			'originalOrderAmount'    => $cart_total,
			'cartTotal'              => $final_amount_formatted,
			'providerAddress'        => $provider,
			'laqiraAajaxUrl'         => admin_url( 'admin-ajax.php' ),
			'laqiraAjaxnonce'        => wp_create_nonce( 'laqira_nonce' ),
			'mainContractAddress'    => LAQIRAPAYMENTS_MAIN_CONTRACT_ADDRESS,
			'originalOrderID'        => $order_id ?: null,
			'walletConnectProjectID' => get_option( 'laqira_payments_walletconnect_project_id' ),
			'wcpi'                   => $this->wooCommerceService->getWcpi(),
			'translation'            => $translations,
			'isRTL'                  => $this->utilityService->detectRtl(),
			'isGuest'                => get_option( 'laqira_payments_only_logged_in_user' ),
			'currencyLabel'          => $currency_label,
		);

		wp_localize_script(
			'laqira-payments-bootstrap-script',
			'LaqiraData',
			array(
				'availableNetworks' => $this->blockchainService->getNetworks(),
				'availableAssets'   => $this->blockchainService->getNetworksAssets(),
				'stableCoins'       => $this->blockchainService->getStableCoins(),
				'orderData'         => $order_data,
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

		return array( 'laqira-payments-block-script' );
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return array(
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
		);
	}
}
