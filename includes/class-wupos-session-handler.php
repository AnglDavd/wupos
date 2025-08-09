<?php
/**
 * WUPOS Session Handler
 *
 * Manages secure cart sessions for multi-terminal POS operations
 * with WordPress native session handling and terminal identification.
 *
 * @package WUPOS\Session
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS_Session_Handler class.
 *
 * Handles secure session management for POS cart operations
 * with multi-terminal support and concurrent access control.
 */
class WUPOS_Session_Handler {

    /**
     * Session prefix for WordPress transients
     */
    const SESSION_PREFIX = 'wupos_session_';

    /**
     * Cart prefix for session data
     */
    const CART_PREFIX = 'wupos_cart_';

    /**
     * Default session timeout in seconds (4 hours)
     */
    const DEFAULT_TIMEOUT = 14400;

    /**
     * Session extension time in seconds (1 hour)
     */
    const EXTENSION_TIME = 3600;

    /**
     * Current session ID
     *
     * @var string
     */
    private $session_id;

    /**
     * Terminal ID
     *
     * @var string
     */
    private $terminal_id;

    /**
     * User ID
     *
     * @var int
     */
    private $user_id;

    /**
     * Session data
     *
     * @var array
     */
    private $session_data = array();

    /**
     * Constructor.
     *
     * @param string $terminal_id Terminal identifier
     */
    public function __construct($terminal_id = '') {
        $this->user_id = get_current_user_id();
        $this->terminal_id = $this->sanitize_terminal_id($terminal_id);
        $this->init_session();
        
        // Hook for cleanup expired sessions
        add_action('wupos_cleanup_expired_sessions', array($this, 'cleanup_expired_sessions'));
        
        // Schedule cleanup if not already scheduled
        if (!wp_next_scheduled('wupos_cleanup_expired_sessions')) {
            wp_schedule_event(time(), 'hourly', 'wupos_cleanup_expired_sessions');
        }
    }

    /**
     * Initialize session.
     */
    private function init_session() {
        $this->session_id = $this->get_or_create_session_id();
        $this->load_session_data();
    }

    /**
     * Get or create session ID.
     *
     * @return string Session ID
     */
    private function get_or_create_session_id() {
        // Check for existing session in cookie
        $cookie_name = 'wupos_session_' . md5($this->terminal_id . $this->user_id);
        $existing_session = isset($_COOKIE[$cookie_name]) ? sanitize_text_field($_COOKIE[$cookie_name]) : '';
        
        if ($existing_session && $this->validate_session($existing_session)) {
            return $existing_session;
        }

        // Create new session ID
        $session_id = $this->generate_session_id();
        
        // Set session cookie (secure, httponly)
        $this->set_session_cookie($cookie_name, $session_id);
        
        return $session_id;
    }

    /**
     * Generate secure session ID.
     *
     * @return string Secure session ID
     */
    private function generate_session_id() {
        $random_bytes = wp_generate_password(32, false, false);
        $timestamp = current_time('timestamp');
        $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');
        
        return hash('sha256', $random_bytes . $timestamp . $this->user_id . $this->terminal_id . $user_agent);
    }

    /**
     * Validate session ID.
     *
     * @param string $session_id Session ID to validate
     * @return bool True if valid
     */
    private function validate_session($session_id) {
        if (empty($session_id) || strlen($session_id) !== 64) {
            return false;
        }

        // Check if session exists in transients
        $session_key = self::SESSION_PREFIX . $session_id;
        $session_data = get_transient($session_key);
        
        if (!$session_data) {
            return false;
        }

        // Verify session belongs to current user and terminal
        if ($session_data['user_id'] !== $this->user_id || 
            $session_data['terminal_id'] !== $this->terminal_id) {
            return false;
        }

        return true;
    }

