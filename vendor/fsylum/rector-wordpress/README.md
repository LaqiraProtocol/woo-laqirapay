# Rector Rules for WordPress

This package is a [Rector](https://github.com/rectorphp/rector) extension developed to provide upgrades rules for WordPress.

## Install

Install the `rector-wordpress` package as dependency:

```bash
composer require fsylum/rector-wordpress --dev
```

## Use Sets

To add a set to your config, use `Fsylum\RectorWordPress\Set\WordPressSetList` class and pick one of the constants. For example, to update the codebase to WordPress 6.4, use `WordPressSetList::WP_6_4`.

```php
use Fsylum\RectorWordPress\Set\WordPressSetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        WordPressSetList::WP_6_4,
    ]);
};
```

You can also use a level set list to include all the applicable rules from the lowest version, 0.71 up to the one you specified. For example, `WordPressLevelSetList::UP_TO_WP_6_4` will include all the rules from WordPress 0.71 up to 6.4. In most cases, this is the preferable way to transform your code as you only need to specify it once.

```php
use Fsylum\RectorWordPress\Set\WordPressLevelSetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        WordPressLevelSetList::UP_TO_WP_6_4,
    ]);
};
```
