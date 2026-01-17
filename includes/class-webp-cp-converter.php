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
        $result = $this->convert_image($attachment_id);
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
        
        // Check if image is already converted (Bug #9)
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
        $webp_file_name = $original_file_name . '.webp';
        
        // Create backup if enabled
        if (!$this->create_backup_if_enabled($attachment_id, $original_file_name, $webp_file_name)) {
            return false;
        }
        
        // Validate file and check resources
        if (!$this->validate_file_for_conversion($attachment_path, $attachment_id, $original_file_name, $webp_file_name)) {
            return false;
        }
        
        // Convert main image
        $webp_path = $attachment_path . '.webp';
        $quality = get_option('webp_cp_compression_quality', 82);
        
        if (!$this->convert_image_to_webp($attachment_path, $webp_path, $file_ext, $quality, $attachment_id, $original_file_name, $webp_file_name)) {
            return false;
        }
        
        // Move and update main file
        $new_webp_path = str_replace('.' . $file_ext, '.webp', $attachment_path);
        if (!$this->move_webp_file($webp_path, $new_webp_path, $attachment_id, $original_file_name, $webp_file_name)) {
            return false;
        }
        
        // Process size variants
        $new_metadata = $this->process_size_variants($metadata, $attachment_path, $file_ext, $quality);
        
        // Update attachment in database
        $this->update_attachment_to_webp($attachment_id, $new_webp_path, $new_metadata);
        
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
        // Get backup instance
        $backup = WebP_CP_Backup::get_instance();
        
        // Get attachment path
        $attachment_path = get_attached_file($attachment_id);
        
        // Check if the attachment exists
        if (!file_exists($attachment_path)) {
            $this->log_conversion($attachment_id, '', '', __('Failed - File not found', 'soovex-webp-converter'));
            return false;
        }
        
        // Check if backup exists
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/webp-cp-backups';
        
        // For converted WebP files, we need to find the original backup
        // The backup was created with the original filename before conversion
        $base_name = pathinfo($attachment_path, PATHINFO_FILENAME);
        $original_extensions = array('jpg', 'jpeg', 'png');
        $backup_file_path = null;
        $original_file_ext = null;
        
        // Try to find backup with original extensions
        foreach ($original_extensions as $ext) {
            $test_backup_path = $backup_dir . '/' . $base_name . '.' . $ext;
            if (file_exists($test_backup_path)) {
                $backup_file_path = $test_backup_path;
                $original_file_ext = $ext;
                break;
            }
        }
        
        // Also check if there's a backup with the same name (in case backup was created after conversion)
        if (!$backup_file_path) {
            $test_backup_path = $backup_dir . '/' . basename($attachment_path);
            if (file_exists($test_backup_path)) {
                $backup_file_path = $test_backup_path;
                $original_file_ext = strtolower(pathinfo($backup_file_path, PATHINFO_EXTENSION));
            }
        }
        
        if (!$backup_file_path || !file_exists($backup_file_path)) {
            $this->log_conversion($attachment_id, '', '', __('Failed - No backup found', 'soovex-webp-converter'));
            return false;
        }
        
        
        // Create new original file path
        $new_original_path = str_replace('.webp', '.' . $original_file_ext, $attachment_path);
        
        // Restore original file from backup
        if (!copy($backup_file_path, $new_original_path)) {
            $this->log_conversion($attachment_id, '', '', __('Failed - Could not restore from backup', 'soovex-webp-converter'));
            return false;
        }
        
        // Update attachment metadata to reflect original format
        $metadata = wp_get_attachment_metadata($attachment_id);
        $new_metadata = $metadata;
        $new_metadata['file'] = str_replace('webp', $original_file_ext, $metadata['file']);
        $new_metadata['sizes'] = array();
        
        // Process size variants
        if (isset($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size => $size_data) {
                // Get size file path
                $size_path = pathinfo($attachment_path, PATHINFO_DIRNAME) . '/' . $size_data['file'];
                
                // Get size backup file path
                $size_backup_file_path = $backup_dir . '/' . $size_data['file'];
                
                // Restore size file from backup
                if (file_exists($size_backup_file_path)) {
                    copy($size_backup_file_path, $size_path);
                }
                
                // Update size metadata
                $new_size_data = $size_data;
                $new_size_data['file'] = str_replace('webp', $original_file_ext, $size_data['file']);
                $new_size_data['mime-type'] = 'image/' . ($original_file_ext === 'jpg' || $original_file_ext === 'jpeg' ? 'jpeg' : $original_file_ext);
                $new_metadata['sizes'][$size] = $new_size_data;
            }
        }
        
        // Update attachment post
        $attachment_post = array(
            'ID' => $attachment_id,
            'post_mime_type' => 'image/' . ($original_file_ext === 'jpg' || $original_file_ext === 'jpeg' ? 'jpeg' : $original_file_ext)
        );
        wp_update_post($attachment_post);
        
        // Update attachment metadata
        wp_update_attachment_metadata($attachment_id, $new_metadata);
        
        // Update attached file path
        update_attached_file($attachment_id, $new_original_path);
        
        // Regenerate attachment metadata to ensure proper display
        $this->regenerate_attachment_metadata($attachment_id, $new_original_path);
        
        // Delete the WebP file
        if (file_exists($attachment_path)) {
            unlink($attachment_path);
        }
        
        // Get original file name
        $original_file_name = basename($attachment_path);
        
        // Get WebP file name
        $webp_file_name = $original_file_name . '.webp';
        
        // Log the reversion
        $this->log_conversion($attachment_id, $original_file_name, $webp_file_name, __('Reverted', 'soovex-webp-converter'));
        
        // Clean up backup files after successful reversion
        $this->cleanup_backup_files($attachment_id);
        
        return true;
    }
    
    /**
     * Clean up backup files for an attachment
     *
     * @param int $attachment_id The attachment ID
     * @return void
     */
    private function cleanup_backup_files($attachment_id) {
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/webp-cp-backups';
        
        if (!is_dir($backup_dir)) {
            return;
        }
        
        // Get attachment metadata to find all related files
        $metadata = wp_get_attachment_metadata($attachment_id);
        $attachment_path = get_attached_file($attachment_id);
        
        if (!$attachment_path) {
            return;
        }
        
        $files_to_cleanup = array();
        
        // Add main file
        $files_to_cleanup[] = basename($attachment_path);
        
        // Add size variants
        if (isset($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size => $size_data) {
                $files_to_cleanup[] = $size_data['file'];
            }
        }
        
        // Remove backup files
        foreach ($files_to_cleanup as $filename) {
            // Sanitize filename for security
            $safe_filename = sanitize_file_name($filename);
            $backup_file_path = $backup_dir . '/' . $safe_filename;
            
            if (file_exists($backup_file_path)) {
                if (unlink($backup_file_path)) {
                } else {
                }
            }
        }
        
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
                    // Look for the WebP version with the same base name
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
            // This is already a WebP image, return as is
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
            // Try to find the file in uploads directory
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
                    $this->log_conversion($attachment_id, $original_file_name, $webp_file_name, __('Failed - Could not load PNG image', 'soovex-webp-converter'));
                    return false;
                }
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            } else {
                $image = imagecreatefromjpeg($source_path);
                if (!$image) {
                    $this->log_conversion($attachment_id, $original_file_name, $webp_file_name, __('Failed - Could not load JPEG image', 'soovex-webp-converter'));
                    return false;
                }
            }
            
            $result = imagewebp($image, $destination_path, $quality);
            
            if (!$result) {
                $error_msg = $file_ext === 'png' 
                    ? __('Failed - Could not create WebP from PNG', 'soovex-webp-converter')
                    : __('Failed - Could not create WebP from JPEG', 'soovex-webp-converter');
                $this->log_conversion($attachment_id, $original_file_name, $webp_file_name, $error_msg);
                return false;
            }
        } finally {
            // Clean up image resource - PHP will automatically handle garbage collection
            // imagedestroy() is deprecated in PHP 8.0+ for GdImage objects
            // We let PHP handle cleanup automatically for both resources and GdImage objects
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
                    unlink($source_path);
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
     * @param string $attachment_path Main attachment path
     * @param string $file_ext File extension
     * @param int $quality Compression quality
     * @return array Updated metadata
     */
    private function process_size_variants($metadata, $attachment_path, $file_ext, $quality) {
        $new_metadata = $metadata;
        $new_metadata['file'] = str_replace($file_ext, 'webp', $metadata['file']);
        $new_metadata['sizes'] = array();
        
        if (!isset($metadata['sizes']) || empty($metadata['sizes'])) {
            return $new_metadata;
        }
        
        foreach ($metadata['sizes'] as $size => $size_data) {
            $size_path = pathinfo($attachment_path, PATHINFO_DIRNAME) . '/' . $size_data['file'];
            $size_webp_path = $size_path . '.webp';
            
            if ($this->convert_image_to_webp($size_path, $size_webp_path, $file_ext, $quality, 0, '', '')) {
                if (file_exists($size_webp_path)) {
                    rename($size_webp_path, $size_path);
                }
                
                $new_size_data = $size_data;
                $new_size_data['file'] = str_replace($file_ext, 'webp', $size_data['file']);
                $new_size_data['mime-type'] = 'image/webp';
                $new_metadata['sizes'][$size] = $new_size_data;
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
        $this->regenerate_attachment_metadata($attachment_id, $file_path);
    }
}