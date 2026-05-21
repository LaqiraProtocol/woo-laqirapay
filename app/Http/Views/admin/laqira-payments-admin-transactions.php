<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<?php use LaqiraPayments\Admin\TransactionsListTable;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

$list_table = new TransactionsListTable(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- View template local variable.
$list_table->prepare_items();
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'LaqiraPayments Transactions', 'laqira-payments' ); ?></h1>
	<form method="get">
		<input type="hidden" name="page" value="laqira-payments" />
		<?php
		$list_table->search_box( esc_html__( 'Search Transactions', 'laqira-payments' ), 'laqira-payments-transactions' );
		$list_table->display();
		?>
	</form>
</div>
