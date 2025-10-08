<?php
/**
 * Transactions list table implementation.
 *
 * @package LaqiraPay\Admin
 */

namespace LaqiraPay\Admin;

use LaqiraPay\Helpers\TransactionDetailsRenderer;
use WP_List_Table;
use wpdb;
use function laqirapay_filter_input;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Admin list table for displaying transactions.
 */
/**
 * Admin list table for displaying LaqiraPay transactions.
 */
class TransactionsListTable extends WP_List_Table {

	/**
	 * WordPress database accessor.
	 *
	 * @var wpdb
	 */
	private wpdb $db;

	/**
	 * Fully-qualified name for the transactions table.
	 *
	 * @var string
	 */
	private string $table;

	/**
	 * Initialise the list table configuration.
	 */
	public function __construct() {
		global $wpdb;
		$this->db    = $wpdb;
		$this->table = $wpdb->prefix . 'laqirapay_transactions';

		parent::__construct(
			array(
				'singular' => 'transaction',
				'plural'   => 'transactions',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Retrieve transactions from the database.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function get_data(): array {
		$search_input = laqirapay_filter_input( INPUT_GET, 's' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! is_string( $search_input ) ) {
			$search_input = '';
		}

		$search            = sanitize_text_field( wp_unslash( $search_input ) );
		$columns_to_search = array( 'wc_order_id', 'tx_from', 'tx_hash' );
		$search_conditions = array();
		$search_values     = array();

		if ( '' !== $search ) {
			$search_like = '%' . $this->db->esc_like( $search ) . '%';

			foreach ( $columns_to_search as $column ) {
				$search_conditions[] = sprintf( '%s LIKE %%s', $column );
				$search_values[]     = $search_like;
			}
		}

		$allowed_orderby    = array(
			'wc_order_id'    => 'wc_order_id',
			'wc_total_price' => 'wc_total_price',
			'tx_hash'        => 'tx_hash',
			'tx_from'        => 'tx_from',
		);
		$allowed_directions = array(
			'ASC'  => 'ASC',
			'DESC' => 'DESC',
		);

		$orderby = $allowed_orderby['wc_order_id'];
		$order   = 'ASC';

		$orderby_input = laqirapay_filter_input( INPUT_GET, 'orderby' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( is_string( $orderby_input ) ) {
			$requested_orderby = sanitize_text_field( wp_unslash( $orderby_input ) );
			if ( isset( $allowed_orderby[ $requested_orderby ] ) ) {
				$orderby = $allowed_orderby[ $requested_orderby ];
			}
		}

		$order_input = laqirapay_filter_input( INPUT_GET, 'order' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( is_string( $order_input ) ) {
			$requested_order = strtoupper( sanitize_text_field( wp_unslash( $order_input ) ) );
			if ( isset( $allowed_directions[ $requested_order ] ) ) {
				$order = $allowed_directions[ $requested_order ];
			}
		}

		$query = "SELECT * FROM {$this->table}";
		if ( array() !== $search_conditions ) {
			$where_clause = implode( ' OR ', $search_conditions );
			$query       .= $this->db->prepare(
				" WHERE {$where_clause}",
				...$search_values
			);
		}
		$query .= sprintf( ' ORDER BY %s %s', $orderby, $order );

		return $this->db->get_results( $query, ARRAY_A );
	}

	/**
	 * Prepare items for rendering within the table.
	 */
	public function prepare_items(): void {
		$columns               = $this->get_columns();
		$hidden                = $this->get_hidden_columns();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$data                  = $this->get_data();

		$per_page     = $this->get_items_per_page( 'transactions_per_page', 10 );
		$current_page = $this->get_pagenum();
		$total_items  = count( $data );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		usort( $data, array( $this, 'sort_data' ) );
		$data_slice  = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->items = $data_slice;
	}

	/**
	 * Retrieve visible columns.
	 *
	 * @return array<string,string>
	 */
	public function get_columns(): array {
		return array(
			'wc_order_id'    => esc_html__( 'Order ID', 'laqirapay' ),
			'wc_total_price' => esc_html__( 'Total Price', 'laqirapay' ),
			'wc_currency'    => esc_html__( 'Currency', 'laqirapay' ),
			'exchange_rate'  => esc_html__( 'Exchange Rate', 'laqirapay' ),
			'token_name'     => esc_html__( 'Paid By', 'laqirapay' ),
			'token_amount'   => esc_html__( 'Token Amount', 'laqirapay' ),
			'tx_hash'        => esc_html__( 'Transaction Hash', 'laqirapay' ),
			'tx_from'        => esc_html__( 'From', 'laqirapay' ),
		);
	}

	/**
	 * Retrieve hidden columns for the current screen.
	 *
	 * @return array<int,string>
	 */
	public function get_hidden_columns(): array {
		$screen = get_current_screen();
		if ( $screen ) {
			return get_hidden_columns( $screen );
		}
		return array();
	}

	/**
	 * Retrieve sortable columns.
	 *
	 * @return array<string,array{0:string,1:bool}>
	 */
	public function get_sortable_columns(): array {
		return array(
			'wc_order_id'    => array( 'wc_order_id', false ),
			'wc_total_price' => array( 'wc_total_price', false ),
			'tx_hash'        => array( 'tx_hash', false ),
			'tx_from'        => array( 'tx_from', false ),
		);
	}

	/**
	 * Render a column when a dedicated method is not available.
	 *
	 * @param array<string,mixed> $item        Row data.
	 * @param string              $column_name Column identifier.
	 * @return string
	 */
	protected function column_default( $item, $column_name ): string {
		$value = $item[ $column_name ] ?? '';

		switch ( $column_name ) {
			case 'wc_total_price':
			case 'wc_currency':
			case 'exchange_rate':
			case 'token_name':
			case 'token_amount':
			case 'req_hash':
			case 'tx_from':
				return esc_html( (string) $value );
			case 'wc_order_id':
				$order_id  = (string) $value;
				$order_url = add_query_arg(
					array(
						'post'   => $order_id,
						'action' => 'edit',
					),
					admin_url( 'post.php' )
				);

				return sprintf(
					'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
					esc_url( $order_url ),
					esc_html( $order_id )
				);
			case 'tx_hash':
				$hash = (string) $value;
				if ( '' === $hash ) {
					return '';
				}

				$order        = wc_get_order( $item['wc_order_id'] );
				$explorer_url = '';

				if ( $order ) {
					$explorer_url = TransactionDetailsRenderer::buildExplorerUrl(
						$order->get_meta( 'network_explorer' ),
						$hash
					);
				}

				if ( '' === $explorer_url ) {
					$explorer_url = TransactionDetailsRenderer::buildExplorerUrl( null, $hash );
				}

				return sprintf(
					'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
					esc_url( $explorer_url ),
					esc_html( $hash )
				);
			default:
				return esc_html(
					wp_json_encode(
						$item,
						JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
					)
				);
		}
	}

	/**
	 * Sort callback for transaction data.
	 *
	 * @param array<string,string> $a First row.
	 * @param array<string,string> $b Second row.
	 * @return int
	 */
	private function sort_data( $a, $b ): int {
		$orderby         = 'wc_order_id';
		$order           = 'asc';
		$allowed_columns = array_keys( $this->get_sortable_columns() );

		if ( ! empty( $_GET['orderby'] ) ) {
			$requested_orderby = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
			if ( in_array( $requested_orderby, $allowed_columns, true ) ) {
				$orderby = $requested_orderby;
			}
		}

		if ( ! empty( $_GET['order'] ) ) {
			$requested_order = sanitize_text_field( wp_unslash( $_GET['order'] ) );
			if ( in_array( strtolower( $requested_order ), array( 'asc', 'desc' ), true ) ) {
				$order = strtolower( $requested_order );
			}
		}

		$result = strcmp(
			(string) ( $a[ $orderby ] ?? '' ),
			(string) ( $b[ $orderby ] ?? '' )
		);

		return 'asc' === $order ? $result : -$result;
	}
}
