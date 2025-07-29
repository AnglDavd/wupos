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

        // Sanitize input parameters
        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        try {
            $products_data = $this->get_woocommerce_products($page, $category, $search);
            wp_send_json_success($products_data);
        } catch (Exception $e) {
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
        
        // Format prices for display
        $formatted_price = wc_price($price);
        $price_html = strip_tags($formatted_price);
        
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
            'stock_badge_class' => $stock_info['badge_class'],
            'stock_text'       => $stock_info['text'],
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
     * Get stock information for product
     *
     * @param WC_Product $product WooCommerce product object
     * @return array Stock information
     */
    private function get_stock_info($product) {
        $stock_quantity = $product->get_stock_quantity();
        $is_in_stock = $product->is_in_stock();
        $manage_stock = $product->get_manage_stock();
        $stock_status = $product->get_stock_status();

        // Handle out of stock
        if (!$is_in_stock || $stock_status === 'outofstock') {
            return array(
                'status' => 'outofstock',
                'badge_class' => 'wupos-stock-out',
                'text' => __('Sin stock', 'wupos'),
            );
        }

        // Handle backorder
        if ($stock_status === 'onbackorder') {
            return array(
                'status' => 'onbackorder',
                'badge_class' => 'wupos-stock-medium',
                'text' => __('Bajo pedido', 'wupos'),
            );
        }

        // If stock is not managed, show as in stock
        if (!$manage_stock || $stock_quantity === null) {
            return array(
                'status' => 'instock',
                'badge_class' => 'wupos-stock-high',
                'text' => __('En stock', 'wupos'),
            );
        }

        // Determine stock level based on quantity
        $stock_quantity = (int) $stock_quantity;
        if ($stock_quantity <= 0) {
            return array(
                'status' => 'outofstock',
                'badge_class' => 'wupos-stock-out',
                'text' => __('Sin stock', 'wupos'),
            );
        } elseif ($stock_quantity <= 2) {
            $badge_class = 'wupos-stock-low';
            $status = 'low-stock';
        } elseif ($stock_quantity <= 10) {
            $badge_class = 'wupos-stock-medium';
            $status = 'medium-stock';
        } else {
            $badge_class = 'wupos-stock-high';
            $status = 'high-stock';
        }

        return array(
            'status' => $status,
            'badge_class' => $badge_class,
            'text' => sprintf(__('%d en stock', 'wupos'), $stock_quantity),
        );
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
        
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'wupos_nonce')) {
            error_log('WUPOS DEBUG: Nonce verification failed');
            wp_send_json_error(__('Security check failed', 'wupos'));
            return;
        }
        error_log('WUPOS DEBUG: Nonce verification passed');

        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            error_log('WUPOS DEBUG: WooCommerce not active');
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
        
        // Initialize WooCommerce cart and tax calculations
        if (!function_exists('WC')) {
            error_log('WUPOS DEBUG: WC function not available');
            throw new Exception(__('WooCommerce functions not available', 'wupos'));
        }
        error_log('WUPOS DEBUG: WC function available');

        // Ensure WooCommerce is properly initialized
        if (!WC()->cart) {
            error_log('WUPOS DEBUG: WC cart not initialized, initializing...');
            WC()->init();
            WC()->frontend_includes();
            if (is_null(WC()->cart)) {
                error_log('WUPOS DEBUG: WC cart still null after init');
                WC()->cart = new WC_Cart();
            }
        }
        
        // Clear any existing cart
        WC()->cart->empty_cart();
        error_log('WUPOS DEBUG: Cart emptied');

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
            // Calculate taxes using WooCommerce
            WC()->cart->calculate_totals();

            // Get proper tax totals from WooCommerce cart
            $cart_tax_total = WC()->cart->get_cart_tax();
            $cart_subtotal = WC()->cart->get_subtotal();
            $cart_subtotal_tax = WC()->cart->get_subtotal_tax();
            $cart_total = WC()->cart->get_total('raw');

            // Get tax totals for breakdown
            $tax_totals = WC()->cart->get_tax_totals();
            
            // Build tax breakdown
            foreach ($tax_totals as $tax_total_obj) {
                $tax_amount = floatval($tax_total_obj->amount);
                $tax_breakdown[] = array(
                    'label' => $tax_total_obj->label,
                    'amount' => $tax_amount,
                    'formatted_amount' => $tax_total_obj->formatted_amount,
                    'rate_id' => isset($tax_total_obj->rate_id) ? $tax_total_obj->rate_id : '',
                    'is_compound' => isset($tax_total_obj->is_compound) ? $tax_total_obj->is_compound : false
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

        // Clean up cart
        WC()->cart->empty_cart();

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
            
            // Clean up any remaining HTML tags for POS display
            $suffix = wp_strip_all_tags($suffix);
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