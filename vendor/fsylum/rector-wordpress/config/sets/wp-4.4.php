<?php

use Fsylum\RectorWordPress\Rules\MethodCall\RemoveMethodCallRector;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('get_site_option', 2),
        new RemoveFuncCallArg('get_wp_title_rss', 0),
        new RemoveFuncCallArg('wp_title_rss', 0),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveMethodCallRector::class, [
        'flush_widget_cache' => 'WP_Widget_Recent_Comments',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'force_ssl_login' => 'force_ssl_admin',
        'post_permalink'  => 'get_permalink',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FUNCTIONS
     * - create_empty_blog
     * - get_admin_users_for_domain
     * - wp_get_http
     */
};
