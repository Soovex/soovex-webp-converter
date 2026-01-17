<?php
/**
 * Admin class for the plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WebP_CP_Admin {
    
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
        // Add admin footer text
        add_filter('admin_footer_text', array($this, 'admin_footer_text'), 1);
        
        // Add plugin action links
        add_filter('plugin_action_links_' . WEBP_CP_BASENAME, array($this, 'add_plugin_action_links'));
    }
    
    /**
     * Admin footer text
     */
    public function admin_footer_text($text) {
        $screen = get_current_screen();
        
        if (strpos($screen->id, 'webp-converter-pro') !== false) {
            /* translators: %s: Copyright notice with dynamic year. */
            $text = sprintf(
                __('© %s Soovex WebP Converter. All rights reserved. By %s', 'soovex-webp-converter'),
                date('Y'),
                '<a href="https://soovex.com/" target="_blank">Mustafijur Rahman, Founder & CEO Soovex IT Agency</a>'
            );
        }
        
        return $text;
    }
    
    /**
     * Add plugin action links
     *
     * @param array $links Existing action links
     * @return array Modified action links
     */
    public function add_plugin_action_links($links) {
        // Add Settings link (links to Settings page)
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=webp-converter-pro-settings')),
            __('Settings', 'soovex-webp-converter')
        );
        
        // Add Dashboard link (links to Dashboard page)
        $dashboard_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=webp-converter-pro')),
            __('Dashboard', 'soovex-webp-converter')
        );
        
        // Add Activity link (links to Activity Log page)
        $activity_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=webp-converter-pro-activity-log')),
            __('Activity', 'soovex-webp-converter')
        );
        
        // Insert links at the beginning in the order: Settings, Dashboard, Activity
        array_unshift($links, $activity_link);
        array_unshift($links, $dashboard_link);
        array_unshift($links, $settings_link);
        
        return $links;
    }
}