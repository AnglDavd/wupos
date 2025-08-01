<?php
/**
 * WUPOS Products API
 *
 * Handles WooCommerce products integration for the POS system
 *
 * @package WUPOS
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS Products API Class
 *
 * @class WUPOS_Products_API
 * @version 1.0.0
 */
class WUPOS_Products_API {

    /**
     * Products per page for pagination
     *
     * @var int
     */
    private $products_per_page = 20;

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_wupos_get_products', array($this, 'get_products_ajax'));
        add_action('wp_ajax_nopriv_wupos_get_products', array($this, 'get_products_ajax'));
        add_action('wp_ajax_wupos_search_products', array($this, 'search_products_ajax'));
        add_action('wp_ajax_nopriv_wupos_search_products', array($this, 'search_products_ajax'));
        add_action('wp_ajax_wupos_get_categories', array($this, 'get_categories_ajax'));
        add_action('wp_ajax_nopriv_wupos_get_categories', array($this, 'get_categories_ajax'));
        add_action('wp_ajax_wupos_get_product_by_id', array($this, 'get_product_by_id_ajax'));
        add_action('wp_ajax_nopriv_wupos_get_product_by_id', array($this, 'get_product_by_id_ajax'));
        add_action('wp_ajax_wupos_calculate_taxes', array($this, 'calculate_taxes_ajax'));
        add_action('wp_ajax_nopriv_wupos_calculate_taxes', array($this, 'calculate_taxes_ajax'));
        add_action('wp_ajax_wupos_get_tax_settings', array($this, 'get_tax_settings_ajax'));
        add_action('wp_ajax_nopriv_wupos_get_tax_settings', array($this, 'get_tax_settings_ajax'));
    }

    /**
     * AJAX handler for getting products
     */
    public function get_products_ajax() {
        // Debug logging - start
        error_log('WUPOS DEBUG: get_products_ajax called');
        error_log('WUPOS DEBUG: POST data: ' . print_r($_POST, true));
        error_log('WUPOS DEBUG: Nonce from POST: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'NOT SET'));
        
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'wupos_nonce')) {
            error_log('WUPOS DEBUG: Nonce verification failed for get_products_ajax');
            wp_send_json_error(__('Security check failed', 'wupos'));
            return;
        }
        error_log('WUPOS DEBUG: Nonce verification passed for get_products_ajax');

        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            error_log('WUPOS DEBUG: WooCommerce is not active');
            wp_send_json_error(__('WooCommerce is not active', 'wupos'));
            return;
        }
        error_log('WUPOS DEBUG: WooCommerce is active');

        // Check user permissions
        if (!$this->check_pos_permissions()) {
            error_log('WUPOS DEBUG: Permission check failed');
            wp_send_json_error(__('Insufficient permissions', 'wupos'));
            return;
        }
        error_log('WUPOS DEBUG: Permission check passed');

        // Sanitize input parameters
        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        
        error_log('WUPOS DEBUG: Sanitized parameters - Page: ' . $page . ', Category: ' . $category . ', Search: ' . $search);

        try {
            error_log('WUPOS DEBUG: Calling get_woocommerce_products()');
            $products_data = $this->get_woocommerce_products($page, $category, $search);
            error_log('WUPOS DEBUG: Products data retrieved: ' . print_r($products_data, true));
            wp_send_json_success($products_data);
        } catch (Exception $e) {
            error_log('WUPOS DEBUG: Exception in get_products_ajax: ' . $e->getMessage());
            error_log('WUPOS DEBUG: Exception trace: ' . $e->getTraceAsString());
            wp_send_json_error(__('Error loading products: ', 'wupos') . $e->getMessage());
        }
    }

    /**
     * AJAX handler for searching products
     */
    public function search_products_ajax() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'wupos_nonce')) {
            wp_send_json_error(__('Security check failed', 'wupos'));
            return;
        }

        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            wp_send_json_error(__('WooCommerce is not active', 'wupos'));
            return;
        }

        // Check user permissions
        if (!$this->check_pos_permissions()) {
            wp_send_json_error(__('Insufficient permissions', 'wupos'));
            return;
        }

        // Sanitize search term
        $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        
        if (empty($search_term) || strlen($search_term) < 2) {
            wp_send_json_error(__('Search term must be at least 2 characters', 'wupos'));
            return;
        }

        try {
            $products_data = $this->get_woocommerce_products(1, '', $search_term);
            wp_send_json_success($products_data);
        } catch (Exception $e) {
            wp_send_json_error(__('Error searching products: ', 'wupos') . $e->getMessage());
        }
    }

    /**
     * Get WooCommerce products with proper formatting for POS
     *
     * @param int    $page     Page number for pagination
     * @param string $category Category slug to filter by
     * @param string $search   Search term
     * @return array Products data
     */
    private function get_woocommerce_products($page = 1, $category = '', $search = '') {
        // Use WooCommerce's wc_get_products() function for better compatibility
        $args = array(
            'status'       => 'publish',
            'limit'        => $this->products_per_page,
            'page'         => $page,
            'orderby'      => 'menu_order',
            'order'        => 'ASC',
            'stock_status' => 'instock',
            'catalog_visibility' => array('catalog', 'visible')
        );

        // Add category filter if specified
        if (!empty($category)) {
            $args['category'] = array($category);
        }

        // Add search filter if specified
        if (!empty($search)) {
            $args['search'] = $search;
        }

        // Get products using WooCommerce function
        $wc_products = wc_get_products($args);
        $products = array();
        
        // Get total count for pagination
        $count_args = $args;
        $count_args['limit'] = -1;
        $count_args['return'] = 'ids';
        $total_product_ids = wc_get_products($count_args);
        $total_products = is_array($total_product_ids) ? count($total_product_ids) : 0;
        $total_pages = ceil($total_products / $this->products_per_page);

        // Format products for POS
        foreach ($wc_products as $product) {
            if ($product && $product->is_purchasable()) {
                $products[] = $this->format_product_for_pos($product);
            }
        }

        return array(
            'products'       => $products,
            'total_products' => $total_products,
            'total_pages'    => $total_pages,
            'current_page'   => $page,
            'has_more'       => $page < $total_pages,
        );
    }

    /**
     * Format product data for POS interface
     *
     * @param WC_Product $product WooCommerce product object
     * @return array Formatted product data
     */
    private function format_product_for_pos($product) {
        $product_id = $product->get_id();
        $stock_quantity = $product->get_stock_quantity();
        $is_in_stock = $product->is_in_stock();
        
        // Determine stock level and badge class
        $stock_info = $this->get_stock_info($product);
        
        // Get product image
        $image_info = $this->get_product_image($product);
        
        // Get formatted price
        $price = (float) $product->get_price();
        $regular_price = (float) $product->get_regular_price();
        $sale_price = (float) $product->get_sale_price();
        
        // Format prices for display - completely clean HTML from WooCommerce wc_price()
        $formatted_price = wc_price($price);
        
        // More thorough HTML cleaning to prevent any HTML leakage
        $price_html = strip_tags($formatted_price);
        $price_html = html_entity_decode($price_html, ENT_QUOTES, 'UTF-8');
        $price_html = preg_replace('/[^\d.,\$€£¥₹₽¢₩₪₨₦₫₴₡₵₸₺₼₾₿]/', '', $price_html);
        
        // If cleaning failed, fallback to manual formatting using currency symbol
        if (empty($price_html) || !preg_match('/[\d]/', $price_html)) {
            $currency_symbol = get_woocommerce_currency_symbol();
            $price_html = $currency_symbol . number_format($price, 2);
        }
        
        // Debug logging to identify HTML leakage
        if (strip_tags($price_html) !== $price_html) {
            error_log('WUPOS DEBUG: HTML still present in formatted_price: ' . $price_html);
            error_log('WUPOS DEBUG: Original wc_price output: ' . $formatted_price);
        }
        
        // Get tax information for this product
        $tax_info = $this->get_product_tax_info($product);
        
        // Get categories
        $categories = array();
        $terms = get_the_terms($product_id, 'product_cat');
        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $categories[] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug
                );
            }
        }

        return array(
            'id'               => $product_id,
            'name'             => sanitize_text_field($product->get_name()),
            'price'            => $price,
            'regular_price'    => $regular_price,
            'sale_price'       => $sale_price,
            'formatted_price'  => $price_html,
            'stock_quantity'   => $stock_quantity,
            'stock_status'     => $stock_info['status'],
            'stock_level'      => isset($stock_info['stock_level']) ? $stock_info['stock_level'] : 'unknown',
            'stock_threshold_low'    => isset($stock_info['threshold_info']['wc_low_threshold']) ? $stock_info['threshold_info']['wc_low_threshold'] : null,
            'stock_threshold_medium' => isset($stock_info['threshold_info']['calculated_medium']) ? $stock_info['threshold_info']['calculated_medium'] : null,
            'stock_threshold_high'   => isset($stock_info['threshold_info']['calculated_high']) ? $stock_info['threshold_info']['calculated_high'] : null,
            'stock_badge_class' => $stock_info['badge_class'],
            'stock_text'       => $stock_info['text'],
            'stock_classification' => isset($stock_info['stock_classification']) ? $stock_info['stock_classification'] : null,
            'image_url'        => $image_info['url'],
            'image_alt'        => $image_info['alt'],
            'has_image'        => $image_info['has_image'],
            'is_purchasable'   => $product->is_purchasable(),
            'is_in_stock'      => $is_in_stock,
            'type'             => $product->get_type(),
            'sku'              => sanitize_text_field($product->get_sku()),
            'short_description' => wp_kses_post($product->get_short_description()),
            'categories'       => $categories,
            'weight'           => $product->get_weight(),
            'dimensions'       => array(
                'length' => $product->get_length(),
                'width'  => $product->get_width(),
                'height' => $product->get_height()
            ),
            'tax_info'         => $tax_info
        );
    }

    /**
     * Get stock information for product with WooCommerce integration
     *
     * @param WC_Product $product WooCommerce product object
     * @return array Stock information with dynamic thresholds
     */
    private function get_stock_info($product) {
        // Validate and sanitize stock data first
        $validated_data = $this->validate_stock_data($product);
        
        // Handle validation errors
        if (isset($validated_data['error'])) {
            $thresholds = $this->get_dynamic_stock_thresholds();
            return array(
                'status' => 'error',
                'stock_level' => 'error',
                'badge_class' => 'wupos-stock-error',
                'text' => __('Error en stock', 'wupos'),
                'threshold_info' => $thresholds,
                'error' => $validated_data['error'],
                'stock_classification' => array(
                    'level' => 'error',
                    'quantity' => null,
                    'thresholds' => array(
                        'low' => $thresholds['wc_low_threshold'],
                        'medium' => $thresholds['calculated_medium'],
                        'high' => $thresholds['calculated_high']
                    )
                )
            );
        }
        
        // Extract validated data
        $stock_quantity = $validated_data['stock_quantity'];
        $is_in_stock = $validated_data['is_in_stock'];
        $manage_stock = $validated_data['manage_stock'];
        $stock_status = $validated_data['stock_status'];

        // Get WooCommerce low stock threshold for dynamic calculations
        $thresholds = $this->get_dynamic_stock_thresholds();

        // Handle out of stock
        if (!$is_in_stock || $stock_status === 'outofstock') {
            return array(
                'status' => 'outofstock',
                'stock_level' => 'out',
                'badge_class' => 'wupos-stock-out',
                'text' => __('Sin stock', 'wupos'),
                'threshold_info' => $thresholds,
                'stock_classification' => array(
                    'level' => 'out',
                    'quantity' => 0,
                    'thresholds' => array(
                        'low' => $thresholds['wc_low_threshold'],
                        'medium' => $thresholds['calculated_medium'],
                        'high' => $thresholds['calculated_high']
                    )
                )
            );
        }

        // Handle backorder
        if ($stock_status === 'onbackorder') {
            return array(
                'status' => 'onbackorder',
                'stock_level' => 'backorder',
                'badge_class' => 'wupos-stock-medium',
                'text' => __('Bajo pedido', 'wupos'),
                'threshold_info' => $thresholds,
                'stock_classification' => array(
                    'level' => 'backorder',
                    'quantity' => $stock_quantity ?: 0,
                    'thresholds' => array(
                        'low' => $thresholds['wc_low_threshold'],
                        'medium' => $thresholds['calculated_medium'],
                        'high' => $thresholds['calculated_high']
                    )
                )
            );
        }

        // If stock is not managed, show as in stock
        if (!$manage_stock || $stock_quantity === null) {
            return array(
                'status' => 'instock',
                'stock_level' => 'unlimited',
                'badge_class' => 'wupos-stock-high',
                'text' => __('En stock', 'wupos'),
                'threshold_info' => $thresholds,
                'stock_classification' => array(
                    'level' => 'unlimited',
                    'quantity' => null,
                    'thresholds' => array(
                        'low' => $thresholds['wc_low_threshold'],
                        'medium' => $thresholds['calculated_medium'],
                        'high' => $thresholds['calculated_high']
                    )
                )
            );
        }

        // Sanitize and validate stock quantity
        $stock_quantity = (int) $stock_quantity;
        
        // Handle zero or negative stock
        if ($stock_quantity <= 0) {
            return array(
                'status' => 'outofstock',
                'stock_level' => 'out',
                'badge_class' => 'wupos-stock-out',
                'text' => __('Sin stock', 'wupos'),
                'threshold_info' => $thresholds,
                'stock_classification' => array(
                    'level' => 'out',
                    'quantity' => $stock_quantity,
                    'thresholds' => array(
                        'low' => $thresholds['wc_low_threshold'],
                        'medium' => $thresholds['calculated_medium'],
                        'high' => $thresholds['calculated_high']
                    )
                )
            );
        }

        // Determine stock level using dynamic thresholds
        // Following WUPOS requirements:
        // - Red (Low): Below WooCommerce low stock threshold
        // - Yellow (Medium): Between low threshold and 3x low threshold
        // - Green (High): At or above 3x low threshold (we use 10x as the high threshold for better classification)
        
        $stock_level = 'high';  // Default to high stock
        $status = 'instock';
        $badge_class = 'wupos-stock-high';
        
        if ($stock_quantity < $thresholds['wc_low_threshold']) {
            // Red zone: Below WooCommerce low stock threshold
            $stock_level = 'low';
            $status = 'low-stock';
            $badge_class = 'wupos-stock-low';
        } elseif ($stock_quantity < $thresholds['calculated_medium']) {
            // Yellow zone: Between low threshold and 3x low threshold
            $stock_level = 'medium';
            $status = 'medium-stock';
            $badge_class = 'wupos-stock-medium';
        } else {
            // Green zone: At or above 3x low threshold
            $stock_level = 'high';
            $status = 'high-stock';
            $badge_class = 'wupos-stock-high';
        }

        $stock_info = array(
            'status' => $status,
            'stock_level' => $stock_level,
            'badge_class' => $badge_class,
            'text' => sprintf(__('%d en stock', 'wupos'), $stock_quantity),
            'threshold_info' => $thresholds,
            'stock_classification' => array(
                'level' => $stock_level,
                'quantity' => $stock_quantity,
                'thresholds' => array(
                    'low' => $thresholds['wc_low_threshold'],
                    'medium' => $thresholds['calculated_medium'],
                    'high' => $thresholds['calculated_high']
                )
            )
        );
        
        /**
         * Filter to allow customization of WUPOS product stock information
         *
         * This filter allows developers to modify the stock information returned
         * for each product in the POS system.
         *
         * @since 1.0.0
         * @param array      $stock_info The calculated stock information array
         * @param WC_Product $product    The WooCommerce product object
         * @param array      $thresholds The threshold values used in calculation
         * @return array Modified stock information array
         */
        return apply_filters('wupos_product_stock_info', $stock_info, $product, $thresholds);
    }

    /**
     * Validate and sanitize stock data for consistent API responses
     *
     * This method ensures all stock-related data is properly sanitized and 
     * handles edge cases that might occur with different product configurations.
     *
     * @param WC_Product $product WooCommerce product object
     * @return array Validated stock data
     */
    private function validate_stock_data($product) {
        if (!$product || !is_a($product, 'WC_Product')) {
            return array(
                'stock_quantity' => null,
                'manage_stock' => false,
                'stock_status' => 'outofstock',
                'is_in_stock' => false,
                'error' => 'Invalid product object'
            );
        }

        // Get and sanitize stock data
        $stock_quantity = $product->get_stock_quantity();
        $manage_stock = $product->get_manage_stock();
        $stock_status = $product->get_stock_status();
        $is_in_stock = $product->is_in_stock();

        // Handle numeric stock quantity
        if ($stock_quantity !== null) {
            $stock_quantity = absint($stock_quantity);
            // Allow zero stock but handle negative as zero
            if ($stock_quantity < 0) {
                $stock_quantity = 0;
            }
        }

        // Validate stock status
        $valid_statuses = array('instock', 'outofstock', 'onbackorder');
        if (!in_array($stock_status, $valid_statuses)) {
            $stock_status = $is_in_stock ? 'instock' : 'outofstock';
        }

        // Cross-validate stock quantity with stock status
        if ($manage_stock && $stock_quantity !== null) {
            // If managing stock and quantity is 0 or negative, status should be outofstock
            if ($stock_quantity <= 0 && $stock_status === 'instock') {
                $stock_status = 'outofstock';
                $is_in_stock = false;
            }
        }

        return array(
            'stock_quantity' => $stock_quantity,
            'manage_stock' => (bool) $manage_stock,
            'stock_status' => sanitize_text_field($stock_status),
            'is_in_stock' => (bool) $is_in_stock,
            'product_type' => $product->get_type(),
            'is_purchasable' => $product->is_purchasable()
        );
    }

    /**
     * Get dynamic stock thresholds based on WooCommerce settings
     *
     * This method reads the WooCommerce low stock threshold setting and calculates
     * dynamic thresholds for stock level classification according to WUPOS requirements:
     * - Red (Low Stock): Below WooCommerce low stock threshold
     * - Yellow (Medium Stock): 3x the low stock threshold
     * - Green (High Stock): 10x the low stock threshold or above
     *
     * Example calculations:
     * - If WooCommerce low stock threshold = 5:
     *   * Red (Low): < 5 units
     *   * Yellow (Medium): 5-14 units (5 to 3x5-1)
     *   * Green (High): >= 15 units (3x5)
     *
     * @return array Dynamic stock thresholds
     */
    private function get_dynamic_stock_thresholds() {
        // Get WooCommerce low stock threshold setting
        $wc_low_threshold = get_option('woocommerce_notify_low_stock_amount', 2);
        
        // Ensure we have a valid positive integer, fallback to 2 if invalid
        $wc_low_threshold = absint($wc_low_threshold);
        if ($wc_low_threshold <= 0) {
            $wc_low_threshold = 2;
        }
        
        // Calculate dynamic thresholds based on WooCommerce setting
        $calculated_medium = $wc_low_threshold * 3;  // 3x low stock threshold
        $calculated_high = $wc_low_threshold * 10;   // 10x low stock threshold
        
        $thresholds = array(
            'wc_low_threshold'    => $wc_low_threshold,      // WooCommerce setting (Red threshold)
            'calculated_medium'   => $calculated_medium,     // 3x threshold (Yellow threshold)
            'calculated_high'     => $calculated_high,       // 10x threshold (Green threshold)
            'source_setting'      => 'woocommerce_notify_low_stock_amount',
            'calculation_method'  => 'dynamic_multipliers',
            'multipliers'         => array(
                'medium' => 3,
                'high'   => 10
            )
        );
        
        /**
         * Filter to allow customization of WUPOS stock level thresholds
         *
         * This filter allows developers to modify the stock threshold calculations
         * used by WUPOS for stock level classification.
         *
         * @since 1.0.0
         * @param array $thresholds The calculated thresholds array
         * @param int   $wc_low_threshold The original WooCommerce low stock threshold
         * @return array Modified thresholds array
         */
        return apply_filters('wupos_stock_level_thresholds', $thresholds, $wc_low_threshold);
    }

    /**
     * Get tax information for a specific product
     *
     * @param WC_Product $product WooCommerce product object
     * @return array Tax information
     */
    private function get_product_tax_info($product) {
        if (!$this->is_woocommerce_active() || !wc_tax_enabled()) {
            return array(
                'tax_enabled' => false,
                'tax_inclusive' => false,
                'tax_suffix' => '',
                'tax_class' => ''
            );
        }

        $tax_settings = $this->get_tax_settings();
        $tax_suffix = $this->get_tax_display_suffix();

        return array(
            'tax_enabled' => $tax_settings['tax_enabled'],
            'tax_inclusive' => $tax_settings['tax_inclusive'],
            'tax_suffix' => $tax_suffix,
            'tax_class' => $product->get_tax_class(),
            'tax_display_cart' => $tax_settings['tax_display_cart'],
            'tax_display_shop' => $tax_settings['tax_display_shop']
        );
    }

    /**
     * Get product image information
     *
     * @param WC_Product $product WooCommerce product object
     * @return array Image information
     */
    private function get_product_image($product) {
        $image_id = $product->get_image_id();
        
        if ($image_id) {
            // Get thumbnail size image for POS interface
            $image_url = wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail');
            $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
            
            if (!$image_alt) {
                $image_alt = $product->get_name();
            }
            
            // Fallback to medium size if thumbnail doesn't exist
            if (!$image_url) {
                $image_url = wp_get_attachment_image_url($image_id, 'medium');
            }
            
            // Final fallback to full size
            if (!$image_url) {
                $image_url = wp_get_attachment_image_url($image_id, 'full');
            }
            
            return array(
                'url' => $image_url ?: '',
                'alt' => sanitize_text_field($image_alt),
                'has_image' => !empty($image_url),
            );
        }

        return array(
            'url' => '',
            'alt' => sanitize_text_field($product->get_name()),
            'has_image' => false,
        );
    }

    /**
     * Check if WooCommerce is active
     *
     * @return bool
     */
    private function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }

    /**
     * Check if user has POS permissions
     *
     * @return bool
     */
    private function check_pos_permissions() {
        // Allow users with shop_manager or administrator capabilities
        return current_user_can('manage_woocommerce') || current_user_can('edit_shop_orders');
    }

    /**
     * Get product by ID with proper validation
     *
     * @param int $product_id Product ID
     * @return array|false Product data or false if not found
     */
    public function get_product_by_id($product_id) {
        if (!$this->is_woocommerce_active()) {
            return false;
        }

        $product_id = absint($product_id);
        if (!$product_id) {
            return false;
        }

        $product = wc_get_product($product_id);
        
        if (!$product || !$product->is_purchasable()) {
            return false;
        }

        return $this->format_product_for_pos($product);
    }

    /**
     * AJAX handler for getting single product by ID
     */
    public function get_product_by_id_ajax() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'wupos_nonce')) {
            wp_send_json_error(__('Security check failed', 'wupos'));
            return;
        }

        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            wp_send_json_error(__('WooCommerce is not active', 'wupos'));
            return;
        }

        // Check user permissions
        if (!$this->check_pos_permissions()) {
            wp_send_json_error(__('Insufficient permissions', 'wupos'));
            return;
        }

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        
        if (!$product_id) {
            wp_send_json_error(__('Invalid product ID', 'wupos'));
            return;
        }

        $product_data = $this->get_product_by_id($product_id);
        
        if ($product_data) {
            wp_send_json_success($product_data);
        } else {
            wp_send_json_error(__('Product not found or not purchasable', 'wupos'));
        }
    }

    /**
     * Get product categories for filter dropdown
     *
     * @return array Categories data
     */
    public function get_product_categories() {
        if (!$this->is_woocommerce_active()) {
            return array();
        }

        $categories = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ));

        $formatted_categories = array();
        
        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $formatted_categories[] = array(
                    'id'    => $category->term_id,
                    'slug'  => $category->slug,
                    'name'  => $category->name,
                    'count' => $category->count,
                );
            }
        }

        return $formatted_categories;
    }

    /**
     * AJAX handler for getting product categories
     */
    public function get_categories_ajax() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'wupos_nonce')) {
            wp_send_json_error(__('Security check failed', 'wupos'));
            return;
        }

        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            wp_send_json_error(__('WooCommerce is not active', 'wupos'));
            return;
        }

        // Check user permissions
        if (!$this->check_pos_permissions()) {
            wp_send_json_error(__('Insufficient permissions', 'wupos'));
            return;
        }

        $categories = $this->get_product_categories();
        wp_send_json_success($categories);
    }

    /**
     * AJAX handler for calculating WooCommerce taxes
     */
    public function calculate_taxes_ajax() {
        // Debug logging - start
        error_log('WUPOS DEBUG: Tax calculation AJAX called');
        error_log('WUPOS DEBUG: POST data: ' . print_r($_POST, true));
        
        // Ensure WordPress is fully loaded before proceeding
        if (!did_action('wp_loaded')) {
            error_log('WUPOS DEBUG: WordPress not fully loaded for tax calculation');
            wp_send_json_error(__('System not ready. Please try again.', 'wupos'));
            return;
        }
        
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'wupos_nonce')) {
            error_log('WUPOS DEBUG: Nonce verification failed');
            wp_send_json_error(__('Security check failed', 'wupos'));
            return;
        }
        error_log('WUPOS DEBUG: Nonce verification passed');

        // Check if WooCommerce is active and ready
        if (!$this->is_woocommerce_active()) {
            error_log('WUPOS DEBUG: WooCommerce not active');
            wp_send_json_error(__('WooCommerce is not active', 'wupos'));
            return;
        }
        
        // Enhanced WooCommerce readiness check
        if (!function_exists('WC') || !WC()) {
            error_log('WUPOS DEBUG: WooCommerce functions not ready');
            wp_send_json_error(__('WooCommerce is not ready for cart operations', 'wupos'));
            return;
        }
        error_log('WUPOS DEBUG: WooCommerce is active and ready');

        // Check user permissions
        if (!$this->check_pos_permissions()) {
            error_log('WUPOS DEBUG: Permission check failed');
            wp_send_json_error(__('Insufficient permissions', 'wupos'));
            return;
        }
        error_log('WUPOS DEBUG: Permission check passed');

        // Get cart items from request
        $cart_items = isset($_POST['cart_items']) ? $_POST['cart_items'] : array();
        $customer_data = isset($_POST['customer_data']) ? $_POST['customer_data'] : array();

        error_log('WUPOS DEBUG: Raw cart items received: ' . print_r($cart_items, true));
        error_log('WUPOS DEBUG: Raw customer data received: ' . print_r($customer_data, true));
        
        // Handle JSON-encoded cart items if needed
        if (is_string($cart_items)) {
            error_log('WUPOS DEBUG: Cart items is string, attempting JSON decode');
            $decoded_items = json_decode(stripslashes($cart_items), true);
            if ($decoded_items !== null) {
                $cart_items = $decoded_items;
                error_log('WUPOS DEBUG: Successfully decoded cart items: ' . print_r($cart_items, true));
            } else {
                error_log('WUPOS DEBUG: Failed to decode cart items JSON');
            }
        }
        
        // Handle JSON-encoded customer data if needed
        if (is_string($customer_data)) {
            error_log('WUPOS DEBUG: Customer data is string, attempting JSON decode');
            $decoded_customer = json_decode(stripslashes($customer_data), true);
            if ($decoded_customer !== null) {
                $customer_data = $decoded_customer;
                error_log('WUPOS DEBUG: Successfully decoded customer data: ' . print_r($customer_data, true));
            }
        }

        if (empty($cart_items) || !is_array($cart_items)) {
            error_log('WUPOS DEBUG: Invalid cart data - empty or not array after processing');
            wp_send_json_error(__('Invalid cart data', 'wupos'));
            return;
        }

        try {
            error_log('WUPOS DEBUG: Starting tax calculation');
            $tax_data = $this->calculate_woocommerce_taxes($cart_items, $customer_data);
            error_log('WUPOS DEBUG: Tax calculation completed: ' . print_r($tax_data, true));
            wp_send_json_success($tax_data);
        } catch (Exception $e) {
            error_log('WUPOS DEBUG: Tax calculation exception: ' . $e->getMessage());
            error_log('WUPOS DEBUG: Exception trace: ' . $e->getTraceAsString());
            wp_send_json_error(__('Error calculating taxes: ', 'wupos') . $e->getMessage());
        }
    }

    /**
     * Calculate WooCommerce taxes for cart items
     *
     * @param array $cart_items Cart items data
     * @param array $customer_data Customer location data
     * @return array Tax calculation results
     */
    private function calculate_woocommerce_taxes($cart_items, $customer_data = array()) {
        error_log('WUPOS DEBUG: calculate_woocommerce_taxes called with ' . count($cart_items) . ' items');
        
        // Ensure WordPress is fully loaded before attempting cart operations
        if (!did_action('wp_loaded')) {
            error_log('WUPOS DEBUG: WordPress not fully loaded, cannot safely access cart');
            throw new Exception(__('WordPress not fully loaded. Cannot perform cart operations safely.', 'wupos'));
        }
        
        // Additional safety check - ensure WooCommerce is ready for cart operations
        if (!function_exists('WC') || !WC() || !did_action('woocommerce_init')) {
            error_log('WUPOS DEBUG: WooCommerce not ready for cart operations');
            throw new Exception(__('WooCommerce not ready for cart operations', 'wupos'));
        }
        
        // Initialize WooCommerce cart and tax calculations
        if (!function_exists('WC')) {
            error_log('WUPOS DEBUG: WC function not available');
            throw new Exception(__('WooCommerce functions not available', 'wupos'));
        }
        error_log('WUPOS DEBUG: WC function available');

        // Ensure WooCommerce is properly initialized
        if (!WC()->cart) {
            error_log('WUPOS DEBUG: WC cart not initialized, initializing...');
            // Only initialize if we're in a safe context (after wp_loaded)
            if (did_action('wp_loaded')) {
                WC()->init();
                WC()->frontend_includes();
                if (is_null(WC()->cart)) {
                    error_log('WUPOS DEBUG: WC cart still null after init');
                    WC()->cart = new WC_Cart();
                }
            } else {
                error_log('WUPOS DEBUG: Cannot initialize WC cart - WordPress not fully loaded');
                throw new Exception(__('Cannot initialize WooCommerce cart before WordPress is fully loaded', 'wupos'));
            }
        }
        
        // Clear any existing cart - but only if it's safe to do so
        if (WC()->cart && method_exists(WC()->cart, 'empty_cart')) {
            WC()->cart->empty_cart();
            error_log('WUPOS DEBUG: Cart emptied');
        } else {
            error_log('WUPOS DEBUG: Cart empty method not available');
        }

        // Set customer location for tax calculation
        $this->set_customer_location($customer_data);

        // Get tax settings first to determine calculation method
        $tax_settings = $this->get_tax_settings();
        $prices_include_tax = $tax_settings['tax_inclusive'];
        $tax_enabled = $tax_settings['tax_enabled'];
        
        $subtotal = 0;
        $tax_total = 0;
        $tax_breakdown = array();
        $cart_contents = array();
        $total = 0;

        // Add items to WooCommerce cart for tax calculation
        error_log('WUPOS DEBUG: Adding items to cart...');
        foreach ($cart_items as $index => $item) {
            error_log('WUPOS DEBUG: Processing item ' . $index . ': ' . print_r($item, true));
            
            $product_id = absint($item['id']);
            $quantity = absint($item['quantity']);
            $price = floatval($item['price']);

            error_log("WUPOS DEBUG: Parsed - ID: $product_id, Qty: $quantity, Price: $price");

            if ($product_id <= 0 || $quantity <= 0) {
                error_log('WUPOS DEBUG: Skipping item - invalid ID or quantity');
                continue;
            }

            $product = wc_get_product($product_id);
            if (!$product || !$product->is_purchasable()) {
                error_log('WUPOS DEBUG: Skipping item - product not found or not purchasable');
                continue;
            }

            error_log('WUPOS DEBUG: Product found: ' . $product->get_name());

            // Set product price for calculation (in case of custom pricing)
            if ($price !== (float) $product->get_price()) {
                error_log('WUPOS DEBUG: Custom price detected, creating temp product');
                // Create a temporary product with custom price
                $temp_product = clone $product;
                $temp_product->set_price($price);
                $temp_product->set_regular_price($price);
                $product = $temp_product;
            }

            // Add to cart for tax calculation
            error_log('WUPOS DEBUG: Adding to WC cart...');
            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);
            
            if ($cart_item_key) {
                error_log('WUPOS DEBUG: Successfully added to cart with key: ' . $cart_item_key);
                $cart_contents[] = array(
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'line_total' => $price * $quantity,
                    'product_name' => $product->get_name(),
                    'tax_class' => $product->get_tax_class()
                );
            } else {
                error_log('WUPOS DEBUG: Failed to add item to cart');
            }
        }
        
        error_log('WUPOS DEBUG: Cart contents: ' . print_r($cart_contents, true));

        // Only calculate taxes if enabled
        if ($tax_enabled) {
            // Calculate taxes using WooCommerce - but only if cart is available
            if (WC()->cart && method_exists(WC()->cart, 'calculate_totals')) {
                WC()->cart->calculate_totals();

                // Get proper tax totals from WooCommerce cart
                $cart_tax_total = WC()->cart->get_cart_tax();
                $cart_subtotal = WC()->cart->get_subtotal();
                $cart_subtotal_tax = WC()->cart->get_subtotal_tax();
                $cart_total = WC()->cart->get_total('raw');

                // Get tax totals for breakdown
                $tax_totals = WC()->cart->get_tax_totals();
            } else {
                error_log('WUPOS DEBUG: Cart methods not available for tax calculation');
                throw new Exception(__('WooCommerce cart methods not available for tax calculation', 'wupos'));
            }
            
            // Build tax breakdown with rate percentages
            foreach ($tax_totals as $tax_total_obj) {
                $tax_amount = floatval($tax_total_obj->amount);
                $rate_id = isset($tax_total_obj->rate_id) ? $tax_total_obj->rate_id : '';
                $is_compound = isset($tax_total_obj->is_compound) ? $tax_total_obj->is_compound : false;
                
                // Get tax rate percentage if rate_id is available
                $rate_percentage = '';
                if (!empty($rate_id)) {
                    $tax_rate_data = WC_Tax::_get_tax_rate($rate_id);
                    if ($tax_rate_data && isset($tax_rate_data['tax_rate'])) {
                        $rate_percentage = floatval($tax_rate_data['tax_rate']);
                    }
                }
                
                // Build enhanced label with percentage if available
                $enhanced_label = $tax_total_obj->label;
                if (!empty($rate_percentage)) {
                    // Check if percentage is already in the label
                    if (strpos($enhanced_label, '%') === false) {
                        $enhanced_label = sprintf('%s (%s%%)', $tax_total_obj->label, number_format($rate_percentage, 1));
                    }
                }
                
                // Clean formatted amount to prevent HTML leakage
                $clean_formatted_amount = strip_tags($tax_total_obj->formatted_amount);
                $clean_formatted_amount = html_entity_decode($clean_formatted_amount, ENT_QUOTES, 'UTF-8');
                
                $tax_breakdown[] = array(
                    'label' => $tax_total_obj->label,
                    'enhanced_label' => $enhanced_label,
                    'amount' => $tax_amount,
                    'formatted_amount' => $clean_formatted_amount,
                    'rate_id' => $rate_id,
                    'rate_percentage' => $rate_percentage,
                    'is_compound' => $is_compound
                );
            }
            
            // Calculate proper subtotal and totals based on WooCommerce tax settings
            if ($prices_include_tax) {
                // Prices include tax - subtotal should be tax-exclusive amount
                $subtotal = $cart_subtotal;
                $tax_total = $cart_subtotal_tax;
                $total = $cart_total;
            } else {
                // Prices exclude tax - subtotal is the base amount, add tax for total
                $subtotal = $cart_subtotal;
                $tax_total = $cart_tax_total;
                $total = $cart_total;
            }
        } else {
            // Taxes disabled - calculate subtotal from cart items
            foreach ($cart_contents as $item) {
                $subtotal += $item['line_total'];
            }
            $tax_total = 0;
            $tax_breakdown = array();
            $total = $subtotal;
        }

        // Clean up cart - but only if it's safe to do so
        if (WC()->cart && method_exists(WC()->cart, 'empty_cart')) {
            WC()->cart->empty_cart();
        }

        // Get tax display suffix
        $tax_suffix = $this->get_tax_display_suffix();

        return array(
            'subtotal' => $subtotal,
            'tax_total' => $tax_total,
            'total' => $total,
            'tax_breakdown' => $tax_breakdown,
            'tax_enabled' => $tax_enabled,
            'tax_inclusive' => $prices_include_tax,
            'tax_display_cart' => $tax_settings['tax_display_cart'],
            'tax_suffix' => $tax_suffix,
            'cart_contents' => $cart_contents,
            'currency_symbol' => get_woocommerce_currency_symbol(),
            'currency_position' => get_option('woocommerce_currency_pos'),
            'decimal_separator' => wc_get_price_decimal_separator(),
            'thousand_separator' => wc_get_price_thousand_separator(),
            'decimals' => wc_get_price_decimals()
        );
    }

    /**
     * Set customer location for tax calculation
     *
     * @param array $customer_data Customer location data
     */
    private function set_customer_location($customer_data) {
        // Set default location if no customer data provided
        if (empty($customer_data)) {
            // Use store location as default
            $country = get_option('woocommerce_default_country');
            $state = '';

            if (strstr($country, ':')) {
                $country_parts = explode(':', $country);
                $country = $country_parts[0];
                $state = $country_parts[1];
            }

            $customer_data = array(
                'country' => $country,
                'state' => $state,
                'postcode' => get_option('woocommerce_store_postcode', ''),
                'city' => get_option('woocommerce_store_city', '')
            );
        }

        // Set customer location in WooCommerce
        if (WC()->customer) {
            WC()->customer->set_billing_country($customer_data['country']);
            WC()->customer->set_billing_state(isset($customer_data['state']) ? $customer_data['state'] : '');
            WC()->customer->set_billing_postcode(isset($customer_data['postcode']) ? $customer_data['postcode'] : '');
            WC()->customer->set_billing_city(isset($customer_data['city']) ? $customer_data['city'] : '');

            // Set shipping location same as billing for tax calculation
            WC()->customer->set_shipping_country($customer_data['country']);
            WC()->customer->set_shipping_state(isset($customer_data['state']) ? $customer_data['state'] : '');
            WC()->customer->set_shipping_postcode(isset($customer_data['postcode']) ? $customer_data['postcode'] : '');
            WC()->customer->set_shipping_city(isset($customer_data['city']) ? $customer_data['city'] : '');
        }
    }

    /**
     * Get WooCommerce tax settings
     *
     * @return array Tax settings
     */
    private function get_tax_settings() {
        return array(
            'tax_enabled' => wc_tax_enabled(),
            'tax_inclusive' => wc_prices_include_tax(),
            'tax_display_cart' => get_option('woocommerce_tax_display_cart'),
            'tax_display_shop' => get_option('woocommerce_tax_display_shop'),
            'calc_taxes' => get_option('woocommerce_calc_taxes'),
            'tax_based_on' => get_option('woocommerce_tax_based_on'),
            'shipping_tax_class' => get_option('woocommerce_shipping_tax_class'),
            'tax_round_at_subtotal' => get_option('woocommerce_tax_round_at_subtotal'),
            'tax_classes' => WC_Tax::get_tax_classes()
        );
    }

    /**
     * Get WooCommerce tax display suffix
     *
     * @return string Tax display suffix
     */
    private function get_tax_display_suffix() {
        if (!$this->is_woocommerce_active() || !wc_tax_enabled()) {
            return '';
        }

        // Get the configured tax suffix from WooCommerce settings
        $suffix = get_option('woocommerce_price_display_suffix', '');
        
        // Process suffix with WooCommerce variables if custom suffix exists
        if (!empty($suffix)) {
            // Replace WooCommerce price suffix variables
            $suffix = str_replace(
                array('{price_including_tax}', '{price_excluding_tax}'),
                array(
                    wc_price(100), // Example price with tax
                    wc_price(100)  // Example price without tax
                ),
                $suffix
            );
            
            // Clean up any remaining HTML tags and decode entities for POS display
            $suffix = html_entity_decode(wp_strip_all_tags($suffix), ENT_QUOTES, 'UTF-8');
        } else {
            // If no custom suffix is set, use default based on tax settings
            if (wc_prices_include_tax()) {
                $suffix = __('incl. tax', 'wupos');
            } else {
                $suffix = __('excl. tax', 'wupos');
            }
        }
        
        return sanitize_text_field(trim($suffix));
    }

    /**
     * AJAX handler for getting WooCommerce tax settings
     */
    public function get_tax_settings_ajax() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'wupos_nonce')) {
            wp_send_json_error(__('Security check failed', 'wupos'));
            return;
        }

        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            wp_send_json_error(__('WooCommerce is not active', 'wupos'));
            return;
        }

        // Check user permissions
        if (!$this->check_pos_permissions()) {
            wp_send_json_error(__('Insufficient permissions', 'wupos'));
            return;
        }

        try {
            $tax_settings = $this->get_tax_settings();
            $tax_suffix = $this->get_tax_display_suffix();
            
            $response_data = array(
                'tax_enabled' => $tax_settings['tax_enabled'],
                'tax_inclusive' => $tax_settings['tax_inclusive'],
                'tax_display_cart' => $tax_settings['tax_display_cart'],
                'tax_display_shop' => $tax_settings['tax_display_shop'],
                'tax_suffix' => $tax_suffix,
                'tax_based_on' => $tax_settings['tax_based_on'],
                'currency_symbol' => get_woocommerce_currency_symbol(),
                'currency_position' => get_option('woocommerce_currency_pos'),
                'decimal_separator' => wc_get_price_decimal_separator(),
                'thousand_separator' => wc_get_price_thousand_separator(),
                'decimals' => wc_get_price_decimals()
            );
            
            wp_send_json_success($response_data);
        } catch (Exception $e) {
            wp_send_json_error(__('Error getting tax settings: ', 'wupos') . $e->getMessage());
        }
    }

    /**
     * Debug method to test stock level calculations
     * 
     * This method can be used to test the stock level calculation logic
     * with different stock quantities and threshold settings.
     * 
     * @param int $test_quantity Stock quantity to test
     * @return array Debug information about stock level calculation
     */
    public function debug_stock_calculation($test_quantity = null) {
        $thresholds = $this->get_dynamic_stock_thresholds();
        
        if ($test_quantity === null) {
            return $thresholds;
        }
        
        $test_quantity = absint($test_quantity);
        
        // Simulate stock level calculation
        $stock_level = 'high';
        if ($test_quantity < $thresholds['wc_low_threshold']) {
            $stock_level = 'low';
        } elseif ($test_quantity < $thresholds['calculated_medium']) {
            $stock_level = 'medium';
        }
        
        return array(
            'test_quantity' => $test_quantity,
            'calculated_level' => $stock_level,
            'thresholds' => $thresholds,
            'classification' => array(
                'is_low' => $test_quantity < $thresholds['wc_low_threshold'],
                'is_medium' => $test_quantity >= $thresholds['wc_low_threshold'] && $test_quantity < $thresholds['calculated_medium'],
                'is_high' => $test_quantity >= $thresholds['calculated_medium']
            )
        );
    }

    /**
     * Get available tax rates for display
     *
     * @return array Tax rates information
     */
    public function get_tax_rates_info() {
        if (!$this->is_woocommerce_active() || !wc_tax_enabled()) {
            return array();
        }

        $tax_classes = WC_Tax::get_tax_classes();
        $tax_rates_info = array();

        // Add standard rate (empty tax class)
        $standard_rates = WC_Tax::find_rates();
        if (!empty($standard_rates)) {
            $tax_rates_info['standard'] = array(
                'class_name' => __('Standard', 'wupos'),
                'rates' => $standard_rates
            );
        }

        // Add other tax classes
        foreach ($tax_classes as $tax_class) {
            $rates = WC_Tax::find_rates(array('tax_class' => $tax_class));
            if (!empty($rates)) {
                $tax_rates_info[sanitize_title($tax_class)] = array(
                    'class_name' => $tax_class,
                    'rates' => $rates
                );
            }
        }

        return $tax_rates_info;
    }
}