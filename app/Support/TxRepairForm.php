<?php

namespace LaqiraPay\Support;

class TxRepairForm {

	/**
	 * Enqueue shared assets for both Frontend and Admin, based on context.
	 *
	 * @param string $context 'repair' | 'admin' | 'view' (default)
	 */
	public static function enqueue_assets( $context ) {
			// Style
			wp_enqueue_style(
				'laqirapay-tx-form',
				LAQIRA_PLUGINS_URL . 'assets/public/css/tx-form.css',
				array(),
				LAQIRAPAY_VERSION
			);

		// Script handle & file by context
		$script_file = ( $context === 'repair' ) ? 'tx-repair.js' : 'tx-view.js';
		$handle      = ( $context === 'repair' ) ? 'laqirapay-tx-repair' : 'laqirapay-tx-view';

		// Script
			wp_enqueue_script(
				$handle,
				LAQIRA_PLUGINS_URL . 'assets/public/js/' . $script_file,
				array( 'jquery' ),
				LAQIRAPAY_VERSION,
				true
			);

		if ( in_array( $context, array( 'admin', 'view' ), true ) ) {
				wp_enqueue_script(
					'laqirapay-tx-admin',
					LAQIRA_PLUGINS_URL . 'assets/admin/js/tx-admin.js',
					array( 'jquery', $handle ),
					LAQIRAPAY_VERSION,
					true
				);

				wp_localize_script(
					'laqirapay-tx-admin',
					'laqiraTxAdmin',
					array(
						'genericError'   => esc_html__( 'Unable to confirm the order. Please try again.', 'laqirapay' ),
						'successMessage' => esc_html__( 'Order confirmation completed successfully.', 'laqirapay' ),
					)
				);
		}
	}

	/**
	 * Render the form and localize data. Ensures assets are enqueued
	 * both in Frontend and Admin by calling enqueue_assets() here.
	 *
	 * @param string   $context  'repair' | 'admin' | 'view'(default)
	 * @param int|null $order_id Optional Woo order id
	 * @return string  HTML
	 */
	public static function render_form( $context, $order_id = null ) {
		// ✅ Ensure assets are always loaded (Frontend + Admin)
		self::enqueue_assets( $context );

		$handle          = ( $context === 'repair' ) ? 'laqirapay-tx-repair' : 'laqirapay-tx-view';
		$button_text     = '';
		$include_loading = false;
		$description     = '';
		$input_size      = '';
		$wrap_div        = false;

		switch ( $context ) {
			case 'repair':
				wp_localize_script(
					$handle,
					'laqiraTxRepair',
					array(
						'ajax_url'   => admin_url( 'admin-ajax.php' ),
						'action'     => 'laqirapay_confirm_tx_hash_in_user_panel',
						'order_id'   => $order_id,
						'nonce'      => wp_create_nonce( 'laqira_nonce_confirm_tx_hash_in_user_panel' ),
						'retry_text' => esc_html__( 'Try Again ...', 'laqirapay' ),
					)
				);
				$button_text = esc_html__( 'Repair Order', 'laqirapay' );
				$description = esc_html__( 'This order was created with the LaqiraPay payment gateway and its status is currently incomplete.If your transaction has been completed on the blockchain and you have the successful transaction hash,please enter the transaction hash and click the Repair Order button to repair and rebuild your order.', 'laqirapay' );
				$wrap_div    = true;
				break;

			case 'admin':
					wp_localize_script(
						$handle,
						'laqiraTxView',
						array(
							'ajax_url'       => admin_url( 'admin-ajax.php' ),
							'action'         => 'laqirapay_view_confirmation_tx_hash_admin',
							'order_id'       => $order_id,
							'nonce'          => wp_create_nonce( 'laqirapay_view_confirmation_tx_hash_admin' ),
							'error_required' => esc_html__( 'Please enter a transaction hash.', 'laqirapay' ),
							'error_generic'  => esc_html__( 'Unable to retrieve transaction details.', 'laqirapay' ),
						)
					);
					$button_text     = esc_html__( 'View Transaction Detail', 'laqirapay' );
					$include_loading = true;
				break;

			default:
								wp_localize_script(
									$handle,
									'laqiraTxView',
									array(
										'ajax_url'       => admin_url( 'admin-ajax.php' ),
										'action'         => 'laqirapay_view_confirmation_tx_hash',
										'nonce'          => wp_create_nonce( 'laqirapay_view_confirmation_tx_hash' ),
										'error_required' => esc_html__( 'Please enter a transaction hash.', 'laqirapay' ),
										'error_generic'  => esc_html__( 'Unable to retrieve transaction details.', 'laqirapay' ),
									)
								);
								$button_text     = esc_html__( 'View Transaction Detail', 'laqirapay' );
								$include_loading = true;
								$input_size      = '80';
				break;
		}

		ob_start();

		if ( $wrap_div ) {
			echo '<div id="user-confirmation-tx-section">';
		}

		if ( $description ) {
			echo '<h4>' . esc_html( $description ) . '</h4>';
		}
		?>
		<form id="laqirapay-tx-confirm-form" class="laqirapay-tx-form">
			<label for="tx-hash-input"><?php esc_html_e( 'Please enter your Transaction hash:', 'laqirapay' ); ?></label>
			<input type="text" id="tx-hash-input" name="tx_hash_input" <?php echo $input_size ? 'size="' . esc_attr( $input_size ) . '"' : ''; ?> />
			<button class="button save_order button-primary" type="button" id="verify-button"><?php echo esc_html( $button_text ); ?></button>

			<?php if ( $include_loading ) : ?>
				<div id="loading-indicator">
					<img class="loading" width="24" height="24"
						src="<?php echo esc_url( LAQIRA_PLUGINS_URL . 'assets/img/loading.svg' ); ?>"
						alt="<?php esc_attr_e( 'loading', 'laqirapay' ); ?>" />
				</div>
			<?php else : ?>
				<p id="confirmation_result_area"></p>
			<?php endif; ?>
		</form>

		<div id="laqirapay-confirmation-table"></div>
		<?php

		if ( $wrap_div ) {
			echo '</div>';
		}

		return ob_get_clean();
	}
}
