<?php
/**
 * Backup reminder class for the plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WebP_CP_Backup_Reminder {
    
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
        // Add admin notices
        add_action('admin_notices', array($this, 'show_backup_reminder_notices'));
        
        // Add AJAX handler for dismissing notices
        add_action('wp_ajax_webp_cp_dismiss_backup_reminder', array($this, 'dismiss_backup_reminder'));
        
        // Add cron job for backup reminders
        add_action('webp_cp_backup_reminder_cron', array($this, 'check_backup_reminders'));
    }
    
    /**
     * Show backup reminder notices
     */
    public function show_backup_reminder_notices() {
        // Only show on our plugin pages
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'webp-converter-pro') === false) {
            return;
        }
        
        // Check if backup reminder is enabled
        if (!get_option('webp_cp_backup_reminder', 0)) {
            return;
        }
        
        // Check if user has dismissed the notice
        $dismissed = get_user_meta(get_current_user_id(), 'webp_cp_backup_reminder_dismissed', true);
        if ($dismissed) {
            return;
        }
        
        // Get backup deletion info
        $deletion_duration = get_option('webp_cp_backup_deletion_duration', '30');
        $deletion_date = get_option('webp_cp_backup_deletion_date', '');
        
        // Skip if deletion is set to "Never"
        if ($deletion_duration === 'Never') {
            return;
        }
        
        // Calculate deletion date if not set
        if (empty($deletion_date)) {
            $deletion_date = date('Y-m-d', strtotime('+' . $deletion_duration . ' days'));
            update_option('webp_cp_backup_deletion_date', $deletion_date);
        } else {
            // Check if the stored deletion date is in the past, if so, recalculate
            if (strtotime($deletion_date) < time()) {
                $deletion_date = date('Y-m-d', strtotime('+' . $deletion_duration . ' days'));
                update_option('webp_cp_backup_deletion_date', $deletion_date);
            }
        }
        
        // Calculate days until deletion
        $days_until_deletion = (strtotime($deletion_date) - time()) / DAY_IN_SECONDS;
        
        // Show reminder if within 3 days
        if ($days_until_deletion <= 3 && $days_until_deletion > 0) {
            $this->render_backup_reminder_notice($deletion_date, $days_until_deletion);
        }
    }
    
    /**
     * Render backup reminder notice
     */
    private function render_backup_reminder_notice($deletion_date, $days_until_deletion) {
        $days = floor($days_until_deletion);
        $hours = floor(($days_until_deletion - $days) * 24);
        
        $time_text = '';
        if ($days > 0) {
            $time_text = $days . ' day' . ($days > 1 ? 's' : '');
        } else {
            $time_text = $hours . ' hour' . ($hours > 1 ? 's' : '');
        }
        
        ?>
        <div class="notice notice-warning is-dismissible webp-cp-backup-reminder" data-nonce="<?php echo esc_attr(wp_create_nonce('webp_cp_dismiss_backup_reminder')); ?>">
            <div class="flex items-start gap-3 p-4">
                <span class="material-symbols-outlined text-2xl text-orange-600">warning</span>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-orange-800 mb-2">Backup Deletion Reminder</h3>
                    <p class="text-orange-700 mb-3">
                        Your Soovex WebP Converter backups are scheduled for deletion in <strong><?php echo esc_html($time_text); ?></strong> 
                        (<?php echo esc_html(date('M j, Y', strtotime($deletion_date))); ?>).
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="webp-cp-open-settings inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors text-sm font-medium">
                            <span class="material-symbols-outlined text-sm">settings</span>
                            Manage Settings
                        </button>
                        <button type="button" class="webp-cp-dismiss-reminder inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                            <span class="material-symbols-outlined text-sm">close</span>
                            Dismiss
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Dismiss backup reminder
     */
    public function dismiss_backup_reminder() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'webp_cp_dismiss_backup_reminder')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        }
        
        // Dismiss the notice for this user
        update_user_meta(get_current_user_id(), 'webp_cp_backup_reminder_dismissed', time());
        
        wp_send_json_success();
    }
    
    /**
     * Check backup reminders (cron job)
     */
    public function check_backup_reminders() {
        // Check if backup reminder is enabled
        if (!get_option('webp_cp_backup_reminder', 0)) {
            return;
        }
        
        // Get backup deletion info
        $deletion_duration = get_option('webp_cp_backup_deletion_duration', '30');
        $deletion_date = get_option('webp_cp_backup_deletion_date', '');
        
        // Skip if deletion is set to "Never"
        if ($deletion_duration === 'Never') {
            return;
        }
        
        // Calculate deletion date if not set
        if (empty($deletion_date)) {
            $deletion_date = date('Y-m-d', strtotime('+' . $deletion_duration . ' days'));
            update_option('webp_cp_backup_deletion_date', $deletion_date);
        } else {
            // Check if the stored deletion date is in the past, if so, recalculate
            if (strtotime($deletion_date) < time()) {
                $deletion_date = date('Y-m-d', strtotime('+' . $deletion_duration . ' days'));
                update_option('webp_cp_backup_deletion_date', $deletion_date);
            }
        }
        
        // Check if current date is after deletion date
        if (date('Y-m-d') >= $deletion_date) {
            // Send email reminder to admin
            $this->send_backup_deletion_email();
            
            // Reset deletion date for next cycle
            $deletion_date = date('Y-m-d', strtotime('+' . $deletion_duration . ' days'));
            update_option('webp_cp_backup_deletion_date', $deletion_date);
            
            // Reset dismissed notices for all users
            $this->reset_dismissed_notices();
        }
    }
    
    /**
     * Send backup deletion email
     */
    private function send_backup_deletion_email() {
        $to = get_option('admin_email');
        $subject = 'Soovex WebP Converter - Backup Deletion Reminder';
        
        $message = '<h2>Backup Deletion Reminder</h2>';
        $message .= '<p>This is a reminder that your Soovex WebP Converter backups are scheduled for deletion today.</p>';
        $message .= '<p>If you want to keep your backups, please update the backup deletion settings in your WordPress admin panel.</p>';
        $message .= '<p><a href="' . admin_url('admin.php?page=webp-converter-pro') . '" style="background: #1173d4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Manage Settings</a></p>';
        $message .= '<p>Best regards,<br>Soovex WebP Converter Team</p>';
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Reset dismissed notices for all users
     */
    private function reset_dismissed_notices() {
        global $wpdb;
        
        // Get all users who have dismissed the notice
        $users = $wpdb->get_results("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'webp_cp_backup_reminder_dismissed'");
        
        foreach ($users as $user) {
            delete_user_meta($user->user_id, 'webp_cp_backup_reminder_dismissed');
        }
    }
}
