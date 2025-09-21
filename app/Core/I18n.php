<?php

namespace LaqiraPay\Core;

class I18n
{
    public static function load(): void
    {
        load_plugin_textdomain(
            'laqirapay',
            false,
            dirname(plugin_basename(__FILE__), 3) . '/languages'
        );
    }
}

