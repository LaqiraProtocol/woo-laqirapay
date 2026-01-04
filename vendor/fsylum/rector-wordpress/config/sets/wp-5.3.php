<?php

use Fsylum\RectorWordPress\Rules\FuncCall\ParameterAdderRector;
use Fsylum\RectorWordPress\Rules\FuncCall\ReturnFirstArgumentRector;
use Fsylum\RectorWordPress\ValueObject\FunctionParameterAdder;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(ParameterAdderRector::class, [
        new FunctionParameterAdder('wp_rel_nofollow_callback', 1, 'nofollow'),
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'wp_rel_nofollow_callback' => 'wp_rel_callback',
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        '_wp_privacy_requests_screen_options',
    ]);

    $rectorConfig->ruleWithConfiguration(ReturnFirstArgumentRector::class, [
        '_wp_json_prepare_data',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * CLASSES
     * - WP_Privacy_Data_Export_Requests_Table
     * - WP_Privacy_Data_Removal_Requests_Table
     *
     * FILES
     * - wp-admin/custom-background.php
     * - wp-admin/custom-header.php
     * - wp-includes/class-oembed.php
     * - wp-includes/date.php
     * - wp-includes/spl-autoload-compat.php
     *
     * FUNCTIONS
     * - update_user_status
     *
     * METHODS
     * - Services_JSON::_encode
     * - Services_JSON::decode
     * - Services_JSON::encode
     * - Services_JSON::encodeUnsafe
     * - Services_JSON::isError
     * - Services_JSON::name_value
     * - Services_JSON::reduce_string
     * - Services_JSON::Services_JSON
     * - Services_JSON::strlen8
     * - Services_JSON::substr8
     * - Services_JSON::utf162utf8
     * - Services_JSON::utf82utf16
     * - Services_JSON_Error::Services_JSON_Error
     *
     */
};
