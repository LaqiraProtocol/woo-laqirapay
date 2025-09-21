<?php

namespace LaqiraPay\Support;

class Requirements {
    public static function check() {
        if (version_compare(get_bloginfo('version'), '6.2', '<')) {
            return false;
        }
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            return false;
        }
        if (
            ! function_exists('WC')
            || ! is_object($wc = WC())
            || version_compare($wc->version, '8.2', '<')
        ) {
            return false;
        }
        return true;
    }
}