    /**
     * Set session cookie.
     *
     * @param string $name Cookie name
     * @param string $value Cookie value
     */
    private function set_session_cookie($name, $value) {
        $expire = time() + self::DEFAULT_TIMEOUT;
        $secure = is_ssl();
        $httponly = true;
        $samesite = 'Lax';

        // PHP 7.3+ supports SameSite parameter
        if (PHP_VERSION_ID >= 70300) {
            setcookie($name, $value, array(
                'expires' => $expire,
                'path' => COOKIEPATH,
                'domain' => COOKIE_DOMAIN,
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite
            ));
        } else {
            setcookie($name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure, $httponly);
        }
    }

    /**
     * Load session data from transients.
     */
    private function load_session_data() {
        $session_key = self::SESSION_PREFIX . $this->session_id;
        $this->session_data = get_transient($session_key) ?: array();
        
        // Initialize session data if empty
        if (empty($this->session_data)) {
            $this->session_data = array(
                'user_id' => $this->user_id,
                'terminal_id' => $this->terminal_id,
                'created_at' => current_time('timestamp'),
                'last_activity' => current_time('timestamp'),
                'cart' => array(),
                'customer_id' => 0,
                'meta' => array()
            );
            $this->save_session_data();
        } else {
            // Update last activity
            $this->session_data['last_activity'] = current_time('timestamp');
            $this->save_session_data();
        }
    }

    /**
     * Save session data to transients.
     *
     * @return bool Success status
     */
    private function save_session_data() {
        $session_key = self::SESSION_PREFIX . $this->session_id;
        return set_transient($session_key, $this->session_data, self::DEFAULT_TIMEOUT);
    }

    /**
     * Get session ID.
     *
     * @return string Session ID
     */
    public function get_session_id() {
        return $this->session_id;
    }

    /**
     * Get terminal ID.
     *
     * @return string Terminal ID
     */
    public function get_terminal_id() {
        return $this->terminal_id;
    }

    /**
     * Get user ID.
     *
     * @return int User ID
     */
    public function get_user_id() {
        return $this->user_id;
    }

    /**
     * Get session data.
     *
     * @param string $key Optional key to retrieve specific data
     * @return mixed Session data or specific value
     */
    public function get_session_data($key = null) {
        if ($key !== null) {
            return isset($this->session_data[$key]) ? $this->session_data[$key] : null;
        }
        return $this->session_data;
    }

