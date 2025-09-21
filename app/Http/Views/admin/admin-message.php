<?php
/**
 * Simple admin message view.
 *
 * @var string $title
 * @var string $message
 */

defined('ABSPATH') || exit;

echo '<div class="wrap">';
echo '<h2>' . esc_html($title) . '</h2>';
echo '<p>' . esc_html($message) . '</p>';
echo '</div>';
