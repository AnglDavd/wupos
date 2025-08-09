<?php
/**
 * Installation related functions and actions
 *
 * @package WUPOS
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS_Install Class.
 */
class WUPOS_Install {

    /**
     * Hook in tabs.
     */
    public static function init() {
        add_action('init', array(__CLASS__, 'check_version'), 5);
    }

    /**
     * Check WUPOS version and run installer if required.
     */
    public static function check_version() {
        if (!defined('IFRAME_REQUEST') && version_compare(get_option('wupos_version'), wupos_get_version(), '<')) {
            self::install();
            do_action('wupos_updated');
        }
    }

    /**
     * Install WUPOS.
     */
    public static function install() {
        if (!is_blog_installed()) {
            return;
        }

        // Check if we are not already running this routine.
        if (false === wp_installing()) {
            wp_installing(true);

            self::create_options();
            self::create_roles();
            self::create_capabilities();
            self::update_wupos_version();

            wp_installing(false);
        }

        // Trigger action
        do_action('wupos_installed');
    }

    /**
     * Default options.
     *
     * Sets up the default options used on the settings pages.
     */
    private static function create_options() {
        $options = array(
            'wupos_pos_enabled'              => 'yes',
            'wupos_pos_page'                 => '',
            'wupos_currency_symbol_position' => get_option('woocommerce_currency_pos', 'left'),
            'wupos_tax_display'              => get_option('woocommerce_tax_display_shop', 'excl'),
            'wupos_receipt_template'         => 'default',
            'wupos_barcode_field'            => '_sku',
            'wupos_customer_registration'    => 'yes',
            'wupos_default_customer'         => 0,
            'wupos_auto_print_receipt'       => 'no',
            'wupos_sound_enabled'            => 'yes',
        );

        foreach ($options as $option => $value) {
            add_option($option, $value);
        }
    }

    /**
     * Create roles and capabilities.
     */
    private static function create_roles() {
        global $wp_roles;

        if (!class_exists('WP_Roles')) {
            return;
        }

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        // POS Manager role
        add_role(
            'wupos_manager',
            __('POS Manager', 'wupos'),
            array(
                'read'                   => true,
                'manage_woocommerce_pos' => true,
                'edit_shop_orders'       => true,
                'read_shop_orders'       => true,
                'edit_products'          => true,
                'read_products'          => true,
                'manage_product_terms'   => true,
                'edit_product_terms'     => true,
                'delete_product_terms'   => true,
                'assign_product_terms'   => true,
                'view_woocommerce_reports' => true,
            )
        );

        // POS Cashier role
        add_role(
            'wupos_cashier',
            __('POS Cashier', 'wupos'),
            array(
                'read'                   => true,
                'manage_woocommerce_pos' => true,
                'edit_shop_orders'       => true,
                'read_shop_orders'       => true,
                'read_products'          => true,
            )
        );
    }

    /**
     * Create capabilities.
     */
    private static function create_capabilities() {
        // Add capabilities to administrator
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('manage_woocommerce_pos');
            $admin->add_cap('wupos_view_reports');
            $admin->add_cap('wupos_manage_settings');
        }

        // Add capabilities to shop manager
        $shop_manager = get_role('shop_manager');
        if ($shop_manager) {
            $shop_manager->add_cap('manage_woocommerce_pos');
            $shop_manager->add_cap('wupos_view_reports');
        }
    }

    /**
     * Update WUPOS version to current.
     */
    private static function update_wupos_version() {
        delete_option('wupos_version');
        add_option('wupos_version', wupos_get_version());
    }

    /**
     * Deactivate plugin.
     */
    public static function deactivate() {
        // Clear scheduled hooks
        wp_clear_scheduled_hook('wupos_cleanup_sessions');
        
        // Clear any cached data
        wp_cache_flush();
        
        wupos_log('WUPOS plugin deactivated');
    }

    /**
     * Remove plugin data on uninstall.
     */
    public static function uninstall() {
        global $wpdb;

        // Delete options
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wupos_%'");
        
        // Delete user meta
        $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '_wupos_%'");
        
        // Delete post meta
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_wupos_%'");
        
        // Remove roles
        remove_role('wupos_manager');
        remove_role('wupos_cashier');
        
        // Remove capabilities from existing roles
        $roles = array('administrator', 'shop_manager');
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                $role->remove_cap('manage_woocommerce_pos');
                $role->remove_cap('wupos_view_reports');
                $role->remove_cap('wupos_manage_settings');
            }
        }

        wupos_log('WUPOS plugin data removed');
    }
}

WUPOS_Install::init();