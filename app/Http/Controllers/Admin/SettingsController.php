<?php

namespace LaqiraPay\Http\Controllers\Admin;

use LaqiraPay\Domain\Models\Settings;
use LaqiraPay\Services\BlockchainService;
/**
 * Handles registration and rendering of plugin settings fields.
 */
class SettingsController
{
    /**
     * Option names that belong to the settings form and should trigger a cache flush.
     */
    private const SETTINGS_OPTION_NAMES = [
        'laqirapay_main_contract',
        'laqirapay_main_rpc_url',
        'laqirapay_api_key',
        'laqirapay_only_logged_in_user',
        'laqirapay_delete_data_uninstall',
        'laqirapay_order_recovery_status',
        'laqirapay_walletconnect_project_id',
        'laqirapay_log_enabled',
        'laqirapay_current_tab_setting',
    ];

    /**
     * Register settings sections and fields for the admin area.
     *
     * @return void
     */
    public function registerAndBuildFields()
    {
        foreach (self::SETTINGS_OPTION_NAMES as $option) {
            register_setting('laqirapay_options', $option);
        }

        add_settings_section(
            'laqirapay_main_section',
            esc_html__('Main Settings', 'laqirapay'),
            [$this, 'displayGeneralSetting'],
            'laqirapay-settings'
        );

        add_settings_field(
            'laqirapay_main_contract',
            esc_html__('Laqira Contract Address', 'laqirapay'),
            [$this, 'mainContractField'],
            'laqirapay-settings',
            'laqirapay_main_section'
        );

        add_settings_field(
            'laqirapay_main_rpc_url',
            esc_html__('Laqira RPC Url', 'laqirapay'),
            [$this, 'mainRpcUrlField'],
            'laqirapay-settings',
            'laqirapay_main_section'
        );

        add_settings_field(
            'laqirapay_api_key',
            esc_html__('API Key', 'laqirapay'),
            [$this, 'apiKeyField'],
            'laqirapay-settings',
            'laqirapay_main_section'
        );

        add_settings_field(
            'laqirapay_walletconnect_project_id',
            esc_html__('WalletConnect Project ID', 'laqirapay'),
            [$this, 'walletconnectProjectIdField'],
            'laqirapay-settings',
            'laqirapay_main_section'
        );

        add_settings_field(
            'laqirapay_only_logged_in_user',
            esc_html__('Only logged in users can pay', 'laqirapay'),
            [$this, 'onlyLoggedInUserField'],
            'laqirapay-settings',
            'laqirapay_main_section'
        );

        add_settings_field(
            'laqirapay_delete_data_uninstall',
            esc_html__('Delete All plugin Data on uninstallation', 'laqirapay'),
            [$this, 'deleteDataUninstallField'],
            'laqirapay-settings',
            'laqirapay_main_section'
        );

        add_settings_field(
            'laqirapay_order_recovery_status',
            esc_html__('Order Status after order complete/recovery by TX hash', 'laqirapay'),
            [$this, 'orderRecoveryStatusField'],
            'laqirapay-settings',
            'laqirapay_main_section'
        );

        add_settings_field(
            'laqirapay_log_enabled',
            esc_html__('Enable Logging', 'laqirapay'),
            [$this, 'logField'],
            'laqirapay-settings',
            'laqirapay_main_section'
        );
    }

    /**
     * Render a view file with provided data.
     *
     * @param string $view View path relative to views directory.
     * @param array  $data Data to extract into the view.
     *
     * @return void
     */
    private function render(string $view, array $data = []): void
    {
        $path = LAQIRAPAY_PLUGIN_DIR . 'app/Http/Views/' . $view . '.php';
        if (file_exists($path)) {
            (function (array $data) use ($path) {
                include $path;
            })($data);
        }
    }

