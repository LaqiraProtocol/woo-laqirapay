<?php
/**
 * Settings controller for LaqiraPay admin pages.
 *
 * @package LaqiraPay
 */

namespace LaqiraPay\Http\Controllers\Admin;

use LaqiraPay\Domain\Models\Settings;
use LaqiraPay\Domain\Services\LaqiraLogger;
use LaqiraPay\Services\BlockchainService;

/**
 * Handles registration and rendering of plugin settings fields.
 */
class SettingsController {

	/**
	 * Currently active WooCommerce currency code.
	 *
	 * @var String
	 */
	private string $current_currency = '';

	/**
	 * Option name used for persisting the current currency exchange rate.
	 *
	 * @var String
	 */
	private string $exchange_rate_option_name = '';

	/**
	 * Identifier for the general settings option group.
	 */
	private const OPTION_GROUP_GENERAL = 'laqirapay_general_options';

	/**
	 * Identifier for the exchange rate option group.
	 */
	private const OPTION_GROUP_EXCHANGE_RATE = 'laqirapay_exchange_rate_options';

	/**
	 * Identifier for the general settings section.
	 */
	public const SECTION_GENERAL = 'laqirapay_main_section';

	/**
	 * Identifier for the exchange rate settings section.
	 */
	public const SECTION_EXCHANGE_RATE = 'laqirapay_exchange_rate_section';

	/**
	 * Identifier for the order recovery settings section.
	 */
	public const SECTION_ORDER_RECOVERY = 'laqirapay_order_recovery_section';

	/**
	 * Whitelisted view templates rendered by the settings controller.
	 *
	 * @var array<string,array{path:string,allowed_keys:string[]}>
	 */
	private const VIEW_CONFIG = array(
		'admin/field-api-key'                  => array(
			'path'         => 'admin/field-api-key',
			'allowed_keys' => array( 'value', 'provider_key', 'networks', 'is_config_ready' ),
		),
		'admin/field-main-contract'            => array(
			'path'         => 'admin/field-main-contract',
			'allowed_keys' => array( 'value' ),
		),
		'admin/field-main-rpc-url'             => array(
			'path'         => 'admin/field-main-rpc-url',
			'allowed_keys' => array( 'value' ),
		),
		'admin/field-walletconnect-project-id' => array(
			'path'         => 'admin/field-walletconnect-project-id',
			'allowed_keys' => array( 'value' ),
		),
		'admin/field-only-logged-in-user'      => array(
			'path'         => 'admin/field-only-logged-in-user',
			'allowed_keys' => array( 'checked' ),
		),
		'admin/field-delete-data-uninstall'    => array(
			'path'         => 'admin/field-delete-data-uninstall',
			'allowed_keys' => array( 'checked' ),
		),
		'admin/field-order-recovery-status'    => array(
			'path'         => 'admin/field-order-recovery-status',
			'allowed_keys' => array( 'order_statuses', 'value' ),
		),
		'admin/field-log'                      => array(
			'path'         => 'admin/field-log',
			'allowed_keys' => array( 'checked' ),
		),
		'admin/field-exchange-rate'            => array(
			'path'         => 'admin/field-exchange-rate',
			'allowed_keys' => array( 'current_currency', 'saved_rate', 'nonce_field', 'option_name' ),
		),
		'admin/section-general'                => array(
			'path'         => 'admin/section-general',
			'allowed_keys' => array(),
		),
		'admin/section-exchange-rate'          => array(
			'path'         => 'admin/section-exchange-rate',
			'allowed_keys' => array(),
		),
		'admin/section-order-recovery'         => array(
			'path'         => 'admin/section-order-recovery',
			'allowed_keys' => array(),
		),
	);

	/**
	 * Option names that belong to the settings form and should trigger a cache flush.
	 *
	 * @var string[]
	 */
	private const SETTINGS_OPTION_NAMES = array(
		'laqirapay_main_contract',
		'laqirapay_main_rpc_url',
		'laqirapay_api_key',
		'laqirapay_only_logged_in_user',
		'laqirapay_delete_data_uninstall',
		'laqirapay_order_recovery_status',
		'laqirapay_walletconnect_project_id',
		'laqirapay_log_enabled',
		'laqirapay_current_tab_setting',
	);

