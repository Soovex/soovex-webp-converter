<?php
/**
 * Dashboard widget template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Calculate conversion percentage
$conversion_percentage = $images_in_media > 0 ? round(($compressed_images / $images_in_media) * 100, 1) : 0;
?>

<style>
/* Material Icons Font */
@font-face {
  font-family: 'Material Symbols Outlined';
  font-style: normal;
  font-weight: 400;
  src: url('<?php echo esc_url(WEBP_CP_URL . 'assets/css/materialicon.woff2'); ?>') format('woff2');
}

.material-symbols-outlined {
  font-family: 'Material Symbols Outlined';
  font-weight: normal;
  font-style: normal;
  font-size: 24px;
  line-height: 1;
  letter-spacing: normal;
  text-transform: none;
  display: inline-block;
  white-space: nowrap;
  word-wrap: normal;
  direction: ltr;
  font-feature-settings: 'liga';
  -webkit-font-feature-settings: 'liga';
  -webkit-font-smoothing: antialiased;
}

.webp-cp-widget {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    background: #f6f7f8;
    padding: 15px;
    border-radius: 10px;
}

.webp-cp-stats-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    margin-bottom: 32px;
}

.webp-cp-stat-card {
    background: #fff;
    border: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.webp-cp-stat-info {
    text-align: left;
}

.webp-cp-stat-info h3 {
    font-size: 14px;
    font-weight: 500;
    color: #6b7280;
    margin: 0 0 0 0;
}

.webp-cp-stat-info p {
    font-size: 24px;
    font-weight: 700;
    color: #111827;
    margin: 0;
    line-height: 1;
}

.webp-cp-stat-info .text-primary {
    color: #1173d4;
}

.webp-cp-stat-icon {
    width: 48px;
    height: 48px;
    color: #1173d4;
    opacity: 0.2;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
}

.webp-cp-button {
    display: block;
    width: 100%;
    background: #1173d4;
    color: #fff;
    text-decoration: none;
    padding: 14px 20px;
    border-radius: 8px;
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(17, 115, 212, 0.2);
    position: relative;
    overflow: hidden;
    box-sizing: border-box;
}

.webp-cp-button:hover {
    background: #0f6bb8;
    color: #fff;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(17, 115, 212, 0.4);
}

.webp-cp-button:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(17, 115, 212, 0.2);
}

/* Dark mode support */
.dark .webp-cp-stat-card {
    background: #1f2937;
    border-color: rgba(255, 255, 255, 0.05);
}

.dark .webp-cp-stat-info h3 {
    color: #9ca3af;
}

.dark .webp-cp-stat-info p {
    color: #f9fafb;
}

/* Responsive */
@media (max-width: 768px) {
    .webp-cp-stats-grid {
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .webp-cp-stat-card {
        padding: 20px;
    }
    
    .webp-cp-stat-icon {
        width: 40px;
        height: 40px;
        font-size: 24px;
    }
    
    .webp-cp-stat-info h3 {
        font-size: 13px;
    }
    
    .webp-cp-stat-info p {
        font-size: 20px;
    }
}
</style>

<div class="webp-cp-widget">
    <div class="webp-cp-stats-grid">
        <!-- Total Images Card -->
        <div class="webp-cp-stat-card">
            <div class="webp-cp-stat-info">
                <h3>Images in Media</h3>
                <p><?php echo esc_html($images_in_media); ?></p>
            </div>
            <div class="webp-cp-stat-icon">
                <span class="material-symbols-outlined">image</span>
            </div>
        </div>

        <!-- Converted Images Card -->
        <div class="webp-cp-stat-card">
            <div class="webp-cp-stat-info">
                <h3>Compressed Images</h3>
                <p><?php echo esc_html($compressed_images); ?></p>
            </div>
            <div class="webp-cp-stat-icon">
                <span class="material-symbols-outlined">compress</span>
            </div>
        </div>

        <!-- Storage Saved Card -->
        <div class="webp-cp-stat-card">
            <div class="webp-cp-stat-info">
                <h3>Storage Saved</h3>
                <p class="text-primary"><?php echo esc_html(webp_cp_format_filesize($storage_saved)); ?></p>
            </div>
            <div class="webp-cp-stat-icon">
                <span class="material-symbols-outlined">data_saver_on</span>
            </div>
        </div>
    </div>

    <a href="<?php echo esc_url(admin_url('admin.php?page=webp-converter-pro')); ?>" class="webp-cp-button">
        View Full Dashboard
    </a>
</div>