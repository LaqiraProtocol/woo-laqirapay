<?php

use Fsylum\RectorWordPress\Rules\ConstFetch\ConstToFuncCallRector;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(ConstToFuncCallRector::class, [
        'STYLESHEETPATH' => 'get_stylesheet_directory',
        'TEMPLATEPATH'   => 'get_template_directory',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        '_admin_bar_bump_cb'           => 'wp_enqueue_admin_bar_bump_styles',
        'print_embed_styles'           => 'wp_enqueue_embed_styles',
        'print_emoji_styles'           => 'wp_enqueue_emoji_styles',
        'wp_admin_bar_header'          => 'wp_enqueue_admin_bar_header_styles',
        'wp_img_tag_add_decoding_attr' => 'wp_img_tag_add_loading_optimization_attrs',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * CLASSES
     * - WP_Http_Curl
     * - WP_Http_Streams
     *
     * FILTERS
     * - http_api_transports
     *
     * METHODS
     * - WP_Http::_get_first_available_transport
     */
};
