<?php

use Fsylum\RectorWordPress\Rules\FuncCall\ParameterAdderRector as FuncParameterAdderRector;
use Fsylum\RectorWordPress\Rules\MethodCall\ParameterAdderRector as MethodParameterAdderRector;
use Fsylum\RectorWordPress\Rules\MethodCall\ReturnFirstArgumentRector;
use Fsylum\RectorWordPress\ValueObject\FunctionParameterAdder;
use Fsylum\RectorWordPress\ValueObject\MethodParameterAdder;
use Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(FunctionArgumentDefaultValueReplacerRector::class, [
        new ReplaceFuncCallArgumentDefaultValue('add_settings_field', 3, 'privacy', 'reading'),
        new ReplaceFuncCallArgumentDefaultValue('add_settings_section', 3, 'privacy', 'reading'),
        new ReplaceFuncCallArgumentDefaultValue('register_setting', 0, 'privacy', 'reading'),
        new ReplaceFuncCallArgumentDefaultValue('unregister_setting', 0, 'privacy', 'reading'),
    ]);

    $rectorConfig->ruleWithConfiguration(FuncParameterAdderRector::class, [
        new FunctionParameterAdder('get_default_page_to_edit', 0, 'page'),
        new FunctionParameterAdder('get_post_to_edit', 1, new PhpParser\Node\Expr\ConstFetch(new PhpParser\Node\Name('OBJECT'))),
        new FunctionParameterAdder('get_post_to_edit', 2, 'edit'),
        new FunctionParameterAdder('get_udims', 2, 128),
        new FunctionParameterAdder('get_udims', 3, 96),
    ]);

    $rectorConfig->ruleWithConfiguration(MethodParameterAdderRector::class, [
        new MethodParameterAdder('wpdb', 'supports_collation', 0, 'collation'),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('switch_to_blog', 1),
        new RemoveFuncCallArg('wp_create_thumbnail', 2),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallRector::class, [
        '_get_post_ancestors',
        '_insert_into_post_button',
        '_media_button',
        '_save_post_hook',
        'ms_deprecated_blogs_file',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'wp_get_single_post'       => 'get_post',
        'get_page'                 => 'get_post',
        'get_default_page_to_edit' => 'get_default_post_to_edit',
        'get_post_to_edit'         => 'get_post',
        'get_udims'                => 'wp_constrain_dimensions',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('wpdb', 'supports_collation', 'has_cap'),
    ]);

    $rectorConfig->ruleWithConfiguration(ReturnFirstArgumentRector::class, [
        'attachment_fields_to_edit' => 'Custom_Background',
        'filter_upload_tabs'        => 'Custom_Background',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * ARGUMENTS
     * - image_edit_apply_changes:1
     * - wp_stream_image:1
     *
     * FILTERS
     * - image_edit_before_change
     * - image_save_pre
     * - media_buttons_context
     * - wp_save_image_file
     *
     * FUNCTIONS
     * - _flip_image_resource
     * - _rotate_image_resource
     * - gd_edit_image_support
     * - image_resize
     * - sticky_class
     * - user_pass_ok
     * - wp_cache_reset
     * - wp_create_thumbnail
     * - wp_load_image
     *
     * METHODS
     * - Custom_Background::wp_set_background_image
     * - wp_atom_server::__call
     * - wp_atom_server::__callStatic
     * - WP_Object_Cache::reset
     * - wp_xmlrpc_server::blogger_getTemplate
     * - wp_xmlrpc_server::blogger_setTemplate
     *
     * PROPERTIES
     * - WP_Screen::$is_network
     * - WP_Screen::$is_user
     */
};
