<?php

namespace LaqiraPay\Support;

/**
 * Define the translation strings for this plugin.
 *
 * @link       https://laqira.io
 * @since      0.1.0
 * @package    LaqiraPay
 * @subpackage LaqiraPay/includes
 */

class LaqiraPayTranslations {

    /**
     * Get the translation strings.
     *
     * @since    1.0.0
     * @return array
     */
    public static function get_translations() {
        return array(
            "External Payment"=> esc_html__("External Payment","laqirapay"),
            "Internal Payment"=> esc_html__("Internal Payment","laqirapay"),
            "Transaction Amount"=> esc_html__("Transaction Amount","laqirapay"),
            "You need Charge to pay..."=> esc_html__("You need Charge to pay...","laqirapay"),
            "Search Asset"=> esc_html__("Search Asset","laqirapay"),
            "Order Amount" => esc_html__("Order Amount","laqirapay"),
            'Price Feed' => esc_html__("Price Feed","laqirapay"),
            'Select Network' => esc_html__("Select Network","laqirapay"),
            'Select Asset' => esc_html__("Select Asset","laqirapay"),
            'See Invoice' => esc_html__("See Invoice","laqirapay"),
            'Close' => esc_html__("Close","laqirapay"),
            'My Account Page' => esc_html__("My Account Page","laqirapay"),
            'Shop Page' => esc_html__("Shop Page","laqirapay"),
            'Home Page' => esc_html__("Home Page","laqirapay"),
            'Please fill all required address fields.' => esc_html__("Please fill all required address fields.","laqirapay"),
            'Laqira Pay Gateway' => esc_html__('Laqira Pay Gateway', 'laqirapay'),
            'Error' => esc_html__('Error', 'laqirapay'),
            'Payment Processing' => esc_html__('Payment Processing', 'laqirapay'),
            'Network' => esc_html__('Network', 'laqirapay'),
            'Asset' => esc_html__('Asset', 'laqirapay'),
            'Payment' => esc_html__('Payment', 'laqirapay'),
            'Result' => esc_html__('Result', 'laqirapay'),
            'Please select network' => esc_html__('Please select network', 'laqirapay'),
            'COPYRIGHT © 2024 Laqira Protocol, All rights Reserved' => esc_html__('COPYRIGHT © 2024 Laqira Protocol, All rights Reserved', 'laqirapay'),
            'Please select asset for' => esc_html__('Please select asset for', 'laqirapay'),
            'Please select network first' => esc_html__('Please select network first', 'laqirapay'),
            'Please select asset first' => esc_html__('Please select asset first', 'laqirapay'),
            'Order Details' => esc_html__('Order Details', 'laqirapay'),
            'Provider Address not found. please contact your provider...' => esc_html__('Provider Address not found. please contact your provider...', 'laqirapay'),
            'Your request is invalid. please contact your provider...' => esc_html__('Your request is invalid. please contact your provider...', 'laqirapay'),
            'Balance' => esc_html__('Balance', 'laqirapay'),
            'Provider Address is invalid' => esc_html__('Provider Address is invalid', 'laqirapay'),
            'Enter your order destails' => esc_html__('Enter your order destails', 'laqirapay'),
            'Selected Network' => esc_html__('Selected Network', 'laqirapay'),
            'Selected Asset' => esc_html__('Selected Asset', 'laqirapay'),
            'Enter Amount In USDT' => esc_html__('Enter Amount In USDT', 'laqirapay'),
            'Approximate Amount' => esc_html__('Approximate Amount', 'laqirapay'),
            'Please Enter Your Order Note' => esc_html__('Please Enter Your Order Note', 'laqirapay'),
            'The approximate payment based on chosen Asset and Decentralized price feeds.' => esc_html__('The approximate payment based on chosen Asset and Decentralized price feeds.', 'laqirapay'),
            'Remaining' => esc_html__('Remaining', 'laqirapay'),
            'Slippage Tolerance' => esc_html__('Slippage Tolerance', 'laqirapay'),
            'Setting a high slippage tolerance can help transactions succeed, Increase Slippage the value to enhance the acceptance rate of seamless alignment.' => esc_html__('Setting a high slippage tolerance can help transactions succeed, Increase Slippage the value to enhance the acceptance rate of seamless alignment.', 'laqirapay'),
            'Select Payment Method' => esc_html__('Select Payment Method', 'laqirapay'),
            'External' => esc_html__('External', 'laqirapay'),
            'Internal' => esc_html__('Internal', 'laqirapay'),
            'Pay' => esc_html__('Pay', 'laqirapay'),
            'Your Balance' => esc_html__('Your Balance', 'laqirapay'),
            'Loading' => esc_html__('Loading', 'laqirapay'),
            'Loading ...' => esc_html__('Loading ...', 'laqirapay'),
            'Loading Networks ...' => esc_html__('Loading Networks ...', 'laqirapay'),
            'Error loading networks' => esc_html__('Error loading networks', 'laqirapay'),
            'No networks available' => esc_html__('No networks available', 'laqirapay'),
            'Loading Assets ...' => esc_html__('Loading Assets ...', 'laqirapay'),
            'Error loading Assets' => esc_html__('Error loading Assets', 'laqirapay'),
            'Please use at most 20 characters' => esc_html__('Please use at most 20 characters', 'laqirapay'),
            'Approve successfully Done!' => esc_html__('Approve successfully Done!', 'laqirapay'),
            'Error on Approve' => esc_html__('Error on Approve', 'laqirapay'),
            'Unfortunately, we encountered an error. Please try again or contact the administrator' => esc_html__('Unfortunately, we encountered an error. Please try again or contact the administrator', 'laqirapay'),
            'Request was not successful' => esc_html__('Request was not successful', 'laqirapay'),
            'Transaction Done Successfully' => esc_html__('Transaction Done Successfully', 'laqirapay'),
            'Transaction Done but not Confirmed. use recovery mode' => esc_html__('Transaction Done but not Confirmed. use recovery mode', 'laqirapay'),
            'Transaction Reverted. Please try again ...' => esc_html__('Transaction Reverted. Please try again ...', 'laqirapay'),
            'Transaction Failed. Please try again ...' => esc_html__('Transaction Failed. Please try again ...', 'laqirapay'),
            'Transaction not found on Blockchain' => esc_html__('Transaction not found on Blockchain', 'laqirapay'),
            'Error checking transaction status. Please try again ...' => esc_html__('Error checking transaction status. Please try again ...', 'laqirapay'),
            'Product Name' => esc_html__('Product Name', 'laqirapay'),
            'Or Enter Slippage' => esc_html__('Or Enter Slippage', 'laqirapay'),
            'Or Enter Slippage (only 0.6% to 2%)' => esc_html__('Or Enter Slippage (only 0.6% to 2%)', 'laqirapay'),
            'Approve' => esc_html__('Approve', 'laqirapay'),
            'You need Charge to pay ...' => esc_html__('You need Charge to pay ...', 'laqirapay'),
            'Sharing was successful' => esc_html__('Sharing was successful', 'laqirapay'),
            'Sharing error' => esc_html__('Sharing error', 'laqirapay'),
            'Your browser does not support native sharing' => esc_html__('Your browser does not support native sharing', 'laqirapay'),
            'Congratulation' => esc_html__('Congratulation', 'laqirapay'),
            'Your order received successfully' => esc_html__('Your order received successfully', 'laqirapay'),
            'Show Order' => esc_html__('Show Order', 'laqirapay'),
            'Copy to clipboard' => esc_html__('Copy to clipboard', 'laqirapay'),
            'Share' => esc_html__('Share', 'laqirapay'),
            'Share your order' => esc_html__('Share your order', 'laqirapay'),
            'Assets' => esc_html__('Assets', 'laqirapay'),
            'External Pay' => esc_html__('External Pay', 'laqirapay'),
            'Internal Pay' => esc_html__('Internal Pay', 'laqirapay'),
            'You have the option to make payment through a connected wallet on the Website or Utilize your Internal account for transactions' => esc_html__('You have the option to make payment through a connected wallet on the Website or Utilize your Internal account for transactions', 'laqirapay'),
            'Payment through a connected wallet' => esc_html__('Payment through a connected wallet', 'laqirapay'),
            'Payment Utilize your Internal account' => esc_html__('Payment Utilize your Internal account', 'laqirapay'),
            'More' => esc_html__('More', 'laqirapay'),
            'Exchange Rate' => esc_html__('Exchange Rate', 'laqirapay'),
            'Please Enter correct email address' =>  esc_html__('Please Enter correct email address', 'laqirapay'),
            'Please connect your wallet to continue' =>  esc_html__('Please connect your wallet to continue', 'laqirapay'),
            'Connect wallet' => esc_html__('Connect wallet', 'laqirapay'),
            'Order Processing' =>  esc_html__('Order Processing', 'laqirapay'),
            'You must accept the Terms & Conditions before proceeding.' =>  esc_html__('You must accept the Terms & Conditions before proceeding.', 'laqirapay'),
            'User rejected the transaction.' =>  esc_html__('User rejected the transaction.', 'laqirapay'),
            'Requested resource not available.Please try again and wait to load data.' =>  esc_html__('Requested resource not available.Please try again and wait to load data.', 'laqirapay'),
            'An error occurred in server on save TxHash to order' => esc_html__('An error occurred in server on save TxHash to order', 'laqirapay'),
            'Contract error, please contact to administrator.' =>  esc_html__('Contract error, please contact to administrator.', 'laqirapay'),
            'Network error, please contact to administrator.' =>  esc_html__('Network error, please contact to administrator.', 'laqirapay'),
            'Unknown error, please contact to administrator.' =>  esc_html__('Unknown error, please contact to administrator.', 'laqirapay'),
            'Transaction took too long' =>  esc_html__('Transaction took too long', 'laqirapay'),
            'Insufficient gas' =>  esc_html__('Insufficient gas', 'laqirapay'),
            'Error checking funds:' =>  esc_html__('Error checking funds:', 'laqirapay'),

            'Insufficient balance for token' =>  esc_html__('Insufficient balance for token', 'laqirapay'),
            'Insufficient balance for gas cost' =>  esc_html__('Insufficient balance for gas cost', 'laqirapay'),
            'Insufficient balance for transaction and gas cost' =>  esc_html__('Insufficient balance for transaction and gas cost', 'laqirapay'),

            'insufficient funds for gas' =>  esc_html__('insufficient funds for gas', 'laqirapay'),
            'No active networks available. Please contact the site administrator.' =>  esc_html__('No active networks available. Please contact the site administrator.', 'laqirapay'),

            'To proceed with your payment, you must first obtain a payment authorization. Please initiate the transaction by clicking on "Approve" Once the authorization is successfully issued, kindly return to this section to complete your payment.
' =>  esc_html__('To proceed with your payment, you must first obtain a payment authorization. Please initiate the transaction by clicking on "Approve" Once the authorization is successfully issued, kindly return to this section to complete your payment.
', 'laqirapay'),

        );
    }
}
