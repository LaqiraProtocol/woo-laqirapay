<?php

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('trackback_rdf', 0),
        new RemoveFuncCallArg('wp_login', 2),
        new RemoveFuncCallArg('xfn_check', 2),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'documentation_link',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'comments_rss_link' => 'post_comments_feed_link',
        'gzip_compression'  => '__return_false',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * ARGUMENTS
     * - the_attachment_link:3
     * - trackback_url:1
     *
     * FILE
     * - wp-admin/admin-functions.php
     * - wp-admin/upgrade-functions.php
     *
     * FUNCTIONS
     * - get_attachment_icon
     * - get_attachment_icon_src
     * - get_attachment_innerHTML
     * - get_author_rss_link
     * - get_category_rss_link
     * - get_the_attachment_link
     * - wp_clearcookie
     * - wp_get_cookie_login
     * - wp_login
     * - wp_setcookie
     */
};
