<?php

namespace LaqiraPay\Domain\Models;

use LaqiraPay\Domain\Services\LaqiraLogger;

/**
 * Simple wrapper around WordPress options API.
 */
class Settings
{
    /**
     * Retrieve an option value from wp_options table.
     *
     * @param string $key     Option name.
     * @param mixed  $default Default value if option does not exist.
     *
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return get_option($key, $default);
    }

    /**
     * Update an option value in wp_options table.
     *
     * @param string $key   Option name.
     * @param mixed  $value Option value.
     *
     * @return bool
     */
    public static function set(string $key, $value): bool
    {
        $updated = update_option($key, $value);

        if (class_exists(LaqiraLogger::class)) {
            LaqiraLogger::log(
                $updated ? 200 : 400,
                'settings',
                'update_option',
                [$key => $value, 'result' => $updated ? 'updated' : 'failed']
            );
        }

        return $updated;
    }
}
