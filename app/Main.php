<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://laqira.io
 * @since      0.1.0
 *
 * @package    LaqiraPay
 * @subpackage LaqiraPay/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.1.0
 * @package    LaqiraPay
 * @subpackage LaqiraPay/includes
 * @author     Laqira Protocol <info@laqira.io>
 */



use LaqiraPay\Http\Controllers\Frontend\AssetsController as PublicAssetsController;
use LaqiraPay\Http\Controllers\Admin\AdminController;
use LaqiraPay\Http\Controllers\Ajax\AjaxController;
use LaqiraPay\Services\BlockchainService;
use LaqiraPay\Helpers\JwtHelper;
use LaqiraPay\Helpers\WooCommerceHelper;
use LaqiraPay\Hooks\ExtrasService;
use LaqiraPay\Support\LaqiraPayLoader;

class LaqiraPayMain {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      LaqiraPayLoader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'LAQIRAPAY_VERSION' ) ) {
			$this->version = LAQIRAPAY_VERSION;
		} else {
			$this->version = '0.1.0';
		}
		$this->plugin_name = 'laqirapay';

                $this->load_dependencies();
                $this->define_admin_hooks();
                $this->define_public_hooks();

	}

        /**
         * Initialize plugin dependencies and register core hooks.
         *
         * @since    1.0.0
         * @access   private
        */
        private function load_dependencies() {

		$this->loader = new LaqiraPayLoader();

                $ajaxController = AjaxController::class;
                $this->loader->add_action('wp_ajax_laqirapay_verify_transaction', $ajaxController, 'verifyTransaction');
                $this->loader->add_action('wp_ajax_nopriv_laqirapay_verify_transaction', $ajaxController, 'verifyTransaction');
                $this->loader->add_action('wp_ajax_laqirapay_update_cart_data', $ajaxController, 'updateCartData');
                $this->loader->add_action('wp_ajax_nopriv_laqirapay_update_cart_data', $ajaxController, 'updateCartData');

                $extras = new ExtrasService(new WooCommerceHelper(), new JwtHelper());
                $extras->register();




        }

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
        private function define_admin_hooks() {

               new AdminController( $this->get_plugin_name(), $this->get_version(), new BlockchainService() );

        }

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
        private function define_public_hooks() {

               $assets_controller = new PublicAssetsController();

               $this->loader->add_action( 'wp_enqueue_scripts', $assets_controller, 'enqueue_styles' );
               $this->loader->add_action( 'wp_enqueue_scripts', $assets_controller, 'enqueue_scripts' );

       }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run(): void {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name(): string {
		return $this->plugin_name;
	}


	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version(): string {
		return $this->version;
	}
}
