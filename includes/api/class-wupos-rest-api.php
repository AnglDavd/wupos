<?php
/**
 * WUPOS REST API
 *
 * Enhanced REST API with WooCommerce integration, caching,
 * and real-time inventory synchronization.
 *
 * @package WUPOS\API
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS_REST_API class.
 *
 * Handles all REST API endpoints for the WUPOS system with
 * high-performance caching and WooCommerce native integration.
 */
class WUPOS_REST_API {

    /**
     * API namespace
     */
    const NAMESPACE_V1 = 'wupos/v1';

    /**
     * Product Manager instance
     *
     * @var WUPOS_Product_Manager
     */
    private $product_manager;

    /**
     * Cache Manager instance
     *
     * @var WUPOS_Cache_Manager
     */
    private $cache_manager;

    /**
     * Inventory Sync instance
     *
     * @var WUPOS_Inventory_Sync
     */
    private $inventory_sync;

    /**
     * Rate limiting data
     *
     * @var array
     */
    private $rate_limits = array();

    /**
     * Constructor.
     */
    public function __construct() {
        $this->init_dependencies();
        add_action('rest_api_init', array($this, 'register_routes'));
        add_action('init', array($this, 'init_rate_limiting'));
    }

    /**
     * Initialize dependencies
     */
    private function init_dependencies() {
        if (class_exists('WUPOS_Product_Manager')) {
            $this->product_manager = new WUPOS_Product_Manager();
        }
        
        if (class_exists('WUPOS_Cache_Manager')) {
            $this->cache_manager = new WUPOS_Cache_Manager();
        }
        
        if (class_exists('WUPOS_Inventory_Sync')) {
            $this->inventory_sync = new WUPOS_Inventory_Sync();
        }
    }

    /**
     * Initialize rate limiting
     */
    public function init_rate_limiting() {
        $this->rate_limits = get_transient('wupos_api_rate_limits') ?: array();
    }

    /**
     * Register API routes.
     */
    public function register_routes() {
        // Products endpoints with enhanced functionality
        register_rest_route(self::NAMESPACE_V1, '/products', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_products'),
            'permission_callback' => array($this, 'check_pos_permissions'),
            'args'                => array(
                'page'          => array('default' => 1, 'sanitize_callback' => 'absint'),
                'per_page'      => array('default' => 20, 'sanitize_callback' => 'absint'),
                'search'        => array('default' => '', 'sanitize_callback' => 'sanitize_text_field'),
                'category'      => array('default' => 0, 'sanitize_callback' => 'absint'),
                'stock_status'  => array('default' => 'instock', 'sanitize_callback' => 'sanitize_text_field'),
                'orderby'       => array('default' => 'date', 'sanitize_callback' => 'sanitize_text_field'),
                'order'         => array('default' => 'DESC', 'sanitize_callback' => 'sanitize_text_field'),
                'include_variations' => array('default' => false, 'sanitize_callback' => 'rest_sanitize_boolean'),
            ),
        ));

