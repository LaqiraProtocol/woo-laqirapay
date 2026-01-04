<?php

use Fsylum\RectorWordPress\Rules\FuncCall\ReturnFirstArgumentRector;
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
        new ReplaceFuncCallArgumentDefaultValue('add_filter', 0, 'htmledit_pre', 'format_for_editor'),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'preview_theme',
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('preview_theme_ob_filter_callback', 0),
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        '_preview_theme_stylesheet_filter' => '__return_empty_string',
        '_preview_theme_template_filter'   => '__return_empty_string',
        'preview_theme_ob_filter_callback' => '__return_empty_string',
        'wp_htmledit_pre'                  => 'format_for_editor',
    ]);

    $rectorConfig->ruleWithConfiguration(ReturnFirstArgumentRector::class, [
        'preview_theme_ob_filter',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FUNCTIONS
     * - wp_ajax_wp_fullscreen_save_post
     * - wp_new_user_notification
     * - wp_richedit_pre
     *
     * FILTERS
     * - richedit_pre
     *
     * METHODS
     * - _WP_Editors::wp_fullscreen_html
     */
};
