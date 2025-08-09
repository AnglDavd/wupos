<?php
/**
 * WUPOS HPOS Compatibility Layer
 *
 * Handles WooCommerce High Performance Order Storage (HPOS) compatibility
 * ensuring seamless operation with both legacy and HPOS modes.
 *
 * @package WUPOS\HPOS
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS_HPOS_Compatibility class.
 *
 * Provides compatibility layer for WooCommerce HPOS functionality,
 * ensuring all POS operations work correctly with both storage modes.
 */
class WUPOS_HPOS_Compatibility {

    /**
     * HPOS enabled status cache
     *
     * @var bool|null
     */
    private static $hpos_enabled = null;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize HPOS compatibility hooks
     */
    private function init_hooks() {
        // Order meta compatibility
        add_filter('wupos_get_order_meta', array($this, 'get_order_meta_hpos_compatible'), 10, 3);
        add_filter('wupos_update_order_meta', array($this, 'update_order_meta_hpos_compatible'), 10, 4);
        
        // Order query compatibility
        add_filter('wupos_order_query_args', array($this, 'adjust_order_query_for_hpos'), 10, 2);
        
        // Performance optimization for HPOS
        add_action('woocommerce_before_order_object_save', array($this, 'optimize_hpos_order_save'), 10, 2);
        
        // Admin compatibility
        if (is_admin()) {
            add_action('admin_notices', array($this, 'hpos_status_notice'));
        }
    }

    /**
     * Check if HPOS is enabled and available
     *
     * @return bool True if HPOS is enabled
     */
    public static function is_hpos_enabled() {
        if (null !== self::$hpos_enabled) {
            return self::$hpos_enabled;
        }

        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            self::$hpos_enabled = false;
            return false;
        }

        // Check if HPOS feature is available (WC 7.1+)
        if (!class_exists('Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore')) {
            self::$hpos_enabled = false;
            return false;
        }

        // Check if HPOS is enabled in settings
        if (function_exists('wc_get_container')) {
            try {
                $orders_table_data_store = wc_get_container()->get('Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore');
                self::$hpos_enabled = $orders_table_data_store && method_exists($orders_table_data_store, 'is_table_exists') 
                    ? $orders_table_data_store->is_table_exists() 
                    : false;
            } catch (Exception $e) {
                self::$hpos_enabled = false;
                wupos_log('Error checking HPOS status: ' . $e->getMessage(), 'error');
            }
        } else {
            self::$hpos_enabled = false;
        }

