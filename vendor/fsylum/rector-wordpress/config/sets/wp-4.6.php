<?php

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('install_search_form', 0),
        new RemoveFuncCallArg('wp_embed_handler_googlevideo', 0),
        new RemoveFuncCallArg('wp_embed_handler_googlevideo', 1),
        new RemoveFuncCallArg('wp_embed_handler_googlevideo', 2),
        new RemoveFuncCallArg('wp_embed_handler_googlevideo', 3),
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'wp_embed_handler_googlevideo' => '__return_empty_string',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FUNCTIONS
     * - post_form_autocomplete_off
     * - register_meta
     * - wp_get_sites
     */
};
