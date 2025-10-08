<?php

namespace LaqiraPay\Support;

use kornrunner\Keccak;

/**
 * Class for decoding Ethereum contract data based on its ABI.
 *
 * @since      0.1.0
 * @package    LaqiraPay
 * @subpackage LaqiraPay/includes
 * @author     Laqira Protocol <info@laqira.io>
 */
class LaqiraPayAbiDecoder {

	protected $abi = array();

	public function __construct( array $abi ) {
		$this->abi = $abi;
	}

	public function decode_contract_data( $data ) {
		$data           = is_string( $data ) ? $data : '';
		$data           = trim( $data );
		$data_buf       = hex2bin( str_replace( '0x', '', $data ) );
		$method_id      = bin2hex( substr( $data_buf, 0, 4 ) );
		$inputs_buf_bin = substr( $data_buf, 4 );
		$inputs_buf     = bin2hex( $inputs_buf_bin );
		$result         = array_reduce(
			$this->abi,
			function ( $acc, $obj ) use ( $method_id, $inputs_buf, $inputs_buf_bin ) {
				if ( $obj->type === 'constructor' ) {
					return $acc;
				}
				$name  = isset( $obj->name ) ? $obj->name : 'null';
				$types = isset( $obj->inputs ) ? array_map(
					function ( $input ) {
						return $input->type;
					},
					$obj->inputs
				) : array();
				$names = isset( $obj->inputs ) ? array_map(
					function ( $input ) {
						return $input->name;
					},
					$obj->inputs
				) : array();
				$hash  = $this->method_id( $name, $types );

				if ( $hash === $method_id ) {
					if ( $method_id === 'a9059cbb' ) {
						$inputs_buf = str_repeat( '00', 12 ) . bin2hex( substr( $inputs_buf_bin, 12, 32 - 12 ) ) . bin2hex( substr( $inputs_buf_bin, 32 ) );
					}
					$inputs         = $this->raw_decode( $types, $inputs_buf, $inputs_buf_bin );
					$return         = new \stdClass();
					$return->name   = $name;
					$return->names  = $names;
					$return->types  = $types;
					$return->inputs = $inputs;

					return $return;
				}
				return $acc;
			}
		);
		return $result;
	}

	public function convert_inputs( $inputs, $types, $names ) {
		$result = array();
		if ( ! is_array( $inputs ) || ! is_array( $types ) || ! is_array( $names ) || count( $inputs ) !== count( $types ) || count( $inputs ) !== count( $names ) ) {
			return $result;
		}

		foreach ( $inputs as $index => $input ) {
			$type = $types[ $index ] ?? 'unknown';
			$name = $names[ $index ] ?? 'unnamed';

			switch ( $type ) {
				case 'uint256':
					$value = hexdec( $input );
					break;
				case 'address':
					$trimmed_input = substr( $input, -40 );
					$value         = '0x' . $trimmed_input;
					break;
				case 'bytes32':
					$value = '0x' . $input;
					break;
				default:
					$value = hex2bin( $input );
					break;
			}
			$result[ $name ] = $value;
		}
		return $result;
	}

	public function decode_input( $input ) {
		$decoded = $this->decode_contract_data( $input );
		if ( ! $decoded || ! isset( $decoded->inputs, $decoded->types, $decoded->names ) ) {
			return array();
		}

		$array = json_decode( json_encode( $decoded ), true );
		return $this->convert_inputs( $array['inputs'], $array['types'], $array['names'] );
	}

	public static function event_id( $name, $types ) {
		$types = implode( ',', array_map( array( __CLASS__, 'elementary_name' ), $types ) );
		$sig   = $name . '(' . $types . ')';
		return Keccak::hash( $sig, 256 );
	}

	public static function method_id( $name, $types ) {
		return substr( self::event_id( $name, $types ), 0, 8 );
	}

