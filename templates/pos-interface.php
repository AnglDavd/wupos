<?php
/**
 * POS Interface Template
 *
 * This template will contain the POS interface according to the wireframe.
 *
 * @package WUPOS
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="<?php echo esc_attr($atts['class']); ?>" id="wupos-pos-system">
    <!-- Loading Screen -->
    <div class="wupos-loading" id="wupos-loading">
        <div class="loading-content">
            <div class="wupos-logo">
                <h2><?php _e('WUPOS', 'wupos'); ?></h2>
                <p><?php _e('WooCommerce Ultimate Point of Sale', 'wupos'); ?></p>
            </div>
            <div class="loading-spinner">
                <div class="spinner"></div>
            </div>
            <p class="loading-text"><?php _e('Initializing POS System...', 'wupos'); ?></p>
        </div>
    </div>
    
    <!-- Main POS Interface -->
    <div class="wupos-pos-interface" id="wupos-pos-interface" style="display: none;">
        <!-- Top Header -->
        <header class="wupos-header">
            <div class="header-left">
                <h1 class="pos-title"><?php _e('Point of Sale', 'wupos'); ?></h1>
                <span class="store-info"><?php echo get_bloginfo('name'); ?></span>
            </div>
            <div class="header-center">
                <div class="current-time" id="wupos-current-time"></div>
            </div>
            <div class="header-right">
                <div class="connection-status">
                    <span class="status-indicator online" id="wupos-status">
                        <span class="status-dot"></span>
                        <?php _e('Online', 'wupos'); ?>
                    </span>
                </div>
                <div class="user-info">
                    <?php $current_user = wp_get_current_user(); ?>
                    <span class="cashier-name"><?php echo esc_html($current_user->display_name); ?></span>
                </div>
            </div>
        </header>
        
        <!-- Main Content Area -->
        <main class="wupos-main-content">
            <!-- Left Panel - Products -->
            <section class="wupos-left-panel">
                <!-- Search Bar -->
                <div class="product-search-container">
                    <div class="search-input-wrapper">
                        <input type="text" 
                               id="wupos-product-search" 
                               class="product-search-input" 
                               placeholder="<?php _e('Search products, scan barcode...', 'wupos'); ?>"
                               autocomplete="off">
                        <button class="search-clear-btn" id="wupos-search-clear" style="display: none;">
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    </div>
                    <button class="barcode-scan-btn" id="wupos-barcode-scan">
                        <span class="dashicons dashicons-camera"></span>
                        <?php _e('Scan', 'wupos'); ?>
                    </button>
                </div>
                
                <!-- Category Filter -->
                <div class="category-filter" id="wupos-category-filter">
                    <button class="category-btn active" data-category="all">
                        <?php _e('All', 'wupos'); ?>
                    </button>
                    <!-- Categories will be loaded dynamically -->
                </div>
                
                <!-- Products Grid -->
                <div class="products-container">
                    <div class="product-grid" id="wupos-product-grid">
                        <!-- Products will be loaded here -->
                        <div class="wupos-placeholder">
                            <div class="placeholder-icon">
                                <span class="dashicons dashicons-products"></span>
                            </div>
                            <h3><?php _e('Ready to Start Selling!', 'wupos'); ?></h3>
                            <p><?php _e('The product interface will be implemented according to the wireframe design.', 'wupos'); ?></p>
                            <p class="placeholder-note"><?php _e('Search for products or scan barcodes to begin.', 'wupos'); ?></p>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Right Panel - Cart & Checkout -->
            <aside class="wupos-right-panel">
                <!-- Cart Header -->
                <div class="cart-header">
                    <h2><?php _e('Current Sale', 'wupos'); ?></h2>
                    <button class="cart-clear-btn" id="wupos-cart-clear" disabled>
                        <span class="dashicons dashicons-trash"></span>
                        <?php _e('Clear', 'wupos'); ?>
                    </button>
                </div>
                
                <!-- Cart Items -->
                <div class="cart-container">
                    <div class="cart-items" id="wupos-cart-items">
                        <!-- Cart items will be loaded here -->
                        <div class="cart-empty" id="wupos-cart-empty">
                            <div class="empty-cart-icon">
                                <span class="dashicons dashicons-cart"></span>
                            </div>
                            <p><?php _e('Cart is empty', 'wupos'); ?></p>
                            <p class="empty-cart-help"><?php _e('Add products to start a new sale', 'wupos'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Cart Summary -->
                <div class="cart-summary">
                    <div class="summary-lines">
                        <div class="summary-line subtotal">
                            <span class="label"><?php _e('Subtotal:', 'wupos'); ?></span>
                            <span class="amount" id="wupos-subtotal">$0.00</span>
                        </div>
                        <div class="summary-line tax">
                            <span class="label"><?php _e('Tax:', 'wupos'); ?></span>
                            <span class="amount" id="wupos-tax">$0.00</span>
                        </div>
                        <div class="summary-line total">
                            <span class="label"><?php _e('Total:', 'wupos'); ?></span>
                            <span class="amount" id="wupos-total">$0.00</span>
                        </div>
                    </div>
                </div>
                
                <!-- Checkout Actions -->
                <div class="checkout-actions">
                    <button class="checkout-btn primary" id="wupos-checkout" disabled>
                        <span class="btn-icon dashicons dashicons-money-alt"></span>
                        <?php _e('Process Payment', 'wupos'); ?>
                    </button>
                    <div class="secondary-actions">
                        <button class="action-btn" id="wupos-hold-sale" disabled>
                            <span class="dashicons dashicons-clock"></span>
                            <?php _e('Hold', 'wupos'); ?>
                        </button>
                        <button class="action-btn" id="wupos-add-discount" disabled>
                            <span class="dashicons dashicons-tag"></span>
                            <?php _e('Discount', 'wupos'); ?>
                        </button>
                    </div>
                </div>
            </aside>
        </main>
        
        <!-- Footer -->
        <footer class="wupos-footer">
            <div class="footer-info">
                <span class="version-info">WUPOS v<?php echo WUPOS_VERSION; ?></span>
                <span class="separator">|</span>
                <span class="support-info">
                    <a href="#" id="wupos-help"><?php _e('Help & Support', 'wupos'); ?></a>
                </span>
            </div>
        </footer>
    </div>
</div>

<!-- Hidden elements for dialogs/modals -->
<div id="wupos-modals-container"></div>