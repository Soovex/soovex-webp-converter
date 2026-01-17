<?php
/**
 * Settings class for the plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WebP_CP_Settings {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add AJAX handler for saving settings
        add_action('wp_ajax_webp_cp_save_settings', array($this, 'save_settings'));
        
        // Add cron job for backup reminder
        add_action('webp_cp_backup_reminder_cron', array($this, 'backup_reminder_cron'));
        
        // Schedule backup reminder if enabled
        if (get_option('webp_cp_backup_reminder', 0)) {
            if (!wp_next_scheduled('webp_cp_backup_reminder_cron')) {
                wp_schedule_event(time(), 'daily', 'webp_cp_backup_reminder_cron');
            }
        } else {
            // Clear scheduled hook if disabled
            $timestamp = wp_next_scheduled('webp_cp_backup_reminder_cron');
            if ($timestamp) {
                wp_unschedule_event($timestamp, 'webp_cp_backup_reminder_cron');
            }
        }
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('webp_cp_settings', 'webp_cp_enable_backup', array('sanitize_callback' => 'absint'));
        register_setting('webp_cp_settings', 'webp_cp_backup_reminder', array('sanitize_callback' => 'absint'));
        register_setting('webp_cp_settings', 'webp_cp_backup_deletion_duration', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('webp_cp_settings', 'webp_cp_backup_deletion_date', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('webp_cp_settings', 'webp_cp_auto_convert', array('sanitize_callback' => 'absint'));
        register_setting('webp_cp_settings', 'webp_cp_lazy_load', array('sanitize_callback' => 'absint'));
        register_setting('webp_cp_settings', 'webp_cp_compression_quality', array('sanitize_callback' => 'absint'));
        register_setting('webp_cp_settings', 'webp_cp_serve_webp', array('sanitize_callback' => 'absint'));
    }
    
    /**
     * Get settings
     */
    public function get_settings() {
        $deletion_duration = get_option('webp_cp_backup_deletion_duration', '30');
        $custom_duration = get_option('webp_cp_custom_duration', '');
        
        // Check if the current duration is a custom value (not in predefined list)
        $predefined_durations = array('7', '14', '30', '60', '90', '180', '365', 'Never');
        if (!in_array($deletion_duration, $predefined_durations)) {
            // This is a custom duration, set the select to 'custom' and store the value
            $custom_duration = $deletion_duration;
            $deletion_duration = 'custom';
        }
        
        return array(
            'enable_backup' => get_option('webp_cp_enable_backup', 1),
            'backup_reminder' => get_option('webp_cp_backup_reminder', 0),
            'backup_deletion_duration' => $deletion_duration,
            'custom_duration' => $custom_duration,
            'auto_convert' => get_option('webp_cp_auto_convert', 0), // Disabled by default
            'lazy_load' => get_option('webp_cp_lazy_load', 0), // Disabled by default
            'compression_quality' => get_option('webp_cp_compression_quality', 82),
            'serve_webp' => get_option('webp_cp_serve_webp', 1),
        );
    }
    
    /**
     * Save settings
     */
    public function save_settings() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        }
        
        // Save settings
        // Handle custom duration
        $deletion_duration = sanitize_text_field($_POST['backup_deletion_duration']);
        $custom_duration = '';
        
        if ($deletion_duration === 'custom') {
            $custom_duration = intval($_POST['custom_duration']);
            if ($custom_duration > 0 && $custom_duration <= 3650) {
                $deletion_duration = $custom_duration;
            } else {
                $deletion_duration = '30'; // Default to 30 days if invalid
                $custom_duration = '';
            }
        }
        
        $settings = array(
            'webp_cp_enable_backup' => isset($_POST['enable_backup']) && $_POST['enable_backup'] ? 1 : 0,
            'webp_cp_backup_reminder' => isset($_POST['backup_reminder']) && $_POST['backup_reminder'] ? 1 : 0,
            'webp_cp_backup_deletion_duration' => $deletion_duration,
            'webp_cp_custom_duration' => sanitize_text_field($_POST['custom_duration']),
            'webp_cp_auto_convert' => isset($_POST['auto_convert']) && $_POST['auto_convert'] ? 1 : 0,
            'webp_cp_lazy_load' => isset($_POST['lazy_load']) && $_POST['lazy_load'] ? 1 : 0,
            'webp_cp_compression_quality' => intval($_POST['compression_quality']),
            'webp_cp_serve_webp' => isset($_POST['serve_webp']) && $_POST['serve_webp'] ? 1 : 0,
        );
        
        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        
        // Reset backup deletion date when duration changes (Bug #3 - Input validation)
        if ($deletion_duration !== 'Never' && $deletion_duration !== 'custom') {
            // Validate that deletion_duration is numeric or a valid predefined value
            $predefined_durations = array('7', '14', '30', '60', '90', '180', '365');
            if (in_array($deletion_duration, $predefined_durations) || (is_numeric($deletion_duration) && intval($deletion_duration) > 0 && intval($deletion_duration) <= 3650)) {
                $new_deletion_date = date('Y-m-d', strtotime('+' . intval($deletion_duration) . ' days'));
                if ($new_deletion_date !== false) {
                    update_option('webp_cp_backup_deletion_date', $new_deletion_date);
                }
            }
        }
        
        // Schedule or unschedule backup reminder cron job
        if ($settings['webp_cp_backup_reminder']) {
            if (!wp_next_scheduled('webp_cp_backup_reminder_cron')) {
                wp_schedule_event(time(), 'daily', 'webp_cp_backup_reminder_cron');
            }
        } else {
            $timestamp = wp_next_scheduled('webp_cp_backup_reminder_cron');
            if ($timestamp) {
                wp_unschedule_event($timestamp, 'webp_cp_backup_reminder_cron');
            }
        }
        
        wp_send_json_success(array('message' => 'Settings saved successfully.'));
    }
    
    /**
     * Backup reminder cron job (legacy - now handled by WebP_CP_Backup_Reminder)
     */
    public function backup_reminder_cron() {
        // This method is kept for backward compatibility
        // The actual backup reminder logic is now handled by WebP_CP_Backup_Reminder class
        WebP_CP_Backup_Reminder::get_instance()->check_backup_reminders();
    }
    
    /**
     * Render settings modal
     */
    public function render_settings_modal() {
        include WEBP_CP_PATH . 'admin/templates/settings-modal.php';
    }
    
    /**
     * Render settings page
     */
    public function render_page() {
        include WEBP_CP_PATH . 'admin/templates/settings.php';
    }
}