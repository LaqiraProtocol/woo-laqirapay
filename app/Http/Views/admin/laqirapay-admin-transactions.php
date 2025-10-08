<?php
use LaqiraPay\Admin\TransactionsListTable;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

$list_table = new TransactionsListTable();
$list_table->prepare_items();
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'LaqiraPay Transactions', 'laqirapay' ); ?></h1>
	<form method="get">
		<input type="hidden" name="page" value="laqirapay" />
		<?php
		$list_table->search_box( esc_html__( 'Search Transactions', 'laqirapay' ), 'laqirapay-transactions' );
		$list_table->display();
		?>
	</form>
</div>
