<?php

use Fsylum\RectorWordPress\Rules\FuncCall\ReturnFirstArgumentRector;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'install_global_terms',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'global_terms_enabled' => '__return_false',
    ]);

    $rectorConfig->ruleWithConfiguration(ReturnFirstArgumentRector::class, [
        '_wp_multiple_block_styles',
        'global_terms',
        'sync_category_tag_slugs',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FILES
     * - wp-includes/class.wp-db.php
     * - wp-includes/class.wp-dependencies.php
     * - wp-includes/class.wp-scripts.php
     * - wp-includes/class.wp-styles.php
     *
     * FUNCTIONS
     * - wp_get_attachment_thumb_file
     *
     * METHODS
     * - WP_REST_Settings_Controller::set_additional_properties_to_false
     */
};
