<select name="laqirapay_order_recovery_status">
<?php foreach ( ( $data['order_statuses'] ?? array() ) as $status_slug => $status_name ) : ?>
	<option value="<?php echo esc_attr( $status_slug ); ?>" <?php echo esc_attr( $status_slug == ( $data['value'] ?? '' ) ? 'selected' : '' ); ?>><?php echo esc_html( $status_name ); ?></option>
<?php endforeach; ?>
</select>
