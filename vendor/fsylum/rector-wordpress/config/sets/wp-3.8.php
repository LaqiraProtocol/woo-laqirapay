<?php

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('wp_notify_postauthor', 1),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'wp_dashboard_incoming_links',
        'wp_dashboard_incoming_links_control',
        'wp_dashboard_incoming_links_output',
        'wp_dashboard_plugins',
        'wp_dashboard_primary_control',
        'wp_dashboard_recent_comments_control',
        'wp_dashboard_secondary',
        'wp_dashboard_secondary_control',
        'wp_dashboard_secondary_output',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FUNCTIONS
     * - get_screen_icon
     * - screen_icon
     */
};
