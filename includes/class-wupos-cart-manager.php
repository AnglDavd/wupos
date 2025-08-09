<?php
/**
 * WUPOS Cart Manager
 *
 * Main cart management class handling all cart operations with
 * WooCommerce integration, session management, and multi-terminal support.
 *
 * @package WUPOS\Cart
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS_Cart_Manager class.
 *
 * Comprehensive cart management system with WooCommerce integration,
 * tax calculations, stock management, and concurrent access support.
 */
class WUPOS_Cart_Manager {

    /**
     * Session handler instance
     *
     * @var WUPOS_Session_Handler
     */
    private $session_handler;

    /**
     * Tax calculator instance
     *
     * @var WUPOS_Tax_Calculator
     */
    private $tax_calculator;

    /**
     * Inventory sync instance
     *
     * @var WUPOS_Inventory_Sync
     */
    private $inventory_sync;

    /**
     * Cache manager instance
     *
     * @var WUPOS_Cache_Manager
     */
    private $cache_manager;

    /**
     * Cart contents
     *
     * @var array
     */
    private $cart_contents = array();

    /**
     * Cart totals
     *
     * @var array
     */
    private $cart_totals = array();

    /**
     * Customer location
     *
     * @var array
     */
    private $customer_location = array();

    /**
     * Applied coupons
     *
     * @var array
     */
    private $applied_coupons = array();

    /**
     * Cart hash for change detection
     *
     * @var string
     */
    private $cart_hash = '';

    /**
     * Calculation required flag
     *
     * @var bool
     */
    private $needs_calculation = true;

    /**
     * Performance optimization flag
     *
     * @var bool
     */
    private $performance_mode = true;

    /**
     * Batch operations flag
     *
     * @var bool
     */
    private $batch_mode = false;

    /**
     * Performance metrics
     *
     * @var array
     */
    private $performance_metrics = array();

    /**
     * Microtime start for performance tracking
     *
     * @var float
     */
    private $operation_start_time;

    /**
     * Constructor.
     *
     * @param string $terminal_id Terminal identifier
     */
    public function __construct($terminal_id = '') {
        $this->init_dependencies($terminal_id);
        $this->load_cart_from_session();
        
        // Hook for cleanup
        add_action('wp_logout', array($this, 'clear_cart'));
        add_action('wupos_cleanup_cart_sessions', array($this, 'cleanup_abandoned_carts'));
    }

    /**
     * Initialize dependencies.
     *
     * @param string $terminal_id Terminal identifier
     */
    private function init_dependencies($terminal_id) {
        // Initialize session handler
        if (class_exists('WUPOS_Session_Handler')) {
            $this->session_handler = new WUPOS_Session_Handler($terminal_id);
        }
        
        // Initialize tax calculator
        if (class_exists('WUPOS_Tax_Calculator')) {
            $this->tax_calculator = new WUPOS_Tax_Calculator();
        }
        
        // Initialize inventory sync
        if (class_exists('WUPOS_Inventory_Sync')) {
            $this->inventory_sync = new WUPOS_Inventory_Sync();
        }
        
        // Initialize cache manager
        if (class_exists('WUPOS_Cache_Manager')) {
            $this->cache_manager = new WUPOS_Cache_Manager();
        }
    }

