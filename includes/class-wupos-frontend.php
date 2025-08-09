<?php
/**
 * WUPOS Frontend
 *
 * @package WUPOS\Frontend
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS_Frontend class.
 */
class WUPOS_Frontend {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        add_action('init', array($this, 'init'));
        add_shortcode('wupos', array($this, 'pos_shortcode'));
    }

    /**
     * Initialize frontend functionality.
     */
    public function init() {
        // Add custom query vars
        add_filter('query_vars', array($this, 'add_query_vars'));
        
        // Add rewrite rules
        add_action('init', array($this, 'add_rewrite_rules'));
        
        // Handle POS page template
        add_filter('template_include', array($this, 'pos_template'));
    }

    /**
     * Add custom query vars.
     */
    public function add_query_vars($vars) {
        $vars[] = 'wupos_page';
        $vars[] = 'wupos_action';
        return $vars;
    }

    /**
     * Add rewrite rules for POS.
     */
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^pos/?([^/]*)/?([^/]*)',
            'index.php?wupos_page=$matches[1]&wupos_action=$matches[2]',
            'top'
        );
    }

    /**
     * Handle POS page template.
     */
    public function pos_template($template) {
        if (get_query_var('wupos_page')) {
            if (!wupos_user_can_pos()) {
                wp_die(__('You do not have permission to access the POS.', 'wupos'));
            }

            $pos_template = wupos_get_template_path('pos-page.php');
            if (file_exists($pos_template)) {
                return $pos_template;
            }
        }

        return $template;
    }

    /**
     * Enqueue frontend scripts and styles.
     */
    public function frontend_scripts() {
        // Only load on POS pages
        if (!$this->is_pos_page()) {
            return;
        }

        // Bootstrap CSS (CDN)
        wp_enqueue_style(
            'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            array(),
            '5.3.0'
        );

        // Feather Icons
        wp_enqueue_style(
            'feather-icons',
            'https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/dist/feather.css',
            array(),
            '4.29.0'
        );

        // WUPOS POS styles
        wp_enqueue_style(
            'wupos-pos-styles',
            WUPOS_PLUGIN_URL . 'assets/css/dist/pos.css',
            array('bootstrap'),
            WUPOS_VERSION
        );

        // jQuery (WordPress bundled)
        wp_enqueue_script('jquery');

        // Bootstrap JS (CDN)
        wp_enqueue_script(
            'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            array('jquery'),
            '5.3.0',
            true
        );

        // Feather Icons
        wp_enqueue_script(
            'feather-icons',
            'https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/dist/feather.min.js',
            array(),
            '4.29.0',
            true
        );

        // WUPOS POS scripts
        wp_enqueue_script(
            'wupos-pos',
            WUPOS_PLUGIN_URL . 'assets/js/dist/pos.js',
            array('jquery', 'bootstrap', 'feather-icons'),
            WUPOS_VERSION,
            true
        );

        // Localize script
        wp_localize_script('wupos-pos', 'wupos_pos', array(
            'ajax_url'       => admin_url('admin-ajax.php'),
            'rest_url'       => rest_url('wupos/v1/'),
            'nonce'          => wp_create_nonce('wupos_ajax'),
            'rest_nonce'     => wp_create_nonce('wp_rest'),
            'currency_symbol' => get_woocommerce_currency_symbol(),
            'currency_position' => get_option('woocommerce_currency_pos', 'left'),
            'tax_display'    => get_option('wupos_tax_display', 'excl'),
            'sound_enabled'  => get_option('wupos_sound_enabled', 'yes'),
            'session_id'     => wupos_get_session_id(),
            'strings'        => array(
                'add_to_cart'     => __('Add to Cart', 'wupos'),
                'remove_item'     => __('Remove Item', 'wupos'),
                'checkout'        => __('Checkout', 'wupos'),
                'payment_success' => __('Payment Successful', 'wupos'),
                'payment_failed'  => __('Payment Failed', 'wupos'),
                'loading'         => __('Loading...', 'wupos'),
                'error'           => __('Error occurred', 'wupos'),
                'confirm_remove'  => __('Remove this item from cart?', 'wupos'),
                'empty_cart'      => __('Cart is empty', 'wupos'),
                'search_products' => __('Search products...', 'wupos'),
                'search_customers' => __('Search customers...', 'wupos'),
                'total'           => __('Total', 'wupos'),
                'subtotal'        => __('Subtotal', 'wupos'),
                'tax'             => __('Tax', 'wupos'),
                'cash'            => __('Cash', 'wupos'),
                'card'            => __('Card', 'wupos'),
                'change'          => __('Change', 'wupos'),
                'receipt'         => __('Receipt', 'wupos'),
                'print'           => __('Print', 'wupos'),
            ),
        ));
    }

    /**
     * POS shortcode.
     */
    public function pos_shortcode($atts) {
        if (!wupos_user_can_pos()) {
            return '<p>' . __('You do not have permission to access the POS.', 'wupos') . '</p>';
        }

        if (!wupos_is_woocommerce_active()) {
            return '<p>' . __('WooCommerce is required for WUPOS to function.', 'wupos') . '</p>';
        }

        // Enqueue scripts for shortcode
        $this->frontend_scripts();

        ob_start();
        include wupos_get_template_path('pos-shortcode.php');
        return ob_get_clean();
    }

    /**
     * Check if current page is POS page.
     */
    private function is_pos_page() {
        // Check for query var
        if (get_query_var('wupos_page')) {
            return true;
        }

        // Check for shortcode
        global $post;
        if ($post && has_shortcode($post->post_content, 'wupos')) {
            return true;
        }

        // Check for admin POS page
        if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'wupos') {
            return true;
        }

        return false;
    }

    /**
     * Get current POS context.
     */
    public function get_pos_context() {
        return array(
            'user_id'    => get_current_user_id(),
            'session_id' => wupos_get_session_id(),
            'terminal_id' => $this->get_terminal_id(),
            'timestamp'  => current_time('timestamp'),
        );
    }

    /**
     * Get terminal ID.
     */
    private function get_terminal_id() {
        // For now, use user ID as terminal ID
        // In future versions, this could be more sophisticated
        return 'terminal_' . get_current_user_id();
    }
}

return new WUPOS_Frontend();