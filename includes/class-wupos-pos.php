<?php
/**
 * WUPOS POS Core Class
 *
 * @package WUPOS\POS
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS_POS class.
 */
class WUPOS_POS {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('wp_ajax_wupos_get_products', array($this, 'ajax_get_products'));
        add_action('wp_ajax_wupos_search_products', array($this, 'ajax_search_products'));
        add_action('wp_ajax_wupos_get_categories', array($this, 'ajax_get_categories'));
        add_action('wp_ajax_wupos_search_customers', array($this, 'ajax_search_customers'));
        add_action('wp_ajax_wupos_create_order', array($this, 'ajax_create_order'));
        add_action('wp_ajax_wupos_process_payment', array($this, 'ajax_process_payment'));
        add_action('wp_ajax_wupos_get_order', array($this, 'ajax_get_order'));
    }

    /**
     * Get products for POS via AJAX.
     */
    public function ajax_get_products() {
        // Verify nonce
        if (!wupos_verify_nonce()) {
            wp_die(__('Security check failed.', 'wupos'));
        }

        // Check capabilities
        if (!wupos_user_can_pos()) {
            wp_die(__('Insufficient permissions.', 'wupos'));
        }

        $page = intval($_POST['page'] ?? 1);
        $per_page = intval($_POST['per_page'] ?? 20);
        $search = sanitize_text_field($_POST['search'] ?? '');
        $category = intval($_POST['category'] ?? 0);

        $products = $this->get_products_for_pos($page, $per_page, $search, $category);

        wp_send_json_success($products);
    }

    /**
     * Search products via AJAX (for live search dropdown).
     */
    public function ajax_search_products() {
        // Verify nonce
        if (!wupos_verify_nonce()) {
            wp_die(__('Security check failed.', 'wupos'));
        }

        // Check capabilities
        if (!wupos_user_can_pos()) {
            wp_die(__('Insufficient permissions.', 'wupos'));
        }

        $search = sanitize_text_field($_POST['search'] ?? '');
        $limit = intval($_POST['limit'] ?? 10);

        if (empty($search) || strlen($search) < 2) {
            wp_send_json_success(array());
            return;
        }

        $products = $this->search_products_quick($search, $limit);
        wp_send_json_success($products);
    }

    /**
     * Get product categories via AJAX.
     */
    public function ajax_get_categories() {
        // Verify nonce
        if (!wupos_verify_nonce()) {
            wp_die(__('Security check failed.', 'wupos'));
        }

        // Check capabilities
        if (!wupos_user_can_pos()) {
            wp_die(__('Insufficient permissions.', 'wupos'));
        }

        $categories = $this->get_product_categories();
        wp_send_json_success($categories);
    }

    /**
     * Search customers via AJAX.
     */
    public function ajax_search_customers() {
        // Verify nonce
        if (!wupos_verify_nonce()) {
            wp_die(__('Security check failed.', 'wupos'));
        }

        // Check capabilities
        if (!wupos_user_can_pos()) {
            wp_die(__('Insufficient permissions.', 'wupos'));
        }

        $search = sanitize_text_field($_POST['search'] ?? '');
        $customers = $this->search_customers($search);

        wp_send_json_success($customers);
    }

    /**
     * Create order via AJAX.
     */
    public function ajax_create_order() {
        // Verify nonce
        if (!wupos_verify_nonce()) {
            wp_die(__('Security check failed.', 'wupos'));
        }

        // Check capabilities
        if (!wupos_user_can_pos()) {
            wp_die(__('Insufficient permissions.', 'wupos'));
        }

        $order_data = $_POST['order_data'] ?? array();
        $order_data = wupos_sanitize_input($order_data);

        try {
            $order = $this->create_pos_order($order_data);
            wp_send_json_success(array('order_id' => $order->get_id()));
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Process payment via AJAX.
     */
    public function ajax_process_payment() {
        // Verify nonce
        if (!wupos_verify_nonce()) {
            wp_die(__('Security check failed.', 'wupos'));
        }

        // Check capabilities
        if (!wupos_user_can_pos()) {
            wp_die(__('Insufficient permissions.', 'wupos'));
        }

        $order_id = intval($_POST['order_id'] ?? 0);
        $payment_data = $_POST['payment_data'] ?? array();
        $payment_data = wupos_sanitize_input($payment_data);

        try {
            $result = $this->process_pos_payment($order_id, $payment_data);
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Get order details via AJAX.
     */
    public function ajax_get_order() {
        // Verify nonce
        if (!wupos_verify_nonce()) {
            wp_die(__('Security check failed.', 'wupos'));
        }

        // Check capabilities
        if (!wupos_user_can_pos()) {
            wp_die(__('Insufficient permissions.', 'wupos'));
        }

        $order_id = intval($_POST['order_id'] ?? 0);
        
        try {
            $order_data = $this->get_pos_order_data($order_id);
            wp_send_json_success($order_data);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }


    /**
     * Search customers.
     */
    private function search_customers($search = '') {
        if (empty($search)) {
            return array();
        }

        $users = get_users(array(
            'search'         => '*' . $search . '*',
            'search_columns' => array('user_login', 'user_email', 'display_name'),
            'number'         => 20,
            'role__in'       => array('customer', 'subscriber'),
        ));

        $customers = array();
        
        foreach ($users as $user) {
            $customers[] = array(
                'id'           => $user->ID,
                'display_name' => $user->display_name,
                'email'        => $user->user_email,
                'first_name'   => get_user_meta($user->ID, 'first_name', true),
                'last_name'    => get_user_meta($user->ID, 'last_name', true),
            );
        }

        return $customers;
    }

    /**
     * Create POS order.
     */
    private function create_pos_order($order_data) {
        if (empty($order_data['line_items'])) {
            throw new Exception(__('No items in cart.', 'wupos'));
        }

        // Create the order
        $order = wc_create_order();
        
        if (is_wp_error($order)) {
            throw new Exception(__('Failed to create order.', 'wupos'));
        }

        // Set customer
        if (!empty($order_data['customer_id'])) {
            $order->set_customer_id(intval($order_data['customer_id']));
        }

        // Add line items
        foreach ($order_data['line_items'] as $item) {
            $product = wc_get_product($item['product_id']);
            
            if (!$product) {
                continue;
            }

            $order->add_product($product, $item['quantity']);
        }

        // Calculate totals
        $order->calculate_totals();

        // Add POS meta data
        $order->add_meta_data('_wupos_pos_order', 'yes');
        $order->add_meta_data('_wupos_terminal_id', $order_data['terminal_id'] ?? '');
        $order->add_meta_data('_wupos_cashier_id', get_current_user_id());
        $order->add_meta_data('_wupos_session_id', wupos_get_session_id());

        // Save the order
        $order->save();

        return $order;
    }

    /**
     * Process POS payment.
     */
    private function process_pos_payment($order_id, $payment_data) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            throw new Exception(__('Order not found.', 'wupos'));
        }

        $payment_method = sanitize_text_field($payment_data['method'] ?? 'cash');
        
        // Set payment method
        $order->set_payment_method($payment_method);
        
        // Add payment meta data
        if ($payment_method === 'cash') {
            $order->add_meta_data('_wupos_cash_received', floatval($payment_data['cash_received'] ?? 0));
            $order->add_meta_data('_wupos_change_given', floatval($payment_data['change_given'] ?? 0));
        }

        $order->add_meta_data('_wupos_payment_timestamp', current_time('timestamp'));

        // Complete the payment
        $order->payment_complete();
        $order->set_status('completed');
        $order->save();

        return array(
            'order_id' => $order->get_id(),
            'status'   => $order->get_status(),
            'total'    => $order->get_total(),
        );
    }

    /**
     * Get POS order data.
     */
    private function get_pos_order_data($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            throw new Exception(__('Order not found.', 'wupos'));
        }

        $order_data = array(
            'id'           => $order->get_id(),
            'status'       => $order->get_status(),
            'total'        => $order->get_total(),
            'subtotal'     => $order->get_subtotal(),
            'tax_total'    => $order->get_total_tax(),
            'currency'     => $order->get_currency(),
            'payment_method' => $order->get_payment_method(),
            'date_created' => $order->get_date_created()->format('Y-m-d H:i:s'),
            'line_items'   => array(),
            'customer'     => array(),
            'pos_data'     => array(),
        );

        // Get line items
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $order_data['line_items'][] = array(
                'name'     => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'total'    => $item->get_total(),
                'sku'      => $product ? $product->get_sku() : '',
            );
        }

        // Get customer data
        if ($order->get_customer_id()) {
            $customer = new WC_Customer($order->get_customer_id());
            $order_data['customer'] = array(
                'id'         => $customer->get_id(),
                'email'      => $customer->get_email(),
                'first_name' => $customer->get_first_name(),
                'last_name'  => $customer->get_last_name(),
            );
        }

        // Get POS specific data
        $order_data['pos_data'] = array(
            'terminal_id'  => $order->get_meta('_wupos_terminal_id'),
            'cashier_id'   => $order->get_meta('_wupos_cashier_id'),
            'session_id'   => $order->get_meta('_wupos_session_id'),
            'cash_received' => $order->get_meta('_wupos_cash_received'),
            'change_given' => $order->get_meta('_wupos_change_given'),
        );

        return $order_data;
    }

    /**
     * Quick product search for live search dropdown.
     */
    private function search_products_quick($search, $limit = 10) {
        $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            's'              => $search,
            'meta_query'     => array(
                array(
                    'key'     => '_stock_status',
                    'value'   => array('instock', 'onbackorder'),
                    'compare' => 'IN',
                ),
            ),
        );

        // Also search by SKU
        $sku_query = new WP_Query(array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'meta_query'     => array(
                array(
                    'key'     => '_sku',
                    'value'   => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key'     => '_stock_status',
                    'value'   => array('instock', 'onbackorder'),
                    'compare' => 'IN',
                ),
            ),
        ));

        $query = new WP_Query($args);
        $products = array();
        $product_ids = array();

        // Get products from name search
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product = wc_get_product(get_the_ID());
                
                if (!$product || in_array($product->get_id(), $product_ids)) {
                    continue;
                }

                $product_ids[] = $product->get_id();
                $products[] = array(
                    'id'           => $product->get_id(),
                    'name'         => $product->get_name(),
                    'sku'          => $product->get_sku(),
                    'price'        => $product->get_price(),
                    'stock_status' => $product->get_stock_status(),
                    'image_url'    => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
                );
            }
        }

        // Add products from SKU search if we haven't reached the limit
        if (count($products) < $limit && $sku_query->have_posts()) {
            while ($sku_query->have_posts() && count($products) < $limit) {
                $sku_query->the_post();
                $product = wc_get_product(get_the_ID());
                
                if (!$product || in_array($product->get_id(), $product_ids)) {
                    continue;
                }

                $products[] = array(
                    'id'           => $product->get_id(),
                    'name'         => $product->get_name(),
                    'sku'          => $product->get_sku(),
                    'price'        => $product->get_price(),
                    'stock_status' => $product->get_stock_status(),
                    'image_url'    => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
                );
            }
        }

        wp_reset_postdata();

        return $products;
    }

    /**
     * Get product categories with counts.
     */
    private function get_product_categories() {
        $terms = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ));

        $categories = array();

        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $categories[] = array(
                    'term_id'     => $term->term_id,
                    'name'        => $term->name,
                    'slug'        => $term->slug,
                    'count'       => $term->count,
                    'parent'      => $term->parent,
                    'description' => $term->description,
                );
            }
        }

        return $categories;
    }

    /**
     * Enhanced products query with better performance.
     */
    private function get_products_for_pos($page = 1, $per_page = 20, $search = '', $category = 0) {
        $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
            'meta_query'     => array(
                array(
                    'key'     => '_visibility',
                    'value'   => array('visible', 'catalog'),
                    'compare' => 'IN',
                ),
            ),
        );

        // Add search
        if (!empty($search)) {
            $args['s'] = $search;
            
            // Also search by SKU in meta query
            $args['meta_query'][] = array(
                'relation' => 'OR',
                array(
                    'key'     => '_sku',
                    'value'   => $search,
                    'compare' => 'LIKE',
                ),
            );
        }

        // Add category filter
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
                
                if (!$product) {
                    continue;
                }

                $products[] = array(
                    'id'             => $product->get_id(),
                    'name'           => $product->get_name(),
                    'sku'            => $product->get_sku(),
                    'price'          => $product->get_price(),
                    'regular_price'  => $product->get_regular_price(),
                    'sale_price'     => $product->get_sale_price(),
                    'stock_quantity' => $product->get_stock_quantity(),
                    'manage_stock'   => $product->get_manage_stock(),
                    'stock_status'   => $product->get_stock_status(),
                    'image_url'      => wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail'),
                    'categories'     => wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names')),
                    'type'           => $product->get_type(),
                    'featured'       => $product->is_featured(),
                );
            }
        }

        wp_reset_postdata();

        return array(
            'products'       => $products,
            'total_pages'    => $query->max_num_pages,
            'current_page'   => $page,
            'total_products' => $query->found_posts,
            'per_page'       => $per_page,
        );
    }
}

return new WUPOS_POS();