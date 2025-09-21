<?php
/**
 * Admin Currency Rates View
 *
 * @var string $current_currency
 * @var string $saved_rate
 * @var string $nonce_field
 * @var string $message
 */

defined('ABSPATH') || exit;

echo '<div class="wrap">';
echo '<h1>' . esc_html__( 'Currency Exchange Rate', 'laqirapay' ) . '</h1>';

if ($current_currency !== 'USD') {
    if ($message) {
        echo '<div class="updated"><p>' . esc_html($message) . '</p></div>';
    }
    echo '<p>' . sprintf(
        esc_html__( 'The current store currency is: %s', 'laqirapay' ),
        esc_html($current_currency)
    ) . '</p>';
    echo '<form method="post" action="">';
    echo $nonce_field;
    echo '<label for="laqirapay_exchange_rate">' . esc_html__( 'Exchange rate to USD:', 'laqirapay' ) . '</label>';
    echo '<input type="text" id="laqirapay_exchange_rate" name="laqirapay_exchange_rate" value="' . esc_attr($saved_rate) . '" required>';
    echo '<input type="submit" value="' . esc_attr__( 'Save', 'laqirapay' ) . '" class="button button-primary">';
    echo '</form>';
} else {
    echo '<p>' . esc_html__( 'The current store currency is USD and no exchange rate is needed.', 'laqirapay' ) . '</p>';
}

echo '</div>';

