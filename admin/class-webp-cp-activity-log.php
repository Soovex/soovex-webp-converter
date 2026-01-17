<?php
/**
 * Activity log class for the plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WebP_CP_Activity_Log {
    
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
        // Nothing to do here
    }
    
    /**
     * Render activity log page
     */
    public function render_page() {
        include WEBP_CP_PATH . 'admin/templates/activity-log.php';
    }
}