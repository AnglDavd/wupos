<?php
/**
 * WooTPV POS Page Template
 * Main POS interface with 3-column layout optimized for desktop terminals
 *
 * @package WooTPV
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Ensure user has POS capabilities
if (!wupos_user_can_pos()) {
    wp_die(__('Access denied. You do not have permission to access the POS system.', 'wupos'));
}

// Get current user info for header
$current_user = wp_get_current_user();
$session_id = wupos_get_session_id();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title><?php _e('WooTPV Point of Sale', 'wupos'); ?> - <?php bloginfo('name'); ?></title>
    
    <!-- Bootstrap 5.3 CDN for rapid development -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Feather Icons for UI consistency -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    <?php
    // Enqueue POS styles
    wp_enqueue_style('wupos-pos-styles');
    
    // Enqueue WordPress and jQuery
    wp_head();
    ?>
    
    <style>
        /* Critical CSS for immediate render */
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8fafc;
        }
        
        .wootpv-pos-layout {
            display: grid;
            height: 100vh;
            grid-template-rows: 65px 1fr;
            grid-template-columns: 100px 1fr 320px;
            grid-template-areas: 
                "header header header"
                "sidebar products cart";
        }
        
        @media (min-width: 1600px) {
            .wootpv-pos-layout {
                grid-template-columns: 120px 1fr 320px;
            }
        }
        
        @media (max-width: 1199px) {
            .wootpv-pos-layout {
                grid-template-columns: 80px 1fr 280px;
            }
        }
    </style>
</head>

