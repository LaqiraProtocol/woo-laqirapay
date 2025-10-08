<?php

declare(strict_types=1);

use Fsylum\RectorWordPress\Set\WordPressSetList;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/app',
        __DIR__ . '/laqirapay.php',
        __DIR__ . '/index.php',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/vendor',
        __DIR__ . '/node_modules',
        __DIR__ . '/tests/Fixtures',
    ]);

    if (defined(SetList::class . '::WORDPRESS')) {
        $rectorConfig->sets([SetList::WORDPRESS]);
    } elseif (class_exists(WordPressSetList::class)) {
        $rectorConfig->sets([WordPressSetList::WP_0_71]);
    }

    if (class_exists('Rector\\CodingStyle\\Rector\\BinaryOp\\YodaStyleRector')) {
        $rectorConfig->rule('Rector\\CodingStyle\\Rector\\BinaryOp\\YodaStyleRector');
    }
};