        register_rest_route(self::NAMESPACE_V1, '/products/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_product'),
            'permission_callback' => array($this, 'check_pos_permissions'),
            'args'                => array(
                'id' => array('required' => true, 'sanitize_callback' => 'absint'),
            ),
        ));

        // Product search endpoint
        register_rest_route(self::NAMESPACE_V1, '/products/search', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'search_products'),
            'permission_callback' => array($this, 'check_pos_permissions'),
            'args'                => array(
                'q'              => array('required' => true, 'sanitize_callback' => 'sanitize_text_field'),
                'limit'          => array('default' => 50, 'sanitize_callback' => 'absint'),
                'search_fields'  => array('default' => array('name', 'sku', 'barcode'), 'sanitize_callback' => array($this, 'sanitize_array')),
            ),
        ));

        // Product categories endpoint
        register_rest_route(self::NAMESPACE_V1, '/categories', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_categories'),
            'permission_callback' => array($this, 'check_pos_permissions'),
            'args'                => array(
                'hide_empty'     => array('default' => false, 'sanitize_callback' => 'rest_sanitize_boolean'),
                'hierarchical'   => array('default' => true, 'sanitize_callback' => 'rest_sanitize_boolean'),
                'include_count'  => array('default' => true, 'sanitize_callback' => 'rest_sanitize_boolean'),
            ),
        ));

        // Stock management endpoints
        register_rest_route(self::NAMESPACE_V1, '/stock/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_stock_info'),
            'permission_callback' => array($this, 'check_pos_permissions'),
            'args'                => array(
                'id' => array('required' => true, 'sanitize_callback' => 'absint'),
            ),
        ));

        register_rest_route(self::NAMESPACE_V1, '/stock/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => array($this, 'update_stock'),
            'permission_callback' => array($this, 'check_stock_permissions'),
            'args'                => array(
                'id'        => array('required' => true, 'sanitize_callback' => 'absint'),
                'quantity'  => array('required' => true, 'sanitize_callback' => 'floatval'),
                'operation' => array('default' => 'set', 'sanitize_callback' => 'sanitize_text_field'),
                'reason'    => array('default' => 'pos_adjustment', 'sanitize_callback' => 'sanitize_text_field'),
                'note'      => array('default' => '', 'sanitize_callback' => 'sanitize_textarea_field'),
            ),
        ));

        // Stock reservations
        register_rest_route(self::NAMESPACE_V1, '/stock/reserve', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'reserve_stock'),
            'permission_callback' => array($this, 'check_pos_permissions'),
            'args'                => array(
                'product_id' => array('required' => true, 'sanitize_callback' => 'absint'),
                'quantity'   => array('required' => true, 'sanitize_callback' => 'absint'),
                'order_key'  => array('required' => true, 'sanitize_callback' => 'sanitize_text_field'),
                'timeout'    => array('default' => 300, 'sanitize_callback' => 'absint'),
            ),
        ));

        register_rest_route(self::NAMESPACE_V1, '/stock/release', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'release_stock'),
            'permission_callback' => array($this, 'check_pos_permissions'),
            'args'                => array(
                'order_key'  => array('required' => true, 'sanitize_callback' => 'sanitize_text_field'),
                'product_id' => array('default' => null, 'sanitize_callback' => 'absint'),
            ),
        ));

        // Cache management endpoints
        register_rest_route(self::NAMESPACE_V1, '/cache/clear', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'clear_cache'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        register_rest_route(self::NAMESPACE_V1, '/cache/stats', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_cache_stats'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        // Customers endpoints
        register_rest_route(self::NAMESPACE_V1, '/customers', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_customers'),
            'permission_callback' => array($this, 'check_pos_permissions'),
            'args'                => array(
                'search' => array('default' => '', 'sanitize_callback' => 'sanitize_text_field'),
            ),
        ));

        register_rest_route(self::NAMESPACE_V1, '/customers', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'create_customer'),
            'permission_callback' => array($this, 'check_pos_permissions'),
            'args'                => array(
                'first_name' => array('required' => true, 'sanitize_callback' => 'sanitize_text_field'),
                'last_name'  => array('required' => true, 'sanitize_callback' => 'sanitize_text_field'),
                'email'      => array('required' => true, 'sanitize_callback' => 'sanitize_email'),
            ),
        ));

        // Orders endpoints
        register_rest_route(self::NAMESPACE_V1, '/orders', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'create_order'),
            'permission_callback' => array($this, 'check_pos_permissions'),
            'args'                => array(
                'customer_id' => array('default' => 0, 'sanitize_callback' => 'absint'),
                'line_items'  => array('required' => true, 'type' => 'array'),
                'terminal_id' => array('default' => '', 'sanitize_callback' => 'sanitize_text_field'),
            ),
        ));

        register_rest_route(self::NAMESPACE_V1, '/orders/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_order'),
            'permission_callback' => array($this, 'check_pos_permissions'),
            'args'                => array(
                'id' => array('required' => true, 'sanitize_callback' => 'absint'),
            ),
        ));

        register_rest_route(self::NAMESPACE_V1, '/orders/(?P<id>\d+)/payment', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'process_payment'),
            'permission_callback' => array($this, 'check_pos_permissions'),
            'args'                => array(
                'id'            => array('required' => true, 'sanitize_callback' => 'absint'),
                'method'        => array('required' => true, 'sanitize_callback' => 'sanitize_text_field'),
                'cash_received' => array('default' => 0, 'sanitize_callback' => 'floatval'),
                'change_given'  => array('default' => 0, 'sanitize_callback' => 'floatval'),
            ),
        ));

        // Settings endpoints
        register_rest_route(self::NAMESPACE_V1, '/settings', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_settings'),
            'permission_callback' => array($this, 'check_pos_permissions'),
        ));
    }

    /**
     * Check POS permissions.
     */
    public function check_pos_permissions($request) {
        return wupos_user_can_pos();
    }

    /**
     * Get products using enhanced Product Manager.
     */
    public function get_products($request) {
        // Apply rate limiting
        if (!$this->check_rate_limit($request)) {
            return new WP_Error('rate_limit_exceeded', 
                __('Rate limit exceeded. Please try again later.', 'wupos'), 
                array('status' => 429)
            );
        }

        try {
            // Fallback if Product Manager not available
            if (!$this->product_manager) {
                return $this->get_products_legacy($request);
            }

            $args = array(
                'page'          => $request->get_param('page'),
                'per_page'      => $request->get_param('per_page'),
                'search'        => $request->get_param('search'),
                'category'      => $request->get_param('category'),
                'stock_status'  => $request->get_param('stock_status'),
                'orderby'       => $request->get_param('orderby'),
                'order'         => $request->get_param('order'),
                'include_variations' => $request->get_param('include_variations'),
            );

            $result = $this->product_manager->get_products($args);
            
            if (isset($result['error']) && $result['error']) {
                return new WP_Error($result['code'], $result['message'], array('status' => 500));
            }

            // Add performance metadata
            $result['api_version'] = '1.0.0';
            $result['timestamp'] = current_time('timestamp');
            $result['hpos_enabled'] = wupos_is_hpos_enabled();

            return new WP_REST_Response($result, 200);

        } catch (Exception $e) {
            wupos_log('API Error in get_products: ' . $e->getMessage(), 'error');
            return new WP_Error('wupos_api_error', 
                __('An error occurred while fetching products.', 'wupos'), 
                array('status' => 500)
            );
        }
    }

    /**
     * Search products using enhanced search functionality.
     */
    public function search_products($request) {
        if (!$this->check_rate_limit($request)) {
            return new WP_Error('rate_limit_exceeded', 
                __('Rate limit exceeded. Please try again later.', 'wupos'), 
                array('status' => 429)
            );
        }

        try {
            if (!$this->product_manager) {
                return new WP_Error('service_unavailable', 
                    __('Product search service is not available.', 'wupos'), 
                    array('status' => 503)
                );
            }

            $search_term = $request->get_param('q');
            $args = array(
                'limit' => $request->get_param('limit'),
                'search_fields' => $request->get_param('search_fields'),
            );

            $result = $this->product_manager->search_products($search_term, $args);
            $result['timestamp'] = current_time('timestamp');

            return new WP_REST_Response($result, 200);

        } catch (Exception $e) {
            wupos_log('API Error in search_products: ' . $e->getMessage(), 'error');
            return new WP_Error('search_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Get product categories.
     */
    public function get_categories($request) {
        if (!$this->check_rate_limit($request)) {
            return new WP_Error('rate_limit_exceeded', 
                __('Rate limit exceeded. Please try again later.', 'wupos'), 
                array('status' => 429)
            );
        }

        try {
            if (!$this->product_manager) {
                return $this->get_categories_legacy($request);
            }

            $args = array(
                'hide_empty' => $request->get_param('hide_empty'),
                'hierarchical' => $request->get_param('hierarchical'),
                'include_count' => $request->get_param('include_count'),
            );

            $result = $this->product_manager->get_categories($args);
            
            if (isset($result['error']) && $result['error']) {
                return new WP_Error('categories_error', $result['error'], array('status' => 500));
            }

            $result['timestamp'] = current_time('timestamp');
            return new WP_REST_Response($result, 200);

        } catch (Exception $e) {
            wupos_log('API Error in get_categories: ' . $e->getMessage(), 'error');
            return new WP_Error('categories_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Get stock information for a product.
     */
    public function get_stock_info($request) {
        if (!$this->check_rate_limit($request)) {
            return new WP_Error('rate_limit_exceeded', 
                __('Rate limit exceeded. Please try again later.', 'wupos'), 
                array('status' => 429)
            );
        }

        try {
            $product_id = $request->get_param('id');

            if (!$this->inventory_sync) {
                return new WP_Error('service_unavailable', 
                    __('Inventory service is not available.', 'wupos'), 
                    array('status' => 503)
                );
            }

            $result = $this->inventory_sync->get_real_time_stock($product_id);
            
            if (isset($result['error']) && $result['error']) {
                return new WP_Error($result['code'], $result['message'], array('status' => 400));
            }

            return new WP_REST_Response($result, 200);

        } catch (Exception $e) {
            wupos_log('API Error in get_stock_info: ' . $e->getMessage(), 'error');
            return new WP_Error('stock_info_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Update product stock.
     */
    public function update_stock($request) {
        if (!$this->check_rate_limit($request, 'write')) {
            return new WP_Error('rate_limit_exceeded', 
                __('Rate limit exceeded. Please try again later.', 'wupos'), 
                array('status' => 429)
            );
        }

        try {
            if (!$this->inventory_sync) {
                return new WP_Error('service_unavailable', 
                    __('Inventory service is not available.', 'wupos'), 
                    array('status' => 503)
                );
            }

            $product_id = $request->get_param('id');
            $quantity = $request->get_param('quantity');
            $operation = $request->get_param('operation');
            
            $options = array(
                'terminal_id' => wupos_get_session_id(),
                'user_id' => get_current_user_id(),
                'reason' => $request->get_param('reason'),
                'note' => $request->get_param('note'),
            );

            $result = $this->inventory_sync->update_stock($product_id, $quantity, $operation, $options);
            
            if (is_wp_error($result)) {
                return $result;
            }

            return new WP_REST_Response($result, 200);

        } catch (Exception $e) {
            wupos_log('API Error in update_stock: ' . $e->getMessage(), 'error');
            return new WP_Error('stock_update_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Reserve stock for pending orders.
     */
    public function reserve_stock($request) {
        if (!$this->check_rate_limit($request, 'write')) {
            return new WP_Error('rate_limit_exceeded', 
                __('Rate limit exceeded. Please try again later.', 'wupos'), 
                array('status' => 429)
            );
        }

        try {
            if (!$this->inventory_sync) {
                return new WP_Error('service_unavailable', 
                    __('Inventory service is not available.', 'wupos'), 
                    array('status' => 503)
                );
            }

            $product_id = $request->get_param('product_id');
            $quantity = $request->get_param('quantity');
            $order_key = $request->get_param('order_key');
            $timeout = $request->get_param('timeout');

            $result = $this->inventory_sync->reserve_stock($product_id, $quantity, $order_key, $timeout);
            
            if (is_wp_error($result)) {
                return $result;
            }

            return new WP_REST_Response($result, 201);

        } catch (Exception $e) {
            wupos_log('API Error in reserve_stock: ' . $e->getMessage(), 'error');
            return new WP_Error('stock_reservation_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Release stock reservations.
     */
    public function release_stock($request) {
        if (!$this->check_rate_limit($request, 'write')) {
            return new WP_Error('rate_limit_exceeded', 
                __('Rate limit exceeded. Please try again later.', 'wupos'), 
                array('status' => 429)
            );
        }

        try {
            if (!$this->inventory_sync) {
                return new WP_Error('service_unavailable', 
                    __('Inventory service is not available.', 'wupos'), 
                    array('status' => 503)
                );
            }

            $order_key = $request->get_param('order_key');
            $product_id = $request->get_param('product_id');

            $result = $this->inventory_sync->release_stock_reservation($order_key, $product_id);
            
            if (is_wp_error($result)) {
                return $result;
            }

            return new WP_REST_Response($result, 200);

        } catch (Exception $e) {
            wupos_log('API Error in release_stock: ' . $e->getMessage(), 'error');
            return new WP_Error('stock_release_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Clear cache.
     */
    public function clear_cache($request) {
        try {
            if (!$this->cache_manager) {
                return new WP_Error('service_unavailable', 
                    __('Cache service is not available.', 'wupos'), 
                    array('status' => 503)
                );
            }

            $this->cache_manager->clear_all_cache();

            return new WP_REST_Response(array(
                'success' => true,
                'message' => __('All caches cleared successfully.', 'wupos'),
                'timestamp' => current_time('timestamp'),
            ), 200);

        } catch (Exception $e) {
            wupos_log('API Error in clear_cache: ' . $e->getMessage(), 'error');
            return new WP_Error('cache_clear_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Get cache statistics.
     */
    public function get_cache_stats($request) {
        try {
            if (!$this->cache_manager) {
                return new WP_Error('service_unavailable', 
                    __('Cache service is not available.', 'wupos'), 
                    array('status' => 503)
                );
            }

            $stats = $this->cache_manager->get_cache_stats();
            $health = $this->cache_manager->get_cache_health();

            return new WP_REST_Response(array(
                'stats' => $stats,
                'health' => $health,
                'timestamp' => current_time('timestamp'),
            ), 200);

        } catch (Exception $e) {
            wupos_log('API Error in get_cache_stats: ' . $e->getMessage(), 'error');
            return new WP_Error('cache_stats_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Get single product.
     */
    public function get_product($request) {
        try {
            $product_id = $request->get_param('id');
            $product = wc_get_product($product_id);

            if (!$product) {
                return new WP_Error('product_not_found', __('Product not found.', 'wupos'), array('status' => 404));
            }

            return new WP_REST_Response($this->prepare_product_response($product), 200);

        } catch (Exception $e) {
            return new WP_Error('wupos_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Get customers.
     */
    public function get_customers($request) {
        try {
            $search = $request->get_param('search');

            if (empty($search)) {
                return new WP_REST_Response(array(), 200);
            }

            $users = get_users(array(
                'search'         => '*' . $search . '*',
                'search_columns' => array('user_login', 'user_email', 'display_name'),
                'number'         => 20,
                'role__in'       => array('customer', 'subscriber'),
            ));

            $customers = array();
            
            foreach ($users as $user) {
                $customers[] = $this->prepare_customer_response($user);
            }

            return new WP_REST_Response($customers, 200);

        } catch (Exception $e) {
            return new WP_Error('wupos_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Create customer.
     */
    public function create_customer($request) {
        try {
            $first_name = $request->get_param('first_name');
            $last_name = $request->get_param('last_name');
            $email = $request->get_param('email');

            // Check if email already exists
            if (email_exists($email)) {
                return new WP_Error('email_exists', __('Email already exists.', 'wupos'), array('status' => 400));
            }

            // Create user
            $user_id = wp_create_user($email, wp_generate_password(), $email);

            if (is_wp_error($user_id)) {
                return $user_id;
            }

            // Set user meta
            update_user_meta($user_id, 'first_name', $first_name);
            update_user_meta($user_id, 'last_name', $last_name);

            // Set role
            $user = new WP_User($user_id);
            $user->set_role('customer');

            return new WP_REST_Response($this->prepare_customer_response($user), 201);

        } catch (Exception $e) {
            return new WP_Error('wupos_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Create order.
     */
    public function create_order($request) {
        try {
            $customer_id = $request->get_param('customer_id');
            $line_items = $request->get_param('line_items');
            $terminal_id = $request->get_param('terminal_id');

            if (empty($line_items)) {
                return new WP_Error('empty_cart', __('No items in cart.', 'wupos'), array('status' => 400));
            }

            // Create the order
            $order = wc_create_order();
            
            if (is_wp_error($order)) {
                return new WP_Error('order_creation_failed', __('Failed to create order.', 'wupos'), array('status' => 500));
            }

            // Set customer
            if ($customer_id > 0) {
                $order->set_customer_id($customer_id);
            }

            // Add line items
            foreach ($line_items as $item) {
                $product = wc_get_product($item['product_id']);
                
                if ($product) {
                    $order->add_product($product, $item['quantity']);
                }
            }

            // Calculate totals
            $order->calculate_totals();

            // Add POS meta data
            $order->add_meta_data('_wupos_pos_order', 'yes');
            $order->add_meta_data('_wupos_terminal_id', $terminal_id);
            $order->add_meta_data('_wupos_cashier_id', get_current_user_id());
            $order->add_meta_data('_wupos_session_id', wupos_get_session_id());

            // Save the order
            $order->save();

            return new WP_REST_Response($this->prepare_order_response($order), 201);

        } catch (Exception $e) {
            return new WP_Error('wupos_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Get order.
     */
    public function get_order($request) {
        try {
            $order_id = $request->get_param('id');
            $order = wc_get_order($order_id);

            if (!$order) {
                return new WP_Error('order_not_found', __('Order not found.', 'wupos'), array('status' => 404));
            }

            return new WP_REST_Response($this->prepare_order_response($order), 200);

        } catch (Exception $e) {
            return new WP_Error('wupos_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Process payment.
     */
    public function process_payment($request) {
        try {
            $order_id = $request->get_param('id');
            $method = $request->get_param('method');
            $cash_received = $request->get_param('cash_received');
            $change_given = $request->get_param('change_given');

            $order = wc_get_order($order_id);
            
            if (!$order) {
                return new WP_Error('order_not_found', __('Order not found.', 'wupos'), array('status' => 404));
            }

            // Set payment method
            $order->set_payment_method($method);
            
            // Add payment meta data
            if ($method === 'cash') {
                $order->add_meta_data('_wupos_cash_received', $cash_received);
                $order->add_meta_data('_wupos_change_given', $change_given);
            }

            $order->add_meta_data('_wupos_payment_timestamp', current_time('timestamp'));

            // Complete the payment
            $order->payment_complete();
            $order->set_status('completed');
            $order->save();

            return new WP_REST_Response($this->prepare_order_response($order), 200);

        } catch (Exception $e) {
            return new WP_Error('wupos_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Get settings.
     */
    public function get_settings($request) {
        try {
            $settings = array(
                'currency_symbol'         => get_woocommerce_currency_symbol(),
                'currency_position'       => get_option('woocommerce_currency_pos', 'left'),
                'tax_display'             => get_option('wupos_tax_display', 'excl'),
                'receipt_template'        => get_option('wupos_receipt_template', 'default'),
                'barcode_field'           => get_option('wupos_barcode_field', '_sku'),
                'customer_registration'   => get_option('wupos_customer_registration', 'yes'),
                'default_customer'        => get_option('wupos_default_customer', 0),
                'auto_print_receipt'      => get_option('wupos_auto_print_receipt', 'no'),
                'sound_enabled'           => get_option('wupos_sound_enabled', 'yes'),
            );

            return new WP_REST_Response($settings, 200);

        } catch (Exception $e) {
            return new WP_Error('wupos_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Prepare product response.
     */
    private function prepare_product_response($product) {
        return array(
            'id'             => $product->get_id(),
            'name'           => $product->get_name(),
            'sku'            => $product->get_sku(),
            'price'          => $product->get_price(),
            'regular_price'  => $product->get_regular_price(),
            'sale_price'     => $product->get_sale_price(),
            'stock_quantity' => $product->get_stock_quantity(),
            'manage_stock'   => $product->get_manage_stock(),
            'stock_status'   => $product->get_stock_status(),
            'image_url'      => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
            'categories'     => wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names')),
            'type'           => $product->get_type(),
        );
    }

    /**
     * Prepare customer response.
     */
    private function prepare_customer_response($user) {
        return array(
            'id'           => $user->ID,
            'display_name' => $user->display_name,
            'email'        => $user->user_email,
            'first_name'   => get_user_meta($user->ID, 'first_name', true),
            'last_name'    => get_user_meta($user->ID, 'last_name', true),
        );
    }

    /**
     * Prepare order response.
     */
    private function prepare_order_response($order) {
        $line_items = array();
        
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $line_items[] = array(
                'name'     => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'total'    => $item->get_total(),
                'sku'      => $product ? $product->get_sku() : '',
            );
        }

        $customer_data = array();
        if ($order->get_customer_id()) {
            $customer = new WC_Customer($order->get_customer_id());
            $customer_data = array(
                'id'         => $customer->get_id(),
                'email'      => $customer->get_email(),
                'first_name' => $customer->get_first_name(),
                'last_name'  => $customer->get_last_name(),
            );
        }

        return array(
            'id'             => $order->get_id(),
            'status'         => $order->get_status(),
            'total'          => $order->get_total(),
            'subtotal'       => $order->get_subtotal(),
            'tax_total'      => $order->get_total_tax(),
            'currency'       => $order->get_currency(),
            'payment_method' => $order->get_payment_method(),
            'date_created'   => $order->get_date_created()->format('Y-m-d H:i:s'),
            'line_items'     => $line_items,
            'customer'       => $customer_data,
            'pos_data'       => array(
                'terminal_id'    => $order->get_meta('_wupos_terminal_id'),
                'cashier_id'     => $order->get_meta('_wupos_cashier_id'),
                'session_id'     => $order->get_meta('_wupos_session_id'),
                'cash_received'  => $order->get_meta('_wupos_cash_received'),
                'change_given'   => $order->get_meta('_wupos_change_given'),
            ),
        );
    }

    /**
     * Check rate limiting for API requests
     *
     * @param WP_REST_Request $request Request object
     * @param string $type Request type (read/write)
     * @return bool True if within limits
     */
    private function check_rate_limit($request, $type = 'read') {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return false; // No anonymous access
        }

        // Get rate limits based on user role
        $limits = $this->get_rate_limits($user_id, $type);
        $current_time = current_time('timestamp');
        $window_start = $current_time - HOUR_IN_SECONDS;
        
        // Generate rate limit key
        $rate_key = sprintf('user_%d_%s_%d', $user_id, $type, floor($current_time / HOUR_IN_SECONDS));
        
        // Get current count
        $current_count = get_transient($rate_key) ?: 0;
        
        if ($current_count >= $limits) {
            wupos_log(sprintf('Rate limit exceeded for user %d: %d/%d (%s)', $user_id, $current_count, $limits, $type), 'warning');
            return false;
        }
        
        // Increment counter
        set_transient($rate_key, $current_count + 1, HOUR_IN_SECONDS);
        
        return true;
    }

    /**
     * Get rate limits for user
     *
     * @param int $user_id User ID
     * @param string $type Request type
     * @return int Rate limit
     */
    private function get_rate_limits($user_id, $type = 'read') {
        $user = get_userdata($user_id);
        $limits = array(
            'read' => 1000,   // Default read limit per hour
            'write' => 200,   // Default write limit per hour
        );
        
        if ($user && in_array('administrator', $user->roles)) {
            $limits = array(
                'read' => 5000,
                'write' => 1000,
            );
        } elseif ($user && in_array('shop_manager', $user->roles)) {
            $limits = array(
                'read' => 2000,
                'write' => 500,
            );
        }
        
        $limits = apply_filters('wupos_api_rate_limits', $limits, $user_id, $type);
        
        return isset($limits[$type]) ? $limits[$type] : 1000;
    }

    /**
     * Check admin permissions
     */
    public function check_admin_permissions($request) {
        return current_user_can('manage_options') && wupos_user_can_pos();
    }

    /**
     * Check stock management permissions
     */
    public function check_stock_permissions($request) {
        return current_user_can('manage_woocommerce') && wupos_user_can_pos();
    }

    /**
     * Sanitize array input
     *
     * @param mixed $value Input value
     * @return array Sanitized array
     */
    public function sanitize_array($value) {
        if (!is_array($value)) {
            return array();
        }
        
        return array_map('sanitize_text_field', $value);
    }

    /**
     * Legacy fallback for get_products (without Product Manager)
     */
    private function get_products_legacy($request) {
        try {
            $page = $request->get_param('page');
            $per_page = $request->get_param('per_page');
            $search = $request->get_param('search');
            $category = $request->get_param('category');

            $args = array(
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'posts_per_page' => $per_page,
                'paged'          => $page,
                'meta_query'     => array(
                    array(
                        'key'     => '_stock_status',
                        'value'   => 'instock',
                        'compare' => '=',
                    ),
                ),
            );

            if (!empty($search)) {
                $args['s'] = $search;
            }

            if ($category > 0) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field'    => 'term_id',
                        'terms'    => $category,
                    ),
                );
            }

            $query = new WP_Query($args);
            $products = array();

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $product = wc_get_product(get_the_ID());
                    
                    if ($product) {
                        $products[] = $this->prepare_product_response($product);
                    }
                }
            }

            wp_reset_postdata();

            return new WP_REST_Response(array(
                'products'       => $products,
                'total_pages'    => $query->max_num_pages,
                'current_page'   => $page,
                'total_products' => $query->found_posts,
                'api_version'    => '1.0.0-legacy',
                'timestamp'      => current_time('timestamp'),
                'hpos_enabled'   => wupos_is_hpos_enabled(),
                'from_cache'     => false,
            ), 200);

        } catch (Exception $e) {
            return new WP_Error('wupos_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Legacy fallback for get_categories (without Product Manager)
     */
    private function get_categories_legacy($request) {
        try {
            $hide_empty = $request->get_param('hide_empty');
            
            $terms = get_terms(array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => $hide_empty,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ));
            
            if (is_wp_error($terms)) {
                throw new Exception($terms->get_error_message());
            }

            $categories = array();
            
            foreach ($terms as $term) {
                $categories[] = array(
                    'id'          => $term->term_id,
                    'name'        => $term->name,
                    'slug'        => $term->slug,
                    'parent'      => $term->parent,
                    'description' => $term->description,
                    'count'       => $term->count,
                );
            }
            
            return new WP_REST_Response(array(
                'categories' => $categories,
                'total' => count($categories),
                'timestamp' => current_time('timestamp'),
                'from_cache' => false,
            ), 200);

        } catch (Exception $e) {
            return new WP_Error('categories_error', $e->getMessage(), array('status' => 500));
        }
    }
}

return new WUPOS_REST_API();