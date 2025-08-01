# WUPOS - WordPress Point of Sale Plugin

A comprehensive Point of Sale (POS) system for WordPress that integrates seamlessly with WooCommerce, featuring intelligent stock management, advanced cart UX optimization, and reliable real-time operations with enhanced WooCommerce timing integration.

## Features

- **Complete POS Interface**: Modern, responsive point of sale interface with optimized cart UX
- **Advanced Cart System**: Enhanced cart operations with WooCommerce timing fixes and component conflict resolution
- **Intelligent Stock Management with Traffic Light System**: Dynamic stock level indicators with configurable thresholds (Red: critical, Yellow: low, Green: adequate)
- **Product Management**: Easy product search, selection, and inventory management with real-time stock status  
- **Transaction Processing**: Secure payment processing with multiple payment methods and enhanced reliability
- **Receipt Generation**: Automatic receipt creation and printing
- **Sales Reports**: Comprehensive reporting and analytics
- **User Management**: Role-based access control for different user types
- **WooCommerce Integration**: Full integration with existing WooCommerce stores with enhanced timing compatibility
- **Real-time Tax Calculations**: Comprehensive tax handling with consistent minimalista display and dynamic updates
- **Component Deconfliction**: Advanced system preventing visual conflicts and redundant components
- **Enhanced Reliability**: Resolved fatal errors and timing issues for consistent performance

## Installation

1. Upload the plugin files to the `/wp-content/plugins/wupos` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Configure the plugin settings in WP Admin > WUPOS Settings
4. Set up your products and payment methods
5. Start using the POS interface at `/wp-admin/admin.php?page=wupos`

## Requirements

- WordPress 5.0 or higher
- WooCommerce 5.0 or higher (tested up to 9.0)
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Active WooCommerce installation with products configured

## Configuration

### Stock Management Configuration

WUPOS uses dynamic stock thresholds based on your WooCommerce settings:

1. **Navigate to** WooCommerce → Settings → Products → Inventory
2. **Configure Low Stock Threshold**: Set your preferred low stock amount (default: 2)
3. **WUPOS automatically calculates**:
   - **Red (Critical)**: Below WooCommerce low stock threshold
   - **Yellow (Low)**: Between low threshold and 3x low threshold
   - **Green (Adequate)**: 3x low threshold or above

**Example**: If your WooCommerce low stock threshold is 5:
- Red: 0-4 units
- Yellow: 5-14 units  
- Green: 15+ units

### Tax Configuration

WUPOS integrates with WooCommerce tax settings:

1. **Enable Taxes**: WooCommerce → Settings → Tax → Enable taxes
2. **Configure Tax Rates**: Set up your tax classes and rates
3. **Price Display**: Choose whether to display prices including or excluding tax
4. **WUPOS automatically handles**: Tax calculations, display suffixes, and breakdowns

## Usage

1. Access the POS interface from the WordPress admin menu
2. **Stock Indicators**: Products display color-coded stock badges (Red/Yellow/Green)
3. Search and select products to add to cart
4. **Real-time Updates**: Stock levels update dynamically as you add items
5. Process payments using configured payment methods
6. Generate and print receipts
7. View sales reports and analytics

## Support

For support and documentation, please visit our support channels.

## License

This plugin is licensed under the GPL v2 or later.

## Version

Current Version: 1.0.0

### Recent Major Improvements (August 2025)

**Cart UX Optimization System:**
- ✅ Resolved WooCommerce timing issues causing fatal errors
- ✅ Implemented consistent minimalista tax display across all cart states
- ✅ Enhanced cart reliability with proper initialization sequence
- ✅ Eliminated component conflicts and visual redundancies
- ✅ Improved real-time tax calculation and display updates

**Technical Enhancements:**
- ✅ Fixed cart access timing with proper `wp_loaded` hook integration
- ✅ Enhanced WooCommerce compatibility and integration stability
- ✅ Implemented component deconfliction system
- ✅ Optimized cart performance and reduced conflicts
- ✅ Enhanced error handling and system reliability

## API Documentation

### Enhanced Product Endpoints

WUPOS provides enhanced REST API endpoints with comprehensive stock information:

#### Get Products
```
POST /wp-admin/admin-ajax.php
Action: wupos_get_products
```

**Response includes enhanced stock data:**
```json
{
  "success": true,
  "data": {
    "products": [
      {
        "id": 123,
        "name": "Sample Product",
        "price": 29.99,
        "stock_quantity": 15,
        "stock_status": "high-stock",
        "stock_level": "high",
        "stock_badge_class": "wupos-stock-high",
        "stock_text": "15 en stock",
        "stock_threshold_low": 5,
        "stock_threshold_medium": 15,
        "stock_threshold_high": 50,
        "stock_classification": {
          "level": "high",
          "quantity": 15,
          "thresholds": {
            "low": 5,
            "medium": 15,
            "high": 50
          }
        }
      }
    ]
  }
}
```

#### Calculate Taxes
```
POST /wp-admin/admin-ajax.php
Action: wupos_calculate_taxes
```

**Response includes comprehensive tax breakdown:**
```json
{
  "success": true,
  "data": {
    "subtotal": 100.00,
    "tax_total": 15.00,
    "total": 115.00,
    "tax_breakdown": [
      {
        "label": "VAT 15%",
        "amount": 15.00,
        "formatted_amount": "$15.00"
      }
    ],
    "tax_enabled": true,
    "tax_inclusive": false
  }
}
```

## Phase 2 & 3 Implementation (Coming Soon)

### Phase 2: CSS Implementation
- Visual stock badge system with traffic light colors
- Enhanced product card layouts
- Responsive design improvements
- Custom CSS for stock indicators

### Phase 3: JavaScript Enhancement
- Real-time stock updates without page refresh
- Dynamic cart synchronization
- Enhanced user interactions
- Progressive web app features

*UI screenshots and visual demonstrations will be added upon completion of Phase 2 visual implementation.*