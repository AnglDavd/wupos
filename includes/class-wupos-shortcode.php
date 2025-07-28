<?php
/**
 * WUPOS Shortcode Class
 *
 * @package WUPOS
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS_Shortcode class.
 */
class WUPOS_Shortcode {
    
    /**
     * Constructor.
     */
    public function __construct() {
        add_shortcode('wupos_pos', array($this, 'pos_shortcode'));
    }
    
    /**
     * POS shortcode callback.
     *
     * @param array $atts Shortcode attributes.
     * @return string Shortcode output.
     */
    public function pos_shortcode($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'class' => 'wupos-pos-container'
        ), $atts, 'wupos_pos');
        
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return '<div class="wupos-error">' . __('WooCommerce is required for WUPOS to work properly.', 'wupos') . '</div>';
        }
        
        // Start output buffering
        ob_start();
        
        // Load the POS template
        $this->load_pos_template($atts);
        
        return ob_get_clean();
    }
    
    /**
     * Load POS template.
     *
     * @param array $atts Shortcode attributes.
     */
    private function load_pos_template($atts) {
        $template_path = WUPOS_PLUGIN_PATH . 'templates/pos-interface.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            $this->default_pos_output($atts);
        }
    }
    
    /**
     * Default POS output when template doesn't exist.
     *
     * @param array $atts Shortcode attributes.
     */
    private function default_pos_output($atts) {
        ?>
        <div class="<?php echo esc_attr($atts['class']); ?>" id="wupos-pos-system">
            <div class="wupos-loading">
                <h2><?php _e('WUPOS - Point of Sale System', 'wupos'); ?></h2>
                <p><?php _e('Loading POS interface...', 'wupos'); ?></p>
                <div class="wupos-spinner"></div>
            </div>
            
            <!-- POS Interface will be loaded here -->
            <div class="wupos-pos-interface" style="display: none;">
                <div class="wupos-header">
                    <h1><?php _e('Point of Sale', 'wupos'); ?></h1>
                    <div class="wupos-status">
                        <span class="status-indicator online"><?php _e('Online', 'wupos'); ?></span>
                    </div>
                </div>
                
                <div class="wupos-content">
                    <div class="wupos-left-panel">
                        <div class="product-search">
                            <input type="text" placeholder="<?php _e('Search products...', 'wupos'); ?>" id="wupos-product-search">
                        </div>
                        <div class="product-grid" id="wupos-product-grid">
                            <!-- Products will be loaded here -->
                            <div class="wupos-placeholder">
                                <p><?php _e('Ready to start selling! The interface will be implemented according to the wireframe.', 'wupos'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="wupos-right-panel">
                        <div class="cart-section">
                            <h3><?php _e('Cart', 'wupos'); ?></h3>
                            <div class="cart-items" id="wupos-cart-items">
                                <!-- Cart items will be loaded here -->
                                <div class="cart-empty">
                                    <p><?php _e('Cart is empty', 'wupos'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="checkout-section">
                            <div class="total-section">
                                <div class="total-line">
                                    <span><?php _e('Total:', 'wupos'); ?></span>
                                    <span class="total-amount">$0.00</span>
                                </div>
                            </div>
                            <button class="checkout-btn" disabled><?php _e('Process Payment', 'wupos'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}