    /**
     * Set session data.
     *
     * @param string|array $key Key or array of key-value pairs
     * @param mixed $value Value if key is string
     * @return bool Success status
     */
    public function set_session_data($key, $value = null) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->session_data[$k] = $v;
            }
        } else {
            $this->session_data[$key] = $value;
        }
        
        $this->session_data['last_activity'] = current_time('timestamp');
        return $this->save_session_data();
    }

    /**
     * Get cart data.
     *
     * @return array Cart data
     */
    public function get_cart_data() {
        return isset($this->session_data['cart']) ? $this->session_data['cart'] : array();
    }

    /**
     * Set cart data.
     *
     * @param array $cart_data Cart data
     * @return bool Success status
     */
    public function set_cart_data($cart_data) {
        $this->session_data['cart'] = $cart_data;
        $this->session_data['last_activity'] = current_time('timestamp');
        return $this->save_session_data();
    }

    /**
     * Get customer ID.
     *
     * @return int Customer ID
     */
    public function get_customer_id() {
        return isset($this->session_data['customer_id']) ? (int) $this->session_data['customer_id'] : 0;
    }

    /**
     * Set customer ID.
     *
     * @param int $customer_id Customer ID
     * @return bool Success status
     */
    public function set_customer_id($customer_id) {
        $this->session_data['customer_id'] = (int) $customer_id;
        $this->session_data['last_activity'] = current_time('timestamp');
        return $this->save_session_data();
    }

    /**
     * Get session meta data.
     *
     * @param string $key Meta key
     * @param mixed $default Default value
     * @return mixed Meta value
     */
    public function get_meta($key, $default = null) {
        $meta = isset($this->session_data['meta']) ? $this->session_data['meta'] : array();
        return isset($meta[$key]) ? $meta[$key] : $default;
    }

    /**
     * Set session meta data.
     *
     * @param string $key Meta key
     * @param mixed $value Meta value
     * @return bool Success status
     */
    public function set_meta($key, $value) {
        if (!isset($this->session_data['meta'])) {
            $this->session_data['meta'] = array();
        }
        
        $this->session_data['meta'][$key] = $value;
        $this->session_data['last_activity'] = current_time('timestamp');
        return $this->save_session_data();
    }

    /**
     * Extend session timeout.
     *
     * @param int $additional_time Additional time in seconds
     * @return bool Success status
     */
    public function extend_session($additional_time = null) {
        $additional_time = $additional_time ?: self::EXTENSION_TIME;
        
        // Update session data
        $this->session_data['last_activity'] = current_time('timestamp');
        
        // Extend transient
        $session_key = self::SESSION_PREFIX . $this->session_id;
        $current_timeout = $this->get_session_remaining_time();
        $new_timeout = $current_timeout + $additional_time;
        
        // Limit maximum session time to 24 hours
        $max_timeout = 24 * HOUR_IN_SECONDS;
        if ($new_timeout > $max_timeout) {
            $new_timeout = $max_timeout;
        }
        
        return set_transient($session_key, $this->session_data, $new_timeout);
    }

    /**
     * Get remaining session time.
     *
     * @return int Remaining time in seconds
     */
    public function get_session_remaining_time() {
        $session_key = self::SESSION_PREFIX . $this->session_id;
        
        // Get timeout from WordPress transient API
        $timeout = get_option('_transient_timeout_' . $session_key);
        
        if (!$timeout) {
            return 0;
        }
        
        $remaining = $timeout - current_time('timestamp');
        return max(0, $remaining);
    }

    /**
     * Check if session is valid and active.
     *
     * @return bool True if session is valid
     */
    public function is_session_valid() {
        if (empty($this->session_id)) {
            return false;
        }
        
        $remaining_time = $this->get_session_remaining_time();
        return $remaining_time > 0;
    }

    /**
     * Destroy current session.
     *
     * @return bool Success status
     */
    public function destroy_session() {
        $session_key = self::SESSION_PREFIX . $this->session_id;
        
        // Delete transient
        delete_transient($session_key);
        
        // Clear session cookie
        $cookie_name = 'wupos_session_' . md5($this->terminal_id . $this->user_id);
        setcookie($cookie_name, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
        
        // Clear session data
        $this->session_data = array();
        $this->session_id = '';
        
        return true;
    }

    /**
     * Get all active sessions for a terminal.
     *
     * @param string $terminal_id Terminal ID
     * @return array Active sessions
     */
    public static function get_terminal_sessions($terminal_id) {
        global $wpdb;
        
        $terminal_id = sanitize_text_field($terminal_id);
        $session_prefix = self::SESSION_PREFIX;
        
        // Query transients for sessions
        $sql = $wpdb->prepare(
            "SELECT option_name, option_value 
             FROM {$wpdb->options} 
             WHERE option_name LIKE %s 
             AND option_name NOT LIKE %s",
            '_transient_' . $session_prefix . '%',
            '_transient_timeout_%'
        );
        
        $results = $wpdb->get_results($sql);
        $sessions = array();
        
        foreach ($results as $result) {
            $session_data = maybe_unserialize($result->option_value);
            
            if (is_array($session_data) && 
                isset($session_data['terminal_id']) && 
                $session_data['terminal_id'] === $terminal_id) {
                
                $session_id = str_replace('_transient_' . $session_prefix, '', $result->option_name);
                $sessions[$session_id] = $session_data;
            }
        }
        
        return $sessions;
    }

    /**
     * Cleanup expired sessions.
     */
    public function cleanup_expired_sessions() {
        global $wpdb;
        
        // Delete expired session transients
        $current_time = current_time('timestamp');
        
        $sql = $wpdb->prepare(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE %s 
             AND option_value < %d",
            '_transient_timeout_' . self::SESSION_PREFIX . '%',
            $current_time
        );
        
        $deleted = $wpdb->query($sql);
        
        if ($deleted > 0) {
            wupos_log(sprintf('Cleaned up %d expired POS sessions', $deleted), 'info');
        }
        
        // Also clean up orphaned session data
        $sql = $wpdb->prepare(
            "DELETE o1 FROM {$wpdb->options} o1
             LEFT JOIN {$wpdb->options} o2 ON o1.option_name = REPLACE(o2.option_name, '_transient_timeout_', '_transient_')
             WHERE o1.option_name LIKE %s
             AND o2.option_name IS NULL",
            '_transient_' . self::SESSION_PREFIX . '%'
        );
        
        $orphaned = $wpdb->query($sql);
        
        if ($orphaned > 0) {
            wupos_log(sprintf('Cleaned up %d orphaned POS session data', $orphaned), 'info');
        }
    }

    /**
     * Get session statistics.
     *
     * @return array Session statistics
     */
    public static function get_session_stats() {
        global $wpdb;
        
        $session_prefix = self::SESSION_PREFIX;
        $current_time = current_time('timestamp');
        
        // Count active sessions
        $active_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) 
             FROM {$wpdb->options} 
             WHERE option_name LIKE %s 
             AND option_value > %d",
            '_transient_timeout_' . $session_prefix . '%',
            $current_time
        ));
        
        // Get total sessions
        $total_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) 
             FROM {$wpdb->options} 
             WHERE option_name LIKE %s",
            '_transient_' . $session_prefix . '%'
        ));
        
        return array(
            'active_sessions' => (int) $active_count,
            'total_sessions' => (int) $total_count,
            'expired_sessions' => (int) $total_count - (int) $active_count,
            'timestamp' => $current_time
        );
    }

    /**
     * Sanitize terminal ID.
     *
     * @param string $terminal_id Raw terminal ID
     * @return string Sanitized terminal ID
     */
    private function sanitize_terminal_id($terminal_id) {
        if (empty($terminal_id)) {
            // Generate default terminal ID based on user and browser
            $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');
            $ip_address = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
            $terminal_id = 'terminal_' . md5($this->user_id . $user_agent . $ip_address);
        }
        
        return sanitize_text_field($terminal_id);
    }

    /**
     * Create session for API usage.
     *
     * @param string $terminal_id Terminal ID
     * @param int $user_id User ID
     * @return array Session info
     */
    public static function create_api_session($terminal_id, $user_id = null) {
        $user_id = $user_id ?: get_current_user_id();
        
        if (!$user_id) {
            return new WP_Error('invalid_user', 'Valid user required for session creation');
        }
        
        $handler = new self($terminal_id);
        
        return array(
            'session_id' => $handler->get_session_id(),
            'terminal_id' => $handler->get_terminal_id(),
            'user_id' => $handler->get_user_id(),
            'expires_in' => $handler->get_session_remaining_time(),
            'created_at' => current_time('timestamp')
        );
    }

    /**
     * Validate API session.
     *
     * @param string $session_id Session ID
     * @param string $terminal_id Terminal ID
     * @return bool|WP_Error True if valid, WP_Error if invalid
     */
    public static function validate_api_session($session_id, $terminal_id) {
        if (empty($session_id) || empty($terminal_id)) {
            return new WP_Error('missing_session_data', 'Session ID and Terminal ID are required');
        }
        
        $session_key = self::SESSION_PREFIX . $session_id;
        $session_data = get_transient($session_key);
        
        if (!$session_data) {
            return new WP_Error('invalid_session', 'Session not found or expired');
        }
        
        if ($session_data['terminal_id'] !== $terminal_id) {
            return new WP_Error('terminal_mismatch', 'Session does not belong to this terminal');
        }
        
        // Update last activity
        $session_data['last_activity'] = current_time('timestamp');
        set_transient($session_key, $session_data, self::DEFAULT_TIMEOUT);
        
        return true;
    }
}