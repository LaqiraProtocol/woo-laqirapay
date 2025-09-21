<?php
/**
 * Admin Order Recovery View
 *
 * @var string $settings_output
 * @var string $content
 */

defined('ABSPATH') || exit;

echo '<div class="wrap">';
echo '<div class="info-box">';
echo '<h3>' . esc_html__( 'LaqiraPay Order Recovery by Transaction Hash', 'laqirapay' ) . '</h3>';
echo '<p><span class="dashicons dashicons-warning"></span>' .
    esc_html__( 'In this section, you can view detailed information by entering a transaction hash. If a customer\'s order corresponds to this hash, the order details will be displayed along with the transaction information retrieved from the blockchain. If the transaction is marked as incomplete on the website, you can use the blockchain data to finalize the customer\'s order.', 'laqirapay' ) .
    '</p>';
echo '</div>';
echo '<hr>';
echo $settings_output;
echo $content;
echo '</div>';

