<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="ui toggle checkbox">
<input type="checkbox" name="laqira_payments_only_logged_in_user" value="1" <?php echo esc_attr( $data['checked'] ?? '' ); ?> />
	<label></label>
</div>


