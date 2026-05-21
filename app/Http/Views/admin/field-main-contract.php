<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<input type="text" name="laqira_payments_main_contract" required size="60" value="<?php echo esc_attr( strtolower( $data['value'] ?? '' ) ); ?>" /><br>
