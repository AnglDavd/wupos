<?php
/**
 * WUPOS Admin Class
 *
 * @package WUPOS
 * @version 1.0.0
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
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add admin menu.
     */
    public function admin_menu() {
        add_options_page(
            __('WUPOS Settings', 'wupos'),
            __('WUPOS', 'wupos'),
            'manage_options',
            'wupos-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Register plugin settings.
     */
    public function register_settings() {
        register_setting(
            'wupos_settings_group',
            'wupos_settings',
            array($this, 'sanitize_settings')
        );
        
        add_settings_section(
            'wupos_main_settings',
            __('Main Settings', 'wupos'),
            array($this, 'settings_section_callback'),
            'wupos-settings'
        );
        
        add_settings_field(
            'pos_page',
            __('POS Page', 'wupos'),
            array($this, 'pos_page_callback'),
            'wupos-settings',
            'wupos_main_settings'
        );
    }
    
    /**
     * Settings section callback.
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure the basic settings for WUPOS.', 'wupos') . '</p>';
    }
    
    /**
     * POS page callback.
     */
    public function pos_page_callback() {
        $options = get_option('wupos_settings', array());
        $selected_page = isset($options['pos_page']) ? $options['pos_page'] : 0;
        
        $pages = get_pages();
        
        echo '<select name="wupos_settings[pos_page]" id="pos_page">';
        echo '<option value="0">' . __('Select a page...', 'wupos') . '</option>';
        
        foreach ($pages as $page) {
            $selected = selected($selected_page, $page->ID, false);
            echo '<option value="' . esc_attr($page->ID) . '"' . $selected . '>' . esc_html($page->post_title) . '</option>';
        }
        
        echo '</select>';
        echo '<p class="description">' . __('Select the page where the POS system will be displayed. Add the shortcode [wupos_pos] to that page.', 'wupos') . '</p>';
    }
    
    /**
     * Sanitize settings.
     *
     * @param array $input Settings input.
     * @return array Sanitized settings.
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['pos_page'])) {
            $sanitized['pos_page'] = absint($input['pos_page']);
        }
        
        return $sanitized;
    }
    
    /**
     * Settings page callback.
     */
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check if settings were saved
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'wupos_messages',
                'wupos_message',
                __('Settings saved successfully.', 'wupos'),
                'updated'
            );
        }
        
        settings_errors('wupos_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="wupos-admin-header">
                <h2><?php _e('WUPOS - WooCommerce Ultimate Point of Sale', 'wupos'); ?></h2>
                <p><?php _e('Version', 'wupos'); ?> <?php echo WUPOS_VERSION; ?></p>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('wupos_settings_group');
                do_settings_sections('wupos-settings');
                submit_button(__('Save Settings', 'wupos'));
                ?>
            </form>
            
            <div class="wupos-admin-info">
                <h3><?php _e('How to use WUPOS', 'wupos'); ?></h3>
                <ol>
                    <li><?php _e('Select a page from the dropdown above where you want to display the POS system.', 'wupos'); ?></li>
                    <li><?php _e('Add the shortcode [wupos_pos] to that page content.', 'wupos'); ?></li>
                    <li><?php _e('Save the settings and visit the selected page to see the POS interface.', 'wupos'); ?></li>
                </ol>
                
                <h4><?php _e('Shortcode Usage', 'wupos'); ?></h4>
                <p><code>[wupos_pos]</code> - <?php _e('Display the complete POS interface', 'wupos'); ?></p>
            </div>
        </div>
        
        <style>
        .wupos-admin-header {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .wupos-admin-header h2 {
            margin: 0 0 10px 0;
            color: #1d2327;
        }
        
        .wupos-admin-info {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .wupos-admin-info h3, .wupos-admin-info h4 {
            margin-top: 0;
        }
        
        .wupos-admin-info code {
            background: #f0f0f1;
            padding: 2px 4px;
            border-radius: 2px;
        }
        </style>
        <?php
    }
}

// Initialize admin class
if (is_admin()) {
    new WUPOS_Admin();
}