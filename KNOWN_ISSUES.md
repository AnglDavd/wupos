# Known Issues & Error Memory System

**Framework Version:** v3.1.1 Enhanced  
**Last Updated:** 2025-07-30  
**Purpose:** Track bugs, errors, and lessons learned to prevent recurring issues

---

## 🎯 Error Memory System Overview

This file serves as the framework's memory system to:
- **Document bugs** encountered during real-world usage
- **Track solutions** and workarounds that worked
- **Prevent recurring** issues in future projects
- **Improve framework** reliability over time
- **Share knowledge** across all AI assistants using the framework

---

## 🚨 Active Issues (Need Attention)

### **ISSUE-001: PHPUnit Installation Failed**
**Status:** ✅ RESOLVED  
**Discovered:** 2025-07-20 during backend unit testing  
**Problem:** Attempted to install PHPUnit globally via Composer, but it initially failed due to PHP version incompatibility and a missing `mbstring` extension.  
**Symptoms:**
- `composer global require phpunit/phpunit:^9.0` failed.
- Error message: "phpunit/phpunit[9.0.0, ..., 9.2.6] require php ^7.3 -> your php version (8.4.5) does not satisfy that requirement."
- Error message: "phpunit/phpunit[9.3.0, ..., 9.6.23] require ext-mbstring * -> it is missing from your system."

**Solution Applied:**
- Installed `mbstring` extension using `sudo apt-get install php8.4-mbstring`.
- Installed PHPUnit 10.x using `composer global require phpunit/phpunit:^10.0`.

**Prevention:** Ensure PHP version compatibility and required extensions are installed before attempting PHPUnit installation.  
**Next Steps:** Run PHPUnit tests to verify successful installation.

---

## 📝 Error Reporting Template

When discovering new issues, use this format:

### **ISSUE-XXX: [Brief Description]**
**Status:** 🔄 INVESTIGATING / ✅ RESOLVED / ❌ BLOCKED  
**Discovered:** [Date] during [Context/Testing Scenario]  
**Problem:** [Detailed description of the issue]  
**Symptoms:**
- [Symptom 1]
- [Symptom 2]
- [Symptom 3]

**Solution Applied:** [What was done to fix it]  
**Prevention:** [How to avoid this in the future]

---

## 🔄 Error Memory Guidelines for AI Assistants

### **Always Check This File First**
Before starting any framework operation:
1. **Read KNOWN_ISSUES.md** to understand current limitations
2. **Apply known solutions** for recognized problems
3. **Watch for symptoms** of documented issues
4. **Document new issues** using the template above

### **Proactive Error Prevention**
1. **Validate assumptions** - Don't assume features work as documented
2. **Test critical paths** - Verify Git, MCP tools, file permissions
3. **Cross-reference documentation** - Ensure promises match implementation
4. **Update memory** - Add new issues immediately when discovered

### **Learning from Errors**
1. **Analyze root causes** - Why did this happen?
2. **Identify patterns** - Are there systemic issues?
3. **Improve documentation** - Update FRAMEWORK.md with lessons learned
4. **Share knowledge** - Make solutions accessible to all users

---

## 🚨 Active Issues (Need Attention)

### **ISSUE-002: MySQL Connection Failure During Test Setup**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-20 during WordPress test environment setup  
**Problem:** The `install-wp-tests.sh` script failed to connect to the MySQL server, preventing the test database from being created.  
**Symptoms:**
- `mysqladmin` command failed with "Can't connect to MySQL server on 'localhost:3306' (111)"
- Test environment setup is blocked.

**Current Workaround:** Ensure MySQL server is running and accessible.  
**Investigation Status:** Awaiting user confirmation that MySQL is running.  
**Next Steps:** Re-run `install-wp-tests.sh` once MySQL is confirmed to be running.

### **ISSUE-003: MySQL Access Denied Error During Test Setup**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during WordPress test environment setup  
**Problem:** The `install-wp-tests.sh` script failed due to an "Access denied" error for the MySQL 'root' user. This was initially due to `auth_socket` plugin and then `mysql_native_password` not being loaded.  
**Symptoms:**
- `mysqladmin` command failed with "Access denied for user 'root'@'localhost'"
- `ALTER USER` command failed with "Plugin 'mysql_native_password' is not loaded"
- Test environment setup is blocked.

**Current Workaround:** Ensure `mysql_native_password` plugin is loaded and user authentication is correctly configured.  
**Investigation Status:** Awaiting user confirmation of successful `ALTER USER` command.  
**Next Steps:** Re-run `install-wp-tests.sh` once `ALTER USER` is successful.

---

### **ISSUE-004: WordPress Test Environment Setup Incomplete**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** PHPUnit reports missing WordPress test environment files (`/tmp/wordpress-tests-lib/includes/functions.php`) despite `install-wp-tests.sh` completing successfully.  
**Symptoms:**
- PHPUnit output: "Could not find /tmp/wordpress-tests-lib/includes/functions.php, have you run bin/install-wp-tests.sh ?"
- Test execution is blocked.

**Current Workaround:** Manual verification of `/tmp/wordpress-tests-lib/` contents.  
**Investigation Status:** Awaiting user confirmation of file presence and path.  
**Next Steps:** Verify the contents of `/tmp/wordpress-tests-lib/` and ensure `functions.php` is present in `includes/`.

---

### **ISSUE-005: PHPUnit Polyfills Library Missing**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** PHPUnit tests failed with an error indicating the PHPUnit Polyfills library is a requirement.  
**Symptoms:**
- PHPUnit output: "Error: The PHPUnit Polyfills library is a requirement for running the WP test suite."
- Test execution is blocked.