    /**
     * Add product to cart.
     *
     * @param int $product_id Product ID
     * @param int $quantity Quantity to add
     * @param int $variation_id Variation ID (optional)
     * @param array $variation_data Variation data (optional)
     * @param array $item_data Additional item data (optional)
     * @return array|WP_Error Add to cart result
     */
    public function add_to_cart($product_id, $quantity = 1, $variation_id = 0, $variation_data = array(), $item_data = array()) {
        $this->start_performance_tracking('add_to_cart');
        try {
            // Validate inputs
            $validation = $this->validate_add_to_cart($product_id, $quantity, $variation_id);
            if (is_wp_error($validation)) {
                return $validation;
            }

            // Get product
            $product = wc_get_product($variation_id ? $variation_id : $product_id);
            if (!$product) {
                return new WP_Error('product_not_found', __('Product not found.', 'wupos'));
            }

            // Check stock availability
            $stock_check = $this->check_stock_availability($product, $quantity);
            if (is_wp_error($stock_check)) {
                return $stock_check;
            }

            // Generate cart item key
            $cart_item_key = $this->generate_cart_item_key($product_id, $variation_id, $variation_data);
            
            // Check if item already exists in cart
            if (isset($this->cart_contents[$cart_item_key])) {
                return $this->update_cart_item_quantity($cart_item_key, 
                    $this->cart_contents[$cart_item_key]['quantity'] + $quantity);
            }

            // Create cart item
            $cart_item = $this->create_cart_item($product, $quantity, $variation_id, $variation_data, $item_data);
            
            // Reserve stock if inventory sync is available
            if ($this->inventory_sync) {
                $reservation = $this->inventory_sync->reserve_stock(
                    $product->get_id(), 
                    $quantity, 
                    $this->session_handler->get_session_id()
                );
                
                if (is_wp_error($reservation)) {
                    wupos_log('Stock reservation failed: ' . $reservation->get_error_message(), 'warning');
                }
                
                $cart_item['stock_reserved'] = !is_wp_error($reservation);
                $cart_item['reservation_id'] = is_wp_error($reservation) ? null : $reservation['reservation_id'];
            }

            // Add to cart contents
            $this->cart_contents[$cart_item_key] = $cart_item;
            
            // Mark for recalculation
            $this->needs_calculation = true;
            
            // Save to session
            $this->save_cart_to_session();
            
            // Log cart action
            wupos_log(sprintf('Added product %d (qty: %d) to cart', $product_id, $quantity), 'info');
            
            $result = array(
                'success' => true,
                'cart_item_key' => $cart_item_key,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'message' => __('Product added to cart successfully.', 'wupos'),
                'cart_count' => $this->get_cart_count(),
                'cart_item' => $this->prepare_cart_item_response($cart_item_key, $cart_item)
            );
            
            $this->end_performance_tracking('add_to_cart', $result);
            return $result;

        } catch (Exception $e) {
            wupos_log('Cart add error: ' . $e->getMessage(), 'error');
            $error = new WP_Error('cart_add_error', $e->getMessage());
            $this->end_performance_tracking('add_to_cart', $error);
            return $error;
        }
    }

    /**
     * Update cart item quantity.
     *
     * @param string $cart_item_key Cart item key
     * @param int $quantity New quantity
     * @return array|WP_Error Update result
     */
    public function update_cart_item_quantity($cart_item_key, $quantity) {
        try {
            if (!isset($this->cart_contents[$cart_item_key])) {
                return new WP_Error('item_not_found', __('Cart item not found.', 'wupos'));
            }

            $cart_item = $this->cart_contents[$cart_item_key];
            $product_id = $cart_item['product_id'];
            $old_quantity = $cart_item['quantity'];
            
            // Validate quantity
            $quantity = max(0, (int) $quantity);
            
            if ($quantity === 0) {
                return $this->remove_cart_item($cart_item_key);
            }

            // Check stock for new quantity
            $product = wc_get_product($cart_item['variation_id'] ?: $product_id);
            if (!$product) {
                return new WP_Error('product_not_found', __('Product not found.', 'wupos'));
            }

            $stock_check = $this->check_stock_availability($product, $quantity);
            if (is_wp_error($stock_check)) {
                return $stock_check;
            }

            // Update stock reservation if needed
            if ($this->inventory_sync && isset($cart_item['reservation_id'])) {
                $quantity_diff = $quantity - $old_quantity;
                
                if ($quantity_diff !== 0) {
                    $reservation_result = $this->inventory_sync->update_stock_reservation(
                        $cart_item['reservation_id'],
                        $quantity_diff
                    );
                    
                    if (is_wp_error($reservation_result)) {
                        wupos_log('Stock reservation update failed: ' . $reservation_result->get_error_message(), 'warning');
                    }
                }
            }

            // Update cart item
            $this->cart_contents[$cart_item_key]['quantity'] = $quantity;
            $this->cart_contents[$cart_item_key]['updated_at'] = current_time('timestamp');
            
            // Mark for recalculation
            $this->needs_calculation = true;
            
            // Save to session
            $this->save_cart_to_session();
            
            // Log action
            wupos_log(sprintf('Updated cart item %s quantity from %d to %d', $cart_item_key, $old_quantity, $quantity), 'info');
            
            return array(
                'success' => true,
                'cart_item_key' => $cart_item_key,
                'old_quantity' => $old_quantity,
                'new_quantity' => $quantity,
                'message' => __('Cart item quantity updated successfully.', 'wupos'),
                'cart_count' => $this->get_cart_count(),
                'cart_item' => $this->prepare_cart_item_response($cart_item_key, $this->cart_contents[$cart_item_key])
            );

        } catch (Exception $e) {
            wupos_log('Cart update error: ' . $e->getMessage(), 'error');
            return new WP_Error('cart_update_error', $e->getMessage());
        }
    }

