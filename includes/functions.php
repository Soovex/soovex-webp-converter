<?php
/**
 * Helper functions for the plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all images from media library
 */
function webp_cp_get_all_images() {
    $args = array(
        'post_type' => 'attachment',
        'post_mime_type' => array('image/jpeg', 'image/png'),
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'fields' => 'ids', // Only return IDs instead of full post objects
    );
    
    $query = new WP_Query($args);
    $all_images = $query->posts;
    
    // Filter to only include images that can be converted (JPG/PNG format)
    $unconverted_images = array();
    
    foreach ($all_images as $image_id) {
        // Check if the image can be converted (is in JPG/PNG format)
        if (webp_cp_can_convert_attachment($image_id)) {
            $unconverted_images[] = $image_id;
        }
    }
    
    return $unconverted_images;
}

/**
 * Get converted images
 */
function webp_cp_get_converted_images() {
    // First try to get from activity log (more reliable)
    global $wpdb;
    $table_name = $wpdb->prefix . 'webp_cp_activity_log';
    $converted_logs = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT attachment_id FROM `{$table_name}` WHERE status = %s", 'Converted'));
    
    $converted_images = array();
    foreach ($converted_logs as $log) {
        $attachment_id = intval($log->attachment_id);
        if ($attachment_id && get_post($attachment_id)) {
            // Double-check that the file is actually WebP
            if (webp_cp_is_attachment_converted($attachment_id)) {
                $converted_images[] = $attachment_id;
            }
        }
    }
    
    // If no converted images found in log, fallback to the original method
    if (empty($converted_images)) {
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => array('image/webp'),
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'fields' => 'ids', // Only return IDs instead of full post objects
        );
        
        $query = new WP_Query($args);
        $all_images = $query->posts;
        
        // Filter to only include images that are actually in WebP format
        foreach ($all_images as $image_id) {
            // Check if the image is actually in WebP format
            if (webp_cp_is_attachment_converted($image_id)) {
                $converted_images[] = $image_id;
            }
        }
    }
    
    return $converted_images;
}

/**
 * Get storage saved
 */
