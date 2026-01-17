<?php
/**
 * Settings page template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$settings = WebP_CP_Settings::get_instance()->get_settings();
?>

<div class="wrap">
    <div class="font-display bg-background-light dark:bg-background-dark">
        <header class="flex items-center justify-between whitespace-nowrap border-b border-gray-200 dark:border-gray-800 px-10 py-3">
            <div class="flex items-center gap-4">
                <div class="size-6 text-primary">
                    <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path clip-rule="evenodd" d="M24 4H42V17.3333V30.6667H24V44H6V30.6667V17.3333H24V4Z" fill="currentColor" fill-rule="evenodd"></path>
                    </svg>
                </div>
                <h2 class="text-lg font-bold">Soovex WebP Converter - Settings</h2>
            </div>
        </header>
        <main class="flex-1 px-4 sm:px-6 lg:px-10 py-8">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl font-bold text-black/90 dark:text-white/90 mb-6">Settings</h2>
                
                <form id="webp-cp-settings-form" class="space-y-8">
                    <!-- Data Backup & Recovery Section -->
                    <div class="bg-white dark:bg-background-dark rounded-xl p-6 shadow-sm border border-black/5 dark:border-white/5">
                        <h3 class="text-xl font-bold text-black/90 dark:text-white/90 mb-6">Data Backup & Recovery</h3>
                        
                        <div class="space-y-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <label class="font-medium text-black/90 dark:text-white/90" for="backup">Enable Data Backup</label>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Keep original images as a backup after conversion.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input <?php checked($settings['enable_backup'], 1); ?> class="sr-only peer" id="backup" type="checkbox" name="enable_backup" value="1" />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 dark:peer-focus:ring-primary/80 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                                </label>
                            </div>
                            
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <label class="font-medium text-black/90 dark:text-white/90" for="reminder">Set Backup Reminder</label>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Get notified before the backup data is automatically deleted.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input <?php checked($settings['backup_reminder'], 1); ?> class="sr-only peer" id="reminder" type="checkbox" name="backup_reminder" value="1" />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 dark:peer-focus:ring-primary/80 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                                </label>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block font-medium text-black/90 dark:text-white/90" for="deletion-time">Automatically Delete Original Data from Backup</label>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm text-gray-500 dark:text-gray-400" for="deletion-duration">After a duration:</label>
                                        <select class="form-select w-full rounded-lg bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-primary/50 text-sm mt-1" id="deletion-duration" name="backup_deletion_duration" onchange="toggleCustomDuration()">
                                            <option value="7" <?php selected($settings['backup_deletion_duration'], '7'); ?>>7 days</option>
                                            <option value="14" <?php selected($settings['backup_deletion_duration'], '14'); ?>>14 days</option>
                                            <option value="30" <?php selected($settings['backup_deletion_duration'], '30'); ?>>30 days</option>
                                            <option value="60" <?php selected($settings['backup_deletion_duration'], '60'); ?>>60 days</option>
                                            <option value="90" <?php selected($settings['backup_deletion_duration'], '90'); ?>>90 days</option>
                                            <option value="180" <?php selected($settings['backup_deletion_duration'], '180'); ?>>180 days</option>
                                            <option value="365" <?php selected($settings['backup_deletion_duration'], '365'); ?>>1 year</option>
                                            <option value="custom" <?php selected($settings['backup_deletion_duration'], 'custom'); ?>>Custom</option>
                                            <option value="Never" <?php selected($settings['backup_deletion_duration'], 'Never'); ?>>Never</option>
                                        </select>
                                    </div>
                                    <div id="custom-duration-container" style="display: none;">
                                        <label class="text-sm text-gray-500 dark:text-gray-400" for="custom-duration">Custom duration (days):</label>
                                        <input class="form-input w-full rounded-lg bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-primary/50 text-sm mt-1" id="custom-duration" name="custom_duration" type="number" min="1" max="3650" value="<?php echo esc_attr($settings['custom_duration'] ?? ''); ?>" placeholder="Enter number of days" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3 rounded-lg bg-red-50 dark:bg-red-900/30 p-4 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800/60">
                                <span class="material-symbols-outlined mt-0.5 text-xl text-red-600 dark:text-red-400">warning</span>
                                <div class="flex-1">
                                    <p class="text-sm font-bold">CRITICAL WARNING</p>
                                    <p class="text-sm">Once the original data is deleted from the backup, it is <span class="font-semibold">permanently irrecoverable</span>. This action cannot be undone. Please ensure you have secured your data elsewhere if needed before proceeding.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Other Features Section -->
                    <div class="bg-white dark:bg-background-dark rounded-xl p-6 shadow-sm border border-black/5 dark:border-white/5">
                        <h3 class="text-xl font-bold text-black/90 dark:text-white/90 mb-6">Other Features</h3>
                        
                        <div class="space-y-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <label class="font-medium text-black/90 dark:text-white/90" for="auto-convert">Auto-convert new uploads</label>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Automatically convert images to WebP upon uploading to the media library.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input <?php checked($settings['auto_convert'], 1); ?> class="sr-only peer" id="auto-convert" type="checkbox" name="auto_convert" value="1" />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 dark:peer-focus:ring-primary/80 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                                </label>
                            </div>
                            
                            <div class="flex items-start gap-3 rounded-lg bg-blue-50 dark:bg-blue-900/30 p-4 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60">
                                <span class="material-symbols-outlined mt-0.5 text-xl text-blue-600 dark:text-blue-400">info</span>
                                <div class="flex-1">
                                    <p class="text-sm font-bold">Note</p>
                                    <p class="text-sm">Auto-convert requires backup to be enabled. If backup is disabled, auto-convert will also be disabled.</p>
                                </div>
                            </div>

                            <div x-data="{ compressionQuality: <?php echo esc_attr($settings['compression_quality']); ?> }" class="space-y-2">
                                <label class="block font-medium text-black/90 dark:text-white/90" for="compression-quality">Image Compression Quality</label>
                                <input x-model="compressionQuality" @change="saveCompressionQuality(compressionQuality)" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700 slider-thumb" id="compression-quality" name="compression_quality" max="100" min="40" type="range" />
                                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <span>40% (High Compression)</span>
                                    <span x-text="`${compressionQuality}%`"></span>
                                    <span>100% (Lossless)</span>
                                </div>
                            </div>
                            
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <label class="font-medium text-black/90 dark:text-white/90" for="lazy-load">Enable Lazy Loading</label>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Improve page load times by loading images only when they enter the viewport.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input <?php checked($settings['lazy_load'], 1); ?> class="sr-only peer" id="lazy-load" type="checkbox" name="lazy_load" value="1" />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 dark:peer-focus:ring-primary/80 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                                </label>
                            </div>
                            
                            <div class="mt-4" x-data="{ open: false }">
                                <button type="button" @click="open = !open" class="flex items-center justify-between w-full py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white focus:outline-none">
                                    <span>How Lazy Loading Works</span>
                                    <span class="ml-2 transform transition-transform" :class="{ 'rotate-180': open }">
                                        <span class="material-symbols-outlined">expand_more</span>
                                    </span>
                                </button>
                                <div x-show="open" x-collapse class="mt-2 text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-lg">
                                    <p class="mb-2">Lazy loading is a technique that defers the loading of images until they are needed. When you enable lazy loading, images will only load when they are visible on the screen, which can significantly improve your website's loading speed and performance.</p>
                                    <p class="mb-2">This feature works by adding the "loading='lazy'" attribute to all img tags on your website. Modern browsers support this attribute natively, so no additional JavaScript is required.</p>
                                    <p>Benefits of lazy loading:</p>
                                    <ul class="list-disc pl-5 mt-1 space-y-1">
                                        <li>Faster initial page load times</li>
                                        <li>Reduced bandwidth usage</li>
                                        <li>Improved performance on mobile devices</li>
                                        <li>Better user experience, especially on image-heavy pages</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <label class="font-medium text-black/90 dark:text-white/90" for="serve-webp">Serve WebP Images</label>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Automatically serve WebP images to browsers that support them for better performance.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input <?php checked($settings['serve_webp'], 1); ?> class="sr-only peer" id="serve-webp" type="checkbox" name="serve_webp" value="1" />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 dark:peer-focus:ring-primary/80 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="bg-white dark:bg-background-dark rounded-xl p-6 shadow-sm border border-black/5 dark:border-white/5">
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                                <button type="button" class="webp-cp-revert-all w-full sm:w-auto flex items-center justify-center gap-2 rounded-lg bg-primary py-2.5 px-6 text-sm font-semibold text-white hover:bg-primary/90 transition-colors shadow-sm">
                                    <span class="material-symbols-outlined">settings_backup_restore</span> All Data Recovery
                                </button>
                                <button type="button" class="webp-cp-reset-everything w-full sm:w-auto flex items-center justify-center gap-2 rounded-lg bg-red-600 py-2.5 px-6 text-sm font-semibold text-white hover:bg-red-700 transition-colors shadow-sm">
                                    <span class="material-symbols-outlined">refresh</span> Reset Everything
                                </button>
                            </div>
                            <button type="submit" class="webp-cp-save-settings w-full sm:w-auto rounded-lg bg-primary text-white py-2.5 px-6 text-sm font-semibold hover:bg-primary/90 transition-colors shadow-sm">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
        <footer class="mt-auto">
            <div class="mx-auto max-w-7xl py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">❤️ Made with love by Soovex IT Agency</p>
            </div>
        </footer>
    </div>
</div>

<!-- Success Popup -->
<div class="fixed inset-0 z-50 flex items-center justify-center p-4" id="webp-cp-success-popup" style="display: none;">
    <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
    <div class="w-full max-w-md p-8 space-y-6 bg-white dark:bg-background-dark rounded-xl shadow-lg m-4 relative z-10">
        <div class="flex flex-col items-center space-y-4">
            <div class="w-16 h-16 flex items-center justify-center bg-primary/20 rounded-full">
                <span class="material-symbols-outlined text-primary text-4xl">check_circle</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white text-center" id="webp-cp-success-title">Success</h1>
            <p class="text-center text-gray-600 dark:text-gray-300" id="webp-cp-success-message">
                Operation completed successfully.
            </p>
        </div>
        <div class="flex flex-col space-y-3">
            <button class="webp-cp-close-popup w-full py-3 px-4 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:focus:ring-offset-background-dark transition-colors duration-200">
                OK
            </button>
        </div>
    </div>
</div>

<!-- Failed Popup -->
<div class="fixed inset-0 z-50 flex items-center justify-center p-4" id="webp-cp-failed-popup" style="display: none;">
    <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
    <div class="w-full max-w-md p-8 space-y-6 bg-white dark:bg-background-dark rounded-xl shadow-lg m-4 relative z-10">
        <div class="flex flex-col items-center space-y-4">
            <div class="w-16 h-16 flex items-center justify-center bg-red-500/20 rounded-full">
                <span class="material-symbols-outlined text-red-500 text-4xl">error</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white text-center" id="webp-cp-failed-title">Error</h1>
            <p class="text-center text-gray-600 dark:text-gray-300" id="webp-cp-failed-message">
                An error occurred. Please try again.
            </p>
        </div>
        <div class="flex flex-col space-y-3">
            <button class="webp-cp-close-popup w-full py-3 px-4 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:focus:ring-offset-background-dark transition-colors duration-200">
                OK
            </button>
        </div>
    </div>
</div>

<script>
    // Toggle custom duration input
    function toggleCustomDuration() {
        const durationSelect = document.getElementById('deletion-duration');
        const customContainer = document.getElementById('custom-duration-container');
        
        if (durationSelect.value === 'custom') {
            customContainer.style.display = 'block';
        } else {
            customContainer.style.display = 'none';
        }
    }
    
    // Initialize custom duration visibility on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleCustomDuration();
    });

    jQuery(document).ready(function($) {
        // Close popup
        $('.webp-cp-close-popup').on('click', function() {
            $(this).closest('.fixed.inset-0.z-50').hide();
        });
        
        // Handle backup dependency for auto-convert
        $('#backup').on('change', function() {
            if (!$(this).is(':checked')) {
                $('#auto-convert').prop('checked', false);
            }
        });
        
        // Save settings
        $('#webp-cp-settings-form').on('submit', function(e) {
            e.preventDefault();
            
            var settings = {};
            
            // Collect form data
            $(this).find('input, select').each(function() {
                var name = $(this).attr('name');
                var value = $(this).val();
                
                if ($(this).attr('type') === 'checkbox') {
                    value = $(this).is(':checked') ? 1 : 0;
                }
                
                if (name) {
                    settings[name] = value;
                }
            });
            
            // Ensure auto-convert is disabled if backup is disabled
            if (!settings.enable_backup) {
                settings.auto_convert = 0;
            }
            
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_save_settings',
                    nonce: webp_cp_vars.nonce,
                    enable_backup: settings.enable_backup,
                    backup_reminder: settings.backup_reminder,
                    backup_deletion_duration: settings.backup_deletion_duration,
                    custom_duration: settings.custom_duration,
                    auto_convert: settings.auto_convert,
                    lazy_load: settings.lazy_load,
                    compression_quality: settings.compression_quality,
                    serve_webp: settings.serve_webp
                },
                success: function(response) {
                    if (response.success) {
                        $('#webp-cp-success-title').text('Settings Saved');
                        $('#webp-cp-success-message').text(response.data.message);
                        $('#webp-cp-success-popup').show();
                    } else {
                        $('#webp-cp-failed-title').text('Settings Not Saved');
                        $('#webp-cp-failed-message').text(response.data.message);
                        $('#webp-cp-failed-popup').show();
                    }
                },
                error: function() {
                    $('#webp-cp-failed-title').text('Error');
                    $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                    $('#webp-cp-failed-popup').show();
                }
            });
        });
        
        // Revert all images
        $('.webp-cp-revert-all').on('click', function() {
            if (!confirm('Are you sure you want to revert all converted images back to their original format? This action cannot be undone.')) {
                return;
            }
            
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_revert_all',
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#webp-cp-success-title').text('Revert Successful');
                        $('#webp-cp-success-message').text(response.data.message);
                        $('#webp-cp-success-popup').show();
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $('#webp-cp-failed-title').text('Revert Failed');
                        $('#webp-cp-failed-message').text(response.data.message);
                        $('#webp-cp-failed-popup').show();
                    }
                },
                error: function() {
                    $('#webp-cp-failed-title').text('Error');
                    $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                    $('#webp-cp-failed-popup').show();
                }
            });
        });
        
        // Reset everything
        $('.webp-cp-reset-everything').on('click', function() {
            if (!confirm('Are you sure you want to reset everything? This will:\n\n1. Revert all converted images\n2. Clear all activity logs\n3. Reset all settings to default\n4. Delete all backup files\n\nThis action cannot be undone!')) {
                return;
            }
            
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_reset_everything',
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#webp-cp-success-title').text('Reset Successful');
                        $('#webp-cp-success-message').text(response.data.message);
                        $('#webp-cp-success-popup').show();
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $('#webp-cp-failed-title').text('Reset Failed');
                        $('#webp-cp-failed-message').text(response.data.message);
                        $('#webp-cp-failed-popup').show();
                    }
                },
                error: function() {
                    $('#webp-cp-failed-title').text('Error');
                    $('#webp-cp-failed-message').text('An error occurred. Please try again.');
                    $('#webp-cp-failed-popup').show();
                }
            });
        });
        
        // Save compression quality
        window.saveCompressionQuality = function(compressionQuality) {
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_save_compression_level',
                    compression_level: compressionQuality,
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    // Settings saved successfully
                }
            });
        };
    });
</script>

