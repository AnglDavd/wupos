/**
 * WUPOS - Admin JavaScript
 *
 * @package WUPOS
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Admin Application Class
     */
    class WUPOSAdmin {
        constructor() {
            this.init();
        }

        /**
         * Initialize the admin application
         */
        init() {
            this.bindEvents();
            this.initializeComponents();
        }

        /**
         * Bind event listeners
         */
        bindEvents() {
            // Settings form
            $('.wupos-settings-form').on('submit', this.handleSettingsSubmit.bind(this));
            
            // Color picker
            $('.color-picker').wpColorPicker();
            
            // Media uploader
            $('.upload-button').on('click', this.openMediaUploader.bind(this));
            
            // Test connection buttons
            $('.test-connection').on('click', this.testConnection.bind(this));
            
            // Import/Export
            $('#export-settings').on('click', this.exportSettings.bind(this));
            $('#import-settings').on('change', this.importSettings.bind(this));
            
            // Tabs
            $('.nav-tab').on('click', this.switchTab.bind(this));
        }

        /**
         * Initialize components
         */
        initializeComponents() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Initialize sortable elements
            if ($.fn.sortable) {
                $('.sortable').sortable({
                    handle: '.sort-handle',
                    update: this.handleSortUpdate.bind(this)
                });
            }
            
            // Load dashboard widgets
            this.loadDashboardData();
        }

        /**
         * Handle settings form submission
         */
        handleSettingsSubmit(e) {
            e.preventDefault();
            
            const form = $(e.currentTarget);
            const formData = new FormData(form[0]);
            
            // Show loading state
            form.find('.submit-button').prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: form.attr('action') || ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        this.showNotice('Settings saved successfully!', 'success');
                    } else {
                        this.showNotice(response.data || 'Failed to save settings.', 'error');
                    }
                },
                error: () => {
                    this.showNotice('An error occurred while saving settings.', 'error');
                },
                complete: () => {
                    form.find('.submit-button').prop('disabled', false).text('Save Settings');
                }
            });
        }

        /**
         * Open media uploader
         */
        openMediaUploader(e) {
            e.preventDefault();
            
            const button = $(e.currentTarget);
            const targetInput = button.siblings('input[type="text"]');
            const preview = button.siblings('.image-preview');
            
            // Create media frame
            const frame = wp.media({
                title: 'Select Image',
                button: { text: 'Use Image' },
                multiple: false,
                library: { type: 'image' }
            });
            
            frame.on('select', function() {
                const attachment = frame.state().get('selection').first().toJSON();
                targetInput.val(attachment.url);
                
                if (preview.length) {
                    preview.html('<img src="' + attachment.url + '" style="max-width: 200px; height: auto;">');
                }
            });
            
            frame.open();
        }

        /**
         * Test connection
         */
        testConnection(e) {
            e.preventDefault();
            
            const button = $(e.currentTarget);
            const type = button.data('type');
            const originalText = button.text();
            
            button.prop('disabled', true).text('Testing...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wupos_test_connection',
                    type: type,
                    nonce: wupos_admin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotice('Connection successful!', 'success');
                        button.addClass('button-primary').removeClass('button-secondary');
                    } else {
                        this.showNotice(response.data || 'Connection failed.', 'error');
                        button.addClass('button-secondary').removeClass('button-primary');
                    }
                },
                error: () => {
                    this.showNotice('Connection test failed.', 'error');
                    button.addClass('button-secondary').removeClass('button-primary');
                },
                complete: () => {
                    button.prop('disabled', false).text(originalText);
                }
            });
        }

        /**
         * Export settings
         */
        exportSettings(e) {
            e.preventDefault();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wupos_export_settings',
                    nonce: wupos_admin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        // Create download link
                        const blob = new Blob([JSON.stringify(response.data, null, 2)], {
                            type: 'application/json'
                        });
                        const url = URL.createObjectURL(blob);
                        
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'wupos-settings-' + new Date().toISOString().split('T')[0] + '.json';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                        
                        this.showNotice('Settings exported successfully!', 'success');
                    } else {
                        this.showNotice(response.data || 'Export failed.', 'error');
                    }
                },
                error: () => {
                    this.showNotice('Export failed.', 'error');
                }
            });
        }

        /**
         * Import settings
         */
        importSettings(e) {
            const file = e.target.files[0];
            
            if (!file) {
                return;
            }
            
            if (file.type !== 'application/json') {
                this.showNotice('Please select a valid JSON file.', 'error');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = (event) => {
                try {
                    const settings = JSON.parse(event.target.result);
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wupos_import_settings',
                            settings: JSON.stringify(settings),
                            nonce: wupos_admin.nonce
                        },
                        success: (response) => {
                            if (response.success) {
                                this.showNotice('Settings imported successfully! Page will reload.', 'success');
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            } else {
                                this.showNotice(response.data || 'Import failed.', 'error');
                            }
                        },
                        error: () => {
                            this.showNotice('Import failed.', 'error');
                        }
                    });
                } catch (error) {
                    this.showNotice('Invalid JSON file.', 'error');
                }
            };
            
            reader.readAsText(file);
        }

        /**
         * Switch tab
         */
        switchTab(e) {
            e.preventDefault();
            
            const tab = $(e.currentTarget);
            const tabId = tab.attr('href');
            
            // Update active tab
            $('.nav-tab').removeClass('nav-tab-active');
            tab.addClass('nav-tab-active');
            
            // Show corresponding content
            $('.tab-content').hide();
            $(tabId).show();
            
            // Update URL hash
            history.replaceState(null, null, tabId);
        }

        /**
         * Handle sortable update
         */
        handleSortUpdate(event, ui) {
            const order = $(event.target).sortable('toArray');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wupos_update_sort_order',
                    order: order,
                    nonce: wupos_admin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotice('Order updated successfully!', 'success');
                    }
                }
            });
        }

        /**
         * Load dashboard data
         */
        loadDashboardData() {
            if (!$('.wupos-dashboard').length) {
                return;
            }

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wupos_get_dashboard_data',
                    nonce: wupos_admin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateDashboardWidgets(response.data);
                    }
                },
                error: () => {
                    console.error('Failed to load dashboard data');
                }
            });
        }

        /**
         * Update dashboard widgets
         */
        updateDashboardWidgets(data) {
            // Update sales stats
            if (data.sales) {
                $('.today-sales .stat-value').text(data.sales.today || '0');
                $('.weekly-sales .stat-value').text(data.sales.week || '0');
                $('.monthly-sales .stat-value').text(data.sales.month || '0');
            }

            // Update order stats
            if (data.orders) {
                $('.pending-orders .stat-value').text(data.orders.pending || '0');
                $('.processing-orders .stat-value').text(data.orders.processing || '0');
                $('.completed-orders .stat-value').text(data.orders.completed || '0');
            }

            // Update charts
            if (data.charts) {
                this.renderCharts(data.charts);
            }

            // Update recent activity
            if (data.activity) {
                this.renderRecentActivity(data.activity);
            }
        }

        /**
         * Render charts
         */
        renderCharts(chartData) {
            // This would integrate with Chart.js or similar library
            console.log('Chart data:', chartData);
        }

        /**
         * Render recent activity
         */
        renderRecentActivity(activities) {
            const container = $('.recent-activity-list');
            container.empty();

            activities.forEach(activity => {
                const activityHtml = `
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="dashicons dashicons-${activity.icon}"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">${activity.title}</div>
                            <div class="activity-time">${activity.time}</div>
                        </div>
                    </div>
                `;
                container.append(activityHtml);
            });
        }

        /**
         * Show admin notice
         */
        showNotice(message, type = 'info') {
            const noticeClass = type === 'error' ? 'notice-error' : 'notice-success';
            const notice = $(`
                <div class="notice ${noticeClass} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `);

            // Add to notices area
            $('.wrap h1').after(notice);

            // Auto dismiss after 5 seconds
            setTimeout(() => {
                notice.fadeOut(() => {
                    notice.remove();
                });
            }, 5000);

            // Handle dismiss button
            notice.on('click', '.notice-dismiss', function() {
                notice.fadeOut(() => {
                    notice.remove();
                });
            });
        }

        /**
         * Initialize data tables
         */
        initializeDataTables() {
            if ($.fn.DataTable) {
                $('.wupos-data-table').DataTable({
                    responsive: true,
                    pageLength: 25,
                    order: [[0, 'desc']],
                    language: {
                        search: 'Search:',
                        lengthMenu: 'Show _MENU_ entries',
                        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                        paginate: {
                            first: 'First',
                            last: 'Last',
                            next: 'Next',
                            previous: 'Previous'
                        }
                    }
                });
            }
        }
    }

    // Initialize the admin application when document is ready
    $(document).ready(function() {
        window.WUPOSAdmin = new WUPOSAdmin();
    });

    // Handle tab activation from URL hash
    $(window).on('load', function() {
        if (location.hash && $(location.hash).length) {
            $('.nav-tab[href="' + location.hash + '"]').trigger('click');
        }
    });

})(jQuery);