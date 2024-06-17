<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://laqira.io
 * @since      0.1.0
 *
 * @package    WooLaqiraPay
 * @subpackage WooLaqiraPay/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WooLaqiraPay
 * @subpackage WooLaqiraPay/admin
 * @author     Laqira Protocol <info@laqira.io>
 */
class WooLaqiraPayAdmin
{

    /**
     * The ID of this plugin.
     *
     * @since    0.1.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    0.1.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.1.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_action('admin_menu', array($this, 'addPluginAdminMenu'), 9);
        add_action('admin_init', array($this, 'registerAndBuildFields'));
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    0.1.0
     */
    public function enqueue_styles()
    {

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

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/woo-laqirapay-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    0.1.0
     */
    public function enqueue_scripts()
    {

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
        add_action('admin_head', function () {
            echo '<style>
                    .wp-menu-image img {
                        width: 18px;
                    }
                    </style>';
        });


        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woo-laqirapay-admin.js', array('jquery'), $this->version, false);
    }
    public function addPluginAdminMenu()
    {
        // Main menu item
        add_menu_page($this->plugin_name, 'WooLaqiraPay', 'administrator', $this->plugin_name . '-main', null, 'https://s2.coinmarketcap.com/static/img/coins/64x64/14446.png', 26);
    
        // Add the first submenu item 'Transactions'
        add_submenu_page($this->plugin_name . '-main', 'Transactions', 'Transactions', 'administrator', $this->plugin_name . '-transactions', array($this, 'displayPluginAdminTransactions'));
    
        // Add the second submenu item 'Order Recovery'
        add_submenu_page($this->plugin_name . '-main', 'WooLaqiraPay Order Recovery', 'Order Recovery', 'administrator', $this->plugin_name . '-order-recovery', array($this, 'displayPluginAdminOrderRecovery'));
    
        // Add the third submenu item 'Settings'
        add_submenu_page($this->plugin_name . '-main', 'WooLaqiraPay Settings', 'Settings', 'administrator', $this->plugin_name . '-settings', array($this, 'displayPluginAdminSettings'));
     // Remove the first submenu item
     remove_submenu_page($this->plugin_name . '-main', $this->plugin_name . '-main');

    }

    public function displayPluginAdminDashboard()
    {
        require_once 'partials/' . $this->plugin_name . '-admin-display.php';
    }



    public function displayPluginAdminSettings()
    {
        // set this var to be used in the settings-display view
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        if (isset($_GET['error_message'])) {
            add_action('admin_notices', array($this, 'settingsPageSettingsMessages'));
            do_action('admin_notices', $_GET['error_message']);
        }
        require_once 'partials/' . $this->plugin_name . '-admin-settings-display.php';
    }

    public function displayPluginAdminTransactions()
    {
        // set this var to be used in the settings-display view
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        if (isset($_GET['error_message'])) {
            add_action('admin_notices', array($this, 'settingsPageSettingsMessages'));
            do_action('admin_notices', $_GET['error_message']);
        }
        require_once 'partials/' . $this->plugin_name . '-admin-transactions.php';
    }

    public function displayPluginAdminOrderRecovery()
    {
        // set this var to be used in the settings-display view
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        if (isset($_GET['error_message'])) {
            add_action('admin_notices', array($this, 'settingsPageSettingsMessages'));
            do_action('admin_notices', $_GET['error_message']);
        }
        require_once 'partials/' . $this->plugin_name . '-admin-order-recovery.php';
    }

    public function settingsPageSettingsMessages($error_message)
    {
        switch ($error_message) {
            case '1':
                $message = __('There was an error adding this setting. Please try again.  If this persists, shoot us an email.', 'woo-laqirapay');
                $err_code = esc_attr('settings_page_example_setting');
                $setting_field = 'settings_page_example_setting';
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

    public function registerAndBuildFields()
    {
        register_setting('woo_laqirapay_options', 'woo_laqirapay_api_key'); //save in db->wp-options->laqirapay_api_key
        register_setting('woo_laqirapay_options', 'woo_laqirapay_only_logged_in_user'); //save in db->wp-options->laqirapay_only_logged_in_user
        register_setting('woo_laqirapay_options', 'woo_laqirapay_order_recovery_status');
        add_settings_section('woo_laqirapay_main_section', __('Main Settings', 'woo-laqirapay'), array($this, 'woo_laqirapay_display_general_setting'), 'woo-laqirapay-settings');
        add_settings_field('woo_laqirapay_api_key', __('API Key', 'woo-laqirapay'), array($this, 'woo_laqirapay_api_key_input'), 'woo-laqirapay-settings', 'woo_laqirapay_main_section');

        add_settings_field('woo_laqirapay_only_logged_in_user', __('Only logged in users can pay', 'woo-laqirapay'), array($this, 'woo_laqirapay_only_logged_in_user_input'), 'woo-laqirapay-settings', 'woo_laqirapay_main_section');
        add_settings_field('woo_laqirapay_order_recovery_status', __('Order Status after order recovery by TX hash', 'woo-laqirapay'), array($this, 'woo_laqirapay_order_recovery_status_select'), 'woo-laqirapay-settings', 'woo_laqirapay_main_section');
    }

    //Input field for API key
    public function woo_laqirapay_api_key_input()
    {
        $api_key = get_option('woo_laqirapay_api_key');
        echo '<input type="text" name="woo_laqirapay_api_key" size="60" value="' . esc_attr($api_key) . '" /><br>';
        echo '<small><strong>Provider address :</strong>' . get_provider() . '</small>';
    }

    public function woo_laqirapay_only_logged_in_user_input()
    {
        $only_logged_in_user = get_option('woo_laqirapay_only_logged_in_user');
        $checked = $only_logged_in_user ? 'checked' : '';
        echo '<input type="checkbox" name="woo_laqirapay_only_logged_in_user" value="1" ' . $checked . ' />';
    }

    public function woo_laqirapay_order_recovery_status_select()
    {
        $order_recovery_status = get_option('woo_laqirapay_order_recovery_status');
        $order_statuses = wc_get_order_statuses();
        echo '<select name="woo_laqirapay_order_recovery_status">';
        foreach ($order_statuses as $status_slug => $status_name) {
            $selected = ($status_slug == $order_recovery_status) ? 'selected' : '';
            echo '<option value="' . esc_attr($status_slug) . '" ' . $selected . '>' . esc_html($status_name) . '</option>';
        }
        
        echo '</select>';
    }
    

    public function woo_laqirapay_display_general_setting()
    {
        echo '<p>' . __('Please Enter your Laqira API from', 'woo-laqirapay') . ' <a href="https://laqirahub.com/laqira-pay/register"><Strong>' . __('Get API-KEY', 'woo-laqirapay') . '</strong></a></p>';
    }
}
