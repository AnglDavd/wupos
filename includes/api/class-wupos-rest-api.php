<?php
/**
 * WUPOS REST API
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
 */
class WUPOS_REST_API {

    /**
     * API namespace
     */
    const NAMESPACE_V1 = 'wupos/v1';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register API routes.
     */
    public function register_routes() {
        // Products endpoints
        register_rest_route(self::NAMESPACE_V1, '/products', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_products'),
            'permission_callback' => array($this, 'check_pos_permissions'),
            'args'                => array(
                'page'     => array('default' => 1, 'sanitize_callback' => 'absint'),
                'per_page' => array('default' => 20, 'sanitize_callback' => 'absint'),
                'search'   => array('default' => '', 'sanitize_callback' => 'sanitize_text_field'),
                'category' => array('default' => 0, 'sanitize_callback' => 'absint'),
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
     * Get products.
     */
    public function get_products($request) {
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
            ), 200);

        } catch (Exception $e) {
            return new WP_Error('wupos_error', $e->getMessage(), array('status' => 500));
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
}

return new WUPOS_REST_API();