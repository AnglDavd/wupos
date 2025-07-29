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
        add_action('wp_ajax_wupos_create_pos_page', array($this, 'ajax_create_pos_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add admin menu.
     */
    public function admin_menu() {
        add_menu_page(
            __('WUPOS Settings', 'wupos'),
            __('WUPOS', 'wupos'),
            'manage_woocommerce',
            'wupos-settings',
            array($this, 'settings_page'),
            'dashicons-store',
            58.5
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
            __('Select Existing Page', 'wupos'),
            array($this, 'pos_page_callback'),
            'wupos-settings',
            'wupos_main_settings'
        );
        
        add_settings_field(
            'create_pos_page',
            __('Create New POS Page', 'wupos'),
            array($this, 'create_pos_page_callback'),
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
        
        echo '<div class="wupos-page-selection">';
        echo '<select name="wupos_settings[pos_page]" id="pos_page">';
        echo '<option value="0">' . __('Select a page...', 'wupos') . '</option>';
        
        foreach ($pages as $page) {
            $selected = selected($selected_page, $page->ID, false);
            echo '<option value="' . esc_attr($page->ID) . '"' . $selected . '>' . esc_html($page->post_title) . '</option>';
        }
        
        echo '</select>';
        echo '<button type="button" class="button" id="copy-shortcode">' . __('Copy Shortcode', 'wupos') . '</button>';
        echo '<span id="copy-feedback" class="wupos-feedback"></span>';
        echo '</div>';
        echo '<div class="wupos-shortcode-display">';
        echo '<input type="text" id="shortcode-text" value="[wupos_pos]" readonly>';
        echo '</div>';
        echo '<p class="description">' . __('Select the page where the POS system will be displayed, then copy the shortcode to that page.', 'wupos') . '</p>';
    }
    
    /**
     * Create POS page callback.
     */
    public function create_pos_page_callback() {
        echo '<div class="wupos-create-page-section">';
        echo '<table class="form-table" style="margin: 0;">';
        echo '<tr>';
        echo '<td style="width: 150px;"><label for="pos_page_title">' . __('Page Title:', 'wupos') . '</label></td>';
        echo '<td><input type="text" id="pos_page_title" name="pos_page_title" value="' . __('POS System', 'wupos') . '" style="width: 250px;"></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td><label for="pos_page_slug">' . __('Page Slug:', 'wupos') . '</label></td>';
        echo '<td><input type="text" id="pos_page_slug" name="pos_page_slug" value="' . __('pos-system', 'wupos') . '" style="width: 250px;"></td>';
        echo '</tr>';
        echo '</table>';
        echo '<br>';
        echo '<button type="button" class="button button-primary" id="create-pos-page">' . __('Create POS Page', 'wupos') . '</button>';
        echo '<span id="create-page-status" class="wupos-feedback"></span>';
        echo '<p class="description">' . __('Create a new page specifically for the POS system. The shortcode will be automatically added.', 'wupos') . '</p>';
        echo '</div>';
    }
    
    /**
     * Enqueue admin scripts and styles.
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our settings page
        if ('toplevel_page_wupos-settings' !== $hook) {
            return;
        }
        
        wp_enqueue_script('wupos-admin-custom', WUPOS_PLUGIN_URL . 'assets/js/wupos-admin.js', array('jquery'), WUPOS_VERSION, true);
        
        // Localize script for AJAX
        wp_localize_script('wupos-admin-custom', 'wupos_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wupos_admin_nonce'),
            'messages' => array(
                'copied' => __('Shortcode copied to clipboard!', 'wupos'),
                'copy_error' => __('Failed to copy shortcode', 'wupos'),
                'creating' => __('Creating page...', 'wupos'),
                'created' => __('Page created successfully!', 'wupos'),
                'error' => __('Error creating page', 'wupos'),
                'title_required' => __('Page title is required', 'wupos'),
                'slug_required' => __('Page slug is required', 'wupos')
            )
        ));
    }
    
    /**
     * AJAX handler for creating POS page.
     */
    public function ajax_create_pos_page() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wupos_admin_nonce')) {
            wp_die(__('Security check failed', 'wupos'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'wupos'));
        }
        
        $page_title = sanitize_text_field($_POST['page_title']);
        $page_slug = sanitize_title($_POST['page_slug']);
        
        // Validate input
        if (empty($page_title)) {
            wp_send_json_error(__('Page title is required', 'wupos'));
        }
        
        if (empty($page_slug)) {
            wp_send_json_error(__('Page slug is required', 'wupos'));
        }
        
        // Check if page with this slug already exists
        $existing_page = get_page_by_path($page_slug);
        if ($existing_page) {
            wp_send_json_error(__('A page with this slug already exists', 'wupos'));
        }
        
        // Create the page
        $page_data = array(
            'post_title' => $page_title,
            'post_name' => $page_slug,
            'post_content' => '[wupos_pos]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => get_current_user_id()
        );
        
        $page_id = wp_insert_post($page_data);
        
        if (is_wp_error($page_id)) {
            wp_send_json_error(__('Failed to create page', 'wupos'));
        }
        
        // Update plugin settings to select the new page
        $options = get_option('wupos_settings', array());
        $options['pos_page'] = $page_id;
        update_option('wupos_settings', $options);
        
        // Get updated pages list for dropdown
        $pages = get_pages();
        $pages_html = '<option value="0">' . __('Select a page...', 'wupos') . '</option>';
        
        foreach ($pages as $page) {
            $selected = ($page_id == $page->ID) ? ' selected="selected"' : '';
            $pages_html .= '<option value="' . esc_attr($page->ID) . '"' . $selected . '>' . esc_html($page->post_title) . '</option>';
        }
        
        wp_send_json_success(array(
            'message' => __('Page created successfully!', 'wupos'),
            'page_id' => $page_id,
            'page_url' => get_permalink($page_id),
            'pages_html' => $pages_html
        ));
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
        
        .wupos-page-selection {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .wupos-page-selection select {
            min-width: 200px;
        }
        
        .wupos-shortcode-display {
            margin: 10px 0;
        }
        
        .wupos-shortcode-display input {
            width: 150px;
            font-family: monospace;
            background: #f0f0f1;
            border: 1px solid #ccd0d4;
            padding: 5px 8px;
        }
        
        .wupos-create-page-section {
            background: #f9f9f9;
            border: 1px solid #e5e5e5;
            padding: 15px;
            border-radius: 4px;
            margin-top: 15px;
        }
        
        .wupos-feedback {
            margin-left: 10px;
            font-weight: bold;
        }
        
        .wupos-feedback.success {
            color: #46b450;
        }
        
        .wupos-feedback.error {
            color: #dc3232;
        }
        
        .wupos-feedback.loading {
            color: #0073aa;
        }
        
        #create-pos-page:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        </style>
        <?php
    }
}

// Initialize admin class
if (is_admin()) {
    new WUPOS_Admin();
}