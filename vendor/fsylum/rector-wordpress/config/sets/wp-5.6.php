<?php

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'addslashes_strings_only' => 'wp_slash',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FILES
     * -wp-includes/class-wp-feed-cache.php
     *
     * FUNCTIONS
     * - wp_ajax_health_check_dotorg_communication
     * - wp_ajax_health_check_background_updates
     * - wp_ajax_health_check_loopback_requests
     * - wp_ajax_health_check_get_sizes
     * - wp_count_terms
     *
     * METHODS
     * - WP_Community_Events::format_event_data_time
     * - WP_REST_Meta_Fields::default_additional_properties_to_false
     * - WP_REST_Meta_Fields::register_field
     */
};
