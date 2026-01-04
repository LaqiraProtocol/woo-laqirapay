<?php

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    /*
     * TODO: these are not handled currently
     *
     * ARGUMENTS
     * - bloginfo:1
     * - get_bloginfo:1
     *
     * FUNCTIONS
     * - comments_rss
     */
};