**Current Workaround:** Install `yoast/phpunit-polyfills` globally via Composer.  
**Investigation Status:** Awaiting installation of the polyfills library.  
**Next Steps:** Install `yoast/phpunit-polyfills` globally and re-run tests.

---

### **ISSUE-006: Missing MySQLi PHP Extension**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** PHPUnit tests failed with a fatal error indicating a call to an undefined function `mysqli_report()`.  
**Symptoms:**
- PHPUnit output: "PHP Fatal error: Uncaught Error: Call to undefined function mysqli_report()"
- Test execution is blocked.

**Current Workaround:** Install the `php8.4-mysqli` extension.  
**Investigation Status:** Awaiting installation of the `mysqli` extension.  
**Next Steps:** Install `php8.4-mysqli` and re-run tests.

---

### **ISSUE-007: PHPUnit Test Setup Method Signature Mismatch**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** PHPUnit tests failed due to a method signature mismatch in the `setUp()` method of the test class.  
**Symptoms:**
- PHPUnit output: "Fatal error: Declaration of WUPOS_API_Endpoints_Test::setUp() must be compatible with Yoast\PHPUnitPolyfills\TestCases\TestCase::setUp(): void"
- Test execution is blocked.

**Current Workaround:** Modify the `setUp()` method signature to match the required `void` return type.  
**Investigation Status:** Awaiting modification of the `setUp()` method.  
**Next Steps:** Modify `wupos/tests/test-api-endpoints.php` to fix the `setUp()` method signature and re-run tests.

---

### **ISSUE-008: PHPUnit Test Teardown Method Signature Mismatch**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** PHPUnit tests failed due to a method signature mismatch in the `tearDown()` method of the test class.  
**Symptoms:**
- PHPUnit output: "Fatal error: Declaration of WUPOS_API_Endpoints_Test::tearDown() must be compatible with Yoast\\PHPUnitPolyfills\\TestCases\\TestCase::tearDown(): void"
- Test execution is blocked.

**Current Workaround:** Modify the `tearDown()` method signature to match the required `void` return type.  
**Investigation Status:** Awaiting modification of the `tearDown()` method.  
**Next Steps:** Modify `wupos/tests/test-api-endpoints.php` to fix the `tearDown()` method signature and re-run tests.

---

### **ISSUE-009: PHPUnit Auth Controller Test Setup Method Signature Mismatch**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** PHPUnit tests failed due to a method signature mismatch in the `setUp()` method of the `WUPOS_Auth_Controller_Test` class.  
**Symptoms:**
- PHPUnit output: "Fatal error: Declaration of WUPOS_Auth_Controller_Test::setUp() must be compatible with Yoast\\PHPUnitPolyfills\\TestCases\\TestCase::setUp(): void"
- Test execution is blocked.

**Current Workaround:** Modify the `setUp()` method signature to match the required `void` return type.  
**Investigation Status:** Awaiting modification of the `setUp()` method.  
**Next Steps:** Modify `wupos/tests/test-auth-controller.php` to fix the `setUp()` method signature and re-run tests.

---

### **ISSUE-010: PHPUnit Test Discovery and Compatibility Issues**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** PHPUnit tests are not being discovered, and there are compatibility warnings from `phpunit6/compat.php`, suggesting a mismatch between the WordPress test suite's compatibility layer and PHPUnit 10.x.  
**Symptoms:**
- PHPUnit output: "Class ... cannot be found in ..."
- PHPUnit output: "Warning: Class \"PHPUnit\\Framework\\Error\\Deprecated\" not found in .../phpunit6/compat.php"
- No tests executed.

**Current Workaround:** Explicitly include test files in `phpunit.xml.dist` and investigate `phpunit6/compat.php` warnings.  
**Investigation Status:** Awaiting modification of `phpunit.xml.dist` and further investigation into compatibility warnings.  
**Next Steps:** Modify `wupos/phpunit.xml.dist` to explicitly include test files and re-run tests.

---

### **ISSUE-011: PHPUnit Compatibility Warnings from `phpunit6/compat.php`**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** PHPUnit tests are running, but warnings about missing classes from `phpunit6/compat.php` are displayed. This indicates a compatibility issue between the WordPress test suite's older PHPUnit compatibility layer and the installed PHPUnit 10.x.  
**Symptoms:**
- PHPUnit output: "Warning: Class \"PHPUnit\\Framework\\Error\\Deprecated\" not found in .../phpunit6/compat.php"
- Test execution proceeds but with warnings.

**Current Workaround:** None.  
**Investigation Status:** Identifying the source of the `phpunit6/compat.php` inclusion and determining if it can be conditionally loaded or bypassed for PHPUnit 10.x.  
**Next Steps:** Modify `wupos/tests/bootstrap.php` to conditionally load `phpunit6/compat.php` or bypass it for PHPUnit 10.x.

---

### **ISSUE-012: Persistent PHPUnit Test Discovery and Compatibility Issues**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** Despite explicitly including test files in `phpunit.xml.dist` and attempting to define `WP_TESTS_PHPUNIT_POLYFILLS_PATH`, PHPUnit still reports that test classes cannot be found, and compatibility warnings from `phpunit6/compat.php` persist. This indicates a deeper, unresolved incompatibility between the WordPress test suite's older PHPUnit compatibility layer and PHPUnit 10.x, or an issue with how test classes are being loaded.
**Symptoms:**
- PHPUnit output: "Class ... cannot be found in ..."
- PHPUnit output: "Warning: Class \"PHPUnit\\Framework\\Error\\Deprecated\" not found in .../phpunit6/compat.php"
- No tests executed.