    /**
     * Remove item from cart.
     *
     * @param string $cart_item_key Cart item key
     * @return array|WP_Error Remove result
     */
    public function remove_cart_item($cart_item_key) {
        try {
            if (!isset($this->cart_contents[$cart_item_key])) {
                return new WP_Error('item_not_found', __('Cart item not found.', 'wupos'));
            }

            $cart_item = $this->cart_contents[$cart_item_key];
            
            // Release stock reservation
            if ($this->inventory_sync && isset($cart_item['reservation_id'])) {
                $this->inventory_sync->release_stock_reservation($cart_item['reservation_id']);
            }

            // Remove from cart
            unset($this->cart_contents[$cart_item_key]);
            
            // Mark for recalculation
            $this->needs_calculation = true;
            
            // Save to session
            $this->save_cart_to_session();
            
            // Log action
            wupos_log(sprintf('Removed cart item %s (product %d)', $cart_item_key, $cart_item['product_id']), 'info');
            
            return array(
                'success' => true,
                'cart_item_key' => $cart_item_key,
                'message' => __('Item removed from cart successfully.', 'wupos'),
                'cart_count' => $this->get_cart_count()
            );

        } catch (Exception $e) {
            wupos_log('Cart remove error: ' . $e->getMessage(), 'error');
            return new WP_Error('cart_remove_error', $e->getMessage());
        }
    }

