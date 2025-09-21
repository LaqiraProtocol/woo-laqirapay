<?php

namespace LaqiraPay\Domain\Services;

class UtilityService
{
    /**
     * Detect if the current locale is right-to-left.
     *
     * @return bool
     */
    public function detectRtl(): bool
    {
        // Use WordPress core detection if available, otherwise fallback to locale check.
        if (function_exists('is_rtl')) {
            return is_rtl();
        }

        $locale        = function_exists('get_locale') ? get_locale() : 'en_US';
        $rtl_languages = ['ar', 'he', 'fa', 'ur', 'ug', 'sd', 'ku', 'ps'];
        foreach ($rtl_languages as $rtl) {
            if (strpos($locale, $rtl) !== false) {
                return true;
            }
        }

        return false;
    }
}

