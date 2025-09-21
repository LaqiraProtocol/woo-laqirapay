<input type="text" name="laqirapay_api_key" size="60" value="<?php echo esc_attr(strtolower($data['value'] ?? '')); ?>" /><br>

<?php
$networks = $data['networks'] ?? [];

if (empty($networks)) {
    echo '<small>' .
        esc_html__('There was a problem retrieving networks; please check your internet connection and SSL.', 'laqirapay') .
        '</small><br>';
} else {
    foreach ($networks as $network) {
        echo '<small>' . esc_html($network['message']) . '</small><br>';
    }
}
?>

<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=laqirapay_clear_web3_cache'), 'laqirapay_clear_web3_cache')); ?>" class="button"><?php esc_html_e('Clear Web3 Cache', 'laqirapay'); ?></a>