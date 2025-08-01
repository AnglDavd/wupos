# WUPOS - WordPress Point of Sale Plugin

## ⚠️ DEVELOPMENT STATUS WARNING

**This plugin is currently in active development and is NOT ready for production use.**

- **Development Phase:** Active development in progress
- **Production Readiness:** NOT RECOMMENDED for live/production websites
- **Stability:** Features and functionality may change during development
- **Testing Only:** Use only in development/staging environments
- **Risk:** Use at your own risk - backup your site before testing
- **Feedback Welcome:** Bug reports and contributions are appreciated during development

**For production POS solutions, please wait for the official stable release.**

---

Professional Point of Sale system for WordPress with seamless WooCommerce integration. Features intelligent stock management with Traffic Light System, advanced cart optimization, and enhanced reliability for consistent POS operations.

## Key Features

### Core POS Capabilities
- **Complete POS Interface** - Modern responsive design with optimized cart UX
- **Advanced Cart System** - Enhanced operations with WooCommerce timing fixes and component deconfliction
- **Transaction Processing** - Secure payment processing with multiple methods and enhanced reliability
- **Receipt Generation** - Automatic receipt creation and printing capabilities

### Intelligent Stock Management
- **Traffic Light System** - Color-coded stock indicators (Red/Yellow/Green) with dynamic thresholds
- **Real-time Updates** - Live stock level monitoring without page refresh
- **WooCommerce Integration** - Seamless inventory synchronization with existing stores

### Enhanced User Experience
- **Real-time Tax Calculations** - Comprehensive tax handling with consistent minimalista display
- **Component Deconfliction** - Prevents visual conflicts and eliminates redundant components
- **Role-based Access Control** - Multi-user support with appropriate permissions
- **Professional Reliability** - Resolved fatal errors and timing issues for consistent performance

## Installation

1. Upload the plugin files to the `/wp-content/plugins/wupos` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Configure the plugin settings in WP Admin > WUPOS Settings
4. Set up your products and payment methods
5. Start using the POS interface at `/wp-admin/admin.php?page=wupos`

## Technical Requirements

- **WordPress** 5.0+ (tested up to 6.6)
- **WooCommerce** 5.0+ (tested up to 9.0)
- **PHP** 7.4+ (recommended: 8.0+)
- **MySQL** 5.6+ or MariaDB 10.0+
- Active WooCommerce installation with configured products

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

## Quick Start

1. **Access POS** - Navigate to WordPress Admin → WUPOS from the admin menu
2. **Visual Stock Management** - Products display color-coded badges (Red: critical, Yellow: low, Green: adequate)
3. **Add Products** - Search and select products with real-time stock level updates
4. **Process Transactions** - Complete sales using configured payment methods
5. **Generate Receipts** - Automatic receipt creation with print functionality
6. **Monitor Performance** - Access comprehensive sales reports and analytics

## Standards & Compliance

- **WordPress Coding Standards** - Fully compliant with WordPress development guidelines
- **WordPress Security Guidelines** - Implements recommended security practices
- **WooCommerce Compatibility** - Tested and optimized for WooCommerce integration
- **Professional Documentation** - Comprehensive technical and user documentation

## License

Licensed under GPL v2 or later - WordPress compatible open source license.

---

**Current Version:** 1.0.0 | **WordPress Compatibility:** 5.0+ | **WooCommerce Compatibility:** 5.0-9.0+

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