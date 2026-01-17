<?php
/**
 * Backup class for the plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WebP_CP_Backup {
    
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
        // Add backup deletion hook
        add_action('webp_cp_delete_backup', array($this, 'delete_backup'));
    }
    
    /**
     * Create backup
     */
    public function create_backup($attachment_id) {
        // Get attachment path
        $attachment_path = get_attached_file($attachment_id);
        
        // Check if the attachment exists
        if (!file_exists($attachment_path)) {
            return false;
        }
        
        // Get backup directory
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/webp-cp-backups';
        
        // Create backup directory if it doesn't exist
        if (!file_exists($backup_dir)) {
            wp_mkdir_p($backup_dir);
        }
        
        // Get backup file path (sanitize filename for security)
        $safe_filename = sanitize_file_name(basename($attachment_path));
        $backup_file_path = $backup_dir . '/' . $safe_filename;
        
        // Check if backup already exists
        if (file_exists($backup_file_path)) {
            // Backup already exists, check if it's the same file
            if (filesize($attachment_path) === filesize($backup_file_path) && 
                md5_file($attachment_path) === md5_file($backup_file_path)) {
                return true; // Backup already exists and is identical
            } else {
                // Backup exists but is different, remove old backup
                unlink($backup_file_path);
            }
        }
        
        // Copy file to backup directory with error handling
        $copy_attempts = 0;
        $max_attempts = 3;
        
        while ($copy_attempts < $max_attempts) {
            if (copy($attachment_path, $backup_file_path)) {
                break; // Success
            }
            
            $copy_attempts++;
            if ($copy_attempts >= $max_attempts) {
                return false;
            }
            
            // Wait a bit before retry
            usleep(100000); // 0.1 seconds
        }
        
        // Log successful backup creation
        
        // Get attachment metadata
        $metadata = wp_get_attachment_metadata($attachment_id);
        
        // Backup size images
        if (isset($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size => $size_data) {
                // Get size file path
                $size_path = pathinfo($attachment_path, PATHINFO_DIRNAME) . '/' . $size_data['file'];
                
                // Get size backup file path
                $size_backup_file_path = $backup_dir . '/' . $size_data['file'];
                
                // Copy file to backup directory
                if (file_exists($size_path)) {
                    // Check if size backup already exists
                    if (file_exists($size_backup_file_path)) {
                        // Remove old size backup
                        if (!unlink($size_backup_file_path)) {
                        }
                    }
                    if (!copy($size_path, $size_backup_file_path)) {
                    }
                }
            }
        }
        
        // Schedule backup deletion if configured
        $this->schedule_backup_deletion($attachment_id);
        
        return true;
    }
    
    /**
     * Schedule backup deletion based on settings
     */
    private function schedule_backup_deletion($attachment_id) {
        $deletion_duration = get_option('webp_cp_backup_deletion_duration', '30');
        $deletion_date = get_option('webp_cp_backup_deletion_date', '');
        
        // Skip if deletion is set to "Never"
        if ($deletion_duration === 'Never') {
            return;
        }
        
        $deletion_timestamp = 0;
        
        // Calculate deletion timestamp based on duration
        if (is_numeric($deletion_duration)) {
            $deletion_timestamp = time() + ($deletion_duration * DAY_IN_SECONDS);
        }
        
        // Use specific date if provided
        if (!empty($deletion_date)) {
            $date_timestamp = strtotime($deletion_date);
            if ($date_timestamp && $date_timestamp > time()) {
                $deletion_timestamp = $date_timestamp;
            }
        }
        
        // Schedule deletion if we have a valid timestamp
        if ($deletion_timestamp > 0) {
            wp_schedule_single_event($deletion_timestamp, 'webp_cp_delete_backup', array($attachment_id));
        }
    }
    
    /**
     * Restore backup
     */
    public function restore_backup($attachment_id) {
        // Get attachment path
        $attachment_path = get_attached_file($attachment_id);
        
        // Check if the attachment exists
        if (!file_exists($attachment_path)) {
            return false;
        }
        
        // Get backup directory
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/webp-cp-backups';
        
        // Get backup file path
        $backup_file_path = $backup_dir . '/' . basename($attachment_path);
        
        // Check if the backup exists
        if (!file_exists($backup_file_path)) {
            return false;
        }
        
        // Restore file from backup
        if (!copy($backup_file_path, $attachment_path)) {
            return false;
        }
        
        // Get attachment metadata
        $metadata = wp_get_attachment_metadata($attachment_id);
        
        // Restore size images
        if (isset($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size => $size_data) {
                // Get size file path
                $size_path = pathinfo($attachment_path, PATHINFO_DIRNAME) . '/' . $size_data['file'];
                
                // Get size backup file path
                $size_backup_file_path = $backup_dir . '/' . $size_data['file'];
                
                // Restore file from backup
                if (file_exists($size_backup_file_path)) {
                    copy($size_backup_file_path, $size_path);
                }
            }
        }
        
        return true;
    }
    
    /**
     * Delete backup
     */
    public function delete_backup($attachment_id) {
        // Get attachment path
        $attachment_path = get_attached_file($attachment_id);
        
        // Get backup directory
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/webp-cp-backups';
        
        // Get backup file path
        $backup_file_path = $backup_dir . '/' . basename($attachment_path);
        
        // Delete backup file
        if (file_exists($backup_file_path)) {
            unlink($backup_file_path);
        }
        
        // Get attachment metadata
        $metadata = wp_get_attachment_metadata($attachment_id);
        
        // Delete size backup files
        if (isset($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size => $size_data) {
                // Get size backup file path
                $size_backup_file_path = $backup_dir . '/' . $size_data['file'];
                
                // Delete backup file
                if (file_exists($size_backup_file_path)) {
                    unlink($size_backup_file_path);
                }
            }
        }
        
        return true;
    }
    
    /**
     * Delete all backups
     */
    public function delete_all_backups() {
        // Get backup directory
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/webp-cp-backups';
        
        // Check if the backup directory exists
        if (!file_exists($backup_dir)) {
            return true;
        }
        
        // Get all files in the backup directory
        $files = glob($backup_dir . '/*');
        
        // Delete all files
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        return true;
    }
}