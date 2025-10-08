<?php
/**
 * Admin controller for LaqiraPay back office pages.
 *
 * @package LaqiraPay
 */

namespace LaqiraPay\Http\Controllers\Admin;

use LaqiraPay\Domain\Models\Settings;
use LaqiraPay\Domain\Services\LaqiraLogger;
use LaqiraPay\Services\BlockchainService;
use LaqiraPay\Support\Requirements;

/**
 * Manages admin pages, assets, and settings interactions.
 */
class AdminController {

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	private string $plugin_name;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private string $version;

	/**
	 * Settings controller dependency.
	 *
	 * @var SettingsController
	 */
	private SettingsController $settings_controller;

	/**
	 * Blockchain service dependency.
	 *
	 * @var BlockchainService
	 */
	private BlockchainService $blockchain_service;

	/**
	 * Whitelisted admin view templates and the corresponding file names.
	 *
	 * @var array<string,string>
	 */
	private const ADMIN_VIEW_FILES = array(
		'admin-settings-display' => 'laqirapay-admin-settings-display',
		'admin-transactions'     => 'laqirapay-admin-transactions',
	);

	/**
	 * Allowed data keys for each whitelisted view.
	 *
	 * @var array<string,string[]>
	 */
	private const ADMIN_VIEW_ALLOWED_KEYS = array(
		'admin-settings-display' => array(
			'order_recovery_settings_output',
			'order_recovery_content',
			'order_recovery_allowed_tags',
		),
	);

