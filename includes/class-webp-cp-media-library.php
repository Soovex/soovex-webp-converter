<?php
/**
 * Media Library integration class for the plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WebP_CP_Media_Library {
    
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
        // Add media library columns
        add_filter('manage_media_columns', array($this, 'add_media_columns'));
        add_action('manage_media_custom_column', array($this, 'display_media_columns'), 10, 2);
        
        // Add media library row actions
        add_filter('media_row_actions', array($this, 'add_media_row_actions'), 10, 2);
        
        // Add admin scripts for media library
        add_action('admin_enqueue_scripts', array($this, 'enqueue_media_scripts'));
        
        // Add AJAX handlers for media library actions
        add_action('wp_ajax_webp_cp_convert_media', array($this, 'ajax_convert_media'));
        add_action('wp_ajax_webp_cp_revert_media', array($this, 'ajax_revert_media'));
        add_action('wp_ajax_webp_cp_check_media_status', array($this, 'ajax_check_media_status'));
    }
    
    /**
     * Add custom columns to media library
     */
    public function add_media_columns($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ($key === 'title') {
                $new_columns['webp_status'] = __('WebP Status', 'soovex-webp-converter');
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Display custom column content
     */
    public function display_media_columns($column_name, $attachment_id) {
        if ($column_name === 'webp_status') {
            $this->display_webp_status($attachment_id);
        }
    }
    
    /**
     * Display WebP status for media item
     */
    private function display_webp_status($attachment_id) {
        // Get attachment
        $attachment = get_post($attachment_id);
        
        if (!$attachment || !wp_attachment_is_image($attachment_id)) {
            echo '<span class="webp-status-not-applicable">-</span>';
            return;
        }
        
        // Get attachment path
        $attachment_path = get_attached_file($attachment_id);
        $file_ext = strtolower(pathinfo($attachment_path, PATHINFO_EXTENSION));
        
        // Check if it's a supported format
        if (!in_array($file_ext, array('jpg', 'jpeg', 'png', 'webp'))) {
            echo '<span class="webp-status-not-applicable">-</span>';
            return;
        }
        
        // Check if it's already WebP
        if ($file_ext === 'webp') {
            // Check if backup is enabled in settings
            $backup_enabled = get_option('webp_cp_enable_backup', 1);
            $backup_exists = $this->check_backup_exists($attachment_id);
            
            if ($backup_enabled && $backup_exists) {
                echo '<span class="webp-status-converted">';
                echo '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> ';
                echo esc_html__('Converted', 'soovex-webp-converter');
                echo '</span>';
            } elseif ($backup_enabled && !$backup_exists) {
                echo '<span class="webp-status-converted-no-backup">';
                echo '<span class="dashicons dashicons-warning" style="color: #f0b849;"></span> ';
                echo esc_html__('Converted (No Backup)', 'soovex-webp-converter');
                echo '</span>';
            } else {
                echo '<span class="webp-status-converted">';
                echo '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> ';
                echo esc_html__('Converted', 'soovex-webp-converter');
                echo '</span>';
            }
        } else {
            // Check if WebP version exists
            $webp_path = $attachment_path . '.webp';
            
            if (file_exists($webp_path)) {
                echo '<span class="webp-status-available">';
                echo '<span class="dashicons dashicons-warning" style="color: #f0b849;"></span> ';
                echo esc_html__('WebP Available', 'soovex-webp-converter');
                echo '</span>';
            } else {
                echo '<span class="webp-status-not-converted">';
                echo '<span class="dashicons dashicons-no-alt" style="color: #d63638;"></span> ';
                echo esc_html__('Not Converted', 'soovex-webp-converter');
                echo '</span>';
            }
        }
    }
    
    /**
     * Add row actions to media library
     */
    public function add_media_row_actions($actions, $post) {
        // Only for images
        if (!wp_attachment_is_image($post->ID)) {
            return $actions;
        }
        
        // Get attachment path
        $attachment_path = get_attached_file($post->ID);
        $file_ext = strtolower(pathinfo($attachment_path, PATHINFO_EXTENSION));
        
        // Check if it's a supported format
        if (!in_array($file_ext, array('jpg', 'jpeg', 'png', 'webp'))) {
            return $actions;
        }
        
        // Add convert/revert actions
        if ($file_ext === 'webp') {
            // This is a WebP image, show revert option
            $backup_enabled = get_option('webp_cp_enable_backup', 1);
            $backup_exists = $this->check_backup_exists($post->ID);
            
            if ($backup_enabled && $backup_exists) {
                $actions['webp_revert'] = sprintf(
                    '<a href="#" class="webp-cp-revert-media" data-attachment-id="%d" data-nonce="%s">%s</a>',
                    $post->ID,
                    wp_create_nonce('webp_cp_media_nonce'),
                    __('Revert to Original', 'soovex-webp-converter')
                );
            }
        } else {
            // This is a JPG/PNG image, show convert option
            $webp_path = $attachment_path . '.webp';
            
            if (!file_exists($webp_path)) {
                $actions['webp_convert'] = sprintf(
                    '<a href="#" class="webp-cp-convert-media" data-attachment-id="%d" data-nonce="%s">%s</a>',
                    $post->ID,
                    wp_create_nonce('webp_cp_media_nonce'),
                    __('Convert to WebP', 'soovex-webp-converter')
                );
            }
        }
        
        return $actions;
    }
    
    /**
     * Enqueue media library scripts
     */
    public function enqueue_media_scripts($hook) {
        // Only load on media library pages
        if ($hook !== 'upload.php' && $hook !== 'media.php') {
            return;
        }
        
        // Enqueue script
        wp_enqueue_script(
            'webp-cp-media-library',
            WEBP_CP_URL . 'assets/js/media-library.js',
            array('jquery'),
            WEBP_CP_VERSION,
            true
        );
        
        // Enqueue style
        wp_enqueue_style(
            'webp-cp-media-library',
            WEBP_CP_URL . 'assets/css/media-library.css',
            array(),
            WEBP_CP_VERSION
        );
        
        // Localize script
        wp_localize_script('webp-cp-media-library', 'webp_cp_media_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('webp_cp_media_nonce'),
            'strings' => array(
                'converting' => __('Converting...', 'soovex-webp-converter'),
                'reverting' => __('Reverting...', 'soovex-webp-converter'),
                'success' => __('Success!', 'soovex-webp-converter'),
                'error' => __('Error occurred', 'soovex-webp-converter'),
                'confirm_convert' => __('Are you sure you want to convert this image to WebP?', 'soovex-webp-converter'),
                'confirm_revert' => __('Are you sure you want to revert this image to its original format?', 'soovex-webp-converter'),
            )
        ));
    }
    
    /**
     * Check if backup exists for attachment
     */
    private function check_backup_exists($attachment_id) {
        // Check if backup is enabled in settings
        $backup_enabled = get_option('webp_cp_enable_backup', 1);
        if (!$backup_enabled) {
            return false;
        }
        
        $attachment_path = get_attached_file($attachment_id);
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/webp-cp-backups';
        
        // Check if this is a converted WebP file
        $file_ext = strtolower(pathinfo($attachment_path, PATHINFO_EXTENSION));
        
        if ($file_ext === 'webp') {
            // For converted WebP files, we need to find the original backup
            // The backup was created with the original filename before conversion
            // So we need to reconstruct the original filename
            
            // Get the base name without extension
            $base_name = pathinfo($attachment_path, PATHINFO_FILENAME);
            
            // Try to find backup with original extensions
            $original_extensions = array('jpg', 'jpeg', 'png');
            
            foreach ($original_extensions as $ext) {
                $backup_file_path = $backup_dir . '/' . $base_name . '.' . $ext;
                if (file_exists($backup_file_path)) {
                    return true;
                }
            }
            
            // Also check if there's a backup with the same name (in case backup was created after conversion)
            $backup_file_path = $backup_dir . '/' . basename($attachment_path);
            if (file_exists($backup_file_path)) {
                return true;
            }
            
            return false;
        } else {
            // For original files, check if WebP version exists
            $webp_path = $attachment_path . '.webp';
            return file_exists($webp_path);
        }
    }
    
    /**
     * AJAX handler for converting media
     */
    public function ajax_convert_media() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_media_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Check user capabilities
        if (!current_user_can('upload_files')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        }
        
        // Get attachment ID
        $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
        
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
    
    /**
     * AJAX handler for reverting media
     */
    public function ajax_revert_media() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_media_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Check user capabilities
        if (!current_user_can('upload_files')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        }
        
        // Get attachment ID
        $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
        
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
    
    /**
     * AJAX handler for checking media status
     */
    public function ajax_check_media_status() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_media_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Check user capabilities
        if (!current_user_can('upload_files')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        }
        
        // Get attachment ID
        $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
        
        if (!$attachment_id) {
            wp_send_json_error(array('message' => 'Invalid attachment ID.'));
        }
        
        // Get attachment
        $attachment = get_post($attachment_id);
        
        if (!$attachment || !wp_attachment_is_image($attachment_id)) {
            wp_send_json_error(array('message' => 'Invalid attachment.'));
        }
        
        // Get attachment path
        $attachment_path = get_attached_file($attachment_id);
        $file_ext = strtolower(pathinfo($attachment_path, PATHINFO_EXTENSION));
        
        // Check status
        $backup_enabled = get_option('webp_cp_enable_backup', 1);
        $has_backup = $this->check_backup_exists($attachment_id);
        
        $status = array(
            'is_webp' => ($file_ext === 'webp'),
            'has_backup' => $has_backup,
            'backup_enabled' => $backup_enabled,
            'can_convert' => in_array($file_ext, array('jpg', 'jpeg', 'png')),
            'can_revert' => ($file_ext === 'webp' && $backup_enabled && $has_backup)
        );
        
        wp_send_json_success($status);
    }
}
