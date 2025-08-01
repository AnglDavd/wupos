# WUPOS Technical Documentation - Comprehensive System Implementation

## Table of Contents

1. [Overview](#overview)
2. [Cart UX Optimization System](#cart-ux-optimization-system)
3. [Tax Display Architecture](#tax-display-architecture)
4. [WooCommerce Timing Fixes](#woocommerce-timing-fixes)
5. [Developer API Documentation](#developer-api-documentation)
6. [Filters and Hooks Documentation](#filters-and-hooks-documentation)
7. [Architecture Documentation](#architecture-documentation)
8. [Integration Guide](#integration-guide)
9. [Troubleshooting and Debugging](#troubleshooting-and-debugging)
10. [Performance Considerations](#performance-considerations)
11. [Security Considerations](#security-considerations)

---

## Overview

The WUPOS Technical Documentation covers the comprehensive WordPress Point of Sale system implementation, including advanced cart UX optimizations, intelligent stock management, and seamless WooCommerce integration. This documentation details the complete system architecture with recent improvements to cart timing, tax display consistency, and user experience enhancements.

### Key Features

- **Advanced Cart UX**: Optimized cart interactions with timing fixes and consistency improvements
- **Minimalista Tax Display**: Consistent tax visualization across all cart states with forced fallback design
- **WooCommerce Timing Fixes**: Resolved access issues and initialization conflicts
- **Dynamic Stock Thresholds**: Automatically calculates stock level thresholds based on WooCommerce low stock settings
- **Three-Tier Classification**: Categorizes stock into Low (Red), Medium (Yellow), and High (Green) levels
- **WooCommerce Integration**: Full compatibility with existing WooCommerce stock management
- **Developer Extensibility**: Comprehensive filter and action hooks for customization
- **Stock Validation**: Robust data validation and error handling
- **Performance Optimized**: Efficient calculations with caching considerations

### Version Compatibility

- **WordPress**: 5.0+
- **WooCommerce**: 5.0+
- **PHP**: 7.4+
- **WUPOS**: 1.0.0+ (with Cart UX Optimization System)

---

## Cart UX Optimization System

### Overview

The Cart UX Optimization System represents a comprehensive overhaul of the WUPOS cart experience, focusing on consistent minimalista tax display, timing fixes, and enhanced user interactions. This system ensures reliable cart behavior across all states and resolves component conflicts.

### Key Improvements

#### Cart Timing Fixes

**Problem Solved**: WooCommerce cart access before `wp_loaded` hook causing fatal errors and inconsistent behavior.

**Solution Implementation**:
```php
// Fixed cart initialization sequence
add_action('wp_loaded', function() {
    // Ensure WooCommerce cart is properly initialized
    if (function_exists('WC') && WC()->cart) {
        // Safe cart operations
        $cart_contents = WC()->cart->get_cart();
        // Process cart with proper timing
    }
});
```

**Benefits**:
- Eliminated fatal errors during cart access
- Consistent cart initialization across all page states
- Proper WooCommerce integration timing
- Improved reliability for cart operations

#### Dynamic Tax Updates

**Problem Solved**: Inconsistent tax calculation and display updates when cart contents change.

**Solution Implementation**:
- Real-time tax recalculation on cart changes
- Automatic tax display updates without page refresh
- Consistent tax formatting across all cart states
- Proper handling of tax-inclusive vs tax-exclusive scenarios

#### Component Conflict Resolution

**Problem Solved**: Multiple tax display components causing visual conflicts and redundant information.

**Solution Implementation**:
- Forced simple tax-fallback design for all cart states
- Eliminated redundant tax status text
- Consistent minimalista approach across components
- Streamlined tax display architecture

### Technical Architecture

#### Cart State Management

```php
/**
 * Enhanced cart state management with timing fixes
 */
class WUPOS_Cart_Manager {
    
    public function init() {
        // Proper WordPress hook timing
        add_action('wp_loaded', array($this, 'initialize_cart'));
        add_action('woocommerce_cart_updated', array($this, 'handle_cart_update'));
    }
    
    public function initialize_cart() {
        // Safe cart access after WooCommerce initialization
        if ($this->is_cart_available()) {
            $this->setup_cart_hooks();
            $this->prepare_tax_calculations();
        }
    }
    
    private function is_cart_available() {
        return function_exists('WC') && 
               WC()->cart && 
               !WC()->cart->is_empty();
    }
}
```

#### Tax Display Consistency

```php
/**
 * Minimalista tax display system
 */
class WUPOS_Tax_Display {
    
    public function render_tax_info($cart_data) {
        // Force simple fallback design
        $tax_display = array(
            'show_tax_breakdown' => false,
            'use_simple_format' => true,
            'force_fallback_design' => true
        );
        
        return $this->generate_consistent_display($cart_data, $tax_display);
    }
    
    private function generate_consistent_display($cart_data, $options) {
        // Consistent tax display across all cart states
        $output = '<div class="wupos-tax-simple">';
        $output .= $this->format_tax_amount($cart_data['tax_total']);
        $output .= '</div>';
        
        return $output;
    }
}
```

### Implementation Benefits

1. **Reliability**: Eliminated cart timing issues and fatal errors
2. **Consistency**: Uniform tax display across all cart states  
3. **Performance**: Optimized cart operations and reduced conflicts
4. **User Experience**: Smooth, predictable cart interactions
5. **Maintainability**: Simplified architecture with clear separation of concerns

---

## Tax Display Architecture

### Minimalista Design Philosophy

The Tax Display Architecture implements a "minimalista" approach, focusing on clean, consistent tax information display without overwhelming the user interface. This system prioritizes clarity and simplicity while maintaining full tax calculation accuracy.

### Design Principles

#### 1. Consistent Visual Approach
- **Single Tax Display Format**: Uniform presentation across all cart states
- **Minimal Visual Clutter**: Clean, focused tax information
- **Consistent Typography**: Standardized text formatting and sizing
- **Unified Color Scheme**: Consistent visual hierarchy

#### 2. Forced Fallback Design
```php
/**
 * Force simple tax display across all components
 */
public function force_tax_fallback_design() {
    // Override complex tax displays with simple format
    $tax_settings = array(
        'display_type' => 'simple',
        'show_breakdown' => false,
        'use_fallback' => true,
        'force_consistency' => true
    );
    
    return $this->apply_minimalista_styling($tax_settings);
}
```

#### 3. Component Conflict Resolution
- **Eliminated Redundancy**: Single source of tax display truth
- **Component Deconfliction**: Prevent multiple tax displays
- **Hierarchical Override**: Consistent display precedence
- **State Synchronization**: Uniform tax info across cart states

### Technical Implementation

#### Tax Display Controller

```php
/**
 * Centralized tax display management
 */  
class WUPOS_Tax_Display_Controller {
    
    private $display_mode = 'minimalista';
    
    public function render_cart_taxes($cart_totals) {
        // Apply minimalista design constraints
        $display_config = $this->get_minimalista_config();
        
        // Force consistent display regardless of cart state
        return $this->generate_unified_tax_display($cart_totals, $display_config);
    }
    
    private function get_minimalista_config() {
        return array(
            'show_tax_label' => true,
            'show_tax_amount' => true, 
            'show_tax_breakdown' => false,
            'show_tax_percentage' => false,
            'use_simple_format' => true,
            'apply_consistent_styling' => true
        );
    }
}
```

#### Dynamic Tax Updates

The system ensures tax information updates dynamically while maintaining the minimalista aesthetic:

```javascript
/**
 * Dynamic tax display updates with consistent styling
 */
function updateTaxDisplay(cartData) {
    const taxContainer = document.querySelector('.wupos-tax-simple');
    
    if (taxContainer && cartData.tax_total) {
        // Maintain minimalista design during updates
        taxContainer.innerHTML = formatMinimalistaTax(cartData.tax_total);
        
        // Apply consistent styling
        taxContainer.className = 'wupos-tax-simple wupos-tax-consistent';
    }
}

function formatMinimalistaTax(taxAmount) {
    // Simple, clean tax display format
    return `<span class="tax-label">Tax:</span> <span class="tax-amount">${taxAmount}</span>`;
}
```

### Visual Design Standards

#### CSS Implementation
```css
/* Minimalista tax display styling */
.wupos-tax-simple {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-top: 1px solid #e0e0e0;
    font-size: 14px;
    color: #666;
}

.wupos-tax-simple .tax-label {
    font-weight: 500;
}

.wupos-tax-simple .tax-amount {
    font-weight: 600;
    color: #333;
}

/* Consistent styling across all cart states */
.wupos-tax-consistent {
    background: transparent;
    border-radius: 0;
    box-shadow: none;
    transition: none;
}
```

---

## WooCommerce Timing Fixes

### Critical Timing Issues Resolved

The WooCommerce timing fixes address fundamental initialization and access issues that were causing system instability and user experience problems.

### Issue Analysis

#### Problem 1: Premature Cart Access
**Symptom**: Fatal errors when accessing WooCommerce cart functionality before proper initialization
**Root Cause**: WUPOS attempting to access WC()->cart before `wp_loaded` hook
**Impact**: System crashes, unreliable cart behavior, poor user experience

#### Problem 2: Hook Sequence Conflicts  
**Symptom**: Inconsistent cart data and display discrepancies
**Root Cause**: WordPress/WooCommerce hook execution order conflicts
**Impact**: Data inconsistency, unpredictable behavior

#### Problem 3: Session Management Issues
**Symptom**: Cart persistence problems and session conflicts
**Root Cause**: Premature session access and improper timing
**Impact**: Lost cart data, authentication issues

### Technical Solutions

#### 1. Proper Hook Timing Implementation

```php
/**
 * Correct hook sequence for WooCommerce integration
 */
class WUPOS_Timing_Manager {
    
    public function __construct() {
        // Respect WordPress/WooCommerce initialization sequence
        add_action('init', array($this, 'early_init'), 5);
        add_action('wp_loaded', array($this, 'wc_integration_init'), 10);
        add_action('woocommerce_init', array($this, 'wc_ready_init'), 10);
    }
    
    public function early_init() {
        // Basic plugin initialization - no WC dependencies
        $this->setup_basic_hooks();
        $this->load_core_classes();
    }
    
    public function wc_integration_init() {
        // Safe WooCommerce integration point
        if ($this->is_woocommerce_ready()) {
            $this->setup_cart_integration();
            $this->initialize_tax_calculations();
        }
    }
    
    public function wc_ready_init() {
        // Final WooCommerce-dependent initialization
        $this->setup_advanced_features();
        $this->enable_realtime_updates();
    }
    
    private function is_woocommerce_ready() {
        return function_exists('WC') && 
               class_exists('WooCommerce') &&
               WC()->cart !== null;
    }
}
```

#### 2. Safe Cart Access Pattern

```php
/**
 * Safe cart access with proper error handling
 */
public function safe_cart_operation($callback) {
    // Verify WooCommerce availability
    if (!$this->is_woocommerce_active()) {
        return new WP_Error('wc_not_active', 'WooCommerce is not active');
    }
    
    // Ensure proper timing
    if (!did_action('wp_loaded')) {
        return new WP_Error('too_early', 'Called before wp_loaded hook');
    }
    
    // Verify cart availability
    if (!WC()->cart) {
        return new WP_Error('cart_not_ready', 'WooCommerce cart not initialized');
    }
    
    // Execute callback safely
    try {
        return call_user_func($callback, WC()->cart);
    } catch (Exception $e) {
        error_log('WUPOS Cart Operation Error: ' . $e->getMessage());
        return new WP_Error('cart_error', $e->getMessage());
    }
}
```

#### 3. Session Management Improvements

```php
/**
 * Proper session handling with timing respect
 */
class WUPOS_Session_Manager {
    
    public function init() {
        // Wait for WordPress session handling
        add_action('wp_loaded', array($this, 'setup_session_handling'));
    }
    
    public function setup_session_handling() {
        // Ensure WooCommerce session is ready
        if (WC()->session) {
            $this->setup_cart_persistence();
            $this->enable_session_sync();
        }
    }
    
    public function get_cart_session() {
        // Safe session access with fallbacks
        if (!WC()->session) {
            return array(); // Safe fallback
        }
        
        return WC()->session->get('wupos_cart_data', array());
    }
}
```

### Validation and Testing

#### Timing Validation Tests
```php
/**
 * Validate proper timing implementation
 */
public function validate_timing_implementation() {
    $results = array();
    
    // Test 1: Verify hook sequence
    $results['hook_sequence'] = $this->test_hook_sequence();
    
    // Test 2: Validate cart access timing
    $results['cart_access'] = $this->test_safe_cart_access();
    
    // Test 3: Check session availability
    $results['session_ready'] = $this->test_session_availability();
    
    return $results;
}
```

### Performance Impact

The timing fixes provide several performance benefits:

1. **Reduced Errors**: Elimination of fatal errors and exceptions
2. **Faster Load Times**: Proper initialization sequence reduces overhead
3. **Better Caching**: Consistent timing enables better caching strategies
4. **Improved Reliability**: Predictable behavior reduces debugging overhead

---

### Stock Level Calculation Methods

#### `get_stock_info($product)`

**Description**: Main method for retrieving comprehensive stock information for a WooCommerce product.

**Parameters**:
- `$product` (WC_Product): WooCommerce product object

**Returns**: `array` - Comprehensive stock information array

**Response Schema**:

```php
array(
    'status'               => string,  // 'instock', 'outofstock', 'low-stock', 'medium-stock', 'high-stock', 'onbackorder', 'error'
    'stock_level'          => string,  // 'low', 'medium', 'high', 'out', 'unlimited', 'backorder', 'error'
    'badge_class'          => string,  // CSS class for UI display
    'text'                 => string,  // Localized display text
    'threshold_info'       => array,   // Threshold calculation details
    'stock_classification' => array    // Detailed classification data
)
```

**Detailed Response Fields**:

```php
// threshold_info structure
'threshold_info' => array(
    'wc_low_threshold'    => int,    // WooCommerce low stock threshold
    'calculated_medium'   => int,    // 3x WooCommerce threshold
    'calculated_high'     => int,    // 10x WooCommerce threshold
    'source_setting'      => string, // 'woocommerce_notify_low_stock_amount'
    'calculation_method'  => string, // 'dynamic_multipliers'
    'multipliers'         => array(
        'medium' => int,             // Medium threshold multiplier (3)
        'high'   => int              // High threshold multiplier (10)
    )
)

// stock_classification structure
'stock_classification' => array(
    'level'      => string,  // Classification level
    'quantity'   => int,     // Current stock quantity
    'thresholds' => array(
        'low'    => int,     // Low stock threshold
        'medium' => int,     // Medium stock threshold  
        'high'   => int      // High stock threshold
    )
)
```

**Usage Example**:

```php
// Get stock information for a product
$product = wc_get_product(123);
$products_api = new WUPOS_Products_API();
$stock_info = $products_api->get_stock_info($product);

// Access stock data
echo $stock_info['text']; // "5 en stock"
echo $stock_info['stock_level']; // "low"
echo $stock_info['status']; // "low-stock"

// Use threshold information
$thresholds = $stock_info['threshold_info'];
echo "Low threshold: " . $thresholds['wc_low_threshold'];
echo "Medium threshold: " . $thresholds['calculated_medium'];
```

#### `get_dynamic_stock_thresholds()`

**Description**: Calculates dynamic stock thresholds based on WooCommerce settings.

**Parameters**: None

**Returns**: `array` - Dynamic threshold configuration

**Response Schema**:

```php
array(
    'wc_low_threshold'    => int,    // Base WooCommerce threshold
    'calculated_medium'   => int,    // Medium threshold (3x base)
    'calculated_high'     => int,    // High threshold (10x base)
    'source_setting'      => string, // WordPress option name
    'calculation_method'  => string, // Calculation methodology
    'multipliers'         => array   // Multiplier values used
)
```

**Usage Example**:

```php
$products_api = new WUPOS_Products_API();
$thresholds = $products_api->get_dynamic_stock_thresholds();

// WooCommerce setting: 5 units
// Result:
// - Low: < 5 units (Red)
// - Medium: 5-14 units (Yellow) 
// - High: >= 15 units (Green)
```

#### `validate_stock_data($product)`

**Description**: Validates and sanitizes stock data for consistent API responses.

**Parameters**:
- `$product` (WC_Product): WooCommerce product object

**Returns**: `array` - Validated stock data

**Response Schema**:

```php
array(
    'stock_quantity'  => int|null,   // Sanitized stock quantity
    'manage_stock'    => bool,       // Stock management status
    'stock_status'    => string,     // Validated stock status
    'is_in_stock'     => bool,       // Stock availability
    'product_type'    => string,     // Product type
    'is_purchasable'  => bool,       // Purchase availability
    'error'           => string|null // Error message if validation fails
)
```

#### `debug_stock_calculation($test_quantity)`

**Description**: Debug method for testing stock level calculations with different quantities.

**Parameters**:
- `$test_quantity` (int, optional): Stock quantity to test

**Returns**: `array` - Debug information

**Usage Example**:

```php
$products_api = new WUPOS_Products_API();

// Test specific quantity
$debug_info = $products_api->debug_stock_calculation(8);
// Returns classification for 8 units based on current thresholds

// Get current thresholds only
$thresholds = $products_api->debug_stock_calculation();
```

### Enhanced Product API Response

When calling `get_products_ajax()` or `format_product_for_pos()`, products now include these additional stock fields:

```php
array(
    // ... existing product fields ...
    
    // New stock classification fields
    'stock_level'              => string,  // 'low', 'medium', 'high', 'out', 'unlimited'
    'stock_threshold_low'      => int,     // Low stock threshold value
    'stock_threshold_medium'   => int,     // Medium stock threshold value  
    'stock_threshold_high'     => int,     // High stock threshold value
    'stock_badge_class'        => string,  // CSS class for stock badge
    'stock_text'               => string,  // Localized stock text
    'stock_classification'     => array,   // Detailed classification data
    
    // Enhanced existing fields
    'stock_status'             => string,  // Enhanced with new statuses
    'stock_quantity'           => int,     // Validated quantity
)
```

---

## Filters and Hooks Documentation

### Available Filters

#### `wupos_product_stock_info`

**Description**: Allows customization of product stock information returned by the stock system.

**Usage**:

```php
/**
 * Customize WUPOS product stock information
 *
 * @param array      $stock_info The calculated stock information
 * @param WC_Product $product    The WooCommerce product object
 * @param array      $thresholds The threshold values used
 * @return array Modified stock information
 */
add_filter('wupos_product_stock_info', 'custom_stock_info', 10, 3);

function custom_stock_info($stock_info, $product, $thresholds) {
    // Example: Add custom stock level for specific product category
    $terms = get_the_terms($product->get_id(), 'product_cat');
    if ($terms) {
        foreach ($terms as $term) {
            if ($term->slug === 'electronics') {
                // Custom logic for electronics category
                if ($stock_info['stock_classification']['quantity'] < 10) {
                    $stock_info['stock_level'] = 'critical';
                    $stock_info['badge_class'] = 'wupos-stock-critical';
                    $stock_info['text'] = __('Critical Stock', 'your-textdomain');
                }
            }
        }
    }
    
    return $stock_info;
}
```

**Parameters**:
- `$stock_info` (array): The calculated stock information array
- `$product` (WC_Product): The WooCommerce product object
- `$thresholds` (array): The threshold values used in calculation

**Common Use Cases**:
- Add custom stock levels for specific product categories
- Modify stock text based on product attributes
- Implement custom badge classes for different themes
- Add additional stock information fields

#### `wupos_stock_level_thresholds`

**Description**: Allows customization of stock level threshold calculations.

**Usage**:

```php
/**
 * Customize WUPOS stock level thresholds
 *
 * @param array $thresholds        The calculated thresholds
 * @param int   $wc_low_threshold  The WooCommerce low stock threshold
 * @return array Modified thresholds
 */
add_filter('wupos_stock_level_thresholds', 'custom_stock_thresholds', 10, 2);

function custom_stock_thresholds($thresholds, $wc_low_threshold) {
    // Example: Use different multipliers
    $thresholds['calculated_medium'] = $wc_low_threshold * 5;  // 5x instead of 3x
    $thresholds['calculated_high'] = $wc_low_threshold * 20;   // 20x instead of 10x
    
    // Update multiplier info
    $thresholds['multipliers']['medium'] = 5;
    $thresholds['multipliers']['high'] = 20;
    $thresholds['calculation_method'] = 'custom_multipliers';
    
    return $thresholds;
}
```

**Parameters**:
- `$thresholds` (array): The calculated thresholds array
- `$wc_low_threshold` (int): The original WooCommerce low stock threshold

**Common Use Cases**:
- Adjust multipliers for different business models
- Implement category-specific thresholds
- Add additional threshold levels
- Modify calculation methodology

### Available Actions

#### `wupos_before_stock_calculation`

**Description**: Fired before stock level calculation begins.

**Usage**:

```php
/**
 * Perform actions before stock calculation
 *
 * @param WC_Product $product The product being processed
 */
add_action('wupos_before_stock_calculation', 'before_stock_calc');

function before_stock_calc($product) {
    // Log stock calculations for debugging
    error_log('WUPOS: Calculating stock for product ' . $product->get_id());
    
    // Update custom product meta
    update_post_meta($product->get_id(), '_last_stock_check', current_time('mysql'));
}
```

#### `wupos_after_stock_calculation`

**Description**: Fired after stock level calculation completes.

**Usage**:

```php
/**
 * Perform actions after stock calculation
 *
 * @param array      $stock_info The calculated stock information
 * @param WC_Product $product    The product that was processed
 */
add_action('wupos_after_stock_calculation', 'after_stock_calc', 10, 2);

function after_stock_calc($stock_info, $product) {
    // Send notifications for low stock
    if ($stock_info['stock_level'] === 'low') {
        // Trigger low stock notification
        do_action('wupos_low_stock_detected', $product, $stock_info);
    }
    
    // Cache stock information
    wp_cache_set('wupos_stock_' . $product->get_id(), $stock_info, 'wupos_stock', 300);
}
```

### Custom Hook Examples

#### Example: Custom Stock Badge Behavior

```php
/**
 * Add custom stock badge behavior for different product types
 */
add_filter('wupos_product_stock_info', 'custom_badge_behavior', 10, 3);

function custom_badge_behavior($stock_info, $product, $thresholds) {
    // Different behavior for variable products
    if ($product->is_type('variable')) {
        $stock_info['badge_class'] .= ' wupos-variable-product';
    }
    
    // Add product type class
    $stock_info['badge_class'] .= ' wupos-type-' . $product->get_type();
    
    return $stock_info;
}
```

#### Example: Category-Specific Thresholds

```php
/**
 * Implement category-specific stock thresholds
 */
add_filter('wupos_stock_level_thresholds', 'category_specific_thresholds', 10, 2);

function category_specific_thresholds($thresholds, $wc_low_threshold) {
    // Get current product from global context if available
    global $wupos_current_product;
    
    if ($wupos_current_product) {
        $categories = get_the_terms($wupos_current_product->get_id(), 'product_cat');
        
        if ($categories) {
            foreach ($categories as $category) {
                switch ($category->slug) {
                    case 'perishables':
                        // Stricter thresholds for perishables
                        $thresholds['calculated_medium'] = $wc_low_threshold * 2;
                        $thresholds['calculated_high'] = $wc_low_threshold * 5;
                        break;
                        
                    case 'seasonal':
                        // More lenient thresholds for seasonal items
                        $thresholds['calculated_medium'] = $wc_low_threshold * 5;
                        $thresholds['calculated_high'] = $wc_low_threshold * 15;
                        break;
                }
            }
        }
    }
    
    return $thresholds;
}
```

---

## Architecture Documentation

### System Architecture Overview

The WUPOS Stock System is built on a modular architecture that integrates with WooCommerce's existing stock management while providing enhanced functionality and extensibility.

```
┌─────────────────────────────────────────────────────────────┐
│                    WUPOS Stock System                       │
├─────────────────────────────────────────────────────────────┤
│  Frontend (POS Interface)                                   │
│  ├── Product Display                                        │
│  ├── Stock Badge Rendering                                  │
│  └── Real-time Stock Updates                               │
├─────────────────────────────────────────────────────────────┤
│  Backend API Layer                                          │
│  ├── WUPOS_Products_API                                     │
│  ├── Stock Calculation Engine                               │
│  ├── Data Validation Layer                                  │
│  └── Cache Management                                       │
├─────────────────────────────────────────────────────────────┤
│  Integration Layer                                          │
│  ├── WooCommerce Stock System                              │
│  ├── WordPress Settings API                                 │
│  └── Database Layer                                         │
├─────────────────────────────────────────────────────────────┤
│  Extension Points                                           │
│  ├── WordPress Filters                                      │
│  ├── Action Hooks                                           │
│  └── Custom Event System                                    │
└─────────────────────────────────────────────────────────────┘
```

### Class Relationship Diagram

```
┌─────────────────┐
│     WUPOS       │
│  (Main Plugin)  │
└─────────┬───────┘
          │
          ├── Initializes
          │
┌─────────▼───────┐      ┌──────────────────┐
│ WUPOS_Products_ │◄────►│  WC_Product      │
│      API        │      │  (WooCommerce)   │
└─────────────────┘      └──────────────────┘
          │
          ├── Uses
          │
┌─────────▼───────┐      ┌──────────────────┐
│ Stock Calculation│◄────►│ WooCommerce      │
│    Methods       │      │ Settings API     │
└─────────────────┘      └──────────────────┘
          │
          ├── Applies
          │
┌─────────▼───────┐      ┌──────────────────┐
│ WordPress       │◄────►│ Custom Filters   │
│ Filter System   │      │ & Actions        │
└─────────────────┘      └──────────────────┘
```

### Integration with WooCommerce Stock Management

The WUPOS Stock System maintains full compatibility with WooCommerce's native stock management:

#### Data Flow

1. **Stock Data Source**: Reads from WooCommerce product stock fields
2. **Threshold Calculation**: Uses WooCommerce low stock threshold as base
3. **Status Determination**: Applies WUPOS classification logic
4. **Display Integration**: Maintains WooCommerce stock display compatibility

#### Compatibility Matrix

| WooCommerce Stock Status | WUPOS Classification | Badge Class |
|-------------------------|---------------------|-------------|
| `instock` + quantity > high threshold | `high-stock` | `wupos-stock-high` |
| `instock` + quantity between medium-high | `medium-stock` | `wupos-stock-medium` |
| `instock` + quantity < low threshold | `low-stock` | `wupos-stock-low` |
| `outofstock` | `outofstock` | `wupos-stock-out` |
| `onbackorder` | `onbackorder` | `wupos-stock-medium` |

### Stock Level Determination Logic

```php
/**
 * Stock Level Classification Logic Flow
 */
function determine_stock_level($stock_quantity, $thresholds) {
    // Step 1: Validate input data
    if (!is_numeric($stock_quantity) || $stock_quantity < 0) {
        return 'error';
    }
    
    // Step 2: Handle zero stock
    if ($stock_quantity == 0) {
        return 'out';
    }
    
    // Step 3: Apply threshold-based classification
    if ($stock_quantity < $thresholds['wc_low_threshold']) {
        return 'low';    // Red zone
    } elseif ($stock_quantity < $thresholds['calculated_medium']) {
        return 'medium'; // Yellow zone  
    } else {
        return 'high';   // Green zone
    }
}
```

### Caching Strategy

The system implements intelligent caching to optimize performance:

#### Cache Layers

1. **Object Cache**: Product stock info cached per request
2. **Transient Cache**: Threshold calculations cached for 1 hour
3. **Database Cache**: WooCommerce settings cached automatically

#### Cache Implementation

```php
/**
 * Cache-aware stock info retrieval
 */
private function get_cached_stock_info($product) {
    $cache_key = 'wupos_stock_' . $product->get_id();
    $cached_info = wp_cache_get($cache_key, 'wupos_stock');
    
    if ($cached_info !== false) {
        return $cached_info;
    }
    
    $stock_info = $this->calculate_stock_info($product);
    wp_cache_set($cache_key, $stock_info, 'wupos_stock', 300); // 5 minutes
    
    return $stock_info;
}
```

### Performance Optimizations

#### Batch Processing

```php
/**
 * Efficient batch stock processing for multiple products
 */
public function get_batch_stock_info($product_ids) {
    $stock_infos = array();
    $thresholds = $this->get_dynamic_stock_thresholds(); // Calculate once
    
    foreach ($product_ids as $product_id) {
        $product = wc_get_product($product_id);
        if ($product) {
            $stock_infos[$product_id] = $this->calculate_stock_info_with_thresholds(
                $product, 
                $thresholds
            );
        }
    }
    
    return $stock_infos;
}
```

---

## Integration Guide

### Step-by-Step Integration Instructions

#### For Third-Party Plugin Developers

**Step 1: Check WUPOS Availability**

```php
/**
 * Check if WUPOS is available and active
 */
function check_wupos_availability() {
    if (!class_exists('WUPOS_Products_API')) {
        return false;
    }
    
    // Verify minimum version
    if (defined('WUPOS_VERSION') && version_compare(WUPOS_VERSION, '1.0.0', '>=')) {
        return true;
    }
    
    return false;
}
```

**Step 2: Access Stock Information**

```php
/**
 * Get enhanced stock information for a product
 */
function get_enhanced_product_stock($product_id) {
    if (!check_wupos_availability()) {
        return false;
    }
    
    $product = wc_get_product($product_id);
    if (!$product) {
        return false;
    }
    
    $products_api = new WUPOS_Products_API();
    return $products_api->get_stock_info($product);
}

// Usage example
$stock_info = get_enhanced_product_stock(123);
if ($stock_info) {
    echo "Stock Level: " . $stock_info['stock_level'];
    echo "Stock Text: " . $stock_info['text'];
    echo "Badge Class: " . $stock_info['badge_class'];
}
```

**Step 3: Integrate with Frontend Display**

```php
/**
 * Display enhanced stock information in product listings
 */
add_action('woocommerce_after_shop_loop_item_title', 'display_wupos_stock_badge', 15);

function display_wupos_stock_badge() {
    global $product;
    
    if (!check_wupos_availability()) {
        return;
    }
    
    $products_api = new WUPOS_Products_API();
    $stock_info = $products_api->get_stock_info($product);
    
    if ($stock_info) {
        printf(
            '<div class="wupos-stock-badge %s">%s</div>',
            esc_attr($stock_info['badge_class']),
            esc_html($stock_info['text'])
        );
    }
}
```

#### For Theme Developers

**Custom Stock Display Template**

```php
/**
 * Template: wupos-stock-display.php
 * Custom stock display template for themes
 */

// Get stock information
$products_api = new WUPOS_Products_API();
$stock_info = $products_api->get_stock_info($product);

if ($stock_info): ?>
<div class="custom-stock-display">
    <div class="stock-badge <?php echo esc_attr($stock_info['badge_class']); ?>">
        <span class="stock-text"><?php echo esc_html($stock_info['text']); ?></span>
        <span class="stock-level"><?php echo esc_html(ucfirst($stock_info['stock_level'])); ?></span>
    </div>
    
    <?php if (isset($stock_info['threshold_info'])): ?>
    <div class="stock-thresholds" style="display: none;" data-thresholds='<?php echo json_encode($stock_info['threshold_info']); ?>'>
        <!-- Threshold data for JavaScript -->
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
```

#### JavaScript Integration

**Frontend Stock Display**

```javascript
/**
 * JavaScript integration for real-time stock updates
 */
class WUPOSStockDisplay {
    constructor() {
        this.initStockBadges();
    }
    
    initStockBadges() {
        const stockBadges = document.querySelectorAll('.wupos-stock-badge');
        stockBadges.forEach(badge => {
            this.enhanceBadge(badge);
        });
    }
    
    enhanceBadge(badge) {
        // Add hover effects for stock details
        badge.addEventListener('mouseenter', (e) => {
            this.showStockDetails(e.target);
        });
        
        badge.addEventListener('mouseleave', (e) => {
            this.hideStockDetails(e.target);
        });
    }
    
    showStockDetails(badge) {
        const thresholdData = badge.closest('.custom-stock-display')
            ?.querySelector('.stock-thresholds')?.dataset.thresholds;
            
        if (thresholdData) {
            const thresholds = JSON.parse(thresholdData);
            this.displayTooltip(badge, thresholds);
        }
    }
    
    displayTooltip(badge, thresholds) {
        const tooltip = document.createElement('div');
        tooltip.className = 'wupos-stock-tooltip';
        tooltip.innerHTML = `
            <div class="threshold-info">
                <div>Low: &lt; ${thresholds.wc_low_threshold}</div>
                <div>Medium: ${thresholds.wc_low_threshold} - ${thresholds.calculated_medium - 1}</div>
                <div>High: &gt;= ${thresholds.calculated_medium}</div>
            </div>
        `;
        
        badge.appendChild(tooltip);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new WUPOSStockDisplay();
});
```

### Best Practices for Integration

#### 1. Always Check Availability

```php
// Good: Check before using
if (class_exists('WUPOS_Products_API')) {
    $api = new WUPOS_Products_API();
    // Use API methods
}

// Bad: Assume availability
$api = new WUPOS_Products_API(); // May cause fatal error
```

#### 2. Handle Errors Gracefully

```php
function safe_get_stock_info($product_id) {
    try {
        if (!check_wupos_availability()) {
            return array('error' => 'WUPOS not available');
        }
        
        $product = wc_get_product($product_id);
        if (!$product) {
            return array('error' => 'Product not found');
        }
        
        $products_api = new WUPOS_Products_API();
        return $products_api->get_stock_info($product);
        
    } catch (Exception $e) {
        error_log('WUPOS Integration Error: ' . $e->getMessage());
        return array('error' => $e->getMessage());
    }
}
```

#### 3. Use Appropriate Hooks

```php
// Good: Use specific hooks for customization
add_filter('wupos_product_stock_info', 'customize_stock_info', 10, 3);

// Good: Use init hooks for setup
add_action('wupos_init', 'setup_custom_stock_features');

// Avoid: Direct method overrides (not supported)
```

#### 4. Respect Caching

```php
function get_cached_enhanced_stock($product_id) {
    $cache_key = 'custom_stock_' . $product_id;
    $cached = wp_cache_get($cache_key, 'custom_stock');
    
    if ($cached !== false) {
        return $cached;
    }
    
    $stock_info = get_enhanced_product_stock($product_id);
    wp_cache_set($cache_key, $stock_info, 'custom_stock', 300);
    
    return $stock_info;
}
```

### Migration Notes for Existing Installations

#### Updating from Basic Stock Display

**Before (Basic WooCommerce)**:
```php
// Old approach
$stock_status = $product->get_stock_status();
$stock_quantity = $product->get_stock_quantity();

if ($stock_status === 'instock') {
    echo "In Stock";
} else {
    echo "Out of Stock";
}
```

**After (WUPOS Enhanced)**:
```php
// New approach with WUPOS
$products_api = new WUPOS_Products_API();
$stock_info = $products_api->get_stock_info($product);

echo '<span class="' . $stock_info['badge_class'] . '">';
echo $stock_info['text'];
echo '</span>';

// Access detailed information
$classification = $stock_info['stock_classification'];
echo "Stock Level: " . $classification['level'];
echo "Quantity: " . $classification['quantity'];
```

#### Database Considerations

No database migrations are required. The WUPOS Stock System reads from existing WooCommerce product meta fields and adds calculated fields on-the-fly.

#### Theme Compatibility

Existing themes will continue to work without modification. Enhanced features are available through new classes and methods without affecting existing functionality.

---

## Troubleshooting and Debugging

### Common Issues and Solutions

#### Issue 1: Stock Levels Not Updating

**Symptoms**:
- Stock badges show incorrect information
- Stock levels don't change after inventory updates

**Diagnosis**:
```php
// Debug stock calculation
$products_api = new WUPOS_Products_API();
$debug_info = $products_api->debug_stock_calculation(10); // Test with 10 units

error_log('WUPOS Debug: ' . print_r($debug_info, true));
```

**Solutions**:

1. **Clear Cache**:
```php
// Clear WUPOS stock cache
wp_cache_flush_group('wupos_stock');

// Clear all object cache
wp_cache_flush();
```

2. **Check WooCommerce Settings**:
```php
// Verify WooCommerce low stock threshold
$wc_threshold = get_option('woocommerce_notify_low_stock_amount', 2);
error_log('WooCommerce Low Stock Threshold: ' . $wc_threshold);
```

3. **Validate Product Data**:
```php
$product = wc_get_product($product_id);
$products_api = new WUPOS_Products_API();
$validated_data = $products_api->validate_stock_data($product);

if (isset($validated_data['error'])) {
    error_log('Stock Data Validation Error: ' . $validated_data['error']);
}
```

#### Issue 2: Incorrect Threshold Calculations

**Symptoms**:
- Stock levels classified incorrectly
- Thresholds don't match expected values

**Diagnosis**:
```php
// Check current thresholds
$products_api = new WUPOS_Products_API();
$thresholds = $products_api->get_dynamic_stock_thresholds();

error_log('Current Thresholds: ' . print_r($thresholds, true));

// Test classification with specific quantities
$test_quantities = array(1, 5, 10, 15, 20, 50);
foreach ($test_quantities as $qty) {
    $result = $products_api->debug_stock_calculation($qty);
    error_log("Quantity $qty -> Level: " . $result['calculated_level']);
}
```

**Solutions**:

1. **Verify WooCommerce Setting**:
```php
// Check if setting exists and is valid
$setting = get_option('woocommerce_notify_low_stock_amount');
if (empty($setting) || !is_numeric($setting)) {
    update_option('woocommerce_notify_low_stock_amount', 2);
}
```

2. **Custom Threshold Override**:
```php
// Temporarily override thresholds for testing
add_filter('wupos_stock_level_thresholds', function($thresholds, $wc_low) {
    error_log('Original thresholds: ' . print_r($thresholds, true));
    
    // Force specific values for testing
    $thresholds['wc_low_threshold'] = 5;
    $thresholds['calculated_medium'] = 15;
    $thresholds['calculated_high'] = 50;
    
    return $thresholds;
}, 10, 2);
```

#### Issue 3: Performance Problems

**Symptoms**:
- Slow product loading
- High server resource usage
- Timeout errors

**Diagnosis**:
```php
// Monitor stock calculation performance
add_action('wupos_before_stock_calculation', function($product) {
    $GLOBALS['wupos_start_time'] = microtime(true);
});

add_action('wupos_after_stock_calculation', function($stock_info, $product) {
    $execution_time = microtime(true) - $GLOBALS['wupos_start_time'];
    if ($execution_time > 0.1) { // Log if takes more than 100ms
        error_log("WUPOS: Slow stock calculation for product {$product->get_id()}: {$execution_time}s");
    }
});
```

**Solutions**:

1. **Enable Object Caching**:
```php
// Add to wp-config.php
define('WP_CACHE', true);

// Or use transients for longer caching
function get_cached_stock_info($product_id) {
    $cache_key = 'wupos_stock_' . $product_id;
    $cached = get_transient($cache_key);
    
    if ($cached === false) {
        $product = wc_get_product($product_id);
        $products_api = new WUPOS_Products_API();
        $cached = $products_api->get_stock_info($product);
        set_transient($cache_key, $cached, 300); // 5 minutes
    }
    
    return $cached;
}
```

2. **Optimize Database Queries**:
```php
// Batch process multiple products
function get_multiple_stock_info($product_ids) {
    $products_api = new WUPOS_Products_API();
    $thresholds = $products_api->get_dynamic_stock_thresholds(); // Calculate once
    
    $results = array();
    foreach ($product_ids as $id) {
        $product = wc_get_product($id);
        if ($product) {
            $results[$id] = calculate_with_cached_thresholds($product, $thresholds);
        }
    }
    
    return $results;
}
```

### Debug Logging Instructions

#### Enable Debug Logging

```php
// Add to wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Enable WUPOS specific logging
define('WUPOS_DEBUG', true);
```

#### Custom Debug Functions

```php
/**
 * WUPOS Debug Logger
 */
function wupos_debug_log($message, $data = null) {
    if (!defined('WUPOS_DEBUG') || !WUPOS_DEBUG) {
        return;
    }
    
    $log_message = 'WUPOS DEBUG: ' . $message;
    if ($data !== null) {
        $log_message .= ' | Data: ' . print_r($data, true);
    }
    
    error_log($log_message);
}

// Usage examples
wupos_debug_log('Stock calculation started', array('product_id' => 123));
wupos_debug_log('Thresholds calculated', $thresholds);
wupos_debug_log('Stock info generated', $stock_info);
```

#### Debug Stock Calculation Flow

```php
/**
 * Complete debug trace for stock calculation
 */
function debug_complete_stock_flow($product_id) {
    wupos_debug_log('=== STOCK DEBUG START ===', array('product_id' => $product_id));
    
    $product = wc_get_product($product_id);
    if (!$product) {
        wupos_debug_log('ERROR: Product not found');
        return;
    }
    
    wupos_debug_log('Product loaded', array(
        'name' => $product->get_name(),
        'type' => $product->get_type(),
        'manage_stock' => $product->get_manage_stock()
    ));
    
    $products_api = new WUPOS_Products_API();
    
    // Debug validation
    $validated_data = $products_api->validate_stock_data($product);
    wupos_debug_log('Data validation', $validated_data);
    
    // Debug thresholds
    $thresholds = $products_api->get_dynamic_stock_thresholds();
    wupos_debug_log('Calculated thresholds', $thresholds);
    
    // Debug final calculation
    $stock_info = $products_api->get_stock_info($product);
    wupos_debug_log('Final stock info', $stock_info);
    
    wupos_debug_log('=== STOCK DEBUG END ===');
    
    return $stock_info;
}

// Usage
debug_complete_stock_flow(123);
```

### Error Resolution Steps

#### Step-by-Step Troubleshooting Process

1. **Verify Prerequisites**:
   - WordPress 5.0+
   - WooCommerce 5.0+
   - PHP 7.4+
   - WUPOS 1.0.0+

2. **Check Plugin Activation**:
   ```php
   if (!class_exists('WUPOS_Products_API')) {
       error_log('WUPOS not properly activated');
   }
   ```

3. **Validate WooCommerce Integration**:
   ```php
   if (!class_exists('WooCommerce')) {
       error_log('WooCommerce not active');
   }
   ```

4. **Test Basic Functionality**:
   ```php
   $test_product_id = 123; // Replace with valid product ID
   debug_complete_stock_flow($test_product_id);
   ```

5. **Clear All Caches**:
   ```php
   wp_cache_flush();
   delete_option('_transient_timeout_wupos_thresholds');
   delete_option('_transient_wupos_thresholds');
   ```

6. **Check Server Resources**:
   - PHP memory limit (recommended: 256MB+)
   - PHP execution time (recommended: 60s+)
   - Database connection limits

### Common Error Messages

#### "Invalid product object"
**Cause**: Product not found or corrupted
**Solution**: Verify product exists and is published

#### "Security check failed"
**Cause**: Nonce verification failure
**Solution**: Clear browser cache, check AJAX nonce generation

#### "WooCommerce is not active"
**Cause**: WooCommerce plugin not activated
**Solution**: Activate WooCommerce plugin

#### "Insufficient permissions"
**Cause**: User lacks required capabilities
**Solution**: Ensure user has `manage_woocommerce` or `edit_shop_orders` capability

---

## Performance Considerations

### Optimization Strategies

#### 1. Caching Implementation

**Object Cache Strategy**:
```php
/**
 * Efficient caching for stock calculations
 */
class WUPOS_Stock_Cache {
    private $cache_group = 'wupos_stock';
    private $cache_expiry = 300; // 5 minutes
    
    public function get_cached_stock_info($product_id) {
        $cache_key = $this->get_cache_key($product_id);
        $cached_data = wp_cache_get($cache_key, $this->cache_group);
        
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        return null;
    }
    
    public function set_stock_cache($product_id, $stock_info) {
        $cache_key = $this->get_cache_key($product_id);
        wp_cache_set($cache_key, $stock_info, $this->cache_group, $this->cache_expiry);
    }
    
    private function get_cache_key($product_id) {
        // Include WC low stock threshold in cache key for invalidation
        $wc_threshold = get_option('woocommerce_notify_low_stock_amount', 2);
        return "stock_info_{$product_id}_{$wc_threshold}";
    }
}
```

**Transient Cache for Thresholds**:
```php
/**
 * Cache threshold calculations using WordPress transients
 */
private function get_cached_thresholds() {
    $cache_key = 'wupos_stock_thresholds';
    $cached_thresholds = get_transient($cache_key);
    
    if ($cached_thresholds === false) {
        $cached_thresholds = $this->calculate_thresholds();
        set_transient($cache_key, $cached_thresholds, HOUR_IN_SECONDS);
    }
    
    return $cached_thresholds;
}
```

#### 2. Database Query Optimization

**Batch Processing**:
```php
/**
 * Process multiple products efficiently
 */
public function get_batch_stock_info($product_ids, $use_cache = true) {
    $results = array();
    $uncached_ids = array();
    
    // Check cache first
    if ($use_cache) {
        foreach ($product_ids as $id) {
            $cached = wp_cache_get("stock_info_$id", 'wupos_stock');
            if ($cached !== false) {
                $results[$id] = $cached;
            } else {
                $uncached_ids[] = $id;
            }
        }
    } else {
        $uncached_ids = $product_ids;
    }
    
    // Process uncached products
    if (!empty($uncached_ids)) {
        $thresholds = $this->get_dynamic_stock_thresholds(); // Calculate once
        
        foreach ($uncached_ids as $id) {
            $product = wc_get_product($id);
            if ($product) {
                $stock_info = $this->calculate_stock_info_with_thresholds($product, $thresholds);
                $results[$id] = $stock_info;
                
                // Cache the result
                if ($use_cache) {
                    wp_cache_set("stock_info_$id", $stock_info, 'wupos_stock', 300);
                }
            }
        }
    }
    
    return $results;
}
```

**Efficient Product Loading**:
```php
/**
 * Optimize product loading for stock calculations
 */
public function get_products_with_stock_info($args = array()) {
    // Use WooCommerce's optimized product query
    $default_args = array(
        'status'    => 'publish',
        'limit'     => 20,
        'return'    => 'objects',
        'meta_query' => array(
            array(
                'key'     => '_manage_stock',
                'value'   => 'yes',
                'compare' => '='
            )
        )
    );
    
    $args = wp_parse_args($args, $default_args);
    $products = wc_get_products($args);
    
    // Process in batch for better performance
    $product_ids = wp_list_pluck($products, 'id');
    $stock_infos = $this->get_batch_stock_info($product_ids);
    
    // Merge stock info with product data
    foreach ($products as &$product) {
        if (isset($stock_infos[$product->get_id()])) {
            $product->wupos_stock_info = $stock_infos[$product->get_id()];
        }
    }
    
    return $products;
}
```

#### 3. Memory Management

**Memory-Efficient Processing**:
```php
/**
 * Process large product sets without memory exhaustion
 */
public function process_large_product_set($product_ids, $batch_size = 50) {
    $results = array();
    $batches = array_chunk($product_ids, $batch_size);
    
    foreach ($batches as $batch) {
        $batch_results = $this->get_batch_stock_info($batch);
        $results = array_merge($results, $batch_results);
        
        // Clear memory after each batch
        foreach ($batch as $id) {
            wp_cache_delete("product_$id", 'posts');
        }
        
        // Force garbage collection for large sets
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }
    
    return $results;
}
```

### Performance Monitoring

#### Built-in Performance Tracking

```php
/**
 * Performance monitoring for stock calculations
 */
class WUPOS_Performance_Monitor {
    private $start_times = array();
    private $metrics = array();
    
    public function start_timing($operation) {
        $this->start_times[$operation] = microtime(true);
    }
    
    public function end_timing($operation) {
        if (isset($this->start_times[$operation])) {
            $duration = microtime(true) - $this->start_times[$operation];
            $this->metrics[$operation][] = $duration;
            
            // Log slow operations
            if ($duration > 0.5) { // 500ms threshold
                error_log("WUPOS Performance: Slow $operation took {$duration}s");
            }
        }
    }
    
    public function get_metrics() {
        $summary = array();
        foreach ($this->metrics as $operation => $times) {
            $summary[$operation] = array(
                'count' => count($times),
                'total' => array_sum($times),
                'average' => array_sum($times) / count($times),
                'max' => max($times),
                'min' => min($times)
            );
        }
        return $summary;
    }
}

// Usage in stock calculations
$monitor = new WUPOS_Performance_Monitor();

$monitor->start_timing('stock_calculation');
$stock_info = $this->get_stock_info($product);
$monitor->end_timing('stock_calculation');

$monitor->start_timing('threshold_calculation');
$thresholds = $this->get_dynamic_stock_thresholds();
$monitor->end_timing('threshold_calculation');
```

#### Database Query Monitoring

```php
/**
 * Monitor database queries during stock operations
 */
add_action('wupos_before_stock_calculation', function() {
    if (defined('SAVEQUERIES')) {
        global $wpdb;
        $wpdb->wupos_query_count_start = count($wpdb->queries);
    }
});

add_action('wupos_after_stock_calculation', function($stock_info, $product) {
    if (defined('SAVEQUERIES')) {
        global $wpdb;
        $query_count = count($wpdb->queries) - $wpdb->wupos_query_count_start;
        
        if ($query_count > 5) { // Log if more than 5 queries
            error_log("WUPOS: Stock calculation for product {$product->get_id()} used $query_count queries");
        }
    }
}, 10, 2);
```

### Recommended Server Configuration

#### PHP Configuration

```ini
; Recommended PHP settings for WUPOS
memory_limit = 256M
max_execution_time = 60
max_input_vars = 3000
post_max_size = 64M
upload_max_filesize = 64M

; Object caching support
extension=redis    ; or memcached
```

#### WordPress Configuration

```php
// wp-config.php optimizations
define('WP_CACHE', true);
define('COMPRESS_CSS', true);
define('COMPRESS_SCRIPTS', true);
define('CONCATENATE_SCRIPTS', false); // May cause issues with some plugins

// Object cache (if available)
define('WP_CACHE_KEY_SALT', 'wupos_unique_key');

// Database optimization
define('WP_DEBUG_DISPLAY', false);
define('SAVEQUERIES', false); // Only enable for debugging
```

#### MySQL Optimization

```sql
-- Recommended MySQL settings for WooCommerce/WUPOS
SET GLOBAL innodb_buffer_pool_size = 1G;
SET GLOBAL query_cache_size = 256M;
SET GLOBAL query_cache_type = 1;

-- Index optimization for stock queries
ALTER TABLE wp_postmeta ADD INDEX wupos_stock_idx (_meta_key, _meta_value(10));
```

---

## Security Considerations

### Input Validation and Sanitization

#### Product Data Validation

```php
/**
 * Comprehensive product data validation
 */
private function validate_product_input($product_id) {
    // Sanitize product ID
    $product_id = absint($product_id);
    
    if ($product_id <= 0) {
        throw new InvalidArgumentException('Invalid product ID provided');
    }
    
    // Verify product exists and is valid
    $product = wc_get_product($product_id);
    if (!$product || $product->get_status() !== 'publish') {
        throw new InvalidArgumentException('Product not found or not available');
    }
    
    return $product;
}
```

#### Stock Quantity Validation

```php
/**
 * Secure stock quantity validation
 */
private function validate_stock_quantity($quantity) {
    // Handle null/empty values
    if ($quantity === null || $quantity === '') {
        return null;
    }
    
    // Sanitize numeric input
    if (!is_numeric($quantity)) {
        throw new InvalidArgumentException('Stock quantity must be numeric');
    }
    
    $quantity = floatval($quantity);
    
    // Validate range (prevent negative stock)
    if ($quantity < 0) {
        $quantity = 0;
    }
    
    // Prevent extremely large values (potential DoS)
    if ($quantity > 999999999) {
        throw new InvalidArgumentException('Stock quantity exceeds maximum allowed value');
    }
    
    return intval($quantity);
}
```

### Access Control and Permissions

#### Permission Checking

```php
/**
 * Comprehensive permission validation
 */
private function validate_user_permissions($context = 'read') {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        throw new UnauthorizedAccessException('User must be logged in');
    }
    
    $current_user = wp_get_current_user();
    
    // Context-specific permission checks
    switch ($context) {
        case 'read':
            if (!current_user_can('read_private_shop_orders') && 
                !current_user_can('manage_woocommerce')) {
                throw new UnauthorizedAccessException('Insufficient permissions to read stock data');
            }
            break;
            
        case 'modify':
            if (!current_user_can('manage_woocommerce')) {
                throw new UnauthorizedAccessException('Insufficient permissions to modify stock settings');
            }
            break;
            
        case 'admin':
            if (!current_user_can('manage_options')) {
                throw new UnauthorizedAccessException('Administrator privileges required');
            }
            break;
    }
    
    return true;
}
```

#### AJAX Security

```php
/**
 * Secure AJAX request handling
 */
public function secure_ajax_handler() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wupos_nonce')) {
        wp_send_json_error(array(
            'message' => __('Security verification failed', 'wupos'),
            'code' => 'invalid_nonce'
        ));
        return;
    }
    
    // Rate limiting (basic implementation)
    $user_id = get_current_user_id();
    $rate_key = "wupos_rate_limit_$user_id";
    $current_requests = get_transient($rate_key);
    
    if ($current_requests && $current_requests > 100) { // 100 requests per minute
        wp_send_json_error(array(
            'message' => __('Rate limit exceeded', 'wupos'),
            'code' => 'rate_limit_exceeded'
        ));
        return;
    }
    
    set_transient($rate_key, ($current_requests + 1), 60);
    
    // Validate permissions
    try {
        $this->validate_user_permissions('read');
    } catch (Exception $e) {
        wp_send_json_error(array(
            'message' => $e->getMessage(),
            'code' => 'permission_denied'
        ));
        return;
    }
    
    // Process request...
}
```

### Data Protection

#### Sensitive Data Handling

```php
/**
 * Secure handling of sensitive stock data
 */
private function sanitize_stock_output($stock_info) {
    // Remove sensitive internal data from public output
    $public_fields = array(
        'status',
        'stock_level', 
        'badge_class',
        'text',
        'stock_classification'
    );
    
    $sanitized = array();
    foreach ($public_fields as $field) {
        if (isset($stock_info[$field])) {
            $sanitized[$field] = $stock_info[$field];
        }
    }
    
    // Sanitize text output
    if (isset($sanitized['text'])) {
        $sanitized['text'] = esc_html($sanitized['text']);
    }
    
    // Sanitize CSS classes
    if (isset($sanitized['badge_class'])) {
        $sanitized['badge_class'] = sanitize_html_class($sanitized['badge_class']);
    }
    
    return $sanitized;
}
```

#### SQL Injection Prevention

```php
/**
 * Safe database queries for stock operations
 */
private function get_products_by_stock_level($stock_level, $limit = 20) {
    global $wpdb;
    
    // Validate and sanitize input
    $allowed_levels = array('low', 'medium', 'high', 'out');
    if (!in_array($stock_level, $allowed_levels)) {
        throw new InvalidArgumentException('Invalid stock level specified');
    }
    
    $limit = absint($limit);
    if ($limit > 100) { // Prevent excessive queries
        $limit = 100;
    }
    
    // Use prepared statements
    $query = $wpdb->prepare("
        SELECT p.ID, p.post_title, pm.meta_value as stock_quantity
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = %s
        AND p.post_status = %s  
        AND pm.meta_key = %s
        AND pm.meta_value < %d
        LIMIT %d
    ", 'product', 'publish', '_stock', 10, $limit);
    
    return $wpdb->get_results($query);
}
```

### Error Handling and Logging

#### Secure Error Reporting

```php
/**
 * Secure error handling that doesn't expose sensitive information
 */
private function handle_stock_error($error, $context = array()) {
    // Log detailed error for administrators
    $log_message = sprintf(
        'WUPOS Stock Error: %s | Context: %s | User: %d | IP: %s',
        $error->getMessage(),
        json_encode($context),
        get_current_user_id(),
        $this->get_client_ip()
    );
    
    error_log($log_message);
    
    // Return generic error to frontend users
    $public_message = __('An error occurred while processing stock information', 'wupos');
    
    // Show detailed errors only to administrators
    if (current_user_can('manage_options') && defined('WP_DEBUG') && WP_DEBUG) {
        $public_message = $error->getMessage();
    }
    
    return array(
        'error' => true,
        'message' => $public_message,
        'code' => $error->getCode()
    );
}

/**
 * Safely get client IP address
 */
private function get_client_ip() {
    $ip_keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
    
    foreach ($ip_keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = sanitize_text_field($_SERVER[$key]);
            // Validate IP format
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return 'unknown';
}
```

### Security Best Practices

#### 1. Input Validation Checklist

- ✅ Sanitize all user inputs
- ✅ Validate data types and ranges
- ✅ Use prepared statements for database queries
- ✅ Implement rate limiting for AJAX requests
- ✅ Verify nonces for all form submissions

#### 2. Permission Management

- ✅ Check user capabilities before processing requests
- ✅ Implement role-based access control
- ✅ Log security-related events
- ✅ Use WordPress capability system consistently

#### 3. Data Protection

- ✅ Escape output for display
- ✅ Remove sensitive data from public APIs
- ✅ Implement secure error handling
- ✅ Use HTTPS for all communications

#### 4. Code Security

```php
/**
 * Security-focused code example
 */
public function secure_get_stock_info($product_id) {
    try {
        // Step 1: Validate permissions
        $this->validate_user_permissions('read');
        
        // Step 2: Validate and sanitize input
        $product = $this->validate_product_input($product_id);
        
        // Step 3: Process with validated data
        $stock_info = $this->calculate_stock_info($product);
        
        // Step 4: Sanitize output
        return $this->sanitize_stock_output($stock_info);
        
    } catch (Exception $e) {
        // Step 5: Handle errors securely
        return $this->handle_stock_error($e, array('product_id' => $product_id));
    }
}
```

---

## Conclusion

This technical documentation provides comprehensive coverage of the WUPOS Phase 1 Backend Stock System implementation. The system offers:

- **Advanced Stock Management**: Dynamic threshold calculations and three-tier classification
- **Developer Extensibility**: Comprehensive hooks and filters for customization
- **Performance Optimization**: Efficient caching and batch processing capabilities
- **Security**: Robust input validation and access control
- **Integration Friendly**: Easy integration with existing WordPress/WooCommerce sites

For additional support or advanced customization requirements, refer to the troubleshooting section or contact the WUPOS development team.

### Version History

- **v1.0.0**: Initial implementation with basic stock classification
- **Current**: Enhanced with comprehensive API, caching, and security features

### Contributing

When extending the WUPOS stock system:

1. Follow WordPress coding standards
2. Implement proper error handling
3. Add appropriate filters and actions
4. Include comprehensive documentation
5. Test with various WooCommerce configurations

---

*This documentation is maintained by the WUPOS development team and is current as of WUPOS v1.0.0.*