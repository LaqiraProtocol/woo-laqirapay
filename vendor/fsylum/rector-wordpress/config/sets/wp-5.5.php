<?php

use Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(FunctionArgumentDefaultValueReplacerRector::class, [
        new ReplaceFuncCallArgumentDefaultValue('add_action', 0, 'wp_blacklist_check', 'wp_check_comment_disallowed_list'),
        new ReplaceFuncCallArgumentDefaultValue('add_filter', 0, 'whitelist_options', 'allowed_options'),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'wp_unregister_GLOBALS',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        '_wp_register_meta_args_whitelist'  => '_wp_register_meta_args_allowed_list',
        'add_option_whitelist'              => 'add_allowed_options',
        'remove_option_whitelist'           => 'remove_allowed_options',
        'wp_blacklist_check'                => 'wp_check_comment_disallowed_list',
        'wp_make_content_images_responsive' => 'wp_filter_content_tags',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FILES
     * - wp-includes/class-phpmailer.php
     * - wp-includes/class-smtp.php
     *
     *
     * add_option
     * get_option
     * update_option
     *
     * METHODS
     * - WP_Community_Events::format_event_data_time
     * - WP_REST_Post_Search_Handler::detect_rest_item_route
     */
};
