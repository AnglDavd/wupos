/**
 * WUPOS Frontend JavaScript
 * 
 * Main JavaScript file for the WUPOS Point of Sale interface.
 * Ready for wireframe implementation.
 *
 * @package WUPOS
 * @version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * WUPOS Main Class
     */
    var WUPOS = {
        
        /**
         * Initialize the POS system
         */
        init: function() {
            console.log('WUPOS: Initializing Point of Sale system...');
            
            this.bindEvents();
            this.initializeInterface();
            this.startClock();
            
            console.log('WUPOS: Initialization complete');
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Search functionality
            $(document).on('input', '#wupos-product-search', this.handleProductSearch);
            $(document).on('click', '#wupos-search-clear', this.clearSearch);
            
            // Barcode scanning
            $(document).on('click', '#wupos-barcode-scan', this.handleBarcodeScanning);
            
            // Category filtering
            $(document).on('click', '.category-btn', this.handleCategoryFilter);
            
            // Cart actions
            $(document).on('click', '#wupos-cart-clear', this.clearCart);
            $(document).on('click', '#wupos-checkout', this.processCheckout);
            $(document).on('click', '#wupos-hold-sale', this.holdSale);
            $(document).on('click', '#wupos-add-discount', this.addDiscount);
            
            // Help and support
            $(document).on('click', '#wupos-help', this.showHelp);
            
            console.log('WUPOS: Event handlers bound');
        },
        
        /**
         * Initialize the interface after loading
         */
        initializeInterface: function() {
            // Simulate loading time
            setTimeout(function() {
                $('#wupos-loading').fadeOut(300, function() {
                    $('#wupos-pos-interface').fadeIn(300);
                    console.log('WUPOS: Interface loaded successfully');
                });
            }, 1500);
        },
        
        /**
         * Start the clock in the header
         */
        startClock: function() {
            function updateTime() {
                var now = new Date();
                var timeString = now.toLocaleTimeString('en-US', {
                    hour12: true,
                    hour: '2-digit',
                    minute: '2-digit'
                });
                $('#wupos-current-time').text(timeString);
            }
            
            updateTime();
            setInterval(updateTime, 1000);
        },
        
        /**
         * Handle product search
         */
        handleProductSearch: function() {
            var searchTerm = $(this).val();
            var clearBtn = $('#wupos-search-clear');
            
            if (searchTerm.length > 0) {
                clearBtn.show();
                console.log('WUPOS: Searching for:', searchTerm);
                // TODO: Implement actual search functionality
                WUPOS.showSearchResults(searchTerm);
            } else {
                clearBtn.hide();
                WUPOS.showAllProducts();
            }
        },
        
        /**
         * Clear search input
         */
        clearSearch: function() {
            $('#wupos-product-search').val('').focus();
            $('#wupos-search-clear').hide();
            WUPOS.showAllProducts();
        },
        
        /**
         * Handle barcode scanning
         */
        handleBarcodeScanning: function(e) {
            e.preventDefault();
            console.log('WUPOS: Barcode scanning requested');
            
            // TODO: Implement barcode scanning functionality
            alert('Barcode scanning will be implemented in the next phase.');
        },
        
        /**
         * Handle category filtering
         */
        handleCategoryFilter: function(e) {
            e.preventDefault();
            
            $('.category-btn').removeClass('active');
            $(this).addClass('active');
            
            var category = $(this).data('category');
            console.log('WUPOS: Filtering by category:', category);
            
            // TODO: Implement category filtering
        },
        
        /**
         * Show search results - Now handled by template JavaScript
         */
        showSearchResults: function(searchTerm) {
            // This is now handled by the loadWooCommerceProducts function in the template
            console.log('WUPOS: Search handled by template JavaScript for:', searchTerm);
        },
        
        /**
         * Show all products - Now handled by template JavaScript
         */
        showAllProducts: function() {
            // This is now handled by the loadWooCommerceProducts function in the template
            console.log('WUPOS: Products loading handled by template JavaScript');
        },
        
        /**
         * Clear cart
         */
        clearCart: function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to clear the cart?')) {
                console.log('WUPOS: Clearing cart');
                
                // Reset cart display
                $('#wupos-cart-items').html(`
                    <div class="cart-empty" id="wupos-cart-empty">
                        <div class="empty-cart-icon">
                            <span class="dashicons dashicons-cart"></span>
                        </div>
                        <p>Cart is empty</p>
                        <p class="empty-cart-help">Add products to start a new sale</p>
                    </div>
                `);
                
                // Reset totals
                $('#wupos-subtotal, #wupos-tax, #wupos-total').text('$0.00');
                
                // Disable buttons
                $('#wupos-cart-clear, #wupos-checkout, #wupos-hold-sale, #wupos-add-discount').prop('disabled', true);
            }
        },
        
        /**
         * Process checkout
         */
        processCheckout: function(e) {
            e.preventDefault();
            console.log('WUPOS: Processing checkout');
            
            // TODO: Implement checkout functionality
            alert('Checkout functionality will be implemented in the next phase.');
        },
        
        /**
         * Hold sale
         */
        holdSale: function(e) {
            e.preventDefault();
            console.log('WUPOS: Holding sale');
            
            // TODO: Implement hold sale functionality
            alert('Hold sale functionality will be implemented in the next phase.');
        },
        
        /**
         * Add discount
         */
        addDiscount: function(e) {
            e.preventDefault();
            console.log('WUPOS: Adding discount');
            
            // TODO: Implement discount functionality
            alert('Discount functionality will be implemented in the next phase.');
        },
        
        /**
         * Show help
         */
        showHelp: function(e) {
            e.preventDefault();
            console.log('WUPOS: Showing help');
            
            // TODO: Implement help system
            alert('Help system will be implemented in the next phase.');
        },
        
        /**
         * Utility function to show loading state
         */
        showLoading: function(element) {
            $(element).addClass('wupos-loading-state');
        },
        
        /**
         * Utility function to hide loading state
         */
        hideLoading: function(element) {
            $(element).removeClass('wupos-loading-state');
        },
        
        /**
         * Utility function for AJAX requests
         */
        ajaxRequest: function(action, data, callback) {
            $.ajax({
                url: wupos_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wupos_' + action,
                    nonce: wupos_ajax.nonce,
                    ...data
                },
                success: function(response) {
                    if (response.success) {
                        if (typeof callback === 'function') {
                            callback(response.data);
                        }
                    } else {
                        console.error('WUPOS AJAX Error:', response.data);
                        alert('An error occurred: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('WUPOS AJAX Request Failed:', error);
                    alert('Request failed. Please try again.');
                }
            });
        }
    };

    /**
     * Document ready handler
     */
    $(document).ready(function() {
        // Only initialize if we're on a page with the POS system
        if ($('#wupos-pos-system').length > 0) {
            WUPOS.init();
        }
    });

    // Make WUPOS globally available for debugging
    window.WUPOS = WUPOS;

})(jQuery);