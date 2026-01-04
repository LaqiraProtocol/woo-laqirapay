<?php

use Fsylum\RectorWordPress\Rules\MethodCall\ParameterAdderRector;
use Fsylum\RectorWordPress\ValueObject\MethodParameterAdder;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(ParameterAdderRector::class, [
        new MethodParameterAdder('WP_Metadata_Lazyloader', 'lazyload_comment_meta', 1, 0),
        new MethodParameterAdder('WP_Metadata_Lazyloader', 'lazyload_comment_meta', 2, ''),
        new MethodParameterAdder('WP_Metadata_Lazyloader', 'lazyload_comment_meta', 3, false),
        new MethodParameterAdder('WP_Metadata_Lazyloader', 'lazyload_comment_meta', 4, 'comment'),
        new MethodParameterAdder('WP_Metadata_Lazyloader', 'lazyload_term_meta', 1, 0),
        new MethodParameterAdder('WP_Metadata_Lazyloader', 'lazyload_term_meta', 2, ''),
        new MethodParameterAdder('WP_Metadata_Lazyloader', 'lazyload_term_meta', 3, false),
        new MethodParameterAdder('WP_Metadata_Lazyloader', 'lazyload_term_meta', 4, 'term'),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'wlwmanifest_link',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'block_core_navigation_submenu_build_css_colors' => 'block_core_navigation_link_build_css_colors',
        'wp_img_tag_add_loading_attr'                    => 'wp_img_tag_add_loading_optimization_attrs',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('WP_Metadata_Lazyloader', 'lazyload_comment_meta', 'lazyload_meta_callback'),
        new MethodCallRename('WP_Metadata_Lazyloader', 'lazyload_term_meta', 'lazyload_meta_callback'),
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FILES
     * - wp-admin/media.php
     *
     * FUNCTIONS
     * - block_core_navigation_get_classic_menu_fallback
     * - block_core_navigation_get_classic_menu_fallback_blocks
     * - block_core_navigation_get_most_recently_published_navigation
     * - block_core_navigation_maybe_use_classic_menu_fallback
     * - block_core_navigation_parse_blocks_from_menu_items
     * - wp_get_global_styles_svg_filters
     * - wp_get_loading_attr_default
     * - wp_global_styles_render_svg_filters
     * - wp_queue_comments_for_comment_meta_lazyload
     *
     * METHODS
     * - WP_Image_Editor_Imagick::set_imagick_time_limit
     * - WP_Scripts::print_inline_script
     */
};
