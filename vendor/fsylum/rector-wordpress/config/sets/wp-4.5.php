<?php

use Fsylum\RectorWordPress\Rules\MethodCall\ReturnFirstArgumentRector;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Transform\Rector\MethodCall\MethodCallToFuncCallRector;
use Rector\Transform\ValueObject\MethodCallToFuncCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(MethodCallToFuncCallRector::class, [
        new MethodCallToFuncCall('WP_Query', 'is_comments_popup', '__return_false'),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('_wp_post_revision_fields', 1),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'comments_popup_script',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'get_comments_popup_template' => '__return_empty_string',
        'get_currentuserinfo'         => 'wp_get_current_user',
        'is_comments_popup'           => '__return_false',
    ]);

    $rectorConfig->ruleWithConfiguration(ReturnFirstArgumentRector::class, [
        'lazyload_comment_meta' => 'WP_Query',
        'lazyload_term_meta'    => 'WP_Query',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FILES
     * - wp-includes/embed-template.php
     *
     * FUNCTIONS
     * - add_object_page
     * - add_utility_page
     * - get_terms
     * - popuplinks
     *
     * METHODS
     * - WP_Customize_Nav_Menus_Panel::wp_nav_menu_manage_columns
     *
     * PROPERTIES
     * - WP_Dependencies::$group
     */
};
