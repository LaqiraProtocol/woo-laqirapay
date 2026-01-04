<?php

use Fsylum\RectorWordPress\Rules\MethodCall\RemoveMethodCallRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveMethodCallRector::class, [
        'add_tab'         => 'WP_Customize_Image_Control',
        'prepare_control' => 'WP_Customize_Image_Control',
        'print_tab_image' => 'WP_Customize_Image_Control',
        'remove_tab'      => 'WP_Customize_Image_Control',
    ]);
};
