<?php

use Fsylum\RectorWordPress\Rules\FuncCall\ReturnFirstArgumentRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(ReturnFirstArgumentRector::class, [
        'wp_interactivity_process_directives_of_interactive_blocks',
        'wp_render_elements_support',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * METHODS
     * - WP_Theme_JSON::set_spacing_sizes
     */
};
