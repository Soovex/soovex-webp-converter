<?php
/**
 * Dashboard class for the plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WebP_CP_Dashboard {
    
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
        // Add dashboard widgets
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
    }
    
    /**
     * Add dashboard widgets
     */
    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'webp_cp_dashboard_widget',
            __('Soovex WebP Converter Statistics', 'soovex-webp-converter'),
            array($this, 'render_dashboard_widget')
        );
    }
    
    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget() {
        $images_in_media = count(webp_cp_get_all_images());
        $compressed_images = count(webp_cp_get_converted_images());
        $storage_saved = webp_cp_get_storage_saved();
        
        include WEBP_CP_PATH . 'admin/templates/dashboard-widget.php';
    }
    
    /**
     * Render dashboard page
     */
    public function render_page() {
        include WEBP_CP_PATH . 'admin/templates/dashboard.php';
    }
}