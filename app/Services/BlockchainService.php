<?php

namespace LaqiraPay\Services;

use Web3\Utils;
use LaqiraPay\Domain\Services\LaqiraLogger;
use WP_Error;

/**
 * Provide network and provider utilities for blockchain operations.
 */
class BlockchainService {

	private ContractService $contractService;

	public function __construct( ?ContractService $contractService = null ) {
		$this->contractService = $contractService ?: new ContractService();
	}

	/**
	 * Describe whether essential blockchain settings have been configured.
	 *
	 * @return array{has_api_key:bool,has_contract_address:bool,has_rpc_url:bool}
	 */
	public function getConfigurationReadiness(): array {
		return array(
			'has_api_key'          => $this->hasNonEmptyOption( 'laqirapay_api_key' ),
			'has_contract_address' => $this->hasNonEmptyOption( 'laqirapay_main_contract' ),
			'has_rpc_url'          => $this->hasNonEmptyOption( 'laqirapay_main_rpc_url' ),
		);
	}

	/**
	 * Determine whether all essential blockchain settings are present.
	 */
	public function isConfigurationReady(): bool {
		return ! in_array( false, $this->getConfigurationReadiness(), true );
	}

	/**
	 * Validate and return provider address.
	 *
	 * @return string
	 */
	public function getProvider() {
		$api_key = get_option( 'laqirapay_api_key' );
		if ( ! Utils::isAddress( $api_key ) ) {
			LaqiraLogger::log( 300, 'web3', 'invalid_provider_key' );
			return 'Your Api Key is invalid';
		}
		update_option( 'laqirapay_provider_key', $api_key );
		LaqiraLogger::log( 200, 'web3', 'provider_key_loaded' );
		return $api_key;
	}

	/**
	 * Retrieve cached provider address.
	 *
	 * @return mixed
	 */
	public function getProviderLocal() {
		return get_option( 'laqirapay_api_key' );// get_option('laqirapay_provider_key');
	}

	/**
	 * Fetch JSON data for a given CID API endpoint.
	 *
	 * @param string|null $api API endpoint.
	 * @return array|null
	 */
	public function getRemoteJsonCid( $api ) {
		$cached = get_transient( 'laqirapay_remote_cid_data' );
		if ( $cached !== false ) {
			return $cached; // Serve cached data to reduce external requests.
		}
		if ( ! $this->isNonEmptyString( $api ) ) {
			LaqiraLogger::log( 300, 'web3', 'remote_cid_invalid_api', array( 'api' => $api ) );
			return null;
		}
		$response = wp_remote_get( $api ); // Fetch CID definition from remote endpoint.
		if ( is_wp_error( $response ) ) {
			LaqiraLogger::log( 400, 'web3', 'remote_cid_fetch_failed', array( 'api' => $api ) );
			return array( 'error' => 'Unable to fetch data.' );
		}
		$body      = wp_remote_retrieve_body( $response );
		$json_data = json_decode( $body, true );
		if ( $json_data === null ) {
			LaqiraLogger::log( 300, 'web3', 'remote_cid_parse_failed' );
			return array( 'error' => 'Unable to parse JSON data.' );
		}
		$ttl = defined( 'HOUR_IN_SECONDS' ) ? HOUR_IN_SECONDS : 3600;
		set_transient( 'laqirapay_remote_cid_data', $json_data, $ttl );
		update_option( 'laqirapay_remote_cid_data', $json_data );
		return $json_data;
	}

	/**
	 * Get saved CID value.
	 *
	 * @return mixed
	 */
	public function getCidLocal() {
		return get_option( 'laqirapay_cid' );
	}

	/**
	 * Retrieve CID value from contract.
	 *
	 * @return string|null
	 */
	public function getCid() {
		return $this->contractService->getCid();
	}

	/**
	 * Return available networks for provider.
	 *
	 * @return array
	 */
	public function getNetworks(): array {
		$cached = get_transient( 'laqirapay_networks_cached' );
		if ( $cached !== false ) {
			return $cached;
		}
		$networks = $this->getMainnetNetworks();
		$ttl      = defined( 'HOUR_IN_SECONDS' ) ? HOUR_IN_SECONDS : 3600;
		set_transient( 'laqirapay_networks_cached', $networks, $ttl );
		return $networks;
	}

	/**
	 * Display networks with status flags.
	 *
	 * @return array
	 */
	public function showNetworks(): array {
		$cached = get_transient( 'laqirapay_networks_status_cached' );
		if ( $cached !== false ) {
			return $cached;
		}
		$data = $this->getRemoteJsonCid( $this->contractService->getCid() );
		if ( ! is_array( $data ) ) {
			return array();
		}
		$mainnet_networks = $data['mainnet'] ?? array();
		if ( ! isset( $mainnet_networks ) || ! is_array( $mainnet_networks ) ) {
			return array();
		}
		$activeNetworks = $this->getMainnetNetworks();
		$activeNames    = array_column( $activeNetworks, 'network' );
		$result         = array();
		foreach ( $mainnet_networks as $network ) {
			$networkName   = $network['network'] ?? 'unknown';
			$isActive      = in_array( $networkName, $activeNames );
			$statusMessage = (
				$isActive ? '✅' : '❌'
			) . " $networkName " . (
				$isActive ? esc_html__( 'is Active', 'laqirapay' ) : esc_html__( 'is Not Active', 'laqirapay' )
			); // Build human-friendly status message.
			$result[]      = array(
				'network' => $networkName,
				'status'  => $isActive ? 'active' : 'inactive',
				'message' => $statusMessage,
			);
		}
		$ttl = defined( 'HOUR_IN_SECONDS' ) ? HOUR_IN_SECONDS : 3600;
		set_transient( 'laqirapay_networks_status_cached', $result, $ttl );
		return $result;
	}

