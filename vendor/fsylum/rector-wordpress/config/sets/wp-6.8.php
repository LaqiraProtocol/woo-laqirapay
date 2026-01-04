<?php

use Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(FunctionArgumentDefaultValueReplacerRector::class, [
        new ReplaceFuncCallArgumentDefaultValue('add_action', 0, 'setted_transient', 'set_transient'),
        new ReplaceFuncCallArgumentDefaultValue('add_action', 0, 'setted_site_transient', 'set_site_transient'),
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FUNCTIONS
     * - wp_add_editor_classic_theme_styles
     *
     * PROPERTIES
     * - WP_HTML_Processor_State::$context_node
     */
};
