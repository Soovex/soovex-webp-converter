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
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
    
    $converted_images = array();
    
    if ($table_exists) {
        $converted_logs = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT attachment_id FROM `{$table_name}` WHERE status = %s", 'Converted'));
        
        foreach ($converted_logs as $log) {
            $attachment_id = intval($log->attachment_id);
            if ($attachment_id && get_post($attachment_id)) {
                // Double-check that the file is actually WebP
                if (webp_cp_is_attachment_converted($attachment_id)) {
                    $converted_images[] = $attachment_id;
                }
            }
        }
    }
    
    // If no converted images found in log, fallback to querying attachments
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
            if (webp_cp_is_attachment_converted($image_id)) {
                $converted_images[] = $image_id;
            }
        }
    }
    
    return array_unique($converted_images);
}

/**
 * Get storage saved
 */
function webp_cp_get_storage_saved() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'webp_cp_activity_log';
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
    
    $storage_saved = 0;
    $backup = WebP_CP_Backup::get_instance();
    $processed_attachments = array();
    
    if ($table_exists) {
        $logs = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT attachment_id FROM `{$table_name}` WHERE status = %s", 'Converted'));
        
        foreach ($logs as $log) {
            $attachment_id = intval($log->attachment_id);
            if (!$attachment_id || isset($processed_attachments[$attachment_id])) {
                continue;
            }
            $processed_attachments[$attachment_id] = true;
            
            $attachment_path = get_attached_file($attachment_id);
            if ($attachment_path && file_exists($attachment_path)) {
                $file_ext = strtolower(pathinfo($attachment_path, PATHINFO_EXTENSION));
                if ($file_ext === 'webp') {
                    $backup_file_path = $backup->get_backup_file($attachment_id);
                    if ($backup_file_path && file_exists($backup_file_path)) {
                        $diff = filesize($backup_file_path) - filesize($attachment_path);
                        if ($diff > 0) {
                            $storage_saved += $diff;
                        }
                    }
                    
                    // Also calculate savings for size thumbnails
                    $att_backup_dir = $backup->get_backup_dir($attachment_id);
                    if (is_dir($att_backup_dir)) {
                        $meta = wp_get_attachment_metadata($attachment_id);
                        if (isset($meta['sizes']) && is_array($meta['sizes'])) {
                            $base_dir = dirname($attachment_path);
                            foreach ($meta['sizes'] as $size_data) {
                                if (isset($size_data['file'])) {
                                    $webp_thumb = $base_dir . '/' . $size_data['file'];
                                    $orig_base = pathinfo($size_data['file'], PATHINFO_FILENAME);
                                    $thumb_backups = glob($att_backup_dir . '/' . $orig_base . '.*');
                                    if (!empty($thumb_backups) && file_exists($webp_thumb)) {
                                        $thumb_diff = filesize($thumb_backups[0]) - filesize($webp_thumb);
                                        if ($thumb_diff > 0) {
                                            $storage_saved += $thumb_diff;
                                        }
                                    }
                                }
                            }
                        }
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
    
    $upload_dir = wp_upload_dir();
    $basedir = $upload_dir['basedir'];
    
    if (!is_dir($basedir)) {
        return 0;
    }
    
    // Collect all registered attachment paths in WP
    $attached_files = $wpdb->get_col("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file'");
    $registered_basenames = array();
    
    if (!empty($attached_files)) {
        foreach ($attached_files as $file) {
            $registered_basenames[basename($file)] = true;
        }
    }
    
    $cleaned_count = 0;
    
    try {
        $it = new RecursiveDirectoryIterator($basedir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it);
        
        foreach ($files as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'webp') {
                $webp_file = $file->getRealPath();
                $basename = basename($webp_file);
                
                // If file is not registered as an attached file and not in backup dir
                if (strpos($webp_file, 'webp-cp-backups') === false && !isset($registered_basenames[$basename])) {
                    $original_file = preg_replace('/\.webp$/i', '', $webp_file);
                    if (!file_exists($original_file)) {
                        if (@unlink($webp_file)) {
                            $cleaned_count++;
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        // Silently catch filesystem exceptions
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
    return WebP_CP_Backup::get_instance()->has_backup($attachment_id);
}
