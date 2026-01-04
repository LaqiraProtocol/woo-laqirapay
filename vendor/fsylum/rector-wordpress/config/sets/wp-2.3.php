<?php

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('permalink_single_rss', 0),
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'permalink_single_rss' => 'the_permalink_rss',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * ACTIONS
     * - private_to_published
     *
     * ARGUMENTS
     * - _future_post_hook:1
     * - add_option:3
     */
};
