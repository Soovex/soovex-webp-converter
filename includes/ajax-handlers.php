<?php
/**
 * AJAX handlers for the plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get stats
add_action('wp_ajax_webp_cp_get_stats', 'webp_cp_ajax_get_stats');
function webp_cp_ajax_get_stats() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Get stats
    $images_in_media = count(webp_cp_get_all_images());
    $compressed_images = count(webp_cp_get_converted_images());
    $storage_saved = webp_cp_get_storage_saved();
    
    // Generate stats HTML
    ob_start();
    ?>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-black/5 dark:border-white/5">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Images in Media</h3>
                <p class="text-2xl font-bold"><?php echo esc_html($images_in_media); ?></p>
            </div>
            <div class="size-12 text-primary/20">
                <span class="material-symbols-outlined text-3xl">image</span>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-black/5 dark:border-white/5">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Compressed Images</h3>
                <p class="text-2xl font-bold"><?php echo esc_html($compressed_images); ?></p>
            </div>
            <div class="size-12 text-primary/20">
                <span class="material-symbols-outlined text-3xl">compress</span>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-black/5 dark:border-white/5">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Storage Saved</h3>
                <p class="text-2xl font-bold text-primary"><?php echo esc_html(webp_cp_format_filesize($storage_saved)); ?></p>
            </div>
            <div class="size-12 text-primary/20">
                <span class="material-symbols-outlined text-3xl">data_saver_on</span>
            </div>
        </div>
    </div>
    <?php
    $stats_html = ob_get_clean();
    
    wp_send_json_success(array('stats_html' => $stats_html));
}

// Get activity logs
add_action('wp_ajax_webp_cp_get_activity_logs', 'webp_cp_ajax_get_activity_logs');
function webp_cp_ajax_get_activity_logs() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Get pagination parameters
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 20;
    
    // Get logs
    global $wpdb;
    $table_name = $wpdb->prefix . 'webp_cp_activity_log';
    
    // Check if table exists
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
    if (!$table_exists) {
        wp_send_json_error(array('message' => 'Activity log table does not exist. Please reactivate the plugin.'));
    }
    
    // Get total logs
    $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
    
    // Get logs for current page
    $offset = ($page - 1) * $per_page;
    $logs = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} ORDER BY date DESC LIMIT %d OFFSET %d", $per_page, $offset));
    
    // Generate logs HTML
    ob_start();
    if (empty($logs)) {
        ?>
        <tr>
            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No activity logs found.</td>
        </tr>
        <?php
    } else {
        foreach ($logs as $log) {
            include WEBP_CP_PATH . 'admin/templates/activity-log-row.php';
        }
    }
    $logs_html = ob_get_clean();
    
    // Calculate pagination
    $total_pages = ceil($total_logs / $per_page);
    
    wp_send_json_success(array(
        'logs_html' => $logs_html,
        'total_logs' => $total_logs,
        'total_pages' => $total_pages,
        'current_page' => $page
    ));
}

// Clear logs
add_action('wp_ajax_webp_cp_clear_logs', 'webp_cp_ajax_clear_logs');
function webp_cp_ajax_clear_logs() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Clear logs
    global $wpdb;
    $table_name = $wpdb->prefix . 'webp_cp_activity_log';
    
    // Check if table exists before truncating
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
    if (!$table_exists) {
        wp_send_json_error(array('message' => 'Activity log table does not exist.'));
    }
    
    $result = $wpdb->query("TRUNCATE TABLE `{$table_name}`");
    
    if ($result !== false) {
        wp_send_json_success(array('message' => 'Activity logs cleared successfully.'));
    } else {
        wp_send_json_error(array('message' => 'Failed to clear activity logs.'));
    }
}

// Convert single image
add_action('wp_ajax_webp_cp_convert_single', 'webp_cp_ajax_convert_single');
function webp_cp_ajax_convert_single() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Rate limiting (Bug #10 - IP-based instead of user-based)
    $user_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : 'unknown';
    // Also check for proxy/load balancer headers
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $forwarded_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $user_ip = sanitize_text_field(trim($forwarded_ips[0]));
    } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
        $user_ip = sanitize_text_field($_SERVER['HTTP_X_REAL_IP']);
    }
    
    $rate_limit_key = 'webp_cp_rate_limit_' . md5($user_ip);
    $rate_limit = get_transient($rate_limit_key);
    if ($rate_limit && $rate_limit > WEBP_CP_RATE_LIMIT) {
        wp_send_json_error(array('message' => 'Rate limit exceeded. Please try again later.'));
    }
    
    // Update rate limit
    $current_count = $rate_limit ? $rate_limit : 0;
    set_transient($rate_limit_key, $current_count + 1, HOUR_IN_SECONDS);
    
    // Get image ID
    $image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
    
    if (!$image_id) {
        wp_send_json_error(array('message' => 'Invalid image ID.'));
    }
    
    // Convert image
    $converter = WebP_CP_Converter::get_instance();
    $result = $converter->convert_image($image_id);
    
    if ($result) {
        wp_send_json_success(array('message' => 'Image converted successfully.'));
    } else {
        wp_send_json_error(array('message' => 'Failed to convert image.'));
    }
}

// Convert multiple images
add_action('wp_ajax_webp_cp_convert_multiple', 'webp_cp_ajax_convert_multiple');
function webp_cp_ajax_convert_multiple() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Get image IDs
    $image_ids = isset($_POST['image_ids']) ? sanitize_text_field($_POST['image_ids']) : '';
    
    if (empty($image_ids)) {
        wp_send_json_error(array('message' => 'No images selected.'));
    }
    
    // Convert to array
    $image_ids = explode(',', $image_ids);
    
    // Store conversion progress
    $progress_key = 'webp_cp_conversion_progress_' . get_current_user_id();
    $progress_data = array(
        'total' => count($image_ids),
        'completed' => 0,
        'success' => 0,
        'failed' => 0,
        'current_image' => '',
        'status' => 'processing', // Status: processing, paused, stopped, completed
        'all_images' => $image_ids // Store all images for resume functionality
    );
    set_transient($progress_key, $progress_data, 300); // 5 minutes
    
    // Schedule conversion in batches
    $batch_size = WEBP_CP_BATCH_SIZE_SMALL;
    $batches = array_chunk($image_ids, $batch_size);
    
    foreach ($batches as $index => $batch) {
        wp_schedule_single_event(time() + ($index * 2), 'webp_cp_convert_batch_progress', array($batch, $progress_key));
    }
    
    wp_send_json_success(array(
        'message' => 'Conversion started. Processing ' . count($image_ids) . ' images...',
        'progress_key' => $progress_key,
        'total' => count($image_ids)
    ));
}

// Convert batch with progress tracking
add_action('webp_cp_convert_batch_progress', 'webp_cp_ajax_convert_batch_progress', 10, 2);
function webp_cp_ajax_convert_batch_progress($image_ids, $progress_key) {
    $converter = WebP_CP_Converter::get_instance();
    $progress_data = get_transient($progress_key);
    
    if (!$progress_data) {
        return;
    }
    
    // Check if conversion is paused or stopped
    if (isset($progress_data['status']) && ($progress_data['status'] === 'paused' || $progress_data['status'] === 'stopped')) {
        return; // Don't process if paused or stopped
    }
    
    // Ensure status is set to processing
    $progress_data['status'] = 'processing';
    set_transient($progress_key, $progress_data, 300);
    
    foreach ($image_ids as $image_id) {
        // Check status before each image (allows pause/stop during processing)
        $current_progress = get_transient($progress_key);
        if (!$current_progress) {
            return; // Progress data lost
        }
        
        if (isset($current_progress['status'])) {
            if ($current_progress['status'] === 'stopped') {
                return; // Stop processing immediately
            }
            if ($current_progress['status'] === 'paused') {
                return; // Exit immediately when paused - resume will trigger new batch
            }
        }
        
        $image_id = intval($image_id);
        if ($image_id) {
            // Update current image
            $progress_data['current_image'] = get_the_title($image_id);
            set_transient($progress_key, $progress_data, 300);
            
            // Convert image
            if ($converter->convert_image($image_id)) {
                $progress_data['success']++;
            } else {
                $progress_data['failed']++;
            }
            
            $progress_data['completed']++;
            set_transient($progress_key, $progress_data, 300);
        }
    }
    
    // Check if all images are processed (only if not stopped)
    if ($progress_data['completed'] >= $progress_data['total'] && (!isset($progress_data['status']) || $progress_data['status'] !== 'stopped')) {
        $progress_data['status'] = 'completed';
        set_transient($progress_key, $progress_data, 300);
    }
}

// Get conversion progress
add_action('wp_ajax_webp_cp_get_conversion_progress', 'webp_cp_ajax_get_conversion_progress');
function webp_cp_ajax_get_conversion_progress() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Get progress key
    $progress_key = isset($_POST['progress_key']) ? sanitize_text_field($_POST['progress_key']) : '';
    
    if (empty($progress_key)) {
        wp_send_json_error(array('message' => 'Invalid progress key.'));
    }
    
    $progress_data = get_transient($progress_key);
    
    if (!$progress_data) {
        wp_send_json_error(array('message' => 'Progress data not found.'));
    }
    
    // Ensure status is set (default to processing if not set)
    if (!isset($progress_data['status'])) {
        $progress_data['status'] = 'processing';
    }
    
    wp_send_json_success($progress_data);
}

// Pause conversion
add_action('wp_ajax_webp_cp_pause_conversion', 'webp_cp_ajax_pause_conversion');
function webp_cp_ajax_pause_conversion() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Get progress key
    $progress_key = isset($_POST['progress_key']) ? sanitize_text_field($_POST['progress_key']) : '';
    
    if (empty($progress_key)) {
        wp_send_json_error(array('message' => 'Invalid progress key.'));
    }
    
    $progress_data = get_transient($progress_key);
    
    if (!$progress_data) {
        wp_send_json_error(array('message' => 'Progress data not found.'));
    }
    
    // Set status to paused
    $progress_data['status'] = 'paused';
    set_transient($progress_key, $progress_data, 300);
    
    // Unschedule all future batch conversion events
    $cron_array = _get_cron_array();
    if ($cron_array) {
        foreach ($cron_array as $timestamp => $cron) {
            if (isset($cron['webp_cp_convert_batch_progress'])) {
                foreach ($cron['webp_cp_convert_batch_progress'] as $hook_key => $hook_data) {
                    // Check if this event matches our progress key
                    if (isset($hook_data['args'][1]) && $hook_data['args'][1] === $progress_key) {
                        wp_unschedule_event($timestamp, 'webp_cp_convert_batch_progress', $hook_data['args']);
                    }
                }
            }
        }
    }
    
    wp_send_json_success(array('message' => __('Conversion paused successfully.', 'soovex-webp-converter')));
}

// Resume conversion
add_action('wp_ajax_webp_cp_resume_conversion', 'webp_cp_ajax_resume_conversion');
function webp_cp_ajax_resume_conversion() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Get progress key
    $progress_key = isset($_POST['progress_key']) ? sanitize_text_field($_POST['progress_key']) : '';
    
    if (empty($progress_key)) {
        wp_send_json_error(array('message' => 'Invalid progress key.'));
    }
    
    $progress_data = get_transient($progress_key);
    
    if (!$progress_data) {
        wp_send_json_error(array('message' => 'Progress data not found.'));
    }
    
    // Only resume if currently paused
    if (isset($progress_data['status']) && $progress_data['status'] === 'paused') {
        // Check if there are remaining images
        $remaining = $progress_data['total'] - $progress_data['completed'];
        if ($remaining > 0) {
            // Get remaining images from stored list
            if (!isset($progress_data['all_images']) || empty($progress_data['all_images'])) {
                wp_send_json_error(array('message' => __('Cannot resume: image list not found.', 'soovex-webp-converter')));
                return;
            }
            
            // Calculate which images still need conversion
            $completed_count = isset($progress_data['completed']) ? $progress_data['completed'] : 0;
            $remaining_images = array_slice($progress_data['all_images'], $completed_count);
            
            if (!empty($remaining_images)) {
                // Clear any existing scheduled events for this progress key first
                $cron_array = _get_cron_array();
                if ($cron_array) {
                    foreach ($cron_array as $timestamp => $cron) {
                        if (isset($cron['webp_cp_convert_batch_progress'])) {
                            foreach ($cron['webp_cp_convert_batch_progress'] as $hook_key => $hook_data) {
                                if (isset($hook_data['args'][1]) && $hook_data['args'][1] === $progress_key) {
                                    wp_unschedule_event($timestamp, 'webp_cp_convert_batch_progress', $hook_data['args']);
                                }
                            }
                        }
                    }
                }
                
                // Schedule remaining batches
                $batch_size = WEBP_CP_BATCH_SIZE_SMALL;
                $batches = array_chunk($remaining_images, $batch_size);
                
                foreach ($batches as $index => $batch) {
                    wp_schedule_single_event(time() + ($index * 2), 'webp_cp_convert_batch_progress', array($batch, $progress_key));
                }
                
                // Set status back to processing
                $progress_data['status'] = 'processing';
                set_transient($progress_key, $progress_data, 300);
                
                // Trigger cron immediately to start processing
                spawn_cron();
                
                wp_send_json_success(array(
                    'message' => __('Conversion resumed. Processing will continue...', 'soovex-webp-converter'),
                    'status' => 'processing'
                ));
            } else {
                // All done
                $progress_data['status'] = 'completed';
                set_transient($progress_key, $progress_data, 300);
                wp_send_json_success(array('message' => __('All images have been processed.', 'soovex-webp-converter')));
            }
        } else {
            // All done
            $progress_data['status'] = 'completed';
            set_transient($progress_key, $progress_data, 300);
            wp_send_json_success(array('message' => __('All images have been processed.', 'soovex-webp-converter')));
        }
    } else {
        wp_send_json_error(array('message' => __('Conversion is not paused.', 'soovex-webp-converter')));
    }
}

// Stop conversion
add_action('wp_ajax_webp_cp_stop_conversion', 'webp_cp_ajax_stop_conversion');
function webp_cp_ajax_stop_conversion() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Get progress key
    $progress_key = isset($_POST['progress_key']) ? sanitize_text_field($_POST['progress_key']) : '';
    
    if (empty($progress_key)) {
        wp_send_json_error(array('message' => 'Invalid progress key.'));
    }
    
    $progress_data = get_transient($progress_key);
    
    if (!$progress_data) {
        wp_send_json_error(array('message' => 'Progress data not found.'));
    }
    
    // Set status to stopped
    $progress_data['status'] = 'stopped';
    set_transient($progress_key, $progress_data, 300);
    
    // Unschedule all future batch conversion events
    $cron_array = _get_cron_array();
    if ($cron_array) {
        foreach ($cron_array as $timestamp => $cron) {
            if (isset($cron['webp_cp_convert_batch_progress'])) {
                foreach ($cron['webp_cp_convert_batch_progress'] as $hook_key => $hook_data) {
                    // Check if this event matches our progress key
                    if (isset($hook_data['args'][1]) && $hook_data['args'][1] === $progress_key) {
                        wp_unschedule_event($timestamp, 'webp_cp_convert_batch_progress', $hook_data['args']);
                    }
                }
            }
        }
    }
    
    wp_send_json_success(array(
        'message' => __('Conversion stopped successfully.', 'soovex-webp-converter'),
        'completed' => isset($progress_data['completed']) ? $progress_data['completed'] : 0,
        'success' => isset($progress_data['success']) ? $progress_data['success'] : 0,
        'failed' => isset($progress_data['failed']) ? $progress_data['failed'] : 0
    ));
}

// Convert by URL
add_action('wp_ajax_webp_cp_convert_url', 'webp_cp_ajax_convert_url');
function webp_cp_ajax_convert_url() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Get image URL
    $image_url = isset($_POST['image_url']) ? esc_url_raw($_POST['image_url']) : '';
    
    if (empty($image_url)) {
        wp_send_json_error(array('message' => 'Invalid image URL.'));
    }
    
    // Validate URL
    if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
        wp_send_json_error(array('message' => 'Invalid image URL format.'));
    }
    
    // Download image with error handling
    $response = wp_remote_get($image_url, array(
        'timeout' => 30,
        'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
    ));
    
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'Failed to download image: ' . $response->get_error_message()));
    }
    
    $image_data = wp_remote_retrieve_body($response);
    
    if (empty($image_data)) {
        wp_send_json_error(array('message' => 'Failed to download image.'));
    }
    
    // Get file info
    $file_info = pathinfo($image_url);
    $file_ext = strtolower($file_info['extension']);
    
    // Check if the file is JPG or PNG
    if (!in_array($file_ext, array('jpg', 'jpeg', 'png'))) {
        wp_send_json_error(array('message' => 'Only JPG and PNG images are supported.'));
    }
    
    // Additional validation: Check if the downloaded data is actually an image
    $image_info = getimagesizefromstring($image_data);
    if (!$image_info || !in_array($image_info[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
        wp_send_json_error(array('message' => 'Downloaded file is not a valid JPG or PNG image.'));
    }
    
    // Check file size limit
    if (strlen($image_data) > WEBP_CP_MAX_FILE_SIZE) {
        wp_send_json_error(array('message' => 'Downloaded file is too large. Maximum size is 50MB.'));
    }
    
    // Generate unique filename
    $filename = wp_unique_filename(wp_upload_dir()['path'], $file_info['basename']);
    $filepath = wp_upload_dir()['path'] . '/' . $filename;
    
    // Save image
    if (file_put_contents($filepath, $image_data) === false) {
        wp_send_json_error(array('message' => 'Failed to save image to server.'));
    }
    
    // Insert attachment
    $attachment = array(
        'post_mime_type' => 'image/' . ($file_ext === 'jpg' || $file_ext === 'jpeg' ? 'jpeg' : 'png'),
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    
    $attachment_id = wp_insert_attachment($attachment, $filepath);
    
    if (!$attachment_id || is_wp_error($attachment_id)) {
        // Clean up file if attachment creation failed
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        wp_send_json_error(array('message' => 'Failed to create attachment.'));
    }
    
    // Generate attachment metadata
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attachment_data = wp_generate_attachment_metadata($attachment_id, $filepath);
    wp_update_attachment_metadata($attachment_id, $attachment_data);
    
    // Convert image
    $converter = WebP_CP_Converter::get_instance();
    $result = $converter->convert_image($attachment_id);
    
    if ($result) {
        wp_send_json_success(array('message' => 'Image converted successfully.'));
    } else {
        wp_send_json_error(array('message' => 'Failed to convert image.'));
    }
}

// Convert all images
add_action('wp_ajax_webp_cp_convert_all', 'webp_cp_ajax_convert_all');
function webp_cp_ajax_convert_all() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Get all images
    $images = webp_cp_get_all_images();
    
    if (empty($images)) {
        wp_send_json_error(array('message' => 'No images found in media library.'));
    }
    
    // Schedule background conversion
    $batch_size = WEBP_CP_BATCH_SIZE_LARGE;
    $batches = array_chunk($images, $batch_size);
    
    foreach ($batches as $index => $batch) {
        wp_schedule_single_event(time() + ($index * 30), 'webp_cp_convert_batch', array($batch));
    }
    
    wp_send_json_success(array('message' => 'Conversion process started. Images will be converted in the background.'));
}

// Convert all images with progress tracking
add_action('wp_ajax_webp_cp_convert_all_with_progress', 'webp_cp_ajax_convert_all_with_progress');

// Convert all images immediately (fallback)
add_action('wp_ajax_webp_cp_convert_all_immediate', 'webp_cp_ajax_convert_all_immediate');
function webp_cp_ajax_convert_all_with_progress() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Get all images
    $images = webp_cp_get_all_images();
    
    if (empty($images)) {
        wp_send_json_error(array('message' => 'No images found in media library.'));
    }
    
    // Store conversion progress
    $progress_key = 'webp_cp_conversion_progress_' . get_current_user_id();
    $progress_data = array(
        'total' => count($images),
        'completed' => 0,
        'success' => 0,
        'failed' => 0,
        'current_image' => '',
        'status' => 'processing',
        'all_images' => $images // Store all images for resume functionality
    );
    set_transient($progress_key, $progress_data, 300); // 5 minutes
    
    // Schedule conversion in batches
    $batch_size = WEBP_CP_BATCH_SIZE_SMALL;
    $batches = array_chunk($images, $batch_size);
    
    foreach ($batches as $index => $batch) {
        wp_schedule_single_event(time() + ($index * 2), 'webp_cp_convert_batch_progress', array($batch, $progress_key));
    }
    
    // If no cron jobs are scheduled, start immediate processing
    if (!wp_next_scheduled('webp_cp_convert_batch_progress')) {
        // Process first batch immediately
        if (!empty($batches)) {
            wp_schedule_single_event(time(), 'webp_cp_convert_batch_progress', array($batches[0], $progress_key));
        }
    }
    
    // Trigger WordPress cron to ensure scheduled events run
    spawn_cron();
    
    wp_send_json_success(array(
        'message' => 'Conversion started. Processing ' . count($images) . ' images...',
        'progress_key' => $progress_key,
        'total' => count($images)
    ));
}

// Convert all images immediately (fallback)
function webp_cp_ajax_convert_all_immediate() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Get all images
    $images = webp_cp_get_all_images();
    
    if (empty($images)) {
        wp_send_json_error(array('message' => 'No images found in media library.'));
    }
    
    // Store conversion progress
    $progress_key = 'webp_cp_conversion_progress_' . get_current_user_id();
    $progress_data = array(
        'total' => count($images),
        'completed' => 0,
        'success' => 0,
        'failed' => 0,
        'current_image' => '',
        'status' => 'processing',
        'all_images' => $images // Store all images for resume functionality
    );
    set_transient($progress_key, $progress_data, 300); // 5 minutes
    
    // Process images immediately in batches
    $converter = WebP_CP_Converter::get_instance();
    $batch_size = WEBP_CP_BATCH_SIZE_SMALL;
    $batches = array_chunk($images, $batch_size);
    
    foreach ($batches as $batch) {
        // Check if stopped before processing batch
        $current_progress = get_transient($progress_key);
        if ($current_progress && isset($current_progress['status']) && $current_progress['status'] === 'stopped') {
            break; // Stop processing if stopped
        }
        
        foreach ($batch as $image_id) {
            // Check status before each image - exit immediately if paused or stopped
            $current_progress = get_transient($progress_key);
            if (!$current_progress) {
                break 2; // Progress data lost, exit
            }
            
            if (isset($current_progress['status'])) {
                if ($current_progress['status'] === 'stopped') {
                    break 2; // Break out of both loops immediately
                }
                if ($current_progress['status'] === 'paused') {
                    // Exit batch processing - wait will be handled by JS polling
                    return; // Return immediately, don't process any more images
                }
            }
            
            $image_id = intval($image_id);
            if ($image_id) {
                // Update current image
                $progress_data['current_image'] = get_the_title($image_id);
                $progress_data['status'] = 'processing';
                set_transient($progress_key, $progress_data, 300);
                
                // Convert image
                if ($converter->convert_image($image_id)) {
                    $progress_data['success']++;
                } else {
                    $progress_data['failed']++;
                }
                
                $progress_data['completed']++;
                set_transient($progress_key, $progress_data, 300);
            }
        }
        
        // Small delay between batches to prevent server overload
        usleep(WEBP_CP_DELAY_BETWEEN_BATCHES);
    }
    
    // Mark as completed only if not stopped
    $final_progress = get_transient($progress_key);
    if ($final_progress && (!isset($final_progress['status']) || $final_progress['status'] !== 'stopped')) {
        $progress_data['status'] = 'completed';
        set_transient($progress_key, $progress_data, 300);
        
        wp_send_json_success(array(
            'message' => 'Conversion completed. ' . $progress_data['success'] . ' images converted successfully.',
            'progress_key' => $progress_key,
            'total' => count($images)
        ));
    } else {
        // Was stopped
        wp_send_json_success(array(
            'message' => 'Conversion stopped. ' . $progress_data['completed'] . ' images processed.',
            'progress_key' => $progress_key,
            'total' => count($images)
        ));
    }
}

// Convert batch
add_action('webp_cp_convert_batch', 'webp_cp_ajax_convert_batch', 10, 1);
function webp_cp_ajax_convert_batch($image_ids) {
    $converter = WebP_CP_Converter::get_instance();
    $success_count = 0;
    $error_count = 0;
    
    foreach ($image_ids as $image_id) {
        $image_id = intval($image_id);
        if ($image_id && $converter->convert_image($image_id)) {
            $success_count++;
        } else {
            $error_count++;
        }
    }
    
    // Log batch completion
}

// Revert single image
add_action('wp_ajax_webp_cp_revert_single', 'webp_cp_ajax_revert_single');
function webp_cp_ajax_revert_single() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Get log ID
    $log_id = isset($_POST['log_id']) ? intval($_POST['log_id']) : 0;
    
    if (!$log_id) {
        wp_send_json_error(array('message' => 'Invalid log ID.'));
    }
    
    // Get log
    global $wpdb;
    $table_name = $wpdb->prefix . 'webp_cp_activity_log';
    $log = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$table_name}` WHERE id = %d", $log_id));
    
    if (!$log) {
        wp_send_json_error(array('message' => 'Log not found.'));
    }
    
    // Get attachment ID from the log
    $attachment_id = intval($log->attachment_id);
    
    if (!$attachment_id) {
        wp_send_json_error(array('message' => 'Invalid attachment ID.'));
    }
    
    // Verify attachment exists
    if (!get_post($attachment_id)) {
        wp_send_json_error(array('message' => 'Attachment not found.'));
    }
    
    // Revert image
    $converter = WebP_CP_Converter::get_instance();
    $result = $converter->revert_image($attachment_id);
    
    if ($result) {
        wp_send_json_success(array('message' => 'Image reverted successfully.'));
    } else {
        wp_send_json_error(array('message' => 'Failed to revert image.'));
    }
}

// Retry single image
add_action('wp_ajax_webp_cp_retry_single', 'webp_cp_ajax_retry_single');
function webp_cp_ajax_retry_single() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Get log ID
    $log_id = isset($_POST['log_id']) ? intval($_POST['log_id']) : 0;
    
    if (!$log_id) {
        wp_send_json_error(array('message' => 'Invalid log ID.'));
    }
    
    // Get log
    global $wpdb;
    $table_name = $wpdb->prefix . 'webp_cp_activity_log';
    $log = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$table_name}` WHERE id = %d", $log_id));
    
    if (!$log) {
        wp_send_json_error(array('message' => 'Log not found.'));
    }
    
    // Get attachment ID from the log
    $attachment_id = intval($log->attachment_id);
    
    if (!$attachment_id) {
        wp_send_json_error(array('message' => 'Invalid attachment ID.'));
    }
    
    // Verify attachment exists
    if (!get_post($attachment_id)) {
        wp_send_json_error(array('message' => 'Attachment not found.'));
    }
    
    // Convert image
    $converter = WebP_CP_Converter::get_instance();
    $result = $converter->convert_image($attachment_id);
    
    if ($result) {
        wp_send_json_success(array('message' => 'Image converted successfully.'));
    } else {
        wp_send_json_error(array('message' => 'Failed to convert image.'));
    }
}

// Save compression level
add_action('wp_ajax_webp_cp_save_compression_level', 'webp_cp_ajax_save_compression_level');
function webp_cp_ajax_save_compression_level() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Get compression level
    $compression_level = isset($_POST['compression_level']) ? intval($_POST['compression_level']) : 82;
    
    // Validate compression level
    if ($compression_level < 40 || $compression_level > 100) {
        wp_send_json_error(array('message' => 'Compression level must be between 40 and 100.'));
    }
    
    // Update option
    update_option('webp_cp_compression_quality', $compression_level);
    
    wp_send_json_success(array('message' => 'Compression level saved successfully.'));
}

// Save settings
add_action('wp_ajax_webp_cp_save_settings', 'webp_cp_ajax_save_settings');
function webp_cp_ajax_save_settings() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Save settings
    $enable_backup = isset($_POST['enable_backup']) && $_POST['enable_backup'] ? 1 : 0;
    $auto_convert = isset($_POST['auto_convert']) && $_POST['auto_convert'] ? 1 : 0;
    
    // Ensure auto-convert is disabled if backup is disabled
    if (!$enable_backup) {
        $auto_convert = 0;
    }
    
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
        'webp_cp_enable_backup' => $enable_backup,
        'webp_cp_backup_reminder' => isset($_POST['backup_reminder']) && $_POST['backup_reminder'] ? 1 : 0,
        'webp_cp_backup_deletion_duration' => $deletion_duration,
        'webp_cp_custom_duration' => sanitize_text_field($_POST['custom_duration']),
        'webp_cp_auto_convert' => $auto_convert,
        'webp_cp_lazy_load' => isset($_POST['lazy_load']) && $_POST['lazy_load'] ? 1 : 0,
        'webp_cp_compression_quality' => max(40, min(100, intval($_POST['compression_quality']))),
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

// Revert all images
add_action('wp_ajax_webp_cp_revert_all', 'webp_cp_ajax_revert_all');
function webp_cp_ajax_revert_all() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    // Get converted images
    $converted_images = webp_cp_get_converted_images();
    
    if (empty($converted_images)) {
        wp_send_json_error(array('message' => 'No converted images found.'));
    }
    
    // Revert images
    $converter = WebP_CP_Converter::get_instance();
    $success_count = 0;
    
    foreach ($converted_images as $image_id) {
        if ($converter->revert_image($image_id)) {
            $success_count++;
        }
    }
    
    if ($success_count > 0) {
        wp_send_json_success(array('message' => "$success_count image(s) reverted successfully."));
    } else {
        wp_send_json_error(array('message' => 'Failed to revert images.'));
    }
}

// Reset everything
add_action('wp_ajax_webp_cp_reset_everything', 'webp_cp_ajax_reset_everything');
function webp_cp_ajax_reset_everything() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
    }
    
    $total_actions = 0;
    $completed_actions = 0;
    $messages = array();
    
    // 1. Revert all converted images
    $converted_images = webp_cp_get_converted_images();
    
    if (!empty($converted_images)) {
        $total_actions++;
        $converter = WebP_CP_Converter::get_instance();
        $success_count = 0;
        
        foreach ($converted_images as $image_id) {
            if ($converter->revert_image($image_id)) {
                $success_count++;
            }
        }
        
        if ($success_count > 0) {
            $completed_actions++;
            $messages[] = "$success_count image(s) reverted successfully.";
        } else {
            $messages[] = "Failed to revert images.";
        }
    } else {
        $completed_actions++;
        $messages[] = "No converted images found to revert.";
    }
    
    // 2. Clear all activity logs
    $total_actions++;
    global $wpdb;
    $table_name = $wpdb->prefix . 'webp_cp_activity_log';
    
    // Check if table exists before truncating
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
    if ($table_exists) {
        $result = $wpdb->query("TRUNCATE TABLE `{$table_name}`");
        
        if ($result !== false) {
            $completed_actions++;
            $messages[] = "Activity logs cleared successfully.";
        } else {
            $messages[] = "Failed to clear activity logs.";
        }
    } else {
        $completed_actions++;
        $messages[] = "Activity log table does not exist.";
    }
    
    // 3. Reset all settings to default
    $total_actions++;
    $default_settings = array(
        'webp_cp_enable_backup' => 1,
        'webp_cp_backup_reminder' => 0,
        'webp_cp_backup_deletion_duration' => '30',
        'webp_cp_custom_duration' => '',
        'webp_cp_auto_convert' => 0, // Disabled by default
        'webp_cp_lazy_load' => 0, // Disabled by default
        'webp_cp_compression_quality' => 82,
        'webp_cp_serve_webp' => 1,
    );
    
    $settings_updated = 0;
    foreach ($default_settings as $key => $value) {
        if (update_option($key, $value)) {
            $settings_updated++;
        }
    }
    
    if ($settings_updated > 0) {
        $completed_actions++;
        $messages[] = "Settings reset to default successfully.";
    } else {
        $messages[] = "Failed to reset settings.";
    }
    
    // 4. Delete all backup files
    $total_actions++;
    $upload_dir = wp_upload_dir();
    $backup_dir = $upload_dir['basedir'] . '/webp-cp-backups';
    
    if (is_dir($backup_dir)) {
        $files_deleted = 0;
        $files = glob($backup_dir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if (unlink($file)) {
                    $files_deleted++;
                }
            }
        }
        
        if ($files_deleted > 0) {
            $completed_actions++;
            $messages[] = "$files_deleted backup file(s) deleted successfully.";
        } else {
            $messages[] = "No backup files found to delete.";
        }
    } else {
        $completed_actions++;
        $messages[] = "No backup directory found.";
    }
    
    // Clear any scheduled cron jobs
    $timestamp = wp_next_scheduled('webp_cp_backup_reminder_cron');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'webp_cp_backup_reminder_cron');
    }
    
    
    if ($completed_actions === $total_actions) {
        wp_send_json_success(array('message' => 'Everything has been reset successfully. ' . implode(' ', $messages)));
    } else {
        wp_send_json_error(array('message' => 'Reset completed with some issues. ' . implode(' ', $messages)));
    }
}