<?php
/**
 * Plugin Name: Soovex WebP Converter – Convert Images | Optimize & Compress | Unlimited Conversions
 * Plugin URI: https://soovex.com/
 * Description: Convert media library images (JPG, PNG) to WebP format with automatic backups, modern dashboard, and performance optimization. Significantly reduce file sizes while maintaining excellent image quality.
 * Version: 1.0.2
 * Requires at least: 6.4
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * Author: Mustafijur Rahman
 * Author URI: https://mustafijur.org/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: soovex-webp-converter
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WEBP_CP_VERSION', '1.0.2');
define('WEBP_CP_PATH', plugin_dir_path(__FILE__));
define('WEBP_CP_URL', plugin_dir_url(__FILE__));
define('WEBP_CP_BASENAME', plugin_basename(__FILE__));

// Define conversion limits and batch sizes
define('WEBP_CP_MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB maximum file size
define('WEBP_CP_BATCH_SIZE_SMALL', 3); // Small batch size for progress tracking
define('WEBP_CP_BATCH_SIZE_LARGE', 5); // Large batch size for background processing
define('WEBP_CP_MEMORY_MULTIPLIER', 5); // Memory estimate multiplier (file_size * 5)
define('WEBP_CP_RATE_LIMIT', 20); // Maximum conversions per hour per IP
define('WEBP_CP_DELAY_BETWEEN_BATCHES', 500000); // Microseconds (0.5 seconds)

// Include required files
require_once WEBP_CP_PATH . 'includes/functions.php';
require_once WEBP_CP_PATH . 'includes/class-webp-cp-activator.php';
require_once WEBP_CP_PATH . 'includes/class-webp-cp-deactivator.php';
require_once WEBP_CP_PATH . 'admin/class-webp-cp-admin.php';
require_once WEBP_CP_PATH . 'admin/class-webp-cp-settings.php';
require_once WEBP_CP_PATH . 'admin/class-webp-cp-dashboard.php';
require_once WEBP_CP_PATH . 'admin/class-webp-cp-activity-log.php';
require_once WEBP_CP_PATH . 'admin/class-webp-cp-backup-reminder.php';
require_once WEBP_CP_PATH . 'admin/class-webp-cp-help.php';
require_once WEBP_CP_PATH . 'includes/class-webp-cp-converter.php';
require_once WEBP_CP_PATH . 'includes/class-webp-cp-backup.php';
require_once WEBP_CP_PATH . 'includes/class-webp-cp-lazy-load.php';
require_once WEBP_CP_PATH . 'includes/class-webp-cp-media-library.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('WebP_CP_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('WebP_CP_Deactivator', 'deactivate'));

// Initialize the plugin
function webp_cp_init() {
    if (is_admin()) {
        WebP_CP_Admin::get_instance();
        WebP_CP_Settings::get_instance();
        WebP_CP_Dashboard::get_instance();
        WebP_CP_Activity_Log::get_instance();
        WebP_CP_Backup_Reminder::get_instance();
        WebP_CP_Help::get_instance();
    }
    WebP_CP_Converter::get_instance();
    WebP_CP_Backup::get_instance();
    WebP_CP_Lazy_Load::get_instance();
    WebP_CP_Media_Library::get_instance();
}
add_action('plugins_loaded', 'webp_cp_init');

// Add custom menu icon CSS (hook must be registered separately)
add_action('admin_head', 'webp_cp_custom_menu_icon');

// Register admin menu
function webp_cp_admin_menu() {
    // Main menu page - Dashboard
    add_menu_page(
        __('Soovex WebP Converter', 'soovex-webp-converter'),
        __('Soovex WebP Converter', 'soovex-webp-converter'),
        'manage_options',
        'webp-converter-pro',
        'webp_cp_dashboard_page',
        WEBP_CP_URL . 'assets/icon.svg',
        30
    );
    
    // Dashboard submenu (first item - links to Dashboard page)
    add_submenu_page(
        'webp-converter-pro',
        __('Dashboard', 'soovex-webp-converter'),
        __('Dashboard', 'soovex-webp-converter'),
        'manage_options',
        'webp-converter-pro',
        'webp_cp_dashboard_page'
    );
    
    // Activity submenu (second item - links to Activity Log page)
    add_submenu_page(
        'webp-converter-pro',
        __('Activity Log', 'soovex-webp-converter'),
        __('Activity', 'soovex-webp-converter'),
        'manage_options',
        'webp-converter-pro-activity-log',
        'webp_cp_activity_log_page'
    );
    
    // Settings submenu (third item - links to Settings page)
    add_submenu_page(
        'webp-converter-pro',
        __('Settings', 'soovex-webp-converter'),
        __('Settings', 'soovex-webp-converter'),
        'manage_options',
        'webp-converter-pro-settings',
        'webp_cp_settings_page'
    );
    
    // Help submenu (fourth item - links to Help page)
    add_submenu_page(
        'webp-converter-pro',
        __('Help', 'soovex-webp-converter'),
        __('Help', 'soovex-webp-converter'),
        'manage_options',
        'webp-converter-pro-help',
        'webp_cp_help_page'
    );
}
add_action('admin_menu', 'webp_cp_admin_menu');

// Add custom menu icon CSS
function webp_cp_custom_menu_icon() {
    // Check if we're on the admin page
    if (!is_admin() || !defined('WEBP_CP_PATH') || !defined('WEBP_CP_URL')) {
        return;
    }
    ?>
    <style>
        /* Hide default dashicon ::before */
        #toplevel_page_webp-converter-pro .wp-menu-image::before {
            display: none !important;
            content: '' !important;
        }
        
        /* Style the SVG image container - match WordPress menu icon container */
        #toplevel_page_webp-converter-pro .wp-menu-image {
            width: 20px !important;
            height: 20px !important;
            line-height: 34px !important; /* Match WordPress menu item line-height */
            text-align: center !important;
            font-size: 20px !important;
            display: inline-block !important;
            vertical-align: top !important;
            position: relative !important;
            margin-right: 6px !important; /* Match WordPress default spacing */
            float: left !important; /* Match WordPress layout */
        }
        
        /* Style the SVG image - proper alignment */
        #toplevel_page_webp-converter-pro .wp-menu-image img {
            width: 20px !important;
            height: 20px !important;
            padding: 0 !important;
            margin: 0 !important;
            display: block !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, 10%) !important;
            vertical-align: middle !important;
            opacity: 0.7 !important;
            filter: brightness(0) saturate(100%) invert(74%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(98%) contrast(98%) !important; /* Convert to #a7aaad gray */
            transition: opacity 0.15s ease !important;
        }
        
        /* Hover state - brighter */
        #toplevel_page_webp-converter-pro:hover .wp-menu-image img {
            opacity: 1 !important;
        }
        
        /* Active/current state - white */
        #toplevel_page_webp-converter-pro.wp-has-current-submenu .wp-menu-image img,
        #toplevel_page_webp-converter-pro.current .wp-menu-image img,
        #toplevel_page_webp-converter-pro.wp-has-current-submenu:hover .wp-menu-image img {
            opacity: 1 !important;
            filter: brightness(0) invert(1) !important; /* White for active state */
        }
        
        /* Ensure menu item uses WordPress default layout */
        #toplevel_page_webp-converter-pro a {
            display: block !important;
            padding: 4px 8px !important;
            line-height: 23px !important;
        }
        
        /* Match WordPress menu text styling and spacing */
        #toplevel_page_webp-converter-pro .wp-menu-name {
            margin-left: 0 !important;
            padding-left: 0 !important;
        }
    </style>
    <?php
}

