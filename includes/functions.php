<?php

/** @noinspection ALL */

use Web3\Contract;
use Web3\Utils;

// require "CryptoJsAes.php";
/**
 *This script defines constants related to the WooLaqiraPay plugin.
 *
 *@var string CONTRACT_ADDRESS The Ethereum contract address for WooLaqiraPay. 
 *            This code is only used to receive the initial data and is a main constant that is not used anywhere in the plugin.
 *@var string RPC_URL The Binance Smart Chain (BSC) RPC URL.
 *@var string LAQIRA_PLUGINS_URL The URL for the WooLaqiraPay plugins directory.
 */


define('CONTRACT_ADDRESS', '0x52aB753DD301e4fb5bf00D9BfEB55e356a44064D');
// define('CONTRACT_ADDRESS', '0x526568fCb37E119000061aC42B3cD9CF7Ed066B5');
define('RPC_URL', 'https://bsc-dataseed.binance.org/');
define('LAQIRA_PLUGINS_URL', plugins_url('woo-laqirapay/'));
define('WOOLAQIRAPAY_TOKEN_BYTE_LENGTH', 32);
define('WOOLAQIRAPAY_JWT_ALG', 'HS256');
define('WOOLAQIRAPAY_JWT_ALG_SIGNATURE', 'sha256');
define('WOOLAQIRAPAY_BEARER_JWT_SECRET', '!@#$%^&*()');




function is_wc_active()
{
    // Test to see if WooCommerce is active (including network activated).
    $plugin_path = trailingslashit(WP_PLUGIN_DIR) . 'woocommerce/woocommerce.php';
    if (
        in_array($plugin_path, wp_get_active_and_valid_plugins())
        || in_array($plugin_path, wp_get_active_network_plugins())
    ) {
        return true;
    } else {
        return false;
    }
}

// define network type in ABI
function net()
{
    return 'mainnet';
}

/**
 * Retrieves the Ethereum contract instance for WooLaqiraPay.
 *
 * @returns {object} The contract instance.
 */
function get_contract()
{
    $contract_address = CONTRACT_ADDRESS;
    $rpc_endpoint = RPC_URL;
    $contractAbi = json_decode(file_get_contents(LAQIRA_PLUGINS_URL . 'assets/json/cidAbi.json'), true);
    // Connect to Ethereum node
    $web3 = new \Web3\Web3($rpc_endpoint, 10);
    $contract = new Contract($web3->provider, $contractAbi);
    return $contract;
}


/**
 * Retrieves the CID (Content Identifier) from the WooLaqiraPay contract.
 *
 * @return string The CID.
 */
function get_cid()
{
    try {
        $contract_address = CONTRACT_ADDRESS;
        $rpc_endpoint = RPC_URL;
        $contractAbi = json_decode(file_get_contents(LAQIRA_PLUGINS_URL . 'assets/json/cidAbi.json'), true);
        // Connect to Ethereum node
        $web3 = new \Web3\Web3($rpc_endpoint, 10);
        $contract = new Contract($web3->provider, $contractAbi);
        $cid = '';
        // Call a function from the contract
        $contract->at($contract_address)->call(
            'getCid',
            function ($err, $data) use (&$cid) {
                $cid = implode('-', $data);
                return (implode('-', $data));
            }
        );
        return $cid;
    } catch (Exception $e) {
        error_log('Error: ' . $e->getMessage());
    }
}


function getTransactionInfo($tx, $callback)
{
    $web3 = new \Web3\Web3('https://bsc-dataseed.binance.org/', 10);
    $result = null;

    $web3->eth->getTransactionByHash($tx, function ($err, $transaction) use (&$result, $callback) {
        if ($err !== null) {
            echo 'Error: ' . $err->getMessage();
            return;
        }
        $result = $callback($transaction);
    });

    return $result;
}


function getTransactionRec($tx, $callback)
{
    $web3 = new \Web3\Web3('https://bsc-dataseed.binance.org/', 10);
    $result = null;

    $web3->eth->getTransactionReceipt($tx, function ($err, $transaction) use (&$result, $callback) {
        if ($err !== null) {
            echo 'Error: ' . $err->getMessage();
            return;
        }
        $result = $callback($transaction);
    });

    return ($result);
}


function decodeTransactionDirect($tx)
{
    $abi = '[
        {
            "inputs": [
                {
                    "internalType": "uint256",
                    "name": "_slippage",
                    "type": "uint256"
                },
                {
                    "internalType": "address",
                    "name": "_provider",
                    "type": "address"
                },
                {
                    "internalType": "address",
                    "name": "_asset",
                    "type": "address"
                },
                {
                    "internalType": "uint256",
                    "name": "_price",
                    "type": "uint256"
                },
                {
                    "internalType": "bytes32",
                    "name": "_orderNum",
                    "type": "bytes32"
                },
                {
                    "internalType": "bytes32",
                    "name": "_reqHash",
                    "type": "bytes32"
                }
            ],
            "name": "customerDirectPayment",
            "outputs": [
                {
                    "internalType": "bool",
                    "name": "",
                    "type": "bool"
                }
            ],
            "stateMutability": "payable",
            "type": "function"
        }
    ]';

    $abiArray = json_decode($abi);
    $decodeValue = new WooLaqiraPayAbiDecoder($abiArray);
    return $decodeValue->decode_input($tx);
}

