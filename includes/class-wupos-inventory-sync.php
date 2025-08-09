<?php
/**
 * WUPOS Inventory Sync
 *
 * Handles real-time inventory synchronization with WooCommerce
 * including stock conflict resolution and multi-terminal support.
 *
 * @package WUPOS\Inventory
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS_Inventory_Sync class.
 *
 * Manages real-time inventory synchronization with conflict resolution
 * for multi-terminal POS environments.
 */
class WUPOS_Inventory_Sync {

    /**
     * Stock reservation timeout (in seconds)
     */
    const RESERVATION_TIMEOUT = 300; // 5 minutes

    /**
     * Cache manager instance
     *
     * @var WUPOS_Cache_Manager
     */
    private $cache_manager;

    /**
     * Active stock reservations
     *
     * @var array
     */
    private $active_reservations = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->cache_manager = new WUPOS_Cache_Manager();
        $this->init_hooks();
        $this->load_active_reservations();
    }

    /**
     * Initialize hooks for inventory synchronization
     */
    private function init_hooks() {
        // WooCommerce stock change hooks
        add_action('woocommerce_product_set_stock', array($this, 'handle_stock_change'), 10, 3);
        add_action('woocommerce_variation_set_stock', array($this, 'handle_variation_stock_change'), 10, 3);
        
        // Order lifecycle hooks
        add_action('woocommerce_order_status_pending', array($this, 'handle_order_pending'), 10, 1);
        add_action('woocommerce_order_status_processing', array($this, 'handle_order_processing'), 10, 1);
        add_action('woocommerce_order_status_completed', array($this, 'handle_order_completed'), 10, 1);
        add_action('woocommerce_order_status_cancelled', array($this, 'handle_order_cancelled'), 10, 1);
        
        // Stock reduction/restoration hooks
        add_action('woocommerce_reduce_order_stock', array($this, 'handle_stock_reduction'), 10, 1);
        add_action('woocommerce_restore_order_stock', array($this, 'handle_stock_restoration'), 10, 1);
        
        // Cleanup expired reservations
        add_action('wupos_cleanup_reservations', array($this, 'cleanup_expired_reservations'));
        
        // Schedule cleanup if not already scheduled
        if (!wp_next_scheduled('wupos_cleanup_reservations')) {
            wp_schedule_event(time(), 'hourly', 'wupos_cleanup_reservations');
        }
    }

    /**
     * Get real-time stock information for a product
     *
     * @param int $product_id Product ID
     * @return array Stock information with real-time data
     */
    public function get_real_time_stock($product_id) {
        $product_id = absint($product_id);
        
        if (!$product_id) {
            return $this->get_error_response('invalid_product_id', 'Invalid product ID provided.');
        }

        $start_time = microtime(true);
        
        // Check cache first (1 minute cache for stock)
        $cache_key = 'stock_' . $product_id;
        $cached_stock = $this->cache_manager->get_stock_cache($cache_key);
        
        if (false !== $cached_stock && $this->is_stock_cache_valid($cached_stock)) {
            $cached_stock['from_cache'] = true;
            $cached_stock['query_time'] = number_format(microtime(true) - $start_time, 4);
            return $cached_stock;
        }

        try {
            $product = wc_get_product($product_id);
            
            if (!$product) {
                return $this->get_error_response('product_not_found', 'Product not found.');
            }

            // Get current stock from WooCommerce
            $stock_data = array(
                'product_id'      => $product_id,
                'current_stock'   => $product->get_stock_quantity(),
                'stock_status'    => $product->get_stock_status(),
                'manage_stock'    => $product->get_manage_stock(),
                'backorders'      => $product->get_backorders(),
                'low_stock_amount' => $product->get_low_stock_amount(),
                'stock_reservations' => $this->get_product_reservations($product_id),
                'available_stock' => $this->calculate_available_stock($product),
                'low_stock_threshold' => $this->get_low_stock_threshold($product),
                'is_low_stock'    => $this->is_low_stock($product),
                'last_updated'    => current_time('timestamp'),
                'query_time'      => number_format(microtime(true) - $start_time, 4),
                'from_cache'      => false,
            );

            // Add variation stock if it's a variable product
            if ($product->is_type('variable')) {
                $stock_data['variations'] = $this->get_variation_stock($product);
            }

            // Add HPOS compatibility data
            if (wupos_is_hpos_enabled()) {
                $stock_data['hpos_enabled'] = true;
                $stock_data['stock_source'] = 'hpos';
            } else {
                $stock_data['hpos_enabled'] = false;
                $stock_data['stock_source'] = 'legacy';
            }

            // Cache the stock data (1 minute cache)
            $this->cache_manager->set_stock_cache($cache_key, $stock_data, 60);
            
            wupos_log(sprintf('Real-time stock loaded for product %d in %s seconds', 
                $product_id, 
                $stock_data['query_time']
            ), 'debug');

            return $stock_data;

        } catch (Exception $e) {
            wupos_log('Error in get_real_time_stock: ' . $e->getMessage(), 'error');
            return $this->get_error_response('stock_fetch_failed', $e->getMessage());
        }
    }

    /**
     * Update product stock with conflict resolution
     *
     * @param int $product_id Product ID
     * @param int $quantity New quantity or change amount
     * @param string $operation Operation type (set, increase, decrease)
     * @param array $options Additional options
     * @return array|WP_Error Operation result
     */
    public function update_stock($product_id, $quantity, $operation = 'set', $options = array()) {
        $product_id = absint($product_id);
        $quantity = is_numeric($quantity) ? floatval($quantity) : 0;
        
        if (!$product_id) {
            return new WP_Error('invalid_product_id', __('Invalid product ID.', 'wupos'), array('status' => 400));
        }

        $defaults = array(
            'terminal_id' => '',
            'user_id' => get_current_user_id(),
            'reason' => 'pos_adjustment',
            'force_update' => false,
            'note' => '',
        );
        
        $options = wp_parse_args($options, $defaults);
        
        try {
            $product = wc_get_product($product_id);
            
            if (!$product) {
                return new WP_Error('product_not_found', __('Product not found.', 'wupos'), array('status' => 404));
            }

            if (!$product->get_manage_stock()) {
                return new WP_Error('stock_not_managed', __('Stock is not managed for this product.', 'wupos'), array('status' => 400));
            }

            // Get current stock with lock
            $current_stock = $this->get_locked_stock($product);
            
            if (is_wp_error($current_stock)) {
                return $current_stock;
            }

            // Calculate new stock based on operation
            $new_stock = $this->calculate_new_stock($current_stock, $quantity, $operation);
            
            // Validate new stock amount
            $validation_result = $this->validate_stock_change($product, $current_stock, $new_stock, $options);
            
            if (is_wp_error($validation_result)) {
                return $validation_result;
            }

            // Perform the stock update
            $update_result = $this->perform_stock_update($product, $new_stock, $options);
            
            if (is_wp_error($update_result)) {
                return $update_result;
            }

            // Log the stock change
            $this->log_stock_change($product_id, $current_stock, $new_stock, $operation, $options);
            
            // Invalidate cache
            $this->invalidate_stock_cache($product_id);
            
            // Trigger stock change hook
            do_action('wupos_stock_changed', $product_id, $current_stock, $new_stock, $operation, $options);
            
            return array(
                'success' => true,
                'product_id' => $product_id,
                'previous_stock' => $current_stock,
                'new_stock' => $new_stock,
                'operation' => $operation,
                'quantity_changed' => $quantity,
                'timestamp' => current_time('timestamp'),
                'user_id' => $options['user_id'],
                'terminal_id' => $options['terminal_id'],
            );

        } catch (Exception $e) {
            wupos_log('Error in update_stock: ' . $e->getMessage(), 'error');
            return new WP_Error('stock_update_failed', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Reserve stock for pending orders
     *
     * @param int $product_id Product ID
     * @param int $quantity Quantity to reserve
     * @param string $order_key Order key or reservation ID
     * @param int $timeout Reservation timeout in seconds
     * @return array|WP_Error Reservation result
     */
    public function reserve_stock($product_id, $quantity, $order_key, $timeout = null) {
        $product_id = absint($product_id);
        $quantity = absint($quantity);
        
        if (!$product_id || !$quantity) {
            return new WP_Error('invalid_parameters', __('Invalid product ID or quantity.', 'wupos'), array('status' => 400));
        }

        if (null === $timeout) {
            $timeout = self::RESERVATION_TIMEOUT;
        }

        try {
            $product = wc_get_product($product_id);
            
            if (!$product || !$product->get_manage_stock()) {
                return new WP_Error('invalid_product', __('Product not found or stock not managed.', 'wupos'), array('status' => 400));
            }

            // Check available stock
            $available_stock = $this->calculate_available_stock($product);
            
            if ($available_stock < $quantity) {
                return new WP_Error('insufficient_stock', 
                    sprintf(__('Insufficient stock. Available: %d, Requested: %d', 'wupos'), $available_stock, $quantity),
                    array('status' => 400)
                );
            }

            // Create reservation
            $reservation = array(
                'product_id' => $product_id,
                'quantity' => $quantity,
                'order_key' => sanitize_text_field($order_key),
                'user_id' => get_current_user_id(),
                'terminal_id' => wupos_get_session_id(),
                'created_at' => current_time('timestamp'),
                'expires_at' => current_time('timestamp') + $timeout,
                'status' => 'active',
            );

            // Save reservation
            $reservation_id = $this->save_stock_reservation($reservation);
            
            if (!$reservation_id) {
                return new WP_Error('reservation_failed', __('Failed to create stock reservation.', 'wupos'), array('status' => 500));
            }

            // Invalidate stock cache
            $this->invalidate_stock_cache($product_id);
            
            wupos_log("Stock reserved: Product {$product_id}, Quantity {$quantity}, Reservation ID {$reservation_id}", 'debug');
            
            return array(
                'success' => true,
                'reservation_id' => $reservation_id,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'order_key' => $order_key,
                'expires_at' => $reservation['expires_at'],
                'available_stock' => $available_stock - $quantity,
            );

        } catch (Exception $e) {
            wupos_log('Error in reserve_stock: ' . $e->getMessage(), 'error');
            return new WP_Error('reservation_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Release stock reservation
     *
     * @param string $order_key Order key or reservation ID
     * @param int $product_id Optional product ID for specific release
     * @return array|WP_Error Release result
     */
    public function release_stock_reservation($order_key, $product_id = null) {
        try {
            $reservations = $this->get_reservations_by_order_key($order_key, $product_id);
            
            if (empty($reservations)) {
                return new WP_Error('reservation_not_found', __('No reservations found for the given order key.', 'wupos'), array('status' => 404));
            }

            $released_count = 0;
            $released_products = array();
            
            foreach ($reservations as $reservation) {
                if ($this->delete_stock_reservation($reservation['id'])) {
                    $released_count++;
                    $released_products[] = $reservation['product_id'];
                    
                    // Invalidate stock cache for this product
                    $this->invalidate_stock_cache($reservation['product_id']);
                }
            }
            
            if ($released_count > 0) {
                wupos_log("Released {$released_count} stock reservations for order key: {$order_key}", 'debug');
                
                return array(
                    'success' => true,
                    'released_count' => $released_count,
                    'released_products' => $released_products,
                    'order_key' => $order_key,
                );
            }
            
            return new WP_Error('release_failed', __('Failed to release stock reservations.', 'wupos'), array('status' => 500));

        } catch (Exception $e) {
            wupos_log('Error in release_stock_reservation: ' . $e->getMessage(), 'error');
            return new WP_Error('release_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Handle stock change events
     *
     * @param WC_Product $product Product object
     * @param int $stock_quantity New stock quantity
     * @param int $old_stock Previous stock quantity
     */
    public function handle_stock_change($product, $stock_quantity, $old_stock) {
        if (!$product) {
            return;
        }

        $product_id = $product->get_id();
        
        // Invalidate cache
        $this->invalidate_stock_cache($product_id);
        
        // Log significant stock changes
        if (abs($stock_quantity - $old_stock) > 0) {
            wupos_log(sprintf('Stock changed for product %d: %d -> %d', 
                $product_id, 
                $old_stock, 
                $stock_quantity
            ), 'debug');
        }
        
        // Check for low stock alerts
        $this->check_low_stock_alert($product, $stock_quantity);
        
        // Trigger custom hook for POS notifications
        do_action('wupos_real_time_stock_changed', $product_id, $stock_quantity, $old_stock);
    }

    /**
     * Handle variation stock changes
     *
     * @param WC_Product_Variation $variation Variation object
     * @param int $stock_quantity New stock quantity
     * @param int $old_stock Previous stock quantity
     */
    public function handle_variation_stock_change($variation, $stock_quantity, $old_stock) {
        if (!$variation) {
            return;
        }

        $variation_id = $variation->get_id();
        $parent_id = $variation->get_parent_id();
        
        // Invalidate cache for both variation and parent
        $this->invalidate_stock_cache($variation_id);
        $this->invalidate_stock_cache($parent_id);
        
        wupos_log(sprintf('Variation stock changed for %d (parent: %d): %d -> %d', 
            $variation_id, 
            $parent_id, 
            $old_stock, 
            $stock_quantity
        ), 'debug');
        
        do_action('wupos_variation_stock_changed', $variation_id, $parent_id, $stock_quantity, $old_stock);
    }

    /**
     * Handle order pending status
     *
     * @param int $order_id Order ID
     */
    public function handle_order_pending($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }

        // For POS orders, reserve stock when order is pending
        if ($order->get_meta('_wupos_pos_order') === 'yes') {
            $this->reserve_order_stock($order);
        }
    }

    /**
     * Handle order processing status
     *
     * @param int $order_id Order ID
     */
    public function handle_order_processing($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }

        // Release reservations and reduce actual stock
        if ($order->get_meta('_wupos_pos_order') === 'yes') {
            $this->release_stock_reservation($order->get_order_key());
        }
    }

    /**
     * Handle order completed status
     *
     * @param int $order_id Order ID
     */
    public function handle_order_completed($order_id) {
        // Stock should already be reduced, just log completion
        wupos_log("POS order {$order_id} completed", 'debug');
        do_action('wupos_order_stock_completed', $order_id);
    }

    /**
     * Handle order cancelled status
     *
     * @param int $order_id Order ID
     */
    public function handle_order_cancelled($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }

        // Release any reservations for cancelled POS orders
        if ($order->get_meta('_wupos_pos_order') === 'yes') {
            $this->release_stock_reservation($order->get_order_key());
        }
    }

    /**
     * Handle stock reduction for orders
     *
     * @param WC_Order $order Order object
     */
    public function handle_stock_reduction($order) {
        if (!$order) {
            return;
        }

        $order_id = $order->get_id();
        
        // For POS orders, release reservations since stock is now reduced
        if ($order->get_meta('_wupos_pos_order') === 'yes') {
            $this->release_stock_reservation($order->get_order_key());
            wupos_log("Stock reduced for POS order {$order_id}", 'debug');
        }
    }

    /**
     * Handle stock restoration for orders
     *
     * @param WC_Order $order Order object
     */
    public function handle_stock_restoration($order) {
        if (!$order) {
            return;
        }

        $order_id = $order->get_id();
        
        if ($order->get_meta('_wupos_pos_order') === 'yes') {
            wupos_log("Stock restored for POS order {$order_id}", 'debug');
            
            // Invalidate stock cache for all products in the order
            foreach ($order->get_items() as $item) {
                $product_id = $item->get_product_id();
                $this->invalidate_stock_cache($product_id);
            }
        }
    }

    /**
     * Calculate available stock considering reservations
     *
     * @param WC_Product $product Product object
     * @return int Available stock quantity
     */
    private function calculate_available_stock($product) {
        if (!$product->get_manage_stock()) {
            return PHP_INT_MAX;
        }

        $current_stock = $product->get_stock_quantity();
        $reserved_stock = $this->get_total_reserved_stock($product->get_id());
        
        return max(0, $current_stock - $reserved_stock);
    }

    /**
     * Get total reserved stock for a product
     *
     * @param int $product_id Product ID
     * @return int Total reserved quantity
     */
    private function get_total_reserved_stock($product_id) {
        $reservations = $this->get_product_reservations($product_id);
        $total_reserved = 0;
        
        foreach ($reservations as $reservation) {
            if ($reservation['status'] === 'active' && $reservation['expires_at'] > current_time('timestamp')) {
                $total_reserved += $reservation['quantity'];
            }
        }
        
        return $total_reserved;
    }

    /**
     * Get product reservations
     *
     * @param int $product_id Product ID
     * @return array Active reservations
     */
    private function get_product_reservations($product_id) {
        global $wpdb;
        
        $reservations = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->options} 
                 WHERE option_name LIKE %s 
                 AND option_name LIKE %s",
                'wupos_stock_reservation_%',
                '%_product_' . $product_id
            ),
            ARRAY_A
        );
        
        $active_reservations = array();
        
        foreach ($reservations as $row) {
            $reservation_data = maybe_unserialize($row['option_value']);
            if ($reservation_data && is_array($reservation_data)) {
                $reservation_data['id'] = str_replace('wupos_stock_reservation_', '', $row['option_name']);
                $active_reservations[] = $reservation_data;
            }
        }
        
        return $active_reservations;
    }

    /**
     * Save stock reservation
     *
     * @param array $reservation Reservation data
     * @return string|false Reservation ID or false on failure
     */
    private function save_stock_reservation($reservation) {
        $reservation_id = 'wupos_' . wp_generate_password(8, false) . '_' . time();
        $option_name = 'wupos_stock_reservation_' . $reservation_id;
        
        if (add_option($option_name, $reservation, '', 'no')) {
            return $reservation_id;
        }
        
        return false;
    }

    /**
     * Delete stock reservation
     *
     * @param string $reservation_id Reservation ID
     * @return bool Success status
     */
    private function delete_stock_reservation($reservation_id) {
        $option_name = 'wupos_stock_reservation_' . $reservation_id;
        return delete_option($option_name);
    }

    /**
     * Get reservations by order key
     *
     * @param string $order_key Order key
     * @param int $product_id Optional product ID filter
     * @return array Reservations
     */
    private function get_reservations_by_order_key($order_key, $product_id = null) {
        global $wpdb;
        
        $reservations = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} 
                 WHERE option_name LIKE %s",
                'wupos_stock_reservation_%'
            ),
            ARRAY_A
        );
        
        $matching_reservations = array();
        
        foreach ($reservations as $row) {
            $reservation_data = maybe_unserialize($row['option_value']);
            if ($reservation_data && is_array($reservation_data) && $reservation_data['order_key'] === $order_key) {
                if (null === $product_id || $reservation_data['product_id'] == $product_id) {
                    $reservation_data['id'] = str_replace('wupos_stock_reservation_', '', $row['option_name']);
                    $matching_reservations[] = $reservation_data;
                }
            }
        }
        
        return $matching_reservations;
    }

    /**
     * Update stock reservation quantity
     *
     * @param string $reservation_id Reservation ID
     * @param int $quantity_change Quantity change (positive or negative)
     * @return array|WP_Error Update result
     */
    public function update_stock_reservation($reservation_id, $quantity_change) {
        try {
            $option_name = 'wupos_stock_reservation_' . $reservation_id;
            $reservation_data = get_option($option_name);
            
            if (!$reservation_data) {
                return new WP_Error('reservation_not_found', __('Stock reservation not found.', 'wupos'), array('status' => 404));
            }

            $new_quantity = $reservation_data['quantity'] + $quantity_change;
            
            if ($new_quantity <= 0) {
                // Delete reservation if quantity becomes zero or negative
                return $this->delete_stock_reservation($reservation_id) ? 
                    array('success' => true, 'action' => 'deleted', 'quantity' => 0) :
                    new WP_Error('deletion_failed', __('Failed to delete reservation.', 'wupos'));
            }

            // Check if new quantity is available
            $product = wc_get_product($reservation_data['product_id']);
            if (!$product) {
                return new WP_Error('product_not_found', __('Product not found.', 'wupos'));
            }

            $current_available = $this->calculate_available_stock($product) + $reservation_data['quantity'];
            
            if ($new_quantity > $current_available) {
                return new WP_Error('insufficient_stock', 
                    sprintf(__('Insufficient stock for reservation update. Available: %d, Requested: %d', 'wupos'), 
                    $current_available, $new_quantity));
            }

            // Update reservation
            $reservation_data['quantity'] = $new_quantity;
            $reservation_data['updated_at'] = current_time('timestamp');
            
            update_option($option_name, $reservation_data);
            
            // Invalidate stock cache
            $this->invalidate_stock_cache($reservation_data['product_id']);
            
            wupos_log("Stock reservation updated: ID {$reservation_id}, new quantity {$new_quantity}", 'debug');
            
            return array(
                'success' => true,
                'action' => 'updated',
                'reservation_id' => $reservation_id,
                'quantity' => $new_quantity,
                'product_id' => $reservation_data['product_id']
            );

        } catch (Exception $e) {
            wupos_log('Error in update_stock_reservation: ' . $e->getMessage(), 'error');
            return new WP_Error('update_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Check stock availability for multiple products (batch check)
     *
     * @param array $products Array of product_id => quantity pairs
     * @return array Availability check results
     */
    public function batch_check_stock_availability($products) {
        $results = array();
        $overall_available = true;
        
        foreach ($products as $product_id => $quantity) {
            $product = wc_get_product($product_id);
            
            if (!$product) {
                $results[$product_id] = array(
                    'available' => false,
                    'error' => 'product_not_found',
                    'message' => 'Product not found',
                    'requested' => $quantity,
                    'available_stock' => 0
                );
                $overall_available = false;
                continue;
            }

            if (!$product->get_manage_stock()) {
                $results[$product_id] = array(
                    'available' => true,
                    'requested' => $quantity,
                    'available_stock' => 'unlimited',
                    'stock_status' => $product->get_stock_status()
                );
                continue;
            }

            $available_stock = $this->calculate_available_stock($product);
            $is_available = $available_stock >= $quantity;
            
            if (!$is_available) {
                $overall_available = false;
            }

            $results[$product_id] = array(
                'available' => $is_available,
                'requested' => $quantity,
                'available_stock' => $available_stock,
                'current_stock' => $product->get_stock_quantity(),
                'reserved_stock' => $this->get_total_reserved_stock($product_id),
                'stock_status' => $product->get_stock_status(),
                'backorders' => $product->get_backorders()
            );

            if (!$is_available) {
                $results[$product_id]['error'] = 'insufficient_stock';
                $results[$product_id]['message'] = sprintf(
                    'Insufficient stock. Available: %d, Requested: %d',
                    $available_stock,
                    $quantity
                );
            }
        }
        
        return array(
            'overall_available' => $overall_available,
            'products' => $results,
            'timestamp' => current_time('timestamp')
        );
    }

    /**
     * Get comprehensive stock status for dashboard/reporting
     *
     * @param array $product_ids Optional array of specific product IDs
     * @return array Stock status report
     */
    public function get_stock_status_report($product_ids = array()) {
        $report = array(
            'timestamp' => current_time('timestamp'),
            'products' => array(),
            'summary' => array(
                'total_products' => 0,
                'in_stock' => 0,
                'out_of_stock' => 0,
                'low_stock' => 0,
                'total_reservations' => 0
            )
        );

        // Get products to check
        if (empty($product_ids)) {
            // Get all products with stock management enabled
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_manage_stock',
                        'value' => 'yes',
                        'compare' => '='
                    )
                ),
                'fields' => 'ids'
            );
            $product_ids = get_posts($args);
        }

        foreach ($product_ids as $product_id) {
            $stock_data = $this->get_real_time_stock($product_id);
            
            if (isset($stock_data['error']) && $stock_data['error']) {
                continue;
            }

            $report['products'][$product_id] = $stock_data;
            $report['summary']['total_products']++;
            
            // Count stock status
            switch ($stock_data['stock_status']) {
                case 'instock':
                    $report['summary']['in_stock']++;
                    break;
                case 'outofstock':
                    $report['summary']['out_of_stock']++;
                    break;
            }

            if ($stock_data['is_low_stock']) {
                $report['summary']['low_stock']++;
            }

            $report['summary']['total_reservations'] += count($stock_data['stock_reservations']);
        }

        return $report;
    }

    /**
     * Force refresh stock cache for product(s)
     *
     * @param int|array $product_ids Product ID or array of IDs
     * @return array Refresh results
     */
    public function force_refresh_stock_cache($product_ids) {
        if (!is_array($product_ids)) {
            $product_ids = array($product_ids);
        }

        $results = array();
        
        foreach ($product_ids as $product_id) {
            // Invalidate existing cache
            $this->invalidate_stock_cache($product_id);
            
            // Force fresh load
            $stock_data = $this->get_real_time_stock($product_id);
            
            $results[$product_id] = array(
                'success' => !isset($stock_data['error']),
                'data' => $stock_data,
                'refreshed_at' => current_time('timestamp')
            );
        }
        
        return array(
            'success' => true,
            'products_refreshed' => count($results),
            'results' => $results
        );
    }

    /**
     * Cleanup expired reservations
     */
    public function cleanup_expired_reservations() {
        global $wpdb;
        
        $current_time = current_time('timestamp');
        $cleaned_count = 0;
        
        $reservations = $wpdb->get_results(
            "SELECT option_name, option_value FROM {$wpdb->options} 
             WHERE option_name LIKE 'wupos_stock_reservation_%'",
            ARRAY_A
        );
        
        foreach ($reservations as $row) {
            $reservation_data = maybe_unserialize($row['option_value']);
            if ($reservation_data && is_array($reservation_data)) {
                if ($reservation_data['expires_at'] < $current_time) {
                    delete_option($row['option_name']);
                    $cleaned_count++;
                    
                    // Invalidate stock cache for the product
                    $this->invalidate_stock_cache($reservation_data['product_id']);
                }
            }
        }
        
        if ($cleaned_count > 0) {
            wupos_log("Cleaned up {$cleaned_count} expired stock reservations", 'debug');
        }
    }

    /**
     * Load active reservations into memory
     */
    private function load_active_reservations() {
        // This method could be used to preload active reservations for better performance
        // For now, we query them on-demand for simplicity and accuracy
    }

    /**
     * Get variation stock for variable products
     *
     * @param WC_Product $product Variable product
     * @return array Variation stock data
     */
    private function get_variation_stock($product) {
        if (!$product->is_type('variable')) {
            return array();
        }

        $variations = $product->get_available_variations();
        $variation_stock = array();
        
        foreach ($variations as $variation) {
            $variation_obj = wc_get_product($variation['variation_id']);
            if ($variation_obj) {
                $variation_stock[] = array(
                    'variation_id' => $variation_obj->get_id(),
                    'stock_quantity' => $variation_obj->get_stock_quantity(),
                    'stock_status' => $variation_obj->get_stock_status(),
                    'manage_stock' => $variation_obj->get_manage_stock(),
                    'available_stock' => $this->calculate_available_stock($variation_obj),
                );
            }
        }
        
        return $variation_stock;
    }

    /**
     * Get locked stock (for atomic operations)
     *
     * @param WC_Product $product Product object
     * @return int|WP_Error Current stock or error if locked
     */
    private function get_locked_stock($product) {
        // Simple implementation - could be enhanced with database locks
        // for high-concurrency environments
        return $product->get_stock_quantity();
    }

    /**
     * Calculate new stock based on operation
     *
     * @param int $current_stock Current stock
     * @param int $quantity Quantity for operation
     * @param string $operation Operation type
     * @return int New stock quantity
     */
    private function calculate_new_stock($current_stock, $quantity, $operation) {
        switch ($operation) {
            case 'set':
                return $quantity;
            case 'increase':
                return $current_stock + $quantity;
            case 'decrease':
                return $current_stock - $quantity;
            default:
                return $current_stock;
        }
    }

    /**
     * Validate stock change
     *
     * @param WC_Product $product Product object
     * @param int $current_stock Current stock
     * @param int $new_stock New stock
     * @param array $options Operation options
     * @return true|WP_Error Validation result
     */
    private function validate_stock_change($product, $current_stock, $new_stock, $options) {
        // Check for negative stock
        if ($new_stock < 0 && !$options['force_update']) {
            if ($product->get_backorders() === 'no') {
                return new WP_Error('negative_stock', 
                    __('Stock cannot be negative and backorders are not allowed.', 'wupos'), 
                    array('status' => 400)
                );
            }
        }
        
        // Check for reasonable stock limits
        $max_stock = apply_filters('wupos_max_stock_quantity', 999999);
        if ($new_stock > $max_stock) {
            return new WP_Error('stock_too_high', 
                sprintf(__('Stock quantity cannot exceed %d.', 'wupos'), $max_stock),
                array('status' => 400)
            );
        }
        
        return true;
    }

    /**
     * Perform the actual stock update
     *
     * @param WC_Product $product Product object
     * @param int $new_stock New stock quantity
     * @param array $options Update options
     * @return true|WP_Error Update result
     */
    private function perform_stock_update($product, $new_stock, $options) {
        try {
            // Use WooCommerce native method
            $product->set_stock_quantity($new_stock);
            $product->save();
            
            return true;
            
        } catch (Exception $e) {
            return new WP_Error('update_failed', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Log stock change
     *
     * @param int $product_id Product ID
     * @param int $old_stock Old stock
     * @param int $new_stock New stock
     * @param string $operation Operation
     * @param array $options Options
     */
    private function log_stock_change($product_id, $old_stock, $new_stock, $operation, $options) {
        $log_entry = array(
            'product_id' => $product_id,
            'old_stock' => $old_stock,
            'new_stock' => $new_stock,
            'operation' => $operation,
            'user_id' => $options['user_id'],
            'terminal_id' => $options['terminal_id'],
            'reason' => $options['reason'],
            'note' => $options['note'],
            'timestamp' => current_time('timestamp'),
        );
        
        // Log to WordPress debug.log if enabled
        wupos_log('Stock Update: ' . print_r($log_entry, true), 'info');
        
        // Could also save to a custom log table for detailed reporting
        do_action('wupos_stock_change_logged', $log_entry);
    }

    /**
     * Check for low stock alerts
     *
     * @param WC_Product $product Product object
     * @param int $stock_quantity Current stock quantity
     */
    private function check_low_stock_alert($product, $stock_quantity) {
        if ($this->is_low_stock($product, $stock_quantity)) {
            do_action('wupos_low_stock_alert', $product->get_id(), $stock_quantity, $product->get_low_stock_amount());
        }
    }

    /**
     * Check if product is low stock
     *
     * @param WC_Product $product Product object
     * @param int $stock_quantity Optional stock quantity
     * @return bool True if low stock
     */
    private function is_low_stock($product, $stock_quantity = null) {
        if (!$product->get_manage_stock()) {
            return false;
        }

        if (null === $stock_quantity) {
            $stock_quantity = $product->get_stock_quantity();
        }

        $low_stock_threshold = $this->get_low_stock_threshold($product);
        
        return $stock_quantity <= $low_stock_threshold;
    }

    /**
     * Get low stock threshold for product
     *
     * @param WC_Product $product Product object
     * @return int Low stock threshold
     */
    private function get_low_stock_threshold($product) {
        $threshold = $product->get_low_stock_amount();
        
        if (null === $threshold) {
            $threshold = get_option('woocommerce_notify_low_stock_amount', 2);
        }
        
        return intval($threshold);
    }

    /**
     * Reserve stock for entire order
     *
     * @param WC_Order $order Order object
     * @return array Reservation results
     */
    private function reserve_order_stock($order) {
        $reservations = array();
        $order_key = $order->get_order_key();
        
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $quantity = $item->get_quantity();
            
            $reservation_result = $this->reserve_stock($product_id, $quantity, $order_key);
            if (!is_wp_error($reservation_result)) {
                $reservations[] = $reservation_result;
            }
        }
        
        return $reservations;
    }

    /**
     * Check if stock cache is still valid
     *
     * @param array $cached_stock Cached stock data
     * @return bool True if valid
     */
    private function is_stock_cache_valid($cached_stock) {
        if (!isset($cached_stock['last_updated'])) {
            return false;
        }
        
        $cache_age = current_time('timestamp') - $cached_stock['last_updated'];
        return $cache_age < 60; // 1 minute validity
    }

    /**
     * Invalidate stock cache for product
     *
     * @param int $product_id Product ID
     */
    private function invalidate_stock_cache($product_id) {
        $cache_key = 'stock_' . $product_id;
        $this->cache_manager->get_stock_cache($cache_key); // This will delete if exists
        
        // Also invalidate product cache as it contains stock data
        $this->cache_manager->invalidate_product_cache($product_id);
    }

    /**
     * Get error response structure
     *
     * @param string $code Error code
     * @param string $message Error message
     * @return array Error response
     */
    private function get_error_response($code, $message) {
        return array(
            'error' => true,
            'code' => $code,
            'message' => $message,
            'timestamp' => current_time('timestamp'),
        );
    }
}