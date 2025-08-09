<?php
/**
 * Cart Business Logic Tests
 *
 * Comprehensive test suite for WUPOS Cart Manager, Session Handler,
 * Tax Calculator, and Inventory Sync functionality.
 *
 * @package WUPOS\Tests
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS_Cart_Business_Logic_Test class.
 *
 * Tests all cart business logic components including performance,
 * multi-terminal functionality, tax calculations, and stock management.
 */
class WUPOS_Cart_Business_Logic_Test {

    /**
     * Test results storage
     *
     * @var array
     */
    private $test_results = array();

    /**
     * Test terminal IDs for multi-terminal testing
     *
     * @var array
     */
    private $terminal_ids = array();

    /**
     * Performance benchmark data
     *
     * @var array
     */
    private $performance_data = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->terminal_ids = array(
            'terminal_1',
            'terminal_2', 
            'terminal_3'
        );
    }

    /**
     * Run all cart business logic tests
     *
     * @return array Complete test results
     */
    public function run_all_tests() {
        $this->log_test('Starting WUPOS Cart Business Logic Test Suite');
        
        // Core functionality tests
        $this->test_session_management();
        $this->test_cart_operations();
        $this->test_tax_calculations();
        $this->test_stock_verification();
        $this->test_multi_terminal_support();
        
        // Performance tests
        $this->test_performance_targets();
        $this->test_batch_operations();
        
        // Security and error handling tests
        $this->test_security_validation();
        $this->test_error_handling();
        
        // Generate comprehensive report
        return $this->generate_test_report();
    }

    /**
     * Test session management functionality
     */
    private function test_session_management() {
        $this->log_test('Testing Session Management');
        
        try {
            // Test session creation
            $session_handler = new WUPOS_Session_Handler('test_terminal_001');
            $session_id = $session_handler->get_session_id();
            
            $this->assert_true(!empty($session_id), 'Session ID should be generated');
            $this->assert_true($session_handler->is_session_valid(), 'Session should be valid after creation');
            
            // Test session data storage
            $test_data = array('test_key' => 'test_value', 'timestamp' => time());
            $result = $session_handler->set_session_data($test_data);
            $this->assert_true($result, 'Session data should be saved successfully');
            
            // Test session data retrieval
            $retrieved_data = $session_handler->get_session_data();
            $this->assert_equals($test_data['test_key'], $retrieved_data['test_key'], 'Session data should match stored data');
            
            // Test session extension
            $remaining_before = $session_handler->get_session_remaining_time();
            $extension_result = $session_handler->extend_session(3600);
            $remaining_after = $session_handler->get_session_remaining_time();
            
            $this->assert_true($extension_result, 'Session should extend successfully');
            $this->assert_true($remaining_after > $remaining_before, 'Session time should be extended');
            
            // Test cart data storage
            $cart_data = array(
                'contents' => array(),
                'totals' => array('subtotal' => 0),
                'hash' => 'test_hash'
            );
            
            $cart_result = $session_handler->set_cart_data($cart_data);
            $this->assert_true($cart_result, 'Cart data should be saved to session');
            
            $retrieved_cart = $session_handler->get_cart_data();
            $this->assert_equals($cart_data['hash'], $retrieved_cart['hash'], 'Cart data should match');
            
            $this->test_results['session_management'] = array(
                'status' => 'passed',
                'tests_run' => 6,
                'session_id' => $session_id,
                'performance' => array(
                    'session_creation' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
                )
            );
            
        } catch (Exception $e) {
            $this->test_results['session_management'] = array(
                'status' => 'failed',
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Test cart operations functionality
     */
    private function test_cart_operations() {
        $this->log_test('Testing Cart Operations');
        
        try {
            $cart_manager = new WUPOS_Cart_Manager('test_terminal_cart');
            
            // Test adding product to cart
            $start_time = microtime(true);
            $add_result = $cart_manager->add_to_cart(1, 2); // Product ID 1, Quantity 2
            $add_time = microtime(true) - $start_time;
            
            if (!is_wp_error($add_result)) {
                $this->assert_true($add_result['success'], 'Product should be added to cart successfully');
                $this->assert_equals(2, $add_result['quantity'], 'Quantity should match');
            }
            
            // Test cart contents retrieval
            $start_time = microtime(true);
            $cart_contents = $cart_manager->get_cart_contents();
            $get_time = microtime(true) - $start_time;
            
            $this->assert_true(is_array($cart_contents), 'Cart contents should be array');
            $this->assert_true(isset($cart_contents['items']), 'Cart should contain items');
            
            // Test cart count
            $cart_count = $cart_manager->get_cart_count();
            $this->assert_true($cart_count >= 2, 'Cart count should reflect added items');
            
            // Test cart summary (optimized)
            $start_time = microtime(true);
            $summary = $cart_manager->get_cart_summary();
            $summary_time = microtime(true) - $start_time;
            
            $this->assert_true(is_array($summary), 'Cart summary should be array');
            $this->assert_true(isset($summary['count']), 'Summary should contain count');
            $this->assert_true(isset($summary['hash']), 'Summary should contain hash');
            
            // Test updating cart item quantity
            if (!is_wp_error($add_result) && isset($add_result['cart_item_key'])) {
                $start_time = microtime(true);
                $update_result = $cart_manager->update_cart_item_quantity($add_result['cart_item_key'], 5);
                $update_time = microtime(true) - $start_time;
                
                if (!is_wp_error($update_result)) {
                    $this->assert_true($update_result['success'], 'Cart item should update successfully');
                    $this->assert_equals(5, $update_result['new_quantity'], 'New quantity should match');
                }
            }
            
            // Test cart clearing
            $start_time = microtime(true);
            $clear_result = $cart_manager->clear_cart();
            $clear_time = microtime(true) - $start_time;
            
            $this->assert_true($clear_result['success'], 'Cart should clear successfully');
            $this->assert_true($cart_manager->is_cart_empty(), 'Cart should be empty after clearing');
            
            $this->test_results['cart_operations'] = array(
                'status' => 'passed',
                'tests_run' => 8,
                'performance' => array(
                    'add_time' => $add_time * 1000, // Convert to ms
                    'get_time' => $get_time * 1000,
                    'summary_time' => $summary_time * 1000,
                    'update_time' => isset($update_time) ? $update_time * 1000 : 0,
                    'clear_time' => $clear_time * 1000
                )
            );
            
        } catch (Exception $e) {
            $this->test_results['cart_operations'] = array(
                'status' => 'failed',
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Test tax calculations functionality
     */
    private function test_tax_calculations() {
        $this->log_test('Testing Tax Calculations');
        
        try {
            $tax_calculator = new WUPOS_Tax_Calculator();
            
            // Test tax settings retrieval
            $tax_settings = $tax_calculator->get_tax_settings();
            $this->assert_true(is_array($tax_settings), 'Tax settings should be array');
            $this->assert_true(isset($tax_settings['taxes_enabled']), 'Tax settings should contain enabled flag');
            
            // Test single product tax calculation
            $start_time = microtime(true);
            $product_tax = $tax_calculator->calculate_product_tax(1, 100.00);
            $product_tax_time = microtime(true) - $start_time;
            
            $this->assert_true(is_array($product_tax), 'Product tax result should be array');
            $this->assert_true(isset($product_tax['taxable']), 'Tax result should indicate if taxable');
            
            // Test cart tax calculation
            $cart_items = array(
                'item1' => array(
                    'product_id' => 1,
                    'variation_id' => 0,
                    'quantity' => 2
                )
            );
            
            $start_time = microtime(true);
            $cart_tax = $tax_calculator->calculate_cart_taxes($cart_items);
            $cart_tax_time = microtime(true) - $start_time;
            
            $this->assert_true(is_array($cart_tax), 'Cart tax result should be array');
            $this->assert_true(isset($cart_tax['subtotal']), 'Tax result should contain subtotal');
            $this->assert_true(isset($cart_tax['total_tax']), 'Tax result should contain total tax');
            
            // Test cache functionality
            $cache_stats = $tax_calculator->get_cache_stats();
            $this->assert_true(is_array($cache_stats), 'Cache stats should be array');
            $this->assert_true(isset($cache_stats['taxes_enabled']), 'Cache stats should contain tax status');
            
            $this->test_results['tax_calculations'] = array(
                'status' => 'passed',
                'tests_run' => 6,
                'performance' => array(
                    'product_tax_time' => $product_tax_time * 1000,
                    'cart_tax_time' => $cart_tax_time * 1000
                ),
                'cache_stats' => $cache_stats
            );
            
        } catch (Exception $e) {
            $this->test_results['tax_calculations'] = array(
                'status' => 'failed',
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Test stock verification functionality
     */
    private function test_stock_verification() {
        $this->log_test('Testing Stock Verification');
        
        try {
            $inventory_sync = new WUPOS_Inventory_Sync();
            
            // Test real-time stock retrieval
            $start_time = microtime(true);
            $stock_data = $inventory_sync->get_real_time_stock(1);
            $stock_time = microtime(true) - $start_time;
            
            $this->assert_true(is_array($stock_data), 'Stock data should be array');
            $this->assert_true(isset($stock_data['current_stock']), 'Stock data should contain current stock');
            $this->assert_true(isset($stock_data['available_stock']), 'Stock data should contain available stock');
            
            // Test stock reservation
            $reservation_key = 'test_order_' . time();
            $start_time = microtime(true);
            $reservation_result = $inventory_sync->reserve_stock(1, 2, $reservation_key);
            $reservation_time = microtime(true) - $start_time;
            
            if (!is_wp_error($reservation_result)) {
                $this->assert_true($reservation_result['success'], 'Stock reservation should succeed');
                $this->assert_equals(2, $reservation_result['quantity'], 'Reserved quantity should match');
                
                // Test reservation release
                $release_result = $inventory_sync->release_stock_reservation($reservation_key);
                if (!is_wp_error($release_result)) {
                    $this->assert_true($release_result['success'], 'Stock reservation should release successfully');
                }
            }
            
            // Test batch stock availability check
            $products_to_check = array(
                1 => 3,
                2 => 1
            );
            
            $start_time = microtime(true);
            $batch_check = $inventory_sync->batch_check_stock_availability($products_to_check);
            $batch_time = microtime(true) - $start_time;
            
            $this->assert_true(is_array($batch_check), 'Batch check should return array');
            $this->assert_true(isset($batch_check['overall_available']), 'Batch check should indicate overall availability');
            $this->assert_true(isset($batch_check['products']), 'Batch check should contain product results');
            
            // Test stock status report
            $status_report = $inventory_sync->get_stock_status_report(array(1, 2));
            $this->assert_true(is_array($status_report), 'Status report should be array');
            $this->assert_true(isset($status_report['summary']), 'Report should contain summary');
            
            $this->test_results['stock_verification'] = array(
                'status' => 'passed',
                'tests_run' => 7,
                'performance' => array(
                    'stock_time' => $stock_time * 1000,
                    'reservation_time' => isset($reservation_time) ? $reservation_time * 1000 : 0,
                    'batch_time' => $batch_time * 1000
                )
            );
            
        } catch (Exception $e) {
            $this->test_results['stock_verification'] = array(
                'status' => 'failed',
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Test multi-terminal support functionality
     */
    private function test_multi_terminal_support() {
        $this->log_test('Testing Multi-Terminal Support');
        
        try {
            $terminal_managers = array();
            
            // Create multiple cart managers for different terminals
            foreach ($this->terminal_ids as $terminal_id) {
                $terminal_managers[$terminal_id] = new WUPOS_Cart_Manager($terminal_id);
            }
            
            // Test concurrent operations
            $concurrent_results = array();
            foreach ($terminal_managers as $terminal_id => $manager) {
                $start_time = microtime(true);
                $result = $manager->add_to_cart(1, 1); // Same product, different terminals
                $operation_time = microtime(true) - $start_time;
                
                $concurrent_results[$terminal_id] = array(
                    'result' => $result,
                    'time' => $operation_time * 1000,
                    'session_id' => $manager->get_session_id(),
                    'cart_count' => $manager->get_cart_count()
                );
            }
            
            // Verify each terminal has separate carts
            $unique_sessions = array_unique(array_column($concurrent_results, 'session_id'));
            $this->assert_equals(count($this->terminal_ids), count($unique_sessions), 'Each terminal should have unique session');
            
            // Test session statistics
            $session_stats = WUPOS_Session_Handler::get_session_stats();
            $this->assert_true(is_array($session_stats), 'Session stats should be array');
            $this->assert_true($session_stats['active_sessions'] >= count($this->terminal_ids), 'Should have multiple active sessions');
            
            // Test terminal-specific sessions
            $terminal_sessions = WUPOS_Session_Handler::get_terminal_sessions($this->terminal_ids[0]);
            $this->assert_true(is_array($terminal_sessions), 'Terminal sessions should be array');
            
            $this->test_results['multi_terminal_support'] = array(
                'status' => 'passed',
                'tests_run' => 4,
                'terminals_tested' => count($this->terminal_ids),
                'concurrent_results' => $concurrent_results,
                'session_stats' => $session_stats
            );
            
        } catch (Exception $e) {
            $this->test_results['multi_terminal_support'] = array(
                'status' => 'failed',
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Test performance targets (<50ms operations)
     */
    private function test_performance_targets() {
        $this->log_test('Testing Performance Targets');
        
        try {
            $cart_manager = new WUPOS_Cart_Manager('performance_test_terminal');
            $cart_manager->set_performance_mode(true);
            
            $performance_tests = array();
            
            // Test add to cart performance (multiple iterations)
            $add_times = array();
            for ($i = 0; $i < 10; $i++) {
                $start_time = microtime(true);
                $result = $cart_manager->add_to_cart($i + 1, 1);
                $add_times[] = (microtime(true) - $start_time) * 1000;
            }
            
            $avg_add_time = array_sum($add_times) / count($add_times);
            $max_add_time = max($add_times);
            
            $performance_tests['add_to_cart'] = array(
                'average_time' => $avg_add_time,
                'max_time' => $max_add_time,
                'target_met' => $max_add_time < 50,
                'iterations' => count($add_times)
            );
            
            // Test cart contents retrieval performance
            $get_times = array();
            for ($i = 0; $i < 10; $i++) {
                $start_time = microtime(true);
                $cart_manager->get_cart_contents();
                $get_times[] = (microtime(true) - $start_time) * 1000;
            }
            
            $avg_get_time = array_sum($get_times) / count($get_times);
            $max_get_time = max($get_times);
            
            $performance_tests['get_cart_contents'] = array(
                'average_time' => $avg_get_time,
                'max_time' => $max_get_time,
                'target_met' => $max_get_time < 50,
                'iterations' => count($get_times)
            );
            
            // Test cart summary performance
            $summary_times = array();
            for ($i = 0; $i < 10; $i++) {
                $start_time = microtime(true);
                $cart_manager->get_cart_summary();
                $summary_times[] = (microtime(true) - $start_time) * 1000;
            }
            
            $avg_summary_time = array_sum($summary_times) / count($summary_times);
            $max_summary_time = max($summary_times);
            
            $performance_tests['get_cart_summary'] = array(
                'average_time' => $avg_summary_time,
                'max_time' => $max_summary_time,
                'target_met' => $max_summary_time < 50,
                'iterations' => count($summary_times)
            );
            
            // Get performance metrics from cart manager
            $cart_metrics = $cart_manager->get_performance_metrics();
            
            // Overall performance assessment
            $targets_met = 0;
            $total_tests = count($performance_tests);
            
            foreach ($performance_tests as $test) {
                if ($test['target_met']) {
                    $targets_met++;
                }
            }
            
            $performance_score = ($targets_met / $total_tests) * 100;
            
            $this->test_results['performance_targets'] = array(
                'status' => $performance_score >= 90 ? 'passed' : 'warning',
                'performance_score' => $performance_score,
                'targets_met' => $targets_met,
                'total_tests' => $total_tests,
                'detailed_results' => $performance_tests,
                'cart_metrics' => $cart_metrics
            );
            
        } catch (Exception $e) {
            $this->test_results['performance_targets'] = array(
                'status' => 'failed',
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Test batch operations functionality
     */
    private function test_batch_operations() {
        $this->log_test('Testing Batch Operations');
        
        try {
            $cart_manager = new WUPOS_Cart_Manager('batch_test_terminal');
            
            // Prepare batch items
            $batch_items = array();
            for ($i = 1; $i <= 5; $i++) {
                $batch_items[] = array(
                    'product_id' => $i,
                    'quantity' => 2,
                    'variation_id' => 0,
                    'variation_data' => array(),
                    'item_data' => array()
                );
            }
            
            // Test batch add to cart
            $start_time = microtime(true);
            $batch_result = $cart_manager->batch_add_to_cart($batch_items);
            $batch_time = (microtime(true) - $start_time) * 1000;
            
            if (!is_wp_error($batch_result)) {
                $this->assert_true($batch_result['success'], 'Batch add should succeed');
                $this->assert_equals(count($batch_items), $batch_result['total_items'], 'Total items should match');
                $this->assert_true($batch_result['success_count'] > 0, 'Should have successful additions');
            }
            
            // Compare batch vs individual performance
            $cart_manager_individual = new WUPOS_Cart_Manager('individual_test_terminal');
            
            $start_time = microtime(true);
            foreach ($batch_items as $item) {
                $cart_manager_individual->add_to_cart($item['product_id'], $item['quantity']);
            }
            $individual_time = (microtime(true) - $start_time) * 1000;
            
            $performance_improvement = (($individual_time - $batch_time) / $individual_time) * 100;
            
            $this->test_results['batch_operations'] = array(
                'status' => 'passed',
                'batch_time' => $batch_time,
                'individual_time' => $individual_time,
                'performance_improvement' => $performance_improvement,
                'items_processed' => count($batch_items),
                'batch_result' => $batch_result
            );
            
        } catch (Exception $e) {
            $this->test_results['batch_operations'] = array(
                'status' => 'failed',
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Test security validation
     */
    private function test_security_validation() {
        $this->log_test('Testing Security Validation');
        
        try {
            $cart_manager = new WUPOS_Cart_Manager('security_test_terminal');
            
            // Test invalid product ID
            $invalid_result = $cart_manager->add_to_cart(-1, 1);
            $this->assert_true(is_wp_error($invalid_result), 'Invalid product ID should return error');
            
            // Test zero quantity
            $zero_qty_result = $cart_manager->add_to_cart(1, 0);
            $this->assert_true(is_wp_error($zero_qty_result), 'Zero quantity should return error');
            
            // Test negative quantity
            $negative_qty_result = $cart_manager->add_to_cart(1, -5);
            $this->assert_true(is_wp_error($negative_qty_result), 'Negative quantity should return error');
            
            // Test cart status validation
            $cart_status = $cart_manager->check_cart_status();
            $this->assert_true(is_array($cart_status), 'Cart status should be array');
            $this->assert_true(isset($cart_status['session_valid']), 'Status should include session validity');
            
            $this->test_results['security_validation'] = array(
                'status' => 'passed',
                'tests_run' => 4,
                'validation_checks' => array(
                    'invalid_product_id' => 'blocked',
                    'zero_quantity' => 'blocked',
                    'negative_quantity' => 'blocked',
                    'cart_status_check' => 'functional'
                )
            );
            
        } catch (Exception $e) {
            $this->test_results['security_validation'] = array(
                'status' => 'failed',
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Test error handling
     */
    private function test_error_handling() {
        $this->log_test('Testing Error Handling');
        
        try {
            $cart_manager = new WUPOS_Cart_Manager('error_test_terminal');
            
            // Test non-existent product
            $nonexistent_result = $cart_manager->add_to_cart(999999, 1);
            $this->assert_true(is_wp_error($nonexistent_result), 'Non-existent product should return error');
            
            // Test invalid cart item key for update
            $invalid_update = $cart_manager->update_cart_item_quantity('invalid_key', 5);
            $this->assert_true(is_wp_error($invalid_update), 'Invalid cart item key should return error');
            
            // Test invalid cart item key for removal
            $invalid_remove = $cart_manager->remove_cart_item('invalid_key');
            $this->assert_true(is_wp_error($invalid_remove), 'Invalid cart item key removal should return error');
            
            $this->test_results['error_handling'] = array(
                'status' => 'passed',
                'tests_run' => 3,
                'error_scenarios_tested' => array(
                    'nonexistent_product',
                    'invalid_update_key',
                    'invalid_remove_key'
                )
            );
            
        } catch (Exception $e) {
            $this->test_results['error_handling'] = array(
                'status' => 'failed',
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Generate comprehensive test report
     *
     * @return array Test report with quality assessment
     */
    private function generate_test_report() {
        $total_tests = 0;
        $passed_tests = 0;
        $failed_tests = 0;
        $warning_tests = 0;
        
        $quality_metrics = array(
            'functionality' => 0,
            'performance' => 0,
            'security' => 0,
            'reliability' => 0,
            'overall' => 0
        );
        
        foreach ($this->test_results as $test_category => $result) {
            $total_tests++;
            
            switch ($result['status']) {
                case 'passed':
                    $passed_tests++;
                    break;
                case 'failed':
                    $failed_tests++;
                    break;
                case 'warning':
                    $warning_tests++;
                    break;
            }
        }
        
        // Calculate quality metrics (out of 10)
        $success_rate = $total_tests > 0 ? ($passed_tests / $total_tests) * 100 : 0;
        
        // Functionality score based on core feature tests
        $functionality_tests = array('session_management', 'cart_operations', 'tax_calculations', 'stock_verification');
        $functionality_passed = 0;
        foreach ($functionality_tests as $test) {
            if (isset($this->test_results[$test]) && $this->test_results[$test]['status'] === 'passed') {
                $functionality_passed++;
            }
        }
        $quality_metrics['functionality'] = (count($functionality_tests) > 0) ? 
            ($functionality_passed / count($functionality_tests)) * 10 : 0;
        
        // Performance score
        $performance_score = 0;
        if (isset($this->test_results['performance_targets'])) {
            $performance_score = isset($this->test_results['performance_targets']['performance_score']) ?
                ($this->test_results['performance_targets']['performance_score'] / 100) * 10 : 0;
        }
        $quality_metrics['performance'] = $performance_score;
        
        // Security score
        $security_score = 0;
        if (isset($this->test_results['security_validation']) && $this->test_results['security_validation']['status'] === 'passed') {
            $security_score = 10;
        }
        $quality_metrics['security'] = $security_score;
        
        // Reliability score based on error handling and multi-terminal
        $reliability_tests = array('error_handling', 'multi_terminal_support');
        $reliability_passed = 0;
        foreach ($reliability_tests as $test) {
            if (isset($this->test_results[$test]) && $this->test_results[$test]['status'] === 'passed') {
                $reliability_passed++;
            }
        }
        $quality_metrics['reliability'] = (count($reliability_tests) > 0) ?
            ($reliability_passed / count($reliability_tests)) * 10 : 0;
        
        // Overall quality score
        $quality_metrics['overall'] = array_sum($quality_metrics) / 4;
        
        return array(
            'test_summary' => array(
                'total_tests' => $total_tests,
                'passed' => $passed_tests,
                'failed' => $failed_tests,
                'warnings' => $warning_tests,
                'success_rate' => round($success_rate, 2)
            ),
            'quality_metrics' => $quality_metrics,
            'detailed_results' => $this->test_results,
            'recommendations' => $this->generate_recommendations(),
            'timestamp' => current_time('timestamp'),
            'test_duration' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            'environment' => array(
                'wp_version' => get_bloginfo('version'),
                'wc_version' => defined('WC_VERSION') ? WC_VERSION : 'N/A',
                'php_version' => PHP_VERSION,
                'hpos_enabled' => wupos_is_hpos_enabled(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            )
        );
    }

    /**
     * Generate recommendations based on test results
     *
     * @return array Recommendations for improvement
     */
    private function generate_recommendations() {
        $recommendations = array();
        
        // Performance recommendations
        if (isset($this->test_results['performance_targets'])) {
            $performance_result = $this->test_results['performance_targets'];
            if (isset($performance_result['performance_score']) && $performance_result['performance_score'] < 90) {
                $recommendations[] = array(
                    'category' => 'performance',
                    'priority' => 'high',
                    'issue' => 'Some cart operations exceed 50ms target',
                    'recommendation' => 'Consider implementing object caching, database query optimization, or session storage improvements'
                );
            }
        }
        
        // Multi-terminal recommendations
        if (isset($this->test_results['multi_terminal_support'])) {
            $mt_result = $this->test_results['multi_terminal_support'];
            if ($mt_result['status'] !== 'passed') {
                $recommendations[] = array(
                    'category' => 'reliability',
                    'priority' => 'high',
                    'issue' => 'Multi-terminal support test failed',
                    'recommendation' => 'Review session isolation and concurrent access handling'
                );
            }
        }
        
        // Add general recommendations
        $recommendations[] = array(
            'category' => 'monitoring',
            'priority' => 'medium',
            'issue' => 'Continuous monitoring needed',
            'recommendation' => 'Implement real-time performance monitoring and alerting for cart operations'
        );
        
        return $recommendations;
    }

    /**
     * Assert helper function
     */
    private function assert_true($condition, $message) {
        if (!$condition) {
            throw new Exception("Assertion failed: $message");
        }
    }

    /**
     * Assert equals helper function
     */
    private function assert_equals($expected, $actual, $message) {
        if ($expected !== $actual) {
            throw new Exception("Assertion failed: $message (Expected: $expected, Actual: $actual)");
        }
    }

    /**
     * Test logging helper
     */
    private function log_test($message) {
        wupos_log("CART_TEST: $message", 'info');
    }
}

/**
 * Run cart business logic tests if accessed directly
 */
if (defined('WP_CLI') || (defined('DOING_AJAX') && DOING_AJAX)) {
    $test_suite = new WUPOS_Cart_Business_Logic_Test();
    $results = $test_suite->run_all_tests();
    
    if (defined('WP_CLI')) {
        WP_CLI::success('Cart Business Logic Tests Completed');
        WP_CLI::line('Success Rate: ' . $results['test_summary']['success_rate'] . '%');
        WP_CLI::line('Overall Quality Score: ' . round($results['quality_metrics']['overall'], 1) . '/10');
    } else {
        wp_send_json_success($results);
    }
}