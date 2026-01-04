<?php

use Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('term_description', 1),
    ]);

    $rectorConfig->ruleWithConfiguration(FunctionArgumentDefaultValueReplacerRector::class, [
        new ReplaceFuncCallArgumentDefaultValue('add_action', 0, 'refresh_blog_details', 'clean_site_cache'),
    ]);

    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('WP_User', 'for_blog', 'for_site'),
    ]);

    /*
     * TODO: these are not handled currently
     *
     * CLASSES
     * - WP_Customize_New_Menu_Control
     * - WP_Customize_New_Menu_Section
     *
     * FILES
     * - wp-includes/customize/class-wp-customize-nav-menu-name-control.php
     * - wp-includes/customize/class-wp-customize-new-menu-control.php
     * - wp-includes/customize/class-wp-customize-new-menu-section.php
     *
     * FILTERS
     * - auth_{$object_type}_{$object_subtype}_meta_{$meta_key}
     * - shortcut_link
     *
     * FUNCTIONS
     * - get_shortcut_link
     * - is_user_option_local
     * - options_permalink_add_js
     * - wp_ajax_press_this_add_category
     * - wp_ajax_press_this_save_post
     *
     * METHODS
     * - WP_Community_Events::maybe_log_events_response
     * - WP_Customize_New_Menu_Control::render_content
     * - WP_Customize_New_Menu_Section::render
     * - WP_Roles::_init
     * - WP_User::_init_caps
     */
};
