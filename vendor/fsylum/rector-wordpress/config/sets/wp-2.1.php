<?php

use Fsylum\RectorWordPress\Rules\FuncCall\ParameterAdderRector;
use Fsylum\RectorWordPress\Rules\FuncCall\ReturnFirstArgumentRector;
use Fsylum\RectorWordPress\ValueObject\FunctionParameterAdder;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('get_the_author', 0),
        new RemoveFuncCallArg('get_autotoggle', 0),
        new RemoveFuncCallArg('wp_get_post_cats', 0),
        new RemoveFuncCallArg('wp_set_post_cats', 0),
    ]);

    $rectorConfig->ruleWithConfiguration(ParameterAdderRector::class, [
        new FunctionParameterAdder('get_autotoggle', 0, 0),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'links_popup_script',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'get_link'         => 'get_bookmark',
        'get_settings'     => 'get_option',
        'wp_get_post_cats' => 'wp_get_post_categories',
        'wp_set_post_cats' => 'wp_set_post_categories',
    ]);

    $rectorConfig->ruleWithConfiguration(ReturnFirstArgumentRector::class, [
        'get_autotoggle',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * ARGUMENTS
     * - the_author:1
     * - the_author_posts_link:1
     *
     * FILES
     * - wp-includes/registration-functions.php
     * - wp-includes/rss-functions.php
     *
     * FILTERS
     * - query_string
     *
     * FUNCTIONS
     * - dropdown_cats
     * - get_archives
     * - get_author_link
     * - get_linkcatname
     * - get_linkobjects
     * - get_linkobjectsbyname
     * - get_linkrating
     * - get_links
     * - get_links_list
     * - get_links_withrating
     * - get_linksbyname
     * - get_linksbyname_withrating
     * - link_pages
     * - list_authors
     * - list_cats
     * - tinymce_include
     * - wp_get_links
     * - wp_get_linksbyname
     * - wp_list_cats
     */
};
