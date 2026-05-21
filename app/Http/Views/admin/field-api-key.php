<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<input type="text" name="laqira_payments_api_key" size="60" value="<?php echo esc_attr( strtolower( $data['value'] ?? '' ) ); ?>" /><br>

<?php
$networks        = $data['networks'] ?? array(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- View template local variable.
$is_config_ready = isset( $data['is_config_ready'] ) ? (bool) $data['is_config_ready'] : true; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- View template local variable.

if ( ! $is_config_ready ) {
	echo '<small>' .
		esc_html__(
			'Complete the contract address, RPC URL, and API key, then save settings to load available networks.',
			'laqira-payments'
		) .
		'</small><br>';
} elseif ( empty( $networks ) ) {
	echo '<small>' .
		esc_html__( 'There was a problem retrieving networks; please check your internet connection and SSL.', 'laqira-payments' ) .
		'</small><br>';
} else {
	foreach ( $networks as $network ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- View template loop variable.
		echo '<small>' . esc_html( $network['message'] ) . '</small><br>';
	}
}
?>

<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=laqira_payments_clear_web3_cache' ), 'laqira_payments_clear_web3_cache' ) ); ?>" class="button"><?php esc_html_e( 'Clear Web3 Cache', 'laqira-payments' ); ?></a>
