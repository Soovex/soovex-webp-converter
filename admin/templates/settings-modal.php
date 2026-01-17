<?php
/**
 * Settings modal template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$settings = WebP_CP_Settings::get_instance()->get_settings();
?>
<div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-show="settingsModalOpen" style="display: none;">
    <div @click="settingsModalOpen = false" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm" x-show="settingsModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-end="opacity-100" x-transition:enter-start="opacity-0" x-transition:leave="ease-in duration-200" x-transition:leave-end="opacity-0" x-transition:leave-start="opacity-100"></div>
    <div @click.outside="settingsModalOpen = false" class="relative w-full max-w-2xl overflow-hidden rounded-xl bg-background-light dark:bg-background-dark shadow-2xl" x-show="settingsModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-end="opacity-100 scale-100" x-transition:enter-start="opacity-0 scale-95" x-transition:leave="ease-in duration-200" x-transition:leave-end="opacity-0 scale-95" x-transition:leave-start="opacity-100 scale-100">
        <header class="flex items-center justify-between whitespace-nowrap border-b border-gray-200 dark:border-gray-800 px-10 py-3">
            <div class="flex items-center gap-4">
                <div class="size-6 text-primary">
                    <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path clip-rule="evenodd" d="M24 4H42V17.3333V30.6667H24V44H6V30.6667V17.3333H24V4Z" fill="currentColor" fill-rule="evenodd"></path>
                    </svg>
                </div>
                <h2 class="text-lg font-bold">Soovex WebP Converter Settings</h2>
            </div>
            <div class="flex items-center gap-4">
                <button @click="settingsModalOpen = false" class="flex size-10 items-center justify-center rounded-full bg-background-light dark:bg-background-dark hover:bg-gray-200 dark:hover:bg-gray-700">
                    <span class="material-symbols-outlined"> close </span>
                </button>
            </div>
        </header>
        <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
            <div class="space-y-4 border-b border-gray-200 dark:border-gray-700 pb-6">
                <h4 class="text-lg font-medium text-gray-900 dark:text-white">Data Backup &amp; Recovery</h4>
                <div class="flex items-start justify-between">
                    <div>
                        <label class="font-medium" for="backup">Enable Data Backup</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Keep original images as a backup after conversion.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input <?php checked($settings['enable_backup'], 1); ?> class="sr-only peer" id="backup" type="checkbox" name="enable_backup" value="1" />
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 dark:peer-focus:ring-primary/80 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                    </label>
                </div>
                <div class="flex items-start justify-between">
                    <div>
                        <label class="font-medium" for="reminder">Set Backup Reminder</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Get notified before the backup data is automatically deleted.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input <?php checked($settings['backup_reminder'], 1); ?> class="sr-only peer" id="reminder" type="checkbox" name="backup_reminder" value="1" />
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 dark:peer-focus:ring-primary/80 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                    </label>
                </div>
                <div class="space-y-2">
                    <label class="block font-medium" for="deletion-time">Automatically Delete Original Data from Backup</label>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm text-gray-500 dark:text-gray-400" for="deletion-duration">After a duration:</label>
                            <select class="form-select w-full rounded-lg bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-primary/50 text-sm mt-1" id="deletion-duration" name="backup_deletion_duration" onchange="toggleCustomDuration()" style="width: 100%;max-width:100%">
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
                <div class="flex items-start gap-3 rounded-lg bg-red-50 dark:bg-red-900/30 p-4 mt-4 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800/60">
                    <span class="material-symbols-outlined mt-0.5 text-xl text-red-600 dark:text-red-400">warning</span>
                    <div class="flex-1">
                        <p class="text-sm font-bold">CRITICAL WARNING</p>
                        <p class="text-sm">Once the original data is deleted from the backup, it is <span class="font-semibold">permanently irrecoverable</span>. This action cannot be undone. Please ensure you have secured your data elsewhere if needed before proceeding.</p>
                    </div>
                </div>
            </div>
            <div class="space-y-4 pt-6">
                <h4 class="text-lg font-medium text-gray-900 dark:text-white">Other Features</h4>
                <div class="flex items-start justify-between">
                    <div>
                        <label class="font-medium" for="auto-convert">Auto-convert new uploads</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Automatically convert images to WebP upon uploading to the media library.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input <?php checked($settings['auto_convert'], 1); ?> class="sr-only peer" id="auto-convert" type="checkbox" name="auto_convert" value="1" />
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 dark:peer-focus:ring-primary/80 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                    </label>
                </div>
                <div class="flex items-start gap-3 rounded-lg bg-blue-50 dark:bg-blue-900/30 p-4 mt-2 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60">
                    <span class="material-symbols-outlined mt-0.5 text-xl text-blue-600 dark:text-blue-400">info</span>
                    <div class="flex-1">
                        <p class="text-sm font-bold">Note</p>
                        <p class="text-sm">Auto-convert requires backup to be enabled. If backup is disabled, auto-convert will also be disabled.</p>
                    </div>
                </div>

                <div x-data="{ compressionQuality: <?php echo esc_attr($settings['compression_quality']); ?> }" class="space-y-2">
                    <label class="block font-medium" for="compression-quality">Image Compression Quality</label>
                    <input x-model="compressionQuality" @change="saveCompressionQuality(compressionQuality)" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700" id="compression-quality" name="compression_quality" max="100" min="40" type="range" />
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>40% (High Compression)</span>
                        <span x-text="`${compressionQuality}%`"></span>
                        <span>100% (Lossless)</span>
                    </div>
                </div>
                
                <div class="flex items-start justify-between">
                    <div>
                        <label class="font-medium" for="lazy-load">Enable Lazy Loading</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Improve page load times by loading images only when they enter the viewport.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input <?php checked($settings['lazy_load'], 1); ?> class="sr-only peer" id="lazy-load" type="checkbox" name="lazy_load" value="1" />
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 dark:peer-focus:ring-primary/80 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                    </label>
                </div>
                
                <!-- Accordion for Lazy Load Information -->
                <div class="mt-4" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white focus:outline-none">
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
                    <div>
                        <label class="font-medium" for="serve-webp">Serve WebP Images</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Automatically serve WebP images to browsers that support them for better performance.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input <?php checked($settings['serve_webp'], 1); ?> class="sr-only peer" id="serve-webp" type="checkbox" name="serve_webp" value="1" />
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 dark:peer-focus:ring-primary/80 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                    </label>
                </div>
                
            </div>
        </div>
        <div class="flex flex-col sm:flex-row justify-between items-center p-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 gap-4">
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto order-1 sm:order-1">
                <button class="webp-cp-revert-all w-full sm:w-auto flex items-center justify-center gap-2 rounded-lg bg-primary py-2.5 px-6 text-sm font-semibold text-white hover:bg-primary/90 transition-colors shadow-sm">
                    <span class="material-symbols-outlined">settings_backup_restore</span> All Data Recovery
                </button>
                <button class="webp-cp-reset-everything w-full sm:w-auto flex items-center justify-center gap-2 rounded-lg bg-red-600 py-2.5 px-6 text-sm font-semibold text-white hover:bg-red-700 transition-colors shadow-sm">
                    <span class="material-symbols-outlined">refresh</span> Reset Everything
                </button>
            </div>
            <button class="webp-cp-save-settings w-full sm:w-auto rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 py-2.5 px-6 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors order-2 sm:order-2"> Save Changes </button>
        </div>
    </div>
</div>