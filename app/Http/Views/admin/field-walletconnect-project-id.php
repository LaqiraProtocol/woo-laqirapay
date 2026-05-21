<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<input type="text" name="laqira_payments_walletconnect_project_id" size="60" value="<?php echo esc_attr( $data['value'] ?? '' ); ?>" /><br>
<p>Your Project ID gives you access to WalletConnect Cloud. <a href="https://cloud.walletconnect.com/" target="_blank" rel="noopener noreferrer">How to get it</a></p>