function decodeTransactionInApp($tx)
{
    $abi = '[
        {
            "inputs": [
                {
                    "internalType": "uint256",
                    "name": "_slippage",
                    "type": "uint256"
                },
                {
                    "internalType": "address",
                    "name": "_provider",
                    "type": "address"
                },
                {
                    "internalType": "address",
                    "name": "_asset",
                    "type": "address"
                },
                {
                    "internalType": "uint256",
                    "name": "_price",
                    "type": "uint256"
                },
                {
                    "internalType": "bytes32",
                    "name": "_orderNum",
                    "type": "bytes32"
                },
                {
                    "internalType": "bytes32",
                    "name": "_reqHash",
                    "type": "bytes32"
                }
            ],
            "name": "customerInAppPayment",
            "outputs": [
                {
                    "internalType": "bool",
                    "name": "",
                    "type": "bool"
                }
            ],
            "stateMutability": "nonpayable",
            "type": "function"
        }
    ]';

    $abiArray = json_decode($abi);
    $decodeValue = new WooLaqiraPayAbiDecoder($abiArray);
    return $decodeValue->decode_input($tx);
}





/**
 * Retrieves the provider address from the WooLaqiraPay API-Key.
 *
 * @return string The provider address.
 */
function get_provider()
{
    $result = '';

    try {
        $api_key = get_option('woo_laqirapay_api_key');
        $contract_address = $api_key;
        if (!Utils::isAddress($contract_address)) {
            return "Your Api Key is invalid";
        }
        $rpc_endpoint = RPC_URL;
        $contractAbi = json_decode(file_get_contents(LAQIRA_PLUGINS_URL . 'assets/json/apiabi.json'), true);
        $web3 = new \Web3\Web3($rpc_endpoint, 10);
        $contract = new Contract($web3->provider, $contractAbi);

        $contract->at($contract_address)->call(
            'getProvider',
            function ($err, $data) use (&$result) {
                if ($err !== null) {
                    error_log('Error: ' . $err->getMessage());
                    return;
                } else if ($data !== null) {
                    $result = $data[0];
                }
            }
        );
    } catch (Exception $e) {
        error_log('Error: ' . $e->getMessage());
        return;
    }

    return $result;
}

/**
 * Retrieves JSON data from a remote API.
 *
 * @param string $api The API URL.
 * @return array|string Associative array of data or an error message.
 */
function get_remote_json_cid($api)
{
    $response = wp_remote_get($api);

    if (is_wp_error($response)) {
        return array('error' => 'Unable to fetch data.');
    }
    $body = wp_remote_retrieve_body($response);

    $json_data = json_decode($body, true);
    if ($json_data === null) {
        return array('error' => 'Unable to parse JSON data.');
    }
    return $json_data;
}

/**
 * Converts a multidimensional associative array to an object.
 *
 * @param array $array The input array.
 * @return stdClass The resulting object.
 */
function array_to_object($array)
{
    $obj = new stdClass();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $value = array_to_object($value);
        }
        $obj->$key = $value;
    }
    return $obj;
}

/**
 * Retrieves an array of networks based on the current network type (mainnet or testnet).
 *
 * @return array An array of network data.
 */
function get_networks(): array
{
    if (net() == 'testnet') {
        return get_testnet_networks();
    } else {
        return get_mainnet_networks();
    }
}

/**
 * Retrieves an array of mainnet networks.
 *
 * @return array Mainnet network information.
 */
function get_mainnet_networks(): array
{
    $data = get_remote_json_cid(get_cid());
    $mainnet_networks = $data['mainnet'];
    if (isset($mainnet_networks) && is_array($mainnet_networks)) {
        return $mainnet_networks;
    } else {
        return array();
    }
}

function get_wcpi()
{
    $data = get_remote_json_cid(get_cid());
    $wcpi = $data['wcpi'];
  return $wcpi;
}



/**
 * Retrieves an array of testnet networks.
 *
 * @return array Testnet network information.
 */
function get_testnet_networks(): array
{
    $data = get_remote_json_cid(get_cid());
    $testnet_networks = $data['testnet'];;
    return $testnet_networks;
}

/**
 * Retrieves an array of network assets based on the current network type (mainnet or testnet).
 *
 * @return array Available assets for the specified network.
 */
function get_networks_assets(): array
{
    if (net() == 'testnet') {
        return get_testnet_networks_assets();
    } else {
        return get_mainnet_networks_assets();
    }
}

/**
 * Retrieves an array of mainnet network assets.
 *
 * @return array Available assets for the mainnet.
 */
