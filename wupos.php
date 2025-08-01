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
        include_once WUPOS_ABSPATH . 'includes/class-wupos-products-api.php';
    }
    
    /**
     * Hook into actions and filters.
     */
    private function init_hooks() {
        register_activation_hook(WUPOS_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(WUPOS_PLUGIN_FILE, array($this, 'deactivate'));
        
        // Use wp_loaded for proper WooCommerce cart initialization timing
        add_action('wp_loaded', array($this, 'init'), 0);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('before_woocommerce_init', array($this, 'declare_wc_compatibility'));
        
        // Prevent WooCommerce cart access before wp_loaded
        add_action('init', array($this, 'prevent_early_cart_access'), 1);
        add_action('wp_head', array($this, 'disable_wc_blocks_hints'), 1);
    }
    
    /**
     * Init WUPOS when WordPress is fully loaded.
     * This ensures WooCommerce cart functions are available.
     */
    public function init() {
        // Before init action.
        do_action('before_wupos_init');
        
        // Set up localisation.
        $this->load_plugin_textdomain();
        
        // Enhanced WooCommerce compatibility check
        if (!$this->is_woocommerce_active() || !$this->is_woocommerce_ready()) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Ensure WooCommerce is fully initialized before proceeding
        if (!did_action('woocommerce_init')) {
            // If WooCommerce hasn't initialized yet, delay our initialization
            add_action('woocommerce_init', array($this, 'delayed_init'));
            return;
        }
        
        $this->initialize_components();
        
        // Init action.
        do_action('wupos_init');
    }
    
    /**
     * Initialize WUPOS components after WooCommerce is ready.
     */
    public function initialize_components() {
        // Initialize shortcode
        if (class_exists('WUPOS_Shortcode')) {
            new WUPOS_Shortcode();
        }
        
        // Initialize products API - only after WooCommerce is ready
        if (class_exists('WUPOS_Products_API')) {
            new WUPOS_Products_API();
        }
    }
    
    /**
     * Delayed initialization when WooCommerce is ready.
     */
    public function delayed_init() {
        $this->initialize_components();
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
    
    /**
     * Check if WooCommerce is active
     *
     * @return bool
     */
    public function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }
    
    /**
     * Check if WooCommerce is ready for cart operations
     *
     * @return bool
     */
    public function is_woocommerce_ready() {
        if (!$this->is_woocommerce_active()) {
            return false;
        }
        
        // Check if WooCommerce main functions are available
        if (!function_exists('WC') || !function_exists('wc_get_products')) {
            return false;
        }
        
        // Check if WooCommerce is properly initialized
        $wc = WC();
        if (!$wc) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Display notice when WooCommerce is not active
     */
    public function woocommerce_missing_notice() {
        $class = 'notice notice-error';
        $message = sprintf(
            /* translators: %1$s: Plugin name, %2$s: WooCommerce plugin link */
            __('%1$s requires WooCommerce to be installed and active. %2$s', 'wupos'),
            '<strong>WUPOS</strong>',
            '<a href="' . esc_url(admin_url('plugin-install.php?s=woocommerce&tab=search&type=term')) . '">' . __('Install WooCommerce', 'wupos') . '</a>'
        );
        
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), wp_kses_post($message));
    }
    
    /**
     * Prevent WooCommerce cart access before wp_loaded
     */
    public function prevent_early_cart_access() {
        // Check if we're likely on a WUPOS page
        if ($this->is_pos_page_request()) {
            // Disable WooCommerce Blocks cart access during wp_head
            add_filter('woocommerce_cart_ready_to_calc_shipping', '__return_false', 9999);
            
            // Remove problematic resource hints
            remove_action('wp_head', 'wp_resource_hints', 2);
            add_action('wp_head', array($this, 'safe_resource_hints'), 2);
        }
    }
    
    /**
     * Disable WooCommerce Blocks resource hints that access cart
     */
    public function disable_wc_blocks_hints() {
        if ($this->is_pos_page_request()) {
            // Use a safer approach to prevent WC Blocks from accessing cart early
            // Instead of instantiating the class directly, we'll hook into the appropriate actions
            add_filter('woocommerce_blocks_asset_api_get_script_url', array($this, 'filter_wc_blocks_scripts'), 10, 2);
            add_filter('wp_resource_hints', array($this, 'filter_wc_resource_hints'), 10, 2);
        }
    }
    
    /**
     * Filter WooCommerce Blocks scripts to prevent early cart access
     */
    public function filter_wc_blocks_scripts($url, $handle) {
        // Return the URL as-is but log the attempt for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WUPOS: WC Blocks script filtered: ' . $handle);
        }
        return $url;
    }
    
    /**
     * Filter resource hints to prevent early cart access
     */
    public function filter_wc_resource_hints($urls, $relation_type) {
        if (!is_array($urls)) {
            return $urls;
        }
        
        // Filter out any resource hints that might trigger early cart access
        $filtered_urls = array();
        foreach ($urls as $url) {
            $href = is_array($url) ? $url['href'] : $url;
            
            // Skip WooCommerce Blocks resources that might access cart
            if (strpos($href, 'wc-blocks') !== false || strpos($href, 'woocommerce-blocks') !== false) {
                continue;
            }
            
            $filtered_urls[] = $url;
        }
        
        return $filtered_urls;
    }
    
    /**
     * Safe resource hints that don't access cart
     */
    public function safe_resource_hints($urls, $relation_type) {
        // Ensure we have an array to work with
        if (!is_array($urls)) {
            $urls = array();
        }
        
        // Only add safe hints that don't require cart access
        if ('preload' === $relation_type) {
            // Add only essential WUPOS resources
            $urls[] = array(
                'href' => WUPOS_PLUGIN_URL . 'assets/css/wupos.css',
                'as'   => 'style',
            );
            $urls[] = array(
                'href' => WUPOS_PLUGIN_URL . 'assets/js/wupos.js',
                'as'   => 'script',
            );
        }
        return $urls;
    }
    
    /**
     * Check if current request is for a WUPOS POS page
     */
    private function is_pos_page_request() {
        global $post;
        
        // Safety check - ensure we don't access global $post too early
        if (!function_exists('has_shortcode')) {
            return false;
        }
        
        // Check for shortcode in post content
        if ($post && isset($post->post_content) && has_shortcode($post->post_content, 'wupos_pos')) {
            return true;
        }
        
        // Check for WUPOS AJAX requests
        if (isset($_POST['action']) && is_string($_POST['action']) && strpos($_POST['action'], 'wupos_') === 0) {
            return true;
        }
        
        // Check for WUPOS specific parameters
        if (isset($_GET['wupos']) || isset($_REQUEST['wupos_page'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Declare WooCommerce compatibility.
     */
    public function declare_wc_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', WUPOS_PLUGIN_FILE, true);
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', WUPOS_PLUGIN_FILE, true);
        }
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