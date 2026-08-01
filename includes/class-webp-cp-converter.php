<?php
/**
 * Converter class for the plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WebP_CP_Converter {
    
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
        // Add hooks for auto-converting new uploads
        add_action('add_attachment', array($this, 'auto_convert_new_attachment'));
        
        // Add hook for preventing redirect after conversion
        add_filter('wp_redirect', array($this, 'prevent_redirect'), 10, 2);
        
        // Add WebP serving functionality
        add_action('init', array($this, 'serve_webp_images'));
        
        // Add filter to serve WebP images in content
        add_filter('wp_get_attachment_image_src', array($this, 'serve_webp_attachment'), 10, 4);
        
        // Add auto-convert hook
        add_action('webp_cp_auto_convert_attachment', array($this, 'auto_convert_attachment'));
    }
    
    /**
     * Auto convert new attachment when uploaded
     *
     * @param int $attachment_id The attachment ID
     * @return void
     */
    public function auto_convert_new_attachment($attachment_id) {
        // Check if auto convert is enabled
        if (!get_option('webp_cp_auto_convert', 0)) {
            return;
        }
        
        // Check if backup is enabled (required for auto-convert)
        if (!get_option('webp_cp_enable_backup', 1)) {
            return;
        }
        
        // Get attachment
        $attachment = get_post($attachment_id);
        if (!$attachment || !wp_attachment_is_image($attachment_id)) {
            return;
        }
        
        // Get attachment path
        $attachment_path = get_attached_file($attachment_id);
        if (!$attachment_path || !file_exists($attachment_path)) {
            return;
        }
        
        // Check if the image is JPG or PNG
        $file_ext = strtolower(pathinfo($attachment_path, PATHINFO_EXTENSION));
        if (!in_array($file_ext, array('jpg', 'jpeg', 'png'))) {
            return;
        }
        
        // Schedule conversion for after upload is complete
        wp_schedule_single_event(time() + 2, 'webp_cp_auto_convert_attachment', array($attachment_id));
    }
    
    /**
     * Auto convert attachment (scheduled event handler)
     *
     * @param int $attachment_id The attachment ID
     * @return void
     */
    public function auto_convert_attachment($attachment_id) {
        // Check if auto convert is still enabled
        if (!get_option('webp_cp_auto_convert', 0)) {
            return;
        }
        
        // Check if attachment exists
        if (!get_post($attachment_id)) {
            return;
        }
        
        // Convert the image
        $this->convert_image($attachment_id);
    }
    
    /**
     * Prevent redirect after conversion
     *
     * @param string $location The redirect location
     * @param int $status The redirect status code
     * @return string The filtered location
     */
    public function prevent_redirect($location, $status) {
        // Check if the redirect is related to our conversion process
        if (strpos($location, 'webp-converter-pro') !== false) {
            // Return the current URL to prevent redirect
            return remove_query_arg('webp-cp-redirect');
        }
        
        return $location;
    }
    
    /**
     * Convert a single image to WebP format
     *
     * @param int $attachment_id The attachment ID to convert
     * @return bool True on success, false on failure
     */
    public function convert_image($attachment_id) {
        // Check if WebP is supported
        if (!webp_cp_is_webp_supported()) {
            $this->log_conversion($attachment_id, '', '', __('Failed - WebP not supported', 'soovex-webp-converter'));
            return false;
        }
        
        // Check if image can be converted
        if (!webp_cp_can_convert_attachment($attachment_id)) {
            return false;
        }
        
        // Check if image is already converted
        if (webp_cp_is_attachment_converted($attachment_id)) {
            $this->log_conversion($attachment_id, '', '', __('Skipped - Already converted', 'soovex-webp-converter'));
            return false;
        }
        
        // Validate and get attachment path
        $attachment_path = $this->validate_and_get_attachment_path($attachment_id);
        if (!$attachment_path) {
            return false;
        }
        
        // Get attachment metadata
        $metadata = wp_get_attachment_metadata($attachment_id);
        
        // Get file extension and validate format
        $file_ext = strtolower(pathinfo($attachment_path, PATHINFO_EXTENSION));
        if (!in_array($file_ext, array('jpg', 'jpeg', 'png'))) {
            return false;
        }
        
        // Get file names for logging
        $original_file_name = basename($attachment_path);
        
        // Create backup if enabled
        if (!$this->create_backup_if_enabled($attachment_id, $original_file_name, $original_file_name . '.webp')) {
            return false;
        }
        
        // Validate file and check resources
        if (!$this->validate_file_for_conversion($attachment_path, $attachment_id, $original_file_name, $original_file_name . '.webp')) {
            return false;
        }
        
        $quality = get_option('webp_cp_compression_quality', 82);
        $dir = dirname($attachment_path);
        $base_name = pathinfo($attachment_path, PATHINFO_FILENAME);
        
        // Collision-free WebP filename generation
        $desired_webp_name = $base_name . '.webp';
        $target_webp_path = $dir . '/' . $desired_webp_name;
        
        // If a file with this name already exists in the folder (from a different attachment / format), make it unique
        if (file_exists($target_webp_path)) {
            if (!function_exists('wp_unique_filename')) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }
            $unique_filename = wp_unique_filename($dir, $desired_webp_name);
            $target_webp_path = $dir . '/' . $unique_filename;
        }
        
        $temp_webp_path = $target_webp_path . '.tmp';
        $webp_file_name = basename($target_webp_path);
        
        // Convert main image
        if (!$this->convert_image_to_webp($attachment_path, $temp_webp_path, $file_ext, $quality, $attachment_id, $original_file_name, $webp_file_name)) {
            if (file_exists($temp_webp_path)) {
                @unlink($temp_webp_path);
            }
            return false;
        }
        
        // Move temp file to final WebP path
        if (!$this->move_webp_file($temp_webp_path, $target_webp_path, $attachment_id, $original_file_name, $webp_file_name)) {
            return false;
        }
        
        // Process size variants (thumbnails)
        $new_metadata = $this->process_size_variants($metadata, $attachment_path, $target_webp_path, $file_ext, $quality, $attachment_id);
        
        // Update attachment in database
        $this->update_attachment_to_webp($attachment_id, $target_webp_path, $new_metadata);
        
        // Remove original main image on disk if target path is different and original still exists
        if (file_exists($attachment_path) && $attachment_path !== $target_webp_path) {
            @unlink($attachment_path);
        }
        
        // Log successful conversion
        $this->log_conversion($attachment_id, $original_file_name, $webp_file_name, __('Converted', 'soovex-webp-converter'));
        
        return true;
    }
    
    /**
     * Revert a converted image back to original format
     *
     * @param int $attachment_id The attachment ID to revert
     * @return bool True on success, false on failure
     */
    public function revert_image($attachment_id) {
        $attachment_id = intval($attachment_id);
        if ($attachment_id <= 0) {
            return false;
        }
        
        // Get backup instance
        $backup = WebP_CP_Backup::get_instance();
        
        // Retrieve the backup file path
        $backup_file_path = $backup->get_backup_file($attachment_id);
        
        if (!$backup_file_path || !file_exists($backup_file_path)) {
            $this->log_conversion($attachment_id, '', '', __('Failed - No backup found', 'soovex-webp-converter'));
            return false;
        }
        
        // Get current attached file
        $current_attached_path = get_attached_file($attachment_id);
        $upload_dir = wp_upload_dir();
        
        // Retrieve original metadata and properties
        $original_metadata = get_post_meta($attachment_id, '_webp_cp_original_metadata', true);
        $original_file_rel = get_post_meta($attachment_id, '_webp_cp_original_file', true);
        $original_mime_type = get_post_meta($attachment_id, '_webp_cp_original_mime_type', true);
        
        // Fallbacks if post meta was not set (e.g. converted in prior plugin version)
        if (empty($original_file_rel)) {
            $backup_filename = basename($backup_file_path);
            if ($current_attached_path) {
                $current_dir = dirname($current_attached_path);
                $rel_dir = str_replace($upload_dir['basedir'] . '/', '', $current_dir);
                $original_file_rel = ($rel_dir !== $upload_dir['basedir']) ? $rel_dir . '/' . $backup_filename : $backup_filename;
            } else {
                $original_file_rel = $backup_filename;
            }
        }
        
        if (empty($original_mime_type)) {
            $orig_ext = strtolower(pathinfo($backup_file_path, PATHINFO_EXTENSION));
            $original_mime_type = ($orig_ext === 'png') ? 'image/png' : 'image/jpeg';
        }
        
        // Determine full restored original file path
        $restored_original_path = $upload_dir['basedir'] . '/' . $original_file_rel;
        $restored_dir = dirname($restored_original_path);
        
        if (!file_exists($restored_dir)) {
            wp_mkdir_p($restored_dir);
        }
        
        // Restore main original file from backup
        if (!copy($backup_file_path, $restored_original_path)) {
            $this->log_conversion($attachment_id, '', '', __('Failed - Could not restore from backup', 'soovex-webp-converter'));
            return false;
        }
        
        // Clean up converted WebP thumbnails and restore original thumbnails
        $current_metadata = wp_get_attachment_metadata($attachment_id);
        if (isset($current_metadata['sizes']) && is_array($current_metadata['sizes'])) {
            $current_dir = $current_attached_path ? dirname($current_attached_path) : $restored_dir;
            foreach ($current_metadata['sizes'] as $size => $size_data) {
                if (isset($size_data['file'])) {
                    $webp_thumb_path = $current_dir . '/' . $size_data['file'];
                    if (file_exists($webp_thumb_path) && strtolower(pathinfo($webp_thumb_path, PATHINFO_EXTENSION)) === 'webp') {
                        @unlink($webp_thumb_path);
                    }
                }
            }
        }
        
        // Restore size thumbnails from attachment backup folder
        $att_backup_dir = $backup->get_backup_dir($attachment_id);
        $legacy_backup_dir = $upload_dir['basedir'] . '/webp-cp-backups';
        
        if (is_array($original_metadata) && isset($original_metadata['sizes'])) {
            foreach ($original_metadata['sizes'] as $size => $size_data) {
                if (isset($size_data['file'])) {
                    $orig_thumb_file = $size_data['file'];
                    $target_thumb_path = $restored_dir . '/' . $orig_thumb_file;
                    
                    // Look in isolated backup dir first, then legacy flat backup dir
                    $thumb_backup_path = $att_backup_dir . '/' . $orig_thumb_file;
                    if (!file_exists($thumb_backup_path)) {
                        $thumb_backup_path = $legacy_backup_dir . '/' . $orig_thumb_file;
                    }
                    
                    if (file_exists($thumb_backup_path)) {
                        @copy($thumb_backup_path, $target_thumb_path);
                    }
                }
            }
        }
        
        // Update attachment post mime type
        $attachment_post = array(
            'ID' => $attachment_id,
            'post_mime_type' => $original_mime_type
        );
        wp_update_post($attachment_post);
        
        // Update attached file path
        update_attached_file($attachment_id, $restored_original_path);
        
        // Restore or regenerate metadata
        if (is_array($original_metadata) && !empty($original_metadata)) {
            $original_metadata['file'] = $original_file_rel;
            wp_update_attachment_metadata($attachment_id, $original_metadata);
        } else {
            $this->regenerate_attachment_metadata($attachment_id, $restored_original_path);
        }
        
        // Delete the main converted WebP file if it's different from the restored path
        if ($current_attached_path && file_exists($current_attached_path) && $current_attached_path !== $restored_original_path) {
            @unlink($current_attached_path);
        }
        
        // Clear caches
        wp_cache_delete($attachment_id, 'posts');
        clean_post_cache($attachment_id);
        
        $original_file_name = basename($restored_original_path);
        $webp_file_name = $current_attached_path ? basename($current_attached_path) : $original_file_name . '.webp';
        
        // Log the reversion
        $this->log_conversion($attachment_id, $original_file_name, $webp_file_name, __('Reverted', 'soovex-webp-converter'));
        
        // Clean up backup files and directory for this attachment
        $backup->delete_backup($attachment_id);
        
        return true;
    }
    
    /**
     * Clean up backup files for an attachment
     *
     * @param int $attachment_id The attachment ID
     * @return void
     */
    public function cleanup_backup_files($attachment_id) {
        $backup = WebP_CP_Backup::get_instance();
        $backup->delete_backup($attachment_id);
    }
    
    /**
     * Log conversion activity to database
     *
     * @param int $attachment_id The attachment ID
     * @param string $original_file_name Original file name
     * @param string $webp_file_name WebP file name
     * @param string $status Conversion status message
     * @return int|false The log entry ID on success, false on failure
     */
    private function log_conversion($attachment_id, $original_file_name, $webp_file_name, $status) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'webp_cp_activity_log';
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
        if (!$table_exists) {
            return false;
        }
        
        $data = array(
            'attachment_id' => $attachment_id,
            'original_image' => $original_file_name,
            'webp_image' => $webp_file_name,
            'status' => $status,
            'date' => current_time('mysql')
        );
        
        $format = array('%d', '%s', '%s', '%s', '%s');
        $wpdb->insert($table_name, $data, $format);
        
        return $wpdb->insert_id;
    }
    
    /**
     * Initialize WebP image serving functionality
     *
     * @return void
     */
    public function serve_webp_images() {
        // Check if we should serve WebP images
        if (!get_option('webp_cp_serve_webp', 1)) {
            return;
        }
        
        // Add rewrite rules for WebP serving
        add_rewrite_rule(
            '^webp-cp/(.+\.(jpg|jpeg|png))\.webp$',
            'index.php?webp_cp_serve=1&webp_cp_file=$matches[1]',
            'top'
        );
        
        // Handle WebP serving
        if (get_query_var('webp_cp_serve')) {
            $this->handle_webp_serving();
        }
        
        // Add action to handle direct image requests
        add_action('template_redirect', array($this, 'handle_image_redirect'));
    }
    
    /**
     * Check if browser supports WebP format
     *
     * @return bool True if browser supports WebP, false otherwise
     */
    private function browser_supports_webp() {
        if (!isset($_SERVER['HTTP_ACCEPT'])) {
            return false;
        }
        
        return strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;
    }
    
    /**
     * Handle WebP file serving via rewrite rules
     *
     * @return void
     */
    private function handle_webp_serving() {
        $file = get_query_var('webp_cp_file');
        
        if (!$file) {
            status_header(404);
            exit;
        }
        
        // Get upload directory
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/' . $file;
        $webp_path = $file_path . '.webp';
        
        // Check if WebP file exists
        if (!file_exists($webp_path)) {
            status_header(404);
            exit;
        }
        
        // Serve the WebP file
        $this->serve_file($webp_path, 'image/webp');
    }
    
    /**
     * Handle image redirect for converted images
     *
     * @return void
     */
    public function handle_image_redirect() {
        // Check if we should serve WebP images
        if (!get_option('webp_cp_serve_webp', 1)) {
            return;
        }
        
        // Check if browser supports WebP
        if (!$this->browser_supports_webp()) {
            return;
        }
        
        // Get current request URI
        $request_uri = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        
        // Check if this is an image request
        if (preg_match('/\.(jpg|jpeg|png)$/i', $request_uri)) {
            // Get upload directory
            $upload_dir = wp_upload_dir();
            $upload_path = $upload_dir['basedir'];
            $upload_url = $upload_dir['baseurl'];
            
            // Convert URL to file path with security validation
            $file_path = str_replace($upload_url, $upload_path, $request_uri);
            $file_path = parse_url($file_path, PHP_URL_PATH);
            $file_path = $upload_path . $file_path;
            
            // Security check: ensure the file is within uploads directory
            $real_upload_path = realpath($upload_path);
            $real_file_path = realpath($file_path);
            
            if ($real_file_path === false || strpos($real_file_path, $real_upload_path) !== 0) {
                // File path is outside uploads directory, skip
                return;
            }
            
            // Check if file exists
            if (file_exists($file_path)) {
                // Check if this is a converted image (WebP format)
                $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                
                if ($file_ext === 'webp') {
                    // This is already a WebP file, serve it directly with proper headers
                    $this->serve_file($file_path, 'image/webp');
                } else {
                    // Check if this file has been converted to WebP
                    $base_name = pathinfo($file_path, PATHINFO_FILENAME);
                    $webp_path = dirname($file_path) . '/' . $base_name . '.webp';
                    
                    if (file_exists($webp_path)) {
                        // Serve WebP content for the original URL
                        $this->serve_file($webp_path, 'image/webp');
                    }
                }
            }
        }
    }
    
    /**
     * Serve file with proper HTTP headers
     *
     * @param string $file_path The file path to serve
     * @param string $mime_type The MIME type of the file
     * @return void Exits after serving file
     */
    private function serve_file($file_path, $mime_type) {
        if (!file_exists($file_path)) {
            status_header(404);
            exit;
        }
        
        $file_size = filesize($file_path);
        
        // Set headers
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . $file_size);
        header('Cache-Control: public, max-age=31536000');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        
        // Output file
        readfile($file_path);
        exit;
    }
    
    /**
     * Serve WebP attachment images in WordPress
     *
     * @param array|false $image Image data array
     * @param int $attachment_id The attachment ID
     * @param string|array $size Image size
     * @param bool $icon Whether to return icon
     * @return array|false Modified image data array or false
     */
    public function serve_webp_attachment($image, $attachment_id, $size, $icon) {
        // Check if we should serve WebP images
        if (!get_option('webp_cp_serve_webp', 1)) {
            return $image;
        }
        
        // Check if browser supports WebP
        if (!$this->browser_supports_webp()) {
            return $image;
        }
        
        if (!$image || !$attachment_id) {
            return $image;
        }
        
        // Get attachment path
        $attachment_path = get_attached_file($attachment_id);
        if (!$attachment_path) {
            return $image;
        }
        
        // Check if this is already a WebP image
        $file_ext = strtolower(pathinfo($attachment_path, PATHINFO_EXTENSION));
        if ($file_ext === 'webp') {
            return $image;
        }
        
        // Check if WebP version exists
        $webp_path = $attachment_path . '.webp';
        if (!file_exists($webp_path)) {
            return $image;
        }
        
        // Get upload directory
        $upload_dir = wp_upload_dir();
        $relative_path = str_replace($upload_dir['basedir'], '', $attachment_path);
        
        // Replace the image URL with WebP version
        $image[0] = $upload_dir['baseurl'] . '/webp-cp' . $relative_path . '.webp';
        
        return $image;
    }
    
    /**
     * Regenerate attachment metadata for proper display
     *
     * @param int $attachment_id The attachment ID
     * @param string $file_path The file path
     * @return void
     */
    private function regenerate_attachment_metadata($attachment_id, $file_path) {
        // Include WordPress image functions
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        
        // Generate new metadata for the WebP file
        $new_metadata = wp_generate_attachment_metadata($attachment_id, $file_path);
        
        // Update the metadata
        if ($new_metadata) {
            wp_update_attachment_metadata($attachment_id, $new_metadata);
        }
        
        // Clear any cached data
        wp_cache_delete($attachment_id, 'posts');
        clean_post_cache($attachment_id);
    }
    
    /**
     * Convert memory limit string to bytes
     *
     * @param string $memory_limit Memory limit string (e.g., "128M", "256MB")
     * @return int Memory limit in bytes
     */
    private function convert_to_bytes($memory_limit) {
        $memory_limit = trim($memory_limit);
        $last = strtolower($memory_limit[strlen($memory_limit) - 1]);
        $value = (int) $memory_limit;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * Validate and get attachment file path
     *
     * @param int $attachment_id The attachment ID
     * @return string|false The file path on success, false on failure
     */
    private function validate_and_get_attachment_path($attachment_id) {
        $attachment_path = get_attached_file($attachment_id);
        
        if (!$attachment_path || !file_exists($attachment_path)) {
            // Try to find the file in uploads directory using metadata
            $upload_dir = wp_upload_dir();
            $upload_path = $upload_dir['basedir'];
            
            $metadata = wp_get_attachment_metadata($attachment_id);
            if ($metadata && isset($metadata['file'])) {
                $correct_path = $upload_path . '/' . $metadata['file'];
                
                if (file_exists($correct_path)) {
                    update_attached_file($attachment_id, $correct_path);
                    return $correct_path;
                }
            }
            
            $this->log_conversion($attachment_id, '', '', __('Failed - File not found', 'soovex-webp-converter'));
            return false;
        }
        
        return $attachment_path;
    }
    
    /**
     * Create backup if backup is enabled
     *
     * @param int $attachment_id The attachment ID
     * @param string $original_file_name Original file name for logging
     * @param string $webp_file_name WebP file name for logging
     * @return bool True on success, false on failure
     */
    private function create_backup_if_enabled($attachment_id, $original_file_name, $webp_file_name) {
        $backup_enabled = get_option('webp_cp_enable_backup', 1);
        
        if ($backup_enabled) {
            $backup = WebP_CP_Backup::get_instance();
            $backup_result = $backup->create_backup($attachment_id);
            if (!$backup_result) {
                $this->log_conversion($attachment_id, $original_file_name, $webp_file_name, __('Failed - Could not create backup', 'soovex-webp-converter'));
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate file for conversion (readable, size, memory)
     *
     * @param string $attachment_path The file path
     * @param int $attachment_id The attachment ID
     * @param string $original_file_name Original file name for logging
     * @param string $webp_file_name WebP file name for logging
     * @return bool True if valid, false otherwise
     */
    private function validate_file_for_conversion($attachment_path, $attachment_id, $original_file_name, $webp_file_name) {
        if (!is_readable($attachment_path)) {
            $this->log_conversion($attachment_id, $original_file_name, $webp_file_name, __('Failed - File not readable', 'soovex-webp-converter'));
            return false;
        }
        
        $file_size = filesize($attachment_path);
        if ($file_size > WEBP_CP_MAX_FILE_SIZE) {
            $this->log_conversion($attachment_id, $original_file_name, $webp_file_name, __('Failed - File too large', 'soovex-webp-converter'));
            return false;
        }
        
        // Check available PHP memory
        $memory_limit = ini_get('memory_limit');
        $memory_limit_bytes = $this->convert_to_bytes($memory_limit);
        $memory_usage = memory_get_usage(true);
        $available_memory = $memory_limit_bytes - $memory_usage;
        $estimated_memory_needed = $file_size * WEBP_CP_MEMORY_MULTIPLIER;
        
        if ($estimated_memory_needed > $available_memory) {
            $this->log_conversion($attachment_id, $original_file_name, $webp_file_name, __('Failed - Insufficient memory', 'soovex-webp-converter'));
            return false;
        }
        
        return true;
    }
    
    /**
     * Convert image file to WebP format
     *
     * @param string $source_path Source image path
     * @param string $destination_path Destination WebP path
     * @param string $file_ext File extension (jpg, jpeg, or png)
     * @param int $quality Compression quality (0-100)
     * @param int $attachment_id Attachment ID for logging
     * @param string $original_file_name Original file name for logging
     * @param string $webp_file_name WebP file name for logging
     * @return bool True on success, false on failure
     */
    private function convert_image_to_webp($source_path, $destination_path, $file_ext, $quality, $attachment_id, $original_file_name, $webp_file_name) {
        $image = null;
        
        try {
            if ($file_ext === 'png') {
                $image = imagecreatefrompng($source_path);
                if (!$image) {
                    if ($attachment_id > 0) {
                        $this->log_conversion($attachment_id, $original_file_name, $webp_file_name, __('Failed - Could not load PNG image', 'soovex-webp-converter'));
                    }
                    return false;
                }
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            } else {
                $image = imagecreatefromjpeg($source_path);
                if (!$image) {
                    if ($attachment_id > 0) {
                        $this->log_conversion($attachment_id, $original_file_name, $webp_file_name, __('Failed - Could not load JPEG image', 'soovex-webp-converter'));
                    }
                    return false;
                }
            }
            
            $result = imagewebp($image, $destination_path, $quality);
            
            if (!$result) {
                if ($attachment_id > 0) {
                    $error_msg = $file_ext === 'png' 
                        ? __('Failed - Could not create WebP from PNG', 'soovex-webp-converter')
                        : __('Failed - Could not create WebP from JPEG', 'soovex-webp-converter');
                    $this->log_conversion($attachment_id, $original_file_name, $webp_file_name, $error_msg);
                }
                return false;
            }
        } finally {
            if ($image) {
                unset($image);
            }
        }
        
        return file_exists($destination_path);
    }
    
    /**
     * Move WebP file to final location
     *
     * @param string $source_path Source WebP file path
     * @param string $destination_path Destination path
     * @param int $attachment_id Attachment ID for logging
     * @param string $original_file_name Original file name for logging
     * @param string $webp_file_name WebP file name for logging
     * @return bool True on success, false on failure
     */
    private function move_webp_file($source_path, $destination_path, $attachment_id, $original_file_name, $webp_file_name) {
        $max_attempts = 3;
        $move_attempts = 0;
        
        while ($move_attempts < $max_attempts) {
            if (rename($source_path, $destination_path)) {
                return true;
            }
            
            $move_attempts++;
            if ($move_attempts >= $max_attempts) {
                if (file_exists($source_path)) {
                    @unlink($source_path);
                }
                $this->log_conversion($attachment_id, $original_file_name, $webp_file_name, sprintf(__('Failed - Could not move WebP file after %d attempts', 'soovex-webp-converter'), $max_attempts));
                return false;
            }
            
            usleep(100000); // 0.1 seconds
        }
        
        return false;
    }
    
    /**
     * Process size variants (thumbnails)
     *
     * @param array $metadata Attachment metadata
     * @param string $original_attachment_path Main attachment original path
     * @param string $target_webp_path Main converted WebP path
     * @param string $file_ext File extension
     * @param int $quality Compression quality
     * @param int $attachment_id Attachment ID
     * @return array Updated metadata
     */
    private function process_size_variants($metadata, $original_attachment_path, $target_webp_path, $file_ext, $quality, $attachment_id = 0) {
        $new_metadata = is_array($metadata) ? $metadata : array();
        
        $upload_dir = wp_upload_dir();
        $relative_file = str_replace($upload_dir['basedir'] . '/', '', $target_webp_path);
        $new_metadata['file'] = $relative_file;
        
        if (!isset($metadata['sizes']) || empty($metadata['sizes']) || !is_array($metadata['sizes'])) {
            return $new_metadata;
        }
        
        $base_dir = dirname($target_webp_path);
        $new_metadata['sizes'] = array();
        
        foreach ($metadata['sizes'] as $size => $size_data) {
            if (!isset($size_data['file'])) {
                continue;
            }
            
            $orig_size_filename = $size_data['file'];
            $orig_size_path = dirname($original_attachment_path) . '/' . $orig_size_filename;
            
            // Determine WebP thumbnail filename
            $size_base = pathinfo($orig_size_filename, PATHINFO_FILENAME);
            $size_webp_filename = $size_base . '.webp';
            $size_webp_path = $base_dir . '/' . $size_webp_filename;
            
            // Check for collision with existing files from different attachments
            if (file_exists($size_webp_path) && $size_webp_path !== $orig_size_path) {
                if (!function_exists('wp_unique_filename')) {
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                }
                $size_webp_filename = wp_unique_filename($base_dir, $size_base . '.webp');
                $size_webp_path = $base_dir . '/' . $size_webp_filename;
            }
            
            // Find source thumbnail file (from disk or from backup)
            $source_size_path = file_exists($orig_size_path) ? $orig_size_path : '';
            if (!$source_size_path && $attachment_id > 0) {
                $backup_size = WebP_CP_Backup::get_instance()->get_backup_dir($attachment_id) . '/' . $orig_size_filename;
                if (file_exists($backup_size)) {
                    $source_size_path = $backup_size;
                }
            }
            
            if ($source_size_path && file_exists($source_size_path)) {
                $temp_thumb_path = $size_webp_path . '.tmp';
                if ($this->convert_image_to_webp($source_size_path, $temp_thumb_path, $file_ext, $quality, 0, '', '')) {
                    if (file_exists($temp_thumb_path)) {
                        rename($temp_thumb_path, $size_webp_path);
                    }
                    
                    // Remove original thumbnail on disk if target WebP thumbnail exists and is distinct
                    if (file_exists($orig_size_path) && $orig_size_path !== $size_webp_path) {
                        @unlink($orig_size_path);
                    }
                    
                    $new_size_data = $size_data;
                    $new_size_data['file'] = $size_webp_filename;
                    $new_size_data['mime-type'] = 'image/webp';
                    $new_metadata['sizes'][$size] = $new_size_data;
                }
            }
        }
        
        return $new_metadata;
    }
    
    /**
     * Update attachment in database to WebP format
     *
     * @param int $attachment_id The attachment ID
     * @param string $file_path New file path
     * @param array $metadata New metadata
     * @return void
     */
    private function update_attachment_to_webp($attachment_id, $file_path, $metadata) {
        $attachment_post = array(
            'ID' => $attachment_id,
            'post_mime_type' => 'image/webp'
        );
        wp_update_post($attachment_post);
        wp_update_attachment_metadata($attachment_id, $metadata);
        update_attached_file($attachment_id, $file_path);
        
        // Clear caches
        wp_cache_delete($attachment_id, 'posts');
        clean_post_cache($attachment_id);
    }
}