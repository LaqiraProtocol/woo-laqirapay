<?php

use LaqiraPay\Support\Requirements;
use LaqiraPay\Helpers\FileHelper;

?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <h2><?php esc_html_e('LaqiraPay Settings', 'laqirapay'); ?></h2>
    <?php
    if (current_user_can('administrator')) {
        if (Requirements::check()) {
            settings_errors();

            if (! is_ssl()) {
                
                echo '<div class="notice notice-warning"><p>' .
                    esc_html__('SSL is not enabled on the site; therefore Web3 functions cannot receive network data.', 'laqirapay') .
                    '</p></div>';
            }

            $abi = json_decode(
                FileHelper::get_contents_secure(LAQIRA_PLUGINS_URL . 'assets/json/cidAbi.json'),
                true
            );
            if (empty($abi)) {
                echo '<div class="notice notice-warning"><p>' .
                   esc_html__('ABI files not found or their JSON structure is invalid; network data cannot be received. This may also be due to an SSL failure', 'laqirapay') .
                    '</p></div>';
            }

            $current_tab = get_option('laqirapay_current_tab_setting', 'laqirapay_main_section');
    ?>
            <form id="laqirapay_admin_form" method="POST" action="options.php">
                <?php settings_fields('laqirapay_options'); ?>
                <input type="hidden" name="laqirapay_current_tab_setting" id="laqirapay_current_tab_setting_input" value="<?php echo esc_attr($current_tab); ?>">
                <div id="laqirapay_admin_wrapper">
                    <div id="laqirapay_admin_menu" class="ui labeled stackable large vertical menu attached">
                        <a class="green item <?php echo $current_tab === 'laqirapay_main_section' ? 'active' : ''; ?>" data-tab="laqirapay_main_section">
                            <i class="settings icon"></i>
                            <div class="header"><?php esc_html_e('Main Settings', 'laqirapay'); ?></div>
                            <span class="laqirapay_menu_description"><?php esc_html_e('Configure LaqiraPay', 'laqirapay'); ?></span>
                        </a>
                    </div>
                    <div id="laqirapay_tabs_wrapper">
                        <div class="ui bottom attached tab segment <?php echo $current_tab === 'laqirapay_main_section' ? 'active' : ''; ?>" data-tab="laqirapay_main_section">
                            <div class="laqirapay_attached_content_wrapper">
                                <h2 class="ui block header">
                                    <i class="settings icon"></i>
                                    <div class="content">
                                        <?php esc_html_e('Main Settings', 'laqirapay'); ?>
                                        <div class="sub header"><?php esc_html_e('Configure LaqiraPay', 'laqirapay'); ?></div>
                                    </div>
                                </h2>
                                <table class="form-table">
                                    <?php do_settings_fields('laqirapay-settings', 'laqirapay_main_section'); ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <input type="submit" name="submit" class="ui primary button" value="<?php esc_attr_e('Save Settings', 'laqirapay'); ?>">
            </form>
    <?php
        } else {
            echo '<h2>' . esc_html__('Plugin Requirements Not Met', 'laqirapay') . '</h2>';
            echo '<p>' . esc_html__('Please check PHP >= 7.4 ,wordpress >= 6.3, Woocommerce >= 8.2 ', 'laqirapay') . '</p>';
        }
    } else {
        echo '<h2>' . esc_html__('Access Denied...', 'laqirapay') . '</h2>';
        echo '<p>' . esc_html__("You don't have right permission to this setting page", 'laqirapay') . '</p>';
    }
    ?>
</div>