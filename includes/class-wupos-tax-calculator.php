<?php
/**
 * WUPOS Tax Calculator
 *
 * Handles tax calculations using WooCommerce native APIs with
 * location-based tax rates and multi-currency support.
 *
 * @package WUPOS\Tax
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS_Tax_Calculator class.
 *
 * Provides comprehensive tax calculation functionality using
 * WooCommerce's native tax system with POS-specific optimizations.
 */
class WUPOS_Tax_Calculator {

    /**
     * Tax display mode (incl or excl)
     *
     * @var string
     */
    private $tax_display_mode;

    /**
     * Tax calculation enabled flag
     *
     * @var bool
     */
    private $taxes_enabled;

    /**
     * Round tax at subtotal level
     *
     * @var bool
     */
    private $round_at_subtotal;

    /**
     * Default customer location
     *
     * @var array
     */
    private $default_location;

    /**
     * Cache for tax calculations
     *
     * @var array
     */
    private $calculation_cache = array();

    /**
     * Constructor.
     */
    public function __construct() {
        $this->init_tax_settings();
    }

    /**
     * Initialize tax settings from WooCommerce.
     */
    private function init_tax_settings() {
        $this->taxes_enabled = wc_tax_enabled();
        $this->tax_display_mode = get_option('woocommerce_tax_display_cart', 'excl');
        $this->round_at_subtotal = get_option('woocommerce_tax_round_at_subtotal', 'no') === 'yes';
        
        // Get default location
        $this->default_location = array(
            'country' => WC()->countries->get_base_country(),
            'state' => WC()->countries->get_base_state(),
            'postcode' => WC()->countries->get_base_postcode(),
            'city' => WC()->countries->get_base_city()
        );
    }

    /**
     * Calculate tax for cart items.
     *
     * @param array $cart_items Cart items array
     * @param array $customer_location Customer location data
     * @return array Tax calculation results
     */
    public function calculate_cart_taxes($cart_items, $customer_location = array()) {
        if (!$this->taxes_enabled || empty($cart_items)) {
            return $this->get_empty_tax_result();
        }

        try {
            // Sanitize and validate location
            $location = $this->prepare_location($customer_location);
            
            // Create cache key
            $cache_key = $this->generate_cache_key($cart_items, $location);
            
            // Check cache first
            if (isset($this->calculation_cache[$cache_key])) {
                wupos_log('Tax calculation cache hit for key: ' . substr($cache_key, 0, 8), 'debug');
                return $this->calculation_cache[$cache_key];
            }

            // Calculate taxes using WooCommerce
            $tax_result = $this->perform_tax_calculation($cart_items, $location);
            
            // Cache result for 5 minutes
            $this->calculation_cache[$cache_key] = $tax_result;
            set_transient('wupos_tax_calc_' . $cache_key, $tax_result, 5 * MINUTE_IN_SECONDS);
            
            return $tax_result;

        } catch (Exception $e) {
            wupos_log('Tax calculation error: ' . $e->getMessage(), 'error');
            return $this->get_error_tax_result($e->getMessage());
        }
    }

    /**
     * Perform the actual tax calculation.
     *
     * @param array $cart_items Cart items
     * @param array $location Customer location
     * @return array Tax calculation result
     */
    private function perform_tax_calculation($cart_items, $location) {
        // Initialize WooCommerce tax calculation
        $tax_calculator = new WC_Tax();
        
        $tax_result = array(
            'subtotal' => 0,
            'subtotal_tax' => 0,
            'total' => 0,
            'total_tax' => 0,
            'tax_lines' => array(),
            'items' => array(),
            'location' => $location,
            'display_prices_including_tax' => wc_prices_include_tax(),
            'display_tax_totals' => $this->tax_display_mode === 'incl'
        );

        $combined_taxes = array();
        
        foreach ($cart_items as $item_key => $item) {
            $item_result = $this->calculate_item_tax($item, $location);
            
            if ($item_result['error']) {
                wupos_log('Tax calculation error for item ' . $item['product_id'] . ': ' . $item_result['message'], 'warning');
                continue;
            }

            // Add to totals
            $tax_result['subtotal'] += $item_result['line_subtotal'];
            $tax_result['subtotal_tax'] += $item_result['line_subtotal_tax'];
            $tax_result['total'] += $item_result['line_total'];
            $tax_result['total_tax'] += $item_result['line_tax'];
            
            // Store item result
            $tax_result['items'][$item_key] = $item_result;
            
            // Combine tax lines
            foreach ($item_result['taxes'] as $rate_id => $tax_amount) {
                if (!isset($combined_taxes[$rate_id])) {
                    $combined_taxes[$rate_id] = 0;
                }
                $combined_taxes[$rate_id] += $tax_amount;
            }
        }

        // Build tax lines with rate information
        $tax_result['tax_lines'] = $this->build_tax_lines($combined_taxes);
        
        // Apply rounding if needed
        if ($this->round_at_subtotal) {
            $tax_result = $this->apply_subtotal_rounding($tax_result);
        }

        // Calculate final totals
        $tax_result['cart_total'] = $tax_result['total'] + $tax_result['total_tax'];
        $tax_result['tax_total'] = $tax_result['total_tax'];
        
        // Add formatting information
        $tax_result['formatted'] = $this->format_tax_totals($tax_result);
        
        return $tax_result;
    }

