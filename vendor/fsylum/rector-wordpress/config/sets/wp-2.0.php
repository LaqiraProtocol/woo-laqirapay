<?php

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'create_user' => 'wp_create_user',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * ARGUMENTS
     * - wp_upload_bits:2
     *
     * FUNCTIONS
     * - next_post
     * - previous_post
     * - user_can_create_draft
     * - user_can_create_post
     * - user_can_delete_post
     * - user_can_delete_post_comments
     * - user_can_edit_post
     * - user_can_edit_post_comments
     * - user_can_edit_post_date
     * - user_can_edit_user
     * - user_can_set_post_date
     */
};
