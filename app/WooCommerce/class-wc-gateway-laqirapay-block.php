<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * WC_laqirapay Payments Blocks integration
 *
 * @since 1.0.3
 */
use LaqiraPay\WooCommerce\Gateway;
use LaqiraPay\Services\BlockchainService;
use LaqiraPay\Helpers\JwtHelper;
use LaqiraPay\Helpers\WooCommerceHelper;
use LaqiraPay\Domain\Services\UtilityService;
use LaqiraPay\Support\LaqiraPayTranslations;
use function LaqiraPay\Support\laqirapay_cookie_options;

final class WC_laqirapay_Block extends AbstractPaymentMethodType {

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
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'WC_laqirapay';

	public function __construct( ?WooCommerceHelper $wooCommerceService = null, ?JwtHelper $jwtService = null, ?BlockchainService $blockchainService = null, ?UtilityService $utilityService = null ) {
		$this->wooCommerceService = $wooCommerceService ?: new WooCommerceHelper();
		$this->jwtService         = $jwtService ?: new JwtHelper();
		$this->blockchainService  = $blockchainService ?: new BlockchainService();
		$this->utilityService     = $utilityService ?: new UtilityService();

		// Ensure the JWT cookie is set once the main query is available.
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

		// Clear existing laqira_jwt cookie by setting it with an expired time
		if ( isset( $_COOKIE['laqira_jwt'] ) ) {
				$expiredCookie = laqirapay_cookie_options( time() - 3600 );
				setcookie( 'laqira_jwt', '', $expiredCookie );
		}

				// Set new laqira_jwt cookie with fresh token
				$cookieOptions = laqirapay_cookie_options( time() + 3600 );
				setcookie(
					'laqira_jwt',
					$this->jwtService->create_access_token( $provider )['token'],
					$cookieOptions
				); // Store short-lived JWT for authenticated AJAX calls.
	}

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_WC_laqirapay_settings', array() );

		// FIX: Gateway expects ?PaymentController as first arg; previously a WooCommerceHelper was passed.
		// Pass null (allowed by type) so Gateway can handle defaults internally.
		$this->gateway = new Gateway( null, $this->jwtService, $this->blockchainService );
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
				'laqirapayJS',
				LAQIRA_PLUGINS_URL . '/assets/public/js/laqirapay-first.js',
				array( 'jquery' ),
				$timestamp,
				true
			);
		}

		$asset_file = plugin_dir_path( __FILE__ ) . '../../build/laqiraPayMain.asset.php';
		$assetjs    = include $asset_file;

		wp_enqueue_script(
			'wclaqirapay-script',
			LAQIRA_PLUGINS_URL . '/build/laqiraPayMain.js',
			$assetjs['dependencies'],
			$timestamp,
			true
		);

		if ( ! is_admin() ) {
			wp_register_style(
				'wclaqirapay-style',
				LAQIRA_PLUGINS_URL . 'build/laqiraPayMain.css',
				array(),
				$timestamp
			);
			wp_enqueue_style( 'wclaqirapay-style' );
		}

		global $woocommerce, $wp; // Ensure $wp is available

		// Recognize the Order-Pay page
		$order_id   = isset( $wp->query_vars['order-pay'] ) ? absint( $wp->query_vars['order-pay'] ) : null;
		$cart_total = $order_id ? wc_get_order( $order_id )->get_total( 'edit' ) : $this->wooCommerceService->getTotal();

		$currencies       = get_woocommerce_currencies();
		$current_currency = get_woocommerce_currency();
		$currency_label   = isset( $currencies[ $current_currency ] ) ? $currencies[ $current_currency ] : $current_currency;

		if ( $current_currency !== 'USD' ) {
			$saved_exchange_rate = get_option( 'laqirapay_exchange_rate_' . $current_currency, '' );
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

		$translations = LaqiraPayTranslations::get_translations();
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
			'mainContractAddress'    => CONTRACT_ADDRESS,
			'originalOrderID'        => null,
			'walletConnectProjectID' => get_option( 'laqirapay_walletconnect_project_id' ),
			'wcpi'                   => $this->wooCommerceService->getWcpi(),
			'translation'            => $translations,
			'isRTL'                  => $this->utilityService->detectRtl(),
			'isGuest'                => get_option( 'laqirapay_only_logged_in_user' ),
		);

		wp_localize_script(
			'laqirapayJS',
			'LaqiraData',
			array(
				'laqiraAajaxUrl' => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_localize_script(
			'wclaqirapay-script',
			'LaqiraData',
			array(
				'availableNetworks' => $this->blockchainService->getNetworks(),
				'availableAssets'   => $this->blockchainService->getNetworksAssets(),
				'stableCoins'       => $this->blockchainService->getStableCoins(),
				'orderData'         => $order_data,
			)
		);

		return array( 'wclaqirapay-script' );
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
