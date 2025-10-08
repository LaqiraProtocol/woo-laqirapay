<?php

namespace LaqiraPay\WooCommerce;

use LaqiraPay\Http\Controllers\Frontend\PaymentController;
use WC_Payment_Gateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Gateway extends WC_Payment_Gateway {

	public $id = 'WC_laqirapay';
	public $instructions;

	private PaymentController $controller;

	public function __construct( ?PaymentController $controller = null ) {
		$this->controller = $controller ?: new PaymentController();

		$this->method_title       = esc_html__( 'LaqiraPay', 'laqirapay' );
		$this->method_description = esc_html__( 'LaqiraPay Woocommerce payment gateway', 'laqirapay' );
		$this->has_fields         = true;
		$this->init_form_fields();
		$this->init_settings();

		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions', $this->description );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_filter( 'woocommerce_form_field', array( $this, 'laqirapay_checkout_fields_in_label_error' ), 10, 4 );
		add_action( 'woocommerce_review_order_before_submit', array( $this, 'laqira_validation_checkout' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_validation_assets' ) );
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => esc_html__( 'Enable/Disable', 'laqirapay' ),
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Enable Laqira Pay Payments', 'laqirapay' ),
				'default' => 'yes',
			),
			'title'       => array(
				'title'       => esc_html__( 'Title', 'laqirapay' ),
				'type'        => 'text',
				'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'laqirapay' ),
				'default'     => esc_html__( 'Laqira Pay', 'laqirapay' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => esc_html__( 'Description', 'laqirapay' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'Payment method description that the customer will see on your checkout.', 'laqirapay' ),
				'default'     => esc_html__( 'Pay with Crypto', 'laqirapay' ),
				'desc_tip'    => true,
			),
		);
	}

	public function get_icon(): string {
		$iconHtml = '<img src="https://s2.coinmarketcap.com/static/img/coins/64x64/14446.png" width="25" height="25">';
		return apply_filters( 'woocommerce_gateway_icon', $iconHtml, $this->id );
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
			'laqirapay-checkout-validation',
			LAQIRA_PLUGINS_URL . 'assets/public/css/checkout-validation.css',
			array(),
			LAQIRAPAY_VERSION
		);

		wp_enqueue_script(
			'laqirapay-checkout-validation',
			LAQIRA_PLUGINS_URL . 'assets/public/js/checkout-validation.js',
			array( 'jquery' ),
			LAQIRAPAY_VERSION,
			true
		);
	}

	public function laqirapay_checkout_fields_in_label_error( $field, $key, $args, $value ) {
		if ( $args['required'] && empty( $value ) ) {
			$field = str_replace( '<input', '<input class="required-error"', $field );
		}

		return $field;
	}

	public function laqira_validation_checkout(): void {
		echo '<div class="laqira-validation-placeholder"></div>';
	}
}
