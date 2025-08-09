<?php
/**
 * WUPOS Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @package WUPOS
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if WooCommerce is active
 *
 * @since 1.0.0
 * @return bool
 */
function wupos_is_woocommerce_active() {
    return class_exists('WooCommerce');
}

/**
 * Check if HPOS is enabled
 *
 * @since 1.0.0
 * @return bool
 */
function wupos_is_hpos_enabled() {
    if (!wupos_is_woocommerce_active()) {
        return false;
    }
    
    return class_exists('Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore') &&
           wc_get_container()->get('Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore')->is_table_available();
}

/**
 * Get WUPOS version
 *
 * @since 1.0.0
 * @return string
 */
function wupos_get_version() {
    return defined('WUPOS_VERSION') ? WUPOS_VERSION : '1.0.0';
}

/**
 * Log function for debugging
 *
 * @since 1.0.0
 * @param mixed $message
 * @param string $level
 */
function wupos_log($message, $level = 'info') {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }
        error_log(sprintf('[WUPOS-%s] %s', strtoupper($level), $message));
    }
}

/**
 * Check if user has POS capabilities
 *
 * @since 1.0.0
 * @param int $user_id Optional user ID
 * @return bool
 */
function wupos_user_can_pos($user_id = null) {
    if (null === $user_id) {
        $user_id = get_current_user_id();
    }
    
    return user_can($user_id, 'manage_woocommerce_pos') || user_can($user_id, 'manage_woocommerce');
}

/**
 * Sanitize POS input data
 *
 * @since 1.0.0
 * @param mixed $input
 * @return mixed
 */
function wupos_sanitize_input($input) {
    if (is_array($input)) {
        return array_map('wupos_sanitize_input', $input);
    }
    
    return sanitize_text_field($input);
}

/**
 * Format price for POS display
 *
 * @since 1.0.0
 * @param float $price
 * @return string
 */
function wupos_format_price($price) {
    if (!wupos_is_woocommerce_active()) {
        return number_format($price, 2);
    }
    
    return wc_price($price);
}

/**
 * Get current POS session ID
 *
 * @since 1.0.0
 * @return string
 */
function wupos_get_session_id() {
    $session_id = get_user_meta(get_current_user_id(), '_wupos_session_id', true);
    
    if (!$session_id) {
        $session_id = 'wupos_' . wp_generate_password(12, false);
        update_user_meta(get_current_user_id(), '_wupos_session_id', $session_id);
    }
    
    return $session_id;
}

/**
 * Verify nonce for AJAX requests
 *
 * @since 1.0.0
 * @param string $action
 * @return bool
 */
function wupos_verify_nonce($action = 'wupos_ajax') {
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    return wp_verify_nonce($nonce, $action);
}

/**
 * Get WUPOS template path
 *
 * @since 1.0.0
 * @param string $template
 * @return string
 */
function wupos_get_template_path($template) {
    $template_path = WUPOS_PLUGIN_DIR . 'templates/' . $template;
    
    // Check if theme has custom template
    $theme_template = get_template_directory() . '/wupos/' . $template;
    if (file_exists($theme_template)) {
        return $theme_template;
    }
    
    return $template_path;
}

/**
 * Get product data optimized for POS
 *
 * @since 1.0.0
 * @param int $product_id Product ID
 * @return array|false Product data or false if not found
 */
function wupos_get_product_data($product_id) {
    $product = wc_get_product($product_id);
    
    if (!$product) {
        return false;
    }
    
    return array(
        'id' => $product->get_id(),
        'name' => $product->get_name(),
        'sku' => $product->get_sku(),
        'price' => $product->get_price(),
        'stock_quantity' => $product->get_stock_quantity(),
        'stock_status' => $product->get_stock_status(),
        'manage_stock' => $product->get_manage_stock(),
        'type' => $product->get_type(),
        'image_url' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
    );
}

/**
 * Check if product is available for POS
 *
 * @since 1.0.0
 * @param WC_Product|int $product Product object or ID
 * @return bool True if available
 */
function wupos_is_product_available($product) {
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }
    
    if (!$product || !$product->exists()) {
        return false;
    }
    
    // Must be published and purchasable
    if ($product->get_status() !== 'publish' || !$product->is_purchasable()) {
        return false;
    }
    
    // Must be visible (not hidden)
    if ($product->get_catalog_visibility() === 'hidden') {
        return false;
    }
    
    return apply_filters('wupos_is_product_available', true, $product);
}

/**
 * Get WooCommerce order by ID with HPOS compatibility
 *
 * @since 1.0.0
 * @param int $order_id Order ID
 * @return WC_Order|false Order object or false
 */
function wupos_get_order($order_id) {
    return wc_get_order($order_id);
}

/**
 * Create POS order with proper metadata
 *
 * @since 1.0.0
 * @param array $order_data Order data
 * @return WC_Order|WP_Error Order object or error
 */
