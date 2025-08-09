/**
 * WUPOS - POS Frontend JavaScript
 *
 * @package WUPOS
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * POS Application Class
     */
    class WUPOSApp {
        constructor() {
            this.cart = [];
            this.customer = null;
            this.settings = {};
            
            this.init();
        }

        /**
         * Initialize the application
         */
        init() {
            this.bindEvents();
            this.loadSettings();
            this.initializeComponents();
            
            // Initialize Feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }

        /**
         * Bind event listeners
         */
        bindEvents() {
            // Product selection
            $(document).on('click', '.product-item', this.addToCart.bind(this));
            
            // Cart management
            $(document).on('click', '.remove-item', this.removeFromCart.bind(this));
            $(document).on('change', '.quantity-input', this.updateQuantity.bind(this));
            
            // Customer management
            $('#customer-search').on('input', this.searchCustomers.bind(this));
            $(document).on('click', '.customer-item', this.selectCustomer.bind(this));
            
            // Checkout process
            $('#checkout-btn').on('click', this.startCheckout.bind(this));
            $('#process-payment').on('click', this.processPayment.bind(this));
            
            // Product search
            $('#product-search').on('input', this.searchProducts.bind(this));
            
            // Category filter
            $('.category-filter').on('click', this.filterByCategory.bind(this));
        }

        /**
         * Load POS settings
         */
        loadSettings() {
            $.ajax({
                url: wupos_pos.rest_url + 'settings',
                type: 'GET',
                headers: {
                    'X-WP-Nonce': wupos_pos.rest_nonce
                },
                success: (response) => {
                    this.settings = response;
                    this.updateUISettings();
                },
                error: (xhr, status, error) => {
                    console.error('Failed to load settings:', error);
                }
            });
        }

        /**
         * Initialize components
         */
        initializeComponents() {
            this.loadProducts();
            this.updateCartDisplay();
            this.updateTotals();
        }

        /**
         * Load products for POS
         */
        loadProducts(page = 1, search = '', category = 0) {
            const params = {
                page: page,
                per_page: 20,
                search: search,
                category: category
            };

            $.ajax({
                url: wupos_pos.rest_url + 'products',
                type: 'GET',
                data: params,
                headers: {
                    'X-WP-Nonce': wupos_pos.rest_nonce
                },
                success: (response) => {
                    this.displayProducts(response.products);
                    this.updatePagination(response);
                },
                error: (xhr, status, error) => {
                    console.error('Failed to load products:', error);
                    this.showError(wupos_pos.strings.error);
                }
            });
        }

        /**
         * Display products in grid
         */
        displayProducts(products) {
            const container = $('.products-grid');
            container.empty();

            if (products.length === 0) {
                container.html('<div class="col-12"><p class="text-center">' + wupos_pos.strings.no_products + '</p></div>');
                return;
            }

            products.forEach(product => {
                const productHtml = this.createProductHTML(product);
                container.append(productHtml);
            });
        }

        /**
         * Create product HTML
         */
        createProductHTML(product) {
            const price = this.formatPrice(product.price);
            const imageUrl = product.image_url || wupos_pos.default_image;
            
            return `
                <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
                    <div class="product-item card h-100" data-product-id="${product.id}" data-product='${JSON.stringify(product)}'>
                        <div class="product-image">
                            <img src="${imageUrl}" alt="${product.name}" class="card-img-top">
                        </div>
                        <div class="card-body p-2">
                            <h6 class="card-title mb-1">${product.name}</h6>
                            <p class="card-text mb-1">
                                <small class="text-muted">${product.sku || ''}</small>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price font-weight-bold">${price}</span>
                                <span class="stock badge badge-${product.stock_status === 'instock' ? 'success' : 'warning'}">
                                    ${product.stock_quantity || '∞'}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Add product to cart
         */
        addToCart(e) {
            const productElement = $(e.currentTarget);
            const product = productElement.data('product');
            
            if (!product) {
                return;
            }

            // Check if product already in cart
            const existingIndex = this.cart.findIndex(item => item.id === product.id);
            
            if (existingIndex >= 0) {
                // Update quantity
                this.cart[existingIndex].quantity += 1;
            } else {
                // Add new item
                this.cart.push({
                    ...product,
                    quantity: 1,
                    line_total: parseFloat(product.price)
                });
            }

            this.updateCartDisplay();
            this.updateTotals();
            this.playSound('add');
        }

        /**
         * Remove item from cart
         */
        removeFromCart(e) {
            const index = $(e.currentTarget).data('index');
            
            if (confirm(wupos_pos.strings.confirm_remove)) {
                this.cart.splice(index, 1);
                this.updateCartDisplay();
                this.updateTotals();
                this.playSound('remove');
            }
        }

        /**
         * Update item quantity
         */
        updateQuantity(e) {
            const index = $(e.currentTarget).data('index');
            const newQuantity = parseInt($(e.currentTarget).val());
            
            if (newQuantity > 0) {
                this.cart[index].quantity = newQuantity;
                this.cart[index].line_total = this.cart[index].price * newQuantity;
            } else {
                this.cart.splice(index, 1);
            }
            
            this.updateCartDisplay();
            this.updateTotals();
        }

        /**
         * Update cart display
         */
        updateCartDisplay() {
            const cartContainer = $('.cart-items');
            cartContainer.empty();

            if (this.cart.length === 0) {
                cartContainer.html('<div class="text-center p-3">' + wupos_pos.strings.empty_cart + '</div>');
                return;
            }

            this.cart.forEach((item, index) => {
                const itemHtml = `
                    <div class="cart-item d-flex align-items-center mb-2 p-2 border-bottom">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${item.name}</h6>
                            <small class="text-muted">${item.sku || ''}</small>
                        </div>
                        <div class="quantity mx-2">
                            <input type="number" class="form-control form-control-sm quantity-input" 
                                   value="${item.quantity}" min="1" data-index="${index}" style="width: 60px;">
                        </div>
                        <div class="price mx-2">
                            ${this.formatPrice(item.line_total)}
                        </div>
                        <button class="btn btn-sm btn-outline-danger remove-item" data-index="${index}">
                            <i data-feather="x"></i>
                        </button>
                    </div>
                `;
                cartContainer.append(itemHtml);
            });

            // Re-initialize feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }

        /**
         * Update totals
         */
        updateTotals() {
            const subtotal = this.cart.reduce((sum, item) => sum + item.line_total, 0);
            const tax = this.calculateTax(subtotal);
            const total = subtotal + tax;

            $('.subtotal-amount').text(this.formatPrice(subtotal));
            $('.tax-amount').text(this.formatPrice(tax));
            $('.total-amount').text(this.formatPrice(total));
            
            // Enable/disable checkout button
            $('#checkout-btn').prop('disabled', this.cart.length === 0);
        }

        /**
         * Calculate tax (simplified)
         */
        calculateTax(subtotal) {
            // This is a simplified tax calculation
            // In a real implementation, you would use WooCommerce's tax calculation
            const taxRate = 0.1; // 10% tax rate
            return subtotal * taxRate;
        }

        /**
         * Format price
         */
        formatPrice(amount) {
            const symbol = this.settings.currency_symbol || wupos_pos.currency_symbol;
            const position = this.settings.currency_position || wupos_pos.currency_position;
            const formatted = parseFloat(amount).toFixed(2);
            
            switch (position) {
                case 'left':
                    return symbol + formatted;
                case 'right':
                    return formatted + symbol;
                case 'left_space':
                    return symbol + ' ' + formatted;
                case 'right_space':
                    return formatted + ' ' + symbol;
                default:
                    return symbol + formatted;
            }
        }

        /**
         * Search products
         */
        searchProducts(e) {
            const search = $(e.target).val();
            clearTimeout(this.searchTimeout);
            
            this.searchTimeout = setTimeout(() => {
                this.loadProducts(1, search);
            }, 300);
        }

        /**
         * Search customers
         */
        searchCustomers(e) {
            const search = $(e.target).val();
            
            if (search.length < 2) {
                $('.customer-results').hide();
                return;
            }

            $.ajax({
                url: wupos_pos.rest_url + 'customers',
                type: 'GET',
                data: { search: search },
                headers: {
                    'X-WP-Nonce': wupos_pos.rest_nonce
                },
                success: (customers) => {
                    this.displayCustomerResults(customers);
                },
                error: (xhr, status, error) => {
                    console.error('Failed to search customers:', error);
                }
            });
        }

        /**
         * Display customer search results
         */
        displayCustomerResults(customers) {
            const results = $('.customer-results');
            results.empty();

            customers.forEach(customer => {
                const customerHtml = `
                    <div class="customer-item p-2 border-bottom" data-customer='${JSON.stringify(customer)}'>
                        <div class="font-weight-bold">${customer.display_name}</div>
                        <small class="text-muted">${customer.email}</small>
                    </div>
                `;
                results.append(customerHtml);
            });

            results.show();
        }

        /**
         * Select customer
         */
        selectCustomer(e) {
            const customer = $(e.currentTarget).data('customer');
            this.customer = customer;
            
            $('#customer-search').val(customer.display_name);
            $('.customer-results').hide();
            $('.selected-customer').show().find('.customer-name').text(customer.display_name);
        }

        /**
         * Start checkout process
         */
        startCheckout() {
            if (this.cart.length === 0) {
                this.showError(wupos_pos.strings.empty_cart);
                return;
            }

            // Show checkout modal
            $('#checkout-modal').modal('show');
            this.updateCheckoutSummary();
        }

        /**
         * Update checkout summary
         */
        updateCheckoutSummary() {
            const subtotal = this.cart.reduce((sum, item) => sum + item.line_total, 0);
            const tax = this.calculateTax(subtotal);
            const total = subtotal + tax;

            $('#checkout-subtotal').text(this.formatPrice(subtotal));
            $('#checkout-tax').text(this.formatPrice(tax));
            $('#checkout-total').text(this.formatPrice(total));
        }

        /**
         * Process payment
         */
        processPayment() {
            const paymentMethod = $('input[name="payment_method"]:checked').val();
            
            if (!paymentMethod) {
                this.showError('Please select a payment method');
                return;
            }

            // First create the order
            this.createOrder().then(orderId => {
                if (orderId) {
                    return this.processOrderPayment(orderId, paymentMethod);
                }
            }).then(result => {
                if (result && result.success) {
                    this.onPaymentSuccess(result.data);
                } else {
                    this.showError(wupos_pos.strings.payment_failed);
                }
            }).catch(error => {
                console.error('Payment processing error:', error);
                this.showError(wupos_pos.strings.payment_failed);
            });
        }

        /**
         * Create order
         */
        createOrder() {
            const orderData = {
                customer_id: this.customer ? this.customer.id : 0,
                line_items: this.cart.map(item => ({
                    product_id: item.id,
                    quantity: item.quantity
                })),
                terminal_id: wupos_pos.session_id
            };

            return new Promise((resolve, reject) => {
                $.ajax({
                    url: wupos_pos.rest_url + 'orders',
                    type: 'POST',
                    data: JSON.stringify(orderData),
                    contentType: 'application/json',
                    headers: {
                        'X-WP-Nonce': wupos_pos.rest_nonce
                    },
                    success: (response) => {
                        resolve(response.id);
                    },
                    error: (xhr, status, error) => {
                        console.error('Order creation failed:', error);
                        reject(error);
                    }
                });
            });
        }

        /**
         * Process order payment
         */
        processOrderPayment(orderId, paymentMethod) {
            const paymentData = {
                method: paymentMethod
            };

            if (paymentMethod === 'cash') {
                const cashReceived = parseFloat($('#cash-received').val()) || 0;
                const total = this.cart.reduce((sum, item) => sum + item.line_total, 0);
                const tax = this.calculateTax(total);
                const orderTotal = total + tax;
                
                paymentData.cash_received = cashReceived;
                paymentData.change_given = Math.max(0, cashReceived - orderTotal);
            }

            return new Promise((resolve, reject) => {
                $.ajax({
                    url: wupos_pos.rest_url + 'orders/' + orderId + '/payment',
                    type: 'POST',
                    data: JSON.stringify(paymentData),
                    contentType: 'application/json',
                    headers: {
                        'X-WP-Nonce': wupos_pos.rest_nonce
                    },
                    success: (response) => {
                        resolve({ success: true, data: response });
                    },
                    error: (xhr, status, error) => {
                        console.error('Payment processing failed:', error);
                        reject(error);
                    }
                });
            });
        }

        /**
         * Handle successful payment
         */
        onPaymentSuccess(orderData) {
            // Hide checkout modal
            $('#checkout-modal').modal('hide');
            
            // Show success message
            this.showSuccess(wupos_pos.strings.payment_success);
            
            // Clear cart
            this.cart = [];
            this.customer = null;
            
            // Reset UI
            this.updateCartDisplay();
            this.updateTotals();
            $('#customer-search').val('');
            $('.selected-customer').hide();
            
            // Play success sound
            this.playSound('success');
            
            // Optionally print receipt
            if (this.settings.auto_print_receipt === 'yes') {
                this.printReceipt(orderData);
            }
        }

        /**
         * Play sound
         */
        playSound(type) {
            if (this.settings.sound_enabled !== 'yes' && wupos_pos.sound_enabled !== 'yes') {
                return;
            }

            // Create audio element and play sound
            const audio = new Audio();
            switch (type) {
                case 'add':
                    // Add beep sound
                    break;
                case 'remove':
                    // Remove sound
                    break;
                case 'success':
                    // Success sound
                    break;
            }
        }

        /**
         * Print receipt
         */
        printReceipt(orderData) {
            // Implementation for receipt printing
            console.log('Printing receipt for order:', orderData);
        }

        /**
         * Show error message
         */
        showError(message) {
            // Implementation for error display
            alert(message); // Temporary implementation
        }

        /**
         * Show success message
         */
        showSuccess(message) {
            // Implementation for success display
            alert(message); // Temporary implementation
        }

        /**
         * Update UI settings
         */
        updateUISettings() {
            // Apply settings to UI elements
            console.log('Settings loaded:', this.settings);
        }

        /**
         * Filter products by category
         */
        filterByCategory(e) {
            const categoryId = $(e.currentTarget).data('category');
            this.loadProducts(1, '', categoryId);
        }

        /**
         * Update pagination
         */
        updatePagination(response) {
            // Implementation for pagination controls
            console.log('Pagination:', response);
        }
    }

    // Initialize the POS application when document is ready
    $(document).ready(function() {
        window.WUPOS = new WUPOSApp();
    });

})(jQuery);