// Dashboard page callback
function webp_cp_dashboard_page() {
    WebP_CP_Dashboard::get_instance()->render_page();
}

// Activity log page callback
function webp_cp_activity_log_page() {
    WebP_CP_Activity_Log::get_instance()->render_page();
}

// Settings page callback
function webp_cp_settings_page() {
    WebP_CP_Settings::get_instance()->render_page();
}

// Help page callback
function webp_cp_help_page() {
    WebP_CP_Help::get_instance()->render_page();
}

// Enqueue admin scripts and styles
function webp_cp_admin_enqueue_scripts($hook) {
    // Only load on our plugin pages
    if (strpos($hook, 'webp-converter-pro') === false) {
        return;
    }
    
    // Tailwind CSS (CDN version loaded as script)
    wp_enqueue_script(
        'webp-cp-tailwindcss',
        WEBP_CP_URL . 'assets/js/tailwindcss.js',
        array(),
        WEBP_CP_VERSION,
        false
    );
    
    // Tailwind config (inline script)
    $tailwind_config = "tailwind.config = {
    darkMode: \"class\",
    theme: {
    extend: {
        colors: {
        \"primary\": \"#1173d4\",
        \"background-light\": \"#f6f7f8\",
        \"background-dark\": \"#101922\",
        },
        fontFamily: {
        \"display\": [\"Inter\"]
        },
        borderRadius: {
        \"DEFAULT\": \"0.25rem\",
        \"lg\": \"0.5rem\",
        \"xl\": \"0.75rem\",
        \"full\": \"9999px\"
        },
    },
    },
}";
    wp_add_inline_script('webp-cp-tailwindcss', $tailwind_config);
    
    // Slider styles (inline style)
    $slider_styles = ".slider-thumb::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 1rem;
    height: 1rem;
    background-color: #1173d4;
    border-radius: 9999px;
    cursor: pointer;
}
.slider-thumb::-moz-range-thumb {
    width: 1rem;
    height: 1rem;
    background-color: #1173d4;
    border-radius: 9999px;
    cursor: pointer;
}";
    
    // Fonts
    wp_enqueue_style(
        'webp-cp-fonts',
        WEBP_CP_URL . 'assets/css/fonts.css',
        array(),
        WEBP_CP_VERSION
    );
    
    // Plugin admin style
    wp_enqueue_style(
        'webp-cp-admin',
        WEBP_CP_URL . 'assets/css/admin.css',
        array(),
        WEBP_CP_VERSION
    );
    
    // Add inline slider styles
    wp_add_inline_style('webp-cp-admin', $slider_styles);
    
    // Alpine.js (with defer attribute)
    wp_enqueue_script(
        'webp-cp-alpine',
        WEBP_CP_URL . 'assets/js/alpine.js',
        array(),
        WEBP_CP_VERSION,
        false
    );
    wp_script_add_data('webp-cp-alpine', 'defer', true);
    
    // Plugin admin script
    wp_enqueue_script(
        'webp-cp-admin',
        WEBP_CP_URL . 'assets/js/admin.js',
        array('jquery'),
        WEBP_CP_VERSION,
        true
    );
    
    // Progress bar script
    wp_enqueue_script(
        'webp-cp-progress-bar',
        WEBP_CP_URL . 'assets/js/progress-bar.js',
        array('jquery'),
        WEBP_CP_VERSION,
        true
    );
    
    // Media uploader scripts
    wp_enqueue_media();
    
    // Localize script
    wp_localize_script('webp-cp-admin', 'webp_cp_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('webp_cp_nonce'),
        'plugin_url' => WEBP_CP_URL,
    ));
    
    // Add page-specific inline scripts based on hook
    // Dashboard page inline script
    if ($hook === 'toplevel_page_webp-converter-pro' || $hook === 'webp-converter-pro_page_webp-converter-pro') {
        $dashboard_script = "jQuery(document).ready(function($) {
        // Load stats on page load
        webp_cp_load_stats();
        
        // Load activity log on page load
        webp_cp_load_activity_log(1, 5); // Load only 5 items for dashboard
        
        // Close popup
        $('.webp-cp-close-popup').on('click', function() {
            $(this).closest('.fixed.inset-0.z-50').hide();
        });
        
        // Close modal
        $('.webp-cp-close-modal').on('click', function() {
            $(this).closest('.fixed.inset-0.z-50').hide();
        });
        
        // Close modal when clicking on backdrop
        $('.webp-cp-convert-single-modal-backdrop, .webp-cp-convert-multiple-modal-backdrop, .webp-cp-convert-url-modal-backdrop, .webp-cp-convert-all-modal-backdrop').on('click', function() {
            $(this).closest('.fixed.inset-0.z-50').hide();
        });
        
        // Open single image modal
        $('.webp-cp-convert-single').on('click', function() {
            $('#webp-cp-convert-single-modal').show();
        });
        
        // Open multiple images modal
        $('.webp-cp-convert-multiple').on('click', function() {
            $('#webp-cp-convert-multiple-modal').show();
        });
        
        // Open URL modal
        $('.webp-cp-convert-url').on('click', function() {
            $('#webp-cp-convert-url-modal').show();
        });
        
        // Open convert all modal
        $('.webp-cp-convert-all').on('click', function() {
            $('#webp-cp-convert-all-modal').show();
        });
        
        // Select single image
        var single_image_frame;
        $('#webp-cp-select-single-image').on('click', function(e) {
            e.preventDefault();
            
            if (single_image_frame) {
                single_image_frame.open();
                return;
            }
            
            single_image_frame = wp.media({
                title: 'Select Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: ['image/jpeg', 'image/jpg', 'image/png']
                }
            });
            
            single_image_frame.on('select', function() {
                var attachment = single_image_frame.state().get('selection').first().toJSON();
                $('#webp-cp-single-image-id').val(attachment.id);
                $('#webp-cp-single-image-name').text(attachment.title);
            });
            
            single_image_frame.open();
        });
        
        // Select multiple images
        var multiple_images_frame;
        $('#webp-cp-select-multiple-images').on('click', function(e) {
            e.preventDefault();
            
            if (multiple_images_frame) {
                multiple_images_frame.open();
                return;
            }
            
            multiple_images_frame = wp.media({
                title: 'Select Images',
                button: {
                    text: 'Use these images'
                },
                multiple: 'add',
                library: {
                    type: ['image/jpeg', 'image/jpg', 'image/png']
                }
            });
            
            multiple_images_frame.on('select', function() {
                var attachments = multiple_images_frame.state().get('selection').toJSON();
                var ids = [];
                $.each(attachments, function(i, attachment) {
                    ids.push(attachment.id);
                });
                $('#webp-cp-multiple-images-ids').val(ids.join(','));
                $('#webp-cp-multiple-images-count').text(ids.length + ' image(s) selected');
            });
            
            multiple_images_frame.open();
        });
        
        // Convert single image
        $('#webp-cp-convert-single-image-btn').on('click', function() {
            var image_id = $('#webp-cp-single-image-id').val();
            
            if (!image_id) {
                $('#webp-cp-failed-title').text('Error');
                $('#webp-cp-failed-message').text('Please select an image to convert.');
                $('#webp-cp-failed-popup').show();
                return;
            }
            
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_convert_single',
                    image_id: image_id,
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#webp-cp-convert-single-modal').hide();
                        $('#webp-cp-success-title').text('Conversion Successful');
                        $('#webp-cp-success-message').text(response.data.message);
                        $('#webp-cp-success-popup').show();
                        webp_cp_load_stats();
                        webp_cp_load_activity_log(1, 5);
                    } else {
                        $('#webp-cp-failed-title').text('Conversion Failed');
                        $('#webp-cp-failed-message').text(response.data.message);
                        $('#webp-cp-failed-popup').show();
                    }
                },
                error: function() {
                    $('#webp-cp-failed-title').text('Error');
                    $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                    $('#webp-cp-failed-popup').show();
                }
            });
        });
        
        // Convert multiple images
        $('#webp-cp-convert-multiple-images-btn').on('click', function() {
            var image_ids = $('#webp-cp-multiple-images-ids').val();
            
            if (!image_ids) {
                $('#webp-cp-failed-title').text('Error');
                $('#webp-cp-failed-message').text('Please select images to convert.');
                $('#webp-cp-failed-popup').show();
                return;
            }
            
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_convert_multiple',
                    image_ids: image_ids,
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#webp-cp-convert-multiple-modal').hide();
                        
                        // Show progress modal for multiple conversions
                        if (typeof webp_cp_show_progress === 'function') {
                            webp_cp_show_progress(response.data.progress_key, response.data.total);
                        }
                        
                        // Start progress polling
                        if (typeof startProgressPolling === 'function') {
                            startProgressPolling();
                        }
                    } else {
                        $('#webp-cp-failed-title').text('Conversion Failed');
                        $('#webp-cp-failed-message').text(response.data.message);
                        $('#webp-cp-failed-popup').show();
                    }
                },
                error: function() {
                    $('#webp-cp-failed-title').text('Error');
                    $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                    $('#webp-cp-failed-popup').show();
                }
            });
        });
        
        // Convert by URL
        $('#webp-cp-convert-url-btn').on('click', function() {
            var image_url = $('#webp-cp-image-url').val();
            
            if (!image_url) {
                $('#webp-cp-failed-title').text('Error');
                $('#webp-cp-failed-message').text('Please enter an image URL.');
                $('#webp-cp-failed-popup').show();
                return;
            }
            
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_convert_url',
                    image_url: image_url,
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#webp-cp-convert-url-modal').hide();
                        $('#webp-cp-success-title').text('Conversion Successful');
                        $('#webp-cp-success-message').text(response.data.message);
                        $('#webp-cp-success-popup').show();
                        webp_cp_load_stats();
                        webp_cp_load_activity_log(1, 5);
                    } else {
                        $('#webp-cp-failed-title').text('Conversion Failed');
                        $('#webp-cp-failed-message').text(response.data.message);
                        $('#webp-cp-failed-popup').show();
                    }
                },
                error: function() {
                    $('#webp-cp-failed-title').text('Error');
                    $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                    $('#webp-cp-failed-popup').show();
                }
            });
        });
        
        // Convert all images
        $('#webp-cp-convert-all-btn').on('click', function() {
            // First try the scheduled conversion
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_convert_all_with_progress',
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#webp-cp-convert-all-modal').hide();
                        
                        // Show progress modal
                        if (typeof webp_cp_show_progress === 'function') {
                            webp_cp_show_progress(response.data.progress_key, response.data.total);
                        }
                        
                        // Start progress polling
                        if (typeof startProgressPolling === 'function') {
                            startProgressPolling();
                        }
                    } else {
                        // If scheduled conversion fails, try immediate conversion
                        convertAllImmediate();
                    }
                },
                error: function() {
                    // If AJAX fails, try immediate conversion
                    convertAllImmediate();
                }
            });
        });
        
        // Fallback function for immediate conversion
        function convertAllImmediate() {
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_convert_all_immediate',
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#webp-cp-convert-all-modal').hide();
                        
                        // Show progress modal
                        if (typeof webp_cp_show_progress === 'function') {
                            webp_cp_show_progress(response.data.progress_key, response.data.total);
                        }
                        
                        // Start progress polling
                        if (typeof startProgressPolling === 'function') {
                            startProgressPolling();
                        }
                    } else {
                        $('#webp-cp-failed-title').text('Conversion Failed');
                        $('#webp-cp-failed-message').text(response.data.message);
                        $('#webp-cp-failed-popup').show();
                    }
                },
                error: function() {
                    $('#webp-cp-failed-title').text('Error');
                    $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                    $('#webp-cp-failed-popup').show();
                }
            });
        }
        
        // Save compression level
        window.saveCompressionLevel = function(level) {
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_save_compression_level',
                    compression_level: level,
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#webp-cp-success-title').text('Settings Saved');
                        $('#webp-cp-success-message').text(response.data.message);
                        $('#webp-cp-success-popup').show();
                    } else {
                        $('#webp-cp-failed-title').text('Error');
                        $('#webp-cp-failed-message').text(response.data.message);
                        $('#webp-cp-failed-popup').show();
                    }
                },
                error: function() {
                    $('#webp-cp-failed-title').text('Error');
                    $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                    $('#webp-cp-failed-popup').show();
                }
            });
        };
        
        // Load stats
        function webp_cp_load_stats() {
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_get_stats',
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#webp-cp-stats-container').html(response.data.stats_html);
                    }
                }
            });
        }
        
        // Load activity log
        function webp_cp_load_activity_log(page, per_page) {
            per_page = per_page || 20;
            
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_get_activity_logs',
                    page: page,
                    per_page: per_page,
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#webp-cp-activity-log-container').html(response.data.logs_html);
                    }
                }
            });
        }
        
        // Revert image
        $(document).on('click', '.webp-cp-revert-image', function() {
            var log_id = $(this).data('log-id');
            
            if (!confirm('Are you sure you want to revert this image to its original format?')) {
                return;
            }
            
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_revert_single',
                    log_id: log_id,
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#webp-cp-success-title').text('Revert Successful');
                        $('#webp-cp-success-message').text(response.data.message);
                        $('#webp-cp-success-popup').show();
                        webp_cp_load_stats();
                        webp_cp_load_activity_log(1, 5);
                    } else {
                        $('#webp-cp-failed-title').text('Revert Failed');
                        $('#webp-cp-failed-message').text(response.data.message);
                        $('#webp-cp-failed-popup').show();
                    }
                },
                error: function() {
                    $('#webp-cp-failed-title').text('Error');
                    $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                    $('#webp-cp-failed-popup').show();
                }
            });
        });
        
        // Retry image
        $(document).on('click', '.webp-cp-retry-image', function() {
            var log_id = $(this).data('log-id');
            
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_retry_single',
                    log_id: log_id,
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#webp-cp-success-title').text('Conversion Successful');
                        $('#webp-cp-success-message').text(response.data.message);
                        $('#webp-cp-success-popup').show();
                        webp_cp_load_stats();
                        webp_cp_load_activity_log(1, 5);
                    } else {
                        $('#webp-cp-failed-title').text('Conversion Failed');
                        $('#webp-cp-failed-message').text(response.data.message);
                        $('#webp-cp-failed-popup').show();
                    }
                },
                error: function() {
                    $('#webp-cp-failed-title').text('Error');
                    $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                    $('#webp-cp-failed-popup').show();
                }
            });
        });
        
        // Global function to show settings modal
        window.webp_cp_show_settings = function() {
            // Trigger the Alpine.js data to open the modal
            var event = new CustomEvent('webp-cp-open-settings');
            document.dispatchEvent(event);
        };
        
        // Listen for the custom event to open settings
        document.addEventListener('webp-cp-open-settings', function() {
            // Find the Alpine.js component and trigger the modal
            var alpineComponent = document.querySelector('[x-data]');
            if (alpineComponent && alpineComponent._x_dataStack && alpineComponent._x_dataStack[0]) {
                alpineComponent._x_dataStack[0].settingsModalOpen = true;
            }
        });
    });