function get_mainnet_networks_assets(): array
{
    $data = get_remote_json_cid(get_cid());
    $mainnet_networks_assets = $data['availableAssets']['mainnet'];
    if (isset($mainnet_networks_assets) && is_array($mainnet_networks_assets)) {
        usort($mainnet_networks_assets, function ($a, $b) {
            return $a['index'] - $b['index'];
        });
        return $mainnet_networks_assets;
    } else {
        return array();
    }
}

/**
 * Retrieves an array of testnet network assets.
 *
 * @return array Available assets for the testnet.
 */
function get_testnet_networks_assets(): array
{
    $data = get_remote_json_cid(get_cid());
    $testnet_networks_assets = $data['availableAssets']['testnet'];
    return $testnet_networks_assets;
}

/**
 * Encode data with base64url encoding.
 *
 * @param string $data The data to be encoded.
 * @return string The encoded data.
 */
function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Decode data with base64url decoding.
 *
 * @param string $data The data to be decoded.
 * @return string The decoded data.
 */
function base64url_decode($data)
{
    return base64_decode(strtr($data, '-_', '+/'));
}



/**
 * Create an access token.
 *
 * @param int $user_id The user ID.
 * @return array An array containing the token, expiration datetime, and expiration duration.
 */
function create_access_token($user_id)
{
    $datetimeFormat = 'Y-m-d H:i:s';
    $API_BEARER_JWT_SECRET = "!@#$%^&*()";
    $exp = new DateTime();
    $nowTs = $exp->getTimeStamp();
    $exp->add(new DateInterval('PT' . 3600 . 'S'));
    $expTs = $exp->getTimeStamp();
    $expiresIn = $expTs - $nowTs;

    $token = null;
    $header = base64url_encode(json_encode(['typ' => 'JWT', 'alg' => WOOLAQIRAPAY_BEARER_JWT_SECRET]));
    $payload = base64url_encode(json_encode([
        'exp' => $expTs,
        'sub' => $user_id
    ]));
    $dataEncoded = $header . '.' . $payload;
    $signature = hash_hmac(WOOLAQIRAPAY_JWT_ALG_SIGNATURE, $dataEncoded, $API_BEARER_JWT_SECRET, true);
    $signatureEncoded = base64url_encode($signature);
    $token = $dataEncoded . '.' . $signatureEncoded;


    return [
        'token' => $token,
        'expires_datetime' => $exp->format($datetimeFormat),
        'expires_in' => $expiresIn
    ];
}

/**
 * Verify a JWT token.
 *
 * @param string $token The JWT token to be verified.
 * @return object|WP_Error The decoded payload on success, or WP_Error on failure.
 */
function verify_jwt($token)
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return new WP_Error(
            'api_api_bearer_auth_jwt',
            __('JWT token bad format', 'api_bearer_auth'),
            ['status' => 401]
        );
    }
    if (base64url_encode(
        hash_hmac(
            WOOLAQIRAPAY_JWT_ALG_SIGNATURE,
            $parts[0] . '.' . $parts[1],
            WOOLAQIRAPAY_BEARER_JWT_SECRET,
            true
        )
    ) !== $parts[2]) {
        return new WP_Error(
            'api_api_bearer_auth_signature',
            __('Signature could not be verified', 'api_bearer_auth'),
            ['status' => 401]
        );
    }
    $payload = json_decode(base64url_decode($parts[1]));

    if ($payload->exp < time()) {
        return new WP_Error(
            'api_api_bearer_auth_token_expired',
            __('Token expired', 'api_bearer_auth'),
            ['status' => 401]
        );
    }

    return $payload;
}

/**
 * Verify the Authorization header.
 *
 * @param string $headers The Authorization header.
 * @return string The verification status: 'verified', 'failed', or 'invalid'.
 */
function verify_header($headers)
{
    if (strpos($headers, 'Bearer ') === 0) {
        $token = substr($headers, 7);
        $result_header = verify_jwt($token);
        if (isset($result_header) && $result_header->{'sub'} == get_provider()) {
            return 'verified';
        } else {
            return 'failed';
        }
    } else {
        return 'invalid';
    }
}


/**
 * Clears the WooCommerce cart after payment status change.
 *
 * @param int $order_id The order ID.
 * @param string $old_status The previous order status.
 * @param string $new_status The new order status.
 * @param object $order The WooCommerce order object.
 */
add_action('woocommerce_order_status_changed', 'custom_empty_cart_on_status_change', 10, 4);
function custom_empty_cart_on_status_change($order_id, $old_status, $new_status, $order)
{
    if ($old_status === 'pending') {
        // Empty the cart
        WC()->cart->empty_cart();
        // Set last order ID (if needed)
        WC()->session->set('last_order_id', '');
    }
}

/**
 * Display Custom Checkout Fields Data on Thankyou page
 */