	/**
	 * Register settings sections and fields for the admin area.
	 */
	public function register_and_build_fields(): void {
		$this->register_exchange_rate_setting();

		foreach ( self::SETTINGS_OPTION_NAMES as $option ) {
			register_setting( self::OPTION_GROUP_GENERAL, $option );
		}

		$settings_page = 'laqirapay-settings';

		add_settings_section(
			self::SECTION_GENERAL,
			esc_html__( 'Main Settings', 'laqirapay' ),
			array( $this, 'display_general_setting' ),
			$settings_page
		);

		add_settings_field(
			'laqirapay_main_contract',
			esc_html__( 'Laqira Contract Address', 'laqirapay' ),
			array( $this, 'main_contract_field' ),
			$settings_page,
			self::SECTION_GENERAL
		);

		add_settings_field(
			'laqirapay_main_rpc_url',
			esc_html__( 'Laqira RPC Url', 'laqirapay' ),
			array( $this, 'main_rpc_url_field' ),
			$settings_page,
			self::SECTION_GENERAL
		);

		add_settings_field(
			'laqirapay_api_key',
			esc_html__( 'API Key', 'laqirapay' ),
			array( $this, 'api_key_field' ),
			$settings_page,
			self::SECTION_GENERAL
		);

		add_settings_field(
			'laqirapay_walletconnect_project_id',
			esc_html__( 'WalletConnect Project ID', 'laqirapay' ),
			array( $this, 'walletconnect_project_id_field' ),
			$settings_page,
			self::SECTION_GENERAL
		);

		add_settings_field(
			'laqirapay_only_logged_in_user',
			esc_html__( 'Only logged in users can pay', 'laqirapay' ),
			array( $this, 'only_logged_in_user_field' ),
			$settings_page,
			self::SECTION_GENERAL
		);

		add_settings_field(
			'laqirapay_delete_data_uninstall',
			esc_html__( 'Delete All plugin Data on uninstallation', 'laqirapay' ),
			array( $this, 'delete_data_uninstall_field' ),
			$settings_page,
			self::SECTION_GENERAL
		);

		add_settings_field(
			'laqirapay_order_recovery_status',
			esc_html__( 'Order Status after order complete/recovery by TX hash', 'laqirapay' ),
			array( $this, 'order_recovery_status_field' ),
			$settings_page,
			self::SECTION_GENERAL
		);

		add_settings_field(
			'laqirapay_log_enabled',
			esc_html__( 'Enable Logging', 'laqirapay' ),
			array( $this, 'log_field' ),
			$settings_page,
			self::SECTION_GENERAL
		);

		add_settings_section(
			self::SECTION_EXCHANGE_RATE,
			esc_html__( 'Exchange Rate', 'laqirapay' ),
			array( $this, 'display_exchange_rate_setting' ),
			$settings_page
		);

		add_settings_field(
			'laqirapay_exchange_rate_field',
			esc_html__( 'Currency Exchange Rate', 'laqirapay' ),
			array( $this, 'exchange_rate_field' ),
			$settings_page,
			self::SECTION_EXCHANGE_RATE
		);

		add_settings_section(
			self::SECTION_ORDER_RECOVERY,
			esc_html__( 'Order Recovery', 'laqirapay' ),
			array( $this, 'display_order_recovery_setting' ),
			$settings_page
		);
	}

	/**
	 * Render a view file with provided data.
	 *
	 * @param string $view View path relative to views directory.
	 * @param array  $data Data to extract into the view.
	 */
	private function render( string $view, array $data = array() ): void {
		$normalized_view = strtolower( str_replace( '\\', '/', $view ) );
		if ($normalized_view === null) {
   			 $normalized_view = '';
			}
		$normalized_view = preg_replace( '/[^a-z0-9_\/-]/', '', $normalized_view ?? '' );

		if ( '' === $normalized_view || ! isset( self::VIEW_CONFIG[ $normalized_view ] ) ) {
			LaqiraLogger::log(
				400,
				'admin',
				'settings_render_invalid_view',
				array(
					'view' => sanitize_text_field( (string) $view ),
				)
			);
			return;
		}

		$config = self::VIEW_CONFIG[ $normalized_view ];
		$path   = LAQIRAPAY_PLUGIN_DIR . 'app/Http/Views/' . $config['path'] . '.php';

		if ( ! is_readable( $path ) ) {
			LaqiraLogger::log( 500, 'admin', 'settings_render_missing_view', array( 'view' => $normalized_view ) );
			return;
		}

		$allowed_keys = $config['allowed_keys'];
		$view_data    = array() === $allowed_keys
			? array()
			: array_intersect_key( $data, array_flip( $allowed_keys ) );

		( static function ( array $safe_data ) use ( $path ): void {
			$data = $safe_data;
			include $path;
		} )( $view_data );
	}

	/**
	 * Render the API key field.
	 */
	public function api_key_field(): void {
		$api_key      = (string) Settings::get( 'laqirapay_api_key' );
		$provider_key = (string) Settings::get( 'laqirapay_provider_key' );
		$contract     = (string) Settings::get( 'laqirapay_main_contract' );
		$rpc_url      = (string) Settings::get( 'laqirapay_main_rpc_url' );

		$is_config_ready = '' !== $api_key && '' !== $contract && '' !== $rpc_url;
		$networks        = $is_config_ready ? ( new BlockchainService() )->showNetworks() : array();

		$this->render(
			'admin/field-api-key',
			array(
				'value'           => $api_key,
				'provider_key'    => $provider_key,
				'networks'        => $networks,
				'is_config_ready' => $is_config_ready,
			)
		);
	}

