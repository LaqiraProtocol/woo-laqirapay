<?php
/**
 * Core plugin bootstrap class.
 *
 * @package LaqiraPay
 */

use LaqiraPay\Helpers\JwtHelper;
use LaqiraPay\Helpers\WooCommerceHelper;
use LaqiraPay\Hooks\ExtrasService;
use LaqiraPay\Http\Controllers\Admin\AdminController;
use LaqiraPay\Http\Controllers\Ajax\AjaxController;
use LaqiraPay\Http\Controllers\Frontend\AssetsController as PublicAssetsController;
use LaqiraPay\Services\BlockchainService;
use LaqiraPay\Support\LaqiraPayLoader;

/**
 * Coordinates plugin services and hook registration.
 *
 * @since 1.0.0
 */
class LaqiraPayMain {

	/**
	 * Loader responsible for maintaining all hooks.
	 *
	 * @since 1.0.0
	 *
	 * @var LaqiraPayLoader
	 */
	protected $loader;

	/**
	 * Unique identifier of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * Current plugin version.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( defined( 'LAQIRAPAY_VERSION' ) ) {
			$this->version = LAQIRAPAY_VERSION;
		} else {
			$this->version = '0.1.0';
		}
		$this->plugin_name = LAQIRAPAY_PLUGIN_NAME;

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Initialize plugin dependencies and register core hooks.
	 *
	 * @since 1.0.0
	 */
	private function load_dependencies() {
		$this->loader = new LaqiraPayLoader();

		$ajax_controller = AjaxController::class;
		$this->loader->add_action( 'wp_ajax_laqirapay_verify_transaction', $ajax_controller, 'verifyTransaction' );
		$this->loader->add_action( 'wp_ajax_nopriv_laqirapay_verify_transaction', $ajax_controller, 'verifyTransaction' );
		$this->loader->add_action( 'wp_ajax_laqirapay_update_cart_data', $ajax_controller, 'updateCartData' );
		$this->loader->add_action( 'wp_ajax_nopriv_laqirapay_update_cart_data', $ajax_controller, 'updateCartData' );

		$extras = new ExtrasService( new WooCommerceHelper(), new JwtHelper() );
		$extras->register();
	}

	/**
	 * Register admin-area hooks.
	 *
	 * @since 1.0.0
	 */
	private function define_admin_hooks() {
		new AdminController( $this->get_plugin_name(), $this->get_version(), new BlockchainService() );
	}

	/**
	 * Register public-facing hooks.
	 *
	 * @since 1.0.0
	 */
	private function define_public_hooks() {
		$assets_controller = new PublicAssetsController();

		$this->loader->add_action( 'wp_enqueue_scripts', $assets_controller, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $assets_controller, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run(): void {
		$this->loader->run();
	}

	/**
	 * Retrieve the plugin identifier.
	 *
	 * @since 1.0.0
	 *
	 * @return string Plugin slug.
	 */
	public function get_plugin_name(): string {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the plugin version number.
	 *
	 * @since 1.0.0
	 *
	 * @return string Plugin version.
	 */
	public function get_version(): string {
		return $this->version;
	}
}