";
        wp_add_inline_script('webp-cp-admin', $dashboard_script);
    }
    
    // Activity log page inline script
    if ($hook === 'webp-converter-pro_page_webp-converter-pro-activity-log' || (isset($_GET['page']) && $_GET['page'] === 'webp-converter-pro-activity-log')) {
        $activity_log_script = "
        // Load activity log function (global scope for pagination)
        window.webp_cp_load_activity_log = function(page) {
            if (typeof webp_cp_vars === 'undefined') {
                return;
            }
            
            jQuery.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_get_activity_logs',
                    page: page || 1,
                    per_page: 20,
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response && response.success) {
                        jQuery('#webp-cp-activity-log-container').html(response.data.logs_html);
                        
                        // Update pagination
                        var pagination_html = '';
                        if (response.data.total_pages > 1) {
                            pagination_html = '<div class=\"flex flex-1 justify-between sm:hidden\">';
                            if (response.data.current_page > 1) {
                                pagination_html += '<button class=\"webp-cp-pagination-prev relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50\">Previous</button>';
                            }
                            if (response.data.current_page < response.data.total_pages) {
                                pagination_html += '<button class=\"webp-cp-pagination-next relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50\">Next</button>';
                            }
                            pagination_html += '</div>';
                            pagination_html += '<div class=\"hidden sm:flex sm:flex-1 sm:items-center sm:justify-between\">';
                            pagination_html += '<div>';
                            pagination_html += '<p class=\"text-sm text-gray-700\">';
                            pagination_html += 'Showing <span class=\"font-medium\">' + ((response.data.current_page - 1) * 20 + 1) + '</span> to <span class=\"font-medium\">' + Math.min(response.data.current_page * 20, response.data.total_logs) + '</span> of <span class=\"font-medium\">' + response.data.total_logs + '</span> results';
                            pagination_html += '</p>';
                            pagination_html += '</div>';
                            pagination_html += '<div>';
                            pagination_html += '<nav class=\"isolate inline-flex -space-x-px rounded-md shadow-sm\" aria-label=\"Pagination\">';
                            
                            if (response.data.current_page > 1) {
                                pagination_html += '<button class=\"webp-cp-pagination-prev relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0\">';
                                pagination_html += '<span class=\"sr-only\">Previous</span>';
                                pagination_html += '<svg class=\"h-5 w-5\" viewBox=\"0 0 20 20\" fill=\"currentColor\" aria-hidden=\"true\">';
                                pagination_html += '<path fill-rule=\"evenodd\" d=\"M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z\" clip-rule=\"evenodd\" />';
                                pagination_html += '</svg>';
                                pagination_html += '</button>';
                            }
                            
                            // Page numbers
                            var start_page = Math.max(1, response.data.current_page - 2);
                            var end_page = Math.min(response.data.total_pages, response.data.current_page + 2);
                            
                            if (start_page > 1) {
                                pagination_html += '<button class=\"webp-cp-pagination-page relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0\" data-page=\"1\">1</button>';
                                if (start_page > 2) {
                                    pagination_html += '<span class=\"relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300\">...</span>';
                                }
                            }
                            
                            for (var i = start_page; i <= end_page; i++) {
                                var active_class = i === response.data.current_page ? 'bg-primary text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0';
                                pagination_html += '<button class=\"webp-cp-pagination-page relative inline-flex items-center px-4 py-2 text-sm font-semibold ' + active_class + '\" data-page=\"' + i + '\">' + i + '</button>';
                            }
                            
                            if (end_page < response.data.total_pages) {
                                if (end_page < response.data.total_pages - 1) {
                                    pagination_html += '<span class=\"relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300\">...</span>';
                                }
                                pagination_html += '<button class=\"webp-cp-pagination-page relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0\" data-page=\"' + response.data.total_pages + '\">' + response.data.total_pages + '</button>';
                            }
                            
                            if (response.data.current_page < response.data.total_pages) {
                                pagination_html += '<button class=\"webp-cp-pagination-next relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0\">';
                                pagination_html += '<span class=\"sr-only\">Next</span>';
                                pagination_html += '<svg class=\"h-5 w-5\" viewBox=\"0 0 20 20\" fill=\"currentColor\" aria-hidden=\"true\">';
                                pagination_html += '<path fill-rule=\"evenodd\" d=\"M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z\" clip-rule=\"evenodd\" />';
                                pagination_html += '</svg>';
                                pagination_html += '</button>';
                            }
                            
                            pagination_html += '</nav>';
                            pagination_html += '</div>';
                            pagination_html += '</div>';
                        }
                        
                        jQuery('#webp-cp-pagination-container').html(pagination_html);
                        
                        // Add click handlers for pagination
                        jQuery('.webp-cp-pagination-prev').on('click', function() {
                            webp_cp_load_activity_log(response.data.current_page - 1);
                        });
                        
                        jQuery('.webp-cp-pagination-next').on('click', function() {
                            webp_cp_load_activity_log(response.data.current_page + 1);
                        });
                        
                        jQuery('.webp-cp-pagination-page').on('click', function() {
                            var page = jQuery(this).data('page');
                            webp_cp_load_activity_log(page);
                        });
                    }
                },
                error: function(xhr, status, error) {
                    jQuery('#webp-cp-activity-log-container').html('<tr><td colspan=\"5\" class=\"px-6 py-4 text-center text-sm text-red-500\">Error loading activity logs. Please refresh the page.</td></tr>');
                }
            });
        }
        
        jQuery(document).ready(function($) {
            // Verify webp_cp_vars is available
            if (typeof webp_cp_vars === 'undefined') {
                return;
            }
            
            // Load activity log on page load
            if (typeof webp_cp_load_activity_log === 'function') {
                webp_cp_load_activity_log(1);
            }
            
            // Close popup
            $('.webp-cp-close-popup').on('click', function() {
                $(this).closest('.fixed.inset-0.z-50').hide();
            });
            
            // Clear logs
            $('#webp-cp-clear-logs').on('click', function() {
                if (!confirm('Are you sure you want to clear all activity logs? This action cannot be undone.')) {
                    return;
                }
                
                $.ajax({
                    url: webp_cp_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'webp_cp_clear_logs',
                        nonce: webp_cp_vars.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#webp-cp-success-title').text('Logs Cleared');
                            $('#webp-cp-success-message').text(response.data.message);
                            $('#webp-cp-success-popup').show();
                            webp_cp_load_activity_log(1);
                        } else {
                            $('#webp-cp-failed-title').text('Error');
                            $('#webp-cp-failed-message').text(response.data.message);
                            $('#webp-cp-failed-popup').show();
                        }
                    },
                    error: function() {
                        $('#webp-cp-failed-title').text('Error');
                        $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                        $('#webp-cp-failed-popup').show();
                    }
                });
            });
            
            // Revert image
            $(document).on('click', '.webp-cp-revert-image', function() {
                var log_id = $(this).data('log-id');
                
                if (!confirm('Are you sure you want to revert this image to its original format?')) {
                    return;
                }
                
                $.ajax({
                    url: webp_cp_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'webp_cp_revert_single',
                        log_id: log_id,
                        nonce: webp_cp_vars.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#webp-cp-success-title').text('Revert Successful');
                            $('#webp-cp-success-message').text(response.data.message);
                            $('#webp-cp-success-popup').show();
                            webp_cp_load_activity_log(1);
                        } else {
                            $('#webp-cp-failed-title').text('Revert Failed');
                            $('#webp-cp-failed-message').text(response.data.message);
                            $('#webp-cp-failed-popup').show();
                        }
                    },
                    error: function() {
                        $('#webp-cp-failed-title').text('Error');
                        $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                        $('#webp-cp-failed-popup').show();
                    }
                });
            });
            
            // Retry image
            $(document).on('click', '.webp-cp-retry-image', function() {
                var log_id = $(this).data('log-id');
                
                $.ajax({
                    url: webp_cp_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'webp_cp_retry_single',
                        log_id: log_id,
                        nonce: webp_cp_vars.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#webp-cp-success-title').text('Conversion Successful');
                            $('#webp-cp-success-message').text(response.data.message);
                            $('#webp-cp-success-popup').show();
                            webp_cp_load_activity_log(1);
                        } else {
                            $('#webp-cp-failed-title').text('Conversion Failed');
                            $('#webp-cp-failed-message').text(response.data.message);
                            $('#webp-cp-failed-popup').show();
                        }
                    },
                    error: function() {
                        $('#webp-cp-failed-title').text('Error');
                        $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                        $('#webp-cp-failed-popup').show();
                    }
                });
            });
        });";
        wp_add_inline_script('webp-cp-admin', $activity_log_script);
    }
    
    // Backup reminder inline script (needed on all plugin pages)
    if (strpos($hook, 'webp-converter-pro') !== false) {
        $backup_reminder_script = "jQuery(document).ready(function($) {
            $('.webp-cp-dismiss-reminder').on('click', function() {
                var notice = $(this).closest('.webp-cp-backup-reminder');
                var nonce = notice.data('nonce');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'webp_cp_dismiss_backup_reminder',
                        nonce: nonce
                    },
                    success: function() {
                        notice.fadeOut();
                    }
                });
            });
            
            $('.webp-cp-open-settings').on('click', function() {
                // Trigger the settings modal
                if (typeof webp_cp_show_settings === 'function') {
                    webp_cp_show_settings();
                } else {
                    // Fallback: redirect to settings page
                    window.location.href = '" . esc_url(admin_url('admin.php?page=webp-converter-pro')) . "';
                }
            });
        });";
        wp_add_inline_script('webp-cp-admin', $backup_reminder_script);
    }
    
    // Settings modal inline script (needed on dashboard and activity log pages)
    if ($hook === 'toplevel_page_webp-converter-pro' || $hook === 'webp-converter-pro_page_webp-converter-pro' || $hook === 'webp-converter-pro_page_webp-converter-pro-activity-log') {
        $settings_modal_script = "// Toggle custom duration input
        function toggleCustomDuration() {
            const durationSelect = document.getElementById('deletion-duration');
            const customContainer = document.getElementById('custom-duration-container');
            
            if (durationSelect.value === 'custom') {
                customContainer.style.display = 'block';
            } else {
                customContainer.style.display = 'none';
            }
        }
        
        // Initialize custom duration visibility on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleCustomDuration();
        });

        jQuery(document).ready(function($) {
            // Handle backup dependency for auto-convert
            $('#backup').on('change', function() {
                if (!$(this).is(':checked')) {
                    $('#auto-convert').prop('checked', false);
                }
            });
            
            // Save settings
            $('.webp-cp-save-settings').on('click', function() {
                var settings = {};
                
                // Collect form data
                $(this).closest('.fixed.inset-0.z-50').find('input, select').each(function() {
                    var name = $(this).attr('name');
                    var value = $(this).val();
                    
                    if ($(this).attr('type') === 'checkbox') {
                        value = $(this).is(':checked') ? 1 : 0;
                    }
                    
                    if (name) {
                        settings[name] = value;
                    }
                });
                
                // Ensure auto-convert is disabled if backup is disabled
                if (!settings.enable_backup) {
                    settings.auto_convert = 0;
                }
                
                $.ajax({
                    url: webp_cp_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'webp_cp_save_settings',
                        nonce: webp_cp_vars.nonce,
                        enable_backup: settings.enable_backup,
                        backup_reminder: settings.backup_reminder,
                        backup_deletion_duration: settings.backup_deletion_duration,
                        custom_duration: settings.custom_duration,
                        auto_convert: settings.auto_convert,
                        lazy_load: settings.lazy_load,
                        compression_quality: settings.compression_quality,
                        serve_webp: settings.serve_webp
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            $('#webp-cp-success-title').text('Settings Saved');
                            $('#webp-cp-success-message').text(response.data.message);
                            $('#webp-cp-success-popup').show();
                            // Don't close settings modal
                        } else {
                            $('#webp-cp-failed-title').text('Settings Not Saved');
                            $('#webp-cp-failed-message').text(response.data.message);
                            $('#webp-cp-failed-popup').show();
                        }
                    },
                    error: function() {
                        $('#webp-cp-failed-title').text('Error');
                        $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                        $('#webp-cp-failed-popup').show();
                    }
                });
            });
            
            // Revert all images
            $('.webp-cp-revert-all').on('click', function() {
                if (!confirm('Are you sure you want to revert all converted images back to their original format? This action cannot be undone.')) {
                    return;
                }
                
                $.ajax({
                    url: webp_cp_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'webp_cp_revert_all',
                        nonce: webp_cp_vars.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#webp-cp-success-title').text('Revert Successful');
                            $('#webp-cp-success-message').text(response.data.message);
                            $('#webp-cp-success-popup').show();
                            // Close settings modal
                            $('[x-show=\"settingsModalOpen\"]').hide();
                            // Reload page to update stats
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            $('#webp-cp-failed-title').text('Revert Failed');
                            $('#webp-cp-failed-message').text(response.data.message);
                            $('#webp-cp-failed-popup').show();
                        }
                    },
                    error: function() {
                        $('#webp-cp-failed-title').text('Error');
                        $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                        $('#webp-cp-failed-popup').show();
                    }
                });
            });
            
            // Reset everything
            $('.webp-cp-reset-everything').on('click', function() {
                if (!confirm('Are you sure you want to reset everything? This will:\\n\\n1. Revert all converted images\\n2. Clear all activity logs\\n3. Reset all settings to default\\n4. Delete all backup files\\n\\nThis action cannot be undone!')) {
                    return;
                }
                
                $.ajax({
                    url: webp_cp_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'webp_cp_reset_everything',
                        nonce: webp_cp_vars.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#webp-cp-success-title').text('Reset Successful');
                            $('#webp-cp-success-message').text(response.data.message);
                            $('#webp-cp-success-popup').show();
                            // Close settings modal
                            $('[x-show=\"settingsModalOpen\"]').hide();
                            // Reload page to update stats
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            $('#webp-cp-failed-title').text('Reset Failed');
                            $('#webp-cp-failed-message').text(response.data.message);
                            $('#webp-cp-failed-popup').show();
                        }
                    },
                    error: function() {
                        $('#webp-cp-failed-title').text('Error');
                        $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                        $('#webp-cp-failed-popup').show();
                    }
                });
            });
            
            // Save compression quality
            window.saveCompressionQuality = function(compressionQuality) {
                $.ajax({
                    url: webp_cp_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'webp_cp_save_compression_level',
                        compression_level: compressionQuality,
                        nonce: webp_cp_vars.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Settings saved successfully
                        }
                    }
                });
            };
        });";
        wp_add_inline_script('webp-cp-admin', $settings_modal_script);
    }
}
add_action('admin_enqueue_scripts', 'webp_cp_admin_enqueue_scripts');

