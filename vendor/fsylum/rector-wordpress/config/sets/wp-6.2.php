<?php

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\StaticCallToFuncCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(StaticCallToFuncCallRector::class, [
        new StaticCallToFuncCall('WP_Theme_JSON_Resolver', 'theme_has_support', 'wp_theme_has_theme_json'),
    ]);

    /*
     * TODO: these are not handled currently
     *
     * CLASSES
     * - Requests
     *
     * FUNCTIONS
     * - get_page_by_title
     *
     * METHODS
     * - WP_Media_List_Table::column_desc
     */
};
