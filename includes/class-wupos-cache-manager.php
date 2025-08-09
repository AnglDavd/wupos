<?php
/**
 * WUPOS Cache Manager
 *
 * Handles high-performance caching using WordPress transients
 * with intelligent cache invalidation and memory management.
 *
 * @package WUPOS\Cache
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS_Cache_Manager class.
 *
 * Manages caching for products, categories, and other POS data
 * using WordPress transients with optimized performance.
 */
class WUPOS_Cache_Manager {

    /**
     * Cache group prefixes
     */
    const CACHE_GROUP_PRODUCTS = 'wupos_products';
    const CACHE_GROUP_CATEGORIES = 'wupos_categories';
    const CACHE_GROUP_CUSTOMERS = 'wupos_customers';
    const CACHE_GROUP_STOCK = 'wupos_stock';
    const CACHE_GROUP_SEARCH = 'wupos_search';

    /**
     * Default cache expiration times (in seconds)
     */
    const CACHE_PRODUCTS_TTL = 300;     // 5 minutes
    const CACHE_CATEGORIES_TTL = 900;   // 15 minutes
    const CACHE_CUSTOMERS_TTL = 600;    // 10 minutes
    const CACHE_STOCK_TTL = 60;         // 1 minute
    const CACHE_SEARCH_TTL = 120;       // 2 minutes

    /**
     * Maximum cache size tracking
     */
    private $cache_stats = array();
    
    /**
     * Cache hit/miss statistics
     */
    private $stats = array(
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'deletes' => 0,
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->init_cache_stats();
    }

    /**
     * Initialize hooks for cache management
     */
    private function init_hooks() {
        // Clean up expired cache on scheduled basis
        add_action('wp_scheduled_delete', array($this, 'cleanup_expired_cache'));
        
        // Monitor cache usage
        add_action('shutdown', array($this, 'log_cache_stats'));
        
        // Clear all caches on specific events
        add_action('wupos_clear_all_cache', array($this, 'clear_all_cache'));
        
        // Cache warming
        add_action('wupos_warm_cache', array($this, 'warm_product_cache'));
    }

    /**
     * Initialize cache statistics
     */
    private function init_cache_stats() {
        $this->cache_stats = get_transient('wupos_cache_stats') ?: array(
            'total_size' => 0,
            'groups' => array(),
            'last_cleanup' => time(),
        );
    }

    /**
     * Get product cache
     *
     * @param string $key Cache key
     * @return mixed Cache data or false if not found
     */
    public function get_product_cache($key) {
        return $this->get_cache(self::CACHE_GROUP_PRODUCTS, $key);
    }

    /**
     * Set product cache
     *
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @param int $expiration Expiration time in seconds
     * @return bool Success status
     */
    public function set_product_cache($key, $data, $expiration = null) {
        if (null === $expiration) {
            $expiration = self::CACHE_PRODUCTS_TTL;
        }
        return $this->set_cache(self::CACHE_GROUP_PRODUCTS, $key, $data, $expiration);
    }

    /**
     * Get category cache
     *
     * @param string $key Cache key
     * @return mixed Cache data or false if not found
     */
    public function get_category_cache($key) {
        return $this->get_cache(self::CACHE_GROUP_CATEGORIES, $key);
    }

    /**
     * Set category cache
     *
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @param int $expiration Expiration time in seconds
     * @return bool Success status
     */
    public function set_category_cache($key, $data, $expiration = null) {
        if (null === $expiration) {
            $expiration = self::CACHE_CATEGORIES_TTL;
        }
        return $this->set_cache(self::CACHE_GROUP_CATEGORIES, $key, $data, $expiration);
    }

    /**
     * Get customer cache
     *
     * @param string $key Cache key
     * @return mixed Cache data or false if not found
     */
    public function get_customer_cache($key) {
        return $this->get_cache(self::CACHE_GROUP_CUSTOMERS, $key);
    }

    /**
     * Set customer cache
     *
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @param int $expiration Expiration time in seconds
     * @return bool Success status
     */
    public function set_customer_cache($key, $data, $expiration = null) {
        if (null === $expiration) {
            $expiration = self::CACHE_CUSTOMERS_TTL;
        }
        return $this->set_cache(self::CACHE_GROUP_CUSTOMERS, $key, $data, $expiration);
    }

    /**
     * Get stock cache
     *
     * @param string $key Cache key
     * @return mixed Cache data or false if not found
     */
    public function get_stock_cache($key) {
        return $this->get_cache(self::CACHE_GROUP_STOCK, $key);
    }

