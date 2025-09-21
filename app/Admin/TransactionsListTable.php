<?php

namespace LaqiraPay\Admin;

use LaqiraPay\Helpers\TransactionDetailsRenderer;
use WP_List_Table;
use wpdb;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Admin list table for displaying transactions.
 */
class TransactionsListTable extends WP_List_Table
{
    private wpdb $db;
    private string $table;

    public function __construct()
    {
        global $wpdb;
        $this->db    = $wpdb;
        $this->table = $wpdb->prefix . 'laqirapay_transactions';

        parent::__construct([
            'singular' => 'transaction',
            'plural'   => 'transactions',
            'ajax'     => false,
        ]);
    }

    private function get_data(): array
    {
        $search = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : false;
        $search_condition = '%' . $this->db->esc_like($search) . '%';
        $columns_to_search = ['wc_order_id', 'tx_from', 'tx_hash'];
        $column_conditions = [];
        foreach ($columns_to_search as $column) {
            $column_conditions[] = "$column LIKE %s";
        }
        $search_condition_combined = implode(' OR ', $column_conditions);

        $orderby = 'wc_order_id';
        $order   = 'ASC';
        $allowed_columns = array_keys($this->get_sortable_columns());

        if (!empty($_GET['orderby'])) {
            $requested_orderby = sanitize_text_field(wp_unslash($_GET['orderby']));
            if (in_array($requested_orderby, $allowed_columns, true)) {
                $orderby = $requested_orderby;
            }
        }

        if (!empty($_GET['order'])) {
            $requested_order = sanitize_text_field(wp_unslash($_GET['order']));
            if (in_array(strtolower($requested_order), ['asc', 'desc'], true)) {
                $order = strtoupper($requested_order);
            }
        }

        $query = "SELECT * FROM {$this->table}";
        if ($search) {
            $query .= $this->db->prepare(
                " WHERE $search_condition_combined",
                $search_condition,
                $search_condition,
                $search_condition
            );
        }
        $query .= " ORDER BY $orderby $order";

        return $this->db->get_results($query, ARRAY_A);
    }

    public function prepare_items(): void
    {
        $columns  = $this->get_columns();
        $hidden   = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];
        $data = $this->get_data();

        $per_page     = $this->get_items_per_page('transactions_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items  = count($data);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);

        usort($data, [$this, 'sort_data']);
        $data_slice   = array_slice($data, (($current_page - 1) * $per_page), $per_page);
        $this->items  = $data_slice;
    }

    public function get_columns(): array
    {
        return [
            'wc_order_id'    => esc_html__('Order ID', 'laqirapay'),
            'wc_total_price' => esc_html__('Total Price', 'laqirapay'),
            'wc_currency'    => esc_html__('Currency', 'laqirapay'),
            'exchange_rate'  => esc_html__('Exchange Rate', 'laqirapay'),
            'token_name'     => esc_html__('Paid By', 'laqirapay'),
            'token_amount'   => esc_html__('Token Amount', 'laqirapay'),
            'tx_hash'        => esc_html__('Transaction Hash', 'laqirapay'),
            'tx_from'        => esc_html__('From', 'laqirapay'),
        ];
    }

    public function get_hidden_columns(): array
    {
        $screen = get_current_screen();
        if ($screen) {
            return get_hidden_columns($screen);
        }
        return [];
    }

    public function get_sortable_columns(): array
    {
        return [
            'wc_order_id'    => ['wc_order_id', false],
            'wc_total_price' => ['wc_total_price', false],
            'tx_hash'        => ['tx_hash', false],
            'tx_from'        => ['tx_from', false],
        ];
    }

    protected function column_default($item, $column_name)
    {
        $value = $item[$column_name] ?? '';

        switch ($column_name) {
            case 'wc_total_price':
            case 'wc_currency':
            case 'exchange_rate':
            case 'token_name':
            case 'token_amount':
            case 'req_hash':
            case 'tx_from':
                return esc_html((string) $value);
            case 'wc_order_id':
                $orderId = (string) $value;
                $orderUrl = add_query_arg(
                    [
                        'post'   => $orderId,
                        'action' => 'edit',
                    ],
                    admin_url('post.php')
                );

                return sprintf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                    esc_url($orderUrl),
                    esc_html($orderId)
                );
            case 'tx_hash':
                $hash = (string) $value;
                if ($hash === '') {
                    return '';
                }

                $order = wc_get_order($item['wc_order_id']);
                $explorerUrl = '';

                if ($order) {
                    $explorerUrl = TransactionDetailsRenderer::buildExplorerUrl(
                        $order->get_meta('network_explorer'),
                        $hash
                    );
                }

                if ($explorerUrl === '') {
                    $explorerUrl = TransactionDetailsRenderer::buildExplorerUrl(null, $hash);
                }

                return sprintf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                    esc_url($explorerUrl),
                    esc_html($hash)
                );
            default:
                return esc_html(print_r($item, true));
        }
    }

    private function sort_data($a, $b)
    {
        $orderby = 'wc_order_id';
        $order   = 'asc';
        $allowed_columns = array_keys($this->get_sortable_columns());

        if (!empty($_GET['orderby'])) {
            $requested_orderby = sanitize_text_field(wp_unslash($_GET['orderby']));
            if (in_array($requested_orderby, $allowed_columns, true)) {
                $orderby = $requested_orderby;
            }
        }

        if (!empty($_GET['order'])) {
            $requested_order = sanitize_text_field(wp_unslash($_GET['order']));
            if (in_array(strtolower($requested_order), ['asc', 'desc'], true)) {
                $order = strtolower($requested_order);
            }
        }

        $result = strcmp($a[$orderby], $b[$orderby]);

        return 'asc' === $order ? $result : -$result;
    }
}