	/**
	 * Retrieve list of assets for networks.
	 *
	 * @return array
	 */
	public function getNetworksAssets(): array {
		$cached = get_transient( 'laqirapay_networks_assets_cached' );
		if ( $cached !== false ) {
			return $cached;
		}
		$assets = $this->getMainnetNetworksAssets();
		$ttl    = defined( 'HOUR_IN_SECONDS' ) ? HOUR_IN_SECONDS : 3600;
		set_transient( 'laqirapay_networks_assets_cached', $assets, $ttl );
		return $assets;
	}

	/**
	 * Get stable coins defined in CID.
	 *
	 * @return mixed|null
	 */
	public function getStableCoins() {
		$cached = get_transient( 'laqirapay_stablecoins_cached' );
		if ( $cached !== false ) {
			return $cached;
		}
		$data = $this->getRemoteJsonCid( $this->contractService->getCid() );
		if ( ! is_array( $data ) ) {
			return null;
		}
		$stable = $data['stablecoins'] ?? null;
		$ttl    = defined( 'HOUR_IN_SECONDS' ) ? HOUR_IN_SECONDS : 3600;
		set_transient( 'laqirapay_stablecoins_cached', $stable, $ttl );
		return $stable;
	}

	/**
	 * Retrieve transaction information.
	 *
	 * @param string   $tx          Transaction hash.
	 * @param string   $network_rpc RPC endpoint.
	 * @param callable $callback    Callback to process the transaction.
	 * @return mixed
	 */
	public function getTransactionInfo( $tx, $network_rpc, $callback ) {
		$web3   = new \Web3\Web3( $network_rpc, 10 );
		$result = null;
		$web3->eth->getTransactionByHash(
			$tx,
			function ( $err, $transaction ) use ( &$result, $callback, $tx ) {
				if ( $err !== null ) {
					$result = $this->buildWeb3Error( $err, $tx, 'transaction_info_fetch_failed' );
					return;
				}
				$result = $callback( $transaction ); // Process asynchronously and capture result.
			}
		);
		return $result;
	}

	/**
	 * Retrieve transaction receipt information.
	 *
	 * @param string   $tx          Transaction hash.
	 * @param string   $network_rpc RPC endpoint.
	 * @param callable $callback    Callback to process the receipt.
	 * @return mixed
	 */
	public function getTransactionRec( $tx, $network_rpc, $callback ) {
		$web3   = new \Web3\Web3( $network_rpc, 10 );
		$result = null;
		$web3->eth->getTransactionReceipt(
			$tx,
			function ( $err, $transaction ) use ( &$result, $callback, $tx ) {
				if ( $err !== null ) {
					$result = $this->buildWeb3Error( $err, $tx, 'transaction_receipt_fetch_failed' );
					return;
				}
				$result = $callback( $transaction ); // Use callback to handle receipt data.
			}
		);
		return $result;
	}

	/**
	 * Internal helper to filter active networks.
	 *
	 * @return array
	 */
	private function getMainnetNetworks(): array {
		$data = $this->getRemoteJsonCid( $this->contractService->getCid() );
		if ( ! is_array( $data ) ) {
			return array();
		}
		$mainnet_networks = $data['mainnet'] ?? array();
		if ( ! isset( $mainnet_networks ) || ! is_array( $mainnet_networks ) ) {
			return array();
		}
		$providerAddress = get_option( 'laqirapay_api_key' );
		$activeNetworks  = array_filter(
			$mainnet_networks,
			function ( $network ) use ( $providerAddress ) {
				// Keep only networks where the provider is registered as active.
				return $this->contractService->isProviderNetworkActive( $network, $providerAddress );
			}
		);
		return array_values( $activeNetworks );
	}

	/**
	 * Internal helper to collect assets.
	 *
	 * @return array
	 */
	private function getMainnetNetworksAssets(): array {
		$data = $this->getRemoteJsonCid( $this->getCidLocal() );
		if ( ! is_array( $data ) ) {
			return array();
		}
		$assets = $data['availableAssets'] ?? array();
		if ( isset( $assets ) && is_array( $assets ) ) {
			return $assets;
		}
		return array();
	}

	private function hasNonEmptyOption( string $optionName ): bool {
		$value = get_option( $optionName );

		if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
			$value = (string) $value;
		} elseif ( ! is_scalar( $value ) ) {
			return false;
		} else {
			$value = (string) $value;
		}

		return trim( $value ) !== '';
	}

	private function isNonEmptyString( $value ): bool {
		return is_string( $value ) && trim( $value ) !== '';
	}

	private function buildWeb3Error( $error, $tx, string $event ) {
		$sanitizedError = $this->sanitizeMessage( $error );
		$context        = array( 'error' => $sanitizedError );
		if ( $this->isNonEmptyString( $tx ) ) {
			$context['tx_hash'] = $this->sanitizeMessage( $tx );
		}
		LaqiraLogger::log( 400, 'web3', $event, $context );

		if ( class_exists( WP_Error::class ) ) {
			return new WP_Error(
				'laqirapay_' . $event,
				esc_html__( 'Unable to retrieve transaction details at this time. Please try again later.', 'laqirapay' ),
				$context
			);
		}

		return null;
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
		if ($string === null) {
   			 $string = '';
			}


		return trim( preg_replace( '/[\r\n\t\0\x0B]+/', ' ', $string ) );
	}
}