function custom_display_order_data($order_id)
{

    $order = wc_get_order(intval($order_id));
    $payment_gateway = $order->get_payment_method();
    if ($payment_gateway == 'WC_woo_laqirapay') {
        // Empty the cart
        WC()->cart->empty_cart();
        // Set last order ID (if needed)
        WC()->session->set('last_order_id', '');
?>
        <h2 class="woocommerce-order-details__title"><?php echo __('LaqiraPay Transaction Details:', 'woo-laqirapay'); ?></h2>
        <table class="shop_table shop_table_responsive additional_info">
            <tbody>
                <tr>
                    <th><?php _e('Token Name'); ?></th>
                    <td><?php echo $order->get_meta('TokenName'); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Token Amount'); ?></th>
                    <td><?php echo $order->get_meta('TokenAmount');; ?></td>
                </tr>
                <tr>
                    <th><?php _e('Transaction Hash'); ?></th>
                    <td><?php echo '<a href="https://bscscan.com/tx/' . $order->get_meta('tx_hash') . '" target="_blank">' . $order->get_meta('tx_hash') . '</a>';; ?></td>
                </tr>
            </tbody>
        </table>
    <?php
    }
}
add_action('woocommerce_thankyou', 'custom_display_order_data',  9);


// Adding wooLaqiraPay Meta container admin shop_order pages
add_action('add_meta_boxes', 'woolaqirapay_order_custom_metabox');
function woolaqirapay_order_custom_metabox()
{
    $screen =  wc_get_page_screen_id('shop_order');

    if ($screen && 'woocommerce_page_wc-orders' === $screen) {
        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $order = wc_get_order($order_id);

        if ($order) {
            $payment_method = $order->get_payment_method();
            if ($payment_method === 'WC_woo_laqirapay') {
                add_meta_box(
                    'laqirapay_metabox',
                    'LaqiraPay Details',
                    'woolaqirapay_metabox_content',
                    $screen,
                    'advanced',
                    'high'
                );
            }
        }
    }
}

add_action('add_meta_boxes', 'woolaqirapay_recovery_order_custom_metabox');
function woolaqirapay_recovery_order_custom_metabox()
{

    $screen =  wc_get_page_screen_id('shop_order');

    if ($screen && 'woocommerce_page_wc-orders' === $screen) {
        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $order = wc_get_order($order_id);

        if ($order) {
            $payment_method = $order->get_payment_method();
            if ($payment_method === 'WC_woo_laqirapay') {
                add_meta_box(
                    'laqirapay_order_recovery_metabox',
                    'LaqiraPay Order Recovery',
                    'woolaqirapay_order_recovery_metabox_content',
                    $screen,
                    'side',
                    'high'
                );
            }
        }
    }
}

// Metabox content
function woolaqirapay_metabox_content($object)
{
    // Get the WC_Order object
    $order = is_a($object, 'WP_Post') ? wc_get_order($object->ID) : $object;

    echo '<p>Customer Selected Payment Type:<strong> ' . $order->get_meta('payment_type') . '</strong></p>';
    echo '<p>Customer Wallet Address:<strong> ' . $order->get_meta('CustomerWalletAddress') . '</strong></p>';
    echo '<p>Token Amount:<strong> ' . $order->get_meta('TokenAmount') . ' ' . $order->get_meta('TokenName') . '</strong></p>';
    echo '<p>Slippage:<strong> ' . $order->get_meta('slippage') . '</strong></p>';
    echo '<p>Transaction Hash:<strong> ' . '<a href="https://bscscan.com/tx/' . $order->get_meta('tx_hash') . '" target="_blank">' . $order->get_meta('tx_hash') . '</a>' . '</strong></p>';
}

function woolaqirapay_order_recovery_metabox_content($object)
{
    // Get the WC_Order object
    $order = is_a($object, 'WP_Post') ? wc_get_order($object->ID) : $object;
    if ($order->get_meta('tx_hash')) {
        laqirapay_view_confirmation_tx_hash_automation($order);
    }
}

