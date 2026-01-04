<?php

use Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(FunctionArgumentDefaultValueReplacerRector::class, [
        new ReplaceFuncCallArgumentDefaultValue('add_filter', 0, 'tag_rewrite_rules', 'post_tag_rewrite_rules'),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('update_blog_option', 3),
        new RemoveFuncCallArg('update_blog_status', 3),
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'update_category_cache' => '__return_true',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * ARGUMENTS
     * - register_uninstall_hook:2
     * - wp_get_recent_posts:1
     *
     * CLASSES
     * - WP_User_Search
     *
     * FILES
     * - wp-includes/registration.php
     *
     * FUNCTIONS
     * - get_author_user_ids
     * - get_dashboard_blog
     * - get_editable_authors
     * - get_editable_user_ids
     * - get_nonauthor_user_ids
     * - get_others_drafts
     * - get_others_pending
     * - get_others_unpublished_posts
     * - get_users_of_blog
     * - install_themes_feature_list
     * - is_plugin_page
     */
};
