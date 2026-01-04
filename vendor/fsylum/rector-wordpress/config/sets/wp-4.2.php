<?php

use Fsylum\RectorWordPress\Rules\MethodCall\RemoveMethodCallRector;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('iframe_header', 1),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveMethodCallRector::class, [
        'prepreview_added_sidebars_widgets' => 'WP_Customize_Widgets',
        'prepreview_added_widget_instance'  => 'WP_Customize_Widgets',
        'remove_prepreview_filters'         => 'WP_Customize_Widgets',
        'setup_widget_addition_previews'    => 'WP_Customize_Widgets',
    ]);
};