	/**
	 * Render the exchange rate field within the settings page.
	 */
	public function exchange_rate_field(): void {
		$current_currency = '' === $this->current_currency ? get_woocommerce_currency() : $this->current_currency;
		$option_name      = '' === $this->exchange_rate_option_name
			? 'laqirapay_exchange_rate_' . $current_currency
			: $this->exchange_rate_option_name;

		if ( 'USD' === $current_currency ) {
			update_option( $option_name, 1 );
		}

		$saved_rate  = get_option( $option_name, '' );
		$nonce_field = 'USD' === $current_currency
			? ''
			: wp_nonce_field( 'laqirapay_currency_rate_action', 'laqirapay_currency_rate_nonce', true, false );

		$this->render(
			'admin/field-exchange-rate',
			array(
				'current_currency' => $current_currency,
				'saved_rate'       => $saved_rate,
				'nonce_field'      => $nonce_field,
				'option_name'      => $option_name,
			)
		);
	}

	/**
	 * Render the main contract field.
	 */
	public function main_contract_field(): void {
		$value = Settings::get( 'laqirapay_main_contract' );
		$this->render( 'admin/field-main-contract', array( 'value' => $value ) );
	}

	/**
	 * Render the main RPC URL field.
	 */
	public function main_rpc_url_field(): void {
		$value = Settings::get( 'laqirapay_main_rpc_url' );
		$this->render( 'admin/field-main-rpc-url', array( 'value' => $value ) );
	}

	/**
	 * Render the WalletConnect project ID field.
	 */
	public function walletconnect_project_id_field(): void {
		$value = Settings::get( 'laqirapay_walletconnect_project_id' );
		$this->render( 'admin/field-walletconnect-project-id', array( 'value' => $value ) );
	}

	/**
	 * Render the option for restricting payments to logged-in users.
	 */
	public function only_logged_in_user_field(): void {
		$checked = Settings::get( 'laqirapay_only_logged_in_user' ) ? 'checked' : '';
		$this->render( 'admin/field-only-logged-in-user', array( 'checked' => $checked ) );
	}

	/**
	 * Render the option to delete plugin data on uninstall.
	 */
	public function delete_data_uninstall_field(): void {
		$checked = Settings::get( 'laqirapay_delete_data_uninstall' ) ? 'checked' : '';
		$this->render( 'admin/field-delete-data-uninstall', array( 'checked' => $checked ) );
	}

	/**
	 * Render the order recovery status field.
	 */
	public function order_recovery_status_field(): void {
		$value          = Settings::get( 'laqirapay_order_recovery_status' );
		$order_statuses = wc_get_order_statuses();
		$this->render(
			'admin/field-order-recovery-status',
			array(
				'value'          => $value,
				'order_statuses' => $order_statuses,
			)
		);
	}

