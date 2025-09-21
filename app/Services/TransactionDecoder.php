<?php

namespace LaqiraPay\Services;


use Web3\Contract;
use LaqiraPay\Support\LaqiraPayAbiDecoder;
use LaqiraPay\Domain\Services\LaqiraLogger;

/**
 * Decode transaction input data.
 */
class TransactionDecoder
{
    /**
     * Decode a payDirect transaction input.
     *
     * @param string $tx Raw transaction input.
     * @return array|null
     */
    public function decodeTransactionDirect($tx)
    {
       LaqiraLogger::log(200, 'web3', 'decode_direct_start');
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
        // Inline minimal ABI avoids network calls when decoding.
        try {
            $abiArray    = json_decode($abi);
            $decodeValue = new LaqiraPayAbiDecoder($abiArray);
            $result      = $decodeValue->decode_input($tx);
            LaqiraLogger::log(200, 'web3', 'decode_direct_success');
            return $result;
        } catch (\Throwable $e) {
            LaqiraLogger::log(400, 'web3', 'decode_direct_failed', [], $e->getMessage());
            return null;
        }
    }

    /**
     * Decode a payInApp transaction input.
     *
     * @param string $tx Raw transaction input.
     * @return array|null
     */
    public function decodeTransactionInApp($tx)
    {
       LaqiraLogger::log(200, 'web3', 'decode_inapp_start');
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
        // Use ABI fragment to decode parameters without needing a Web3 provider.
        try {
            $abiArray    = json_decode($abi);
            $decodeValue = new LaqiraPayAbiDecoder($abiArray);
            $result      = $decodeValue->decode_input($tx);
            LaqiraLogger::log(200, 'web3', 'decode_inapp_success');
            return $result;
        } catch (\Throwable $e) {
            LaqiraLogger::log(400, 'web3', 'decode_inapp_failed', [], $e->getMessage());
            return null;
        }
    }
}
