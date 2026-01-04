<?php

use Fsylum\RectorWordPress\Rules\FuncCall\ParameterPrependerRector;
use Fsylum\RectorWordPress\Rules\FuncCall\ReturnFirstArgumentRector;
use Fsylum\RectorWordPress\ValueObject\FunctionParameterPrepender;
use Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(FunctionArgumentDefaultValueReplacerRector::class, [
        new ReplaceFuncCallArgumentDefaultValue('add_settings_field', 3, 'misc', 'general'),
        new ReplaceFuncCallArgumentDefaultValue('add_settings_section', 3, 'misc', 'general'),
        new ReplaceFuncCallArgumentDefaultValue('register_setting', 0, 'misc', 'general'),
        new ReplaceFuncCallArgumentDefaultValue('unregister_setting', 0, 'misc', 'general'),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('get_blog_list', 2),
        new RemoveFuncCallArg('get_user_option', 2),
        new RemoveFuncCallArg('is_email', 1),
        new RemoveFuncCallArg('newblog_notify_siteadmin', 1),
        new RemoveFuncCallArg('redirect_this_site', 0),
        new RemoveFuncCallArg('update_posts_count', 0),
        new RemoveFuncCallArg('update_user_status', 3),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'clear_global_post_cache',
        'codepress_footer_js',
        'codepress_get_lang',
        'deactivate_sitewide_plugin',
        'use_codepress',
        'wpmu_menu',
    ]);

    $rectorConfig->ruleWithConfiguration(ParameterPrependerRector::class, [
        new FunctionParameterPrepender('get_user_details', 'login'),
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'activate_sitewide_plugin'     => '__return_false',
        'add_option_update_handler'    => 'register_setting',
        'generate_random_password'     => 'wp_generate_password',
        'get_alloptions'               => 'wp_load_alloptions',
        'get_user_details'             => 'get_user_by',
        'get_usernumposts'             => 'count_user_posts',
        'is_main_blog'                 => 'is_main_site',
        'is_taxonomy'                  => 'taxonomy_exists',
        'is_term'                      => 'term_exists',
        'is_wpmu_sitewide_plugin'      => 'is_network_only_plugin',
        'remove_option_update_handler' => 'unregister_setting',
        'set_current_user'             => 'wp_set_current_user',
        'validate_email'               => 'is_email',
        'wp_shrink_dimensions'         => 'wp_constrain_dimensions',
    ]);

    $rectorConfig->ruleWithConfiguration(ReturnFirstArgumentRector::class, [
        'mu_options',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * ARGUMENTS
     * - get_delete_post_link:2
     * - get_last_updated:1
     *
     * FUNCTIONS
     * - automatic_feed_links
     * - clean_url
     * - delete_usermeta
     * - funky_javascript_callback
     * - funky_javascript_fix
     * - get_blog_list
     * - get_most_active_blogs
     * - get_profile
     * - get_usermeta
     * - graceful_fail
     * - install_blog_defaults
     * - is_site_admin
     * - update_usermeta
     * - wp_dropdown_cats
     * - wpmu_checkAvailableSpace
     */
};