    /**
     * Set stock cache
     *
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @param int $expiration Expiration time in seconds
     * @return bool Success status
     */
    public function set_stock_cache($key, $data, $expiration = null) {
        if (null === $expiration) {
            $expiration = self::CACHE_STOCK_TTL;
        }
        return $this->set_cache(self::CACHE_GROUP_STOCK, $key, $data, $expiration);
    }

    /**
     * Generic cache get method
     *
     * @param string $group Cache group
     * @param string $key Cache key
     * @return mixed Cache data or false if not found
     */
    private function get_cache($group, $key) {
        $cache_key = $this->build_cache_key($group, $key);
        $data = get_transient($cache_key);
        
        if (false !== $data) {
            $this->stats['hits']++;
            wupos_log("Cache HIT: {$cache_key}", 'debug');
            
            // Update access time for LRU tracking
            $this->update_access_time($cache_key);
            
            return $data;
        }
        
        $this->stats['misses']++;
        wupos_log("Cache MISS: {$cache_key}", 'debug');
        
        return false;
    }

    /**
     * Generic cache set method
     *
     * @param string $group Cache group
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @param int $expiration Expiration time in seconds
     * @return bool Success status
     */
    private function set_cache($group, $key, $data, $expiration) {
        $cache_key = $this->build_cache_key($group, $key);
        
        // Check cache size limits before setting
        if (!$this->check_cache_limits($group, $data)) {
            wupos_log("Cache limit exceeded for group: {$group}", 'warning');
            $this->cleanup_group_cache($group);
        }
        
        $success = set_transient($cache_key, $data, $expiration);
        
        if ($success) {
            $this->stats['sets']++;
            $this->update_cache_stats($group, $cache_key, $data, $expiration);
            wupos_log("Cache SET: {$cache_key} (expires in {$expiration}s)", 'debug');
        }
        
        return $success;
    }

    /**
     * Build cache key with group prefix
     *
     * @param string $group Cache group
     * @param string $key Original key
     * @return string Full cache key
     */
    private function build_cache_key($group, $key) {
        // Add current user ID for user-specific caching if needed
        $user_suffix = '';
        if (in_array($group, array(self::CACHE_GROUP_CUSTOMERS))) {
            $user_suffix = '_u' . get_current_user_id();
        }
        
        return sprintf('%s_%s%s', $group, $key, $user_suffix);
    }

    /**
     * Update access time for LRU cache management
     *
     * @param string $cache_key Cache key
     */
    private function update_access_time($cache_key) {
        $access_key = $cache_key . '_access';
        set_transient($access_key, time(), DAY_IN_SECONDS);
    }

    /**
     * Update cache statistics
     *
     * @param string $group Cache group
     * @param string $key Cache key
     * @param mixed $data Cached data
     * @param int $expiration Expiration time
     */
    private function update_cache_stats($group, $key, $data, $expiration) {
        $size = $this->estimate_data_size($data);
        
        if (!isset($this->cache_stats['groups'][$group])) {
            $this->cache_stats['groups'][$group] = array(
                'count' => 0,
                'size' => 0,
                'last_set' => 0,
            );
        }
        
        $this->cache_stats['groups'][$group]['count']++;
        $this->cache_stats['groups'][$group]['size'] += $size;
        $this->cache_stats['groups'][$group]['last_set'] = time();
        $this->cache_stats['total_size'] += $size;
        
        // Save stats periodically
        if ($this->cache_stats['groups'][$group]['count'] % 10 === 0) {
            set_transient('wupos_cache_stats', $this->cache_stats, HOUR_IN_SECONDS);
        }
    }

    /**
     * Estimate data size in bytes
     *
     * @param mixed $data Data to estimate
     * @return int Estimated size in bytes
     */
    private function estimate_data_size($data) {
        return strlen(serialize($data));
    }

    /**
     * Check cache size limits
     *
     * @param string $group Cache group
     * @param mixed $data Data to be cached
     * @return bool True if within limits
     */
    private function check_cache_limits($group, $data) {
        $max_total_size = apply_filters('wupos_max_cache_size', 50 * 1024 * 1024); // 50MB default
        $max_group_items = apply_filters('wupos_max_cache_group_items', array(
            self::CACHE_GROUP_PRODUCTS => 1000,
            self::CACHE_GROUP_CATEGORIES => 200,
            self::CACHE_GROUP_CUSTOMERS => 500,
            self::CACHE_GROUP_STOCK => 2000,
            self::CACHE_GROUP_SEARCH => 100,
        ));
        
        $data_size = $this->estimate_data_size($data);
        $new_total_size = $this->cache_stats['total_size'] + $data_size;
        
        // Check total size limit
        if ($new_total_size > $max_total_size) {
            return false;
        }
        
        // Check group item limit
        $group_max = isset($max_group_items[$group]) ? $max_group_items[$group] : 100;
        $current_count = isset($this->cache_stats['groups'][$group]['count']) ? 
                        $this->cache_stats['groups'][$group]['count'] : 0;
        
        if ($current_count >= $group_max) {
            return false;
        }
        
        return true;
    }

