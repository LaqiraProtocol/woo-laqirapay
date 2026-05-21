<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<?php if ( isset( $data['message'] ) ) {
	echo esc_html( $data['message'] );
	return;
}

if ( ! empty( $data['valid'] ) ) {
	$loading_src = esc_url( LAQIRA_PLUGINS_URL . 'assets/img/loading.svg' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- View template local variable.
	echo '<div id="LaqirapayApp"></div>';
	echo '<div id="startLaqiraPaymentsApp"></div>';
	echo '<div style="text-align:center;" id="laqira_loder"><img class="loading" width="24px" height="24px" src="' . esc_url( $loading_src ) . '" alt=""></div>';
	return;
}

$errors = array();
if ( isset( $data['errors'] ) && is_array( $data['errors'] ) ) {
	$errors = array_filter( $data['errors'] );
}

echo '<div id="LaqirapayApp" style="text-align:center;padding-top: 20px;">';

echo '<p>' . esc_html__( 'LaqiraPayments is temporarily unavailable. Please choose another payment method.', 'laqira-payments' ) . '</p>';

if ( ! empty( $errors ) ) {
	echo '<ul style="list-style: none; margin: 0; padding: 0;">';
	foreach ( $errors as $message ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- View template loop variable.
		echo '<li>' . esc_html( $message ) . '</li>';
	}
	echo '</ul>';
}

echo '</div>';