        return self::$hpos_enabled;
    }

    /**
     * Get HPOS compatibility information
     *
     * @return array HPOS compatibility status
     */
    public static function get_hpos_info() {
        $hpos_enabled = self::is_hpos_enabled();
        
        return array(
            'hpos_enabled' => $hpos_enabled,
            'hpos_available' => class_exists('Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore'),
            'wc_version' => defined('WC_VERSION') ? WC_VERSION : '0.0.0',
            'hpos_tables_exist' => $hpos_enabled ? self::check_hpos_tables() : false,
            'storage_mode' => $hpos_enabled ? 'hpos' : 'legacy',
            'compatibility_status' => self::get_compatibility_status(),
        );
    }

    /**
     * Check if HPOS tables exist
     *
     * @return bool True if tables exist
     */
    private static function check_hpos_tables() {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'wc_orders',
            $wpdb->prefix . 'wc_order_addresses',
            $wpdb->prefix . 'wc_order_operational_data',
            $wpdb->prefix . 'wc_order_meta',
        );

        foreach ($tables as $table) {
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
            if (!$table_exists) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get compatibility status
     *
     * @return string Compatibility status
     */
    private static function get_compatibility_status() {
        if (!class_exists('WooCommerce')) {
            return 'woocommerce_missing';
        }

        if (version_compare(WC_VERSION, '7.1.0', '<')) {
            return 'wc_version_unsupported';
        }

        if (self::is_hpos_enabled()) {
            return self::check_hpos_tables() ? 'fully_compatible' : 'hpos_tables_missing';
        }

        return 'legacy_mode';
    }

    /**
     * Get order meta with HPOS compatibility
     *
     * @param mixed $value Current value
     * @param int $order_id Order ID
     * @param string $meta_key Meta key
     * @return mixed Meta value
     */
    public function get_order_meta_hpos_compatible($value, $order_id, $meta_key) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return $value;
        }

        return $order->get_meta($meta_key);
    }

    /**
     * Update order meta with HPOS compatibility
     *
     * @param bool $success Current success status
     * @param int $order_id Order ID
     * @param string $meta_key Meta key
     * @param mixed $meta_value Meta value
     * @return bool Success status
     */
    public function update_order_meta_hpos_compatible($success, $order_id, $meta_key, $meta_value) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return false;
        }

        try {
            $order->update_meta_data($meta_key, $meta_value);
            $order->save();
            return true;
        } catch (Exception $e) {
            wupos_log('Error updating order meta (HPOS): ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Adjust order query arguments for HPOS compatibility
     *
     * @param array $args Query arguments
     * @param string $context Query context
     * @return array Adjusted arguments
     */
    public function adjust_order_query_for_hpos($args, $context = 'default') {
        if (!self::is_hpos_enabled()) {
            return $args;
        }

        // For HPOS, we need to use WC_Order_Query instead of WP_Query
        if (isset($args['post_type']) && $args['post_type'] === 'shop_order') {
            unset($args['post_type']);
            
            // Convert post status to order status
            if (isset($args['post_status'])) {
                $args['status'] = str_replace('wc-', '', $args['post_status']);
                unset($args['post_status']);
            }
            
            // Convert meta query format
            if (isset($args['meta_query'])) {
                $args['meta'] = $args['meta_query'];
                unset($args['meta_query']);
            }
            
            // Convert date query
            if (isset($args['date_query'])) {
                if (isset($args['date_query']['after'])) {
                    $args['date_created'] = '>=' . $args['date_query']['after'];
                }
                if (isset($args['date_query']['before'])) {
                    $args['date_created'] = '<=' . $args['date_query']['before'];
                }
                unset($args['date_query']);
            }
        }

        return $args;
    }

    /**
     * Optimize HPOS order save operations
     *
     * @param WC_Order $order Order object
     * @param WC_Data_Store $data_store Data store object
     */
    public function optimize_hpos_order_save($order, $data_store) {
        if (!self::is_hpos_enabled()) {
            return;
        }

        // Add POS-specific optimization for HPOS
        if ($order->get_meta('_wupos_pos_order') === 'yes') {
            // Batch meta data updates for better performance
            $pos_meta = array(
                '_wupos_hpos_optimized' => 'yes',
                '_wupos_save_timestamp' => current_time('timestamp'),
            );
            
            foreach ($pos_meta as $key => $value) {
                $order->update_meta_data($key, $value);
            }
        }
    }

    /**
     * Create HPOS compatible order query
     *
     * @param array $args Query arguments
     * @return WC_Order_Query|WP_Query Query object
     */
    public static function create_order_query($args = array()) {
        if (self::is_hpos_enabled()) {
            // Use WC_Order_Query for HPOS
            $adjusted_args = self::convert_args_for_hpos($args);
            return new WC_Order_Query($adjusted_args);
        } else {
            // Use WP_Query for legacy mode
            $wp_args = self::convert_args_for_legacy($args);
            return new WP_Query($wp_args);
        }
    }

    /**
     * Convert query arguments for HPOS
     *
     * @param array $args Original arguments
     * @return array HPOS compatible arguments
     */
    private static function convert_args_for_hpos($args) {
        $hpos_args = array();
        
        // Map common arguments
        $arg_mapping = array(
            'posts_per_page' => 'limit',
            'paged' => 'page',
            'post_status' => 'status',
            'orderby' => 'orderby',
            'order' => 'order',
        );
        
        foreach ($arg_mapping as $wp_arg => $hpos_arg) {
            if (isset($args[$wp_arg])) {
                if ($wp_arg === 'post_status' && strpos($args[$wp_arg], 'wc-') === 0) {
                    $hpos_args[$hpos_arg] = str_replace('wc-', '', $args[$wp_arg]);
                } else {
                    $hpos_args[$hpos_arg] = $args[$wp_arg];
                }
            }
        }
        
        // Handle meta query
        if (isset($args['meta_query'])) {
            $hpos_args['meta_query'] = $args['meta_query'];
        }
        
        // Handle date queries
        if (isset($args['date_created'])) {
            $hpos_args['date_created'] = $args['date_created'];
        }
        
        return $hpos_args;
    }

    /**
     * Convert query arguments for legacy mode
     *
     * @param array $args Original arguments
     * @return array Legacy compatible arguments
     */
    private static function convert_args_for_legacy($args) {
        $legacy_args = array(
            'post_type' => 'shop_order',
        );
        
        // Direct mapping for legacy args
        $direct_mapping = array(
            'posts_per_page',
            'paged', 
            'orderby',
            'order',
            'meta_query',
            'date_query',
        );
        
        foreach ($direct_mapping as $arg) {
            if (isset($args[$arg])) {
                $legacy_args[$arg] = $args[$arg];
            }
        }
        
        // Handle status
        if (isset($args['status'])) {
            $status = $args['status'];
            if (strpos($status, 'wc-') !== 0) {
                $status = 'wc-' . $status;
            }
            $legacy_args['post_status'] = $status;
        } elseif (isset($args['post_status'])) {
            $legacy_args['post_status'] = $args['post_status'];
        }
        
        return $legacy_args;
    }

    /**
     * Get orders using HPOS compatible method
     *
     * @param array $args Query arguments
     * @return array Orders array
     */
    public static function get_orders($args = array()) {
        $start_time = microtime(true);
        
        try {
            if (self::is_hpos_enabled()) {
                // Use wc_get_orders for HPOS
                $orders = wc_get_orders(self::convert_args_for_hpos($args));
            } else {
                // Use WP_Query for legacy
                $query = new WP_Query(self::convert_args_for_legacy($args));
                $orders = array();
                
                if ($query->have_posts()) {
                    foreach ($query->posts as $post_id) {
                        $order = wc_get_order($post_id);
                        if ($order) {
                            $orders[] = $order;
                        }
                    }
                }
                wp_reset_postdata();
            }
            
            $query_time = microtime(true) - $start_time;
            
            wupos_log(sprintf('HPOS compatible order query completed in %s seconds (%d orders)', 
                number_format($query_time, 4), 
                count($orders)
            ), 'debug');
            
            return $orders;
            
        } catch (Exception $e) {
            wupos_log('Error in HPOS compatible order query: ' . $e->getMessage(), 'error');
            return array();
        }
    }

    /**
     * Batch update order meta with HPOS optimization
     *
     * @param int $order_id Order ID
     * @param array $meta_data Associative array of meta key => value
     * @return bool Success status
     */
    public static function batch_update_order_meta($order_id, $meta_data) {
        if (!is_array($meta_data) || empty($meta_data)) {
            return false;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        try {
            // Batch all meta updates before saving
            foreach ($meta_data as $key => $value) {
                $order->update_meta_data($key, $value);
            }
            
            // Single save operation for better performance
            $order->save();
            
            wupos_log(sprintf('Batch updated %d meta fields for order %d (HPOS: %s)', 
                count($meta_data), 
                $order_id,
                self::is_hpos_enabled() ? 'yes' : 'no'
            ), 'debug');
            
            return true;
            
        } catch (Exception $e) {
            wupos_log('Error in batch order meta update: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Show HPOS status notice in admin
     */
    public function hpos_status_notice() {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'wupos') === false) {
            return;
        }

        $hpos_info = self::get_hpos_info();
        
        if ($hpos_info['compatibility_status'] === 'fully_compatible') {
            echo '<div class="notice notice-success"><p>';
            echo '<strong>WUPOS:</strong> ' . esc_html__('HPOS (High Performance Order Storage) is enabled and fully compatible.', 'wupos');
            echo '</p></div>';
        } elseif ($hpos_info['compatibility_status'] === 'legacy_mode') {
            echo '<div class="notice notice-info"><p>';
            echo '<strong>WUPOS:</strong> ' . esc_html__('Running in legacy mode. Consider enabling HPOS for better performance.', 'wupos');
            echo '</p></div>';
        } elseif ($hpos_info['compatibility_status'] === 'wc_version_unsupported') {
            echo '<div class="notice notice-warning"><p>';
            echo '<strong>WUPOS:</strong> ' . esc_html__('WooCommerce 7.1+ required for HPOS support. Please update WooCommerce.', 'wupos');
            echo '</p></div>';
        } elseif ($hpos_info['compatibility_status'] === 'hpos_tables_missing') {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>WUPOS:</strong> ' . esc_html__('HPOS is enabled but tables are missing. Please check WooCommerce settings.', 'wupos');
            echo '</p></div>';
        }
    }

    /**
     * Test HPOS compatibility
     *
     * @return array Test results
     */
    public static function test_hpos_compatibility() {
        $tests = array();
        $start_time = microtime(true);
        
        // Test 1: Basic HPOS detection
        $tests['hpos_detection'] = array(
            'name' => 'HPOS Detection',
            'status' => self::is_hpos_enabled() ? 'pass' : 'info',
            'message' => self::is_hpos_enabled() ? 'HPOS is enabled' : 'Legacy mode active',
        );
        
        // Test 2: Order creation
        try {
            $test_order = wc_create_order();
            if ($test_order && !is_wp_error($test_order)) {
                $test_order->add_meta_data('_wupos_test', 'hpos_compatibility_test');
                $test_order->save();
                
                // Test meta retrieval
                $meta_value = $test_order->get_meta('_wupos_test');
                $meta_test_passed = ($meta_value === 'hpos_compatibility_test');
                
                // Cleanup
                $test_order->delete(true);
                
                $tests['order_operations'] = array(
                    'name' => 'Order Operations',
                    'status' => $meta_test_passed ? 'pass' : 'fail',
                    'message' => $meta_test_passed ? 'Order creation and meta operations work correctly' : 'Order meta operations failed',
                );
            } else {
                $tests['order_operations'] = array(
                    'name' => 'Order Operations',
                    'status' => 'fail',
                    'message' => 'Failed to create test order',
                );
            }
        } catch (Exception $e) {
            $tests['order_operations'] = array(
                'name' => 'Order Operations',
                'status' => 'fail',
                'message' => 'Exception: ' . $e->getMessage(),
            );
        }
        
        // Test 3: Query performance
        $query_start = microtime(true);
        $test_orders = wc_get_orders(array('limit' => 10));
        $query_time = microtime(true) - $query_start;
        
        $tests['query_performance'] = array(
            'name' => 'Query Performance',
            'status' => $query_time < 0.5 ? 'pass' : 'warning',
            'message' => sprintf('Query time: %s seconds (%d orders)', 
                number_format($query_time, 4), 
                count($test_orders)
            ),
        );
        
        // Overall test time
        $total_time = microtime(true) - $start_time;
        
        return array(
            'tests' => $tests,
            'summary' => array(
                'total_time' => number_format($total_time, 4),
                'hpos_enabled' => self::is_hpos_enabled(),
                'storage_mode' => self::is_hpos_enabled() ? 'hpos' : 'legacy',
                'wc_version' => defined('WC_VERSION') ? WC_VERSION : 'unknown',
            ),
        );
    }

    /**
     * Get performance comparison between HPOS and legacy modes
     *
     * @return array Performance comparison data
     */
    public static function get_performance_comparison() {
        return array(
            'current_mode' => self::is_hpos_enabled() ? 'hpos' : 'legacy',
            'hpos_benefits' => array(
                'Faster order queries',
                'Better database performance',
                'Improved scalability',
                'Reduced memory usage',
                'Better handling of large datasets',
            ),
            'migration_recommendation' => !self::is_hpos_enabled() && version_compare(WC_VERSION, '7.1.0', '>='),
            'performance_impact' => array(
                'query_speed' => self::is_hpos_enabled() ? 'up to 2x faster' : 'baseline',
                'memory_usage' => self::is_hpos_enabled() ? 'reduced by 20-30%' : 'baseline',
                'scalability' => self::is_hpos_enabled() ? 'excellent' : 'good',
            ),
        );
    }
}