    /**
     * Clear entire cart.
     *
     * @return array Clear result
     */
    public function clear_cart() {
        try {
            // Release all stock reservations
            if ($this->inventory_sync) {
                foreach ($this->cart_contents as $cart_item) {
                    if (isset($cart_item['reservation_id'])) {
                        $this->inventory_sync->release_stock_reservation($cart_item['reservation_id']);
                    }
                }
            }

            $item_count = count($this->cart_contents);
            
            // Clear cart contents
            $this->cart_contents = array();
            $this->cart_totals = array();
            $this->applied_coupons = array();
            $this->needs_calculation = true;
            
            // Save to session
            $this->save_cart_to_session();
            
            // Log action
            wupos_log(sprintf('Cleared cart with %d items', $item_count), 'info');
            
            return array(
                'success' => true,
                'items_removed' => $item_count,
                'message' => __('Cart cleared successfully.', 'wupos'),
                'cart_count' => 0
            );

        } catch (Exception $e) {
            wupos_log('Cart clear error: ' . $e->getMessage(), 'error');
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Get cart contents.
     *
     * @param bool $calculate_totals Whether to calculate totals
     * @return array Cart contents with totals
     */
    public function get_cart_contents($calculate_totals = true) {
        if ($calculate_totals) {
            $this->calculate_totals();
        }

        $cart_items = array();
        foreach ($this->cart_contents as $cart_item_key => $cart_item) {
            $cart_items[$cart_item_key] = $this->prepare_cart_item_response($cart_item_key, $cart_item);
        }

        return array(
            'items' => $cart_items,
            'totals' => $this->cart_totals,
            'count' => $this->get_cart_count(),
            'hash' => $this->get_cart_hash(),
            'customer_id' => $this->session_handler ? $this->session_handler->get_customer_id() : 0,
            'customer_location' => $this->customer_location,
            'applied_coupons' => $this->applied_coupons,
            'session_id' => $this->session_handler ? $this->session_handler->get_session_id() : '',
            'terminal_id' => $this->session_handler ? $this->session_handler->get_terminal_id() : '',
            'last_updated' => current_time('timestamp')
        );
    }

    /**
     * Calculate cart totals.
     *
     * @return array Cart totals
     */
    public function calculate_totals() {
        if (!$this->needs_calculation) {
            return $this->cart_totals;
        }

        try {
            // Initialize totals
            $this->cart_totals = array(
                'subtotal' => 0,
                'subtotal_tax' => 0,
                'total' => 0,
                'total_tax' => 0,
                'discount_total' => 0,
                'discount_tax' => 0,
                'cart_total' => 0,
                'tax_lines' => array(),
                'items_count' => count($this->cart_contents),
                'items_weight' => 0,
                'needs_shipping' => false
            );

            if (empty($this->cart_contents)) {
                $this->needs_calculation = false;
                return $this->cart_totals;
            }

            // Prepare cart items for tax calculation
            $cart_items_for_tax = $this->prepare_cart_items_for_tax();
            
            // Calculate taxes if tax calculator is available
            if ($this->tax_calculator) {
                $tax_result = $this->tax_calculator->calculate_cart_taxes($cart_items_for_tax, $this->customer_location);
                
                if (!isset($tax_result['error'])) {
                    $this->cart_totals['subtotal'] = $tax_result['subtotal'];
                    $this->cart_totals['subtotal_tax'] = $tax_result['subtotal_tax'];
                    $this->cart_totals['total'] = $tax_result['total'];
                    $this->cart_totals['total_tax'] = $tax_result['total_tax'];
                    $this->cart_totals['tax_lines'] = $tax_result['tax_lines'];
                    
                    // Update individual item tax data
                    foreach ($tax_result['items'] as $cart_item_key => $item_tax) {
                        if (isset($this->cart_contents[$cart_item_key])) {
                            $this->cart_contents[$cart_item_key]['line_subtotal'] = $item_tax['line_subtotal'];
                            $this->cart_contents[$cart_item_key]['line_subtotal_tax'] = $item_tax['line_subtotal_tax'];
                            $this->cart_contents[$cart_item_key]['line_total'] = $item_tax['line_total'];
                            $this->cart_contents[$cart_item_key]['line_tax'] = $item_tax['line_tax'];
                            $this->cart_contents[$cart_item_key]['taxes'] = $item_tax['taxes'];
                        }
                    }
                } else {
                    // Fallback calculation without taxes
                    $this->calculate_totals_without_tax();
                }
            } else {
                // Fallback calculation without taxes
                $this->calculate_totals_without_tax();
            }

            // Apply coupon discounts
            $this->apply_coupon_discounts();
            
            // Calculate final cart total
            $this->cart_totals['cart_total'] = $this->cart_totals['total'] + $this->cart_totals['total_tax'] - $this->cart_totals['discount_total'];
            $this->cart_totals['cart_total'] = max(0, $this->cart_totals['cart_total']);
            
            // Add formatted totals
            $this->cart_totals['formatted'] = $this->format_cart_totals();
            
            // Calculate additional metrics
            $this->calculate_additional_metrics();
            
            $this->needs_calculation = false;
            
            // Save updated cart to session
            $this->save_cart_to_session();
            
            return $this->cart_totals;

        } catch (Exception $e) {
            wupos_log('Cart calculation error: ' . $e->getMessage(), 'error');
            return $this->get_fallback_totals();
        }
    }

    /**
     * Set customer location.
     *
     * @param array $location Customer location data
     * @return bool Success status
     */
    public function set_customer_location($location) {
        $this->customer_location = array(
            'country' => isset($location['country']) ? strtoupper(sanitize_text_field($location['country'])) : '',
            'state' => isset($location['state']) ? sanitize_text_field($location['state']) : '',
            'postcode' => isset($location['postcode']) ? sanitize_text_field($location['postcode']) : '',
            'city' => isset($location['city']) ? sanitize_text_field($location['city']) : ''
        );
        
        $this->needs_calculation = true;
        return $this->save_cart_to_session();
    }

    /**
     * Apply coupon to cart.
     *
     * @param string $coupon_code Coupon code
     * @return array|WP_Error Coupon application result
     */
    public function apply_coupon($coupon_code) {
        try {
            $coupon_code = sanitize_text_field($coupon_code);
            
            // Check if coupon already applied
            if (in_array($coupon_code, $this->applied_coupons)) {
                return new WP_Error('coupon_already_applied', __('Coupon already applied.', 'wupos'));
            }

            // Validate coupon
            $coupon = new WC_Coupon($coupon_code);
            if (!$coupon->is_valid()) {
                return new WP_Error('invalid_coupon', __('Invalid coupon code.', 'wupos'));
            }

            // Add to applied coupons
            $this->applied_coupons[] = $coupon_code;
            $this->needs_calculation = true;
            
            $this->save_cart_to_session();
            
            return array(
                'success' => true,
                'coupon_code' => $coupon_code,
                'message' => __('Coupon applied successfully.', 'wupos')
            );

        } catch (Exception $e) {
            return new WP_Error('coupon_error', $e->getMessage());
        }
    }

    /**
     * Remove coupon from cart.
     *
     * @param string $coupon_code Coupon code
     * @return array Removal result
     */
    public function remove_coupon($coupon_code) {
        $coupon_code = sanitize_text_field($coupon_code);
        $key = array_search($coupon_code, $this->applied_coupons);
        
        if ($key !== false) {
            unset($this->applied_coupons[$key]);
            $this->applied_coupons = array_values($this->applied_coupons);
            $this->needs_calculation = true;
            $this->save_cart_to_session();
            
            return array(
                'success' => true,
                'message' => __('Coupon removed successfully.', 'wupos')
            );
        }
        
        return array(
            'success' => false,
            'message' => __('Coupon not found in cart.', 'wupos')
        );
    }

    /**
     * Get cart count (total items).
     *
     * @return int Cart count
     */
    public function get_cart_count() {
        $count = 0;
        foreach ($this->cart_contents as $cart_item) {
            $count += $cart_item['quantity'];
        }
        return $count;
    }

    /**
     * Get cart hash for change detection.
     *
     * @return string Cart hash
     */
    public function get_cart_hash() {
        return md5(serialize($this->cart_contents) . serialize($this->applied_coupons));
    }

    /**
     * Check if cart is empty.
     *
     * @return bool True if cart is empty
     */
    public function is_cart_empty() {
        return empty($this->cart_contents);
    }

    /**
     * Get session ID.
     *
     * @return string Session ID
     */
    public function get_session_id() {
        return $this->session_handler ? $this->session_handler->get_session_id() : '';
    }

    /**
     * Get terminal ID.
     *
     * @return string Terminal ID
     */
    public function get_terminal_id() {
        return $this->session_handler ? $this->session_handler->get_terminal_id() : '';
    }

    /**
     * Validate add to cart request.
     *
     * @param int $product_id Product ID
     * @param int $quantity Quantity
     * @param int $variation_id Variation ID
     * @return bool|WP_Error True if valid, WP_Error if invalid
     */
    private function validate_add_to_cart($product_id, $quantity, $variation_id) {
        // Validate product ID
        if (!is_numeric($product_id) || $product_id <= 0) {
            return new WP_Error('invalid_product_id', __('Invalid product ID.', 'wupos'));
        }

        // Validate quantity
        if (!is_numeric($quantity) || $quantity <= 0) {
            return new WP_Error('invalid_quantity', __('Invalid quantity.', 'wupos'));
        }

        // Validate variation ID if provided
        if ($variation_id > 0 && (!is_numeric($variation_id) || $variation_id <= 0)) {
            return new WP_Error('invalid_variation_id', __('Invalid variation ID.', 'wupos'));
        }

        return true;
    }

    /**
     * Check stock availability for product.
     *
     * @param WC_Product $product Product object
     * @param int $quantity Required quantity
     * @return bool|WP_Error True if available, WP_Error if not
     */
    private function check_stock_availability($product, $quantity) {
        if (!$product->managing_stock()) {
            return true;
        }

        $stock_quantity = $product->get_stock_quantity();
        
        if ($stock_quantity < $quantity) {
            return new WP_Error('insufficient_stock', 
                sprintf(__('Not enough stock. Available: %d', 'wupos'), $stock_quantity));
        }

        if ($product->get_stock_status() !== 'instock') {
            return new WP_Error('out_of_stock', __('Product is out of stock.', 'wupos'));
        }

        return true;
    }

    /**
     * Generate cart item key.
     *
     * @param int $product_id Product ID
     * @param int $variation_id Variation ID
     * @param array $variation_data Variation data
     * @return string Cart item key
     */
    private function generate_cart_item_key($product_id, $variation_id, $variation_data) {
        return md5($product_id . serialize($variation_id) . serialize($variation_data));
    }

    /**
     * Create cart item array.
     *
     * @param WC_Product $product Product object
     * @param int $quantity Quantity
     * @param int $variation_id Variation ID
     * @param array $variation_data Variation data
     * @param array $item_data Additional item data
     * @return array Cart item
     */
    private function create_cart_item($product, $quantity, $variation_id, $variation_data, $item_data) {
        return array(
            'product_id' => $product->get_parent_id() ?: $product->get_id(),
            'variation_id' => $variation_id,
            'variation' => $variation_data,
            'quantity' => (int) $quantity,
            'line_subtotal' => 0,
            'line_subtotal_tax' => 0,
            'line_total' => 0,
            'line_tax' => 0,
            'taxes' => array(),
            'data' => $product,
            'item_data' => $item_data,
            'added_at' => current_time('timestamp'),
            'updated_at' => current_time('timestamp'),
            'stock_reserved' => false,
            'reservation_id' => null
        );
    }

    /**
     * Prepare cart item for API response.
     *
     * @param string $cart_item_key Cart item key
     * @param array $cart_item Cart item data
     * @return array Formatted cart item
     */
    private function prepare_cart_item_response($cart_item_key, $cart_item) {
        $product = $cart_item['data'];
        $product_id = $cart_item['variation_id'] ?: $cart_item['product_id'];
        
        return array(
            'key' => $cart_item_key,
            'product_id' => $cart_item['product_id'],
            'variation_id' => $cart_item['variation_id'],
            'quantity' => $cart_item['quantity'],
            'name' => $product->get_name(),
            'sku' => $product->get_sku(),
            'price' => $product->get_price(),
            'line_subtotal' => $cart_item['line_subtotal'],
            'line_subtotal_tax' => $cart_item['line_subtotal_tax'],
            'line_total' => $cart_item['line_total'],
            'line_tax' => $cart_item['line_tax'],
            'formatted' => array(
                'price' => wc_price($product->get_price()),
                'line_subtotal' => wc_price($cart_item['line_subtotal']),
                'line_total' => wc_price($cart_item['line_total'] + $cart_item['line_tax'])
            ),
            'image_url' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
            'permalink' => $product->get_permalink(),
            'variation' => $cart_item['variation'],
            'item_data' => $cart_item['item_data']
        );
    }

    /**
     * Prepare cart items for tax calculation.
     *
     * @return array Cart items formatted for tax calculation
     */
    private function prepare_cart_items_for_tax() {
        $items = array();
        
        foreach ($this->cart_contents as $cart_item_key => $cart_item) {
            $items[$cart_item_key] = array(
                'product_id' => $cart_item['product_id'],
                'variation_id' => $cart_item['variation_id'],
                'quantity' => $cart_item['quantity']
            );
        }
        
        return $items;
    }

    /**
     * Calculate totals without tax (fallback method).
     */
    private function calculate_totals_without_tax() {
        $subtotal = 0;
        
        foreach ($this->cart_contents as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $quantity = $cart_item['quantity'];
            $price = $product->get_price();
            
            $line_total = $price * $quantity;
            
            $this->cart_contents[$cart_item_key]['line_subtotal'] = $line_total;
            $this->cart_contents[$cart_item_key]['line_subtotal_tax'] = 0;
            $this->cart_contents[$cart_item_key]['line_total'] = $line_total;
            $this->cart_contents[$cart_item_key]['line_tax'] = 0;
            $this->cart_contents[$cart_item_key]['taxes'] = array();
            
            $subtotal += $line_total;
        }
        
        $this->cart_totals['subtotal'] = $subtotal;
        $this->cart_totals['total'] = $subtotal;
        $this->cart_totals['subtotal_tax'] = 0;
        $this->cart_totals['total_tax'] = 0;
    }

    /**
     * Apply coupon discounts to cart totals.
     */
    private function apply_coupon_discounts() {
        // Implementation for coupon discounts would go here
        // This is a placeholder for the full coupon system
        $this->cart_totals['discount_total'] = 0;
        $this->cart_totals['discount_tax'] = 0;
    }

    /**
     * Format cart totals for display.
     *
     * @return array Formatted totals
     */
    private function format_cart_totals() {
        return array(
            'subtotal' => wc_price($this->cart_totals['subtotal']),
            'subtotal_tax' => wc_price($this->cart_totals['subtotal_tax']),
            'total' => wc_price($this->cart_totals['total']),
            'total_tax' => wc_price($this->cart_totals['total_tax']),
            'cart_total' => wc_price($this->cart_totals['cart_total']),
            'discount_total' => wc_price($this->cart_totals['discount_total'])
        );
    }

    /**
     * Calculate additional cart metrics.
     */
    private function calculate_additional_metrics() {
        $weight = 0;
        $needs_shipping = false;
        
        foreach ($this->cart_contents as $cart_item) {
            $product = $cart_item['data'];
            $quantity = $cart_item['quantity'];
            
            if ($product->has_weight()) {
                $weight += (float) $product->get_weight() * $quantity;
            }
            
            if ($product->needs_shipping()) {
                $needs_shipping = true;
            }
        }
        
        $this->cart_totals['items_weight'] = $weight;
        $this->cart_totals['needs_shipping'] = $needs_shipping;
    }

    /**
     * Get fallback totals in case of calculation error.
     *
     * @return array Fallback totals
     */
    private function get_fallback_totals() {
        return array(
            'subtotal' => 0,
            'subtotal_tax' => 0,
            'total' => 0,
            'total_tax' => 0,
            'discount_total' => 0,
            'discount_tax' => 0,
            'cart_total' => 0,
            'tax_lines' => array(),
            'items_count' => count($this->cart_contents),
            'items_weight' => 0,
            'needs_shipping' => false,
            'formatted' => array(
                'subtotal' => wc_price(0),
                'subtotal_tax' => wc_price(0),
                'total' => wc_price(0),
                'total_tax' => wc_price(0),
                'cart_total' => wc_price(0),
                'discount_total' => wc_price(0)
            ),
            'error' => true
        );
    }

    /**
     * Load cart from session.
     */
    private function load_cart_from_session() {
        if (!$this->session_handler) {
            return;
        }

        $cart_data = $this->session_handler->get_cart_data();
        
        if (!empty($cart_data['contents'])) {
            $this->cart_contents = $cart_data['contents'];
        }
        
        if (!empty($cart_data['location'])) {
            $this->customer_location = $cart_data['location'];
        }
        
        if (!empty($cart_data['coupons'])) {
            $this->applied_coupons = $cart_data['coupons'];
        }
        
        if (!empty($cart_data['totals'])) {
            $this->cart_totals = $cart_data['totals'];
        }
    }

    /**
     * Save cart to session.
     *
     * @return bool Success status
     */
    private function save_cart_to_session() {
        if (!$this->session_handler) {
            return false;
        }

        $cart_data = array(
            'contents' => $this->cart_contents,
            'location' => $this->customer_location,
            'coupons' => $this->applied_coupons,
            'totals' => $this->cart_totals,
            'hash' => $this->get_cart_hash(),
            'updated_at' => current_time('timestamp')
        );

        return $this->session_handler->set_cart_data($cart_data);
    }

    /**
     * Cleanup abandoned carts.
     */
    public function cleanup_abandoned_carts() {
        // This would be called by cron to clean up old cart sessions
        if ($this->session_handler) {
            $this->session_handler->cleanup_expired_sessions();
        }
    }

    /**
     * Start performance tracking for an operation.
     *
     * @param string $operation Operation name
     */
    private function start_performance_tracking($operation) {
        if ($this->performance_mode) {
            $this->operation_start_time = microtime(true);
            
            if (!isset($this->performance_metrics[$operation])) {
                $this->performance_metrics[$operation] = array(
                    'count' => 0,
                    'total_time' => 0,
                    'average_time' => 0,
                    'max_time' => 0,
                    'min_time' => PHP_FLOAT_MAX
                );
            }
        }
    }

    /**
     * End performance tracking and log metrics.
     *
     * @param string $operation Operation name
     * @param mixed $result Operation result for additional metrics
     */
    private function end_performance_tracking($operation, $result = null) {
        if ($this->performance_mode && $this->operation_start_time) {
            $execution_time = microtime(true) - $this->operation_start_time;
            
            $metrics = &$this->performance_metrics[$operation];
            $metrics['count']++;
            $metrics['total_time'] += $execution_time;
            $metrics['average_time'] = $metrics['total_time'] / $metrics['count'];
            $metrics['max_time'] = max($metrics['max_time'], $execution_time);
            $metrics['min_time'] = min($metrics['min_time'], $execution_time);
            
            // Log if operation exceeds performance target (50ms)
            if ($execution_time > 0.05) {
                wupos_log(sprintf('Cart operation %s took %s ms (target: <50ms)', 
                    $operation, 
                    number_format($execution_time * 1000, 2)
                ), 'warning');
            }
            
            // Reset timer
            $this->operation_start_time = null;
        }
    }

    /**
     * Get performance metrics.
     *
     * @return array Performance metrics
     */
    public function get_performance_metrics() {
        return $this->performance_metrics;
    }

    /**
     * Reset performance metrics.
     */
    public function reset_performance_metrics() {
        $this->performance_metrics = array();
    }

    /**
     * Enable or disable performance mode.
     *
     * @param bool $enabled Enable performance tracking
     */
    public function set_performance_mode($enabled) {
        $this->performance_mode = $enabled;
    }

    /**
     * Enable batch mode for multiple operations.
     *
     * @param bool $enabled Enable batch mode
     */
    public function set_batch_mode($enabled) {
        $this->batch_mode = $enabled;
        
        if ($enabled) {
            // Defer session saves and calculations in batch mode
            $this->needs_calculation = false;
        }
    }

    /**
     * Process batch operations and finalize.
     */
    public function finalize_batch() {
        if ($this->batch_mode) {
            // Force recalculation after batch operations
            $this->needs_calculation = true;
            $this->calculate_totals();
            $this->save_cart_to_session();
            $this->batch_mode = false;
        }
    }

    /**
     * Add multiple items to cart in batch (optimized).
     *
     * @param array $items Array of items to add
     * @return array Batch add result
     */
    public function batch_add_to_cart($items) {
        $this->start_performance_tracking('batch_add_to_cart');
        $this->set_batch_mode(true);
        
        $results = array();
        $success_count = 0;
        $error_count = 0;
        
        try {
            foreach ($items as $item) {
                $product_id = isset($item['product_id']) ? $item['product_id'] : 0;
                $quantity = isset($item['quantity']) ? $item['quantity'] : 1;
                $variation_id = isset($item['variation_id']) ? $item['variation_id'] : 0;
                $variation_data = isset($item['variation_data']) ? $item['variation_data'] : array();
                $item_data = isset($item['item_data']) ? $item['item_data'] : array();
                
                $result = $this->add_to_cart($product_id, $quantity, $variation_id, $variation_data, $item_data);
                
                $results[] = array(
                    'product_id' => $product_id,
                    'result' => $result
                );
                
                if (is_wp_error($result)) {
                    $error_count++;
                } else {
                    $success_count++;
                }
            }
            
            $this->finalize_batch();
            
            $batch_result = array(
                'success' => true,
                'total_items' => count($items),
                'success_count' => $success_count,
                'error_count' => $error_count,
                'results' => $results,
                'cart_count' => $this->get_cart_count()
            );
            
            $this->end_performance_tracking('batch_add_to_cart', $batch_result);
            return $batch_result;
            
        } catch (Exception $e) {
            $this->set_batch_mode(false);
            wupos_log('Batch add error: ' . $e->getMessage(), 'error');
            $error = new WP_Error('batch_add_error', $e->getMessage());
            $this->end_performance_tracking('batch_add_to_cart', $error);
            return $error;
        }
    }

    /**
     * Get optimized cart summary for quick display.
     *
     * @return array Quick cart summary
     */
    public function get_cart_summary() {
        $this->start_performance_tracking('get_cart_summary');
        
        $summary = array(
            'count' => $this->get_cart_count(),
            'hash' => $this->get_cart_hash(),
            'empty' => $this->is_cart_empty(),
            'terminal_id' => $this->get_terminal_id(),
            'session_id' => $this->get_session_id(),
            'timestamp' => current_time('timestamp')
        );
        
        if (!$this->is_cart_empty()) {
            $summary['subtotal'] = isset($this->cart_totals['subtotal']) ? $this->cart_totals['subtotal'] : 0;
            $summary['total'] = isset($this->cart_totals['cart_total']) ? $this->cart_totals['cart_total'] : 0;
            $summary['has_tax'] = isset($this->cart_totals['total_tax']) && $this->cart_totals['total_tax'] > 0;
        }
        
        $this->end_performance_tracking('get_cart_summary', $summary);
        return $summary;
    }

    /**
     * Check cart status for conflicts.
     *
     * @return array Cart status check result
     */
    public function check_cart_status() {
        $this->start_performance_tracking('check_cart_status');
        
        $status = array(
            'valid' => true,
            'conflicts' => array(),
            'warnings' => array(),
            'session_valid' => $this->session_handler ? $this->session_handler->is_session_valid() : false,
            'stock_issues' => array()
        );
        
        // Check for stock conflicts
        foreach ($this->cart_contents as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $required_quantity = $cart_item['quantity'];
            
            if ($product->managing_stock()) {
                $available_stock = $product->get_stock_quantity();
                
                // Check with inventory sync if available
                if ($this->inventory_sync) {
                    $stock_info = $this->inventory_sync->get_real_time_stock($product->get_id());
                    if (!isset($stock_info['error'])) {
                        $available_stock = $stock_info['available_stock'];
                    }
                }
                
                if ($available_stock < $required_quantity) {
                    $status['valid'] = false;
                    $status['stock_issues'][] = array(
                        'cart_item_key' => $cart_item_key,
                        'product_id' => $product->get_id(),
                        'required' => $required_quantity,
                        'available' => $available_stock,
                        'product_name' => $product->get_name()
                    );
                }
            }
        }
        
        $this->end_performance_tracking('check_cart_status', $status);
        return $status;
    }
}