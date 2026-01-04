<?php

use Fsylum\RectorWordPress\Rules\MethodCall\RemoveMethodCallRector;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'wp_get_global_styles_custom_css'     => 'wp_get_global_stylesheet',
        'wp_enqueue_global_styles_custom_css' => 'wp_enqueue_global_styles',
        'current_user_can_for_blog'           => 'current_user_can_for_site',
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'wp_init_targeted_link_rel_filters',
        'wp_remove_targeted_link_rel_filters',
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveMethodCallRector::class, [
        'print_client_interactivity_data' => 'WP_Interactivity_API',
        'register_script_modules'         => 'WP_Interactivity_API',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('WP_Interactivity_API', 'print_router_loading_and_screen_reader_markup', 'print_router_markup'),
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FUNCTIONS
     * - wp_simplepie_autoload
     * - wp_create_block_style_variation_instance_name
     * - wp_targeted_link_rel
     * - wp_targeted_link_rel_callback
     *
     * METHODS
     * - WP_Sitemaps::redirect_sitemapxml
     * - WP_Theme_JSON::get_custom_css
     */
};
