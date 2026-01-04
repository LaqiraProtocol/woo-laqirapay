<?php

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'upgrade_500',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * ACTIONS
     * - delete_blog
     * - deleted_blog
     * - wpmu_new_blog
     *
     * FUNCTIONS
     * - insert_blog
     * - install_blog
     *
     * METHODS
     * - WP_Http::_dispatch_request
     */
};
