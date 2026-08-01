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
     * Get backup directory path
     *
     * @param int $attachment_id Optional attachment ID for isolated subfolder
     * @return string
     */
    public function get_backup_dir($attachment_id = 0) {
        $upload_dir = wp_upload_dir();
        $base_backup_dir = $upload_dir['basedir'] . '/webp-cp-backups';
        
        if ($attachment_id > 0) {
            return $base_backup_dir . '/' . intval($attachment_id);
        }
        
        return $base_backup_dir;
    }
    
    /**
     * Get path to the main backup file for an attachment (with legacy fallback)
     *
     * @param int $attachment_id
     * @return string|false
     */
    public function get_backup_file($attachment_id) {
        $attachment_id = intval($attachment_id);
        if ($attachment_id <= 0) {
            return false;
        }
        
        // 1. Check if stored in post meta
        $meta_backup_path = get_post_meta($attachment_id, '_webp_cp_backup_path', true);
        if (!empty($meta_backup_path) && file_exists($meta_backup_path)) {
            return $meta_backup_path;
        }
        
        // 2. Check attachment-isolated directory
        $att_backup_dir = $this->get_backup_dir($attachment_id);
        if (is_dir($att_backup_dir)) {
            $orig_file_rel = get_post_meta($attachment_id, '_webp_cp_original_file', true);
            if (!empty($orig_file_rel)) {
                $target_path = $att_backup_dir . '/' . basename($orig_file_rel);
                if (file_exists($target_path)) {
                    return $target_path;
                }
            }
            
            // Look for any main image in the attachment backup directory
            $files = glob($att_backup_dir . '/*');
            if (!empty($files)) {
                // Return the first file or find non-dimensioned file
                foreach ($files as $file) {
                    if (is_file($file)) {
                        return $file;
                    }
                }
            }
        }
        
        // 3. Fallback: Legacy flat backup directory
        $upload_dir = wp_upload_dir();
        $legacy_backup_dir = $upload_dir['basedir'] . '/webp-cp-backups';
        
        if (!is_dir($legacy_backup_dir)) {
            return false;
        }
        
        // Try getting original name from activity log
        global $wpdb;
        $table_name = $wpdb->prefix . 'webp_cp_activity_log';
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
        if ($table_exists) {
            $orig_image_name = $wpdb->get_var($wpdb->prepare(
                "SELECT original_image FROM `{$table_name}` WHERE attachment_id = %d AND original_image != '' ORDER BY id DESC LIMIT 1",
                $attachment_id
            ));
            
            if (!empty($orig_image_name)) {
                $legacy_path = $legacy_backup_dir . '/' . sanitize_file_name($orig_image_name);
                if (file_exists($legacy_path)) {
                    return $legacy_path;
                }
            }
        }
        
        // Try checking attachment path base name with common original extensions
        $attachment_path = get_attached_file($attachment_id);
        if ($attachment_path) {
            $base_name = pathinfo($attachment_path, PATHINFO_FILENAME);
            $extensions = array('jpg', 'jpeg', 'png');
            foreach ($extensions as $ext) {
                $test_path = $legacy_backup_dir . '/' . $base_name . '.' . $ext;
                if (file_exists($test_path)) {
                    return $test_path;
                }
            }
            
            $test_path = $legacy_backup_dir . '/' . basename($attachment_path);
            if (file_exists($test_path)) {
                return $test_path;
            }
        }
        
        return false;
    }
    
    /**
     * Check if a backup exists for an attachment
     *
     * @param int $attachment_id
     * @return bool
     */
    public function has_backup($attachment_id) {
        $backup_file = $this->get_backup_file($attachment_id);
        return (!empty($backup_file) && file_exists($backup_file));
    }
    
    /**
     * Create backup for an attachment
     *
     * @param int $attachment_id
     * @return bool
     */
    public function create_backup($attachment_id) {
        $attachment_id = intval($attachment_id);
        if ($attachment_id <= 0) {
            return false;
        }
        
        // Get attachment path
        $attachment_path = get_attached_file($attachment_id);
        
        // Check if the attachment exists; if not try metadata path
        if (!$attachment_path || !file_exists($attachment_path)) {
            $metadata = wp_get_attachment_metadata($attachment_id);
            if ($metadata && isset($metadata['file'])) {
                $upload_dir = wp_upload_dir();
                $correct_path = $upload_dir['basedir'] . '/' . $metadata['file'];
                if (file_exists($correct_path)) {
                    $attachment_path = $correct_path;
                    update_attached_file($attachment_id, $correct_path);
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        
        // Get attachment metadata and mime type before conversion
        $metadata = wp_get_attachment_metadata($attachment_id);
        $mime_type = get_post_mime_type($attachment_id);
        
        // Get isolated backup directory for this attachment
        $att_backup_dir = $this->get_backup_dir($attachment_id);
        
        // Create backup directory if it doesn't exist
        if (!file_exists($att_backup_dir)) {
            wp_mkdir_p($att_backup_dir);
        }
        
        // Get backup file path (sanitize filename for security)
        $safe_filename = sanitize_file_name(basename($attachment_path));
        $backup_file_path = $att_backup_dir . '/' . $safe_filename;
        
        // Check if identical backup already exists
        if (file_exists($backup_file_path)) {
            if (filesize($attachment_path) === filesize($backup_file_path) && 
                md5_file($attachment_path) === md5_file($backup_file_path)) {
                // Ensure post meta is set
                update_post_meta($attachment_id, '_webp_cp_backup_path', $backup_file_path);
                update_post_meta($attachment_id, '_webp_cp_original_file', $metadata && isset($metadata['file']) ? $metadata['file'] : basename($attachment_path));
                update_post_meta($attachment_id, '_webp_cp_original_mime_type', $mime_type);
                if ($metadata) {
                    update_post_meta($attachment_id, '_webp_cp_original_metadata', $metadata);
                }
                return true;
            } else {
                @unlink($backup_file_path);
            }
        }
        
        // Copy main file to backup directory with retry
        $copy_attempts = 0;
        $max_attempts = 3;
        $copied = false;
        
        while ($copy_attempts < $max_attempts) {
            if (copy($attachment_path, $backup_file_path)) {
                $copied = true;
                break;
            }
            
            $copy_attempts++;
            usleep(100000); // 0.1 seconds
        }
        
        if (!$copied) {
            return false;
        }
        
        // Store original information in post meta for 100% reliable restoration
        $relative_file = $metadata && isset($metadata['file']) ? $metadata['file'] : basename($attachment_path);
        update_post_meta($attachment_id, '_webp_cp_original_file', $relative_file);
        update_post_meta($attachment_id, '_webp_cp_original_mime_type', $mime_type);
        update_post_meta($attachment_id, '_webp_cp_backup_path', $backup_file_path);
        if (!empty($metadata)) {
            update_post_meta($attachment_id, '_webp_cp_original_metadata', $metadata);
        }
        
        // Backup size variants (thumbnails) into the isolated folder
        if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
            $base_dir = pathinfo($attachment_path, PATHINFO_DIRNAME);
            foreach ($metadata['sizes'] as $size => $size_data) {
                if (isset($size_data['file'])) {
                    $size_path = $base_dir . '/' . $size_data['file'];
                    $size_backup_file_path = $att_backup_dir . '/' . sanitize_file_name($size_data['file']);
                    
                    if (file_exists($size_path)) {
                        if (file_exists($size_backup_file_path)) {
                            @unlink($size_backup_file_path);
                        }
                        @copy($size_path, $size_backup_file_path);
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
     *
     * @param int $attachment_id
     * @return void
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
            $deletion_timestamp = time() + (intval($deletion_duration) * DAY_IN_SECONDS);
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
     * Restore backup (delegates to converter for full metadata synchronization)
     *
     * @param int $attachment_id
     * @return bool
     */
    public function restore_backup($attachment_id) {
        $converter = WebP_CP_Converter::get_instance();
        return $converter->revert_image($attachment_id);
    }
    
    /**
     * Delete backup files and directory for an attachment
     *
     * @param int $attachment_id
     * @return bool
     */
    public function delete_backup($attachment_id) {
        $attachment_id = intval($attachment_id);
        if ($attachment_id <= 0) {
            return false;
        }
        
        // 1. Delete isolated attachment backup folder
        $att_backup_dir = $this->get_backup_dir($attachment_id);
        if (is_dir($att_backup_dir)) {
            $this->recursive_rmdir($att_backup_dir);
        }
        
        // 2. Delete legacy backup file if referenced
        $meta_backup_path = get_post_meta($attachment_id, '_webp_cp_backup_path', true);
        if (!empty($meta_backup_path) && file_exists($meta_backup_path)) {
            @unlink($meta_backup_path);
        }
        
        // 3. Clean up post meta
        delete_post_meta($attachment_id, '_webp_cp_original_file');
        delete_post_meta($attachment_id, '_webp_cp_original_mime_type');
        delete_post_meta($attachment_id, '_webp_cp_original_metadata');
        delete_post_meta($attachment_id, '_webp_cp_backup_path');
        
        return true;
    }
    
    /**
     * Delete all backups across all attachments
     *
     * @return bool
     */
    public function delete_all_backups() {
        $upload_dir = wp_upload_dir();
        $base_backup_dir = $upload_dir['basedir'] . '/webp-cp-backups';
        
        if (is_dir($base_backup_dir)) {
            $items = scandir($base_backup_dir);
            if ($items !== false) {
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') {
                        continue;
                    }
                    $path = $base_backup_dir . '/' . $item;
                    if (is_dir($path)) {
                        $this->recursive_rmdir($path);
                    } elseif (is_file($path)) {
                        @unlink($path);
                    }
                }
            }
        }
        
        // Clean all attachment backup meta keys
        delete_post_meta_by_key('_webp_cp_original_file');
        delete_post_meta_by_key('_webp_cp_original_mime_type');
        delete_post_meta_by_key('_webp_cp_original_metadata');
        delete_post_meta_by_key('_webp_cp_backup_path');
        
        return true;
    }
    
    /**
     * Recursively delete a directory and its contents
     *
     * @param string $dir
     * @return bool
     */
    public function recursive_rmdir($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = scandir($dir);
        if ($files !== false) {
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    $this->recursive_rmdir($path);
                } else {
                    @unlink($path);
                }
            }
        }
        
        return @rmdir($dir);
    }
}