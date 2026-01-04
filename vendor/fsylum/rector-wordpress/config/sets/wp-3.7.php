<?php

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'the_attachment_links',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * ACTIONS
     * - dbx_post_advanced
     *
     * CLASSES
     * - WP_HTTP_Fsockopen
     *
     * FUNCTIONS
     * - _search_terms_tidy
     * - get_blogaddress_by_domain
     * - wp_update_core
     * - wp_update_plugin
     * - wp_update_theme
     */
};