    /**
     * Invalidate specific product cache
     *
     * @param int $product_id Product ID
     */
    public function invalidate_product_cache($product_id) {
        // Individual product cache
        $product_key = 'wupos_product_' . $product_id;
        delete_transient($product_key);
        
        // Product list caches (need to clear all variants)
        $this->invalidate_group_cache(self::CACHE_GROUP_PRODUCTS);
        
        // Search caches
        $this->invalidate_group_cache(self::CACHE_GROUP_SEARCH);
        
        // Stock cache
        $stock_key = $this->build_cache_key(self::CACHE_GROUP_STOCK, 'product_' . $product_id);
        delete_transient($stock_key);
        
        $this->stats['deletes']++;
        wupos_log("Invalidated cache for product: {$product_id}", 'debug');
        
        do_action('wupos_product_cache_invalidated', $product_id);
    }

    /**
     * Invalidate category cache
     */
    public function invalidate_category_cache() {
        $this->invalidate_group_cache(self::CACHE_GROUP_CATEGORIES);
        
        // Also invalidate product caches as they contain category data
        $this->invalidate_group_cache(self::CACHE_GROUP_PRODUCTS);
        
        wupos_log("Invalidated all category caches", 'debug');
        do_action('wupos_category_cache_invalidated');
    }

    /**
     * Invalidate all caches for a specific group
     *
     * @param string $group Cache group to invalidate
     */
    public function invalidate_group_cache($group) {
        global $wpdb;
        
        // Get all transients for this group
        $pattern = '_transient_' . $group . '_%';
        
        $transients = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} 
                 WHERE option_name LIKE %s",
                $pattern
            )
        );
        
        foreach ($transients as $transient) {
            $key = str_replace('_transient_', '', $transient);
            delete_transient($key);
        }
        
        // Reset group stats
        if (isset($this->cache_stats['groups'][$group])) {
            $this->cache_stats['total_size'] -= $this->cache_stats['groups'][$group]['size'];
            unset($this->cache_stats['groups'][$group]);
        }
        
        $this->stats['deletes'] += count($transients);
        wupos_log("Invalidated {$group} cache group ({count} items)", 'debug', array('count' => count($transients)));
    }

    /**
     * Clean up specific group cache using LRU strategy
     *
     * @param string $group Cache group to clean
     */
    private function cleanup_group_cache($group) {
        global $wpdb;
        
        // Get all cache keys for this group with access times
        $pattern = '_transient_' . $group . '_%';
        $access_pattern = '_transient_' . $group . '_%_access';
        
        $cache_items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    REPLACE(c.option_name, '_transient_', '') as cache_key,
                    c.option_value as cache_data,
                    IFNULL(a.option_value, 0) as last_access
                 FROM {$wpdb->options} c
                 LEFT JOIN {$wpdb->options} a ON a.option_name = CONCAT(c.option_name, '_access')
                 WHERE c.option_name LIKE %s
                 ORDER BY IFNULL(a.option_value, 0) ASC",
                $pattern
            )
        );
        
        // Remove oldest 25% of items
        $total_items = count($cache_items);
        $items_to_remove = max(1, intval($total_items * 0.25));
        
        for ($i = 0; $i < $items_to_remove; $i++) {
            if (isset($cache_items[$i])) {
                delete_transient($cache_items[$i]->cache_key);
                delete_transient($cache_items[$i]->cache_key . '_access');
                $this->stats['deletes']++;
            }
        }
        
        wupos_log("Cleaned up {$group} cache: removed {$items_to_remove} of {$total_items} items", 'info');
    }

    /**
     * Clear all WUPOS caches
     */
    public function clear_all_cache() {
        $groups = array(
            self::CACHE_GROUP_PRODUCTS,
            self::CACHE_GROUP_CATEGORIES,
            self::CACHE_GROUP_CUSTOMERS,
            self::CACHE_GROUP_STOCK,
            self::CACHE_GROUP_SEARCH,
        );
        
        foreach ($groups as $group) {
            $this->invalidate_group_cache($group);
        }
        
        // Reset all stats
        $this->cache_stats = array(
            'total_size' => 0,
            'groups' => array(),
            'last_cleanup' => time(),
        );
        
        delete_transient('wupos_cache_stats');
        
        wupos_log("Cleared all WUPOS caches", 'info');
        do_action('wupos_all_cache_cleared');
    }

    /**
     * Clean up expired cache entries
     */
    public function cleanup_expired_cache() {
        global $wpdb;
        
        // WordPress automatically cleans up expired transients, but we can help
        $expired_transients = $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_timeout_wupos_%' 
             AND option_value < UNIX_TIMESTAMP()"
        );
        
        if ($expired_transients > 0) {
            wupos_log("Cleaned up {$expired_transients} expired cache entries", 'debug');
        }
        
        $this->cache_stats['last_cleanup'] = time();
        set_transient('wupos_cache_stats', $this->cache_stats, HOUR_IN_SECONDS);
    }

    /**
     * Warm up product cache with popular products
     */
    public function warm_product_cache() {
        // Get most popular products (by sales)
        $popular_products = wc_get_products(array(
            'limit' => 50,
            'orderby' => 'popularity',
            'order' => 'DESC',
            'status' => 'publish',
        ));
        
        $warmed = 0;
        foreach ($popular_products as $product) {
            $cache_key = 'wupos_product_' . $product->get_id();
            
            // Only warm if not already cached
            if (false === get_transient($cache_key)) {
                $product_data = array(
                    'id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'price' => $product->get_price(),
                    'stock_status' => $product->get_stock_status(),
                    // Add more essential data
                );
                
                $this->set_product_cache($cache_key, $product_data);
                $warmed++;
            }
        }
        
        wupos_log("Warmed cache for {$warmed} popular products", 'info');
    }

    /**
     * Get cache statistics
     *
     * @return array Cache statistics
     */
    public function get_cache_stats() {
        return array_merge($this->cache_stats, array(
            'session_stats' => $this->stats,
            'hit_rate' => $this->calculate_hit_rate(),
            'memory_usage' => $this->format_bytes($this->cache_stats['total_size']),
        ));
    }

    /**
     * Calculate cache hit rate
     *
     * @return float Hit rate percentage
     */
    private function calculate_hit_rate() {
        $total_requests = $this->stats['hits'] + $this->stats['misses'];
        if ($total_requests === 0) {
            return 0.0;
        }
        return round(($this->stats['hits'] / $total_requests) * 100, 2);
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes Bytes
     * @return string Formatted size
     */
    private function format_bytes($bytes) {
        $units = array('B', 'KB', 'MB', 'GB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Log cache statistics on shutdown
     */
    public function log_cache_stats() {
        if (defined('WUPOS_DEBUG') && WUPOS_DEBUG) {
            $stats = $this->get_cache_stats();
            wupos_log('Cache Stats: ' . print_r($stats, true), 'debug');
        }
    }

    /**
     * Preload cache for specific products
     *
     * @param array $product_ids Array of product IDs to preload
     */
    public function preload_products($product_ids) {
        if (!is_array($product_ids) || empty($product_ids)) {
            return;
        }
        
        $preloaded = 0;
        foreach ($product_ids as $product_id) {
            $cache_key = 'wupos_product_' . $product_id;
            
            if (false === get_transient($cache_key)) {
                $product = wc_get_product($product_id);
                if ($product) {
                    $product_data = array(
                        'id' => $product->get_id(),
                        'name' => $product->get_name(),
                        'sku' => $product->get_sku(),
                        'price' => $product->get_price(),
                        'stock_quantity' => $product->get_stock_quantity(),
                        'stock_status' => $product->get_stock_status(),
                        'image_url' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
                        'preloaded' => true,
                        'preload_time' => time(),
                    );
                    
                    $this->set_product_cache($cache_key, $product_data);
                    $preloaded++;
                }
            }
        }
        
        if ($preloaded > 0) {
            wupos_log("Preloaded cache for {$preloaded} products", 'debug');
        }
    }

    /**
     * Get cache health status
     *
     * @return array Cache health information
     */
    public function get_cache_health() {
        $stats = $this->get_cache_stats();
        $hit_rate = $stats['hit_rate'];
        
        $health = array(
            'status' => 'good',
            'hit_rate' => $hit_rate,
            'memory_usage' => $stats['memory_usage'],
            'recommendations' => array(),
        );
        
        // Analyze performance
        if ($hit_rate < 70) {
            $health['status'] = 'warning';
            $health['recommendations'][] = 'Cache hit rate is below optimal. Consider preloading popular products.';
        }
        
        if ($this->cache_stats['total_size'] > 40 * 1024 * 1024) { // 40MB
            $health['status'] = 'warning';
            $health['recommendations'][] = 'Cache size is approaching limits. Consider cleanup.';
        }
        
        if (empty($health['recommendations'])) {
            $health['recommendations'][] = 'Cache is performing optimally.';
        }
        
        return $health;
    }
}