<?php

/**
 * Fired during plugin activation
 *
 * @link       https://laqira.io
 * @since      0.1.0
 *
 * @package    WooLaqiraPay
 * @subpackage WooLaqiraPay/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.1.0
 * @package    WooLaqiraPay
 * @subpackage WooLaqiraPay/includes
 * @author     Laqira Protocol <info@laqira.io>
 */

class WooLaqiraPayActivator
{

    /**
     *
     * @since    1.0.0
     */
    public static function activate()
    {

        self::woo_laqira_create_transactions_table();
        self::woolaqirapay_create_recovery_order_page();
        if (get_option('woo_laqirapay_order_recovery_status') === false) {
            update_option('woo_laqirapay_order_recovery_status', 'wc-completed');
        }
        if (get_option('woo_laqirapay_only_logged_in_user') === false) {
            update_option('woo_laqirapay_only_logged_in_user', 'checked');
        }
    }

    // Function to create a custom page
    private static function woolaqirapay_create_recovery_order_page() {
    // Define the page title, content, and other parameters
    $page_title = 'Recovery Order';
    $page_content = '[lqr_recovery]';
    $page_template = ''; // Optional: specify a custom template file

    // Check if the page already exists
    $page_check = get_page_by_title($page_title, OBJECT, 'page');
    if (!isset($page_check->ID)) {
        // Create the page
        $page_id = wp_insert_post(array(
            'post_title'     => $page_title,
            'post_content'   => $page_content,
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_author'    => 1,
            'post_template'  => $page_template
        ));

        // Optionally, store the page ID in the options table for future reference
        if ($page_id && !is_wp_error($page_id)) {
            update_option('woolaqirapay_recovery_order_page_id', $page_id);
        }
    }
}


    

    /**
     * Creates the Laqira transactions table in the WordPress database.
     */
    private static function woo_laqira_create_transactions_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'woo_laqira_transactions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            wc_total_price decimal(10,2) DEFAULT NULL,
            wc_currency varchar(3) DEFAULT NULL,
            wc_created_date datetime DEFAULT NULL,
            wc_confirmed_date datetime DEFAULT NULL,
            token_address varchar(42) DEFAULT NULL,
            token_name varchar(10) DEFAULT NULL,
            token_amount varchar(10) DEFAULT NULL,
            wc_order_id bigint(20) DEFAULT NULL,
            tx_hash varchar(66) DEFAULT NULL,
            req_hash varchar(66) DEFAULT NULL,
            tx_log longtext DEFAULT NULL,
            tx_from varchar(42) DEFAULT NULL,
            tx_to varchar(42) DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}


register_activation_hook(__FILE__, array('WooLaqiraPayActivator', 'activate'));
