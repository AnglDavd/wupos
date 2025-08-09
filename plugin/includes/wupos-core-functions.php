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