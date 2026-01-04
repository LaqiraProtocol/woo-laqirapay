<?php

use Fsylum\RectorWordPress\Rules\FuncCall\ParameterAdderRector;
use Fsylum\RectorWordPress\ValueObject\FunctionParameterAdder;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('discover_pingback_server_uri', 1),
        new RemoveFuncCallArg('wp_get_http_headers', 1),
        new RemoveFuncCallArg('get_commentdata', 1),
        new RemoveFuncCallArg('get_commentdata', 2),
    ]);

    $rectorConfig->ruleWithConfiguration(ParameterAdderRector::class, [
        new FunctionParameterAdder('get_commentdata', 1, new PhpParser\Node\Expr\ConstFetch(new PhpParser\Node\Name('ARRAY_A'))),
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'get_commentdata' => 'get_comment',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * ARGUMENTS
     * - load_plugin_textdomain:2
     *
     * FUNCTIONS
     * - get_commentdata
     *
     * METHODS
     * - WP_Filesystem_Base::find_base_dir
     * - WP_Filesystem_Base::get_base_dir
     */
};
