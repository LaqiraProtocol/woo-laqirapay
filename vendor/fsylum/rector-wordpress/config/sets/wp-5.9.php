<?php

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\StaticCallToFuncCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('_load_remote_block_patterns', 0),
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'readonly'                        => 'wp_readonly',
        'wp_render_duotone_filter_preset' => 'wp_get_duotone_filter_property',
    ]);

    $rectorConfig->ruleWithConfiguration(StaticCallToFuncCallRector::class, [
        new StaticCallToFuncCall('WP_Theme_JSON_Resolver', 'get_fields_to_translate', '__return_empty_array'),
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FILES
     * - wp-includes/class-http.php
     *
     * FUNCTIONS
     * - _load_remote_block_patterns
     *
     * METHODS
     * - WP_Theme_JSON::get_stylesheet
     * - WP_Theme_JSON_Resolver::get_merged_data
     * - WP_Theme_JSON_Resolver::get_theme_data
     * - WP_User_Query::prepare_query
     */
};