	/**
	 * Persist exchange rate updates submitted from the settings page.
	 *
	 * @param mixed $value Submitted option value.
	 */
	public function sanitize_exchange_rate_option( $value ): string {
		$currency    = '' === $this->current_currency ? get_woocommerce_currency() : $this->current_currency;
		$option_name = '' === $this->exchange_rate_option_name
			? 'laqirapay_exchange_rate_' . $currency
			: $this->exchange_rate_option_name;

		if ( 'USD' === $currency ) {
			return '1';
		}

		$request_method       = '';
		$request_method_input = laqirapay_filter_input( INPUT_SERVER, 'REQUEST_METHOD' );
		if ( is_string( $request_method_input ) ) {
			$request_method = strtoupper( sanitize_text_field( wp_unslash( $request_method_input ) ) );
		}
		if ( 'POST' !== $request_method ) {
			return (string) get_option( $option_name, '' );
		}

		$option_page       = '';
		$option_page_input = laqirapay_filter_input( INPUT_POST, 'option_page' );
		if ( is_string( $option_page_input ) ) {
			$option_page = $this->sanitize_option_key( wp_unslash( $option_page_input ) );
		}
		if ( self::OPTION_GROUP_EXCHANGE_RATE !== $option_page ) {
			return (string) get_option( $option_name, '' );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce registers this capability.
			add_settings_error(
				'laqirapay_exchange_rate',
				'laqirapay_exchange_rate_cap',
				esc_html__( 'You are not allowed to update the exchange rate.', 'laqirapay' )
			);

			return (string) get_option( $option_name, '' );
		}

		$nonce       = '';
		$nonce_input = laqirapay_filter_input( INPUT_POST, 'laqirapay_currency_rate_nonce' );
		if ( is_string( $nonce_input ) ) {
			$nonce = sanitize_text_field( wp_unslash( $nonce_input ) );
		}
		if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'laqirapay_currency_rate_action' ) ) {
			add_settings_error(
				'laqirapay_exchange_rate',
				'laqirapay_exchange_rate_nonce',
				esc_html__( 'Security check failed. Please refresh the page and try again.', 'laqirapay' )
			);

			return (string) get_option( $option_name, '' );
		}

		if ( ! is_scalar( $value ) ) {
			$value = '';
		}

		$formatted_rate = wc_format_decimal( (string) $value );
		if ( '' === $formatted_rate ) {
			add_settings_error(
				'laqirapay_exchange_rate',
				'laqirapay_exchange_rate_format',
				esc_html__( 'Exchange rate must be a number.', 'laqirapay' )
			);

			return (string) get_option( $option_name, '' );
		}

		add_settings_error(
			'laqirapay_exchange_rate',
			'laqirapay_exchange_rate_saved',
			esc_html__( 'Exchange rate saved!', 'laqirapay' ),
			'updated'
		);

		return $formatted_rate;
	}

	/**
	 * Register the exchange rate setting with WordPress.
	 */
	private function register_exchange_rate_setting(): void {
		$this->current_currency          = get_woocommerce_currency();
		$this->exchange_rate_option_name = 'laqirapay_exchange_rate_' . $this->current_currency;

		register_setting(
			self::OPTION_GROUP_EXCHANGE_RATE,
			$this->exchange_rate_option_name,
			array(
				'sanitize_callback' => array( $this, 'sanitize_exchange_rate_option' ),
			)
		);

		if ( 'USD' === $this->current_currency ) {
			update_option( $this->exchange_rate_option_name, 1 );
		}
	}

	/**
	 * Delete all transients related to Web3 caches.
	 */
	private function flush_web3_cache(): void {
		delete_transient( 'laqirapay_cid_cached' );
		delete_transient( 'laqirapay_remote_cid_data' );
		delete_transient( 'laqirapay_networks_cached' );
		delete_transient( 'laqirapay_networks_status_cached' );
		delete_transient( 'laqirapay_networks_assets_cached' );
		delete_transient( 'laqirapay_stablecoins_cached' );
	}

	/**
	 * Handle clearing of cached Web3 data.
	 */
	public function clear_web3_cache(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'laqirapay' ) );
		}

		check_admin_referer( 'laqirapay_clear_web3_cache' );
		$this->flush_web3_cache();

		$redirect_url = wp_get_referer();
		if ( ! $redirect_url ) {
			$redirect_url = admin_url( 'admin.php?page=laqirapay-settings' );
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Clear Web3 caches when plugin settings are saved.
	 *
	 * @param string $option    Option name being saved.
	 * @param mixed  $old_value Previously stored value.
	 * @param mixed  $value     New value.
	 */
	public function maybeclear_web3_cache_on_settings_update( string $option, $old_value = null, $value = null ): void {
		unset( $old_value, $value );

		if ( ! in_array( $option, self::SETTINGS_OPTION_NAMES, true ) ) {
			return;
		}

		static $cache_cleared = false;

		if ( $cache_cleared ) {
			return;
		}

		$this->flush_web3_cache();
		$cache_cleared = true;
	}

	/**
	 * Render the option to enable logging.
	 */
	public function log_field(): void {
		$checked = Settings::get( 'laqirapay_log_enabled' ) ? 'checked' : '';
		$this->render( 'admin/field-log', array( 'checked' => $checked ) );
	}

	/**
	 * Display the exchange rate settings section description.
	 */
	public function display_exchange_rate_setting(): void {
		$this->render( 'admin/section-exchange-rate' );
	}

	/**
	 * Display the order recovery settings section description.
	 */
	public function display_order_recovery_setting(): void {
		$this->render( 'admin/section-order-recovery' );
	}

	/**
	 * Display the general settings section description.
	 */
	public function display_general_setting(): void {
		$this->render( 'admin/section-general' );
	}

	/**
	 * Local fallback for sanitize_key when WordPress helpers are unavailable.
	 *
	 * @param string $value Raw option key.
	 * @return string Sanitized key.
	 */
	private function sanitize_option_key( string $value ): string {
		if ( function_exists( 'sanitize_key' ) ) {
			return sanitize_key( $value );
		}

		$value = strtolower( $value );
		if ($value === null) {
   			 $value = '';
			}

		return preg_replace( '/[^a-z0-9_\-]/', '', $value ) ?? '';
	}
}
