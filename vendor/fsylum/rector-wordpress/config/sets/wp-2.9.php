<?php

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    /*
     * TODO: these are not handled currently
     *
     * FUNCTIONS
     * - _c
     * - _nc
     * - get_real_file_to_edit
     * - make_url_footnote
     * - the_content_rss
     * - translate_with_context
     */
};
