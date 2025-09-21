<?php

namespace LaqiraPay\Http\Controllers\Admin;

use LaqiraPay\Domain\Services\LaqiraLogger;
use LaqiraPay\Services\BlockchainService;
use LaqiraPay\Support\Requirements;

/**
 * Manages admin pages and assets for the LaqiraPay plugin.
 */
class AdminController
{
    private string $plugin_name;
    private string $version;
    private SettingsController $settingsController;
    private BlockchainService $blockchainService;

    /**
     * Set up controller dependencies and register WordPress hooks.
     *
     * @param string $plugin_name Plugin slug.
     * @param string $version Plugin version.
     * @param BlockchainService|null $blockchainService Optional blockchain service.
     */
    public function __construct(string $plugin_name, string $version, ?BlockchainService $blockchainService = null)
    {
        $this->plugin_name       = $plugin_name;
        $this->version           = $version;
	    if ( $blockchainService ) {
		    $this->blockchainService = $blockchainService;
	    } else {
		    $this->blockchainService = new BlockchainService();
	    }
        $this->settingsController = new SettingsController();

        add_action('admin_menu', [$this, 'addPluginAdminMenu'], 9);
        add_action('admin_init', [$this->settingsController, 'registerAndBuildFields']);
        add_action('admin_post_laqirapay_clear_web3_cache', [$this->settingsController, 'clearWeb3Cache']);
        add_action('updated_option', [$this->settingsController, 'maybeClearWeb3CacheOnSettingsUpdate'], 10, 3);
        add_action('added_option', [$this->settingsController, 'maybeClearWeb3CacheOnSettingsUpdate'], 10, 3);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_global_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_settings_assets']);
    }

    /**
     * Load styles and scripts shared across all plugin admin pages.
     *
     * @param string $hook Current admin page hook.
     *
     * @return void
     */
    public function enqueue_global_assets(string $hook): void
    {
        if ( ! str_contains( $hook, $this->plugin_name ) ) {
            return;
        }
		wp_enqueue_style(
            $this->plugin_name,
            LAQIRA_PLUGINS_URL . 'assets/admin/css/laqirapay-admin.css',
            [],
            $this->version
		);

        wp_enqueue_style(
            'laqirapay-admin-menu',
            LAQIRA_PLUGINS_URL . 'assets/admin/css/menu-icon.css',
            [],
            $this->version
        );

        wp_enqueue_script(
            $this->plugin_name,
            LAQIRA_PLUGINS_URL . 'assets/admin/js/laqirapay-admin.js',
            ['jquery'],
            $this->version,
            false
        );
    }

    /**
     * Load assets specific to the settings page.
     *
     * @param string $hook Current admin page hook.
     *
     * @return void
     */
    public function enqueue_settings_assets(string $hook): void
    {
        $slug = $hook;
    if (($pos = strrpos($hook, '_page_')) !== false) {
        // case: '*_page_<menu-slug>'
        $slug = substr($hook, $pos + 6); // after '_page_'
    } elseif ( str_starts_with( $hook, 'toplevel_page_' ) ) {
        // case: 'toplevel_page_<menu-slug>'
        $slug = substr($hook, strlen('toplevel_page_'));
    }


	    if (!str_starts_with($slug, 'laqirapay')) {
	        return;
	    }

//        wp_enqueue_style('wp-color-picker');
//        wp_enqueue_script('wp-color-picker');
//
//        wp_enqueue_media();

        wp_enqueue_style(
            'semantic-ui',
            LAQIRA_PLUGINS_URL . 'assets/admin/semantic/semantic/semantic.min.css',
            [],
            $this->version
        );

        wp_enqueue_script(
            'semantic-ui-js',
            LAQIRA_PLUGINS_URL . 'assets/admin/semantic/semantic/semantic.min.js',
            ['jquery'],
            $this->version,
            true
        );

        wp_enqueue_style(
            'laqirapay-settings',
            LAQIRA_PLUGINS_URL . 'assets/admin/css/laqirapay-settings.css',
            [],
            $this->version
        );

        wp_enqueue_script(
            'laqirapay-settings',
            LAQIRA_PLUGINS_URL . 'assets/admin/js/laqirapay-settings.js',
            ['jquery'],
            $this->version,
            true
        );
    }