<body class="wootpv-pos-body">
    
    <!-- Main POS Layout Container -->
    <div class="wootpv-pos-layout">
        
        <!-- Header Bar -->
        <header class="wootpv-header" style="grid-area: header;">
            <div class="container-fluid h-100">
                <div class="row align-items-center h-100 px-3">
                    
                    <!-- Logo and Brand -->
                    <div class="col-2">
                        <div class="d-flex align-items-center">
                            <?php if (has_custom_logo()) : ?>
                                <div class="pos-logo">
                                    <?php the_custom_logo(); ?>
                                </div>
                            <?php else : ?>
                                <h4 class="mb-0 text-primary fw-semibold">WooTPV</h4>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Main Search Bar -->
                    <div class="col-6">
                        <div class="position-relative">
                            <input 
                                type="text" 
                                id="product-search" 
                                class="form-control form-control-lg"
                                placeholder="<?php _e('Search products, SKU or barcode...', 'wupos'); ?>"
                                autocomplete="off"
                                aria-label="<?php _e('Search products', 'wupos'); ?>"
                            >
                            <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">
                                <i data-feather="search" class="feather-16"></i>
                            </span>
                            
                            <!-- Search Results Dropdown -->
                            <div id="search-results" class="position-absolute w-100 bg-white border border-top-0 rounded-bottom shadow-sm" style="top: 100%; display: none; z-index: 1000; max-height: 300px; overflow-y: auto;">
                                <!-- Dynamic search results will be populated here -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Info and Actions -->
                    <div class="col-4">
                        <div class="d-flex align-items-center justify-content-end gap-3">
                            
                            <!-- Current Session Info -->
                            <div class="text-end">
                                <div class="text-sm fw-medium text-dark">
                                    <?php echo esc_html($current_user->display_name); ?>
                                </div>
                                <small class="text-muted">
                                    <?php _e('Session:', 'wupos'); ?> <?php echo substr($session_id, 0, 8); ?>
                                </small>
                            </div>
                            
                            <!-- Settings Button (Supervisor+) -->
                            <?php if (wupos_user_can_manage()) : ?>
                            <button 
                                type="button" 
                                class="btn btn-outline-secondary" 
                                id="settings-btn"
                                data-bs-toggle="modal" 
                                data-bs-target="#settings-modal"
                                title="<?php _e('POS Settings', 'wupos'); ?>"
                            >
                                <i data-feather="settings" class="feather-16"></i>
                            </button>
                            <?php endif; ?>
                            
                            <!-- Help Button -->
                            <button 
                                type="button" 
                                class="btn btn-outline-info" 
                                id="help-btn"
                                title="<?php _e('Keyboard Shortcuts (F1)', 'wupos'); ?>"
                            >
                                <i data-feather="help-circle" class="feather-16"></i>
                            </button>
                            
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Category Sidebar -->
        <aside class="wootpv-sidebar" style="grid-area: sidebar;">
            <div class="h-100 bg-white border-end">
                
                <!-- Categories Header -->
                <div class="p-3 border-bottom">
                    <h6 class="mb-0 text-uppercase text-muted fw-semibold">
                        <?php _e('Categories', 'wupos'); ?>
                    </h6>
                </div>
                
                <!-- Category List -->
                <div id="category-list" class="flex-grow-1" style="overflow-y: auto;">
                    <div class="list-group list-group-flush">
                        
                        <!-- All Products (Default) -->
                        <button 
                            type="button" 
                            class="list-group-item list-group-item-action active category-filter" 
                            data-category="0"
                            aria-pressed="true"
                        >
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-medium"><?php _e('All', 'wupos'); ?></span>
                                <span class="badge bg-primary rounded-pill" id="total-products-count">0</span>
                            </div>
                        </button>
                        
                        <!-- Dynamic Categories Will Be Loaded Here -->
                        <div id="dynamic-categories">
                            <!-- Categories populated via JavaScript -->
                        </div>
                        
                    </div>
                </div>
                
                <!-- Category Actions -->
                <div class="p-2 border-top">
                    <button 
                        type="button" 
                        class="btn btn-sm btn-outline-secondary w-100" 
                        id="refresh-categories"
                        title="<?php _e('Refresh Categories', 'wupos'); ?>"
                    >
                        <i data-feather="refresh-cw" class="feather-14 me-1"></i>
                        <?php _e('Refresh', 'wupos'); ?>
                    </button>
                </div>
                
            </div>
        </aside>

        <!-- Products Grid Area -->
        <main class="wootpv-products-area" style="grid-area: products;">
            <div class="h-100 bg-white">
                
                <!-- Products Toolbar -->
                <div class="products-toolbar d-flex justify-content-between align-items-center p-3 border-bottom">
                    
                    <!-- View Controls -->
                    <div class="btn-group" role="group" aria-label="<?php _e('View Options', 'wupos'); ?>">
                        <button 
                            type="button" 
                            class="btn btn-outline-secondary active" 
                            id="grid-view-btn"
                            title="<?php _e('Grid View', 'wupos'); ?>"
                        >
                            <i data-feather="grid" class="feather-16"></i>
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-outline-secondary" 
                            id="list-view-btn"
                            title="<?php _e('List View', 'wupos'); ?>"
                        >
                            <i data-feather="list" class="feather-16"></i>
                        </button>
                    </div>
                    
                    <!-- Products Info -->
                    <div class="products-info">
                        <span class="text-muted">
                            <?php _e('Showing', 'wupos'); ?> 
                            <span id="products-showing">0</span> 
                            <?php _e('of', 'wupos'); ?> 
                            <span id="products-total">0</span>
                        </span>
                    </div>
                    
                    <!-- Load More / Pagination -->
                    <div class="products-actions">
                        <button 
                            type="button" 
                            class="btn btn-primary" 
                            id="load-more-btn"
                            style="display: none;"
                        >
                            <i data-feather="plus-circle" class="feather-16 me-1"></i>
                            <?php _e('Load More', 'wupos'); ?>
                        </button>
                        
                        <!-- Loading Indicator -->
                        <div id="products-loading" class="d-none">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden"><?php _e('Loading...', 'wupos'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Products Grid Container -->
                <div class="products-container flex-grow-1" style="overflow-y: auto; height: calc(100vh - 65px - 60px);">
                    
                    <!-- Products Grid -->
                    <div id="products-grid" class="products-grid p-3">
                        <!-- Products will be dynamically loaded here -->
                        <div class="d-flex justify-content-center align-items-center" style="min-height: 300px;" id="initial-loading">
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden"><?php _e('Loading products...', 'wupos'); ?></span>
                                </div>
                                <p class="text-muted"><?php _e('Loading products...', 'wupos'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Empty State -->
                    <div id="empty-products-state" class="d-none text-center py-5">
                        <div class="text-muted mb-3">
                            <i data-feather="package" style="width: 64px; height: 64px;"></i>
                        </div>
                        <h5 class="text-muted"><?php _e('No products found', 'wupos'); ?></h5>
                        <p class="text-muted"><?php _e('Try adjusting your search or category filter.', 'wupos'); ?></p>
                        <button type="button" class="btn btn-outline-primary" id="clear-filters-btn">
                            <?php _e('Clear Filters', 'wupos'); ?>
                        </button>
                    </div>
                    
                    <!-- Error State -->
                    <div id="error-products-state" class="d-none text-center py-5">
                        <div class="text-danger mb-3">
                            <i data-feather="alert-triangle" style="width: 64px; height: 64px;"></i>
                        </div>
                        <h5 class="text-danger"><?php _e('Error loading products', 'wupos'); ?></h5>
                        <p class="text-muted"><?php _e('There was a problem loading the product catalog.', 'wupos'); ?></p>
                        <button type="button" class="btn btn-outline-danger" id="retry-products-btn">
                            <i data-feather="refresh-cw" class="feather-16 me-1"></i>
                            <?php _e('Retry', 'wupos'); ?>
                        </button>
                    </div>
                    
                </div>
                
            </div>
        </main>

        <!-- Shopping Cart Sidebar -->
        <aside class="wootpv-cart" style="grid-area: cart;">
            <div class="h-100 bg-white border-start d-flex flex-column">
                
                <!-- Cart Header -->
                <div class="cart-header p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?php _e('Shopping Cart', 'wupos'); ?></h5>
                        <button 
                            type="button" 
                            class="btn btn-sm btn-outline-danger" 
                            id="clear-cart-btn"
                            title="<?php _e('Clear Cart', 'wupos'); ?>"
                        >
                            <i data-feather="trash-2" class="feather-14"></i>
                        </button>
                    </div>
                    <small class="text-muted">
                        <span id="cart-items-count">0</span> <?php _e('items', 'wupos'); ?>
                    </small>
                </div>
                
                <!-- Cart Items -->
                <div class="cart-items flex-grow-1 p-3" 
                     style="overflow-y: auto;"
                     role="region"
                     aria-label="<?php _e('Shopping cart items', 'wupos'); ?>">
                    <!-- Empty Cart State -->
                    <div id="empty-cart-state" class="text-center py-5" role="status" aria-live="polite">
                        <div class="text-muted mb-3">
                            <i data-feather="shopping-cart" style="width: 48px; height: 48px;" aria-hidden="true"></i>
                        </div>
                        <p class="text-muted mb-2"><?php _e('Your cart is empty', 'wupos'); ?></p>
                        <small class="text-muted"><?php _e('Add products to start a sale', 'wupos'); ?></small>
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('product-search').focus()">
                                <i data-feather="search" class="feather-14 me-1" aria-hidden="true"></i>
                                <?php _e('Search Products', 'wupos'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Dynamic Cart Items -->
                    <div id="cart-items-list" role="list" aria-label="<?php _e('Cart items', 'wupos'); ?>">
                        <!-- Cart items will be populated dynamically -->
                    </div>
                </div>
                
                <!-- Cart Totals -->
                <div class="cart-totals p-3 border-top bg-light" 
                     role="region" 
                     aria-label="<?php _e('Order summary', 'wupos'); ?>">
                    
                    <!-- Customer Selection -->
                    <div class="customer-section mb-3">
                        <label for="customer-search" class="form-label small text-muted">
                            <?php _e('Customer', 'wupos'); ?>
                        </label>
                        <div class="position-relative">
                            <input 
                                type="text" 
                                id="customer-search" 
                                class="form-control form-control-sm wootpv-focus-ring"
                                placeholder="<?php _e('Search customer...', 'wupos'); ?>"
                                autocomplete="off"
                                role="combobox"
                                aria-expanded="false"
                                aria-haspopup="listbox"
                                aria-label="<?php _e('Search and select customer', 'wupos'); ?>"
                            >
                            <div id="customer-results" 
                                 class="position-absolute w-100 bg-white border border-top-0 rounded-bottom shadow-sm" 
                                 style="top: 100%; display: none; z-index: 1000; max-height: 200px; overflow-y: auto;"
                                 role="listbox"
                                 aria-label="<?php _e('Customer search results', 'wupos'); ?>">
                                <!-- Customer search results -->
                            </div>
                        </div>
                        <div id="selected-customer" 
                             class="mt-2 p-2 selected-customer rounded small" 
                             style="display: none;"
                             role="status"
                             aria-live="polite">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="customer-name fw-medium"></span>
                                <button type="button" 
                                        class="btn-close btn-close-sm wootpv-touch-target" 
                                        id="clear-customer-btn"
                                        aria-label="<?php _e('Remove selected customer', 'wupos'); ?>"
                                        title="<?php _e('Remove selected customer', 'wupos'); ?>"></button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Price Breakdown -->
                    <div class="totals-breakdown" role="region" aria-label="<?php _e('Price breakdown', 'wupos'); ?>">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small"><?php _e('Subtotal:', 'wupos'); ?></span>
                            <span class="subtotal-amount" aria-label="<?php _e('Subtotal amount', 'wupos'); ?>">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small"><?php _e('Tax:', 'wupos'); ?></span>
                            <span class="tax-amount" aria-label="<?php _e('Tax amount', 'wupos'); ?>">$0.00</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-semibold"><?php _e('Total:', 'wupos'); ?></span>
                            <span class="total-amount fw-bold text-primary fs-5" 
                                  aria-label="<?php _e('Total amount', 'wupos'); ?>"
                                  role="status"
                                  aria-live="polite">$0.00</span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="cart-actions d-grid gap-2">
                        <button 
                            type="button" 
                            class="btn btn-success btn-lg wootpv-touch-target" 
                            id="checkout-btn"
                            disabled
                            aria-describedby="checkout-help"
                        >
                            <i data-feather="credit-card" class="feather-16 me-2" aria-hidden="true"></i>
                            <?php _e('Checkout', 'wupos'); ?>
                        </button>
                        <div id="checkout-help" class="wootpv-sr-only">
                            <?php _e('Proceed to checkout with current cart items. Keyboard shortcut: F9', 'wupos'); ?>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <button 
                                    type="button" 
                                    class="btn btn-outline-warning btn-sm w-100 wootpv-touch-target" 
                                    id="hold-sale-btn"
                                    disabled
                                    aria-label="<?php _e('Hold current sale for later', 'wupos'); ?>"
                                >
                                    <i data-feather="pause-circle" class="feather-14 me-1" aria-hidden="true"></i>
                                    <?php _e('Hold', 'wupos'); ?>
                                </button>
                            </div>
                            <div class="col-6">
                                <button 
                                    type="button" 
                                    class="btn btn-outline-info btn-sm w-100 wootpv-touch-target" 
                                    id="add-discount-btn"
                                    disabled
                                    aria-label="<?php _e('Apply discount to cart', 'wupos'); ?>"
                                >
                                    <i data-feather="percent" class="feather-14 me-1" aria-hidden="true"></i>
                                    <?php _e('Discount', 'wupos'); ?>
                                </button>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
            </div>
        </aside>

    </div>

    <!-- Screen Reader Announcements Area -->
    <div id="screen-reader-announcements" class="wootpv-sr-only" aria-live="polite" aria-atomic="true"></div>
    
    <!-- Mobile Cart Toggle (hidden on desktop) -->
    <button type="button" 
            class="btn btn-primary cart-toggle-mobile position-fixed d-block d-md-none" 
            style="bottom: 20px; right: 20px; z-index: 999; border-radius: 50%; width: 60px; height: 60px;"
            aria-label="<?php _e('Toggle shopping cart', 'wupos'); ?>">
        <i data-feather="shopping-cart" class="feather-20"></i>
        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle" 
              id="mobile-cart-badge" 
              style="display: none;">0</span>
    </button>

    <!-- Include WordPress Footer (for admin bar and other hooks) -->
    <?php wp_footer(); ?>
    
    <!-- Bootstrap 5.3 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Initialize Feather Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather icons
            feather.replace();
            
            // Set focus on search input for immediate use
            document.getElementById('product-search').focus();
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // F1 - Help
            if (e.key === 'F1') {
                e.preventDefault();
                // Show help modal (to be implemented)
                console.log('Show help modal');
            }
            
            // F5 - Refresh
            if (e.key === 'F5') {
                e.preventDefault();
                if (window.WUPOS) {
                    window.WUPOS.loadProducts();
                }
            }
            
            // Escape - Clear search/close modals
            if (e.key === 'Escape') {
                document.getElementById('product-search').value = '';
                document.getElementById('search-results').style.display = 'none';
            }
        });
    </script>

</body>
</html>