<?php

namespace LaqiraPay\Services;

use Exception;
use Web3\Contract;
use LaqiraPay\Helpers\FileHelper;
use LaqiraPay\Domain\Services\LaqiraLogger;

/**
 * Handle smart contract interactions and ABI caching.
 */
class ContractService {

	private ?Contract $contract = null;
	private ?array $contractAbi = null;
	private array $abiCache     = array();

	/**
	 * Provide readiness flags for contract interactions.
	 *
	 * @return array{has_contract_address:bool,has_rpc_url:bool}
	 */
	protected function getConfigurationStatus(): array {
		return array(
			'has_contract_address' => $this->isNonEmptyConstant( 'CONTRACT_ADDRESS' ),
			'has_rpc_url'          => $this->isNonEmptyConstant( 'RPC_URL' ),
		);
	}

	/**
	 * Determine whether a constant resolves to a non-empty string value.
	 *
	 * @return bool
	 */
	private function isNonEmptyConstant( string $constantName ): bool {
		if ( ! defined( $constantName ) ) {
			return false;
		}

		$value = constant( $constantName );

		if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
			$value = (string) $value;
		} elseif ( ! is_scalar( $value ) ) {
			return false;
		} else {
			$value = (string) $value;
		}

		return trim( $value ) !== '';
	}

	/**
	 * Load the contract ABI data once and cache the decoded array.
	 *
	 * @throws Exception When the ABI cannot be loaded or decoded.
	 * @return array
	 */
	private function loadContractAbi(): array {
		if ( is_array( $this->contractAbi ) ) {
			return $this->contractAbi;
		}

		$remoteSource = defined( 'LAQIRA_PLUGINS_URL' )
			? LAQIRA_PLUGINS_URL . 'assets/json/cidAbi.json'
			: '';

		$abiJson = '';
		if ( $remoteSource !== '' ) {
			$abiJson = FileHelper::get_contents_secure( $remoteSource );
		}

		if ( trim( $abiJson ) === '' && defined( 'LAQIRAPAY_PLUGIN_DIR' ) ) {
			$localPath = LAQIRAPAY_PLUGIN_DIR . 'assets/json/cidAbi.json';
			if ( is_readable( $localPath ) ) {
				$localContents = file_get_contents( $localPath );
				if ( $localContents !== false ) {
					$abiJson = $localContents;
				}
			}
		}

		$decoded = json_decode( $abiJson, true );
		if ( ! is_array( $decoded ) ) {
			$errorCode = json_last_error();
			$context   = array(
				'source'          => $remoteSource,
				'json_error_code' => $errorCode,
				'json_error'      => function_exists( 'json_last_error_msg' ) ? json_last_error_msg() : 'unknown',
				'abi_length'      => strlen( $abiJson ),
			);
			if ( isset( $localPath ) ) {
				$context['local_fallback'] = $localPath;
			}
			LaqiraLogger::log( 400, 'web3', 'contract_abi_load_failed', $context );
			$jsonError = isset( $context['json_error'] ) ? $this->sanitizeMessage( $context['json_error'] ) : 'unknown';
			error_log( '[LaqiraPay] Unable to load contract ABI: ' . $jsonError );
			throw new Exception( 'Unable to load contract ABI.' );
		}

		$this->contractAbi = $decoded;

		return $this->contractAbi;
	}

	/**
	 * Retrieve the main contract instance.
	 *
	 * @return Contract
	 */
	public function getContract(): Contract {
		if ( $this->contract instanceof Contract ) {
			return $this->contract; // Reuse existing instance when available.
		}
		$web3           = new \Web3\Web3( RPC_URL, 10 );
		$this->contract = new Contract( $web3->provider, $this->loadContractAbi() );
		return $this->contract;
	}

	/**
	 * Convenience wrapper to obtain contract instance.
	 *
	 * @return Contract
	 */
	public function createContractInstance(): Contract {
		return $this->getContract();
	}

	/**
	 * Fetch CID from contract and log outcome.
	 *
	 * @return string|null
	 */
	public function getCid(): ?string {
		// Serve cached CID when available to avoid expensive network calls.
		$cached = get_transient( 'laqirapay_cid_cached' );
		if ( $cached !== false ) {
			return $cached;
		}

		$configuration = $this->getConfigurationStatus();
		if ( ! $configuration['has_contract_address'] || ! $configuration['has_rpc_url'] ) {
			LaqiraLogger::log( 300, 'web3', 'cid_fetch_skipped_missing_config', $configuration );

			return $this->getStoredCid();
		}

		try {
			$contract = $this->createContractInstance();
			$cid      = $this->fetchCidFromContract( $contract );
			if ( $cid !== '' ) {
				update_option( 'laqirapay_cid', $cid ); // Persist latest CID value.
				$ttl = defined( 'HOUR_IN_SECONDS' ) ? HOUR_IN_SECONDS : 3600;
				set_transient( 'laqirapay_cid_cached', $cid, $ttl ); // Cache CID for subsequent requests.
				LaqiraLogger::log( 200, 'web3', 'cid_fetched', array( 'cid' => $cid ) );
				return $cid;
			}
		} catch ( Exception $e ) {
			$sanitizedMessage = $this->sanitizeMessage( $e );
			LaqiraLogger::log( 400, 'web3', 'cid_fetch_failed', array(), $sanitizedMessage );
			error_log( 'Error: ' . $sanitizedMessage );
		}

		// Fallback to stored option when remote call fails.
		return $this->getStoredCid();
	}

	/**
	 * Call getCid function on the contract.
	 *
	 * @param Contract $contract Contract instance.
	 * @return string
	 */
	public function fetchCidFromContract( Contract $contract ): string {
		$cid   = '';
		$error = null;
		$contract->at( CONTRACT_ADDRESS )->call(
			'getCid',
			function ( $err, $data ) use ( &$cid, &$error ) {
				if ( $err !== null ) {
					$error = $err;
					return;
				}
				$cid = implode( '-', $data );
			}
		);
		if ( $error !== null ) {
			throw new Exception( $error->getMessage() );
		}
		return $cid;
	}

	/**
	 * Determine if the provider has the network activated.
	 *
	 * @param array  $network Network definition from CID.
	 * @param string $address Provider address.
	 * @return bool
	 */
	public function isProviderNetworkActive( $network, $address ): bool {
		try {
			if ( ! is_array( $network ) || empty( $network['laqiraPayContract'] ) || empty( $network['rpc'] ) || empty( $network['abi_contract_5'] ) ) {
				return false;
			}
			$contractAddress = $network['laqiraPayContract'];
			$rpcEndpoint     = $network['rpc'];
			$abiPath         = $network['abi_contract_5'];
			if ( ! isset( $this->abiCache[ $abiPath ] ) ) {
				// Cache ABI per endpoint to minimize remote requests.
				$decodedAbi = json_decode( FileHelper::get_contents_secure( $abiPath ), true );
				if ( ! is_array( $decodedAbi ) ) {
					$errorCode = json_last_error();
					$context   = array(
						'abi_path'        => $abiPath,
						'json_error_code' => $errorCode,
						'json_error'      => function_exists( 'json_last_error_msg' ) ? json_last_error_msg() : 'unknown',
					);
					LaqiraLogger::log( 400, 'web3', 'network_abi_load_failed', $context );
					$jsonError = isset( $context['json_error'] ) ? $this->sanitizeMessage( $context['json_error'] ) : 'unknown';
					error_log( '[LaqiraPay] Failed to decode network ABI: ' . $jsonError );
					return false;
				}
				$this->abiCache[ $abiPath ] = $decodedAbi;
			}
			$contractAbi = $this->abiCache[ $abiPath ];
			$web3        = new \Web3\Web3( $rpcEndpoint, 10 );
			$contract    = new Contract( $web3->provider, $contractAbi );
			$result      = false;
			$contract->at( $contractAddress )->call(
				'isProviderNetworkActive',
				$address,
				function ( $err, $data ) use ( &$result ) {
					if ( $err === null && isset( $data[0] ) ) {
						$result = $data[0]; // Contract returns true when network is active for provider.
					}
				}
			);
			return $result;
		} catch ( Exception $e ) {
			$sanitizedMessage = $this->sanitizeMessage( $e );
			LaqiraLogger::log( 400, 'web3', 'network_check_error', array( 'network' => $network['laqiraPayContract'] ?? 'unknown' ), $sanitizedMessage );
			error_log( 'Error: ' . $sanitizedMessage );
			return false;
		}
	}

	private function sanitizeMessage( $value ): string {
		if ( is_object( $value ) && method_exists( $value, 'getMessage' ) ) {
			$value = $value->getMessage();
		}

		if ( ! is_scalar( $value ) ) {
			return '';
		}

		$string = (string) $value;

		if ( function_exists( 'sanitize_text_field' ) ) {
			return sanitize_text_field( $string );
		}

		$string = strip_tags( $string );

		return trim( preg_replace( '/[\r\n\t\0\x0B]+/', ' ', $string ) );
	}

	/**
	 * Retrieve stored CID value from WordPress options.
	 *
	 * @return string|null
	 */
	private function getStoredCid(): ?string {
		$storedCid = get_option( 'laqirapay_cid' );

		return $storedCid === false ? null : $storedCid;
	}
}
