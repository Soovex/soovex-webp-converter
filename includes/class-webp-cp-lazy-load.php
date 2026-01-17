<?php
/**
 * Lazy load class for the plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WebP_CP_Lazy_Load {
    
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
        // Add lazy loading to images if enabled
        if (get_option('webp_cp_lazy_load', 0)) {
            add_filter('wp_get_attachment_image_attributes', array($this, 'add_lazy_loading'), 10, 2);
            add_filter('post_thumbnail_html', array($this, 'add_lazy_loading_to_thumbnail'), 10, 3);
            add_filter('the_content', array($this, 'add_lazy_loading_to_content_images'), 10, 1);
        }
    }
    
    /**
     * Add lazy loading to attachment images
     */
    public function add_lazy_loading($attr, $attachment) {
        // Bug #12 - Check if native lazy loading is already supported
        // WordPress 5.5+ has native lazy loading support
        // Only add if not already present and WordPress version < 5.5
        if (version_compare(get_bloginfo('version'), '5.5', '<') && !isset($attr['loading'])) {
            $attr['loading'] = 'lazy';
        }
        return $attr;
    }
    
    /**
     * Add lazy loading to post thumbnails
     */
    public function add_lazy_loading_to_thumbnail($html, $post_id, $post_image_id) {
        // Bug #12 - Check if native lazy loading is already present or WordPress version >= 5.5
        if (version_compare(get_bloginfo('version'), '5.5', '<') && strpos($html, 'loading=') === false) {
            return str_replace('<img', '<img loading="lazy"', $html);
        }
        return $html;
    }
    
    /**
     * Add lazy loading to content images
     */
    public function add_lazy_loading_to_content_images($content) {
        // Bug #12 - Check if native lazy loading is already present or WordPress version >= 5.5
        if (version_compare(get_bloginfo('version'), '5.5', '<')) {
            // Only add to images that don't already have loading attribute
            $content = preg_replace('/<img(?!.*loading=)/i', '<img loading="lazy"', $content);
        }
        return $content;
    }
}