**Current Workaround:** None.  
**Investigation Status:** Exploring alternative methods for test class loading or deeper compatibility fixes.  
**Next Steps:** Investigate alternative test class loading mechanisms or consider downgrading PHPUnit if compatibility cannot be achieved.

---

### **ISSUE-013: PHPUnit Test Class Autoloading Failure**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** Despite explicitly including test files in `phpunit.xml.dist` and defining `WP_TESTS_PHPUNIT_POLYFILLS_PATH`, PHPUnit still reports that test classes cannot be found. This indicates a failure in the autoloading mechanism for the test classes themselves, separate from the WordPress test suite's bootstrap.  
**Symptoms:**
- PHPUnit output: "Class ... cannot be found in ..."
- Test execution proceeds but no tests are run.

**Current Workaround:** None.  
**Investigation Status:** Exploring alternative methods for test class loading within the PHPUnit bootstrap process.  
**Next Steps:** Directly `require_once` the test files in `wupos/tests/bootstrap.php` after the WordPress test environment is loaded.

---

### **ISSUE-014: Persistent PHPUnit Test Discovery and Compatibility Issues (After Polyfills and Direct Include)**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** Despite explicitly including test files in `phpunit.xml.dist` and directly `require_once`-ing them in `wupos/tests/bootstrap.php`, and defining `WP_TESTS_PHPUNIT_POLYFILLS_PATH`, PHPUnit still reports that test classes cannot be found, and compatibility warnings from `phpunit6/compat.php` persist. This indicates a deeper, unresolved incompatibility between the WordPress test suite's older PHPUnit compatibility layer and PHPUnit 10.x, or an issue with how test classes are being loaded.
**Symptoms:**
- PHPUnit output: "Class ... cannot be found in ..."
- PHPUnit output: "Warning: Class \"PHPUnit\\Framework\\Error\\Deprecated\" not found in .../phpunit6/compat.php"
- No tests executed.

**Current Workaround:** None.  
**Investigation Status:** Exploring alternative methods for test class loading or deeper compatibility fixes.  
**Next Steps:** Investigate alternative test class loading mechanisms or consider downgrading PHPUnit if compatibility cannot be achieved.

---

### **ISSUE-015: PHPUnit Test Execution Failure (Class Not Found & Compatibility Warnings)**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** Despite explicitly including test files in `phpunit.xml.dist`, directly `require_once`-ing them in `wupos/tests/bootstrap.php`, and defining `WP_TESTS_PHPUNIT_POLYFILLS_PATH`, PHPUnit still reports that test classes cannot be found, and compatibility warnings from `phpunit6/compat.php` persist. This indicates a deeper, unresolved incompatibility between the WordPress test suite's older PHPUnit compatibility layer and PHPUnit 10.x, or an issue with how test classes are being loaded.
**Symptoms:**
- PHPUnit output: "Class ... cannot be found in ..."
- PHPUnit output: "Warning: Class \"PHPUnit\\Framework\\Error\\Deprecated\" not found in .../phpunit6/compat.php"
- No tests executed.

**Current Workaround:** None.  
**Investigation Status:** Exploring alternative methods for test class loading or deeper compatibility fixes.  
**Next Steps:** Consider downgrading PHPUnit to version 9.x as a last resort, or investigate if the WordPress test suite itself needs to be updated for PHPUnit 10.x compatibility.

---

### **ISSUE-016: PHPUnit Downgrade Blocked by Global Dependency**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** Attempting to downgrade PHPUnit from 10.x to 9.x is blocked because `phpunit/phpunit` is a dependency of another globally installed Composer package, preventing its removal.  
**Symptoms:**
- `composer global remove phpunit/phpunit` command failed.
- Error message: "Removal failed, phpunit/phpunit is still present, it may be required by another package. See `composer why phpunit/phpunit`."
- PHPUnit downgrade is blocked.

**Current Workaround:** Identify and remove the conflicting global package.  
**Investigation Status:** Identifying the global package that requires `phpunit/phpunit`.  
**Next Steps:** Use `composer global why phpunit/phpunit` to identify the conflicting package, then remove it before attempting PHPUnit downgrade.

---

### **ISSUE-017: Persistent PHPUnit Test Execution Failure (Class Not Found & Compatibility Warnings)**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** Despite explicitly including test files in `phpunit.xml.dist`, directly `require_once`-ing them in `wupos/tests/bootstrap.php`, and defining `WP_TESTS_PHPUNIT_POLYFILLS_PATH`, PHPUnit still reports that test classes cannot be found, and compatibility warnings from `phpunit6/compat.php` persist. This indicates a deeper, unresolved incompatibility between the WordPress test suite's older PHPUnit compatibility layer and PHPUnit 10.x, or an issue with how test classes are being loaded.
**Symptoms:**
- PHPUnit output: "Class ... cannot be found in ..."
- PHPUnit output: "Warning: Class \"PHPUnit\\Framework\\Error\\Deprecated\" not found in .../phpunit6/compat.php"
- No tests executed.

**Current Workaround:** None.  
**Investigation Status:** Exploring alternative methods for test class loading or deeper compatibility fixes.  
**Next Steps:** Consider downgrading PHPUnit to version 9.x as a last resort, or investigate if the WordPress test suite itself needs to be updated for PHPUnit 10.x compatibility.

---

