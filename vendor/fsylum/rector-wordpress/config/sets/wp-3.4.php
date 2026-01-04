<?php

use Fsylum\RectorWordPress\Rules\FuncCall\ParameterAdderRector;
use Fsylum\RectorWordPress\ValueObject\FunctionParameterAdder;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(ParameterAdderRector::class, [
        new FunctionParameterAdder('remove_custom_background', 0, 'custom-background'),
        new FunctionParameterAdder('remove_custom_image_header', 0, 'custom-header'),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('debug_fopen', 0),
        new RemoveFuncCallArg('debug_fopen', 1),
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'clean_page_cache'           => 'clean_post_cache',
        'current_theme_info'         => 'wp_get_theme',
        'debug_fopen'                => '__return_false',
        'remove_custom_background'   => 'remove_theme_support',
        'remove_custom_image_header' => 'remove_theme_support',
        'update_page_cache'          => 'update_post_cache',
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'debug_fclose',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FUNCTIONS
     * - add_custom_background
     * - add_custom_image_header
     * - clean_pre
     * - debug_fwrite
     * - display_theme
     * - get_allowed_themes
     * - get_broken_themes
     * - get_current_theme
     * - get_site_allowed_themes
     * - get_theme
     * - get_theme_data
     * - get_themes
     * - logIO
     * - wpmu_get_blog_allowedthemes
     * - wp_explain_nonce
     *
     * PROPERTIES
     * - WP_Scripts::$concat_version
     * - WP_Styles::$concat_version
     */
};
