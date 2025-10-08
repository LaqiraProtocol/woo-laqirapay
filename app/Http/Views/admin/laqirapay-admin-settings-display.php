<?php
/**
 * LaqiraPay Admin Settings Display
 *
 * This file renders the admin settings page for the LaqiraPay plugin, including tabs for General, Exchange Rate, and Order Recovery settings.
 *
 * @package LaqiraPay
 * @since   1.0.0
 */

use LaqiraPay\Helpers\FileHelper;
use LaqiraPay\Http\Controllers\Admin\SettingsController;
use LaqiraPay\Support\Requirements;

/**
 * Template variables for rendering the settings page.
 *
 * @var string $order_recovery_settings_output The HTML output for order recovery settings.
 * @var string $order_recovery_content         The HTML content for order recovery shortcode.
 * @var array  $order_recovery_allowed_tags    The allowed HTML tags for sanitizing order recovery content.
 */

$order_recovery_settings_output = $order_recovery_settings_output ?? '';
$order_recovery_content         = $order_recovery_content ?? '';
$order_recovery_allowed_tags    = is_array( $order_recovery_allowed_tags ?? null ) ? $order_recovery_allowed_tags : array();
?>

<!-- This file primarily consists of HTML with embedded PHP for rendering the LaqiraPay admin settings page. -->
<div class="wrap">
	<div id="icon-themes" class="icon32"></div>
	<h2><?php esc_html_e( 'LaqiraPay Settings', 'laqirapay' ); ?></h2>
	<?php
	if ( current_user_can( 'manage_options' ) ) {
		if ( Requirements::check() ) {
			settings_errors();

			if ( ! is_ssl() ) {
				?>
				<div class="notice notice-warning"><p>
					<?php esc_html_e( 'SSL is not enabled on the site; therefore Web3 functions cannot receive network data.', 'laqirapay' ); ?>
				</p></div>
				<?php
			}

			$abi = json_decode(
				FileHelper::get_contents_secure( LAQIRA_PLUGINS_URL . 'assets/json/cidAbi.json' ),
				true
			);
			if ( empty( $abi ) ) {
				?>
				<div class="notice notice-warning"><p>
					<?php esc_html_e( 'ABI files not found or their JSON structure is invalid; network data cannot be received. This may also be due to an SSL failure', 'laqirapay' ); ?>
				</p></div>
				<?php
			}

			$default_tab  = SettingsController::SECTION_GENERAL;
			$current_tab  = get_option( 'laqirapay_current_tab_setting', $default_tab );
			$allowed_tabs = array(
				SettingsController::SECTION_GENERAL,
				SettingsController::SECTION_EXCHANGE_RATE,
				SettingsController::SECTION_ORDER_RECOVERY,
			);

			if ( ! in_array( $current_tab, $allowed_tabs, true ) ) {
				$current_tab = $default_tab;
			}
			?>
			<div id="laqirapay_admin_wrapper">
				<div id="laqirapay_admin_menu" class="ui labeled stackable large vertical menu attached">
					<a class="green item <?php echo esc_attr( SettingsController::SECTION_GENERAL === $current_tab ? 'active' : '' ); ?>" data-tab="<?php echo esc_attr( SettingsController::SECTION_GENERAL ); ?>">
						<i class="settings icon"></i>
						<div class="header"><?php esc_html_e( 'Main Settings', 'laqirapay' ); ?></div>
						<span class="laqirapay_menu_description"><?php esc_html_e( 'Configure LaqiraPay', 'laqirapay' ); ?></span>
					</a>
					<a class="green item <?php echo esc_attr( SettingsController::SECTION_EXCHANGE_RATE === $current_tab ? 'active' : '' ); ?>" data-tab="<?php echo esc_attr( SettingsController::SECTION_EXCHANGE_RATE ); ?>">
						<i class="chart line icon"></i>
						<div class="header"><?php esc_html_e( 'Exchange Rate', 'laqirapay' ); ?></div>
						<span class="laqirapay_menu_description"><?php esc_html_e( 'Manage conversion settings', 'laqirapay' ); ?></span>
					</a>
					<a class="green item <?php echo esc_attr( SettingsController::SECTION_ORDER_RECOVERY === $current_tab ? 'active' : '' ); ?>" data-tab="<?php echo esc_attr( SettingsController::SECTION_ORDER_RECOVERY ); ?>">
						<i class="redo icon"></i>
						<div class="header"><?php esc_html_e( 'Order Recovery', 'laqirapay' ); ?></div>
						<span class="laqirapay_menu_description"><?php esc_html_e( 'Control recovery behaviour', 'laqirapay' ); ?></span>
					</a>
				</div>
				<div id="laqirapay_tabs_wrapper">
					<div class="ui bottom attached tab segment <?php echo esc_attr( SettingsController::SECTION_GENERAL === $current_tab ? 'active' : '' ); ?>" data-tab="<?php echo esc_attr( SettingsController::SECTION_GENERAL ); ?>">
						<form method="POST" action="options.php" class="laqirapay-admin-form laqirapay-admin-form--general" data-tab="<?php echo esc_attr( SettingsController::SECTION_GENERAL ); ?>">
							<?php settings_fields( 'laqirapay_general_options' ); ?>
							<input type="hidden" name="laqirapay_current_tab_setting" class="laqirapay_current_tab_setting_input" value="<?php echo esc_attr( SettingsController::SECTION_GENERAL ); ?>">
							<div class="laqirapay_attached_content_wrapper">
								<h2 class="ui block header">
									<i class="settings icon"></i>
									<div class="content">
										<?php esc_html_e( 'Main Settings', 'laqirapay' ); ?>
										<div class="sub header"><?php esc_html_e( 'Configure LaqiraPay', 'laqirapay' ); ?></div>
									</div>
								</h2>
								<table class="form-table">
									<?php do_settings_fields( 'laqirapay-settings', SettingsController::SECTION_GENERAL ); ?>
								</table>
								<div class="ui hidden divider"></div>
								<button type="submit" name="submit" class="ui primary button"><?php esc_html_e( 'Save Settings', 'laqirapay' ); ?></button>
							</div>
						</form>
					</div>
					<div class="ui bottom attached tab segment <?php echo esc_attr( SettingsController::SECTION_EXCHANGE_RATE === $current_tab ? 'active' : '' ); ?>" data-tab="<?php echo esc_attr( SettingsController::SECTION_EXCHANGE_RATE ); ?>">
						<form method="POST" action="options.php" class="laqirapay-admin-form laqirapay-admin-form--exchange-rate" data-tab="<?php echo esc_attr( SettingsController::SECTION_EXCHANGE_RATE ); ?>">
							<?php settings_fields( 'laqirapay_exchange_rate_options' ); ?>
							<input type="hidden" name="laqirapay_current_tab_setting" class="laqirapay_current_tab_setting_input" value="<?php echo esc_attr( SettingsController::SECTION_EXCHANGE_RATE ); ?>">
							<div class="laqirapay_attached_content_wrapper">
								<h2 class="ui block header">
									<i class="chart line icon"></i>
									<div class="content">
										<?php esc_html_e( 'Exchange Rate', 'laqirapay' ); ?>
										<div class="sub header"><?php esc_html_e( 'Manage conversion settings', 'laqirapay' ); ?></div>
									</div>
								</h2>
								<table class="form-table">
									<?php do_settings_fields( 'laqirapay-settings', SettingsController::SECTION_EXCHANGE_RATE ); ?>
								</table>
								<div class="ui hidden divider"></div>
								<button type="submit" name="submit" class="ui primary button"><?php esc_html_e( 'Save Settings', 'laqirapay' ); ?></button>
							</div>
						</form>
					</div>
					<div class="ui bottom attached tab segment <?php echo esc_attr( SettingsController::SECTION_ORDER_RECOVERY === $current_tab ? 'active' : '' ); ?>" data-tab="<?php echo esc_attr( SettingsController::SECTION_ORDER_RECOVERY ); ?>">
						<form method="POST" action="options.php" class="laqirapay-admin-form laqirapay-admin-form--order-recovery" data-tab="<?php echo esc_attr( SettingsController::SECTION_ORDER_RECOVERY ); ?>">
							<?php settings_fields( 'laqirapay_general_options' ); ?>
							<input type="hidden" name="laqirapay_current_tab_setting" class="laqirapay_current_tab_setting_input" value="<?php echo esc_attr( SettingsController::SECTION_ORDER_RECOVERY ); ?>">
							<div class="laqirapay_attached_content_wrapper">
								<h2 class="ui block header">
									<i class="redo icon"></i>
									<div class="content">
										<?php esc_html_e( 'Order Recovery', 'laqirapay' ); ?>
										<div class="sub header"><?php esc_html_e( 'Control recovery behaviour', 'laqirapay' ); ?></div>
									</div>
								</h2>
								<div class="info-box">
									<h3><?php esc_html_e( 'LaqiraPay Order Recovery by Transaction Hash', 'laqirapay' ); ?></h3>
									<p>
										<span class="dashicons dashicons-warning" aria-hidden="true"></span>
										<?php esc_html_e( 'In this section, you can view detailed information by entering a transaction hash. If a customer\'s order corresponds to this hash, the order details will be displayed along with the transaction information retrieved from the blockchain. If the transaction is marked as incomplete on the website, you can use the blockchain data to finalize the customer\'s order.', 'laqirapay' ); ?>
									</p>
								</div>

								<?php if ( ! empty( $order_recovery_settings_output ) ) : ?>
									<?php echo wp_kses( $order_recovery_settings_output, wp_kses_allowed_html( 'post' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php endif; ?>
								<table class="form-table">
									<?php do_settings_fields( 'laqirapay-settings', SettingsController::SECTION_ORDER_RECOVERY ); ?>
								</table>

								<div class="laqirapay-order-recovery-shortcode-anchor" data-laqirapay-order-recovery-anchor>
									<?php esc_html_e( 'The order recovery interface is displayed below this form.', 'laqirapay' ); ?>
								</div>
								<?php if ( ! empty( $order_recovery_content ) ) : ?>
									<div class="laqirapay-order-recovery-shortcode">
										<?php
										$allowed_tags = array(
											'div'    => array(
												'id'    => true,
												'class' => true,
											),
											'h4'     => array(),
											'form'   => array(
												'id'    => true,
												'class' => true,
											),
											'label'  => array(
												'for' => true,
											),
											'input'  => array(
												'type' => true,
												'id'   => true,
												'name' => true,
												'size' => true,
											),
											'button' => array(
												'class' => true,
												'type'  => true,
												'id'    => true,
											),
											'img'    => array(
												'class'  => true,
												'width'  => true,
												'height' => true,
												'src'    => true,
												'alt'    => true,
											),
											'p'      => array(
												'id' => true,
											),
										);

										$sanitized_content = wp_kses( $order_recovery_content, $allowed_tags );

										// Use echo to output sanitized content to comply with WordPress escaping standards
										echo $sanitized_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										?>
									</div>
								<?php endif; ?>
								<div class="ui hidden divider"></div>
							</div>
						</form>
					</div>
				</div>
			</div>
			<?php
		} else {
			?>
			<h2><?php esc_html_e( 'Plugin Requirements Not Met', 'laqirapay' ); ?></h2>
			<p><?php esc_html_e( 'Please check PHP >= 8.1, WordPress >= 6.3, WooCommerce >= 8.2', 'laqirapay' ); ?></p>
			<?php
		}
	} else {
		?>
		<h2><?php esc_html_e( 'Access Denied...', 'laqirapay' ); ?></h2>
		<p><?php esc_html_e( "You don't have right permission to this setting page", 'laqirapay' ); ?></p>
		<?php
	}
	?>
</div>