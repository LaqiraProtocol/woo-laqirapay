<?php

/* @link       https://laqira.io
 * @since      0.1.0
 *
 * @package    WooLaqiraPay
 * @subpackage WooLaqiraPay/includes/woocommerce
 */

use kornrunner\Keccak;
use Web3\Utils;



if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load WooLaqiraPay gateway function.
 *
 * @return void
 */
function load_woo_laqirapay_gateway(): void
{
    if (!function_exists('woocommerce_add_woo_laqirapay_gateway') && class_exists('WC_Payment_Gateway') && !class_exists('WC_woo_laqirapay')) {

        /**
         * Adds the WooLaqiraPay gateway to WooCommerce payment gateways.
         *
         * @param array $methods Existing payment methods.
         * @return array Updated payment methods.
         */
        add_filter('woocommerce_payment_gateways', 'woocommerce_add_woo_laqirapay_gateway');
        function woocommerce_add_woo_laqirapay_gateway($methods)
        {
            $methods[] = 'WC_woo_laqirapay';
            return $methods;
        }




        /**
         * This Ajax function is responsible for processing order creation or update in WooCommerce.
         *
         * @return void, but send JSON response.
         */
        add_action('wp_ajax_laqira_get_order_for_laqira_pay', 'laqira_get_order_for_laqira_pay');
        add_action('wp_ajax_nopriv_laqira_get_order_for_laqira_pay', 'laqira_get_order_for_laqira_pay');
        function laqira_get_order_for_laqira_pay()
        {
            // Check Ajax nonce.
            if (wp_verify_nonce($_POST['security'], 'laqira_nonce')) {


                $headers = $_SERVER['HTTP_AUTHORIZATION'];
                if (isset($headers) && verify_header($headers) === 'verified') {
                    global $woocommerce;

                    if ($_POST['customer']) {
                        $cu1 = json_decode(stripslashes(($_POST['customer'])));
                        $cu = $cu1;
                        $customer = array(
                            'billing_address_1' => $cu->{'billing_address_1'},
                            'billing_address_2' => $cu->{'billing_address_2'},
                            'billing_city' => $cu->{'billing_city'},
                            'billing_company' => $cu->{'billing_company'},
                            'billing_country' => $cu->{'billing_country'},
                            'billing_email' => $cu->{'billing_email'},
                            'billing_first_name' => $cu->{'billing_first_name'},
                            'billing_last_name' => $cu->{'billing_last_name'},
                            'billing_phone' => $cu->{'billing_phone'},
                            'billing_postcode' => $cu->{'billing_postcode'},
                            'billing_state' => $cu->{'billing_state'},
                            'order_comments' => $cu->{'order_comments'}
                        );
                    }

                    $cart_hash = $woocommerce->session->get('cart_hash');
                    $current_cart_hash = $woocommerce->cart->get_cart_hash();
                    $last_order_id = $woocommerce->session->get('last_order_id');
                    $order_status = '';
                    $old_order = wc_get_order($last_order_id);
                    // If the shopping cart is changed or a new cart is detected.
                    if ($cart_hash !== $current_cart_hash || empty($cart_hash)) {
                        // Create a new order.
                        $order = wc_create_order();
                        $order_status = ' Created.';

                        // Add cart products to the order.
                        foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
                            $product = $cart_item['data'];
                            $quantity = $cart_item['quantity'];
                            $order->add_product($product, $quantity);
                        }

                        // If the user is logged in, set the user ID for the order.
                        if (get_option('woo_laqirapay_only_logged_in_user') != 0 && is_user_logged_in()) {
                            $order->set_customer_id(get_current_user_id());
                        }

                        // Update order totals.
                        $order->calculate_totals();

                        // Store the new order ID and cart hash in the session.
                        $woocommerce->session->set('last_order_id', $order->get_id());
                        $woocommerce->session->set('cart_hash', $current_cart_hash);

                        $order->update_status('wc-pending', esc_html__('Payment is awaited.', 'woo-laqirapay'));
                        $order->add_order_note(esc_html__('Customer has chosen WooLaqiraPay Wallet payment method, payment is pending.', 'woo-laqirapay'));
                    } else if (isset($last_order_id) && !empty($last_order_id) && $last_order_id != '' && $old_order->get_status() == 'pending') {
                        // Edit previous order.
                        $order = wc_get_order($last_order_id);
                        $order_status = ' Updated.';

                        // Remove previous products from the order.
                        foreach ($order->get_items() as $item_id => $item) {
                            $order->remove_item($item_id);
                        }

                        foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
                            $product = $cart_item['data'];
                            $quantity = $cart_item['quantity'];
                            $order->add_product($product, $quantity);
                        }

                        // If the user is logged in, set the user ID for the order.
                        if (get_option('woo_laqirapay_only_logged_in_user') != 0 && is_user_logged_in()) {
                            $order->set_customer_id(get_current_user_id());
                        }

                        // Update order totals.
                        $order->calculate_totals();

                        $order->update_status('wc-pending', esc_html__('Payment is awaited.', 'woo-laqirapay'));
                        $order->add_order_note(esc_html__('Customer has chosen WooLaqiraPay payment method, payment is pending.', 'woo-laqirapay'));
                    } else {
                        // Create a new order.
                        $order = wc_create_order();
                        $order_status = ' Created2.';

                        // Add cart products to the order.
                        foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
                            $product = $cart_item['data'];
                            $quantity = $cart_item['quantity'];
                            $order->add_product($product, $quantity);
                        }

                        // If the user is logged in, set the user ID for the order.
                        if (get_option('woo_laqirapay_only_logged_in_user') != 0 && is_user_logged_in()) {
                            $order->set_customer_id(get_current_user_id());
                        }


                        // Update order totals.
                        $order->calculate_totals();

                        // Store the new order ID and cart hash in the session.
                        $woocommerce->session->set('last_order_id', $order->get_id());
                        $woocommerce->session->set('cart_hash', $current_cart_hash);
                        $order->update_status('wc-pending', esc_html__('Payment is awaited.', 'woo-laqirapay'));
                        $order->add_order_note(esc_html__('Customer has chosen WooLaqiraPay payment method, payment is pending.', 'woo-laqirapay'));
                    }

                    $customer = json_decode(json_encode($customer), true);

                    if (isset($customer) && is_array($customer) && count($customer) > 0) {
                        $address = array(
                            'first_name' => $customer['billing_first_name'],
                            'last_name'  => $customer['billing_last_name'],
                            'company'    => $customer['billing_company'],
                            'email'      => $customer['billing_email'],
                            'phone'      => $customer['billing_phone'],
                            'address_1'  => $customer['billing_address_1'],
                            'address_2'  => $customer['billing_address_2'],
                            'city'       => $customer['billing_city'],
                            'state'      => $customer['billing_state'],
                            'postcode'   => $customer['billing_postcode'],
                            'country'    => $customer['billing_country']
                        );
                        $order->set_address($address, 'billing');
                        $order->set_address($address, 'shipping');
                        $order->set_customer_note($customer['order_comments']);
                    }

                    $order->save();

                    if (($order->get_id()) !== 0) {
                        wp_send_json_success([
                            'result' => 'success',
                            'order_id' => $order->get_id(),
                            'cart_hash' => $cart_hash,
                            'current Cart Hash' => $current_cart_hash,
                            'Last Order ID' => $last_order_id,
                            'order_status' => $order_status,
                        ]);
                    } else {
                        wp_send_json_error([
                            'result' => 'failed',
                            'order_id' => $order->get_id(),
                            'cart_hash' => $cart_hash,
                            'current Cart Hash' => $current_cart_hash,
                            'Last Order ID' => $last_order_id,
                            'order_status' => $order_status,
                            'error' => __('create or update order not successful. please try again...')
                        ]);
                    }
                } else {
                    wp_send_json_error(['result' => 'error', 'error' => __('Your request was not Authorized. Please refresh the checkout page again')]);
                }
            } else {
                wp_send_json_error(['result' => 'failed', 'error' => __('nonce Error!!!')]);
            }
        }

        //  ----------------------------------------------------------------------------------
        /**
         * Laqira payment create transaction hash function.
         *
         * @return void, but send JSON response.
         */
        add_action('wp_ajax_laqira_payment_create_tx_hash', 'laqira_payment_create_tx_hash');
        add_action('wp_ajax_nopriv_laqira_payment_create_tx_hash', 'laqira_payment_create_tx_hash');
        function laqira_payment_create_tx_hash(): void
        {
            // Check Ajax nonce.
            if (wp_verify_nonce($_POST['security'], 'laqira_nonce')) {
                $headers = $_SERVER['HTTP_AUTHORIZATION'];
                if (isset($headers) && verify_header($headers) === 'verified') {
                    global $woocommerce;
                    $laqira_data = $_POST['laqiradata'];
                    if (isset($laqira_data)) {

                        $data = json_decode(stripslashes($laqira_data));

                        $order_id = $data->{'orderID'};
                        $slippage = $data->{'slippage'};
                        $tx_hash = $data->{'tx_hash'};
                        $site_admin_address_wallet = $data->{'siteAdminAddressWallet'};
                        $user_wallet = $data->{'userWallet'};
                        $req_hash = $data->{'reqHash'};
                        $price = $data->{'price'};
                        $asset = $data->{'asset'};
                        $asset_name = $data->{'assetName'};
                        $asset_amount = $data->{'assetAmount'};
                        $payment_type = $data->{'payment_type'};

                        $order = wc_get_order(intval($order_id));

                        $order->add_order_note('Order was Updated by ' . $payment_type . ' method with TxHash ' . $tx_hash);
                        $order->update_meta_data('tx_hash', $tx_hash);
                        $order->update_meta_data('AdminWalletAddress', $site_admin_address_wallet);
                        $order->update_meta_data('CustomerWalletAddress', $user_wallet);
                        $order->update_meta_data('reqHash', $req_hash);
                        $order->update_meta_data('slippage', $slippage);
                        $order->update_meta_data('TokenAddress', $asset);
                        $order->update_meta_data('TokenName', $asset_name);
                        $order->update_meta_data('TokenAmount', $asset_amount);
                        $order->update_meta_data('payment_type', $payment_type);
                        $order->set_total($price);

                        // Remove previous products from the order.
                        foreach ($order->get_items() as $item_id => $item) {
                            $order->remove_item($item_id);
                        }

                        // Add cart products to the order.
                        foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
                            $product = $cart_item['data'];
                            $quantity = $cart_item['quantity'];
                            $order->add_product($product, $quantity);
                        }

                        // Order total update.
                        $order->calculate_totals();

                        $order->save();

                        wp_send_json_success(['result' => 'success']);
                    } else {
                        wp_send_json_error(['result' => 'error', 'error' => __('Your Data is invalide')]);
                    }
                } else {
                    wp_send_json_error(['result' => 'error', 'error' => __('Your request was not Authorized. Please refresh the checkout page again')]);
                }
            } else {
                wp_send_json_error(['result' => 'failed', 'error' => __('nonce Error!!!')]);
            }
        }

        /**
         * Laqira payment confirmation function.
         *
         * @return void, send $order->get_checkout_order_received_url with wp_send_json_success to redirect by JS.
         */
        add_action('wp_ajax_laqira_payment_confirmation', 'laqira_payment_confirmation');
        add_action('wp_ajax_nopriv_laqira_payment_confirmation', 'laqira_payment_confirmation');
        function laqira_payment_confirmation()
        {
            // Check Ajax nonce.
            if (wp_verify_nonce($_POST['security'], 'laqira_nonce')) {
                $headers = $_SERVER['HTTP_AUTHORIZATION'];
                if (isset($headers) && verify_header($headers) === 'verified') {
                    global $woocommerce;
                    $laqira_data = $_POST['laqiradata'];
                    if (isset($laqira_data)) {

                        $data = json_decode(stripslashes($laqira_data));

                        $order_id = $data->{'orderID'};
                        $slippage = $data->{'slippage'};
                        $tx_hash = $data->{'tx_hash'};
                        $site_admin_address_wallet = $data->{'siteAdminAddressWallet'};
                        $user_wallet = $data->{'userWallet'};
                        $req_hash = $data->{'reqHash'};
                        $price = $data->{'price'};
                        $asset = $data->{'asset'};
                        $asset_name = $data->{'assetName'};
                        $asset_amount = $data->{'assetAmount'};
                        $payment_type = $data->{'payment_type'};

                        $order = wc_get_order(intval($order_id));

                        $old_tx_hash = $order->get_meta('tx_hash');
                        $old_req_hash = $order->get_meta('reqHash');
                        if ($old_tx_hash == $tx_hash && $old_req_hash == $req_hash) {
                            $tx_results = [];
                            if ($payment_type == 'Direct') {
                                $tx_results = getTransactionInfo($tx_hash, function ($transaction) {
                                    return (decodeTransactionDirect($transaction->{"input"}));
                                });
                            } else {
                                $tx_results = getTransactionInfo($tx_hash, function ($transaction) {
                                    return (decodeTransactionInApp($transaction->{"input"}));
                                });
                            }

                            if (
                                isset($tx_results) && !empty($tx_results) &&
                                $slippage == intval($tx_results["_slippage"] / 100) &&
                                $site_admin_address_wallet == $tx_results["_provider"] &&
                                strtolower($asset) == $tx_results["_asset"] &&
                                floatval($price) == floatval($tx_results["_price"] / 100) &&
                                ('0x' . $req_hash) == $tx_results["_reqHash"]
                            ) {

                                $order->update_meta_data('tx_hash', $tx_hash);
                                $order->update_meta_data('AdminWalletAddress', $site_admin_address_wallet);
                                $order->update_meta_data('CustomerWalletAddress', $user_wallet);
                                $order->update_meta_data('reqHash', $req_hash);
                                $order->update_meta_data('slippage', $slippage);
                                $order->update_meta_data('TokenAddress', $asset);
                                $order->update_meta_data('TokenName', $asset_name);
                                $order->update_meta_data('TokenAmount', $asset_amount);
                                $order->update_meta_data('payment_type', $payment_type);
                                $order->set_total($price);

                                // Remove previous products from the order.
                                foreach ($order->get_items() as $item_id => $item) {
                                    $order->remove_item($item_id);
                                }

                                // Add cart products to the order.
                                foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
                                    $product = $cart_item['data'];
                                    $quantity = $cart_item['quantity'];
                                    $order->add_product($product, $quantity);
                                }

                                // Order total update.
                                $order->calculate_totals();
                                $payment_gateways = $woocommerce->payment_gateways->payment_gateways();
                                $order->set_payment_method($payment_gateways['WC_woo_laqirapay']);
                                $order->update_status('wc-completed', '');
                                $order->add_order_note('Order Completed by ' . $payment_type . ' method with TxHash ' . $tx_hash);
                                $order->save();


                                global $wpdb;
                                $table_name_laqira_transactions = $wpdb->prefix . "woo_laqira_transactions";
                                $existing_row = $wpdb->get_row("SELECT * FROM $table_name_laqira_transactions WHERE wc_order_id = $order_id");
                                $laqira_transactions = array(
                                    'wc_total_price' => $price,
                                    'wc_currency' =>  get_woocommerce_currency(),
                                    'wc_order_id' => $order_id,
                                    'tx_hash' => $tx_hash,
                                    'token_address' => $asset,
                                    'token_name' => $asset_name,
                                    'token_amount' => $asset_amount,
                                    'req_hash' => $req_hash,
                                    'tx_from' => $user_wallet,
                                    'tx_to' => $site_admin_address_wallet
                                );

                                if (null !== $existing_row) {
                                    $wpdb->update($table_name_laqira_transactions, $laqira_transactions, array('wc_order_id' => $order_id));
                                } else {
                                    $wpdb->insert($table_name_laqira_transactions, $laqira_transactions);
                                }

                                wp_send_json_success(['result' => 'success', 'data' => $tx_results, 'redirect' => $order->get_checkout_order_received_url()]);
                            } else {
                                $order->update_status('wc-failed', '');
                                $order->add_order_note(__('Order not verified by blockchain.', 'woo-laqirapay'));
                                $order->save();
                                wp_send_json_error([
                                    'result' => 'error',
                                    'data' => [
                                        'issetTXHASH' => isset($tx_results),
                                        'emptyTXHASH' => empty($tx_results),
                                        'slippage' => intval($tx_results["_slippage"] / 100),
                                        'site_admin_address_wallet' => $tx_results["_provider"],
                                        'asset' => $tx_results["_asset"],
                                        '$price' => floatval($tx_results["_price"] / 100),
                                        'req_hash' => $tx_results["_reqHash"],
                                        'TXHASH' => $data->{'tx_hash'}

                                    ],
                                    'error' => __('Your Data not Verified by Blockchain', 'woo-laqirapay')
                                ]);
                            }
                        } else {
                            wp_send_json_error(['result' => 'error', 'error' => __('Your Data is invalide')]);
                        }
                    } else {
                        wp_send_json_error(['result' => 'error', 'error' => __('Your request was not Authorized. Please refresh the checkout page again')]);
                    }
                } else {
                    wp_send_json_error(['result' => 'failed', 'error' => __('nonce Error!!!')]);
                }
            }
        }

        /**
         * This function is used to create Laqira payment data.
         *
         *
         * @return void This function does not return anything, but sends data to JavaScript as JSON.
         */
        add_action('wp_ajax_laqira_payment_data', 'laqira_payment_data');
        add_action('wp_ajax_nopriv_laqira_payment_data', 'laqira_payment_data');
        function laqira_payment_data()
        {
            // Check Ajax nonce.
            if (wp_verify_nonce($_POST['security'], 'laqira_nonce')) {
                $headers = $_SERVER['HTTP_AUTHORIZATION'];
                if (isset($headers) && verify_header($headers) === 'verified') {
                    global $woocommerce;
                    $order_id = $_POST['orderID'];
                    // $cart_total = $woocommerce->cart->get_total('edit');


                    $key_encode = get_option('woo_laqirapay_api_key');
                    $order = wc_get_order(intval($order_id));
                    $cart_total = $order->get_total();

                    // Get Provider Address From API-KEY.
                    $provider_address = str_replace(' ', '', strtolower(get_provider()));

                    // Get Provider Address Value.
                    $provider_address_value = str_replace('0x', '', $provider_address);

                    // Get Main Domain of this site.
                    $main_domain = str_replace(' ', '', strtolower(get_site_url()));

                    // Concatenate ProviderAddress With Domain.
                    $combined_string = $provider_address . $main_domain;

                    // Calculate MD5 for Concatenated value.
                    $md5_hash = md5($combined_string);

                    // Get first 12 characters of MD5 value (12 char of this + 20 Char for OrderID => 32 char ).
                    $short_md5_hash = substr($md5_hash, 0, 12);

                    // Create 20 character String with orderID (bigint(20)).
                    $padded_hex_value_order_id = str_pad(($order_id), 20, "0", STR_PAD_LEFT);

                    $order = $padded_hex_value_order_id;

                    // Get Order Total Price.
                    $price = $cart_total;
                    // Convert Order total Price to String with its decimal.
                    $string_price = str_replace('.', '', number_format($price, 2, '.', ''));

                    // Convert Price to Hex and pad it to 64 characters.
                    $padded_string_price = str_pad(dechex($string_price), 64, "0", STR_PAD_LEFT);

                    // Concatenate values to create first parameter for final Hash.
                    $concat_md5hash_order = $short_md5_hash . $order;

                    // Convert last Parameter of final hash to byte.
                    $order_id_32 = bin2hex($concat_md5hash_order);

                    // Create final hash string.
                    $final_concat = $provider_address_value . $padded_string_price . $order_id_32;

                    // Hash Final value to send.
                    $final_hash = Keccak::hash(hex2bin($final_concat), 256);

                    // Send key to decode values in JS.
                    wp_send_json_success([
                        'result' => 'success',
                        'final_hash' => $final_hash,
                        'order_string_price' => $string_price,
                        'site_admin_provider' => $provider_address,
                        'order_id_32' => $order_id_32,
                        'order_id' => $order_id,
                    ]);
                } else {
                    wp_send_json_error(['result' => 'error', 'error' => __('Your request was not Authorized. Please refresh the checkout page again')]);
                }
            } else {
                wp_send_json_error(['result' => 'failed', 'error' => __('nonce Error!!!')]);
            }
        }


       
        add_action('woocommerce_review_order_before_submit', 'laqira_validation_checkout');
        function laqira_validation_checkout()
        {
            if (is_checkout()) {
                $email = WC()->customer->get_billing_email();
                $first_name =WC()->customer->get_billing_first_name();
                $last_name=WC()->customer->get_billing_last_name();
                // Check if the required fields is empty
                if (empty($email) || empty($first_name) || empty($last_name)) {
                    wc_add_notice(__('please fill your required fields.','woo-laqirapay'), 'error');
                }
            }
        }




        /**
         * Set Laqira pay logo size.
         */
        add_action('after_setup_theme', 'laqira_pay_logo_size');
        function laqira_pay_logo_size()
        {
            add_image_size('laqira_pay_logo_size', 36, 36, true); // 36 pixels wide by 36 pixels tall, hard crop mode
        }




        /**
         * Class WC_woo_laqirapay
         *
         * This class handles the integration of WooLaqiraPay payment gateway with WooCommerce.
         *
         * @extends WC_Payment_Gateway
         */
        class WC_woo_laqirapay extends WC_Payment_Gateway
        {
            /**
             * @var string The message to display when payment fails.
             */
            private $failedMassage;

            /**
             * @var string The message to display when payment is successful.
             */
            private $successMassage;

            public $react_root = ''; //'<div id="wcLaqirapayApp">...</div>';

            /**
             * Constructor method for WC_woo_laqirapay class.
             */
            public function __construct()
            {
                // Set gateway properties
                $this->id = 'WC_woo_laqirapay';
                $this->method_title = __('Laqira Pay', 'woocommerce');
                $this->method_description = __('WooLaqiraPay Woocommerce payment gateway', 'woocommerce');
                $this->has_fields = true;
                $this->init_form_fields();
                $this->init_settings();
                $this->title = $this->settings['title'];
                $this->description = $this->settings['description'];
                $this->successMassage = $this->settings['success_massage'];
                $this->failedMassage = $this->settings['failed_massage'];

                // Hook to save settings
                add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
                add_action('woocommerce_review_order_after_order_total', array($this, 'custom_frontend_script'));
                
                add_action('woocommerce_checkout_update_order_review', array($this, 'custom_frontend_script'));
                add_action('woocommerce_account_content', array($this, 'custom_frontend_script'));
            }




            public function custom_frontend_script()
            {
                if (get_option('woo_laqirapay_only_logged_in_user') && !is_user_logged_in()) {
                    echo esc_html__('Please login to make a payment!', 'woo-laqirapay');
                } else {
                    $nets = get_networks();
                    $assets =  get_networks_assets();
                    $provider = get_provider();



                    if ((isset($nets) && is_array($nets) && count($nets) > 0) && (isset($assets) && is_array($assets) && count($assets) > 0) && Utils::isAddress($provider)) {
                        $this->enqueue_scripts();
                        echo '<div id="wcLaqirapayApp"></div>';
                    } else {
                        wp_enqueue_script('laqirapayJS', (LAQIRA_PLUGINS_URL . '/public/js/woo-laqirapay-first.js'), array('jquery'), '1.0.3', true);
                        $err_message = '<div id="wcLaqirapayApp" style="text-align:center;padding-top: 20px;"><< Error >></br> ';
                        if (!Utils::isAddress($provider)) {
                            $err_message .= 'We encountered an error. Please contact the administrator</br>';
                        }
                        if ((isset($nets) && is_array($nets) && !count($nets) > 0)) {
                            $err_message .= 'Network data not loaded.</br>';
                        }
                        if ((isset($assets) && is_array($assets) && !count($assets) > 0)) {
                            $err_message .= 'Assets data not loaded. </br>';
                        }
                        echo $err_message . '</div>';
                    }
                    // if(is_checkout()){
                    //     do_action('woocommerce_review_order_before_submit');
                    // }

                }
            }

            /**
             * Initializes form fields for the gateway settings.
             */
            public function init_form_fields()
            {
                $this->form_fields = apply_filters(
                    'WC_woo_laqirapay_Config',
                    array(
                        'base_config' => array(
                            'title' => __('General Settings', 'woocommerce'),
                            'type' => 'title',
                            'description' => '',
                        ),
                        'enabled' => array(
                            'title' => __('Enablr/Disable', 'woocommerce'),
                            'type' => 'checkbox',
                            'label' => __('Activation WooLaqiraPay Gateway', 'woocommerce'),
                            'description' => __('To active WooLaqiraPay Gateway please check', 'woocommerce'),
                            'default' => 'yes',
                            'desc_tip' => true,
                        ),
                        'title' => array(
                            'title' => __('Gateway Title', 'woocommerce'),
                            'type' => 'text',
                            'description' => __('The title of the gateway that is displayed to the customer during the purchase', 'woocommerce'),
                            'default' => __('Laqira Pay', 'woocommerce'),
                            'desc_tip' => true,
                        ),
                        'description' => array(
                            'title' => __('Gayeway Description ', 'woocommerce'),
                            'type' => 'text',
                            'desc_tip' => true,

                            'description' => __('The description that will be displayed during the payment operation for the gateway.', 'woocommerce'),
                        ),
                        'payment_config' => array(
                            'title' => __('Payment settings', 'woocommerce'),
                            'type' => 'title',
                            'description' => '',
                        ),
                        'success_massage' => array(
                            'title' => __('Successful payment message', 'woocommerce'),
                            'type' => 'textarea',
                            'description' => __('Message text after successful payment by the user', 'woocommerce'),
                            'default' => __('thank you . Your order has been successfully paid', 'woocommerce'),
                        ),
                        'failed_massage' => array(
                            'title' => __('Payment failed message', 'woocommerce'),
                            'type' => 'textarea',
                            'description' => __(' Enter the text of the message you want to display to the user after unsuccessful payment.', 'woocommerce'),
                            'default' => __('Your payment has failed. Please try again or contact the site administrator in case of problems.', 'woocommerce'),
                        ),
                    )
                );
            }

            /**
             * Returns the HTML for the gateway icon.
             *
             * @return string The HTML for the gateway icon.
             */
            public function get_icon(): string
            {
                // Generate and return the HTML for the gateway icon
                $iconHtml = '<img src="https://s2.coinmarketcap.com/static/img/coins/64x64/14446.png" width="25" height="25">';
                return apply_filters('woocommerce_gateway_icon', $iconHtml, $this->id);
            }

            /**
             * Generates and outputs payment fields on the checkout page.
             */
            public function payment_fields()
            {
                // Output payment fields HTML
                if (get_option('woo_laqirapay_only_logged_in_user') && !is_user_logged_in()) {
                    echo esc_html__('Please login to make a payment!', 'woo-laqirapay');
                } else {

                    echo '<div id="startwooLaqiraPayApp"></div>';
                    echo '<div style="text-align:center;" id="laqira_loder"><img class="loading" width="24px" height="24px" src="' . LAQIRA_PLUGINS_URL . 'assets/img/loading.svg"> </div>';
                    echo '<div style="text-align:center;font-size: x-small;padding-top: 20px;">powered by <a href="https://laqirapay.com" target="_blank">LaqiraPay</a> © ' . date("Y") . '</div>';

                    echo esc_html($this->description);
                }
            }

            /**
             * Enqueues necessary scripts for payment processing.
             */
            private function enqueue_scripts()
            {
                // Enqueue required scripts
                wp_enqueue_script('laqirapayJS', (LAQIRA_PLUGINS_URL . '/public/js/woo-laqirapay-first.js'), array('jquery'), '1.0.3', true);

                $asset_file = plugin_dir_path(__FILE__) . '../../build/wooLaqiraPayMain.asset.php';
                $assetjs = include $asset_file;

                wp_enqueue_script(
                    'wclaqirapay-script',
                    (LAQIRA_PLUGINS_URL . '/build/wooLaqiraPayMain.js'),
                    $assetjs['dependencies'],
                    $assetjs['version'],
                    array(
                        'in_footer' => true,
                    )
                );
                wp_register_style(
                    'wclaqirapay-style',
                    (LAQIRA_PLUGINS_URL . 'build/wooLaqiraPayMain.css'),
                    $assetjs['version']
                );
                wp_enqueue_style('wclaqirapay-style');

                global $woocommerce;
                $order_data = array(
                    'pluginUrl' => LAQIRA_PLUGINS_URL,
                    'homeUrl' => get_home_url(),
                    'shopUrl' => get_permalink( wc_get_page_id( 'shop' ) ),
                    'myAccountUrl' =>  get_permalink( wc_get_page_id( 'myaccount' ) ),
                    'currencySymbol' => '$',
                    'cartTotal' => $woocommerce->cart->get_total('edit'),
                    'providerAddress' => get_provider(),
                    'laqiraAajaxUrl' => admin_url('admin-ajax.php'),
                    'laqiraAjaxnonce' => wp_create_nonce('laqira_nonce'),
                    'mainContractAddress' => CONTRACT_ADDRESS,
                    'token' => create_access_token(get_provider()),
                    'originalOrderID' => null,
                    'customer' => WC()->cart->get_customer()->get_billing_first_name(),
                    'wcpi'=>get_wcpi()
                );

                wp_localize_script(
                    'laqirapayJS',
                    'LaqiraData',
                    [
                        'laqiraAajaxUrl' => admin_url('admin-ajax.php'),
                    ]
                );

                wp_localize_script(
                    'wclaqirapay-script',
                    'LaqiraData',
                    [
                        'availableNetworks'                => get_networks(),
                        'availableAssets'                  => get_networks_assets(),
                        'orderData'                        => $order_data,
                        
                    ]
                );
            }
        }
    }
}

add_action('plugins_loaded', 'load_woo_laqirapay_gateway', 0);
