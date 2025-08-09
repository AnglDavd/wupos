<?php
/**
 * Plugin Name: WUPOS - Professional Point of Sale for WooCommerce
 * Plugin URI: https://github.com/AnglDavd/wupos
 * Description: Professional Terminal POS for WooCommerce with modern interface and robust functionality. 100% WooCommerce native integration.
 * Version: 0.1.0
 * Author: WooTPV Team
 * Author URI: https://github.com/AnglDavd/wupos
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wupos
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 8.0
 * WC requires at least: 8.0
 * WC tested up to: 8.5
 * Network: false
 * 
 * WUPOS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * 
 * WUPOS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WUPOS_VERSION', '0.1.0');
define('WUPOS_PLUGIN_FILE', __FILE__);
define('WUPOS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WUPOS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WUPOS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main WUPOS Class
 * 
 * @class WUPOS
 * @since 1.0.0
 */
final class WUPOS {

    /**
     * The single instance of the class.
     *
     * @var WUPOS
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main WUPOS Instance.
     *
     * Ensures only one instance of WUPOS is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @return WUPOS - Main instance.
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * WUPOS Constructor.
     * 
     * @since 1.0.0
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();

        do_action('wupos_loaded');
    }

    /**
     * Define WUPOS Constants.
     * 
     * @since 1.0.0
     */
    private function define_constants() {
        $this->define('WUPOS_ABSPATH', dirname(WUPOS_PLUGIN_FILE) . '/');
        $this->define('WUPOS_DEBUG', false);
    }

    /**
     * Define constant if not already set.
     *
     * @param string $name  Constant name.
     * @param string|bool $value Constant value.
     */
    private function define($name, $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend.
     * @return bool
     */
    private function is_request($type) {
        switch ($type) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
        }
    }

    /**
     * Include required core files used in admin and on the frontend.
     * 
     * @since 1.0.0
     */
    public function includes() {
        /**
         * Core classes.
         */
        include_once WUPOS_ABSPATH . 'includes/wupos-core-functions.php';
        include_once WUPOS_ABSPATH . 'includes/class-wupos-install.php';
        
        if ($this->is_request('admin')) {
            include_once WUPOS_ABSPATH . 'includes/admin/class-wupos-admin.php';
        }

        if ($this->is_request('frontend')) {
            $this->frontend_includes();
        }

        // REST API
        include_once WUPOS_ABSPATH . 'includes/api/class-wupos-rest-api.php';
    }

    /**
     * Include required frontend files.
     */
    public function frontend_includes() {
        include_once WUPOS_ABSPATH . 'includes/class-wupos-frontend.php';
        include_once WUPOS_ABSPATH . 'includes/class-wupos-pos.php';
    }

    /**
     * Hook into actions and filters.
     * 
     * @since 1.0.0
     */
    private function init_hooks() {
        register_activation_hook(WUPOS_PLUGIN_FILE, array('WUPOS_Install', 'install'));
        register_deactivation_hook(WUPOS_PLUGIN_FILE, array('WUPOS_Install', 'deactivate'));
        
        add_action('init', array($this, 'init'), 0);
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    }

    /**
     * Init WUPOS when WordPress Initialises.
     * 
     * @since 1.0.0
     */
    public function init() {
        // Before init action.
        do_action('before_wupos_init');

        // Set up localisation.
        $this->load_plugin_textdomain();

        // Init action.
        do_action('wupos_init');
    }

    /**
     * Load Localisation files.
     *
     * Note: the first-loaded translation file overrides any following ones if the same translation is present.
     *
     * Locales found in:
     *      - WP_LANG_DIR/wupos/wupos-LOCALE.mo
     *      - WP_LANG_DIR/plugins/wupos-LOCALE.mo
     *
     * @since 1.0.0
     */
    public function load_plugin_textdomain() {
        $locale = determine_locale();
        $locale = apply_filters('plugin_locale', $locale, 'wupos');

        unload_textdomain('wupos');
        load_textdomain('wupos', WP_LANG_DIR . '/wupos/wupos-' . $locale . '.mo');
        load_plugin_textdomain('wupos', false, plugin_basename(dirname(WUPOS_PLUGIN_FILE)) . '/languages');
    }

    /**
     * Get the plugin url.
     * 
     * @return string
     */
    public function plugin_url() {
        return untrailingslashit(plugins_url('/', WUPOS_PLUGIN_FILE));
    }

    /**
     * Get the plugin path.
     * 
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit(plugin_dir_path(WUPOS_PLUGIN_FILE));
    }

    /**
     * Get the template path.
     * 
     * @return string
     */
    public function template_path() {
        return apply_filters('wupos_template_path', 'wupos/');
    }

    /**
     * Get Ajax URL.
     * 
     * @return string
     */
    public function ajax_url() {
        return admin_url('admin-ajax.php', 'relative');
    }
}

/**
 * Main instance of WUPOS.
 *
 * Returns the main instance of WUPOS to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return WUPOS
 */
function WUPOS() {
    return WUPOS::instance();
}

// Check if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // Global for backwards compatibility.
    $GLOBALS['wupos'] = WUPOS();
} else {
    // WooCommerce not active - show admin notice
    add_action('admin_notices', function() {
        echo '<div class="error"><p><strong>' . esc_html__('WUPOS requires WooCommerce to be installed and active.', 'wupos') . '</strong></p></div>';
    });
}