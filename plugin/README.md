# WUPOS - Professional Point of Sale for WooCommerce

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/AnglDavd/wupos/releases)
[![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/woocommerce-8.0%2B-purple.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/php-8.0%2B-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-GPL%20v2%2B-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Professional Terminal POS for WooCommerce with modern interface and robust functionality. 100% WooCommerce native integration with zero custom tables.

## 🎯 Features

### 🏪 Point of Sale Interface
- **Modern POS Interface**: Clean, responsive design optimized for touch and desktop
- **Product Grid**: 5-6 columns responsive grid with real-time search
- **Shopping Cart**: Fixed sidebar with real-time updates and calculations
- **2-Click Sales**: Maximum 2 clicks to complete a standard sale

### 💳 Payment Processing
- **Multiple Payment Methods**: Cash, Card Terminal, Bank Transfer
- **Split Payments**: Divide payment between multiple methods
- **Real-time Processing**: Instant payment confirmation and receipt generation
- **Change Calculation**: Automatic change calculation for cash payments

### 👥 Customer Management
- **Quick Customer Search**: Real-time search by name, email, or phone
- **Customer Registration**: Fast customer creation during checkout
- **Purchase History**: Complete customer transaction history
- **Loyalty Integration**: Compatible with WooCommerce loyalty plugins

### 📊 Inventory & Stock
- **Real-time Stock Updates**: Live inventory synchronization with WooCommerce
- **Low Stock Alerts**: Automatic notifications for products running low
- **Product Variations**: Full support for variable products with attributes
- **Barcode Support**: Barcode scanning for quick product addition

### 🔐 Security & Access Control
- **Role-Based Access**: Granular permissions for different user levels
- **Secure Sessions**: WordPress native authentication with session management
- **Audit Trail**: Complete logging of all POS transactions
- **Terminal Management**: Multi-terminal support with individual configurations

## 🛠️ Technical Specifications

### Requirements
- **WordPress**: 6.0 or higher
- **WooCommerce**: 8.0 or higher (HPOS compatible)
- **PHP**: 8.0 or higher
- **Memory**: 128MB minimum (256MB recommended)

### Performance Targets
- **Load Time**: <2 seconds for POS interface
- **API Response**: <100ms average response time
- **Bundle Size**: <300KB (JS + CSS combined)
- **Database Queries**: <50 per POS page load

### Architecture
- **100% WooCommerce Native**: Uses only WooCommerce and WordPress tables
- **Zero Custom Tables**: No additional database tables required
- **REST API**: Custom endpoints built on WordPress REST API
- **Modern Frontend**: Vanilla JavaScript with jQuery integration

## 📦 Installation

### Automatic Installation (Recommended)
1. Download the latest `wupos.zip` from [Releases](https://github.com/AnglDavd/wupos/releases)
2. Go to **WordPress Admin → Plugins → Add New → Upload Plugin**
3. Select the `wupos.zip` file and click **Install Now**
4. Click **Activate Plugin**

### Manual Installation
1. Download and extract the plugin files
2. Upload the `wupos` folder to `/wp-content/plugins/`
3. Activate the plugin through **WordPress Admin → Plugins**

### Post-Installation Setup
1. Go to **WooCommerce → Settings → Advanced → WUPOS**
2. Configure your POS settings and terminal information
3. Set up user roles and permissions
4. Access the POS interface at `yoursite.com/pos/`

## 🚀 Quick Start

### Basic Configuration
1. **Terminal Setup**: Configure your POS terminal in **WUPOS → Terminals**
2. **User Roles**: Assign POS access to cashiers in **Users → All Users**
3. **Payment Methods**: Enable payment methods in **WooCommerce → Payments**
4. **Product Setup**: Ensure products have proper stock management enabled

### First Sale
1. Access POS interface at `yoursite.com/pos/`
2. Search and select products from the grid
3. Add customer information (optional)
4. Choose payment method and process payment
5. Print receipt or send via email

## 🔧 Configuration

### POS Settings
- **Terminal Information**: Name, location, and hardware configuration
- **Interface Options**: Theme, layout, and display preferences  
- **Behavior Settings**: Auto-print receipts, session timeout, sounds
- **Hardware Integration**: Printer, cash drawer, barcode scanner setup

### User Management
- **Roles Available**: Administrator, Shop Manager, POS Manager, Cashier
- **Granular Permissions**: Control access to specific POS functions
- **Multi-terminal Support**: Assign users to specific terminals

### Payment Configuration
- **Split Payment Setup**: Configure dual payment method processing
- **Gateway Integration**: Connect with existing WooCommerce payment gateways
- **Receipt Customization**: Customize receipt format and content

## 🔌 Integrations

### WooCommerce Extensions
- **Compatible with 500+ plugins**: Automatic compatibility detection
- **Popular Integrations**: Stripe, PayPal, WooCommerce Subscriptions, Points and Rewards
- **Stock Management**: WooCommerce Stock Manager, TradeGecko, etc.

### Hardware Support
- **Receipt Printers**: ESC/POS compatible printers
- **Cash Drawers**: Standard cash drawer integration
- **Barcode Scanners**: USB and Bluetooth barcode scanner support
- **Payment Terminals**: Integration with popular payment terminals

## 📈 Performance & Scalability

### Tested Configurations
- **Small Stores**: Up to 1,000 products, 1-2 terminals
- **Medium Stores**: Up to 5,000 products, 3-5 terminals
- **Large Stores**: 10,000+ products, 10+ terminals

### Performance Optimizations
- **Multi-level Caching**: Object cache, transients, and query optimization
- **Lazy Loading**: Virtual scrolling for large product catalogs
- **Database Optimization**: Optimized queries using WooCommerce lookup tables
- **Asset Optimization**: Minified CSS/JS with smart loading

## 🔒 Security

### WordPress Security Standards
- **Input Sanitization**: All inputs properly sanitized using WordPress functions
- **Output Escaping**: All outputs properly escaped for security
- **Nonce Verification**: CSRF protection on all forms and AJAX calls
- **Capability Checks**: Proper permission verification throughout

### PCI Compliance Considerations
- **No Card Storage**: Never stores sensitive payment card data
- **Audit Logging**: Complete transaction audit trail
- **Secure Transmission**: All API calls use HTTPS
- **Session Security**: Secure session management with automatic timeout

## 🧪 Testing & Quality

### Quality Metrics
- **Code Coverage**: 90%+ PHP, 85%+ JavaScript
- **WordPress Standards**: 100% WPCS compliance
- **Security Testing**: Automated vulnerability scanning
- **Performance Testing**: Continuous performance monitoring

### Browser Support
- **Modern Browsers**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Mobile Support**: iOS Safari 14+, Chrome Mobile 90+
- **Touch Support**: Optimized for touch interfaces

## 📞 Support & Documentation

### Getting Help
- **Documentation**: [Full documentation](https://github.com/AnglDavd/wupos/wiki) (coming soon)
- **Issues**: [Report bugs](https://github.com/AnglDavd/wupos/issues) on GitHub
- **Support**: Community support via GitHub Discussions

### Contributing
We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## 📄 License

WUPOS is licensed under the [GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

## 🙏 Acknowledgments

Built with ❤️ for the WordPress and WooCommerce community.

---

**⭐ If WUPOS helps your business, please consider giving it a star on GitHub!**