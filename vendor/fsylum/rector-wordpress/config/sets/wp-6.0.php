<?php

use Fsylum\RectorWordPress\Rules\FuncCall\ReturnFirstArgumentRector;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'wp_add_iframed_editor_assets_html',
    ]);

    $rectorConfig->ruleWithConfiguration(ReturnFirstArgumentRector::class, [
        'image_attachment_fields_to_save',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FUNCTIONS
     * - the_meta
     *
     * METHODS
     * - WP_Theme_JSON::should_override_preset
     *
     * PROPERTIES
     * - PHPMailer::$SingleTo
     */
};