function laqirapay_view_confirmation_tx_hash_automation($order)
{
    $input_value = $order->get_meta('tx_hash');

    $tx_results_direct = getTransactionInfo($input_value, function ($transaction) {
        return (decodeTransactionDirect($transaction->{"input"}));
    });

    $tx_results_inapp = getTransactionInfo($input_value, function ($transaction) {
        return (decodeTransactionInApp($transaction->{"input"}));
    });

    $tx_results_receipt = getTransactionRec($input_value, function ($transaction) {
        return ($transaction);
    });

    $tx_results_receipt = (array)$tx_results_receipt;


    if (isset($tx_results_direct) && is_array($tx_results_direct) && count($tx_results_direct) > 0) {
        $slippage_form_tx = intval($tx_results_direct["_slippage"] / 100);
        $provider_address_from_tx = $tx_results_direct["_provider"];
        $asset_address_from_tx = $tx_results_direct["_asset"];
        $price_from_tx = floatval($tx_results_direct["_price"] / 100);
        $req_hash_from_tx =  $tx_results_direct["_reqHash"];
    }

    if (isset($tx_results_inapp) && is_array($tx_results_inapp) && count($tx_results_inapp) > 0) {
        $slippage_form_tx = intval($tx_results_inapp["_slippage"] / 100);
        $provider_address_from_tx = $tx_results_inapp["_provider"];
        $asset_address_from_tx = $tx_results_inapp["_asset"];
        $price_from_tx = floatval($tx_results_inapp["_price"] / 100);
        $req_hash_from_tx =  $tx_results_inapp["_reqHash"];
    }

    if (isset($tx_results_receipt) && is_array($tx_results_receipt) && count($tx_results_receipt) > 0) {
        $user_wallet_address_form_tx = $tx_results_receipt["from"];
        $main_laqirapay_contract_from_tx = $tx_results_receipt["to"];
        $transaction_status_from_tx = $tx_results_receipt["status"];
    }


    $tx_hash_to_find = $input_value;
    $order_id = $order->get_id();

    $original_provider = get_provider();
    echo '<div id="lqr-recover-order-result" class="info-box">';
    if ($original_provider == $provider_address_from_tx) {
        if ($transaction_status_from_tx === '0x1') {
            if ($order_id) {
                $order = wc_get_order(intval($order_id));
                $order_recovery_status = get_option('woo_laqirapay_order_recovery_status');
                $order_status = 'wc-' . $order->get_status();
                if (($order_status != 'wc-completed') && ($order_status != $order_recovery_status)) {

                    $order_data_provider_address = $order->get_meta('AdminWalletAddress');
                    $order_data_user_wallet_address = $order->get_meta('userWallet');


                    $order_data_slippage = $order->get_meta('slippage');
                    $order_data_req_hash = $order->get_meta('reqHash');
                    $order_data_asset = $order->get_meta('asset');

                    if (($order_data_provider_address == $original_provider)
                        && ($order_data_slippage == $slippage_form_tx)
                        // &&   ($order_data_asset == $asset_address_from_tx)
                        // &&   ($order_data_req_hash == $req_hash_from_tx)
                    ) {

                        echo '<p><span class="dashicons dashicons-yes-alt" style="color:green;">
                            </span>
                            This order is registered on the blockchain with the completed status, but for some reasons,
                             its status is not confirmed on the WooCommerce.
                              Please, after checking the order information, confirm it.</p></div>';

                        echo  do_compelete_order($order_id);
                        return true;
                    } else {
                        echo '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>Your data not confirmed</p>';
                        echo '</div>';
                        return false;
                    }
                } else {
                    echo '<p><span class="dashicons dashicons-info" style="color:orange;"></span>The order is already stable and does not require further confirmation</p>';
                    echo '</div>';
                    return false;
                }
            } else {
                echo '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>No order found with this Tx hash</p>';
                echo '</div>';
                return false;
            }
        } else {
            echo '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>The transaction status is not Completed on Blockchain</p>';
            echo '</div>';
            return false;
        }
    } else {
        echo '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>Provider Address not Confirmed</p>';
        echo '</div>';
        return false;
    }
    echo '</div>';
    wp_die();
}





add_filter('the_content', 'custom_checkout_content');
function custom_checkout_content($content)
{

    if (is_checkout_pay_page()) {
        global $wp;

        if (isset($wp->query_vars['order-pay']) && absint($wp->query_vars['order-pay']) > 0) {
            $original_order_id = absint($wp->query_vars['order-pay']); // The order ID
        }

        if ($original_order_id) {
            $order = wc_get_order(intval($original_order_id));
            if ($order->get_meta('tx_hash')) {
                if (laqirapay_view_confirmation_tx_hash_automation($order)) {
                    return;
                }
            }
            $cart_total = $order->get_total('edit');
            echo '<div id="wcLaqirapayApp"></div>';
            wp_enqueue_script('laqirapayJS', (LAQIRA_PLUGINS_URL . '/public/js/woo-laqirapay-first.js'), array('jquery'), '1.0.3', true);
            $asset_file = plugin_dir_path(__FILE__) . '../build/wooLaqiraPayMain.asset.php';
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
            $order_data = array(
                'pluginUrl' => LAQIRA_PLUGINS_URL,
                'currencySymbol' => '$',
                'cartTotal' => $cart_total,
                'providerAddress' => get_provider(),
                'laqiraAajaxUrl' => admin_url('admin-ajax.php'),
                'laqiraAjaxnonce' => wp_create_nonce('laqira_nonce'),
                'mainContractAddress' => CONTRACT_ADDRESS,
                'token' => create_access_token(get_provider()),
                'originalOrderID' => $original_order_id,
                'wcpi'=>get_wcpi()
            );
            wp_localize_script(
                'wclaqirapay-script',
                'LaqiraData',
                [
                    'availableNetworks'                => get_networks(),
                    'availableAssets'                  => get_networks_assets(),
                    'orderData'                        => $order_data
                ]
            );
        }
    }

    return $content;
}

