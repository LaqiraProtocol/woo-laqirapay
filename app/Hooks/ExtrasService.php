<?php

namespace LaqiraPay\Hooks;

use LaqiraPay\Helpers\JwtHelper;
use LaqiraPay\Helpers\TransactionDetailsRenderer;
use LaqiraPay\Helpers\WooCommerceHelper;
use LaqiraPay\Support\TxRepairForm;
use LaqiraPay\Domain\Services\LaqiraLogger;

class ExtrasService {
    private $wooCommerceService;
    private $jwtService;
    public function __construct(
        WooCommerceHelper $wooCommerceService,
        JwtHelper $jwtService
    ) {
        $this->wooCommerceService = $wooCommerceService;
        $this->jwtService = $jwtService;
    }

    public function register() {
        add_action('woocommerce_order_status_changed', [$this, 'custom_empty_cart_on_status_change'], 10, 4);
        add_action('woocommerce_thankyou', [$this, 'custom_display_order_data'], 9);
        add_action('add_meta_boxes', [$this, 'order_custom_metabox']);
        add_action('add_meta_boxes', [$this, 'recovery_order_custom_metabox']);
        add_action('woocommerce_order_details_after_order_table', [$this, 'add_tx_check_section_to_view_order'], 10, 1);
        add_shortcode('lqr_recovery', [$this, 'recovery_txHash_shortcode']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_tx_assets']);
        add_action('update_option_woocommerce_currency', [$this, 'check_wc_currency_with_rates_after_change']);
        add_action('admin_init', [$this, 'check_wc_currency_with_rates']);
        add_filter('woocommerce_email_order_meta_fields', [$this, 'woocommerce_email_order_meta_fields'], 10, 3);
//        LaqiraLogger::log(200, 'hooks', 'extras_service_registered');
    }

    public function custom_empty_cart_on_status_change($order_id, $old_status, $new_status, $order) {
        if ($old_status === 'pending') {
            $this->reset_cart_session($order);
            LaqiraLogger::log(200, 'hooks', 'cart_reset_on_status_change', [
                'order_id'   => $order_id,
                'old_status' => $old_status,
                'new_status' => $new_status,
            ]);
        }
    }

    public function custom_display_order_data($order_id) {
        $order = wc_get_order(intval($order_id));
        if ($order->get_payment_method() === 'WC_laqirapay') {
            $this->reset_cart_session($order);
            echo $this->render_tx_details($order);
            LaqiraLogger::log(200, 'hooks', 'display_order_data', ['order_id' => $order_id]);
        }
    }

    public function order_custom_metabox() {
        $screen = wc_get_page_screen_id('shop_order');
        if ($screen && 'woocommerce_page_wc-orders' === $screen) {
            $order_id = isset($_GET['id']) ? intval(sanitize_text_field(wp_unslash($_GET['id']))) : 0;
            $order = wc_get_order($order_id);
            if ($order && $order->get_payment_method() === 'WC_laqirapay') {
                add_meta_box('laqirapay_metabox', 'LaqiraPay Details', [$this, 'metabox_content'], $screen, 'advanced', 'high');
                if (!$order->get_meta('tx_hash')) {
                    add_meta_box('laqirapay_recovery_faild_metabox', 'LaqiraPay Recovery Faild Transaction', [$this, 'recovery_faild_transaction'], $screen, 'advanced', 'high');
                }
                LaqiraLogger::log(200, 'hooks', 'order_metabox_added', ['order_id' => $order_id]);
            }
        }
    }

    public function recovery_order_custom_metabox() {
        $screen = wc_get_page_screen_id('shop_order');
        if ($screen && 'woocommerce_page_wc-orders' === $screen) {
            $order_id = isset($_GET['id']) ? intval(sanitize_text_field(wp_unslash($_GET['id']))) : 0;
            $order = wc_get_order($order_id);
            if ($order && $order->get_payment_method() === 'WC_laqirapay') {
                add_meta_box('laqirapay_order_recovery_metabox', 'LaqiraPay Order Recovery', [$this, 'order_recovery_metabox_content'], $screen, 'side', 'high');
                LaqiraLogger::log(200, 'hooks', 'order_recovery_metabox_added', ['order_id' => $order_id]);
            }
        }
    }

    public function enqueue_admin_tx_assets($hook) {
        $screen = wc_get_page_screen_id('shop_order');
        if ($screen && $hook === $screen) {
            $order_id = isset($_GET['id']) ? intval(sanitize_text_field(wp_unslash($_GET['id']))) : 0;
            $order = wc_get_order($order_id);
            if ($order && $order->get_payment_method() === 'WC_laqirapay') {
                TxRepairForm::enqueue_assets('admin');
                LaqiraLogger::log(200, 'hooks', 'enqueue_admin_tx_assets', ['order_id' => $order_id]);
            }
        }
    }

    public function metabox_content($object) {
        $order = is_a($object, 'WP_Post') ? wc_get_order($object->ID) : $object;
        $paymentType = esc_html($order->get_meta('payment_type'));
        $walletAddress = esc_html($order->get_meta('CustomerWalletAddress'));
        $tokenAmount = esc_html($order->get_meta('TokenAmount'));
        $tokenName = esc_html($order->get_meta('TokenName'));
        $exchangeRate = esc_html($order->get_meta('exchange_rate'));

        $txHash = (string) $order->get_meta('tx_hash');
        $txHashText = esc_html($txHash);
        $explorerUrl = TransactionDetailsRenderer::buildExplorerUrl($order->get_meta('network_explorer'), $txHash);
        $transactionLink = $explorerUrl
            ? sprintf('<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url($explorerUrl), $txHashText)
            : $txHashText;

        echo '<p>Customer Selected Payment Type:<strong> ' . $paymentType . '</strong></p>';
        echo '<p>Customer Wallet Address:<strong> ' . $walletAddress . '</strong></p>';
        echo '<p>Token Amount:<strong> ' . $tokenAmount . ' ' . $tokenName . '</strong></p>';
        echo '<p>Exchange Rate:<strong> ' . $exchangeRate . '</strong></p>';
        echo '<p>Transaction Hash:<strong> ' . $transactionLink . '</strong></p>';
    }

    public function recovery_faild_transaction($object) {
        $order = is_a($object, 'WP_Post') ? wc_get_order($object->ID) : $object;
        $this->recovery_txHash_form_in_admin($order);
    }

    public function order_recovery_metabox_content($object) {
        $order = is_a($object, 'WP_Post') ? wc_get_order($object->ID) : $object;
        if ($order->get_meta('tx_hash')) {
            $this->view_confirmation_tx_hash_automation($order);
        }
    }

    public function add_tx_check_section_to_view_order($order_id) {
        $order = wc_get_order($order_id);
        if (
            $order->get_status() !== 'pending' ||
            $order->get_payment_method() !== 'WC_laqirapay' ||
            $order->get_meta('tx_status', true) !== 'failed'
        ) {
            return;
        }
        TxRepairForm::enqueue_assets('repair');
        echo TxRepairForm::render_form('repair', $order_id);
        LaqiraLogger::log(200, 'hooks', 'tx_repair_form_displayed', ['order_id' => $order_id]);
    }

    public function recovery_txHash_shortcode() {
        TxRepairForm::enqueue_assets('view');
        LaqiraLogger::log(200, 'hooks', 'recovery_shortcode_rendered');
        return TxRepairForm::render_form('view');
    }

    private function recovery_txHash_form_in_admin($order) {
        $order_id = $order->get_id();
        echo TxRepairForm::render_form('admin', $order_id);
    }

    private function view_confirmation_tx_hash_automation($order) {
        // legacy automation logic removed during refactor
    }

    private function reset_cart_session($order) {
        global $woocommerce;
        $woocommerce->cart->empty_cart();
        WC()->session->set('cart', []);
        WC()->session->set('last_order_id', '');
    }

    private function render_tx_details($order) {
        ob_start();
        ?>
        <h2 class="woocommerce-order-details__title"><?php echo esc_html__( 'LaqiraPay Transaction Details:', 'laqirapay' ); ?></h2>
        <table class="shop_table shop_table_responsive additional_info"><tbody>
            <?php echo TransactionDetailsRenderer::renderRows(TransactionDetailsRenderer::buildTransactionRows($order)); ?>
        </tbody></table>
        <?php
        return ob_get_clean();
    }

    public function check_wc_currency_with_rates_after_change() {
        $currencies = get_woocommerce_currencies();
        $current_currency = get_woocommerce_currency();
        if ($current_currency !== 'USD') {
            $saved = get_option('laqirapay_exchange_rate_' . $current_currency, '');
            if ($saved) {
                add_action('admin_notices', function () use ($current_currency, $saved) {
                    $link = admin_url('admin.php?page=laqirapay-currency-rate');
                    $currentCurrencySafe = esc_html($current_currency);
                    $savedRateSafe = esc_html($saved);
                    echo '<div class="notice notice-warning"><p>'
                        . sprintf(
                            esc_html__('WooCommerce currency changed. You set currency exchange rate for %s to %s. If you need to change it, please click ', 'laqirapay'),
                            $currentCurrencySafe,
                            $savedRateSafe
                        )
                        . '<a href="' . esc_url($link) . '">' . esc_html__('here', 'laqirapay') . '</a>.</p></div>';
                });
                LaqiraLogger::log(200, 'hooks', 'currency_rate_exists', ['currency' => $current_currency]);
            }
        }
    }

    public function check_wc_currency_with_rates() {
        if (class_exists('WooCommerce') && version_compare(WC()->version, '8.2', '>')) {
            $currencies = get_woocommerce_currencies();
            $current_currency = get_woocommerce_currency();
            if ($current_currency !== 'USD') {
                $saved = get_option('laqirapay_exchange_rate_' . $current_currency, '');
                if (!$saved) {
                    add_action('admin_notices', function () {
                        $link = admin_url('admin.php?page=laqirapay-currency-rate');
                        echo '<div class="notice notice-error"><p>' . esc_html__('WooCommerce currency changed. Please set your exchange rate for LaqiraPay from ', 'laqirapay') . '<a href="' . esc_url($link) . '">' . esc_html__('here', 'laqirapay') . '</a>.</p></div>';
                    });
                    LaqiraLogger::log(300, 'hooks', 'currency_rate_missing', ['currency' => $current_currency]);
                }
            }
        }
    }

    public function woocommerce_email_order_meta_fields($fields, $sent_to_admin, $order) {
        if ($order->get_payment_method() === 'WC_laqirapay') {
            $fields = array_merge($fields, TransactionDetailsRenderer::buildEmailFields($order));
        }
        return $fields;
    }
}
