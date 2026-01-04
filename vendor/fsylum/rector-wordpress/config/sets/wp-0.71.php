<?php

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('convert_chars', 1),
    ]);

    /*
     * TODO: these are not handled currently
     *
     * ARGUMENTS
     * - comments_link:1
     *
     * FUNCTIONS
     * - the_category_head
     * - the_category_ID
     */
};
