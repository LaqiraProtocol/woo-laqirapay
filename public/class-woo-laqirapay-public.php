<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://laqira.io
 * @since      0.1.0
 *
 * @package    WooLaqiraPay
 * @subpackage WooLaqiraPay/public
 * @author     Laqira Protocol <info@laqira.io>
 */
class WooLaqiraPayPublic {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WooLaqiraPayLoader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WooLaqiraPayLoader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/laqirapay-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style('google-fonts-roboto', 'https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap', false);
		wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css', array(), $this->version);



	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WooLaqiraPayLoader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WooLaqiraPayLoader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//  wp_enqueue_script('laqirapayJS', plugins_url('laqirapay/public/js/laqirapay-public.js'), array('jquery'), '1.0.3', true);
	}


}