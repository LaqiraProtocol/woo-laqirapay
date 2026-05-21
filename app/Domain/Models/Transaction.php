<?php

namespace LaqiraPayments\Domain\Models;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



/**
 * Data model representing a blockchain transaction.
 */
class Transaction {

	private ?int $orderId;
	private ?float $amount;
	private ?string $fromAddress;
	private ?string $toAddress;
	private ?string $txHash;
	private ?string $status;
	private ?string $nonce;

	public function __construct(
		?int $orderId = null,
		?float $amount = null,
		?string $fromAddress = null,
		?string $toAddress = null,
		?string $txHash = null,
		?string $status = null,
		?string $nonce = null
	) {
		$this->orderId     = $orderId;
		$this->amount      = $amount;
		$this->fromAddress = $fromAddress;
		$this->toAddress   = $toAddress;
		$this->txHash      = $txHash;
		$this->status      = $status;
		$this->nonce       = $nonce;
	}

	/**
	 * Create a transaction instance from an array (e.g. $_POST data).
	 */
	public static function fromArray( array $data ): self {
		// Sanitize incoming data before assignment
		$orderId     = isset( $data['orderID'] ) ? intval( sanitize_text_field( $data['orderID'] ) ) : null;
		$amount      = isset( $data['amount'] ) ? floatval( sanitize_text_field( $data['amount'] ) ) : null;
		$fromAddress = isset( $data['fromAddress'] )
			? sanitize_text_field( $data['fromAddress'] )
			: ( isset( $data['from'] ) ? sanitize_text_field( $data['from'] ) : null );
		$toAddress   = isset( $data['toAddress'] )
			? sanitize_text_field( $data['toAddress'] )
			: ( isset( $data['to'] ) ? sanitize_text_field( $data['to'] ) : null );
		$txHash      = isset( $data['txHash'] ) ? sanitize_text_field( $data['txHash'] ) : null;
		$status      = isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : null;
		$nonce       = isset( $data['nonce'] ) ? sanitize_text_field( $data['nonce'] ) : null;

		return new self(
			$orderId,
			$amount,
			$fromAddress,
			$toAddress,
			$txHash,
			$status,
			$nonce
		);
	}

	/**
	 * Create a transaction instance from the current HTTP POST request.
	 */
	public static function fromRequest(): self {
		$data = array();
		foreach ( array( 'orderID', 'amount', 'fromAddress', 'from', 'toAddress', 'to', 'txHash', 'status', 'nonce' ) as $key ) {
			$value = filter_input( INPUT_POST, $key, FILTER_UNSAFE_RAW ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Value object hydration; nonce is validated by request handlers before use.
			if ( is_scalar( $value ) ) {
				$data[ $key ] = $value;
			}
		}

		return self::fromArray( $data );
	}

	public function getOrderId(): ?int {
		return $this->orderId;
	}

	public function getAmount(): ?float {
		return $this->amount;
	}

	public function getFromAddress(): ?string {
		return $this->fromAddress;
	}

	public function getToAddress(): ?string {
		return $this->toAddress;
	}

	public function getTxHash(): ?string {
		return $this->txHash;
	}

	public function getStatus(): ?string {
		return $this->status;
	}

	public function getNonce(): ?string {
		return $this->nonce;
	}

	public function toArray(): array {
		return array(
			'orderID'     => $this->orderId,
			'amount'      => $this->amount,
			'fromAddress' => $this->fromAddress,
			'toAddress'   => $this->toAddress,
			'txHash'      => $this->txHash,
			'status'      => $this->status,
			'nonce'       => $this->nonce,
		);
	}
}
