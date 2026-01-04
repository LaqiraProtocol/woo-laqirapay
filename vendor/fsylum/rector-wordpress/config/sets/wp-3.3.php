<?php

use Fsylum\RectorWordPress\Rules\Deprecated\Functions\TheEditorRector;
use Fsylum\RectorWordPress\Rules\FuncCall\ParameterAdderRector;
use Fsylum\RectorWordPress\Rules\FuncCall\ParameterPrependerRector;
use Fsylum\RectorWordPress\ValueObject\FunctionParameterAdder;
use Fsylum\RectorWordPress\ValueObject\FunctionParameterPrepender;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(ParameterAdderRector::class, [
        new FunctionParameterAdder('start_post_rel_link', 3, true),
        new FunctionParameterAdder('type_url_form_audio', 0, 'audio'),
        new FunctionParameterAdder('type_url_form_file', 0, 'file'),
        new FunctionParameterAdder('type_url_form_image', 0, 'image'),
        new FunctionParameterAdder('type_url_form_video', 0, 'video'),
    ]);

    $rectorConfig->ruleWithConfiguration(ParameterPrependerRector::class, [
        new FunctionParameterPrepender('get_user_by_email', 'email'),
        new FunctionParameterPrepender('get_userdatabylogin', 'login'),
        new FunctionParameterPrepender('is_blog_user', new PhpParser\Node\Expr\FuncCall(new PhpParser\Node\Name('get_current_user_id'))),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        'wp_preload_dialogs',
        'wp_print_editor_js',
        'wp_quicktags',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'get_user_by_email'   => 'get_user_by',
        'get_userdatabylogin' => 'get_user_by',
        'is_blog_user'        => 'is_user_member_of_blog',
        'media_upload_audio'  => 'wp_media_upload_handler',
        'media_upload_file'   => 'wp_media_upload_handler',
        'media_upload_image'  => 'wp_media_upload_handler',
        'media_upload_video'  => 'wp_media_upload_handler',
        'start_post_rel_link' => 'get_boundary_post_rel_link',
        'type_url_form_audio' => 'wp_media_insert_url_form',
        'type_url_form_file'  => 'wp_media_insert_url_form',
        'type_url_form_image' => 'wp_media_insert_url_form',
        'type_url_form_video' => 'wp_media_insert_url_form',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('WP_Scripts', 'print_scripts_l10n', 'print_extra_script'),
    ]);

    $rectorConfig->rule(TheEditorRector::class);

    /*
     * TODO: these are not handled currently
     *
     * FILTERS
     *  - contextual_help
     *  - contextual_help_list
     *  - default_contextual_help
     *
     * FUNCTIONS
     * - add_contextual_help
     * - get_boundary_post_rel_link
     * - get_index_rel_link
     * - get_parent_post_rel_link
     * - get_user_metavalues
     * - index_rel_link
     * - parent_post_rel_link
     * - sanitize_user_object
     * - screen_layout
     * - screen_meta
     * - screen_options
     * - the_editor - CUSTOM RULE
     * - wp_admin_bar_dashboard_view_site_menu
     * - wp_tiny_mce
     * - wpmu_admin_do_redirect
     * - wpmu_admin_redirect_add_updated_param
     *
     * METHODS
     * - WP_Admin_Bar::recursive_render
     *
     * PROPERTIES
     * - WP_Admin_Bar::$menu
     */
};
