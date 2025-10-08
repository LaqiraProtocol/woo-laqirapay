<?php

namespace LaqiraPay\Domain\Models;

/**
 * Value object representing a single log record.
 */
class LogRecord {

	private ?int $id;
	private int $level;
	private string $type;
	private array $context;
	private ?string $createdAt;

	public function __construct(
		int $level,
		string $type,
		array $context = array(),
		?int $id = null,
		?string $createdAt = null
	) {
		$this->level     = $level;
		$this->type      = $type;
		$this->context   = $context;
		$this->id        = $id;
		$this->createdAt = $createdAt;
	}

	/**
	 * Create a log record instance from an associative array.
	 */
	public static function fromArray( array $data ): self {
		$context = $data['context'] ?? array();
		if ( is_string( $context ) ) {
			$decoded = json_decode( $context, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$context = $decoded;
			} else {
				$context = array();
			}
		}

		return new self(
			isset( $data['level'] ) ? (int) $data['level'] : 0,
			isset( $data['type'] ) ? (string) $data['type'] : '',
			is_array( $context ) ? $context : array(),
			isset( $data['id'] ) ? (int) $data['id'] : null,
			$data['created_at'] ?? null
		);
	}

	/**
	 * Export the log record as an associative array.
	 */
	public function toArray(): array {
		return array(
			'id'         => $this->id,
			'level'      => $this->level,
			'type'       => $this->type,
			'context'    => $this->context,
			'created_at' => $this->createdAt,
		);
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getLevel(): int {
		return $this->level;
	}

	public function getType(): string {
		return $this->type;
	}

	public function getContext(): array {
		return $this->context;
	}

	public function getCreatedAt(): ?string {
		return $this->createdAt;
	}
}
