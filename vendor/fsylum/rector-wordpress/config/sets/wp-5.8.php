<?php

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        '_excerpt_render_inner_columns_blocks' => '_excerpt_render_inner_blocks',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FILTERS
     * - allowed_block_types
     * - block_categories
     * - block_editor_preload_paths
     * - block_editor_settings
     * - user_confirmed_action_email_content
     * - user_erasure_complete_email_headers
     * - user_erasure_complete_email_subject
     */
};
