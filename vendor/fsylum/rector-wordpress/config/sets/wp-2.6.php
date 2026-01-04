<?php

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    /*
     * TODO: these are not handled currently
     *
     * ARGUMENTS
     * - wp_install:5
     *
     * FUNCTIONS
     * - dropdown_categories
     * - dropdown_link_categories
     */
};
