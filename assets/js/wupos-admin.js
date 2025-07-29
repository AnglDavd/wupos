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
            
            // Copy shortcode button
            $(document).on('click', '#copy-shortcode', this.copyShortcode);
            
            // Create POS page button
            $(document).on('click', '#create-pos-page', this.createPosPage);
            
            // Auto-generate slug from title
            $(document).on('input', '#pos_page_title', this.generateSlug);
            
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
        },
        
        /**
         * Copy shortcode to clipboard
         */
        copyShortcode: function(e) {
            e.preventDefault();
            
            var shortcodeInput = $('#shortcode-text');
            var feedbackSpan = $('#copy-feedback');
            
            // Select the shortcode text
            shortcodeInput.select();
            shortcodeInput[0].setSelectionRange(0, 99999); // For mobile devices
            
            try {
                // Copy to clipboard
                var successful = document.execCommand('copy');
                
                if (successful) {
                    feedbackSpan.removeClass('error').addClass('success')
                        .text(wupos_admin_ajax.messages.copied);
                } else {
                    throw new Error('Copy command failed');
                }
            } catch (err) {
                // Fallback for modern browsers
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(shortcodeInput.val()).then(function() {
                        feedbackSpan.removeClass('error').addClass('success')
                            .text(wupos_admin_ajax.messages.copied);
                    }).catch(function() {
                        feedbackSpan.removeClass('success').addClass('error')
                            .text(wupos_admin_ajax.messages.copy_error);
                    });
                } else {
                    feedbackSpan.removeClass('success').addClass('error')
                        .text(wupos_admin_ajax.messages.copy_error);
                }
            }
            
            // Clear feedback after 3 seconds
            setTimeout(function() {
                feedbackSpan.removeClass('success error').text('');
            }, 3000);
        },
        
        /**
         * Create new POS page via AJAX
         */
        createPosPage: function(e) {
            e.preventDefault();
            
            var titleInput = $('#pos_page_title');
            var slugInput = $('#pos_page_slug');
            var createButton = $('#create-pos-page');
            var statusSpan = $('#create-page-status');
            
            var pageTitle = titleInput.val().trim();
            var pageSlug = slugInput.val().trim();
            
            // Validate inputs
            if (!pageTitle) {
                statusSpan.removeClass('success loading').addClass('error')
                    .text(wupos_admin_ajax.messages.title_required);
                titleInput.focus();
                return;
            }
            
            if (!pageSlug) {
                statusSpan.removeClass('success loading').addClass('error')
                    .text(wupos_admin_ajax.messages.slug_required);
                slugInput.focus();
                return;
            }
            
            // Show loading state
            createButton.prop('disabled', true);
            statusSpan.removeClass('success error').addClass('loading')
                .text(wupos_admin_ajax.messages.creating);
            
            // AJAX request
            $.ajax({
                url: wupos_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wupos_create_pos_page',
                    nonce: wupos_admin_ajax.nonce,
                    page_title: pageTitle,
                    page_slug: pageSlug
                },
                success: function(response) {
                    createButton.prop('disabled', false);
                    
                    if (response.success) {
                        statusSpan.removeClass('error loading').addClass('success')
                            .text(response.data.message);
                        
                        // Update the page dropdown
                        $('#pos_page').html(response.data.pages_html);
                        
                        // Clear the form
                        titleInput.val('');
                        slugInput.val('');
                        
                        // Show success message
                        WUPOSAdmin.showSuccessMessage(response.data.message + ' <a href="' + response.data.page_url + '" target="_blank">View Page</a>');
                        
                        // Clear status after 5 seconds
                        setTimeout(function() {
                            statusSpan.removeClass('success').text('');
                        }, 5000);
                    } else {
                        statusSpan.removeClass('success loading').addClass('error')
                            .text(response.data || wupos_admin_ajax.messages.error);
                    }
                },
                error: function(xhr, status, error) {
                    createButton.prop('disabled', false);
                    statusSpan.removeClass('success loading').addClass('error')
                        .text(wupos_admin_ajax.messages.error + ': ' + error);
                }
            });
        },
        
        /**
         * Generate slug from title
         */
        generateSlug: function() {
            var title = $(this).val();
            var slug = title
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
                .replace(/\s+/g, '-') // Replace spaces with hyphens
                .replace(/-+/g, '-') // Replace multiple hyphens with single
                .trim('-'); // Remove leading/trailing hyphens
            
            $('#pos_page_slug').val(slug);
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
    
    // Global functions for backward compatibility
    window.copyShortcode = function() {
        WUPOSAdmin.copyShortcode({ preventDefault: function() {} });
    };
    
    window.createPosPage = function() {
        WUPOSAdmin.createPosPage({ preventDefault: function() {} });
    };

    // Make WUPOSAdmin globally available for debugging
    window.WUPOSAdmin = WUPOSAdmin;

})(jQuery);