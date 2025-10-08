<?php
$current_currency = isset( $data['current_currency'] ) ? (string) $data['current_currency'] : '';
$saved_rate       = isset( $data['saved_rate'] ) ? (string) $data['saved_rate'] : '';
$nonce_field      = isset( $data['nonce_field'] ) ? $data['nonce_field'] : '';
$option_name      = isset( $data['option_name'] ) ? (string) $data['option_name'] : '';
?>
<?php if ( 'USD' === $current_currency ) : ?>
<p><?php esc_html_e( 'The current store currency is USD and no exchange rate is needed.', 'laqirapay' ); ?></p>
<?php else : ?>
<p>
								<?php
								printf(
												/* translators: %s: Store currency code. */
									esc_html__( 'The current store currency is: %s', 'laqirapay' ),
									esc_html( $current_currency )
								);
								?>
</p>
<label for="<?php echo esc_attr( $option_name ); ?>"><?php esc_html_e( 'Exchange rate to USD:', 'laqirapay' ); ?></label>
<input type="number" step="any" inputmode="decimal" id="<?php echo esc_attr( $option_name ); ?>" name="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $saved_rate ); ?>" required>
	<?php if ( ! empty( $nonce_field ) ) : ?>
		<?php
		echo wp_kses(
			$nonce_field,
			array(
				'input' => array(
					'type'  => true,
					'id'    => true,
					'name'  => true,
					'value' => true,
				),
			)
		);
		?>
<?php endif; ?>
<?php endif; ?>