function wupos_create_pos_order($order_data = array()) {
    try {
        $order = wc_create_order();
        
        if (is_wp_error($order)) {
            return $order;
        }
        
        // Set POS metadata
        $order->add_meta_data('_wupos_pos_order', 'yes');
        $order->add_meta_data('_wupos_version', WUPOS_VERSION);
        $order->add_meta_data('_wupos_terminal_id', wupos_get_session_id());
        $order->add_meta_data('_wupos_cashier_id', get_current_user_id());
        $order->add_meta_data('_wupos_created_timestamp', current_time('timestamp'));
        
        // Add custom order data
        if (isset($order_data['customer_id']) && $order_data['customer_id'] > 0) {
            $order->set_customer_id($order_data['customer_id']);
        }
        
        if (isset($order_data['line_items']) && is_array($order_data['line_items'])) {
            foreach ($order_data['line_items'] as $item) {
                $product = wc_get_product($item['product_id']);
                if ($product) {
                    $order->add_product($product, $item['quantity']);
                }
            }
        }
        
        // Calculate totals
        $order->calculate_totals();
        $order->save();
        
        do_action('wupos_order_created', $order->get_id(), $order_data);
        
        return $order;
        
    } catch (Exception $e) {
        wupos_log('Error creating POS order: ' . $e->getMessage(), 'error');
        return new WP_Error('order_creation_failed', $e->getMessage());
    }
}

/**
 * Get current terminal/session info
 *
 * @since 1.0.0
 * @return array Terminal information
 */
function wupos_get_terminal_info() {
    return array(
        'terminal_id' => wupos_get_session_id(),
        'user_id' => get_current_user_id(),
        'user_name' => wp_get_current_user()->display_name,
        'timestamp' => current_time('timestamp'),
        'timezone' => wp_timezone_string(),
        'hpos_enabled' => wupos_is_hpos_enabled(),
        'wc_version' => defined('WC_VERSION') ? WC_VERSION : '0.0.0',
        'wp_version' => get_bloginfo('version'),
    );
}

/**
 * Clean up expired POS sessions
 *
 * @since 1.0.0
 */
function wupos_cleanup_expired_sessions() {
    global $wpdb;
    
    // Clean up sessions older than 24 hours
    $expired_time = current_time('timestamp') - DAY_IN_SECONDS;
    
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->usermeta} 
             WHERE meta_key = '_wupos_session_id' 
             AND meta_value LIKE 'wupos_%%' 
             AND user_id IN (
                SELECT user_id FROM {$wpdb->usermeta} 
                WHERE meta_key = '_wupos_session_timestamp' 
                AND meta_value < %d
             )",
            $expired_time
        )
    );
    
    wupos_log('Cleaned up expired POS sessions', 'debug');
}

/**
 * Get formatted currency amount
 *
 * @since 1.0.0
 * @param float $amount Amount to format
 * @param string $currency Currency code
 * @return string Formatted amount
 */
function wupos_format_currency($amount, $currency = '') {
    if (empty($currency)) {
        $currency = get_woocommerce_currency();
    }
    
    return wc_price($amount, array('currency' => $currency));
}

/**
 * Calculate tax for amount
 *
 * @since 1.0.0
 * @param float $amount Amount to calculate tax for
 * @param string $tax_class Tax class
 * @return float Tax amount
 */
function wupos_calculate_tax($amount, $tax_class = '') {
    if (!wupos_is_woocommerce_active()) {
        return 0;
    }
    
    $tax_rates = WC_Tax::get_rates($tax_class);
    $taxes = WC_Tax::calc_tax($amount, $tax_rates);
    
    return array_sum($taxes);
}

/**
 * Get POS settings
 *
 * @since 1.0.0
 * @param string $setting_name Specific setting name
 * @param mixed $default Default value
 * @return mixed Setting value
 */
function wupos_get_setting($setting_name = '', $default = null) {
    $settings = array(
        'currency_symbol' => get_woocommerce_currency_symbol(),
        'currency_position' => get_option('woocommerce_currency_pos', 'left'),
        'tax_display' => get_option('wupos_tax_display', 'excl'),
        'receipt_template' => get_option('wupos_receipt_template', 'default'),
        'barcode_field' => get_option('wupos_barcode_field', '_sku'),
        'customer_registration' => get_option('wupos_customer_registration', 'yes'),
        'default_customer' => get_option('wupos_default_customer', 0),
        'auto_print_receipt' => get_option('wupos_auto_print_receipt', 'no'),
        'sound_enabled' => get_option('wupos_sound_enabled', 'yes'),
    );
    
    $settings = apply_filters('wupos_settings', $settings);
    
    if (empty($setting_name)) {
        return $settings;
    }
    
    return isset($settings[$setting_name]) ? $settings[$setting_name] : $default;
}

/**
 * Update POS setting
 *
 * @since 1.0.0
 * @param string $setting_name Setting name
 * @param mixed $value Setting value
 * @return bool Success status
 */
function wupos_update_setting($setting_name, $value) {
    $option_name = 'wupos_' . $setting_name;
    return update_option($option_name, $value);
}

/**
 * Get POS performance metrics
 *
 * @since 1.0.0
 * @return array Performance metrics
 */
function wupos_get_performance_metrics() {
    global $wpdb;
    
    return array(
        'database_queries' => $wpdb->num_queries,
        'memory_usage' => memory_get_usage(true),
        'memory_peak' => memory_get_peak_usage(true),
        'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
        'cache_status' => wp_using_ext_object_cache() ? 'external' : 'internal',
        'hpos_enabled' => wupos_is_hpos_enabled(),
    );
}