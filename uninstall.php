<?php
/**
 * Uninstall script for Soovex WebP Converter
 * 
 * This file is executed when the plugin is uninstalled (deleted).
 * It cleans up all plugin data from the database and file system.
 */

// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check user capabilities
if (!current_user_can('delete_plugins')) {
    exit;
}

// Remove plugin options
$options_to_remove = array(
    'webp_cp_enable_backup',
    'webp_cp_backup_reminder',
    'webp_cp_backup_deletion_duration',
    'webp_cp_custom_duration',
    'webp_cp_auto_convert',
    'webp_cp_lazy_load',
    'webp_cp_compression_quality',
    'webp_cp_serve_webp',
);

foreach ($options_to_remove as $option) {
    delete_option($option);
}

// Remove activity log table
global $wpdb;
$table_name = $wpdb->prefix . 'webp_cp_activity_log';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Clean up backup files
$upload_dir = wp_upload_dir();
$backup_dir = $upload_dir['basedir'] . '/webp-cp-backups';

if (is_dir($backup_dir)) {
    // Get all files in backup directory
    $files = glob($backup_dir . '/*');
    
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    
    // Remove backup directory
    rmdir($backup_dir);
}

// Clean up .htaccess rules
$htaccess_file = $upload_dir['basedir'] . '/.htaccess';

if (file_exists($htaccess_file)) {
    $htaccess_content = file_get_contents($htaccess_file);
    
    if ($htaccess_content !== false) {
        // Remove WebP Converter Pro rules
        $htaccess_content = preg_replace('/\n# WebP Converter Pro Rules.*?<\/IfModule>\n/s', '', $htaccess_content);
        
        // Write back the cleaned .htaccess
        file_put_contents($htaccess_file, $htaccess_content);
    }
}

// Clear any scheduled events
wp_clear_scheduled_hook('webp_cp_auto_convert_attachment');

// Flush rewrite rules
flush_rewrite_rules();