    /**
     * Render the API key field.
     *
     * @return void
     */
    public function apiKeyField(): void
    {
        $apiKey       = Settings::get('laqirapay_api_key');
        $provider_key = Settings::get('laqirapay_provider_key');
        $networks     = (new BlockchainService())->showNetworks();
        $this->render('admin/field-api-key', [
            'value'        => $apiKey,
            'provider_key' => $provider_key,
            'networks'     => $networks,
        ]);
    }

    /**
     * Render the main contract field.
     *
     * @return void
     */
    public function mainContractField(): void
    {
        $value = Settings::get('laqirapay_main_contract');
        $this->render('admin/field-main-contract', ['value' => $value]);
    }

    /**
     * Render the main RPC URL field.
     *
     * @return void
     */
    public function mainRpcUrlField(): void
    {
        $value = Settings::get('laqirapay_main_rpc_url');
        $this->render('admin/field-main-rpc-url', ['value' => $value]);
    }

    /**
     * Render the WalletConnect project ID field.
     *
     * @return void
     */
    public function walletconnectProjectIdField(): void
    {
        $value = Settings::get('laqirapay_walletconnect_project_id');
        $this->render('admin/field-walletconnect-project-id', ['value' => $value]);
    }

    /**
     * Render the option for restricting payments to logged-in users.
     *
     * @return void
     */
    public function onlyLoggedInUserField(): void
    {
        $checked = Settings::get('laqirapay_only_logged_in_user') ? 'checked' : '';
        $this->render('admin/field-only-logged-in-user', ['checked' => $checked]);
    }

    /**
     * Render the option to delete plugin data on uninstall.
     *
     * @return void
     */
    public function deleteDataUninstallField(): void
    {
        $checked = Settings::get('laqirapay_delete_data_uninstall') ? 'checked' : '';
        $this->render('admin/field-delete-data-uninstall', ['checked' => $checked]);
    }

    /**
     * Render the order recovery status field.
     *
     * @return void
     */
    public function orderRecoveryStatusField(): void
    {
        $value          = Settings::get('laqirapay_order_recovery_status');
        $order_statuses = wc_get_order_statuses();
        $this->render('admin/field-order-recovery-status', [
            'value'          => $value,
            'order_statuses' => $order_statuses,
        ]);
    }

    /**
     * Delete all transients related to Web3 caches.
     *
     * @return void
     */
    private function flushWeb3Cache(): void
    {
        delete_transient('laqirapay_cid_cached');
        delete_transient('laqirapay_remote_cid_data');
        delete_transient('laqirapay_networks_cached');
        delete_transient('laqirapay_networks_status_cached');
        delete_transient('laqirapay_networks_assets_cached');
        delete_transient('laqirapay_stablecoins_cached');
    }

    /**
     * Handle clearing of cached Web3 data.
     *
     * @return void
     */
    public function clearWeb3Cache(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'laqirapay'));
        }
        check_admin_referer('laqirapay_clear_web3_cache');
        $this->flushWeb3Cache();
        wp_safe_redirect(wp_get_referer() ?: admin_url('admin.php?page=laqirapay-settings'));
        exit;
    }

    /**
     * Clear Web3 caches when plugin settings are saved.
     *
     * @param string $option Option name being saved.
     * @param mixed  $oldValue Previously stored value.
     * @param mixed  $value New value.
     *
     * @return void
     */
    public function maybeClearWeb3CacheOnSettingsUpdate(string $option, $oldValue = null, $value = null): void
    {
        if (!in_array($option, self::SETTINGS_OPTION_NAMES, true)) {
            return;
        }

        static $cacheCleared = false;

        if ($cacheCleared) {
            return;
        }

        $this->flushWeb3Cache();
        $cacheCleared = true;
    }




    /**
     * Render the option to enable logging.
     *
     * @return void
     */
    public function logField(): void
    {
        $checked = Settings::get('laqirapay_log_enabled') ? 'checked' : '';
        $this->render('admin/field-log', ['checked' => $checked]);
    }


    /**
     * Display the general settings section description.
     *
     * @return void
     */
    public function displayGeneralSetting(): void
    {
        $this->render('admin/section-general');
    }

}
