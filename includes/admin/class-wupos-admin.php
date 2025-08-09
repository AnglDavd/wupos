<?php
/**
 * WUPOS Admin
 *
 * @package WUPOS\Admin
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WUPOS_Admin class.
 */
class WUPOS_Admin {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_filter('plugin_action_links_' . WUPOS_PLUGIN_BASENAME, array($this, 'plugin_action_links'));
    }

    /**
     * Add menu items.
     */
    public function admin_menu() {
        add_menu_page(
            __('WUPOS', 'wupos'),
            __('POS', 'wupos'),
            'manage_woocommerce_pos',
            'wupos',
            array($this, 'pos_page'),
            'dashicons-store',
            56
        );

        add_submenu_page(
            'wupos',
            __('POS', 'wupos'),
            __('Point of Sale', 'wupos'),
            'manage_woocommerce_pos',
            'wupos',
            array($this, 'pos_page')
        );

        add_submenu_page(
            'wupos',
            __('Settings', 'wupos'),
            __('Settings', 'wupos'),
            'wupos_manage_settings',
            'wupos-settings',
            array($this, 'settings_page')
        );

        add_submenu_page(
            'wupos',
            __('Reports', 'wupos'),
            __('Reports', 'wupos'),
            'wupos_view_reports',
            'wupos-reports',
            array($this, 'reports_page')
        );
    }

    /**
     * Admin init.
     */
    public function admin_init() {
        // Register settings
        register_setting('wupos_settings', 'wupos_pos_enabled');
        register_setting('wupos_settings', 'wupos_currency_symbol_position');
        register_setting('wupos_settings', 'wupos_tax_display');
        register_setting('wupos_settings', 'wupos_receipt_template');
        register_setting('wupos_settings', 'wupos_barcode_field');
        register_setting('wupos_settings', 'wupos_customer_registration');
        register_setting('wupos_settings', 'wupos_default_customer');
        register_setting('wupos_settings', 'wupos_auto_print_receipt');
        register_setting('wupos_settings', 'wupos_sound_enabled');
    }

    /**
     * Enqueue admin scripts and styles.
     */
    public function admin_scripts($hook) {
        if (strpos($hook, 'wupos') === false) {
            return;
        }

        wp_enqueue_style(
            'wupos-admin',
            WUPOS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WUPOS_VERSION
        );

        wp_enqueue_script(
            'wupos-admin',
            WUPOS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            WUPOS_VERSION,
            true
        );

        wp_localize_script('wupos-admin', 'wupos_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('wupos_ajax'),
            'strings'  => array(
                'confirm_delete' => __('Are you sure you want to delete this item?', 'wupos'),
                'error_occurred' => __('An error occurred. Please try again.', 'wupos'),
            ),
        ));
    }

    /**
     * Add action links to plugins page.
     */
    public function plugin_action_links($links) {
        $action_links = array(
            'pos'      => '<a href="' . admin_url('admin.php?page=wupos') . '">' . __('POS', 'wupos') . '</a>',
            'settings' => '<a href="' . admin_url('admin.php?page=wupos-settings') . '">' . __('Settings', 'wupos') . '</a>',
        );

        return array_merge($action_links, $links);
    }

    /**
     * POS page.
     */
    public function pos_page() {
        if (!wupos_user_can_pos()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wupos'));
        }

        // Check if WooCommerce is active
        if (!wupos_is_woocommerce_active()) {
            echo '<div class="error"><p>' . __('WooCommerce is required for WUPOS to function.', 'wupos') . '</p></div>';
            return;
        }

        include_once wupos_get_template_path('admin/pos-page.php');
    }

    /**
     * Settings page.
     */
    public function settings_page() {
        if (!current_user_can('wupos_manage_settings')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wupos'));
        }

        if (isset($_POST['submit'])) {
            $this->save_settings();
        }

        include_once wupos_get_template_path('admin/settings-page.php');
    }

    /**
     * Reports page.
     */
    public function reports_page() {
        if (!current_user_can('wupos_view_reports')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wupos'));
        }

        include_once wupos_get_template_path('admin/reports-page.php');
    }

    /**
     * Save settings.
     */
    private function save_settings() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'wupos-settings-save')) {
            wp_die(__('Security check failed.', 'wupos'));
        }

        $settings = array(
            'wupos_pos_enabled',
            'wupos_currency_symbol_position',
            'wupos_tax_display',
            'wupos_receipt_template',
            'wupos_barcode_field',
            'wupos_customer_registration',
            'wupos_default_customer',
            'wupos_auto_print_receipt',
            'wupos_sound_enabled',
        );

        foreach ($settings as $setting) {
            if (isset($_POST[$setting])) {
                update_option($setting, wupos_sanitize_input($_POST[$setting]));
            }
        }

        add_settings_error('wupos_settings', 'settings_updated', __('Settings saved.', 'wupos'), 'updated');
    }

    /**
     * Display admin notices.
     */
    public static function admin_notices() {
        // Check for WooCommerce
        if (!wupos_is_woocommerce_active()) {
            echo '<div class="error"><p>';
            echo sprintf(
                __('WUPOS requires WooCommerce to be installed and active. You can download %s here.', 'wupos'),
                '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>'
            );
            echo '</p></div>';
        }

        // Check WordPress version
        if (version_compare(get_bloginfo('version'), '6.0', '<')) {
            echo '<div class="error"><p>';
            echo __('WUPOS requires WordPress 6.0 or higher.', 'wupos');
            echo '</p></div>';
        }

        // Check PHP version
        if (version_compare(phpversion(), '8.0', '<')) {
            echo '<div class="error"><p>';
            echo __('WUPOS requires PHP 8.0 or higher.', 'wupos');
            echo '</p></div>';
        }
    }
}

return new WUPOS_Admin();