<?php

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    /*
     * TODO: these are not handled currently
     *
     * FILTERS
     * - wp_get_default_privacy_policy_content
     *
     * FUNCTIONS
     * - noindex
     * - wp_no_robots
     * - wp_sensitive_page_meta
     *
     * METHODS
     * - WP_REST_Application_Passwords_Controller::do_permissions_check
     * - WP_REST_Themes_Controller::sanitize_theme_status
     */
};
