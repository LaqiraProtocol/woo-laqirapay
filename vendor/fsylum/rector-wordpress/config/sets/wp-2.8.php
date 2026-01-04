<?php

use Fsylum\RectorWordPress\Rules\FuncCall\ParameterAdderRector;
use Fsylum\RectorWordPress\Rules\FuncCall\ParameterPrependerRector;
use Fsylum\RectorWordPress\ValueObject\FunctionParameterAdder;
use Fsylum\RectorWordPress\ValueObject\FunctionParameterPrepender;
use Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(FunctionArgumentDefaultValueReplacerRector::class, [
        new ReplaceFuncCallArgumentDefaultValue('add_filter', 0, 'tagsperpage', 'edit_tags_per_page'),
    ]);

    $rectorConfig->ruleWithConfiguration(ParameterPrependerRector::class, [
        new FunctionParameterPrepender('get_author_name', 'display_name'),
    ]);

    $rectorConfig->ruleWithConfiguration(ParameterAdderRector::class, [
        new FunctionParameterAdder('get_the_author_aim', 0, 'aim'),
        new FunctionParameterAdder('get_the_author_description', 0, 'description'),
        new FunctionParameterAdder('get_the_author_email', 0, 'email'),
        new FunctionParameterAdder('get_the_author_firstname', 0, 'first_name'),
        new FunctionParameterAdder('get_the_author_icq', 0, 'icq'),
        new FunctionParameterAdder('get_the_author_ID', 0, 'ID'),
        new FunctionParameterAdder('get_the_author_lastname', 0, 'last_name'),
        new FunctionParameterAdder('get_the_author_login', 0, 'login'),
        new FunctionParameterAdder('get_the_author_msn', 0, 'msn'),
        new FunctionParameterAdder('get_the_author_nickname', 0, 'nickname'),
        new FunctionParameterAdder('get_the_author_url', 0, 'url'),
        new FunctionParameterAdder('get_the_author_yim', 0, 'yim'),
        new FunctionParameterAdder('the_author_aim', 0, 'aim'),
        new FunctionParameterAdder('the_author_description', 0, 'description'),
        new FunctionParameterAdder('the_author_email', 0, 'email'),
        new FunctionParameterAdder('the_author_firstname', 0, 'first_name'),
        new FunctionParameterAdder('the_author_icq', 0, 'icq'),
        new FunctionParameterAdder('the_author_ID', 0, 'ID'),
        new FunctionParameterAdder('the_author_lastname', 0, 'last_name'),
        new FunctionParameterAdder('the_author_login', 0, 'login'),
        new FunctionParameterAdder('the_author_msn', 0, 'msn'),
        new FunctionParameterAdder('the_author_nickname', 0, 'nickname'),
        new FunctionParameterAdder('the_author_url', 0, 'url'),
        new FunctionParameterAdder('the_author_yim', 0, 'yim'),
    ]);

    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('safecss_filter_attr', 1),
        new RemoveFuncCallArg('wp_get_sidebars_widgets', 0),
    ]);

    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        '__ngettext'                 => '_n',
        '__ngettext_noop'            => '_n_noop',
        'attribute_escape'           => 'esc_attr',
        'get_author_name'            => 'get_the_author_meta',
        'get_catname'                => 'get_cat_name',
        'get_the_author_aim'         => 'get_the_author_meta',
        'get_the_author_description' => 'get_the_author_meta',
        'get_the_author_email'       => 'get_the_author_meta',
        'get_the_author_firstname'   => 'get_the_author_meta',
        'get_the_author_icq'         => 'get_the_author_meta',
        'get_the_author_ID'          => 'get_the_author_meta',
        'get_the_author_lastname'    => 'get_the_author_meta',
        'get_the_author_login'       => 'get_the_author_meta',
        'get_the_author_msn'         => 'get_the_author_meta',
        'get_the_author_nickname'    => 'get_the_author_meta',
        'get_the_author_url'         => 'get_the_author_meta',
        'get_the_author_yim'         => 'get_the_author_meta',
        'js_escape'                  => 'esc_js',
        'the_author_aim'             => 'the_author_meta',
        'the_author_description'     => 'the_author_meta',
        'the_author_email'           => 'the_author_meta',
        'the_author_firstname'       => 'the_author_meta',
        'the_author_icq'             => 'the_author_meta',
        'the_author_ID'              => 'the_author_meta',
        'the_author_lastname'        => 'the_author_meta',
        'the_author_login'           => 'the_author_meta',
        'the_author_msn'             => 'the_author_meta',
        'the_author_nickname'        => 'the_author_meta',
        'the_author_url'             => 'the_author_meta',
        'the_author_yim'             => 'the_author_meta',
        'unregister_sidebar_widget'  => 'wp_unregister_sidebar_widget',
        'unregister_widget_control'  => 'wp_unregister_widget_control',
    ]);

    /*
     * TODO: these are not handled currently
     *
     * FUNCTIONS
     * - get_category_children
     * - register_sidebar_widget
     * - register_widget_control
     * - wp_specialchars
     *
     * METHODS
     * - wp_xmlrpc_server::login_pass_ok
     */
};
