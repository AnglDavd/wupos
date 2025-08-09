<?php
/**
 * WUPOS Cart REST API
 *
 * REST API endpoints for cart management operations including
 * add/remove/update items, tax calculations, and session management.
 *
 * @package WUPOS\API\Cart
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS_Cart_API class.
 *
 * Handles all cart-related REST API endpoints with proper
 * authentication, validation, and error handling.
 */
class WUPOS_Cart_API extends WP_REST_Controller {

    /**
     * REST namespace
     */
    const NAMESPACE_V1 = 'wupos/v1';

    /**
     * Cart manager instance
     *
     * @var WUPOS_Cart_Manager
     */
    private $cart_manager;

    /**
     * Session handler instance
     *
     * @var WUPOS_Session_Handler
     */
    private $session_handler;

    /**
     * Tax calculator instance
     *
     * @var WUPOS_Tax_Calculator
     */
    private $tax_calculator;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->init_dependencies();
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Initialize dependencies.
     */
    private function init_dependencies() {
        // Dependencies will be initialized per request to avoid conflicts
        // They are created in get_cart_manager() method
    }

    /**
     * Register REST API routes.
     */
    public function register_routes() {
        // Cart contents endpoints
        register_rest_route(self::NAMESPACE_V1, '/cart', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_cart_contents'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
                'calculate_totals' => array('default' => true, 'sanitize_callback' => 'rest_sanitize_boolean'),
            ),
        ));

        // Add item to cart
        register_rest_route(self::NAMESPACE_V1, '/cart/add', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'add_cart_item'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'product_id'     => array('required' => true, 'sanitize_callback' => 'absint'),
                'quantity'       => array('default' => 1, 'sanitize_callback' => 'absint'),
                'variation_id'   => array('default' => 0, 'sanitize_callback' => 'absint'),
                'variation_data' => array('default' => array(), 'sanitize_callback' => array($this, 'sanitize_variation_data')),
                'item_data'      => array('default' => array(), 'sanitize_callback' => array($this, 'sanitize_item_data')),
                'terminal_id'    => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
            ),
        ));

        // Update cart item
        register_rest_route(self::NAMESPACE_V1, '/cart/update/(?P<item_key>[a-zA-Z0-9]+)', array(
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => array($this, 'update_cart_item'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'item_key' => array('required' => true, 'sanitize_callback' => 'sanitize_text_field'),
                'quantity' => array('required' => true, 'sanitize_callback' => 'absint'),
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
            ),
        ));

        // Remove cart item
        register_rest_route(self::NAMESPACE_V1, '/cart/remove/(?P<item_key>[a-zA-Z0-9]+)', array(
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => array($this, 'remove_cart_item'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'item_key' => array('required' => true, 'sanitize_callback' => 'sanitize_text_field'),
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
            ),
        ));

        // Clear entire cart
        register_rest_route(self::NAMESPACE_V1, '/cart/clear', array(
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => array($this, 'clear_cart'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
                'confirm' => array('default' => false, 'sanitize_callback' => 'rest_sanitize_boolean'),
            ),
        ));

        // Get cart totals
        register_rest_route(self::NAMESPACE_V1, '/cart/totals', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_cart_totals'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
                'customer_location' => array('default' => array(), 'sanitize_callback' => array($this, 'sanitize_location')),
            ),
        ));

        // Calculate cart totals (force recalculation)
        register_rest_route(self::NAMESPACE_V1, '/cart/calculate', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'calculate_cart_totals'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
                'customer_location' => array('default' => array(), 'sanitize_callback' => array($this, 'sanitize_location')),
            ),
        ));

        // Get tax information
        register_rest_route(self::NAMESPACE_V1, '/cart/taxes', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_cart_taxes'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
                'customer_location' => array('default' => array(), 'sanitize_callback' => array($this, 'sanitize_location')),
            ),
        ));

        // Apply discount/coupon
        register_rest_route(self::NAMESPACE_V1, '/cart/apply-discount', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'apply_discount'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'coupon_code' => array('required' => true, 'sanitize_callback' => 'sanitize_text_field'),
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
            ),
        ));

        // Remove discount/coupon
        register_rest_route(self::NAMESPACE_V1, '/cart/remove-discount', array(
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => array($this, 'remove_discount'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'coupon_code' => array('required' => true, 'sanitize_callback' => 'sanitize_text_field'),
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
            ),
        ));

        // Session management endpoints
        register_rest_route(self::NAMESPACE_V1, '/cart/session/create', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'create_session'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'terminal_id' => array('required' => true, 'sanitize_callback' => 'sanitize_text_field'),
            ),
        ));

        register_rest_route(self::NAMESPACE_V1, '/cart/session/validate', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'validate_session'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'session_id' => array('required' => true, 'sanitize_callback' => 'sanitize_text_field'),
                'terminal_id' => array('required' => true, 'sanitize_callback' => 'sanitize_text_field'),
            ),
        ));

        register_rest_route(self::NAMESPACE_V1, '/cart/session/extend', array(
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => array($this, 'extend_session'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
                'additional_time' => array('default' => 3600, 'sanitize_callback' => 'absint'),
            ),
        ));

        register_rest_route(self::NAMESPACE_V1, '/cart/session/destroy', array(
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => array($this, 'destroy_session'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
            ),
        ));

        // Set customer for cart
        register_rest_route(self::NAMESPACE_V1, '/cart/customer', array(
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => array($this, 'set_cart_customer'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'customer_id' => array('required' => true, 'sanitize_callback' => 'absint'),
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
            ),
        ));

        // Set customer location
        register_rest_route(self::NAMESPACE_V1, '/cart/location', array(
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => array($this, 'set_customer_location'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'location' => array('required' => true, 'sanitize_callback' => array($this, 'sanitize_location')),
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
            ),
        ));

        // Batch operations
        register_rest_route(self::NAMESPACE_V1, '/cart/batch-add', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'batch_add_items'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'items' => array('required' => true, 'sanitize_callback' => array($this, 'sanitize_batch_items')),
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
            ),
        ));

        // Cart summary (optimized)
        register_rest_route(self::NAMESPACE_V1, '/cart/summary', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_cart_summary'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
            ),
        ));

        // Cart status check
        register_rest_route(self::NAMESPACE_V1, '/cart/status', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'check_cart_status'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
            ),
        ));

        // Performance metrics
        register_rest_route(self::NAMESPACE_V1, '/cart/performance', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_performance_metrics'),
            'permission_callback' => array($this, 'check_permissions'),
            'args'                => array(
                'terminal_id' => array('required' => false, 'sanitize_callback' => 'sanitize_text_field'),
                'reset' => array('default' => false, 'sanitize_callback' => 'rest_sanitize_boolean'),
            ),
        ));
    }

    /**
     * Get cart contents.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function get_cart_contents($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $calculate_totals = $request->get_param('calculate_totals');
            
            $cart_manager = $this->get_cart_manager($terminal_id);
            
            if (is_wp_error($cart_manager)) {
                return $cart_manager;
            }

            $cart_contents = $cart_manager->get_cart_contents($calculate_totals);
            
            return new WP_REST_Response(array(
                'success' => true,
                'data' => $cart_contents,
                'timestamp' => current_time('timestamp')
            ), 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in get_cart_contents: ' . $e->getMessage(), 'error');
            return new WP_Error('cart_api_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Add item to cart.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function add_cart_item($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $product_id = $request->get_param('product_id');
            $quantity = $request->get_param('quantity');
            $variation_id = $request->get_param('variation_id');
            $variation_data = $request->get_param('variation_data');
            $item_data = $request->get_param('item_data');
            
            $cart_manager = $this->get_cart_manager($terminal_id);
            
            if (is_wp_error($cart_manager)) {
                return $cart_manager;
            }

            $result = $cart_manager->add_to_cart($product_id, $quantity, $variation_id, $variation_data, $item_data);
            
            if (is_wp_error($result)) {
                return $result;
            }

            return new WP_REST_Response($result, 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in add_cart_item: ' . $e->getMessage(), 'error');
            return new WP_Error('cart_add_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Update cart item.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function update_cart_item($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $item_key = $request->get_param('item_key');
            $quantity = $request->get_param('quantity');
            
            $cart_manager = $this->get_cart_manager($terminal_id);
            
            if (is_wp_error($cart_manager)) {
                return $cart_manager;
            }

            $result = $cart_manager->update_cart_item_quantity($item_key, $quantity);
            
            if (is_wp_error($result)) {
                return $result;
            }

            return new WP_REST_Response($result, 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in update_cart_item: ' . $e->getMessage(), 'error');
            return new WP_Error('cart_update_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Remove cart item.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function remove_cart_item($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $item_key = $request->get_param('item_key');
            
            $cart_manager = $this->get_cart_manager($terminal_id);
            
            if (is_wp_error($cart_manager)) {
                return $cart_manager;
            }

            $result = $cart_manager->remove_cart_item($item_key);
            
            if (is_wp_error($result)) {
                return $result;
            }

            return new WP_REST_Response($result, 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in remove_cart_item: ' . $e->getMessage(), 'error');
            return new WP_Error('cart_remove_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Clear entire cart.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function clear_cart($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $confirm = $request->get_param('confirm');
            
            if (!$confirm) {
                return new WP_Error('confirmation_required', 
                    __('Cart clear operation requires confirmation.', 'wupos'), 
                    array('status' => 400));
            }
            
            $cart_manager = $this->get_cart_manager($terminal_id);
            
            if (is_wp_error($cart_manager)) {
                return $cart_manager;
            }

            $result = $cart_manager->clear_cart();

            return new WP_REST_Response($result, 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in clear_cart: ' . $e->getMessage(), 'error');
            return new WP_Error('cart_clear_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Get cart totals.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function get_cart_totals($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $customer_location = $request->get_param('customer_location');
            
            $cart_manager = $this->get_cart_manager($terminal_id);
            
            if (is_wp_error($cart_manager)) {
                return $cart_manager;
            }

            // Set customer location if provided
            if (!empty($customer_location)) {
                $cart_manager->set_customer_location($customer_location);
            }

            $totals = $cart_manager->calculate_totals();

            return new WP_REST_Response(array(
                'success' => true,
                'totals' => $totals,
                'timestamp' => current_time('timestamp')
            ), 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in get_cart_totals: ' . $e->getMessage(), 'error');
            return new WP_Error('cart_totals_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Calculate cart totals (force recalculation).
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function calculate_cart_totals($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $customer_location = $request->get_param('customer_location');
            
            $cart_manager = $this->get_cart_manager($terminal_id);
            
            if (is_wp_error($cart_manager)) {
                return $cart_manager;
            }

            // Set customer location if provided
            if (!empty($customer_location)) {
                $cart_manager->set_customer_location($customer_location);
            }

            // Force recalculation by clearing any cache
            if ($this->tax_calculator) {
                $this->tax_calculator->clear_cache();
            }

            $totals = $cart_manager->calculate_totals();

            return new WP_REST_Response(array(
                'success' => true,
                'totals' => $totals,
                'recalculated' => true,
                'timestamp' => current_time('timestamp')
            ), 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in calculate_cart_totals: ' . $e->getMessage(), 'error');
            return new WP_Error('cart_calculation_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Get cart tax information.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function get_cart_taxes($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $customer_location = $request->get_param('customer_location');
            
            $cart_manager = $this->get_cart_manager($terminal_id);
            
            if (is_wp_error($cart_manager)) {
                return $cart_manager;
            }

            // Set customer location if provided
            if (!empty($customer_location)) {
                $cart_manager->set_customer_location($customer_location);
            }

            $cart_contents = $cart_manager->get_cart_contents(true);
            $tax_data = array();

            if (isset($cart_contents['totals']['tax_lines'])) {
                $tax_data = array(
                    'tax_lines' => $cart_contents['totals']['tax_lines'],
                    'total_tax' => $cart_contents['totals']['total_tax'],
                    'subtotal_tax' => $cart_contents['totals']['subtotal_tax'],
                    'tax_included_in_price' => wc_prices_include_tax(),
                    'tax_display_mode' => get_option('woocommerce_tax_display_cart', 'excl')
                );
            }

            return new WP_REST_Response(array(
                'success' => true,
                'tax_data' => $tax_data,
                'customer_location' => $customer_location,
                'timestamp' => current_time('timestamp')
            ), 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in get_cart_taxes: ' . $e->getMessage(), 'error');
            return new WP_Error('cart_tax_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Apply discount/coupon to cart.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function apply_discount($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $coupon_code = $request->get_param('coupon_code');
            
            $cart_manager = $this->get_cart_manager($terminal_id);
            
            if (is_wp_error($cart_manager)) {
                return $cart_manager;
            }

            $result = $cart_manager->apply_coupon($coupon_code);
            
            if (is_wp_error($result)) {
                return $result;
            }

            return new WP_REST_Response($result, 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in apply_discount: ' . $e->getMessage(), 'error');
            return new WP_Error('discount_apply_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Remove discount/coupon from cart.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function remove_discount($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $coupon_code = $request->get_param('coupon_code');
            
            $cart_manager = $this->get_cart_manager($terminal_id);
            
            if (is_wp_error($cart_manager)) {
                return $cart_manager;
            }

            $result = $cart_manager->remove_coupon($coupon_code);

            return new WP_REST_Response($result, 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in remove_discount: ' . $e->getMessage(), 'error');
            return new WP_Error('discount_remove_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Create new cart session.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function create_session($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $user_id = get_current_user_id();
            
            $session_info = WUPOS_Session_Handler::create_api_session($terminal_id, $user_id);
            
            if (is_wp_error($session_info)) {
                return $session_info;
            }

            return new WP_REST_Response(array(
                'success' => true,
                'session' => $session_info,
                'message' => __('Session created successfully.', 'wupos')
            ), 201);

        } catch (Exception $e) {
            wupos_log('Cart API Error in create_session: ' . $e->getMessage(), 'error');
            return new WP_Error('session_create_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Validate cart session.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function validate_session($request) {
        try {
            $session_id = $request->get_param('session_id');
            $terminal_id = $request->get_param('terminal_id');
            
            $validation = WUPOS_Session_Handler::validate_api_session($session_id, $terminal_id);
            
            if (is_wp_error($validation)) {
                return $validation;
            }

            return new WP_REST_Response(array(
                'success' => true,
                'valid' => true,
                'message' => __('Session is valid.', 'wupos')
            ), 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in validate_session: ' . $e->getMessage(), 'error');
            return new WP_Error('session_validation_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Extend cart session.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function extend_session($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $additional_time = $request->get_param('additional_time');
            
            $session_handler = new WUPOS_Session_Handler($terminal_id);
            
            if (!$session_handler->is_session_valid()) {
                return new WP_Error('invalid_session', __('Session is not valid or has expired.', 'wupos'), array('status' => 400));
            }

            $result = $session_handler->extend_session($additional_time);
            
            if (!$result) {
                return new WP_Error('extension_failed', __('Failed to extend session.', 'wupos'), array('status' => 500));
            }

            return new WP_REST_Response(array(
                'success' => true,
                'extended_by' => $additional_time,
                'remaining_time' => $session_handler->get_session_remaining_time(),
                'message' => __('Session extended successfully.', 'wupos')
            ), 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in extend_session: ' . $e->getMessage(), 'error');
            return new WP_Error('session_extend_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Destroy cart session.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function destroy_session($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            
            $session_handler = new WUPOS_Session_Handler($terminal_id);
            $result = $session_handler->destroy_session();
            
            return new WP_REST_Response(array(
                'success' => $result,
                'message' => $result ? __('Session destroyed successfully.', 'wupos') : __('Failed to destroy session.', 'wupos')
            ), $result ? 200 : 500);

        } catch (Exception $e) {
            wupos_log('Cart API Error in destroy_session: ' . $e->getMessage(), 'error');
            return new WP_Error('session_destroy_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Set customer for cart.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function set_cart_customer($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $customer_id = $request->get_param('customer_id');
            
            $session_handler = new WUPOS_Session_Handler($terminal_id);
            
            if (!$session_handler->is_session_valid()) {
                return new WP_Error('invalid_session', __('Session is not valid or has expired.', 'wupos'), array('status' => 400));
            }

            $result = $session_handler->set_customer_id($customer_id);
            
            return new WP_REST_Response(array(
                'success' => $result,
                'customer_id' => $customer_id,
                'message' => $result ? __('Customer set successfully.', 'wupos') : __('Failed to set customer.', 'wupos')
            ), $result ? 200 : 500);

        } catch (Exception $e) {
            wupos_log('Cart API Error in set_cart_customer: ' . $e->getMessage(), 'error');
            return new WP_Error('customer_set_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Set customer location.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function set_customer_location($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $location = $request->get_param('location');
            
            $cart_manager = $this->get_cart_manager($terminal_id);
            
            if (is_wp_error($cart_manager)) {
                return $cart_manager;
            }

            $result = $cart_manager->set_customer_location($location);
            
            return new WP_REST_Response(array(
                'success' => $result,
                'location' => $location,
                'message' => $result ? __('Customer location set successfully.', 'wupos') : __('Failed to set customer location.', 'wupos')
            ), $result ? 200 : 500);

        } catch (Exception $e) {
            wupos_log('Cart API Error in set_customer_location: ' . $e->getMessage(), 'error');
            return new WP_Error('location_set_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Get cart manager instance.
     *
     * @param string $terminal_id Terminal ID
     * @return WUPOS_Cart_Manager|WP_Error Cart manager instance
     */
    private function get_cart_manager($terminal_id = '') {
        try {
            if (!class_exists('WUPOS_Cart_Manager')) {
                return new WP_Error('cart_manager_unavailable', 
                    __('Cart manager is not available.', 'wupos'), 
                    array('status' => 503));
            }

            return new WUPOS_Cart_Manager($terminal_id);

        } catch (Exception $e) {
            wupos_log('Error creating cart manager: ' . $e->getMessage(), 'error');
            return new WP_Error('cart_manager_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Check permissions for cart operations.
     *
     * @param WP_REST_Request $request Request object
     * @return bool True if user has permission
     */
    public function check_permissions($request) {
        // Check if user is logged in and has POS access
        if (!is_user_logged_in()) {
            return false;
        }

        // Use existing POS permission function
        return wupos_user_can_pos();
    }

    /**
     * Sanitize variation data.
     *
     * @param mixed $value Input value
     * @return array Sanitized variation data
     */
    public function sanitize_variation_data($value) {
        if (!is_array($value)) {
            return array();
        }

        $sanitized = array();
        foreach ($value as $key => $val) {
            $sanitized[sanitize_text_field($key)] = sanitize_text_field($val);
        }

        return $sanitized;
    }

    /**
     * Sanitize item data.
     *
     * @param mixed $value Input value
     * @return array Sanitized item data
     */
    public function sanitize_item_data($value) {
        if (!is_array($value)) {
            return array();
        }

        $sanitized = array();
        foreach ($value as $key => $val) {
            if (is_array($val)) {
                $sanitized[sanitize_text_field($key)] = array_map('sanitize_text_field', $val);
            } else {
                $sanitized[sanitize_text_field($key)] = sanitize_text_field($val);
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize location data.
     *
     * @param mixed $value Input value
     * @return array Sanitized location data
     */
    public function sanitize_location($value) {
        if (!is_array($value)) {
            return array();
        }

        return array(
            'country' => isset($value['country']) ? sanitize_text_field($value['country']) : '',
            'state' => isset($value['state']) ? sanitize_text_field($value['state']) : '',
            'postcode' => isset($value['postcode']) ? sanitize_text_field($value['postcode']) : '',
            'city' => isset($value['city']) ? sanitize_text_field($value['city']) : ''
        );
    }

    /**
     * Batch add items to cart.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function batch_add_items($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $items = $request->get_param('items');
            
            $cart_manager = $this->get_cart_manager($terminal_id);
            
            if (is_wp_error($cart_manager)) {
                return $cart_manager;
            }

            $result = $cart_manager->batch_add_to_cart($items);
            
            if (is_wp_error($result)) {
                return $result;
            }

            return new WP_REST_Response($result, 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in batch_add_items: ' . $e->getMessage(), 'error');
            return new WP_Error('batch_add_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Get cart summary (optimized).
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function get_cart_summary($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            
            $cart_manager = $this->get_cart_manager($terminal_id);
            
            if (is_wp_error($cart_manager)) {
                return $cart_manager;
            }

            $summary = $cart_manager->get_cart_summary();
            
            return new WP_REST_Response(array(
                'success' => true,
                'summary' => $summary,
                'timestamp' => current_time('timestamp')
            ), 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in get_cart_summary: ' . $e->getMessage(), 'error');
            return new WP_Error('cart_summary_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Check cart status for conflicts.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function check_cart_status($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            
            $cart_manager = $this->get_cart_manager($terminal_id);
            
            if (is_wp_error($cart_manager)) {
                return $cart_manager;
            }

            $status = $cart_manager->check_cart_status();
            
            return new WP_REST_Response(array(
                'success' => true,
                'status' => $status,
                'timestamp' => current_time('timestamp')
            ), 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in check_cart_status: ' . $e->getMessage(), 'error');
            return new WP_Error('cart_status_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Get performance metrics.
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response
     */
    public function get_performance_metrics($request) {
        try {
            $terminal_id = $request->get_param('terminal_id');
            $reset = $request->get_param('reset');
            
            $cart_manager = $this->get_cart_manager($terminal_id);
            
            if (is_wp_error($cart_manager)) {
                return $cart_manager;
            }

            $metrics = $cart_manager->get_performance_metrics();
            
            if ($reset) {
                $cart_manager->reset_performance_metrics();
            }
            
            return new WP_REST_Response(array(
                'success' => true,
                'metrics' => $metrics,
                'reset' => $reset,
                'timestamp' => current_time('timestamp')
            ), 200);

        } catch (Exception $e) {
            wupos_log('Cart API Error in get_performance_metrics: ' . $e->getMessage(), 'error');
            return new WP_Error('performance_metrics_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Sanitize batch items data.
     *
     * @param mixed $value Input value
     * @return array Sanitized batch items
     */
    public function sanitize_batch_items($value) {
        if (!is_array($value)) {
            return array();
        }

        $sanitized = array();
        foreach ($value as $item) {
            if (is_array($item)) {
                $sanitized[] = array(
                    'product_id' => isset($item['product_id']) ? absint($item['product_id']) : 0,
                    'quantity' => isset($item['quantity']) ? absint($item['quantity']) : 1,
                    'variation_id' => isset($item['variation_id']) ? absint($item['variation_id']) : 0,
                    'variation_data' => isset($item['variation_data']) ? $this->sanitize_variation_data($item['variation_data']) : array(),
                    'item_data' => isset($item['item_data']) ? $this->sanitize_item_data($item['item_data']) : array()
                );
            }
        }

        return $sanitized;
    }
}

// Initialize the Cart API
new WUPOS_Cart_API();