	public static function raw_decode( $types, $data, $data_bin ) {
		if ( ! is_array( $types ) || empty( $types ) ) {
			return array();
		}

		$ret    = array();
		$offset = 0;
		foreach ( $types as $t ) {
			$type    = self::elementary_name( $t );
			$parsed  = self::parse_type( $type );
			$decoded = self::decode_single( $parsed, $data, $data_bin, $offset );
			$offset += $parsed->memory_usage;
			$ret[]   = $decoded;
		}
		return $ret;
	}

	public static function elementary_name( $name ) {
		if ( strpos( $name, 'int[' ) === 0 ) {
			return 'int256' . substr( $name, 3 );
		} elseif ( $name === 'int' ) {
			return 'int256';
		} elseif ( strpos( $name, 'uint[' ) === 0 ) {
			return 'uint256' . substr( $name, 4 );
		} elseif ( $name === 'uint' ) {
			return 'uint256';
		} elseif ( strpos( $name, 'fixed[' ) === 0 ) {
			return 'fixed128x128' . substr( $name, 5 );
		} elseif ( $name === 'fixed' ) {
			return 'fixed128x128';
		} elseif ( strpos( $name, 'ufixed[' ) === 0 ) {
			return 'ufixed128x128' . substr( $name, 6 );
		} elseif ( $name === 'ufixed' ) {
			return 'ufixed128x128';
		}
		return $name;
	}

	public static function parse_type( $type ) {
		$size = null;
		$ret  = null;
		if ( is_array( $type ) ) {
			$size              = self::parse_type_array( $type );
			$sub_array         = substr( $type, 0, strrpos( $type, '[' ) );
			$sub_array         = self::parse_type( $sub_array );
			$ret               = new \stdClass();
			$ret->is_array     = true;
			$ret->name         = $type;
			$ret->size         = $size;
			$ret->memory_usage = ( $size === 'dynamic' ) ? 32 : $sub_array->memory_usage * $size;
			$ret->sub_array    = $sub_array;
			return $ret;
		} else {
			$raw_type = null;
			switch ( $type ) {
				case 'address':
					$raw_type = 'uint160';
					break;
				case 'bool':
					$raw_type = 'uint8';
					break;
				case 'string':
					$raw_type = 'bytes';
					break;
			}
			$ret               = new \stdClass();
			$ret->is_array     = false;
			$ret->raw_type     = $raw_type;
			$ret->name         = $type;
			$ret->memory_usage = 32;

			if ( strpos( $type, 'bytes' ) === 0 && $type !== 'bytes' || strpos( $type, 'uint' ) === 0 || strpos( $type, 'int' ) === 0 ) {
				$ret->size = self::parse_type_n( $type );
			} elseif ( strpos( $type, 'ufixed' ) === 0 || strpos( $type, 'fixed' ) === 0 ) {
				$ret->size = self::parse_type_nxm( $type );
			}
			if ( strpos( $type, 'bytes' ) === 0 && $type !== 'bytes' && ( $ret->size < 1 || $ret->size > 32 ) ) {
				throw new \Exception( 'Invalid bytes<N> width: ' . $ret->size );
			}
			if ( ( strpos( $type, 'uint' ) === 0 || strpos( $type, 'int' ) === 0 ) &&
				( $ret->size % 8 || $ret->size < 8 || $ret->size > 256 )
			) {
				throw new \Exception( 'Invalid int/uint<N> width: ' . $ret->size );
			}
			return $ret;
		}
	}

	public static function parse_type_n( $type ) {
		preg_match( '/^\D+(\d+)$/', $type, $matches );
		return (int) $matches[1];
	}

	public static function parse_type_nxm( $type ) {
		preg_match( '/^\D+(\d+)x(\d+)$/', $type, $matches );
		return array( (int) $matches[1], (int) $matches[2] );
	}

	public static function parse_type_array( $type ) {
		preg_match( '/(.*)\[(.*?)\]$/', $type, $matches );
		if ( $matches ) {
			return $matches[2] === '' ? 'dynamic' : (int) $matches[2];
		}
		return null;
	}