    /**
     * Register the plugin admin menu and its subpages.
     *
     * @return void
     */
    public function addPluginAdminMenu(): void
    {
        add_menu_page(
            $this->plugin_name,
            'LaqiraPay',
            'manage_woocommerce',
            $this->plugin_name . '-main',
            [$this, 'displayPluginAdminDashboard'],
            LAQIRA_PLUGINS_URL . 'assets/img/icon-logo.png',
            26
        );

        $pages = [
            [
                'page_title' => 'LaqiraPay Dashboard',
                'menu_title' => 'Dashboard',
                'menu_slug'  => $this->plugin_name . '-main',
                'callback'   => [$this, 'displayPluginAdminDashboard'],
                'capability' => 'manage_woocommerce',
            ],
            [
                'page_title' => 'Transactions',
                'menu_title' => 'Transactions',
                'menu_slug'  => $this->plugin_name . '-transactions',
                'callback'   => [$this, 'displayPluginAdminTransactions'],
                'capability' => 'manage_woocommerce',
            ],
            [
                'page_title' => 'LaqiraPay Order Recovery',
                'menu_title' => 'Order Recovery',
                'menu_slug'  => $this->plugin_name . '-order-recovery',
                'callback'   => [$this, 'displayPluginAdminOrderRecovery'],
                'capability' => 'manage_woocommerce',
            ],
            [
                'page_title' => 'LaqiraPay Currency Rate',
                'menu_title' => 'Currency Rate',
                'menu_slug'  => $this->plugin_name . '-currency-rate',
                'callback'   => [$this, 'displayPluginAdminCurrencyRate'],
                'capability' => 'manage_woocommerce',
            ],
            [
                'page_title' => 'LaqiraPay Settings',
                'menu_title' => 'Settings',
                'menu_slug'  => $this->plugin_name . '-settings',
                'callback'   => [$this, 'displayPluginAdminSettings'],
                'capability' => 'manage_options',
            ],
        ];

        foreach ($pages as $page) {
            // Register each admin submenu page defined above.
            add_submenu_page(
                $this->plugin_name . '-main',
                $page['page_title'],
                $page['menu_title'],
                $page['capability'] ?? 'administrator',
                $page['menu_slug'],
                $page['callback']
            );
        }

        remove_submenu_page($this->plugin_name . '-main', $this->plugin_name . '-main');
    }

    /**
     * Display the main plugin dashboard.
     *
     * @return void
     */
    public function displayPluginAdminDashboard(): void
    {
        LaqiraLogger::log(200, 'admin', 'view_dashboard');
        require_once LAQIRAPAY_PLUGIN_DIR . 'app/Http/Views/admin/' . $this->plugin_name . '-admin-display.php';
    }

    /**
     * Render the settings page after checking access rights.
     *
     * @return void
     */
    public function displayPluginAdminSettings(): void
    {
        if ($notice = $this->check_access('manage_options')) {
            $this->render_message($notice);
            return;
        }

        LaqiraLogger::log(200, 'admin', 'view_settings');

        $this->render_admin_page('admin-settings-display');
    }

    /**
     * Render the transactions page after validating permissions.
     *
     * @return void
     */
    public function displayPluginAdminTransactions(): void
    {
        if ($notice = $this->check_access('manage_woocommerce')) {
            $this->render_message($notice);
            return;
        }

        LaqiraLogger::log(200, 'admin', 'view_transactions');

        $this->render_admin_page('admin-transactions');
    }


    /**
     * Render the order recovery page after validating permissions.
     *
     * @return void
     */
    public function displayPluginAdminOrderRecovery(): void
    {
        if ($notice = $this->check_access('administrator')) {
            $this->render_message($notice);
            return;
        }

        LaqiraLogger::log(200, 'admin', 'view_order_recovery');


        ob_start();
        settings_errors();

 

        $settings_output = ob_get_clean();
        $content         = wp_kses(
            do_shortcode('[lqr_recovery]'),
            [ // Whitelist limited HTML elements rendered by shortcode.
                'form'   => [
                    'id'    => [],
                    'class' => [],
                ],
                'input'  => [
                    'type'        => [],
                    'id'          => [],
                    'name'        => [],
                    'size'        => [],
                    'value'       => [],
                    'placeholder' => [],
                    'style'       => [],
                ],
                'button' => [
                    'class' => [],
                    'type'  => [],
                    'id'    => [],
                    'name'  => [],
                    'value' => [],
                    'style' => [],
                ],
                'div'    => [
                    'id'    => [],
                    'class' => [],
                    'style' => [],
                ],
                'label'  => [
                    'for' => [],
                ],
                'img'    => [
                    'src'    => [],
                    'class'  => [],
                    'width'  => [],
                    'height' => [],
                    'alt'    => [],
                ],
            ]
        );

        $this->render_admin_page('admin-order-recovery', [
            'settings_output' => $settings_output,
            'content'         => $content,
        ]);
    }

