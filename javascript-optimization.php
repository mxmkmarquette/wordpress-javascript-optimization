<?php
namespace O10n;

/**
 * Javascript Optimization
 *
 * Advanced Javascript optimization toolkit. Minify, concat/merge, async loading, advanced editor, ES Lint, UglifyJS (professional), beautifier, HTTP/2 Server Push and more.
 *
 * @link              https://github.com/o10n-x/
 * @package    optimization
 *
 * @wordpress-plugin
 * Plugin Name:       Javascript Optimization
 * Description:       Advanced Javascript optimization toolkit. Minify, concat/merge, async loading, advanced editor, ES Lint, UglifyJS (professional), beautifier, HTTP/2 Server Push and more.
 * Version:           0.0.49
 * Author:            Optimization.Team
 * Author URI:        https://optimization.team/
 * GitHub Plugin URI: https://github.com/o10n-x/wordpress-javascript-optimization
 * Text Domain:       o10n
 * Domain Path:       /languages
 */

if (! defined('WPINC')) {
    die;
}

// abort loading during upgrades
if (defined('WP_INSTALLING') && WP_INSTALLING) {
    return;
}

// settings
$module_version = '0.0.49';
$minimum_core_version = '0.0.38';
$plugin_path = dirname(__FILE__);

// load the optimization module loader
if (!class_exists('\O10n\Module')) {
    require $plugin_path . '/core/controllers/module.php';
}

// load module
new Module(
    'js',
    'Javascript Optimization',
    $module_version,
    $minimum_core_version,
    array(
        'core' => array(
            'http',
            'client',
            'proxy',
            'tools',
            'js'
        ),
        'admin' => array(
            'AdminJs',
            'AdminEditor'
        ),
        'admin_global' => array(
            'AdminGlobaljs'
        )
    ),
    3,
    array(
        'src' => array(
            'path' => 'js/src/',
            'file_ext' => '.js',
            'alt_exts' => array('.js.map'),
            'expire' => 259200 // expire after 3 days
        ),
        'concat' => array(
            'hash_id' => true, // store data by database index id
            'path' => 'js/concat/',
            'id_dir' => 'js/',
            'file_ext' => '.js',
            'alt_exts' => array('.js.map'),
            'expire' => 86400 // expire after 1 day
        ),
        'proxy' => array(
            'path' => 'js/proxy/',
            'file_ext' => '.js',
            'expire' => 86400 // expire after 1 day
        )
    ),
    __FILE__
);
