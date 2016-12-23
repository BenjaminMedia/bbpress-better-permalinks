<?php
/**
 * Plugin Name: Bonnier bbPress better permalinks
 * Version: 0.1.0
 * Plugin URI: https://github.com/BenjaminMedia/bbpress-better-permalinks
 * Description: This plugin gives you the ability to select a post in bbPress as the best answer
 * Author: Bonnier - Michael Sørensen
 * License: MIT
 */

namespace Bonnier\WP\BBPress\BetterPermalinks;


// Do not access this file directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle autoload so we can use namespaces
spl_autoload_register(function ($className) {
    if (strpos($className, __NAMESPACE__) !== false) {
        $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
        require_once(__DIR__ . DIRECTORY_SEPARATOR . Plugin::CLASS_DIR . DIRECTORY_SEPARATOR . $className . '.php');
    }
});


class Plugin
{
    /**
     * Text domain for translators
     */
    const TEXT_DOMAIN = 'bbpress-better-permalinks';

    const CLASS_DIR = 'src';

    /**
     * @var object Instance of this class.
     */
    private static $instance;

    public $settings;

    /**
     * @var string Filename of this class.
     */
    public $file;

    /**
     * @var string Basename of this class.
     */
    public $basename;

    /**
     * @var string Plugins directory for this plugin.
     */
    public $plugin_dir;

    /**
     * @var string Plugins url for this plugin.
     */
    public $plugin_url;

    /**
     * Do not load this more than once.
     */
    private function __construct()
    {
        // Set plugin file variables
        $this->file = __FILE__;
        $this->basename = plugin_basename($this->file);
        $this->plugin_dir = plugin_dir_path($this->file);
        $this->plugin_url = plugin_dir_url($this->file);

        // Load textdomain
        load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname($this->basename) . '/languages');
    }

    private function bootstrap()
    {
        Permalinks::bootstrap();
    }

    /**
     * Returns the instance of this class.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self;
            global $bbpress_better_permalinks;
            $bbpress_better_permalinks = self::$instance;
            self::$instance->bootstrap();

            /**
             * Run after the plugin has been loaded.
             */
            do_action('bbpress_better_permalinks_loaded');
        }

        return self::$instance;
    }

}

/**
 * @return Plugin $instance returns an instance of the plugin
 */
function instance()
{
    return Plugin::instance();
}

add_action('plugins_loaded', __NAMESPACE__ . '\instance', 0);
