<?php

use Fsylum\RectorWordPress\Rules\Deprecated\Functions\WpKsesJsEntitiesRector;
use Fsylum\RectorWordPress\Rules\FuncCall\ParameterAdderRector;
use Fsylum\RectorWordPress\Rules\MethodCall\RemoveMethodCallRector;
use Fsylum\RectorWordPress\Rules\MethodCall\ReturnFirstArgumentRector;
use Fsylum\RectorWordPress\ValueObject\FunctionParameterAdder;
use Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(FunctionArgumentDefaultValueReplacerRector::class, [
        new ReplaceFuncCallArgumentDefaultValue('add_filter', 0, 'blog_details', 'site_details'),
    ]);

    $rectorConfig->ruleWithConfiguration(ParameterAdderRector::class, [
        new FunctionParameterAdder('get_paged_template', 0, 'paged'),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('unregister_setting', 2),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveMethodCallRector::class, [
        'customize_preview_base'                => 'WP_Customize_Manager',
        'customize_preview_html5'               => 'WP_Customize_Manager',
        'customize_preview_override_404_status' => 'WP_Customize_Manager',
        'customize_preview_signature'           => 'WP_Customize_Manager',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'get_paged_template' => 'get_query_template',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('WP_Roles', 'reinit', 'for_site'),
    ]);

    $rectorConfig->ruleWithConfiguration(ReturnFirstArgumentRector::class, [
        'remove_preview_signature' => 'WP_Customize_Manager',
    ]);

    $rectorConfig->rule(WpKsesJsEntitiesRector::class);

    /*
     * TODO: these are not handled currently
     *
     * FILES
     * - wp-admin/includes/class-wp-upgrader-skins.php
     * - wp-includes/class-feed.php
     * - wp-includes/locale.php
     * - wp-includes/session.php
     *
     * FILTERS
     * - rest_enabled
     *
     * FUNCTIONS
     * - _sort_nav_menu_items
     * - _usort_terms_by_ID
     * - _usort_terms_by_name
     * - wp_get_network
     *
     * METHODS
     * - WP_Customize_Manager::_cmp_priority
     * - WP_Customize_Manager::wp_die_handler
     * - WP_Customize_Manager::wp_redirect_status
     * - WP_Customize_Nav_Menu_Setting::_sort_menus_by_orderby
     */
};
