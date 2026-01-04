<?php

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'favorite_actions',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'wp_clone'                        => 'clone',
        'wp_dashboard_quick_press_output' => 'wp_dashboard_quick_press',
        'wp_timezone_supported'           => '__return_true',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FILTERS
     * - pub_priv_sql_capability
     */
};
