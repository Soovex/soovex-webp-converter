<?php
/**
 * Activator class for the plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WebP_CP_Activator {
    
    /**
     * Activate the plugin
     */
    public static function activate() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'webp_cp_activity_log';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            attachment_id mediumint(9) NOT NULL,
            original_image varchar(255) NOT NULL,
            webp_image varchar(255) NOT NULL,
            status varchar(50) NOT NULL,
            date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Set default options
        add_option('webp_cp_enable_backup', 1);
        add_option('webp_cp_backup_reminder', 0);
        add_option('webp_cp_backup_deletion_duration', '30');
        add_option('webp_cp_custom_duration', '');
        add_option('webp_cp_auto_convert', 0); // Disabled by default
        add_option('webp_cp_lazy_load', 0); // Disabled by default
        add_option('webp_cp_compression_quality', 82);
        add_option('webp_cp_serve_webp', 1);
        
        // Flush rewrite rules to add WebP serving rules
        flush_rewrite_rules();
    }
}