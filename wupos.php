<?php
/**
 * Plugin Name: WUPOS - WooCommerce Ultimate Point of Sale
 * Plugin URI: https://github.com/username/wupos
 * Description: A complete Point of Sale system for WooCommerce with modern interface and advanced features.
 * Version: 1.0.0
 * Author: WUPOS Team
 * Author URI: https://github.com/username
 * Text Domain: wupos
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 9.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 *
 * @package WUPOS
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WUPOS_VERSION', '1.0.0');
define('WUPOS_PLUGIN_FILE', __FILE__);
define('WUPOS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WUPOS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WUPOS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main WUPOS Class
 *
 * @class WUPOS
 * @version 1.0.0
 */
final class WUPOS {
    
    /**
     * WUPOS version.
     *
     * @var string
     */
    public $version = '1.0.0';
    
    /**
     * The single instance of the class.
     *
     * @var WUPOS
     */
    protected static $instance = null;
    
    /**
     * Main WUPOS Instance.
     *
     * Ensures only one instance of WUPOS is loaded or can be loaded.
     *
     * @static
     * @return WUPOS - Main instance.
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * WUPOS Constructor.
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }
    
    /**
     * Define WUPOS Constants.
     */
    private function define_constants() {
        $this->define('WUPOS_ABSPATH', dirname(WUPOS_PLUGIN_FILE) . '/');
        $this->define('WUPOS_PLUGIN_BASENAME', plugin_basename(WUPOS_PLUGIN_FILE));
        $this->define('WUPOS_VERSION', $this->version);
    }
    
    /**
     * Define constant if not already set.
     *
     * @param string $name  Constant name.
     * @param string $value Constant value.
     */
    private function define($name, $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }
    
    /**
     * Include required core files used in admin and on the frontend.
     */
    public function includes() {
        // Include admin class if we're in admin
        if (is_admin()) {
            include_once WUPOS_ABSPATH . 'admin/class-wupos-admin.php';
        }
        
        // Include frontend classes
        include_once WUPOS_ABSPATH . 'includes/class-wupos-shortcode.php';
    }
    
    /**
     * Hook into actions and filters.
     */
    private function init_hooks() {
        register_activation_hook(WUPOS_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(WUPOS_PLUGIN_FILE, array($this, 'deactivate'));
        
        add_action('init', array($this, 'init'), 0);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    /**
     * Init WUPOS when WordPress Initialises.
     */
    public function init() {
        // Before init action.
        do_action('before_wupos_init');
        
        // Set up localisation.
        $this->load_plugin_textdomain();
        
        // Initialize shortcode
        if (class_exists('WUPOS_Shortcode')) {
            new WUPOS_Shortcode();
        }
        
        // Init action.
        do_action('wupos_init');
    }
    
    /**
     * Load Localisation files.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain('wupos', false, dirname(WUPOS_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Enqueue frontend scripts and styles.
     */
    public function enqueue_scripts() {
        wp_enqueue_style('wupos-style', WUPOS_PLUGIN_URL . 'assets/css/wupos.css', array(), WUPOS_VERSION);
        wp_enqueue_script('wupos-script', WUPOS_PLUGIN_URL . 'assets/js/wupos.js', array('jquery'), WUPOS_VERSION, true);
        
        // Localize script for AJAX
        wp_localize_script('wupos-script', 'wupos_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wupos_nonce')
        ));
    }
    
    /**
     * Enqueue admin scripts and styles.
     */
    public function admin_enqueue_scripts() {
        wp_enqueue_style('wupos-admin-style', WUPOS_PLUGIN_URL . 'assets/css/wupos-admin.css', array(), WUPOS_VERSION);
        wp_enqueue_script('wupos-admin-script', WUPOS_PLUGIN_URL . 'assets/js/wupos-admin.js', array('jquery'), WUPOS_VERSION, true);
    }
    
    /**
     * Plugin activation.
     */
    public function activate() {
        // Create database tables if needed
        $this->create_tables();
        
        // Set default options
        add_option('wupos_settings', array(
            'pos_page' => 0,
            'version' => WUPOS_VERSION
        ));
        
        // Clear the permalinks
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation.
     */
    public function deactivate() {
        // Clear the permalinks
        flush_rewrite_rules();
    }
    
    /**
     * Create plugin tables.
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // We'll add tables here when needed
        // For now, just ensure the function exists
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
}

/**
 * Main instance of WUPOS.
 *
 * Returns the main instance of WUPOS to prevent the need to use globals.
 *
 * @return WUPOS
 */
function WUPOS() {
    return WUPOS::instance();
}

// Global for backwards compatibility.
$GLOBALS['wupos'] = WUPOS();