	public static function decode_single( $parsed_type, $data, $data_bin, $offset ) {
		if ( is_string( $parsed_type ) ) {
			$parsed_type = self::parse_type( $parsed_type );
		}
		$size = null;
		$num  = null;
		$ret  = null;
		$i    = null;

		if ( $parsed_type->name === 'address' ) {
			return self::decode_single( $parsed_type->raw_type, $data, $data_bin, $offset );
		} elseif ( $parsed_type->name === 'bool' ) {
			return (string) self::decode_single( $parsed_type->raw_type, $data, $data_bin, $offset ) === '1';
		} elseif ( $parsed_type->name === 'string' ) {
			$bytes = self::decode_single( $parsed_type->raw_type, $data, $data_bin, $offset );
			return $bytes;
		} elseif ( $parsed_type->is_array ) {
			$ret  = array();
			$size = $parsed_type->size;
			if ( $parsed_type->size === 'dynamic' ) {
				$offset = (int) self::decode_single( 'uint256', $data, $data_bin, $offset );
				$size   = self::decode_single( 'uint256', $data, $data_bin, $offset );
				$offset = $offset + 32;
			}
			for ( $i = 0; $i < $size; $i++ ) {
				$decoded = self::decode_single( $parsed_type->sub_array, $data, $data_bin, $offset );
				$ret[]   = $decoded;
				$offset += $parsed_type->sub_array->memory_usage;
			}
			return $ret;
		} elseif ( $parsed_type->name === 'bytes' ) {
			$offset = (int) self::decode_single( 'uint256', $data, $data_bin, $offset );
			$size   = (int) self::decode_single( 'uint256', $data, $data_bin, $offset );
			return bin2hex( substr( $data_bin, $offset + 32, ( $offset + 32 + $size ) - ( $offset + 32 ) ) );
		} elseif ( strpos( $parsed_type->name, 'bytes' ) === 0 ) {
			return bin2hex( substr( $data_bin, $offset, ( $offset + $parsed_type->size ) - $offset ) );
		} elseif ( strpos( $parsed_type->name, 'uint' ) === 0 ) {
			$num = bin2hex( substr( $data_bin, $offset, $offset + 32 - $offset ) );
			if ( strlen( bin2hex( $num ) ) / 2 > $parsed_type->size ) {
				throw new \Exception( 'Decoded int exceeds width: ' . $parsed_type->size . ' vs ' . strlen( bin2hex( $num ) ) / 2 );
			}
			return $num;
		} elseif ( strpos( $parsed_type->name, 'int' ) === 0 ) {
			$num = bin2hex( substr( $data_bin, $offset, $offset + 32 - $offset ) );
			if ( strlen( bin2hex( $num ) ) / 2 > $parsed_type->size ) {
				throw new \Exception( 'Decoded int exceeds width: ' . $parsed_type->size . ' vs ' . strlen( bin2hex( $num ) ) / 2 );
			}
			return $num;
		} elseif ( strpos( $parsed_type->name, 'ufixed' ) === 0 ) {
			$size = pow( 2, $parsed_type->size[1] );
			$num  = self::decode_single( 'uint256', $data, $data_bin, $offset );
			if ( ! ( $num % $size === 0 ) ) {
				throw new \Exception( 'Decimals not supported yet' );
			}
			return $num / $size;
		} elseif ( strpos( $parsed_type->name, 'fixed' ) === 0 ) {
			$size = pow( 2, $parsed_type->size[1] );
			$num  = self::decode_single( 'int256', $data, $data_bin, $offset );
			if ( ! ( $num % $size === 0 ) ) {
				throw new \Exception( 'Decimals not supported yet' );
			}
			return $num / $size;
		}
		throw new \Exception( 'Unsupported or invalid type: ' . $parsed_type->name );
	}
}
