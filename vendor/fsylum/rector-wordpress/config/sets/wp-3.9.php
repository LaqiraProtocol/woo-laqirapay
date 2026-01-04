<?php

use Fsylum\RectorWordPress\Rules\FuncCall\ReturnFirstArgumentRector;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        '_relocate_children',
    ]);

    $rectorConfig->ruleWithConfiguration(ReturnFirstArgumentRector::class, [
        'default_topic_count_text',
        'format_to_post',
        'get_current_site_name',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FUNCTIONS
     * - rich_edit_exists
     * - wpmu_current_site
     */
};