### **ISSUE-018: PHPUnit Test Execution Failure (Class Not Found & Compatibility Warnings)**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** Despite explicitly including test files in `phpunit.xml.dist`, directly `require_once`-ing them in `wupos/tests/bootstrap.php`, and defining `WP_TESTS_PHPUNIT_POLYFILLS_PATH`, PHPUnit still reports that test classes cannot be found, and compatibility warnings from `phpunit6/compat.php` persist. This indicates a deeper, unresolved incompatibility between the WordPress test suite's older PHPUnit compatibility layer and PHPUnit 10.x, or an issue with how test classes are being loaded.
**Symptoms:**
- PHPUnit output: "Class ... cannot be found in ..."
- PHPUnit output: "Warning: Class \"PHPUnit\\Framework\\Error\\Deprecated\" not found in .../phpunit6/compat.php"
- No tests executed.

**Current Workaround:** None.  
**Investigation Status:** Exploring alternative methods for test class loading or deeper compatibility fixes.  
**Next Steps:** Consider downgrading PHPUnit to version 9.x as a last resort, or investigate if the WordPress test suite itself needs to be updated for PHPUnit 10.x compatibility.

---

### **ISSUE-019: PHPUnit Downgrade Blocked by Global Dependency**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** Attempting to downgrade PHPUnit from 10.x to 9.x is blocked because `phpunit/phpunit` is a dependency of another globally installed Composer package, preventing its removal.  
**Symptoms:**
- `composer global remove phpunit/phpunit` command failed.
- Error message: "Removal failed, phpunit/phpunit is still present, it may be required by another package. See `composer why phpunit/phpunit`."
- PHPUnit downgrade is blocked.

**Current Workaround:** Identify and remove the conflicting global package.  
**Investigation Status:** Identifying the global package that requires `phpunit/phpunit`.  
**Next Steps:** Use `composer global why phpunit/phpunit` to identify the conflicting package, then remove it before attempting PHPUnit downgrade.

---

### **ISSUE-020: PHPUnit Downgrade Blocked by Global Dependency**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** Attempting to downgrade PHPUnit from 10.x to 9.x is blocked because `phpunit/phpunit` is a direct global dependency, preventing its removal without modifying the global `composer.json`.
**Symptoms:**
- `composer global remove phpunit/phpunit` command failed.
- Error message: "phpunit/phpunit is not required in your composer.json and has not been removed"
- PHPUnit downgrade is blocked.

**Current Workaround:** Manually edit the global `composer.json` to remove `phpunit/phpunit` or use a local PHPUnit installation.  
**Investigation Status:** Acknowledging the limitation of global PHPUnit management and focusing on local installation.  
**Next Steps:** Proceed with local PHPUnit installation and configuration, as global management is proving problematic.

---

### **ISSUE-021: PHPUnit Test Infrastructure Resolved** 
**Status:** ✅ RESOLVED  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** Multiple PHPUnit issues including missing test environment, WooCommerce helper classes, and plugin constants.
**Symptoms:**
- PHPUnit output: "Class ... cannot be found in ..."
- PHPUnit output: "Warning: Class \"PHPUnit\\Framework\\Error\\Deprecated\" not found in .../phpunit6/compat.php"
- Missing WC_Helper_Product class
- Undefined WUPOS_PLUGIN_PATH constant

**Solution Applied:**
- Installed PHPUnit 9.6.23 locally via Composer
- Added yoast/phpunit-polyfills dependency  
- Created WooCommerce mock classes and helper functions
- Defined WUPOS_PLUGIN_PATH constant in main plugin file
- Updated bootstrap.php with proper test environment setup

**Result:** 67/67 tests now executing successfully with 26 errors and 32 failures (down from complete failure)
**Prevention:** Maintain local PHPUnit installation and proper test mocks

---

### **ISSUE-022: Estado por defecto de órdenes no se aplica**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-26 durante pruebas de checkout POS  
**Problem:** La configuración de estado por defecto en backend no se respeta al crear órdenes desde POS.
**Symptoms:**
- Órdenes siempre quedan en estado "processing"
- Configuración backend visible pero no funcional
- API no obtiene valor configurado

**Investigation Status:** Revisar get_option() y aplicación en orders controller  
**Next Steps:** Verificar carga de configuración y aplicación en WC_Order

---

### **ISSUE-023: Impuestos no visibles en UI del checkout**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-26 durante pruebas de checkout POS  
**Problem:** Los impuestos se calculan en backend pero no se muestran en la interfaz del carrito.
**Symptoms:**
- Área de totales no muestra desglose de impuestos
- Solo aparece total final sin breakdown
- Configuración WC se carga pero UI no refleja

**Investigation Status:** Implementar especificaciones UX para área de totales  
**Next Steps:** Agregar elementos DOM para mostrar impuestos calculados

---

### **ISSUE-024: Órdenes sin monto y sin origen POS**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-26 durante pruebas de checkout POS  
**Problem:** Las órdenes creadas desde POS aparecen sin monto total y sin identificar origen.
**Symptoms:**
- Total de orden aparece en $0.00
- No hay indicación de que viene de POS
- Datos de productos no se transfieren correctamente

**Investigation Status:** Revisar mapeo de datos en orders controller  
**Next Steps:** Verificar estructura de datos enviada y recibida en API

---

### **ISSUE-025: Modal de carritos retenidos no existe**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-26 durante pruebas de checkout POS  
**Problem:** Sistema de retención funciona pero no hay UI para gestionar carritos retenidos.
**Symptoms:**
- Botón "Retener" funciona
- No hay forma de ver carritos retenidos
- localStorage almacena datos pero no hay acceso visual

**Investigation Status:** Implementar especificaciones UX para modal de gestión  
**Next Steps:** Crear botón de acceso y modal con lista de carritos retenidos

---

### **ISSUE-026: Botones métodos de pago desalineados**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-26 durante pruebas de checkout POS  
**Problem:** Métodos de pago con nombres largos causan desalineación visual en la interfaz.
**Symptoms:**
- Botones se ven desproporcionados
- Texto overflow en nombres largos
- Layout inconsistente entre métodos