    /**
     * Show the currency rate management page.
     *
     * @return void
     */
    public function displayPluginAdminCurrencyRate(): void
    {
        if ($notice = $this->check_access('administrator')) {
            $this->render_message($notice);
            return;
        }

        LaqiraLogger::log(200, 'admin', 'view_currency_rate');

        $saved_rate       = '';
        $nonce_field      = '';
        $message          = '';

        $current_currency = get_woocommerce_currency();
        if ($current_currency !== 'USD') {
            if (
                isset($_POST['laqirapay_exchange_rate'], $_POST['laqirapay_currency_rate_nonce']) &&
                wp_verify_nonce(
                    sanitize_text_field(wp_unslash($_POST['laqirapay_currency_rate_nonce'])),
                    'laqirapay_currency_rate_action'
                )
            ) {
                $exchange_rate = (float) wp_unslash($_POST['laqirapay_exchange_rate']);
                update_option('laqirapay_exchange_rate_' . $current_currency, $exchange_rate);
                $message = esc_html__('Exchange rate saved!', 'laqirapay');
            }
            $saved_rate  = get_option('laqirapay_exchange_rate_' . $current_currency, '');
            $nonce_field = wp_nonce_field(
                'laqirapay_currency_rate_action',
                'laqirapay_currency_rate_nonce',
                true,
                false
            );
        } else {
            update_option('laqirapay_exchange_rate_' . $current_currency, 1);
        }

        $this->render_admin_page('admin-currency-rates', [
            'current_currency' => $current_currency,
            'saved_rate'       => $saved_rate,
            'nonce_field'      => $nonce_field,
            'message'          => $message,
        ]);
    }

    /**
     * Check if the current user has the required capability and plugin requirements are met.
     *
     * @param string $capability Required capability.
     *
     * @return array|null Message data when access is denied, null otherwise.
     */
    private function check_access(string $capability): ?array
    {
        if (! current_user_can($capability)) {
            return [
                'title'   => esc_html__('Access Denied...', 'laqirapay'),
                'message' => esc_html__("You don't have right permission to this setting page", 'laqirapay'),
            ];
        }

        if (! Requirements::check()) {
            return [
                'title'   => esc_html__('Plugin Requirements Not Met', 'laqirapay'),
                'message' => esc_html__('Please check PHP >= 7.4 ,wordpress >= 6.3, Woocommerce >= 8.2 ', 'laqirapay'),
            ];
        }

        return null;
    }

    /**
     * Render an admin notice message.
     *
     * @param array $notice Message data.
     *
     * @return void
     */
    private function render_message(array $notice): void
    {
        $title   = $notice['title'];
        $message = $notice['message'];
        require LAQIRAPAY_PLUGIN_DIR . 'app/Http/Views/admin/admin-message.php';
    }

    /**
     * Render a specific admin page view.
     *
     * @param string $view View name.
     * @param array  $data Data to pass to the view.
     *
     * @return void
     */
    private function render_admin_page(string $view, array $data = []): void
    {
        $active_tab = isset($_GET['tab']) ? esc_html(sanitize_text_field($_GET['tab'])) : 'general';
        if (isset($_GET['error_message'])) {
            add_action('admin_notices', [$this, 'settingsPageSettingsMessages']);
            $error_message = esc_html(sanitize_text_field($_GET['error_message']));
            do_action('admin_notices', $error_message);
        }

        $file = LAQIRAPAY_PLUGIN_DIR . 'app/Http/Views/admin/' . $view . '.php';
        if (! file_exists($file)) {
            $file = LAQIRAPAY_PLUGIN_DIR . 'app/Http/Views/admin/' . $this->plugin_name . '-' . $view . '.php';
        }

        extract($data);
        require $file;
    }

    /**
     * Display settings page messages based on an error code.
     *
     * @param string $error_message Error code identifier.
     *
     * @return void
     */
    public function settingsPageSettingsMessages(string $error_message): void
    {
        switch ($error_message) {
            case '1':
                $message       = esc_html__('There was an error adding this setting. Please try again.  If this persists, shoot us an email.', 'laqirapay');
                $err_code      = esc_attr('settings_page_example_setting');
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
