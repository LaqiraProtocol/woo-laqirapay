<?php

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'wp_get_user_request_data' => 'wp_get_user_request',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FUNCTIONS
     * - wp_favicon_request
     *
     * METHODS
     * - POMO_CachedFileReader::POMO_CachedFileReader
     * - POMO_CachedIntFileReader::POMO_CachedIntFileReader
     * - POMO_FileReader::POMO_FileReader
     * - POMO_Reader::POMO_Reader
     * - POMO_StringReader::POMO_StringReader
     * - Translation_Entry::Translation_Entry
     */
};