function webp_cp_get_storage_saved() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'webp_cp_activity_log';
    $logs = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$table_name}` WHERE status = %s", 'Converted'));
    
    $storage_saved = 0;
    
    foreach ($logs as $log) {
        // Use attachment_id directly from the log instead of searching by title
        $attachment_id = intval($log->attachment_id);
        
        if ($attachment_id) {
            // Get attachment path
            $attachment_path = get_attached_file($attachment_id);
            
            if ($attachment_path && file_exists($attachment_path)) {
                // Check if this is a WebP file (converted)
                $file_ext = strtolower(pathinfo($attachment_path, PATHINFO_EXTENSION));
                
                if ($file_ext === 'webp') {
                    // This is a converted WebP file, calculate savings from backup
                    $upload_dir = wp_upload_dir();
                    $backup_dir = $upload_dir['basedir'] . '/webp-cp-backups';
                    
                    // Get the base filename without extension to find the backup
                    $base_filename = pathinfo($attachment_path, PATHINFO_FILENAME);
                    $backup_file_path = null;
                    
                    // Try to find the backup file with original extensions
                    $original_extensions = array('jpg', 'jpeg', 'png');
                    foreach ($original_extensions as $ext) {
                        $test_backup_path = $backup_dir . '/' . $base_filename . '.' . $ext;
                        if (file_exists($test_backup_path)) {
                            $backup_file_path = $test_backup_path;
                            break;
                        }
                    }
                    
                    // If no backup found with base filename, try with the full filename
                    if (!$backup_file_path) {
                        $backup_file_path = $backup_dir . '/' . basename($attachment_path);
                    }
                    
                    if ($backup_file_path && file_exists($backup_file_path)) {
                        // Calculate actual size difference between original backup and WebP file
                        $original_size = filesize($backup_file_path);
                        $webp_size = filesize($attachment_path);
                        $storage_saved += ($original_size - $webp_size);
                    }
                } else {
                    // This is an original file, check if WebP version exists
                    $webp_path = $attachment_path . '.webp';
                    
                    if (file_exists($webp_path)) {
                        $original_size = filesize($attachment_path);
                        $webp_size = filesize($webp_path);
                        $storage_saved += ($original_size - $webp_size);
                    }
                }
            }
        }
    }
    
    return $storage_saved;
}

/**
 * Format filesize
 */
function webp_cp_format_filesize($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, 2) . ' ' . $units[$i];
}

/**
 * Get attachment by title (replacement for deprecated get_page_by_title)
 */
function webp_cp_get_attachment_by_title($title) {
    $args = array(
        'post_type' => 'attachment',
        'title' => $title,
        'posts_per_page' => 1,
        'post_status' => 'inherit',
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        return $query->posts[0];
    }
    
    return null;
}

/**
 * Check if WebP is supported by the server
 */
function webp_cp_is_webp_supported() {
    // Check if GD extension is loaded
    if (!extension_loaded('gd')) {
        return false;
    }
    
    // Check if WebP support is available
    if (!function_exists('imagewebp')) {
        return false;
    }
    
    return true;
}

/**
 * Get server requirements status
 */
function webp_cp_get_server_status() {
    $status = array(
        'gd_loaded' => extension_loaded('gd'),
        'webp_supported' => function_exists('imagewebp'),
        'memory_limit' => ini_get('memory_limit'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
    );
    
    return $status;
}

/**
 * Get detailed server health check information
 */
function webp_cp_get_detailed_server_health() {
    $health = array(
        'php_version' => PHP_VERSION,
        'php_version_ok' => version_compare(PHP_VERSION, '7.4', '>='),
        'gd_loaded' => extension_loaded('gd'),
        'gd_info' => function_exists('gd_info') ? gd_info() : array(),
        'webp_supported' => function_exists('imagewebp'),
        'memory_limit' => ini_get('memory_limit'),
        'memory_limit_bytes' => wp_convert_hr_to_bytes(ini_get('memory_limit')),
        'memory_limit_ok' => wp_convert_hr_to_bytes(ini_get('memory_limit')) >= (128 * 1024 * 1024),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_execution_time' => ini_get('max_execution_time'),
        'max_input_time' => ini_get('max_input_time'),
        'max_input_vars' => ini_get('max_input_vars'),
        'server_software' => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : 'Unknown',
        'wp_version' => get_bloginfo('version'),
        'wp_memory_limit' => WP_MEMORY_LIMIT,
        'wp_max_memory_limit' => WP_MAX_MEMORY_LIMIT,
        'disk_free_space' => function_exists('disk_free_space') ? disk_free_space(ABSPATH) : false,
        'disk_total_space' => function_exists('disk_total_space') ? disk_total_space(ABSPATH) : false,
    );
    
    return $health;
}

/**
 * Clean up orphaned WebP files
 */
function webp_cp_cleanup_orphaned_webp() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'webp_cp_activity_log';
    $upload_dir = wp_upload_dir();
    
    // Get all WebP files in uploads directory
    $webp_files = glob($upload_dir['basedir'] . '/**/*.webp');
    
    $cleaned_count = 0;
    
    foreach ($webp_files as $webp_file) {
        // Get the original file path
        $original_file = str_replace('.webp', '', $webp_file);
        
        // Check if original file exists
        if (!file_exists($original_file)) {
            // Check if this WebP file is logged in our database
            $relative_path = str_replace($upload_dir['basedir'], '', $webp_file);
            $original_relative = str_replace($upload_dir['basedir'], '', $original_file);
            
            $log_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM `{$table_name}` WHERE webp_image = %s",
                basename($webp_file)
            ));
            
            if (!$log_exists) {
                // This is an orphaned WebP file, delete it
                if (unlink($webp_file)) {
                    $cleaned_count++;
                }
            }
        }
    }
    
    return $cleaned_count;
}

/**
 * Check if an attachment is converted to WebP
 */
function webp_cp_is_attachment_converted($attachment_id) {
    $attachment_path = get_attached_file($attachment_id);
    
    if (!$attachment_path || !file_exists($attachment_path)) {
        // Try to find the file using metadata
        $metadata = wp_get_attachment_metadata($attachment_id);
        if ($metadata && isset($metadata['file'])) {
            $upload_dir = wp_upload_dir();
            $correct_path = $upload_dir['basedir'] . '/' . $metadata['file'];
            if (file_exists($correct_path)) {
                $attachment_path = $correct_path;
                // Update the attached file path
                update_attached_file($attachment_id, $correct_path);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    $file_ext = strtolower(pathinfo($attachment_path, PATHINFO_EXTENSION));
    
    return ($file_ext === 'webp');
}

/**
 * Check if an attachment can be converted to WebP
 */
function webp_cp_can_convert_attachment($attachment_id) {
    $attachment_path = get_attached_file($attachment_id);
    
    if (!$attachment_path || !file_exists($attachment_path)) {
        // Try to find the file using metadata
        $metadata = wp_get_attachment_metadata($attachment_id);
        if ($metadata && isset($metadata['file'])) {
            $upload_dir = wp_upload_dir();
            $correct_path = $upload_dir['basedir'] . '/' . $metadata['file'];
            if (file_exists($correct_path)) {
                $attachment_path = $correct_path;
                // Update the attached file path
                update_attached_file($attachment_id, $correct_path);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    $file_ext = strtolower(pathinfo($attachment_path, PATHINFO_EXTENSION));
    
    return in_array($file_ext, array('jpg', 'jpeg', 'png'));
}

/**
 * Check if an attachment has a backup
 */
function webp_cp_has_attachment_backup($attachment_id) {
    $attachment_path = get_attached_file($attachment_id);
    
    if (!$attachment_path) {
        return false;
    }
    
    $upload_dir = wp_upload_dir();
    $backup_dir = $upload_dir['basedir'] . '/webp-cp-backups';
    $backup_file_path = $backup_dir . '/' . basename($attachment_path);
    
    return file_exists($backup_file_path);
}