function laqirapay_recovery_txHash_shortcode()
{
    ob_start();
    ?>
    <form id="woolaqirapay-tx-confirm-form" style="display: flex;flex-direction: column;justify-content: center;align-items: center; gap: 10px;">
        <label for="tx_hash_input">Please enter your Transaction hash:</label>
        <input type="text" id="tx-hash-input" name="tx_hash_input" size="80" />
        <button class="button save_order button-primary" type="button" id="verify-button">View Transaction Detail</button>
        <div style="text-align:center;" id="loading-indicator"><img class="loading" width="24px" height="24px" src="<?php echo LAQIRA_PLUGINS_URL; ?>assets/img/loading.svg"> </div>
    </form>
    <div id="laqirapay-confirmation-table"></div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#verify-button').on('click', function() {
                var inputValue = $('#tx-hash-input').val();
                $('#loading-indicator').show();
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        'action': 'laqirapay_view_confirmation_tx_hash',
                        'input_value': inputValue,
                    },
                    success: function(response) {
                        $('#laqirapay-confirmation-table').html(response);
                        $('#loading-indicator').hide();
                    },
                    error: function() {
                        // Handle error if needed
                        $('#loading-indicator').hide();
                    }
                });
            });
        });
    </script>
<?php
    return ob_get_clean();
}
add_shortcode('lqr_recovery', 'laqirapay_recovery_txHash_shortcode');

