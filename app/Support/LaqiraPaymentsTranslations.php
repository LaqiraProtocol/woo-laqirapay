<?php

namespace LaqiraPayments\Support;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



/**
 * Define the translation strings for this plugin.
 *
 * @link       https://laqira.io
 * @since      0.1.0
 * @package    LaqiraPayments
 * @subpackage LaqiraPayments/includes
 */

class LaqiraPaymentsTranslations {

	/**
	 * Get the translation strings.
	 *
	 * @since    1.0.0
	 * @return array
	 */
	public static function get_translations() {
		return array(
			'External Payment'                             => esc_html__( 'External Payment', 'laqira-payments' ),
			'Internal Payment'                             => esc_html__( 'Internal Payment', 'laqira-payments' ),
			'Transaction Amount'                           => esc_html__( 'Transaction Amount', 'laqira-payments' ),
			'You need Charge to pay...'                    => esc_html__( 'You need Charge to pay...', 'laqira-payments' ),
			'Search Asset'                                 => esc_html__( 'Search Asset', 'laqira-payments' ),
			'Order Amount'                                 => esc_html__( 'Order Amount', 'laqira-payments' ),
			'Price Feed'                                   => esc_html__( 'Price Feed', 'laqira-payments' ),
			'Select Network'                               => esc_html__( 'Select Network', 'laqira-payments' ),
			'Select Asset'                                 => esc_html__( 'Select Asset', 'laqira-payments' ),
			'See Invoice'                                  => esc_html__( 'See Invoice', 'laqira-payments' ),
			'Close'                                        => esc_html__( 'Close', 'laqira-payments' ),
			'My Account Page'                              => esc_html__( 'My Account Page', 'laqira-payments' ),
			'Shop Page'                                    => esc_html__( 'Shop Page', 'laqira-payments' ),
			'Home Page'                                    => esc_html__( 'Home Page', 'laqira-payments' ),
			'Please fill all required address fields.'     => esc_html__( 'Please fill all required address fields.', 'laqira-payments' ),
			'Laqira Pay Gateway'                           => esc_html__( 'Laqira Pay Gateway', 'laqira-payments' ),
			'Error'                                        => esc_html__( 'Error', 'laqira-payments' ),
			'Payment Processing'                           => esc_html__( 'Payment Processing', 'laqira-payments' ),
			'Network'                                      => esc_html__( 'Network', 'laqira-payments' ),
			'Asset'                                        => esc_html__( 'Asset', 'laqira-payments' ),
			'Payment'                                      => esc_html__( 'Payment', 'laqira-payments' ),
			'Result'                                       => esc_html__( 'Result', 'laqira-payments' ),
			'Please select network'                        => esc_html__( 'Please select network', 'laqira-payments' ),
			'COPYRIGHT © 2024 Laqira Protocol, All rights Reserved' => esc_html__( 'COPYRIGHT © 2024 Laqira Protocol, All rights Reserved', 'laqira-payments' ),
			'Please select asset for'                      => esc_html__( 'Please select asset for', 'laqira-payments' ),
			'Please select network first'                  => esc_html__( 'Please select network first', 'laqira-payments' ),
			'Please select asset first'                    => esc_html__( 'Please select asset first', 'laqira-payments' ),
			'Order Details'                                => esc_html__( 'Order Details', 'laqira-payments' ),
			'Provider Address not found. please contact your provider...' => esc_html__( 'Provider Address not found. please contact your provider...', 'laqira-payments' ),
			'Your request is invalid. please contact your provider...' => esc_html__( 'Your request is invalid. please contact your provider...', 'laqira-payments' ),
			'Balance'                                      => esc_html__( 'Balance', 'laqira-payments' ),
			'Provider Address is invalid'                  => esc_html__( 'Provider Address is invalid', 'laqira-payments' ),
			'Enter your order destails'                    => esc_html__( 'Enter your order destails', 'laqira-payments' ),
			'Selected Network'                             => esc_html__( 'Selected Network', 'laqira-payments' ),
			'Selected Asset'                               => esc_html__( 'Selected Asset', 'laqira-payments' ),
			'Enter Amount In USDT'                         => esc_html__( 'Enter Amount In USDT', 'laqira-payments' ),
			'Approximate Amount'                           => esc_html__( 'Approximate Amount', 'laqira-payments' ),
			'Please Enter Your Order Note'                 => esc_html__( 'Please Enter Your Order Note', 'laqira-payments' ),
			'The approximate payment based on chosen Asset and Decentralized price feeds.' => esc_html__( 'The approximate payment based on chosen Asset and Decentralized price feeds.', 'laqira-payments' ),
			'Remaining'                                    => esc_html__( 'Remaining', 'laqira-payments' ),
			'Slippage Tolerance'                           => esc_html__( 'Slippage Tolerance', 'laqira-payments' ),
			'Setting a high slippage tolerance can help transactions succeed, Increase Slippage the value to enhance the acceptance rate of seamless alignment.' => esc_html__( 'Setting a high slippage tolerance can help transactions succeed, Increase Slippage the value to enhance the acceptance rate of seamless alignment.', 'laqira-payments' ),
			'Select Payment Method'                        => esc_html__( 'Select Payment Method', 'laqira-payments' ),
			'External'                                     => esc_html__( 'External', 'laqira-payments' ),
			'Internal'                                     => esc_html__( 'Internal', 'laqira-payments' ),
			'Pay'                                          => esc_html__( 'Pay', 'laqira-payments' ),
			'Your Balance'                                 => esc_html__( 'Your Balance', 'laqira-payments' ),
			'Loading'                                      => esc_html__( 'Loading', 'laqira-payments' ),
			'Loading ...'                                  => esc_html__( 'Loading ...', 'laqira-payments' ),
			'Loading Networks ...'                         => esc_html__( 'Loading Networks ...', 'laqira-payments' ),
			'Error loading networks'                       => esc_html__( 'Error loading networks', 'laqira-payments' ),
			'No networks available'                        => esc_html__( 'No networks available', 'laqira-payments' ),
			'Loading Assets ...'                           => esc_html__( 'Loading Assets ...', 'laqira-payments' ),
			'Error loading Assets'                         => esc_html__( 'Error loading Assets', 'laqira-payments' ),
			'Please use at most 20 characters'             => esc_html__( 'Please use at most 20 characters', 'laqira-payments' ),
			'Approve successfully Done!'                   => esc_html__( 'Approve successfully Done!', 'laqira-payments' ),
			'Error on Approve'                             => esc_html__( 'Error on Approve', 'laqira-payments' ),
			'Unfortunately, we encountered an error. Please try again or contact the administrator' => esc_html__( 'Unfortunately, we encountered an error. Please try again or contact the administrator', 'laqira-payments' ),
			'Request was not successful'                   => esc_html__( 'Request was not successful', 'laqira-payments' ),
			'Transaction Done Successfully'                => esc_html__( 'Transaction Done Successfully', 'laqira-payments' ),
			'Transaction Done but not Confirmed. use recovery mode' => esc_html__( 'Transaction Done but not Confirmed. use recovery mode', 'laqira-payments' ),
			'Transaction Reverted. Please try again ...'   => esc_html__( 'Transaction Reverted. Please try again ...', 'laqira-payments' ),
			'Transaction Failed. Please try again ...'     => esc_html__( 'Transaction Failed. Please try again ...', 'laqira-payments' ),
			'Transaction not found on Blockchain'          => esc_html__( 'Transaction not found on Blockchain', 'laqira-payments' ),
			'Error checking transaction status. Please try again ...' => esc_html__( 'Error checking transaction status. Please try again ...', 'laqira-payments' ),
			'Product Name'                                 => esc_html__( 'Product Name', 'laqira-payments' ),
			'Or Enter Slippage'                            => esc_html__( 'Or Enter Slippage', 'laqira-payments' ),
			'Or Enter Slippage (only 0.6% to 2%)'          => esc_html__( 'Or Enter Slippage (only 0.6% to 2%)', 'laqira-payments' ),
			'Approve'                                      => esc_html__( 'Approve', 'laqira-payments' ),
			'You need Charge to pay ...'                   => esc_html__( 'You need Charge to pay ...', 'laqira-payments' ),
			'Sharing was successful'                       => esc_html__( 'Sharing was successful', 'laqira-payments' ),
			'Sharing error'                                => esc_html__( 'Sharing error', 'laqira-payments' ),
			'Your browser does not support native sharing' => esc_html__( 'Your browser does not support native sharing', 'laqira-payments' ),
			'Congratulation'                               => esc_html__( 'Congratulation', 'laqira-payments' ),
			'Your order received successfully'             => esc_html__( 'Your order received successfully', 'laqira-payments' ),
			'Show Order'                                   => esc_html__( 'Show Order', 'laqira-payments' ),
			'Copy to clipboard'                            => esc_html__( 'Copy to clipboard', 'laqira-payments' ),
			'Share'                                        => esc_html__( 'Share', 'laqira-payments' ),
			'Share your order'                             => esc_html__( 'Share your order', 'laqira-payments' ),
			'Assets'                                       => esc_html__( 'Assets', 'laqira-payments' ),
			'External Pay'                                 => esc_html__( 'External Pay', 'laqira-payments' ),
			'Internal Pay'                                 => esc_html__( 'Internal Pay', 'laqira-payments' ),
			'You have the option to make payment through a connected wallet on the Website or Utilize your Internal account for transactions' => esc_html__( 'You have the option to make payment through a connected wallet on the Website or Utilize your Internal account for transactions', 'laqira-payments' ),
			'Payment through a connected wallet'           => esc_html__( 'Payment through a connected wallet', 'laqira-payments' ),
			'Payment Utilize your Internal account'        => esc_html__( 'Payment Utilize your Internal account', 'laqira-payments' ),
			'More'                                         => esc_html__( 'More', 'laqira-payments' ),
			'Exchange Rate'                                => esc_html__( 'Exchange Rate', 'laqira-payments' ),
			'Please Enter correct email address'           => esc_html__( 'Please Enter correct email address', 'laqira-payments' ),
			'Please connect your wallet to continue'       => esc_html__( 'Please connect your wallet to continue', 'laqira-payments' ),
			'Connect wallet'                               => esc_html__( 'Connect wallet', 'laqira-payments' ),
			'Order Processing'                             => esc_html__( 'Order Processing', 'laqira-payments' ),
			'You must accept the Terms & Conditions before proceeding.' => esc_html__( 'You must accept the Terms & Conditions before proceeding.', 'laqira-payments' ),
			'User rejected the transaction.'               => esc_html__( 'User rejected the transaction.', 'laqira-payments' ),
			'Requested resource not available.Please try again and wait to load data.' => esc_html__( 'Requested resource not available.Please try again and wait to load data.', 'laqira-payments' ),
			'An error occurred in server on save TxHash to order' => esc_html__( 'An error occurred in server on save TxHash to order', 'laqira-payments' ),
			'Contract error, please contact to administrator.' => esc_html__( 'Contract error, please contact to administrator.', 'laqira-payments' ),
			'Network error, please contact to administrator.' => esc_html__( 'Network error, please contact to administrator.', 'laqira-payments' ),
			'Unknown error, please contact to administrator.' => esc_html__( 'Unknown error, please contact to administrator.', 'laqira-payments' ),
			'Transaction took too long'                    => esc_html__( 'Transaction took too long', 'laqira-payments' ),
			'Insufficient gas'                             => esc_html__( 'Insufficient gas', 'laqira-payments' ),
			'Error checking funds:'                        => esc_html__( 'Error checking funds:', 'laqira-payments' ),

			'Insufficient balance for token'               => esc_html__( 'Insufficient balance for token', 'laqira-payments' ),
			'Insufficient balance for gas cost'            => esc_html__( 'Insufficient balance for gas cost', 'laqira-payments' ),
			'Insufficient balance for transaction and gas cost' => esc_html__( 'Insufficient balance for transaction and gas cost', 'laqira-payments' ),

			'insufficient funds for gas'                   => esc_html__( 'insufficient funds for gas', 'laqira-payments' ),
			'No active networks available. Please contact the site administrator.' => esc_html__( 'No active networks available. Please contact the site administrator.', 'laqira-payments' ),

			'To proceed with your payment, you must first obtain a payment authorization. Please initiate the transaction by clicking on "Approve" Once the authorization is successfully issued, kindly return to this section to complete your payment.
'                                                          => esc_html__(
				'To proceed with your payment, you must first obtain a payment authorization. Please initiate the transaction by clicking on "Approve" Once the authorization is successfully issued, kindly return to this section to complete your payment.
',
				'laqira-payments'
			),

		);
	}
}
