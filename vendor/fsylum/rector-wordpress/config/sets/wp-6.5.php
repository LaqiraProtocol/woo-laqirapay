<?php

use Fsylum\RectorWordPress\Rules\MethodCall\ParameterAdderRector;
use Fsylum\RectorWordPress\ValueObject\MethodParameterAdder;
use Rector\Config\RectorConfig;
use Rector\Transform\Rector\MethodCall\MethodCallToFuncCallRector;
use Rector\Transform\ValueObject\MethodCallToFuncCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(ParameterAdderRector::class, [
        new MethodParameterAdder('Custom_Image_Header', 'create_attachment_object', 2, 'custom-header'),
        new MethodParameterAdder('WP_Site_Icon', 'create_attachment_object', 2, 'site-icon'),
    ]);

    $rectorConfig->ruleWithConfiguration(MethodCallToFuncCallRector::class, [
        new MethodCallToFuncCall('Custom_Image_Header', 'create_attachment_object', 'wp_copy_parent_attachment_properties'),
        new MethodCallToFuncCall('WP_Site_Icon', 'create_attachment_object', 'wp_copy_parent_attachment_properties'),
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FUNCTIONS
     * - block_core_query_ensure_interactivity_dependency
     * - block_core_file_ensure_interactivity_dependency
     * - block_core_image_ensure_interactivity_dependency
     *
     * METHODS
     * - Translations::parenthesize_plural_exression
     */
};
