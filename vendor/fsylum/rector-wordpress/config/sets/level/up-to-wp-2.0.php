<?php

use Fsylum\RectorWordPress\Set\WordPressLevelSetList;
use Fsylum\RectorWordPress\Set\WordPressSetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([WordPressSetList::WP_2_0, WordPressLevelSetList::UP_TO_WP_1_5]);
};
