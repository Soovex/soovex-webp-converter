<?php
/**
 * Deactivator class for the plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WebP_CP_Deactivator {
    
    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Clear scheduled hooks
        wp_clear_scheduled_hook('webp_cp_backup_reminder_cron');
        wp_clear_scheduled_hook('webp_cp_convert_batch');
        
        // Clean up orphaned WebP files
        webp_cp_cleanup_orphaned_webp();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}