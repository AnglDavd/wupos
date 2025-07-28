/**
 * WUPOS Admin JavaScript
 * 
 * JavaScript for the WordPress admin interface of WUPOS.
 *
 * @package WUPOS
 * @version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * WUPOS Admin Class
     */
    var WUPOSAdmin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            console.log('WUPOS Admin: Initializing...');
            
            this.bindEvents();
            this.initializeElements();
            
            console.log('WUPOS Admin: Initialization complete');
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Settings form validation
            $('form').on('submit', this.validateForm);
            
            // POS page selection change
            $('#pos_page').on('change', this.handlePageSelection);
            
            console.log('WUPOS Admin: Event handlers bound');
        },
        
        /**
         * Initialize admin elements
         */
        initializeElements: function() {
            // Enhance select dropdowns if needed
            this.enhanceSelects();
            
            // Show additional info based on current settings
            this.showContextualInfo();
        },
        
        /**
         * Validate settings form
         */
        validateForm: function(e) {
            var posPage = $('#pos_page').val();
            
            // Basic validation
            if (posPage && posPage !== '0') {
                console.log('WUPOS Admin: Form validation passed');
                return true;
            }
            
            // Show validation message for empty selection
            if (posPage === '0') {
                console.log('WUPOS Admin: No page selected');
                // Don't prevent submission - user might want to clear the setting
            }
            
            return true;
        },
        
        /**
         * Handle POS page selection change
         */
        handlePageSelection: function() {
            var selectedPage = $(this).val();
            var selectedText = $(this).find('option:selected').text();
            
            if (selectedPage && selectedPage !== '0') {
                console.log('WUPOS Admin: Selected page:', selectedText);
                
                // Show additional information
                WUPOSAdmin.showPageInfo(selectedPage, selectedText);
            } else {
                WUPOSAdmin.hidePageInfo();
            }
        },
        
        /**
         * Show information about selected page
         */
        showPageInfo: function(pageId, pageTitle) {
            // Remove existing info if present
            $('.wupos-page-info').remove();
            
            // Create info element
            var infoHtml = `
                <div class="wupos-page-info" style="margin-top: 10px; padding: 10px; background: #e7f3ff; border: 1px solid #72aee6; border-radius: 4px;">
                    <p><strong>Selected Page:</strong> ${pageTitle}</p>
                    <p><strong>Next Step:</strong> Add the shortcode <code>[wupos_pos]</code> to this page's content.</p>
                    <p><strong>Preview:</strong> <a href="/wp-admin/post.php?post=${pageId}&action=edit" target="_blank">Edit this page</a></p>
                </div>
            `;
            
            $('#pos_page').closest('td').append(infoHtml);
        },
        
        /**
         * Hide page information
         */
        hidePageInfo: function() {
            $('.wupos-page-info').fadeOut(300, function() {
                $(this).remove();
            });
        },
        
        /**
         * Enhance select dropdowns
         */
        enhanceSelects: function() {
            // Add custom styling or functionality to select elements if needed
            $('#pos_page').addClass('wupos-enhanced-select');
        },
        
        /**
         * Show contextual information based on current settings
         */
        showContextualInfo: function() {
            var currentPage = $('#pos_page').val();
            
            if (currentPage && currentPage !== '0') {
                var pageTitle = $('#pos_page').find('option:selected').text();
                this.showPageInfo(currentPage, pageTitle);
            }
        },
        
        /**
         * Show success message
         */
        showSuccessMessage: function(message) {
            var messageHtml = `
                <div class="notice notice-success is-dismissible wupos-admin-notice">
                    <p><strong>WUPOS:</strong> ${message}</p>
                </div>
            `;
            
            $('.wrap h1').after(messageHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $('.wupos-admin-notice').fadeOut();
            }, 5000);
        },
        
        /**
         * Show error message
         */
        showErrorMessage: function(message) {
            var messageHtml = `
                <div class="notice notice-error is-dismissible wupos-admin-notice error">
                    <p><strong>WUPOS Error:</strong> ${message}</p>
                </div>
            `;
            
            $('.wrap h1').after(messageHtml);
        }
    };

    /**
     * Document ready handler
     */
    $(document).ready(function() {
        // Only initialize on WUPOS admin pages
        if ($('.wrap').find('h1:contains("WUPOS")').length > 0 || $('#pos_page').length > 0) {
            WUPOSAdmin.init();
        }
    });

    // Make WUPOSAdmin globally available for debugging
    window.WUPOSAdmin = WUPOSAdmin;

})(jQuery);