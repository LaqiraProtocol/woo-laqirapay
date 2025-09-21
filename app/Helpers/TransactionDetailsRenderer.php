<?php

namespace LaqiraPay\Helpers;

/**
 * Helper methods for rendering transaction detail tables and email fields
 * with proper escaping applied to dynamic values.
 */
class TransactionDetailsRenderer
{
    private const DEFAULT_EXPLORER = 'https://bscscan.com';

    /**
     * Build the sanitized rows for displaying transaction details in a table.
     *
     * @param object $order Order instance providing get_meta access.
     * @return array<int, array{key:string,label:string,value:string,url?:string}>
     */
    public static function buildTransactionRows($order): array
    {
        $rows = [
            [
                'key'   => 'TokenName',
                'label' => esc_html__('Token Name', 'laqirapay'),
                'value' => self::sanitizeText($order->get_meta('TokenName')),
            ],
            [
                'key'   => 'exchange_rate',
                'label' => esc_html__('Exchange Rate', 'laqirapay'),
                'value' => self::sanitizeText($order->get_meta('exchange_rate')),
            ],
            [
                'key'   => 'TokenAmount',
                'label' => esc_html__('Token Amount', 'laqirapay'),
                'value' => self::sanitizeText($order->get_meta('TokenAmount')),
            ],
        ];

        $txHash = (string) $order->get_meta('tx_hash');
        $rows[] = [
            'key'   => 'tx_hash',
            'label' => esc_html__('Transaction Hash', 'laqirapay'),
            'value' => self::sanitizeText($txHash),
            'url'   => self::buildExplorerUrl($order->get_meta('network_explorer'), $txHash),
        ];

        return $rows;
    }

    /**
     * Render the provided rows into HTML table markup.
     *
     * @param array<int, array{label:string,value:string,url?:string}> $rows
     */
    public static function renderRows(array $rows): string
    {
        $output = '';

        foreach ($rows as $row) {
            $value = $row['value'];
            if (!empty($row['url'])) {
                $value = sprintf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                    esc_url($row['url']),
                    $value
                );
            }

            $output .= sprintf('<tr><th>%s</th><td>%s</td></tr>', $row['label'], $value);
        }

        return $output;
    }

    /**
     * Build the WooCommerce email meta fields array with escaped values.
     *
     * @param object $order Order instance providing get_meta access.
     * @return array<string, array{label:string,value:string}>
     */
    public static function buildEmailFields($order): array
    {
        $fields = [];

        foreach (self::buildTransactionRows($order) as $row) {
            $value = $row['value'];
            if (!empty($row['url'])) {
                $value = sprintf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                    esc_url($row['url']),
                    $row['value']
                );
            }

            $fields[$row['key']] = [
                'label' => $row['label'],
                'value' => $value,
            ];
        }

        return $fields;
    }

    /**
     * Build the blockchain explorer URL for a transaction hash.
     *
     * @param string|null $networkExplorer Custom explorer base URL.
     * @param string      $txHash          Transaction hash.
     */
    public static function buildExplorerUrl($networkExplorer, string $txHash): string
    {
        $hash = trim((string) $txHash);
        if ($hash === '') {
            return '';
        }

        $base = trim((string) $networkExplorer);
        if ($base === '' || !preg_match('#^https?://#i', $base)) {
            $base = self::DEFAULT_EXPLORER;
        }

        return rtrim($base, '/') . '/tx/' . rawurlencode($hash);
    }

    private static function sanitizeText($value): string
    {
        return esc_html((string) $value);
    }
}
