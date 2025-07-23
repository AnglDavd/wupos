# Known Issues & Error Memory System

**Framework Version:** v3.1.1 Enhanced  
**Last Updated:** 2025-07-20  
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

### **ISSUE-021: Persistent PHPUnit Test Execution Failure (WordPress Test Suite Incompatibility)**
**Status:** 🔄 INVESTIGATING  
**Discovered:** 2025-07-21 during backend unit testing  
**Problem:** Despite installing PHPUnit 9.x locally, and attempting various fixes (polyfills, direct includes), PHPUnit still reports that test classes cannot be found, and compatibility warnings from `phpunit6/compat.php` persist. This indicates a fundamental incompatibility between the WordPress test suite's older PHPUnit compatibility layer and the installed PHPUnit 9.x, or an issue with how the WordPress test suite's bootstrap loads its components.
**Symptoms:**
- PHPUnit output: "Class ... cannot be found in ..."
- PHPUnit output: "Warning: Class \"PHPUnit\\Framework\\Error\\Deprecated\" not found in .../phpunit6/compat.php"
- No tests executed.

**Current Workaround:** None.  
**Investigation Status:** Reverting `bootstrap.php` to original state and re-running `install-wp-tests.sh` to ensure a clean WordPress test environment setup for PHPUnit 9.x.  
**Next Steps:** Revert `wupos/tests/bootstrap.php` to original, re-run `install-wp-tests.sh`, then re-run PHPUnit tests.

---

## 📊 Issue Statistics

**Total Issues Discovered:** 21  
**Resolved Issues:** 1  
**Active Issues:** 20  
**Framework Reliability:** 4% → Improving with each discovery

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

**Remember:** Every error is an opportunity to make the framework more robust. This memory system ensures we learn from mistakes and continuously improve the user experience.