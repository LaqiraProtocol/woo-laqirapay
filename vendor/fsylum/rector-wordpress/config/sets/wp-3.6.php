<?php

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Transform\Rector\MethodCall\MethodCallToFuncCallRector;
use Rector\Transform\ValueObject\MethodCallToFuncCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(MethodCallToFuncCallRector::class, [
        new MethodCallToFuncCall('wpdb', '_weak_escape', 'esc_sql'),
        new MethodCallToFuncCall('wpdb', 'escape', 'esc_sql'),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'wp_nav_menu_locations_meta_box',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'wp_convert_bytes_to_hr' => 'size_format',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FUNCTIONS
     * - get_user_id_from_string
     *
     * - wpdb::_weak_escape
     * - wpdb::escape
     */
};