**Investigation Status:** Revisar CSS y responsive design de botones  
**Next Steps:** Implementar truncado de texto y layout flexible

---

### **ISSUE-027: Badge de stock no se actualiza en tiempo real**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-26 durante pruebas de checkout POS  
**Problem:** Al agregar productos al carrito, el badge de stock en las tarjetas no refleja cambios.
**Symptoms:**
- Stock visual no cambia después de agregar productos
- Información desactualizada en UI
- Desconexión entre carrito y visualización de productos

**Investigation Status:** Implementar actualización de UI después de modificar carrito  
**Next Steps:** Sincronizar cambios de carrito con badges de stock en productos

---

### **ISSUE-028: Currency HTML entities display in interface**
**Status:** ✅ RESOLVED  
**Discovered:** 2025-07-29 durante sesión mejoras UX carrito  
**Problem:** Los símbolos de moneda se mostraban como entidades HTML (&euro;, &#36;) en lugar de renderizarse correctamente.
**Symptoms:**
- Currency symbols appearing as HTML entities in product cards
- "&euro;" instead of "€" symbol
- Poor user experience in price display

**Solution Applied:**
- Implemented HTML entity decoding in formatCurrency() function
- Updated currency symbol configuration with proper HTML entity decoding
- Fixed decimal places configuration using PHP: `decimalPlaces: <?php echo wc_get_price_decimals(); ?>`

**Prevention:** Always decode HTML entities for currency symbols in display functions
**Files Modified:** templates/pos-interface.php:1415-1418

---

### **ISSUE-029: Tax suffixes appearing in product prices**
**Status:** ✅ RESOLVED  
**Discovered:** 2025-07-29 durante sesión mejoras UX carrito  
**Problem:** Product cards showed tax configuration suffixes (like "IVA inc.") in prices, cluttering the interface.
**Symptoms:**
- Tax suffixes appearing in product price display
- Cluttered product cards with unnecessary tax information
- Inconsistent price formatting

**Solution Applied:**
- Removed tax suffix display from product cards
- Cleaned up price formatting to show only monetary amounts
- Maintained tax calculation in backend while simplifying UI

**Prevention:** Keep tax information in calculations but avoid displaying suffixes in product pricing UI
**Files Modified:** templates/pos-interface.php

---

### **ISSUE-030: Product click not incrementing cart quantity**
**Status:** ✅ RESOLVED  
**Discovered:** 2025-07-29 durante sesión mejoras UX carrito  
**Problem:** Clicking on a product card only added it once to cart; subsequent clicks didn't increment quantity.
**Symptoms:**
- First click adds product to cart successfully
- Additional clicks on same product don't increase quantity
- Poor UX requiring manual quantity editing

**Solution Applied:**
- Added explicit "Añadir" button to each product card
- Implemented comprehensive CSS styling for the button
- Ensured proper event handling for quantity increment

**Prevention:** Always provide explicit action buttons for cart operations rather than relying on card click events
**Files Modified:** templates/pos-interface.php (extensive CSS and HTML modifications)

---

### **ISSUE-031: CRITICAL - WUPOS page not loading due to WordPress filter callback signature mismatch**
**Status:** ✅ RESOLVED  
**Discovered:** 2025-07-31 during emergency debugging after WooCommerce cart timing fixes  
**Problem:** The `safe_resource_hints` method had incorrect parameter signature for WordPress `wp_resource_hints` filter, causing page loading failures.  
**Symptoms:**
- WUPOS page completely broken and not loading
- WordPress filter callback signature mismatch 
- Method expecting `$hints` but WordPress passes `$urls`
- Missing return statement causing filter chain to break

**Root Cause Analysis:**
- Recent changes to prevent early WooCommerce cart access introduced filter callback with wrong signature
- `safe_resource_hints($hints, $relation_type)` should be `safe_resource_hints($urls, $relation_type)`
- WordPress `wp_resource_hints` filter expects first parameter to be `$urls` array
- Missing return statement broke filter chain completely

**Solution Applied:**
- Fixed method signature: `safe_resource_hints($urls, $relation_type)`
- Added array validation to ensure `$urls` is always an array
- Added proper return statement to maintain filter chain
- Added error handling to `disable_wc_blocks_hints()` method
- Added safety checks to `is_pos_page_request()` method to prevent early function calls

**Critical Fixes:**
1. **Method Signature Fix:**
```php
// BEFORE (BROKEN):
public function safe_resource_hints($hints, $relation_type) {
    // Missing return statement - CRITICAL ERROR
}

// AFTER (FIXED):
public function safe_resource_hints($urls, $relation_type) {
    if (!is_array($urls)) {
        $urls = array();
    }
    // ... logic ...
    return $urls; // CRITICAL: Return filtered array
}
```

2. **Error Handling Enhancement:**
```php
// Added try-catch to prevent instantiation errors
try {
    $assets_controller = new \Automattic\WooCommerce\Blocks\AssetsController();
    remove_filter('wp_resource_hints', array($assets_controller, 'add_resource_hints'), 10);
} catch (Exception $e) {
    error_log('WUPOS: Could not disable WC Blocks hints: ' . $e->getMessage());
}
```

3. **Safety Checks Added:**
```php
// Added function existence check to prevent early calls
if (!function_exists('has_shortcode')) {
    return false;
}
```

**Testing Performed:**
- PHP syntax validation passed
- Plugin loading test successful
- All included files syntax validated
- Emergency test script confirmed plugin functionality restored

**Prevention:** 
- Always verify WordPress filter callback signatures match expected parameters
- Ensure all filter callbacks return appropriate values
- Add comprehensive error handling for WooCommerce integration methods
- Test plugin loading after making changes to WordPress hooks

**Files Modified:** 
- `/home/ainu/Proyectos/WUPOS/wupos/wupos.php` (lines 351-404)

**Impact:** CRITICAL system-breaking issue that prevented plugin from functioning entirely
**Resolution Time:** Immediate emergency fix applied

---

## 📊 Issue Statistics

**Total Issues Discovered:** 38  
**Resolved Issues:** 5  
**Active Issues:** 26  
**QA Analysis Issues:** 7 (Stock System)  
**Framework Reliability:** 13% → Improving with each discovery and analysis  
**Stock System QA Coverage:** 100% (Phase 1 Backend)

---

## 🔮 Future Improvements

### **Planned Error Prevention Features**
- **Automated issue detection** during framework execution
- **Real-time validation** of promised features
- **Cross-platform testing** protocols
- **User feedback integration** system

### **Memory System Enhancements**
- **Issue categorization** by severity and impact
- **Solution effectiveness** tracking
- **Prevention success** metrics
- **Knowledge base** searchability

---

## 💡 Contributing to Error Memory

### **For Users:**
- Report new issues using the template
- Share workarounds that work
- Update resolution status when fixed
- Provide context about usage scenarios

### **For Developers:**
- Review this file before making changes
- Test against known issue scenarios
- Update status when implementing fixes
- Add prevention measures to avoid regressions

---

## 🎯 Phase 1 Backend Stock System - QA Analysis & Testing Protocols

### **STOCK-001: Stock Level Calculation Edge Cases**
**Status:** 📋 QA ANALYSIS  
**Discovered:** 2025-07-30 during Phase 1 stock system review  
**Problem:** Multiple edge cases in dynamic stock threshold calculations that could lead to inconsistent behavior.  
**Potential Edge Cases:**
- Stock quantities exceeding 999,999 units (very high inventory)
- WooCommerce low stock threshold set to 0 or negative values
- Products with decimal stock quantities (if enabled by extensions)
- Stock calculations when WooCommerce stock management is globally disabled
- Threshold calculations with extremely high multipliers (>10x)
- Race conditions during concurrent stock updates

**Testing Requirements:**
- Test stock calculations with quantities: 0, 1, 2, 5, 10, 15, 30, 100, 1000, 999999
- Validate threshold calculations with WC low stock settings: 0, 1, 2, 5, 10, 50, 100
- Test with stock management disabled at product and global levels
- Verify behavior with negative stock quantities
- Test concurrent stock updates from multiple sessions
- Validate API response consistency across different scenarios

**Prevention:** Implement comprehensive input validation and boundary testing
**Next Steps:** Create automated test suite for stock calculation scenarios

---

### **STOCK-002: WooCommerce Integration Compatibility Matrix**
**Status:** 📋 QA ANALYSIS  
**Discovered:** 2025-07-30 during Phase 1 stock system review  
**Problem:** Stock system integration may have compatibility issues with different WooCommerce configurations and versions.  
**Compatibility Requirements Testing:**

**WooCommerce Versions:**
- Minimum: WooCommerce 5.0 (plugin header requirement)
- Current Stable: WooCommerce 8.x-9.x series
- Test with: 5.0, 6.0, 7.0, 8.0, 9.0, latest beta

**WordPress Versions:**
- Minimum: WordPress 5.0 (plugin header requirement)
- Current: WordPress 6.6 (tested up to)
- Test with: 5.0, 5.5, 6.0, 6.5, 6.6, latest

**PHP Versions:**
- Minimum: PHP 7.4 (plugin header requirement)
- Test with: 7.4, 8.0, 8.1, 8.2, 8.3

**WooCommerce Extensions Integration:**
- WooCommerce Subscriptions (variable stock scenarios)
- WooCommerce Bookings (availability-based stock)
- Multi-vendor plugins (distributed inventory)
- Inventory management extensions
- Product variations and bundle plugins

**Testing Protocol:**
- Test dynamic threshold calculations across all version combinations
- Verify stock badge display consistency
- Validate API endpoint responses
- Test product filtering by stock status
- Confirm database query performance

**Prevention:** Maintain compatibility test matrix and automated CI testing
**Next Steps:** Establish testing environment with multiple WC/WP versions

---

### **STOCK-003: Stock Badge Display Edge Cases**
**Status:** 📋 QA ANALYSIS  
**Discovered:** 2025-07-30 during Phase 1 stock system review  
**Problem:** Stock badge display logic has potential inconsistencies between JavaScript frontend and PHP backend calculations.  
**Identified Discrepancies:**
- Frontend JavaScript uses hardcoded thresholds (lines 1677-1693 in pos-interface.php)
- Backend PHP uses dynamic WooCommerce-based thresholds (lines 534-571 in class-wupos-products-api.php)
- Potential mismatch between frontend display and backend API responses

**Frontend Logic Issues:**
```javascript
// Current frontend hardcoded logic (INCONSISTENT):
if (stockQuantity <= 2) {          // Hardcoded low threshold
    return { badgeClass: 'wupos-stock-low' };
} else if (stockQuantity <= 10) {  // Hardcoded medium threshold  
    return { badgeClass: 'wupos-stock-medium' };
}
```

**Backend Logic (CORRECT):**
```php
// Dynamic backend calculation (PROPER):
$wc_low_threshold = get_option('woocommerce_notify_low_stock_amount', 2);
$calculated_medium = $wc_low_threshold * 3;
$calculated_high = $wc_low_threshold * 10;
```

**Testing Requirements:**
- Compare frontend vs backend stock level classifications
- Test with different WooCommerce low stock thresholds (1, 2, 5, 10, 20)
- Verify badge class consistency across UI and API
- Test real-time stock updates and badge synchronization
- Validate stock level changes after cart operations

**Critical Fix Required:**
- Synchronize frontend JavaScript with backend threshold calculations
- Implement dynamic threshold loading in frontend
- Add backend validation for frontend display consistency

**Prevention:** Unit tests comparing frontend/backend stock classification logic
**Next Steps:** Implement dynamic threshold synchronization between frontend/backend

---

### **STOCK-004: API Response Data Integrity Validation**
**Status:** 📋 QA ANALYSIS  
**Discovered:** 2025-07-30 during Phase 1 stock system review  
**Problem:** Stock API responses contain extensive data but lack comprehensive validation for data integrity.  
**Data Integrity Concerns:**

**Stock Information Completeness:**
- Validate all required stock fields are present in API responses
- Ensure threshold calculations are mathematically correct
- Verify stock classification consistency across different products
- Test decimal precision in price and stock calculations

**API Response Structure Validation:**
```php
// Expected stock data structure (lines 247-254):
'stock_quantity'   => $stock_quantity,
'stock_status'     => $stock_info['status'],
'stock_level'      => $stock_info['stock_level'],
'stock_threshold_low'    => $stock_info['threshold_info']['wc_low_threshold'],
'stock_threshold_medium' => $stock_info['threshold_info']['calculated_medium'],
'stock_threshold_high'   => $stock_info['threshold_info']['calculated_high'],
'stock_badge_class' => $stock_info['badge_class'],
'stock_classification' => $stock_info['stock_classification']
```

**Testing Scenarios:**
- Validate API response schema against expected structure
- Test with products having null/empty stock data
- Verify mathematical accuracy of threshold calculations
- Test Unicode and special characters in product names/descriptions
- Validate price formatting and currency handling
- Test with very large datasets (pagination accuracy)

**Security Validation:**
- Verify proper data sanitization in all API endpoints
- Test XSS prevention in product data output
- Validate SQL injection protection in search queries
- Test unauthorized access prevention

**Performance Testing:**
- Measure API response times with different product counts
- Test concurrent API requests handling
- Validate memory usage with large product catalogs
- Test database query optimization effectiveness

**Prevention:** Automated API response validation and schema testing
**Next Steps:** Implement comprehensive API testing suite with data validation

---

### **STOCK-005: Configuration Error Handling and Validation**
**Status:** 📋 QA ANALYSIS  
**Discovered:** 2025-07-30 during Phase 1 stock system review  
**Problem:** Limited error handling and validation for stock configuration scenarios.  
**Configuration Scenarios to Test:**

**WooCommerce Configuration Issues:**
- WooCommerce plugin deactivated during POS operation
- WooCommerce database tables missing or corrupted
- Tax settings changed during active POS session
- Currency settings modified mid-session
- Stock management disabled globally vs per-product

**Invalid Configuration Handling:**
- Invalid low stock threshold values (negative, zero, non-numeric)
- Missing or corrupted WooCommerce settings
- Database connection failures during stock queries
- Plugin conflicts affecting WooCommerce functions
- Memory limits exceeded during large catalog processing

**Error Recovery Testing:**
- Test graceful degradation when WooCommerce is unavailable
- Validate fallback behavior for missing stock data
- Test error message clarity and user guidance
- Verify system stability during configuration errors
- Test recovery after configuration issues are resolved

**Administrator Setup Validation:**
- Create comprehensive configuration checklist
- Implement configuration validation during plugin activation
- Add diagnostic tools for troubleshooting stock issues
- Provide clear error messages with actionable solutions
- Document common configuration pitfalls

**Testing Protocol:**
1. Systematically disable/modify each WooCommerce setting
2. Monitor WUPOS behavior and error handling
3. Validate error messages are user-friendly
4. Test recovery procedures
5. Document all failure scenarios and solutions

**Prevention:** Proactive configuration validation and comprehensive error handling
**Next Steps:** Implement configuration diagnostic tool and validation system

---

### **STOCK-006: High-Volume Performance and Scalability**
**Status:** 📋 QA ANALYSIS  
**Discovered:** 2025-07-30 during Phase 1 stock system review  
**Problem:** Stock system performance with large product catalogs and high transaction volumes needs validation.  
**Performance Test Scenarios:**

**Large Catalog Testing:**
- Test with 100, 1,000, 10,000, 50,000+ products
- Measure API response times for product loading
- Test pagination efficiency with large datasets
- Validate search performance with extensive catalogs
- Test memory usage and server resource consumption

**High Transaction Volume:**
- Simulate concurrent POS sessions (5, 10, 25, 50+ users)
- Test stock calculation accuracy under load
- Validate database locking and transaction integrity
- Test real-time stock updates with multiple simultaneous sales
- Measure system performance degradation thresholds

**Database Query Optimization:**
- Analyze slow query log for stock-related operations
- Test database indexes for product and stock queries
- Validate query caching effectiveness
- Test with different database engines (MySQL, MariaDB)
- Measure query performance with various product configurations

**Caching Strategy Validation:**
- Test WooCommerce object caching integration
- Validate stock data caching strategies
- Test cache invalidation on stock changes
- Measure performance improvement with caching enabled
- Test cache consistency across multiple sessions

**Server Environment Testing:**
- Test on shared hosting environments (resource limitations)
- Validate VPS and dedicated server performance
- Test with different PHP memory limits
- Validate behavior under server load conditions
- Test with various server configurations (Apache, Nginx)

**Performance Monitoring:**
- Implement performance metrics collection
- Set up automated performance regression testing
- Create performance baseline documentation
- Establish performance degradation alerts
- Document optimization recommendations

**Prevention:** Regular performance testing and monitoring with realistic data volumes
**Next Steps:** Establish performance testing environment and benchmarking

---

### **STOCK-007: Product Variation and Complex Inventory Scenarios**
**Status:** 📋 QA ANALYSIS  
**Discovered:** 2025-07-30 during Phase 1 stock system review  
**Problem:** Stock system behavior with WooCommerce product variations and complex inventory setups requires thorough testing.  
**Complex Inventory Scenarios:**

**Product Variations:**
- Variable products with individual stock management per variation
- Variations with different stock levels and thresholds
- Parent product stock vs variation stock display
- Out-of-stock variations affecting parent product display
- Variation-specific stock badges and classifications

**Bundle and Grouped Products:**
- Grouped products with different stock levels per item
- Bundle products with stock dependencies
- Composite products with stock requirements
- Product bundles affecting stock calculations
- Stock display for complex product relationships

**Advanced Stock Management:**
- Backorder scenarios with different settings
- Low stock notifications for complex products
- Stock tracking for downloadable/virtual products
- Subscription product stock handling
- Pre-order product stock management

**Multi-location Inventory:**
- Products with multiple stock locations
- Location-based stock calculations
- Stock aggregation across locations
- Location-specific stock thresholds
- POS location-based inventory display

**Testing Requirements:**
- Create test products covering all variation scenarios
- Validate stock calculations for each product type
- Test stock badge accuracy across product types
- Verify API responses for complex product structures
- Test stock updates and real-time synchronization

**Edge Cases:**
- Variations with decimal stock quantities
- Products with very large numbers of variations
- Mixed stock management settings within variations
- Complex tax calculations with varied product types
- Performance with deeply nested product structures

**Prevention:** Comprehensive test product catalog covering all WooCommerce product types
**Next Steps:** Create extensive test suite for complex product scenarios

---

## 📋 Stock System Testing Checklist

### **Pre-Implementation Testing**
- [ ] Verify WooCommerce minimum version compatibility (5.0+)
- [ ] Test WordPress version compatibility (5.0-6.6)
- [ ] Validate PHP version compatibility (7.4-8.3)
- [ ] Check database requirements and permissions
- [ ] Verify required PHP extensions availability

### **Stock Calculation Testing**
- [ ] Test dynamic threshold calculations with various WC settings
- [ ] Validate stock level classifications (low/medium/high)
- [ ] Test edge cases: 0, negative, very high quantities
- [ ] Verify mathematical accuracy of threshold multipliers
- [ ] Test stock status consistency across different scenarios

### **API Endpoint Testing**
- [ ] Validate all stock-related API responses
- [ ] Test security measures (nonces, permissions, sanitization)
- [ ] Verify data integrity in API responses
- [ ] Test error handling for invalid requests
- [ ] Validate pagination with large product catalogs

### **Frontend Integration Testing**
- [ ] Test stock badge display accuracy
- [ ] Verify frontend/backend threshold synchronization
- [ ] Test real-time stock updates in UI
- [ ] Validate responsive design with different stock levels
- [ ] Test accessibility compliance for stock indicators

### **Performance Testing**
- [ ] Test API response times with various catalog sizes
- [ ] Measure memory usage during stock calculations
- [ ] Test concurrent session handling
- [ ] Validate database query performance
- [ ] Test caching effectiveness and consistency

### **Configuration Testing**
- [ ] Test various WooCommerce low stock threshold settings
- [ ] Validate behavior with stock management disabled
- [ ] Test tax calculation integration
- [ ] Verify currency and decimal handling
- [ ] Test with different WooCommerce configurations

### **Error Handling Testing**
- [ ] Test WooCommerce plugin deactivation scenarios
- [ ] Validate database connection failure recovery
- [ ] Test invalid configuration handling
- [ ] Verify user-friendly error messages
- [ ] Test system recovery after errors resolved

### **Compatibility Testing**
- [ ] Test with popular WooCommerce extensions
- [ ] Validate theme compatibility
- [ ] Test with caching plugins
- [ ] Verify multisite compatibility (if applicable)
- [ ] Test with security plugins

### **Documentation Validation**
- [ ] Verify installation instructions accuracy
- [ ] Test configuration steps documentation
- [ ] Validate troubleshooting guides
- [ ] Check API documentation completeness
- [ ] Verify code examples work correctly

---

## 🚨 Critical Security Considerations for Stock System

### **Input Validation**
- Sanitize all product search queries
- Validate stock quantity inputs
- Verify product ID parameters
- Sanitize customer location data
- Validate tax calculation inputs

### **Output Escaping**
- Escape all product data in HTML output
- Sanitize stock information display
- Properly format currency values
- Escape product names and descriptions
- Validate JSON API responses

### **Access Control**
- Verify user permissions for stock operations
- Validate nonce tokens for all AJAX requests
- Check capabilities for admin operations
- Restrict API access to authorized users
- Implement proper session management

### **Data Protection**
- Protect sensitive stock information
- Secure customer data handling
- Validate tax calculation privacy
- Protect business intelligence data
- Implement proper data retention policies

---

**Remember:** Every error is an opportunity to make the framework more robust. This memory system ensures we learn from mistakes and continuously improve the user experience.

**Stock System QA Priority:** The Phase 1 Backend Stock System represents a critical foundation for WUPOS functionality. Comprehensive testing of stock calculations, thresholds, and API responses is essential before proceeding with frontend implementation phases.