    /**
     * Calculate tax for individual cart item.
     *
     * @param array $item Cart item data
     * @param array $location Customer location
     * @return array Item tax calculation result
     */
    private function calculate_item_tax($item, $location) {
        try {
            $product_id = $item['product_id'];
            $variation_id = isset($item['variation_id']) ? $item['variation_id'] : 0;
            $quantity = $item['quantity'];
            
            // Get product
            $product = wc_get_product($variation_id ? $variation_id : $product_id);
            
            if (!$product) {
                return array(
                    'error' => true,
                    'message' => 'Product not found: ' . $product_id
                );
            }

            // Get product price
            $price = $this->get_product_price($product, $item);
            
            if (!$product->is_taxable()) {
                return $this->get_non_taxable_item_result($item, $price, $quantity);
            }

            // Get tax rates for this product and location
            $tax_class = $product->get_tax_class();
            $tax_rates = WC_Tax::find_rates(array(
                'country' => $location['country'],
                'state' => $location['state'],
                'postcode' => $location['postcode'],
                'city' => $location['city'],
                'tax_class' => $tax_class
            ));

            if (empty($tax_rates)) {
                return $this->get_non_taxable_item_result($item, $price, $quantity);
            }

            // Calculate taxes
            $line_subtotal = $price * $quantity;
            $taxes = WC_Tax::calc_tax($line_subtotal, $tax_rates, wc_prices_include_tax());
            $tax_total = array_sum($taxes);

            // Calculate totals based on price inclusion
            if (wc_prices_include_tax()) {
                $line_subtotal_tax = $tax_total;
                $line_subtotal_excl = $line_subtotal - $tax_total;
                $line_total = $line_subtotal_excl;
                $line_tax = $tax_total;
            } else {
                $line_subtotal_tax = $tax_total;
                $line_subtotal_excl = $line_subtotal;
                $line_total = $line_subtotal;
                $line_tax = $tax_total;
            }

            return array(
                'error' => false,
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'quantity' => $quantity,
                'unit_price' => $price,
                'line_subtotal' => $line_subtotal_excl,
                'line_subtotal_tax' => $line_subtotal_tax,
                'line_total' => $line_total,
                'line_tax' => $line_tax,
                'taxes' => $taxes,
                'tax_class' => $tax_class,
                'taxable' => true
            );

        } catch (Exception $e) {
            return array(
                'error' => true,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Get product price for tax calculation.
     *
     * @param WC_Product $product Product object
     * @param array $item Cart item data
     * @return float Product price
     */
    private function get_product_price($product, $item) {
        // Check if custom price is set (for manual price override)
        if (isset($item['custom_price']) && $item['custom_price'] > 0) {
            return (float) $item['custom_price'];
        }

        // Get regular product price
        $price = $product->get_price();
        
        // Apply any POS-specific discounts
        if (isset($item['discount_percent']) && $item['discount_percent'] > 0) {
            $discount = min(100, max(0, (float) $item['discount_percent']));
            $price = $price * (1 - ($discount / 100));
        }
        
        if (isset($item['discount_amount']) && $item['discount_amount'] > 0) {
            $price = max(0, $price - (float) $item['discount_amount']);
        }

        return (float) $price;
    }

    /**
     * Build tax lines array with rate information.
     *
     * @param array $combined_taxes Combined tax amounts by rate ID
     * @return array Tax lines with rate information
     */
    private function build_tax_lines($combined_taxes) {
        $tax_lines = array();
        
        foreach ($combined_taxes as $rate_id => $tax_amount) {
            $rate = WC_Tax::_get_tax_rate($rate_id);
            
            if ($rate) {
                $tax_lines[] = array(
                    'rate_id' => $rate_id,
                    'label' => WC_Tax::get_rate_label($rate),
                    'compound' => WC_Tax::is_compound($rate),
                    'tax_total' => $tax_amount,
                    'rate_percent' => (float) $rate['tax_rate'],
                    'formatted_tax_total' => wc_price($tax_amount)
                );
            }
        }

        // Sort by compound status and rate
        usort($tax_lines, function($a, $b) {
            if ($a['compound'] !== $b['compound']) {
                return $a['compound'] ? 1 : -1;
            }
            return $a['rate_percent'] <=> $b['rate_percent'];
        });

        return $tax_lines;
    }

    /**
     * Apply subtotal rounding to tax calculation.
     *
     * @param array $tax_result Tax calculation result
     * @return array Rounded tax result
     */
    private function apply_subtotal_rounding($tax_result) {
        if (!$this->round_at_subtotal) {
            return $tax_result;
        }

        // Round subtotal taxes
        $tax_result['subtotal_tax'] = wc_round_tax_total($tax_result['subtotal_tax']);
        $tax_result['total_tax'] = wc_round_tax_total($tax_result['total_tax']);
        
        // Round individual tax lines
        foreach ($tax_result['tax_lines'] as &$tax_line) {
            $tax_line['tax_total'] = wc_round_tax_total($tax_line['tax_total']);
            $tax_line['formatted_tax_total'] = wc_price($tax_line['tax_total']);
        }
        
        return $tax_result;
    }

    /**
     * Format tax totals for display.
     *
     * @param array $tax_result Tax calculation result
     * @return array Formatted values
     */
    private function format_tax_totals($tax_result) {
        return array(
            'subtotal' => wc_price($tax_result['subtotal']),
            'subtotal_tax' => wc_price($tax_result['subtotal_tax']),
            'total' => wc_price($tax_result['total']),
            'total_tax' => wc_price($tax_result['total_tax']),
            'cart_total' => wc_price($tax_result['cart_total']),
            'tax_display_text' => $this->get_tax_display_text($tax_result)
        );
    }

    /**
     * Get tax display text for UI.
     *
     * @param array $tax_result Tax calculation result
     * @return string Tax display text
     */
    private function get_tax_display_text($tax_result) {
        if (!$this->taxes_enabled || $tax_result['total_tax'] == 0) {
            return '';
        }

        if (count($tax_result['tax_lines']) === 1) {
            $tax_line = $tax_result['tax_lines'][0];
            return sprintf(
                __('Includes %s %s', 'wupos'),
                $tax_line['formatted_tax_total'],
                $tax_line['label']
            );
        }

        return sprintf(
            __('Includes %s tax', 'wupos'),
            wc_price($tax_result['total_tax'])
        );
    }

    /**
     * Get non-taxable item result.
     *
     * @param array $item Cart item
     * @param float $price Item price
     * @param int $quantity Item quantity
     * @return array Non-taxable item result
     */
    private function get_non_taxable_item_result($item, $price, $quantity) {
        $line_total = $price * $quantity;
        
        return array(
            'error' => false,
            'product_id' => $item['product_id'],
            'variation_id' => isset($item['variation_id']) ? $item['variation_id'] : 0,
            'quantity' => $quantity,
            'unit_price' => $price,
            'line_subtotal' => $line_total,
            'line_subtotal_tax' => 0,
            'line_total' => $line_total,
            'line_tax' => 0,
            'taxes' => array(),
            'tax_class' => '',
            'taxable' => false
        );
    }

    /**
     * Get empty tax result.
     *
     * @return array Empty tax result
     */
    private function get_empty_tax_result() {
        return array(
            'subtotal' => 0,
            'subtotal_tax' => 0,
            'total' => 0,
            'total_tax' => 0,
            'cart_total' => 0,
            'tax_total' => 0,
            'tax_lines' => array(),
            'items' => array(),
            'location' => $this->default_location,
            'display_prices_including_tax' => wc_prices_include_tax(),
            'display_tax_totals' => false,
            'formatted' => array(
                'subtotal' => wc_price(0),
                'subtotal_tax' => wc_price(0),
                'total' => wc_price(0),
                'total_tax' => wc_price(0),
                'cart_total' => wc_price(0),
                'tax_display_text' => ''
            )
        );
    }

    /**
     * Get error tax result.
     *
     * @param string $message Error message
     * @return array Error tax result
     */
    private function get_error_tax_result($message) {
        $result = $this->get_empty_tax_result();
        $result['error'] = true;
        $result['error_message'] = $message;
        return $result;
    }

    /**
     * Prepare and validate customer location.
     *
     * @param array $location Raw location data
     * @return array Validated location data
     */
    private function prepare_location($location) {
        $default = $this->default_location;
        
        return array(
            'country' => !empty($location['country']) ? 
                         strtoupper(sanitize_text_field($location['country'])) : 
                         $default['country'],
            'state' => !empty($location['state']) ? 
                       sanitize_text_field($location['state']) : 
                       $default['state'],
            'postcode' => !empty($location['postcode']) ? 
                          sanitize_text_field($location['postcode']) : 
                          $default['postcode'],
            'city' => !empty($location['city']) ? 
                      sanitize_text_field($location['city']) : 
                      $default['city']
        );
    }

    /**
     * Generate cache key for tax calculation.
     *
     * @param array $cart_items Cart items
     * @param array $location Customer location
     * @return string Cache key
     */
    private function generate_cache_key($cart_items, $location) {
        $items_hash = md5(serialize($cart_items));
        $location_hash = md5(serialize($location));
        $settings_hash = md5(serialize(array(
            'taxes_enabled' => $this->taxes_enabled,
            'tax_display_mode' => $this->tax_display_mode,
            'round_at_subtotal' => $this->round_at_subtotal,
            'prices_include_tax' => wc_prices_include_tax()
        )));
        
        return hash('sha256', $items_hash . $location_hash . $settings_hash);
    }

    /**
     * Calculate tax for single product (for product lookup).
     *
     * @param int $product_id Product ID
     * @param float $price Product price
     * @param array $location Customer location
     * @return array Tax calculation result
     */
    public function calculate_product_tax($product_id, $price = null, $location = array()) {
        if (!$this->taxes_enabled) {
            return array(
                'taxable' => false,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'price_including_tax' => (float) $price,
                'price_excluding_tax' => (float) $price
            );
        }

        try {
            $product = wc_get_product($product_id);
            
            if (!$product || !$product->is_taxable()) {
                return array(
                    'taxable' => false,
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'price_including_tax' => (float) $price,
                    'price_excluding_tax' => (float) $price
                );
            }

            $location = $this->prepare_location($location);
            $price = $price ?: $product->get_price();
            
            // Get tax rates
            $tax_class = $product->get_tax_class();
            $tax_rates = WC_Tax::find_rates(array(
                'country' => $location['country'],
                'state' => $location['state'],
                'postcode' => $location['postcode'],
                'city' => $location['city'],
                'tax_class' => $tax_class
            ));

            if (empty($tax_rates)) {
                return array(
                    'taxable' => true,
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'price_including_tax' => (float) $price,
                    'price_excluding_tax' => (float) $price
                );
            }

            // Calculate tax
            $taxes = WC_Tax::calc_tax($price, $tax_rates, wc_prices_include_tax());
            $tax_amount = array_sum($taxes);
            
            // Calculate effective tax rate
            $total_rate = 0;
            foreach ($tax_rates as $rate) {
                $total_rate += (float) $rate['rate'];
            }

            if (wc_prices_include_tax()) {
                $price_excl_tax = $price - $tax_amount;
                $price_incl_tax = $price;
            } else {
                $price_excl_tax = $price;
                $price_incl_tax = $price + $tax_amount;
            }

            return array(
                'taxable' => true,
                'tax_rate' => $total_rate,
                'tax_amount' => $tax_amount,
                'price_including_tax' => $price_incl_tax,
                'price_excluding_tax' => $price_excl_tax,
                'tax_rates' => $tax_rates
            );

        } catch (Exception $e) {
            wupos_log('Product tax calculation error: ' . $e->getMessage(), 'error');
            return array(
                'taxable' => false,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'price_including_tax' => (float) $price,
                'price_excluding_tax' => (float) $price,
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Get tax settings for frontend.
     *
     * @return array Tax settings
     */
    public function get_tax_settings() {
        return array(
            'taxes_enabled' => $this->taxes_enabled,
            'tax_display_mode' => $this->tax_display_mode,
            'prices_include_tax' => wc_prices_include_tax(),
            'round_at_subtotal' => $this->round_at_subtotal,
            'tax_display_suffix' => get_option('woocommerce_price_display_suffix'),
            'default_location' => $this->default_location
        );
    }

    /**
     * Clear tax calculation cache.
     *
     * @param string $cache_key Optional specific cache key to clear
     */
    public function clear_cache($cache_key = null) {
        if ($cache_key) {
            unset($this->calculation_cache[$cache_key]);
            delete_transient('wupos_tax_calc_' . $cache_key);
        } else {
            $this->calculation_cache = array();
            
            // Clear all tax calculation transients
            global $wpdb;
            $wpdb->query(
                "DELETE FROM {$wpdb->options} 
                 WHERE option_name LIKE '_transient_wupos_tax_calc_%'"
            );
        }
    }

    /**
     * Get cache statistics.
     *
     * @return array Cache statistics
     */
    public function get_cache_stats() {
        global $wpdb;
        
        $cache_count = $wpdb->get_var(
            "SELECT COUNT(*) 
             FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_wupos_tax_calc_%'"
        );
        
        return array(
            'memory_cache_size' => count($this->calculation_cache),
            'transient_cache_size' => (int) $cache_count,
            'taxes_enabled' => $this->taxes_enabled,
            'calculation_mode' => $this->tax_display_mode
        );
    }
}