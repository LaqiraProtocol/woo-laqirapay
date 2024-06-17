<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://laqira.io
 * @since      0.1.0
 *
 * @package    WooLaqiraPay
 * @subpackage WooLaqiraPay/admin/partials
 */
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Woo_Laqira_Transactions_List extends WP_List_Table
{

    /**
     * Retrieve data from the database.
     *
     * @return array Fetched data from the database.
     */
    function get_data()
    {
        global $wpdb;
        $table_name_woo_laqira_transactions = $wpdb->prefix . "woo_laqira_transactions";
        $search = (isset($_REQUEST['s'])) ? $_REQUEST['s'] : false;
        $search_condition = "%" . $wpdb->esc_like($search) . "%";
        $columns_to_search = ['wc_order_id', 'tx_from', 'tx_hash'];
        $column_conditions = [];
        foreach ($columns_to_search as $column) {
            $column_conditions[] = "$column LIKE %s";
        }

        $search_condition_combined = implode(' OR ', $column_conditions);

        if ($search) {
            $result = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name_woo_laqira_transactions WHERE $search_condition_combined",
                $search_condition,
                $search_condition,
                $search_condition
            ), ARRAY_A);
        } else {
            $result = $wpdb->get_results("SELECT * FROM $table_name_woo_laqira_transactions", ARRAY_A);
        }

        return $result;
    }

    /**
     * Prepare the table items for display.
     */
    function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $data = $this->get_data();

        // Pagination
        $per_page = $this->get_items_per_page('transactions_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items = count($data);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);

        usort($data, array($this, 'sort_data'));
        $data_slice = array_slice($data, (($current_page - 1) * $per_page), $per_page);
        $this->items = $data_slice;
    }


    /**
     * Define the columns to be displayed in the table.
     *
     * @return array Columns and their labels.
     */
    function get_columns()
    {
        $columns = array(
            'wc_order_id' => __('Order ID', 'woo-laqirapay'),
            'wc_total_price' => __('Total Price', 'woo-laqirapay'),
            'wc_currency' => __('Currency', 'woo-laqirapay'),
            'token_name' => __('Paid By', 'woo-laqirapay'),
            'token_amount' => __('Token Amount', 'woo-laqirapay'),
            'tx_hash' => __('Transaction Hash', 'woo-laqirapay'),
            'tx_from' => __('From', 'woo-laqirapay'),
        );
        return $columns;
    }

    /**
     * Define which columns should be hidden.
     *
     * @return array Column names to be hidden.
     */
    function get_hidden_columns()
    {
        $screen = get_current_screen();
        if ($screen) {
            return get_hidden_columns($screen);
        }
        return array();
    }


    /**
     * Define which columns are sortable.
     *
     * @return array Sortable columns and their default sort order.
     */
    function get_sortable_columns()
    {
        return array(
            'wc_order_id' => array('wc_order_id', false),
            'wc_total_price' => array('wc_total_price', false),
            'tx_hash' => array('tx_hash', false),
            'tx_from' => array('tx_from', false),
        );
    }
    /**
     * Render the default column output.
     *
     * @param array $item The current item's data.
     * @param string $column_name The current column name.
     * @return string Output for the column.
     */
    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'wc_total_price':
                return $item[$column_name];
            case 'wc_currency':
                return $item[$column_name];
            case 'token_name':
                return $item[$column_name];
            case 'token_amount':
                return $item[$column_name];
            case 'req_hash':
                return $item[$column_name];
            case 'tx_from':
                return $item[$column_name];
            case 'wc_order_id':
                return '<a href="' . get_admin_url() . "post.php?post=" . $item[$column_name] . '&action=edit" target="_blank">' . $item[$column_name] . '</a>';
            case 'tx_hash':
                return '<a href="https://bscscan.com/tx/' . $item[$column_name] . '" target="_blank">' . $item[$column_name] . '</a>';
            default:
                return print_r($item, true);
        }
    }


    /**
     * Set up the screen options for the table.
     */
    function screen_options()
    {
        $option = 'per_page';
        $args = array(
            'label' => 'Transactions',
            'default' => 10,
            'option' => 'transactions_per_page'
        );
        add_screen_option($option, $args);
    }



    function sort_data($a, $b)
    {
        // Set defaults
        $orderby = 'wc_order_id';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }

        $result = strcmp($a[$orderby], $b[$orderby]);

        if ($order === 'asc') {
            return $result;
        }

        return -$result;
    }
}

/**
 * Add the WooLaqiraPay Transactions menu to the WordPress admin menu.
 */
function laqira_transactions_menu()
{
    $hook = add_menu_page('Woo Laqira Transactions', 'Woo Laqira Transactions', 'manage_options', 'woo-laqirapay', 'woo_laqira_transactions_page');
    add_action("load-$hook", array('woo_Laqira_Transactions_List', 'screen_options'));
}

/**
 * Display the WooLaqiraPay Transactions page.
 */

$WooLaqiraTransactionsList = new Woo_Laqira_Transactions_List();
?>
<div class="wrap">
    <h2>WooLaqiraPay Transactions</h2>
    <form method="get" action="admin.php">
        <input type="hidden" name="page" value="woo-laqirapay">
        <p class="search-box">
            <label class="screen-reader-text" for="post-search-input">Search :</label>
            <input type="search" id="post-search-input" name="s" value="">
            <input type="submit" id="search-submit" class="button" value="Search">
        </p>
    </form>
    <?php

    $WooLaqiraTransactionsList->prepare_items();
    $WooLaqiraTransactionsList->display();
    ?>
</div>
<?php


/**
 * Set the screen option.
 *
 * @param mixed $status The current status.
 * @param string $option The option name.
 * @param mixed $value The option value.
 * @return mixed The status.
 */
function set_screen_option($status, $option, $value)
{
    if ('transactions_per_page' == $option) {
        return $value;
    }
    return $status;
}


// Add the WooLaqiraPay Transactions menu
//add_action('admin_menu', 'laqira_transactions_menu');
// Set screen option
add_filter('set-screen-option', 'set_screen_option', 10, 3);
