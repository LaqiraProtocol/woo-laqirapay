<?php

use Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(FunctionArgumentDefaultValueReplacerRector::class, [
        new ReplaceFuncCallArgumentDefaultValue('add_filter', 0, 'login_headertitle', 'login_headertext'),
    ]);
};