// Register AJAX handlers
require_once WEBP_CP_PATH . 'includes/ajax-handlers.php';

// Add query vars for WebP serving
add_filter('query_vars', 'webp_cp_add_query_vars');
function webp_cp_add_query_vars($vars) {
    $vars[] = 'webp_cp_serve';
    $vars[] = 'webp_cp_file';
    return $vars;
}

// Add .htaccess rules for WebP redirection
add_action('init', 'webp_cp_add_htaccess_rules');
function webp_cp_add_htaccess_rules() {
    // Only add rules if WebP serving is enabled
    if (!get_option('webp_cp_serve_webp', 1)) {
        return;
    }
    
    // Get upload directory
    $upload_dir = wp_upload_dir();
    $upload_path = $upload_dir['basedir'];
    
    // Create .htaccess file in uploads directory
    $htaccess_file = $upload_path . '/.htaccess';
    $htaccess_content = '';
    
    // Check if .htaccess already exists
    if (file_exists($htaccess_file)) {
        $htaccess_content = file_get_contents($htaccess_file);
        if ($htaccess_content === false) {
            $htaccess_content = '';
        }
    }
    
    // Check if our rules are already present
    if (strpos($htaccess_content, '# WebP Converter Pro Rules') === false) {
        $webp_rules = "\n# WebP Converter Pro Rules\n";
        $webp_rules .= "<IfModule mod_rewrite.c>\n";
        $webp_rules .= "RewriteEngine On\n";
        $webp_rules .= "RewriteCond %{HTTP_ACCEPT} image/webp\n";
        $webp_rules .= "RewriteCond %{REQUEST_FILENAME} \\.(jpg|jpeg|png)$\n";
        $webp_rules .= "RewriteCond %{REQUEST_FILENAME}\\.webp -f\n";
        $webp_rules .= "RewriteRule ^(.*)\\.(jpg|jpeg|png)$ $1.$2.webp [T=image/webp,E=accept:1,L]\n";
        $webp_rules .= "</IfModule>\n";
        $webp_rules .= "<IfModule mod_headers.c>\n";
        $webp_rules .= "Header append Vary Accept env=REDIRECT_accept\n";
        $webp_rules .= "</IfModule>\n";
        
        // Append rules to .htaccess
        $result = file_put_contents($htaccess_file, $htaccess_content . $webp_rules);
        if ($result === false) {
            // Failed to write .htaccess file, but don't break the plugin
            // This is a non-critical error
        }
    }
}