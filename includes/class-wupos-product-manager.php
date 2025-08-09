<?php
/**
 * WUPOS Product Manager
 *
 * Handles WooCommerce product integration, caching, and optimization
 * for the POS system. Acts as a wrapper for WooCommerce native APIs.
 *
 * @package WUPOS\ProductManager
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS_Product_Manager class.
 *
 * Manages WooCommerce product data integration with high performance
 * caching and real-time inventory synchronization.
 */
class WUPOS_Product_Manager {

    /**
     * Cache manager instance
     *
     * @var WUPOS_Cache_Manager
     */
    private $cache_manager;

    /**
     * Inventory sync instance
     *
     * @var WUPOS_Inventory_Sync
     */
    private $inventory_sync;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->cache_manager = new WUPOS_Cache_Manager();
        $this->inventory_sync = new WUPOS_Inventory_Sync();
        
        $this->init_hooks();
    }

    /**
     * Initialize hooks for product data synchronization
     */
    private function init_hooks() {
        // Cache invalidation hooks
        add_action('woocommerce_update_product', array($this, 'invalidate_product_cache'), 10, 1);
        add_action('woocommerce_product_set_stock', array($this, 'invalidate_product_cache'), 10, 1);
        add_action('woocommerce_variation_set_stock', array($this, 'invalidate_product_cache'), 10, 1);
        
        // Category cache invalidation
        add_action('create_product_cat', array($this, 'invalidate_category_cache'));
        add_action('edit_product_cat', array($this, 'invalidate_category_cache'));
        add_action('delete_product_cat', array($this, 'invalidate_category_cache'));
        
        // Stock change hooks for real-time sync
        add_action('woocommerce_reduce_order_stock', array($this->inventory_sync, 'handle_stock_reduction'), 10, 1);
        add_action('woocommerce_restore_order_stock', array($this->inventory_sync, 'handle_stock_restoration'), 10, 1);
    }

    /**
     * Get products with advanced filtering and caching
     *
     * @param array $args Query arguments
     * @return array Products data with pagination info
     */
    public function get_products($args = array()) {
        $start_time = microtime(true);
        
        // Default arguments
        $defaults = array(
            'page'          => 1,
            'per_page'      => 20,
            'search'        => '',
            'category'      => 0,
            'stock_status'  => 'instock',
            'orderby'       => 'date',
            'order'         => 'DESC',
            'include_variations' => false,
            'meta_query'    => array(),
            'tax_query'     => array(),
        );

        $args = wp_parse_args($args, $defaults);
        
        // Generate cache key based on arguments
        $cache_key = 'wupos_products_' . md5(serialize($args));
        
        // Try to get from cache first
        $cached_data = $this->cache_manager->get_product_cache($cache_key);
        if (false !== $cached_data) {
            wupos_log(sprintf('Products loaded from cache in %s seconds', number_format(microtime(true) - $start_time, 4)), 'debug');
            return $cached_data;
        }

        try {
            // Build WooCommerce product query
            $product_args = $this->build_product_query_args($args);
            
            // Execute query
            $query = new WP_Query($product_args);
            $products = array();
            $product_ids = array();

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $product_id = get_the_ID();
                    $product_ids[] = $product_id;
                    
                    $product = wc_get_product($product_id);
                    if ($product && $this->is_product_valid_for_pos($product)) {
                        $products[] = $this->prepare_product_data($product);
                    }
                }
                wp_reset_postdata();
            }

            // Prepare response data
            $response_data = array(
                'products'       => $products,
                'total_pages'    => $query->max_num_pages,
                'current_page'   => (int) $args['page'],
                'total_products' => (int) $query->found_posts,
                'per_page'       => (int) $args['per_page'],
                'query_time'     => number_format(microtime(true) - $start_time, 4),
                'from_cache'     => false,
                'product_ids'    => $product_ids, // For debugging
            );

            // Cache the results (5 minutes for products)
            $this->cache_manager->set_product_cache($cache_key, $response_data, 300);
            
            wupos_log(sprintf('Products loaded from database in %s seconds (%d products)', 
                $response_data['query_time'], 
                count($products)
            ), 'debug');

            return $response_data;

        } catch (Exception $e) {
            wupos_log('Error in get_products: ' . $e->getMessage(), 'error');
            return $this->get_error_response('product_query_failed', $e->getMessage());
        }
    }

    /**
     * Get single product by ID with caching
     *
     * @param int $product_id Product ID
     * @return array|WP_Error Product data or error
     */
    public function get_product($product_id) {
        $product_id = absint($product_id);
        
        if (!$product_id) {
            return new WP_Error('invalid_product_id', __('Invalid product ID.', 'wupos'), array('status' => 400));
        }

        $cache_key = 'wupos_product_' . $product_id;
        $cached_data = $this->cache_manager->get_product_cache($cache_key);
        
        if (false !== $cached_data) {
            return $cached_data;
        }

        try {
            $product = wc_get_product($product_id);
            
            if (!$product || !$this->is_product_valid_for_pos($product)) {
                return new WP_Error('product_not_found', __('Product not found or not available for POS.', 'wupos'), array('status' => 404));
            }

            $product_data = $this->prepare_product_data($product, true); // Include extended data
            
            // Cache single product for 5 minutes
            $this->cache_manager->set_product_cache($cache_key, $product_data, 300);
            
            return $product_data;

        } catch (Exception $e) {
            wupos_log('Error in get_product: ' . $e->getMessage(), 'error');
            return new WP_Error('product_fetch_failed', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Search products by name, SKU, or barcode
     *
     * @param string $search_term Search term
     * @param array $args Additional arguments
     * @return array Search results
     */
    public function search_products($search_term, $args = array()) {
        $search_term = sanitize_text_field($search_term);
        
        if (empty($search_term) || strlen($search_term) < 2) {
            return array(
                'products' => array(),
                'total' => 0,
                'search_term' => $search_term,
            );
        }

        $defaults = array(
            'limit' => 50,
            'include_variations' => true,
            'search_fields' => array('name', 'sku', 'barcode'),
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Cache key for search results (shorter cache time)
        $cache_key = 'wupos_search_' . md5($search_term . serialize($args));
        $cached_results = $this->cache_manager->get_product_cache($cache_key);
        
        if (false !== $cached_results) {
            return $cached_results;
        }

        try {
            $products = array();
            
            // Search by SKU first (exact match priority)
            if (in_array('sku', $args['search_fields'])) {
                $sku_products = $this->search_by_sku($search_term, $args['limit']);
                $products = array_merge($products, $sku_products);
            }
            
            // Search by barcode
            if (in_array('barcode', $args['search_fields']) && count($products) < $args['limit']) {
                $barcode_products = $this->search_by_barcode($search_term, $args['limit'] - count($products));
                $products = array_merge($products, $barcode_products);
            }
            
            // Search by name/title
            if (in_array('name', $args['search_fields']) && count($products) < $args['limit']) {
                $name_products = $this->search_by_name($search_term, $args['limit'] - count($products));
                $products = array_merge($products, $name_products);
            }
            
            // Remove duplicates based on product ID
            $unique_products = array();
            $seen_ids = array();
            
            foreach ($products as $product_data) {
                if (!in_array($product_data['id'], $seen_ids)) {
                    $unique_products[] = $product_data;
                    $seen_ids[] = $product_data['id'];
                }
            }
            
            $search_results = array(
                'products' => array_slice($unique_products, 0, $args['limit']),
                'total' => count($unique_products),
                'search_term' => $search_term,
                'search_fields' => $args['search_fields'],
                'from_cache' => false,
            );
            
            // Cache search results for 2 minutes
            $this->cache_manager->set_product_cache($cache_key, $search_results, 120);
            
            return $search_results;

        } catch (Exception $e) {
            wupos_log('Error in search_products: ' . $e->getMessage(), 'error');
            return array(
                'products' => array(),
                'total' => 0,
                'error' => $e->getMessage(),
            );
        }
    }

    /**
     * Get product categories with hierarchical structure
     *
     * @param array $args Query arguments
     * @return array Categories data
     */
    public function get_categories($args = array()) {
        $defaults = array(
            'hide_empty' => false,
            'hierarchical' => true,
            'include_count' => true,
        );
        
        $args = wp_parse_args($args, $defaults);
        $cache_key = 'wupos_categories_' . md5(serialize($args));
        
        // Try cache first (15 minutes for categories)
        $cached_categories = $this->cache_manager->get_product_cache($cache_key);
        if (false !== $cached_categories) {
            return $cached_categories;
        }

        try {
            $terms_args = array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => $args['hide_empty'],
                'orderby'    => 'name',
                'order'      => 'ASC',
            );

            $terms = get_terms($terms_args);
            
            if (is_wp_error($terms)) {
                throw new Exception($terms->get_error_message());
            }

            $categories = array();
            
            foreach ($terms as $term) {
                $category_data = array(
                    'id'          => $term->term_id,
                    'name'        => $term->name,
                    'slug'        => $term->slug,
                    'parent'      => $term->parent,
                    'description' => $term->description,
                    'count'       => $args['include_count'] ? $term->count : null,
                    'image_url'   => $this->get_category_image_url($term->term_id),
                );
                
                $categories[] = $category_data;
            }
            
            // Build hierarchical structure if requested
            if ($args['hierarchical']) {
                $categories = $this->build_category_hierarchy($categories);
            }
            
            $response_data = array(
                'categories' => $categories,
                'total' => count($categories),
                'from_cache' => false,
            );
            
            // Cache for 15 minutes
            $this->cache_manager->set_product_cache($cache_key, $response_data, 900);
            
            return $response_data;

        } catch (Exception $e) {
            wupos_log('Error in get_categories: ' . $e->getMessage(), 'error');
            return array(
                'categories' => array(),
                'total' => 0,
                'error' => $e->getMessage(),
            );
        }
    }

    /**
     * Get real-time stock information for a product
     *
     * @param int $product_id Product ID
     * @return array Stock information
     */
    public function get_stock_info($product_id) {
        return $this->inventory_sync->get_real_time_stock($product_id);
    }

    /**
     * Update product stock with conflict resolution
     *
     * @param int $product_id Product ID
     * @param int $quantity New quantity
     * @param string $operation Operation type (set, increase, decrease)
     * @return array|WP_Error Operation result
     */
    public function update_stock($product_id, $quantity, $operation = 'set') {
        return $this->inventory_sync->update_stock($product_id, $quantity, $operation);
    }

    /**
     * Build WooCommerce product query arguments
     *
     * @param array $args Input arguments
     * @return array WP_Query arguments
     */
    private function build_product_query_args($args) {
        $product_args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => (int) $args['per_page'],
            'paged'          => (int) $args['page'],
            'orderby'        => sanitize_text_field($args['orderby']),
            'order'          => sanitize_text_field($args['order']),
            'fields'         => 'ids', // Only get IDs for better performance
        );

        // Search query
        if (!empty($args['search'])) {
            $product_args['s'] = sanitize_text_field($args['search']);
        }

        // Category filter
        if ($args['category'] > 0) {
            $product_args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => (int) $args['category'],
                ),
            );
        }

        // Stock status filter
        if (!empty($args['stock_status'])) {
            $product_args['meta_query'] = array(
                array(
                    'key'     => '_stock_status',
                    'value'   => sanitize_text_field($args['stock_status']),
                    'compare' => '=',
                ),
            );
        }

        // Additional meta queries
        if (!empty($args['meta_query']) && is_array($args['meta_query'])) {
            if (isset($product_args['meta_query'])) {
                $product_args['meta_query'] = array_merge($product_args['meta_query'], $args['meta_query']);
            } else {
                $product_args['meta_query'] = $args['meta_query'];
            }
        }

        // Additional tax queries
        if (!empty($args['tax_query']) && is_array($args['tax_query'])) {
            if (isset($product_args['tax_query'])) {
                $product_args['tax_query'] = array_merge($product_args['tax_query'], $args['tax_query']);
            } else {
                $product_args['tax_query'] = $args['tax_query'];
            }
        }

        // Include variations if requested
        if ($args['include_variations']) {
            $product_args['post_type'] = array('product', 'product_variation');
        }

        return $product_args;
    }

    /**
     * Prepare product data for API response
     *
     * @param WC_Product $product WooCommerce product object
     * @param bool $extended Include extended data
     * @return array Prepared product data
     */
    private function prepare_product_data($product, $extended = false) {
        $product_data = array(
            'id'             => $product->get_id(),
            'name'           => $product->get_name(),
            'sku'            => $product->get_sku(),
            'price'          => (float) $product->get_price(),
            'regular_price'  => (float) $product->get_regular_price(),
            'sale_price'     => $product->get_sale_price() ? (float) $product->get_sale_price() : null,
            'stock_quantity' => $product->get_stock_quantity(),
            'manage_stock'   => $product->get_manage_stock(),
            'stock_status'   => $product->get_stock_status(),
            'type'           => $product->get_type(),
            'status'         => $product->get_status(),
            'featured'       => $product->is_featured(),
            'catalog_visibility' => $product->get_catalog_visibility(),
        );

        // Add image information
        $image_id = $product->get_image_id();
        $product_data['image'] = array(
            'id'  => $image_id,
            'url' => $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : wc_placeholder_img_src('thumbnail'),
            'alt' => $image_id ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : $product->get_name(),
        );

        // Add categories
        $categories = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'all'));
        $product_data['categories'] = array();
        
        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $product_data['categories'][] = array(
                    'id'   => $category->term_id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                );
            }
        }

        // Extended data for single product requests
        if ($extended) {
            $product_data = array_merge($product_data, array(
                'description'       => $product->get_description(),
                'short_description' => $product->get_short_description(),
                'weight'            => $product->get_weight(),
                'dimensions'        => array(
                    'length' => $product->get_length(),
                    'width'  => $product->get_width(),
                    'height' => $product->get_height(),
                ),
                'tax_status'        => $product->get_tax_status(),
                'tax_class'         => $product->get_tax_class(),
                'barcode'           => get_post_meta($product->get_id(), '_barcode', true),
                'supplier'          => get_post_meta($product->get_id(), '_supplier', true),
                'cost_price'        => get_post_meta($product->get_id(), '_cost_price', true),
            ));

            // Add gallery images
            $gallery_ids = $product->get_gallery_image_ids();
            $product_data['gallery'] = array();
            
            foreach ($gallery_ids as $gallery_id) {
                $product_data['gallery'][] = array(
                    'id'  => $gallery_id,
                    'url' => wp_get_attachment_image_url($gallery_id, 'thumbnail'),
                    'alt' => get_post_meta($gallery_id, '_wp_attachment_image_alt', true),
                );
            }

            // Add variation data for variable products
            if ($product->is_type('variable')) {
                $product_data['variations'] = $this->get_product_variations($product);
            }
        }

        return $product_data;
    }

    /**
     * Get product variations for variable products
     *
     * @param WC_Product $product Variable product
     * @return array Variations data
     */
    private function get_product_variations($product) {
        if (!$product->is_type('variable')) {
            return array();
        }

        $variations_data = array();
        $variations = $product->get_available_variations();

        foreach ($variations as $variation) {
            $variation_obj = wc_get_product($variation['variation_id']);
            if ($variation_obj) {
                $variations_data[] = array(
                    'id'             => $variation_obj->get_id(),
                    'sku'            => $variation_obj->get_sku(),
                    'price'          => (float) $variation_obj->get_price(),
                    'regular_price'  => (float) $variation_obj->get_regular_price(),
                    'sale_price'     => $variation_obj->get_sale_price() ? (float) $variation_obj->get_sale_price() : null,
                    'stock_quantity' => $variation_obj->get_stock_quantity(),
                    'stock_status'   => $variation_obj->get_stock_status(),
                    'attributes'     => $variation_obj->get_variation_attributes(),
                    'image'          => array(
                        'id'  => $variation_obj->get_image_id(),
                        'url' => wp_get_attachment_image_url($variation_obj->get_image_id(), 'thumbnail'),
                    ),
                );
            }
        }

        return $variations_data;
    }

    /**
     * Check if product is valid for POS
     *
     * @param WC_Product $product Product object
     * @return bool True if valid
     */
    private function is_product_valid_for_pos($product) {
        // Basic validation
        if (!$product || !$product->exists()) {
            return false;
        }

        // Must be published
        if ($product->get_status() !== 'publish') {
            return false;
        }

        // Must be purchasable
        if (!$product->is_purchasable()) {
            return false;
        }

        // Must be visible in catalog
        if ($product->get_catalog_visibility() === 'hidden') {
            return false;
        }

        // Allow filtering
        return apply_filters('wupos_is_product_valid_for_pos', true, $product);
    }

    /**
     * Search products by SKU
     *
     * @param string $sku SKU to search
     * @param int $limit Results limit
     * @return array Products found
     */
    private function search_by_sku($sku, $limit = 10) {
        global $wpdb;

        $sku = sanitize_text_field($sku);
        $products = array();

        // Exact SKU match first
        $exact_query = $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = '_sku' 
             AND meta_value = %s 
             LIMIT %d",
            $sku,
            $limit
        );

        $exact_results = $wpdb->get_col($exact_query);

        foreach ($exact_results as $post_id) {
            $product = wc_get_product($post_id);
            if ($product && $this->is_product_valid_for_pos($product)) {
                $products[] = $this->prepare_product_data($product);
            }
        }

        // If not enough results, try partial match
        if (count($products) < $limit) {
            $like_query = $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} 
                 WHERE meta_key = '_sku' 
                 AND meta_value LIKE %s 
                 AND post_id NOT IN (" . implode(',', array_merge($exact_results, array(0))) . ")
                 LIMIT %d",
                '%' . $wpdb->esc_like($sku) . '%',
                $limit - count($products)
            );

            $like_results = $wpdb->get_col($like_query);

            foreach ($like_results as $post_id) {
                $product = wc_get_product($post_id);
                if ($product && $this->is_product_valid_for_pos($product)) {
                    $products[] = $this->prepare_product_data($product);
                }
            }
        }

        return $products;
    }

    /**
     * Search products by barcode
     *
     * @param string $barcode Barcode to search
     * @param int $limit Results limit
     * @return array Products found
     */
    private function search_by_barcode($barcode, $limit = 10) {
        global $wpdb;

        $barcode = sanitize_text_field($barcode);
        $products = array();

        $query = $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = '_barcode' 
             AND meta_value = %s 
             LIMIT %d",
            $barcode,
            $limit
        );

        $results = $wpdb->get_col($query);

        foreach ($results as $post_id) {
            $product = wc_get_product($post_id);
            if ($product && $this->is_product_valid_for_pos($product)) {
                $products[] = $this->prepare_product_data($product);
            }
        }

        return $products;
    }

    /**
     * Search products by name
     *
     * @param string $name Name to search
     * @param int $limit Results limit
     * @return array Products found
     */
    private function search_by_name($name, $limit = 10) {
        $args = array(
            'post_type'      => array('product', 'product_variation'),
            'post_status'    => 'publish',
            's'              => sanitize_text_field($name),
            'posts_per_page' => $limit,
            'fields'         => 'ids',
        );

        $query = new WP_Query($args);
        $products = array();

        if ($query->have_posts()) {
            foreach ($query->posts as $post_id) {
                $product = wc_get_product($post_id);
                if ($product && $this->is_product_valid_for_pos($product)) {
                    $products[] = $this->prepare_product_data($product);
                }
            }
        }

        wp_reset_postdata();
        return $products;
    }

    /**
     * Get category image URL
     *
     * @param int $term_id Category term ID
     * @return string Image URL
     */
    private function get_category_image_url($term_id) {
        $image_id = get_term_meta($term_id, 'thumbnail_id', true);
        if ($image_id) {
            return wp_get_attachment_image_url($image_id, 'thumbnail');
        }
        return '';
    }

    /**
     * Build hierarchical category structure
     *
     * @param array $categories Flat categories array
     * @param int $parent_id Parent ID
     * @return array Hierarchical structure
     */
    private function build_category_hierarchy($categories, $parent_id = 0) {
        $hierarchy = array();

        foreach ($categories as $category) {
            if ($category['parent'] == $parent_id) {
                $children = $this->build_category_hierarchy($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $hierarchy[] = $category;
            }
        }

        return $hierarchy;
    }

    /**
     * Invalidate product cache
     *
     * @param int $product_id Product ID
     */
    public function invalidate_product_cache($product_id) {
        $this->cache_manager->invalidate_product_cache($product_id);
    }

    /**
     * Invalidate category cache
     */
    public function invalidate_category_cache() {
        $this->cache_manager->invalidate_category_cache();
    }

    /**
     * Get error response structure
     *
     * @param string $code Error code
     * @param string $message Error message
     * @return array Error response
     */
    private function get_error_response($code, $message) {
        return array(
            'error' => true,
            'code' => $code,
            'message' => $message,
            'data' => array(),
        );
    }
}