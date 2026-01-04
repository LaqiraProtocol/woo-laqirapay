<?php

use Fsylum\RectorWordPress\Rules\FuncCall\ParameterAdderRector;
use Fsylum\RectorWordPress\ValueObject\FunctionParameterAdder;
use Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\ConstFetch\RenameConstantRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(FunctionArgumentDefaultValueReplacerRector::class, [
        new ReplaceFuncCallArgumentDefaultValue('define', 0, 'FORCE_SSL_LOGIN', 'FORCE_SSL_ADMIN'),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('delete_plugins', 1),
    ]);

    $rectorConfig->ruleWithConfiguration(ParameterAdderRector::class, [
        new FunctionParameterAdder('get_all_category_ids', 0, [
            'taxonomy' => 'category',
            'fields'   => 'ids',
            'get'      => 'all',
        ]),
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'get_all_category_ids' => 'get_terms',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameConstantRector::class, [
        'FORCE_SSL_LOGIN' => 'FORCE_SSL_ADMIN',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FUNCTIONS
     * - like_escape
     * - url_is_accessable_via_ssl
     */
};