	/**
	 * Initialise controller dependencies and register WordPress hooks.
	 *
	 * @param string                 $plugin_name       Plugin slug.
	 * @param string                 $version           Plugin version.
	 * @param BlockchainService|null $blockchain_service Optional blockchain service instance.
	 */
	public function __construct( string $plugin_name, string $version, ?BlockchainService $blockchain_service = null ) {
		$this->plugin_name         = $plugin_name;
		$this->version             = $version;
		$this->blockchain_service  = $blockchain_service ?? new BlockchainService();
		$this->settings_controller = new SettingsController();

		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ), 9 );
		add_action( 'admin_init', array( $this->settings_controller, 'register_and_build_fields' ) );
		add_action( 'admin_post_laqirapay_clear_web3_cache', array( $this->settings_controller, 'clear_web3_cache' ) );
		add_action( 'updated_option', array( $this->settings_controller, 'maybeclear_web3_cache_on_settings_update' ), 10, 3 );
		add_action( 'added_option', array( $this->settings_controller, 'maybeclear_web3_cache_on_settings_update' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_global_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_assets' ) );
	}

	/**
	 * Load styles and scripts shared across all plugin admin pages.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_global_assets( string $hook ): void {
		if ( ! str_contains( $hook, $this->plugin_name ) ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			LAQIRA_PLUGINS_URL . 'assets/admin/css/laqirapay-admin.css',
			array(),
			$this->version
		);

		wp_enqueue_style(
			'laqirapay-admin-menu',
			LAQIRA_PLUGINS_URL . 'assets/admin/css/menu-icon.css',
			array(),
			$this->version
		);

		wp_enqueue_script(
			$this->plugin_name,
			LAQIRA_PLUGINS_URL . 'assets/admin/js/laqirapay-admin.js',
			array( 'jquery' ),
			$this->version,
			false
		);
	}

	/**
	 * Load assets specific to the settings page.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_settings_assets( string $hook ): void {
		$slug = $hook;

		$pos = strrpos( $hook, '_page_' );
		if ( false !== $pos ) {
			$slug = substr( $hook, $pos + 6 );
		} elseif ( str_starts_with( $hook, 'toplevel_page_' ) ) {
			$slug = substr( $hook, strlen( 'toplevel_page_' ) );
		}

		if ( ! str_starts_with( $slug, $this->plugin_name ) ) {
			return;
		}

		wp_enqueue_style(
			'semantic-ui',
			LAQIRA_PLUGINS_URL . 'assets/admin/semantic/semantic/semantic.min.css',
			array(),
			$this->version
		);

		wp_enqueue_script(
			'semantic-ui-js',
			LAQIRA_PLUGINS_URL . 'assets/admin/semantic/semantic/semantic.min.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_enqueue_style(
			'laqirapay-settings',
			LAQIRA_PLUGINS_URL . 'assets/admin/css/laqirapay-settings.css',
			array(),
			$this->version
		);

		wp_enqueue_script(
			'laqirapay-settings',
			LAQIRA_PLUGINS_URL . 'assets/admin/js/laqirapay-settings.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		$tables_language_option = Settings::get( 'laqirapay_tables_language_option', 'en' );

		if ( ! is_string( $tables_language_option ) || '' === $tables_language_option ) {
			$tables_language_option = 'en';
		}

		$tables_language_option = sanitize_text_field( $tables_language_option );

		wp_localize_script(
			'laqirapay-settings',
			'laqirapay',
			array(
				'tables_language_option' => $tables_language_option,
			)
		);
	}

	/**
	 * Register the plugin admin menu and its subpages.
	 */
	public function add_plugin_admin_menu(): void {
		add_menu_page(
			$this->plugin_name,
			'LaqiraPay',
			'manage_woocommerce', // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce registers this capability.
			$this->plugin_name . '-main',
			array( $this, 'display_plugin_admin_dashboard' ),
			LAQIRA_PLUGINS_URL . 'assets/img/icon-logo.png',
			26
		);

		$pages = array(
			array(
				'page_title' => 'LaqiraPay Dashboard',
				'menu_title' => 'Dashboard',
				'menu_slug'  => $this->plugin_name . '-main',
				'callback'   => array( $this, 'display_plugin_admin_dashboard' ),
				'capability' => 'manage_woocommerce', // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce registers this capability.
			),
			array(
				'page_title' => 'Transactions',
				'menu_title' => 'Transactions',
				'menu_slug'  => $this->plugin_name . '-transactions',
				'callback'   => array( $this, 'display_plugin_admin_transactions' ),
				'capability' => 'manage_woocommerce', // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce registers this capability.
			),
			array(
				'page_title' => 'LaqiraPay Order Recovery',
				'menu_title' => 'Order Recovery',
				'menu_slug'  => $this->plugin_name . '-order-recovery',
				'callback'   => array( $this, 'display_plugin_admin_order_recovery' ),
				'capability' => 'manage_woocommerce', // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce registers this capability.
			),
			array(
				'page_title' => 'LaqiraPay Settings',
				'menu_title' => 'Settings',
				'menu_slug'  => $this->plugin_name . '-settings',
				'callback'   => array( $this, 'display_plugin_admin_settings' ),
				'capability' => 'manage_options',
			),
		);

		foreach ( $pages as $page ) {
			add_submenu_page(
				$this->plugin_name . '-main',
				$page['page_title'],
				$page['menu_title'],
				$page['capability'] ?? 'administrator',
				$page['menu_slug'],
				$page['callback']
			);
		}

		remove_submenu_page( $this->plugin_name . '-main', $this->plugin_name . '-main' );
	}

	/**
	 * Display the main plugin dashboard.
	 */
	public function display_plugin_admin_dashboard(): void {
		LaqiraLogger::log( 200, 'admin', 'view_dashboard' );
		require LAQIRAPAY_PLUGIN_DIR . 'app/Http/Views/admin/' . $this->plugin_name . '-admin-display.php';
	}

	/**
	 * Render the settings page after checking access rights.
	 */
	public function display_plugin_admin_settings(): void {
		$notice = $this->check_access( 'manage_options' );
		if ( null !== $notice ) {
			$this->render_message( $notice );

			return;
		}

		LaqiraLogger::log( 200, 'admin', 'view_settings' );

		ob_start();
		settings_errors();
		$order_recovery_settings_output = ob_get_clean();

		$order_recovery_allowed_tags = laqirapay_get_order_confirmation_allowed_tags();
		$order_recovery_content      = do_shortcode( '[lqr_recovery]' );

		$this->render_admin_page(
			'admin-settings-display',
			array(
				'order_recovery_settings_output' => $order_recovery_settings_output,
				'order_recovery_content'         => $order_recovery_content,
				'order_recovery_allowed_tags'    => $order_recovery_allowed_tags,
			)
		);
	}

	/**
	 * Render the transactions page after validating permissions.
	 */
	public function display_plugin_admin_transactions(): void {
		$notice = $this->check_access( 'manage_woocommerce' );
		if ( null !== $notice ) {
			$this->render_message( $notice );

			return;
		}

		LaqiraLogger::log( 200, 'admin', 'view_transactions' );

		$this->render_admin_page( 'admin-transactions' );
	}

	/**
	 * Render the order recovery page after validating permissions.
	 */
	public function display_plugin_admin_order_recovery(): void {
		$notice = $this->check_access( 'administrator' );
		if ( null !== $notice ) {
			$this->render_message( $notice );

			return;
		}

		LaqiraLogger::log( 200, 'admin', 'view_order_recovery' );

		update_option( 'laqirapay_current_tab_setting', SettingsController::SECTION_ORDER_RECOVERY );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => $this->plugin_name . '-settings',
					'tab'  => SettingsController::SECTION_ORDER_RECOVERY,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Check if the current user has the required capability and plugin requirements are met.
	 *
	 * @param string $capability Required capability.
	 *
	 * @return array|null Message data when access is denied, null otherwise.
	 */
	private function check_access( string $capability ): ?array {
		if ( ! current_user_can( $capability ) ) {
			return array(
				'title'   => esc_html__( 'Access Denied...', 'laqirapay' ),
				'message' => esc_html__( "You don't have right permission to this setting page", 'laqirapay' ),
			);
		}

		if ( ! Requirements::check() ) {
			return array(
				'title'   => esc_html__( 'Plugin Requirements Not Met', 'laqirapay' ),
				'message' => esc_html__( 'Please check PHP >= 8.1 ,wordpress >= 6.3, Woocommerce >= 8.2 ', 'laqirapay' ),
			);
		}

		return null;
	}

	/**
	 * Render an admin notice message.
	 *
	 * @param array $notice Message data.
	 */
	private function render_message( array $notice ): void {
		$title   = $notice['title'];
		$message = $notice['message'];
		require LAQIRAPAY_PLUGIN_DIR . 'app/Http/Views/admin/admin-message.php';
	}

	/**
	 * Render a specific admin page view.
	 *
	 * @param string $view View name.
	 * @param array  $data Data to pass to the view.
	 */
	private function render_admin_page( string $view, array $data = array() ): void {
		$view_key = sanitize_key( $view );
		if ( '' === $view_key || ! isset( self::ADMIN_VIEW_FILES[ $view_key ] ) ) {
			LaqiraLogger::log(
				400,
				'admin',
				'render_admin_page_invalid_view',
				array(
					'view' => sanitize_text_field( (string) $view ),
				)
			);
			return;
		}

		$file = LAQIRAPAY_PLUGIN_DIR . 'app/Http/Views/admin/' . self::ADMIN_VIEW_FILES[ $view_key ] . '.php';
		if ( ! is_readable( $file ) ) {
			LaqiraLogger::log( 500, 'admin', 'render_admin_page_missing_file', array( 'view' => $view_key ) );
			return;
		}

		$active_tab = 'general';
		if ( isset( $_GET['tab'] ) ) {
			$tab_param = sanitize_text_field( wp_unslash( (string) $_GET['tab'] ) );
			if ( '' !== $tab_param ) {
				$active_tab = esc_html( $tab_param );
			}
		}

		if ( isset( $_GET['error_message'] ) ) {
			$error_param = sanitize_text_field( wp_unslash( (string) $_GET['error_message'] ) );
			add_action( 'admin_notices', array( $this, 'settings_page_settings_messages' ) );
			do_action( 'admin_notices', esc_html( $error_param ) );
		}

		$allowed_keys   = self::ADMIN_VIEW_ALLOWED_KEYS[ $view_key ] ?? array();
		$sanitized_data = array() === $allowed_keys
			? array()
			: array_intersect_key( $data, array_flip( $allowed_keys ) );

		( static function ( array $view_data, string $file_path, string $active_tab_value ): void {
			foreach ( $view_data as $key => $value ) {
				if ( is_string( $key ) && '' !== $key ) {
					${$key} = $value;
				}
			}

			$active_tab = $active_tab_value;
			unset( $key, $value );

			require $file_path;
		} )( $sanitized_data, $file, $active_tab );
	}

	/**
	 * Display settings page messages based on an error code.
	 *
	 * @param string $error_message Error code identifier.
	 */
	public function settings_page_settings_messages( string $error_message ): void {
		switch ( $error_message ) {
			case '1':
				$message       = esc_html__( 'There was an error adding this setting. Please try again.  If this persists, shoot us an email.', 'laqirapay' );
				$err_code      = esc_attr( 'settings_page_example_setting' );
				$setting_field = 'settings_page_example_setting';
				break;
			default:
				$message       = '';
				$err_code      = '';
				$setting_field = '';
				break;
		}

		$type = 'error';
		add_settings_error(
			$setting_field,
			$err_code,
			$message,
			$type
		);
	}
}
