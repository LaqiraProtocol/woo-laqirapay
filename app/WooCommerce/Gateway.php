<?php

namespace LaqiraPayments\WooCommerce;

use LaqiraPayments\Http\Controllers\Frontend\PaymentController;
use WC_Payment_Gateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Gateway extends WC_Payment_Gateway {

	public const GATEWAY_ID                = 'laqira_payments';
	public const LEGACY_GATEWAY_ID         = 'WC_laqira_payments';
	public const SETTINGS_OPTION_KEY       = 'woocommerce_laqira_payments_settings';
	public const LEGACY_SETTINGS_OPTION_KEY = 'woocommerce_WC_laqira_payments_settings';

	public $id = self::GATEWAY_ID;
	public $instructions;

	private PaymentController $controller;

	public function __construct( ?PaymentController $controller = null ) {
		$this->controller = $controller ?: new PaymentController();
		$this->maybe_migrate_legacy_settings();

		$this->method_title       = esc_html__( 'LaqiraPayments', 'laqira-payments' );
		$this->method_description = esc_html__( 'LaqiraPayments Woocommerce payment gateway', 'laqira-payments' );
		$this->has_fields         = true;
		$this->init_form_fields();
		$this->init_settings();

		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions', $this->description );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_filter( 'woocommerce_form_field', array( $this, 'laqira_payments_checkout_fields_in_label_error' ), 10, 4 );
		add_action( 'woocommerce_review_order_before_submit', array( $this, 'laqira_validation_checkout' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_validation_assets' ) );
	}

	public static function matches_payment_method( string $payment_method ): bool {
		return in_array( $payment_method, self::get_supported_gateway_ids(), true );
	}

	public static function get_supported_gateway_ids(): array {
		return array(
			self::GATEWAY_ID,
			self::LEGACY_GATEWAY_ID,
		);
	}

	private function maybe_migrate_legacy_settings(): void {
		$current_settings = get_option( self::SETTINGS_OPTION_KEY, null );
		$legacy_settings  = get_option( self::LEGACY_SETTINGS_OPTION_KEY, null );

		if ( ! is_array( $current_settings ) && is_array( $legacy_settings ) ) {
			update_option( self::SETTINGS_OPTION_KEY, $legacy_settings );
		}
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => esc_html__( 'Enable/Disable', 'laqira-payments' ),
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Enable Laqira Pay Payments', 'laqira-payments' ),
				'default' => 'yes',
			),
			'title'       => array(
				'title'       => esc_html__( 'Title', 'laqira-payments' ),
				'type'        => 'text',
				'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'laqira-payments' ),
				'default'     => esc_html__( 'Laqira Pay', 'laqira-payments' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => esc_html__( 'Description', 'laqira-payments' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'Payment method description that the customer will see on your checkout.', 'laqira-payments' ),
				'default'     => esc_html__( 'Pay with Crypto', 'laqira-payments' ),
				'desc_tip'    => true,
			),
		);
	}

	public function get_icon(): string {
		$iconHtml = sprintf(
			'<img src="%s" width="25" height="25" alt="%s" />',
			esc_url( LAQIRA_PLUGINS_URL . 'assets/img/icon-logo.png' ),
			esc_attr__( 'LaqiraPayments', 'laqira-payments' )
		);
		return apply_filters( 'woocommerce_gateway_icon', $iconHtml, $this->id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core hook.
	}

	public function payment_fields(): void {
		$this->controller->payment_fields();
	}

	public function process_payment( $order_id ): array {
		return $this->controller->process_payment( (int) $order_id );
	}

	public function enqueue_validation_assets(): void {
		if ( ! is_checkout() ) {
			return;
		}

		wp_enqueue_style(
			'laqira-payments-checkout-validation',
			LAQIRA_PLUGINS_URL . 'assets/public/css/checkout-validation.css',
			array(),
			LAQIRAPAYMENTS_VERSION
		);

		wp_enqueue_script(
			'laqira-payments-checkout-validation',
			LAQIRA_PLUGINS_URL . 'assets/public/js/checkout-validation.js',
			array( 'jquery' ),
			LAQIRAPAYMENTS_VERSION,
			true
		);
	}

	public function laqira_payments_checkout_fields_in_label_error( $field, $key, $args, $value ) {
		if ( $args['required'] && empty( $value ) ) {
			$field = str_replace( '<input', '<input class="required-error"', $field );
		}

		return $field;
	}

	public function laqira_validation_checkout(): void {
		echo '<div class="laqira-validation-placeholder"></div>';
	}
}
