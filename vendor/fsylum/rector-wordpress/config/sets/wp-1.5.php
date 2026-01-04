<?php

use Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(FunctionArgumentDefaultValueReplacerRector::class, [
        new ReplaceFuncCallArgumentDefaultValue('add_action', 0, 'retreive_password', 'retrieve_password'),
        new ReplaceFuncCallArgumentDefaultValue('add_filter', 0, 'rewrite_rules', 'mod_rewrite_rules'),
    ]);

    /*
     * TODO: these are not handled currently
     *
     * ARGUMENTS
     * - the_author:2
     *
     * FILES
     * - my-hacks.php
     *
     * FUNCTIONS
     * - get_postdata
     * - start_wp
     */
};