function laqirapay_view_confirmation_tx_hash()
{
    if (isset($_POST['input_value'])) {
        $input_value = sanitize_text_field($_POST['input_value']);

        $tx_results_direct = getTransactionInfo($input_value, function ($transaction) {
            return (decodeTransactionDirect($transaction->{"input"}));
        });

        $tx_results_inapp = getTransactionInfo($input_value, function ($transaction) {
            return (decodeTransactionInApp($transaction->{"input"}));
        });

        $tx_results_receipt = getTransactionRec($input_value, function ($transaction) {
            return ($transaction);
        });

        $tx_results_receipt = (array)$tx_results_receipt;


        if (isset($tx_results_direct) && is_array($tx_results_direct) && count($tx_results_direct) > 0) {
            $slippage_form_tx = intval($tx_results_direct["_slippage"] / 100);
            $provider_address_from_tx = $tx_results_direct["_provider"];
            $asset_address_from_tx = $tx_results_direct["_asset"];
            $price_from_tx = floatval($tx_results_direct["_price"] / 100);
            $req_hash_from_tx =  $tx_results_direct["_reqHash"];
        }

        if (isset($tx_results_inapp) && is_array($tx_results_inapp) && count($tx_results_inapp) > 0) {
            $slippage_form_tx = intval($tx_results_inapp["_slippage"] / 100);
            $provider_address_from_tx = $tx_results_inapp["_provider"];
            $asset_address_from_tx = $tx_results_inapp["_asset"];
            $price_from_tx = floatval($tx_results_inapp["_price"] / 100);
            $req_hash_from_tx =  $tx_results_inapp["_reqHash"];
        }

        if (isset($tx_results_receipt) && is_array($tx_results_receipt) && count($tx_results_receipt) > 0) {
            $user_wallet_address_form_tx = $tx_results_receipt["from"];
            $main_laqirapay_contract_from_tx = $tx_results_receipt["to"];
            $transaction_status_from_tx = $tx_results_receipt["status"];
        }


        $tx_hash_to_find = $input_value;
        $order_id = find_order_by_tx_hash($tx_hash_to_find);

        $original_provider = get_provider();
        echo '<div id="lqr-recover-order-result" class="info-box">';
        if ($original_provider == $provider_address_from_tx) {
            echo '<p><span class="dashicons dashicons-yes-alt" style="color:green;"></span>Provider Address confirmed</p>';

            if ($transaction_status_from_tx === '0x1') {
                echo '<p><span class="dashicons dashicons-yes-alt" style="color:green;"></span>The transaction status is compelete on blockchain</p>';
                if ($order_id) {
                    echo '<p><span class="dashicons dashicons-yes-alt" style="color:green;"></span>An order found</p>';
                    $order = wc_get_order(intval($order_id));
                    $order_recovery_status = get_option('woo_laqirapay_order_recovery_status');
                    $order_status = 'wc-' . $order->get_status();
                    if (($order_status != 'wc-completed') && ($order_status != $order_recovery_status)) {

                        $order_data_provider_address = $order->get_meta('AdminWalletAddress');
                        $order_data_user_wallet_address = $order->get_meta('userWallet');


                        $order_data_slippage = $order->get_meta('slippage');
                        $order_data_req_hash = $order->get_meta('reqHash');
                        $order_data_asset = $order->get_meta('asset');

                        if (($order_data_provider_address == $original_provider)
                            && ($order_data_slippage == $slippage_form_tx)
                            // &&   ($order_data_asset == $asset_address_from_tx)
                            // &&   ($order_data_req_hash == $req_hash_from_tx)
                        ) {


                            echo '<p><span class="dashicons dashicons-yes-alt" style="color:green;">
                            </span>
                            Order #' . $order_id . ' has been registered with this transaction hash. Order and transaction details are below. 
                            </p>';

                            echo "<hr><h4>Order Details:</h4>";
                            $output = '<table class="laqirapay-table">';
                            $output .= '<tr><th>Title </th><th>Value</th></tr>';

                            $output .= '<tr>';
                            $output .= '<td><strong>Order Status</strong></td>';
                            $output .= '<td>' . $order->get_status() . '</td>';
                            $output .= '</tr>';

                            $output .= '<tr>';
                            $output .= '<td><strong>Order Create Date</strong></td>';
                            $output .= '<td>' . format_date($order->get_date_created()) . '</td>';
                            $output .= '</tr>';

                            $output .= '<tr>';
                            $output .= '<td><strong>Order Modified Date</strong></td>';
                            $output .= '<td>' . format_date($order->get_date_modified()) . '</td>';
                            $output .= '</tr>';

                            $output .= '<tr>';
                            $output .= '<td><strong>Order Total Amount</strong></td>';
                            $output .= '<td>' . $order->get_total() . $order->get_currency() . '</td>';
                            $output .= '</tr>';

                            $output .= '</table>';

                            echo $output;

                            if (current_user_can('administrator') || get_current_user_id() == $order->get_user_id()) {
                                echo "<br><h4>Order Items:</h4>";
                                $order_items_output = '<table class="laqirapay-table">';
                                $order_items_output .= '<tr><th>Product ID </th><th>Name</th><th>Quantity</th><th>SubTotal</th></tr>';
                                foreach ($order->get_items() as $item_id => $item) {
                                    $product = $item->get_product();
                                    $order_items_output .= '<tr>';
                                    $order_items_output .= '<td>' . $item->get_product_id() . '</td>';
                                    $order_items_output .= '<td>' . $product->get_name() . '</td>';
                                    $order_items_output .= '<td>' . $item->get_quantity() . '</td>';
                                    $order_items_output .= '<td>' . wc_price($item->get_subtotal()) . '</td>';
                                    $order_items_output .= '</tr>';
                                }
                                $order_items_output .= '</table>';
                                echo $order_items_output;
                            }

                            echo "<hr><h4>Transaction Details:</h4>";
                            $output = '<table class="laqirapay-table">';
                            $output .= '<tr><th>Title </th><th>Value</th></tr>';

                            $output .= '<tr>';
                            $output .= '<td><strong>Transaction Hash</strong></td>';
                            $output .= '<td>' . $input_value . '</td>';
                            $output .= '</tr>';

                            $output .= '<tr>';
                            $output .= '<td><strong>From</strong></td>';
                            $output .= '<td>' . $user_wallet_address_form_tx . '</td>';
                            $output .= '</tr>';

                            $output .= '<tr>';
                            $output .= '<td><strong>To</strong></td>';
                            $output .= '<td>' . $main_laqirapay_contract_from_tx . '</td>';
                            $output .= '</tr>';

                            $output .= '<tr>';
                            $output .= '<td><strong>Provider</strong></td>';
                            $output .= '<td>' . $provider_address_from_tx . '</td>';
                            $output .= '</tr>';

                            $output .= '<tr>';
                            $output .= '<td><strong>Request Hash</strong></td>';
                            $output .= '<td>' . $req_hash_from_tx . '</td>';
                            $output .= '</tr>';

                            $output .= '<tr>';
                            $output .= '<td><strong>Order Amount</strong></td>';
                            $output .= '<td>' . $price_from_tx . ' $</td>';
                            $output .= '</tr>';

                            $output .= '</table>';

                            echo $output . '</div>';

                            echo  do_compelete_order($order_id);
                        } else {
                            echo '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>Your data not confirmed</p>';
                        }
                    } else {
                        echo '<p><span class="dashicons dashicons-info" style="color:orange;"></span>The order is already stable and does not require further confirmation</p>';
                    }
                } else {
                    echo '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>No order found with this Transaction hash</p>';
                }
            } else {
                echo '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>The transaction status is not Completed on Blockchain</p>';
            }
        } else {
            echo '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>Provider Address not Confirmed</p>';
        }
        echo '</div>';
    }
    wp_die();
}
add_action('wp_ajax_laqirapay_view_confirmation_tx_hash', 'laqirapay_view_confirmation_tx_hash');
add_action('wp_ajax_nopriv_laqirapay_view_confirmation_tx_hash', 'laqirapay_view_confirmation_tx_hash');

function do_compelete_order($order_id)
{
    ob_start();
    echo '<input type="hidden" id="order_id_input"  name="order_id_input" value="' . $order_id . '">';
?>

    <button class="button save_order button-primary" type="button" id="do-confirm-button">Confirm Order</button>
    <div id="laqirapay-after-confirmation-action"></div>
    <div style="text-align:center;" id="loading-indicator-bottom"><img class="loading" width="24px" height="24px" src="<?php echo LAQIRA_PLUGINS_URL; ?>assets/img/loading.svg"> </div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var order_id = $('#order_id_input').val();
            $('#do-confirm-button').on('click', function() {
                $('#loading-indicator-bottom').show();
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        'action': 'laqirapay_do_confim_tx_hash',
                        'orderID': order_id
                    },
                    success: function(response) {
                        if (response.data.result == "success") {
                            if (jQuery('#adminmenumain').length < 1) {
                                var redirectUrl = response.data.redirect;
                                window.location.replace(redirectUrl);
                            } else {
                                $('#laqirapay-after-confirmation-action').html(response.data.admin_result);
                                $('#lqr-recover-order-result').hide();
                                $('#do-confirm-button').hide();
                                $('#loading-indicator-bottom').hide();
                            }
                        } else {
                            $('#laqirapay-after-confirmation-action').html(response);
                            $('#loading-indicator-bottom').hide();
                        }

                    },
                    error: function() {
                        $('#loading-indicator-bottom').hide();
                    }
                });
            });
        });
    </script>

<?php
    return ob_get_clean();
}

add_action('wp_ajax_laqirapay_do_confim_tx_hash', 'laqirapay_do_confim_tx_hash');
add_action('wp_ajax_nopriv_laqirapay_do_confim_tx_hash', 'laqirapay_do_confim_tx_hash');

function laqirapay_do_confim_tx_hash()
{
    global $woocommerce;
    if (isset($_POST['orderID'])) {
        $order_id = intval($_POST['orderID']);
        $order = wc_get_order(intval($order_id));
        $payment_gateways = $woocommerce->payment_gateways->payment_gateways();
        $order->set_payment_method($payment_gateways['WC_woo_laqirapay']);
        $order_recovery_status = get_option('woo_laqirapay_order_recovery_status');
        $order->update_status($order_recovery_status, __('Order updated by TX hash confirmation method', 'woo-laqirapay'));
        $order->add_order_note(__('Order updated by TX hash confirmation method ', 'woo-laqirapay'));
        $order->save();


        global $wpdb;
        $table_name_laqira_transactions = $wpdb->prefix . "woo_laqira_transactions";
        $existing_row = $wpdb->get_row("SELECT * FROM $table_name_laqira_transactions WHERE wc_order_id = $order_id");
        $laqira_transactions = array(
            'wc_total_price' => $order->get_total(),
            'wc_currency' =>  get_woocommerce_currency(),
            'wc_order_id' => $order_id,
            'tx_hash' => $order->get_meta('tx_hash'),
            'token_address' => $order->get_meta('TokenAddress'),
            'token_name' => $order->get_meta('TokenName'),
            'token_amount' => $order->get_meta('TokenAmount'),
            'req_hash' => $order->get_meta('reqHash'),
            'tx_from' => $order->get_meta('CustomerWalletAddress'),
            'tx_to' => $order->get_meta('AdminWalletAddress')
        );

        if (null !== $existing_row) {
            $wpdb->update($table_name_laqira_transactions, $laqira_transactions, array('wc_order_id' => $order_id));
        } else {
            $wpdb->insert($table_name_laqira_transactions, $laqira_transactions);
        }

        $html = '
        <div class="info-box-center">
        <span class="dashicons dashicons-yes-alt" style="color:green;"></span>
        <h3>Order and Transaction confirmed and updated successfully.</h3>
        </div>
        ';
        // echo "Your Order and Transaction confirmed and updated successfully.";

        wp_send_json_success(['result' => 'success', 'redirect' => $order->get_checkout_order_received_url(), 'admin_result' => $html]);
    } else {
        echo '
        <div class="info-box-center">
        <span class="dashicons dashicons-dismiss" style="color:red;"></span>
        <h3>Order and Transaction not confirmed.</h3>
        </div>
        ';

        // wp_send_json_error(['result' => 'failed', 'data' => 'Your Order and Transaction not confirmed.']);
    }
    wp_die();
}


// Add this function to your theme's functions.php or a custom plugin

function find_order_by_tx_hash($tx_hash)
{
    // Define the meta key
    $meta_key = 'tx_hash';

    $args = array(
        'meta_key'      => 'tx_hash',
        'meta_value'    => $tx_hash,
        'meta_compare'  => '=',
        'return'        => 'ids'
    );

    $orders = wc_get_orders($args);

    // NOT empty
    if (!empty($orders)) {
        return $orders[0];
    } else {
        return null;
    }
}

function format_date($date_string)
{
    // Create DateTime object from the input date string
    $date = new DateTime($date_string);

    // Get the current time
    $now = new DateTime();

    // Calculate the difference between the two dates
    $interval = $now->diff($date);

    // Create a human-readable difference (e.g., "20 hrs ago")
    if ($interval->d > 0) {
        $difference = $interval->d . ' days ago';
    } elseif ($interval->h > 0) {
        $difference = $interval->h . ' hrs ago';
    } elseif ($interval->i > 0) {
        $difference = $interval->i . ' mins ago';
    } else {
        $difference = $interval->s . ' secs ago';
    }

    // Format the date to the desired output format
    $formatted_date = $date->format('M-d-Y h:i:s A');

    // Combine the difference and the formatted date
    $output = $difference . ' (' . $formatted_date . ')';

    return $output;
}
