<?php
/**
 * Help template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <div x-data="{ settingsModalOpen: false }" class="font-display bg-background-light dark:bg-background-dark">
        <header class="flex items-center justify-between whitespace-nowrap border-b border-gray-200 dark:border-gray-800 px-10 py-3">
            <div class="flex items-center gap-4">
                <div class="size-6 text-primary">
                    <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path clip-rule="evenodd" d="M24 4H42V17.3333V30.6667H24V44H6V30.6667V17.3333H24V4Z" fill="currentColor" fill-rule="evenodd"></path>
                    </svg>
                </div>
                <h2 class="text-lg font-bold">Soovex WebP Converter</h2>
            </div>
            <div class="flex items-center gap-4">
                <div class="relative">
                    <button @click="settingsModalOpen = true" class="flex size-10 items-center justify-center rounded-full bg-background-light dark:bg-background-dark hover:bg-gray-200 dark:hover:bg-gray-700">
                        <span class="material-symbols-outlined"> settings </span>
                    </button>
                </div>
            </div>
        </header>
        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white mb-2">Help & Documentation</h1>
                    <p class="text-gray-600 dark:text-gray-400">Complete guide to all features and options in Soovex WebP Converter</p>
                </div>

                <!-- Table of Contents -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">menu_book</span>
                        Table of Contents
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <a href="#conversion-options" class="flex items-center gap-2 text-primary hover:text-primary/80 transition-colors">
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            <span>Conversion Options</span>
                        </a>
                        <a href="#compression-settings" class="flex items-center gap-2 text-primary hover:text-primary/80 transition-colors">
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            <span>Compression Settings</span>
                        </a>
                        <a href="#backup-settings" class="flex items-center gap-2 text-primary hover:text-primary/80 transition-colors">
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            <span>Backup & Recovery</span>
                        </a>
                        <a href="#auto-features" class="flex items-center gap-2 text-primary hover:text-primary/80 transition-colors">
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            <span>Auto Features</span>
                        </a>
                        <a href="#activity-log" class="flex items-center gap-2 text-primary hover:text-primary/80 transition-colors">
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            <span>Activity Log</span>
                        </a>
                        <a href="#advanced-options" class="flex items-center gap-2 text-primary hover:text-primary/80 transition-colors">
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            <span>Advanced Options</span>
                        </a>
                    </div>
                </div>

                <!-- Conversion Options -->
                <div id="conversion-options" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary text-3xl">image</span>
                        Conversion Options
                    </h2>
                    
                    <div class="space-y-6">
                        <!-- Convert Single Image -->
                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">photo</span>
                                Convert Single Image
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Convert one image at a time from your media library to WebP format.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">How to use:</p>
                                <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li>Click the "Convert Single Image" button on the Dashboard</li>
                                    <li>Select an image from your media library</li>
                                    <li>Click "Convert" to start the conversion</li>
                                    <li>The converted WebP image will be saved automatically</li>
                                </ol>
                            </div>
                            <div class="mt-3 flex items-start gap-2 text-sm text-blue-600 dark:text-blue-400">
                                <span class="material-symbols-outlined text-base">info</span>
                                <span><strong>Note:</strong> Only JPEG and PNG images can be converted to WebP format.</span>
                            </div>
                        </div>

                        <!-- Convert Multiple Images -->
                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">photo_library</span>
                                Convert Multiple Images
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Convert multiple images at once from your media library.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">How to use:</p>
                                <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li>Click the "Convert Multiple Images" button on the Dashboard</li>
                                    <li>Click "Select Images" to open the media library</li>
                                    <li>Hold <kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-xs font-mono">Ctrl</kbd> (Windows/Linux) or <kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-xs font-mono">Cmd</kbd> (Mac) while clicking to select multiple images</li>
                                    <li>Click "Use these images" to confirm your selection</li>
                                    <li>Click "Convert" to start batch conversion</li>
                                    <li>Monitor progress in the progress modal</li>
                                </ol>
                            </div>
                            <div class="mt-3 flex items-start gap-2 text-sm text-green-600 dark:text-green-400">
                                <span class="material-symbols-outlined text-base">check_circle</span>
                                <span><strong>Tip:</strong> Batch conversion processes images in the background, so you can continue working while conversions complete.</span>
                            </div>
                        </div>

                        <!-- Convert by URL -->
                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">link</span>
                                Convert by URL
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Convert an image from an external URL to WebP format.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">How to use:</p>
                                <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li>Click the "Convert by URL" button on the Dashboard</li>
                                    <li>Enter the full URL of the image (must be a direct link to a JPEG or PNG image)</li>
                                    <li>Click "Convert" to download and convert the image</li>
                                    <li>The converted image will be saved to your media library</li>
                                </ol>
                            </div>
                            <div class="mt-3 flex items-start gap-2 text-sm text-yellow-600 dark:text-yellow-400">
                                <span class="material-symbols-outlined text-base">warning</span>
                                <span><strong>Important:</strong> The URL must be publicly accessible and point directly to an image file (not an HTML page).</span>
                            </div>
                        </div>

                        <!-- Convert All Media -->
                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">auto_awesome</span>
                                Convert All Media
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Convert all JPEG and PNG images in your media library to WebP format at once.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">How to use:</p>
                                <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li>Click the "Convert All Media" button on the Dashboard</li>
                                    <li>Confirm the action in the modal dialog</li>
                                    <li>Monitor the progress in the progress modal</li>
                                    <li>The conversion will process all eligible images in your media library</li>
                                </ol>
                            </div>
                            <div class="mt-3 flex items-start gap-2 text-sm text-red-600 dark:text-red-400">
                                <span class="material-symbols-outlined text-base">error</span>
                                <span><strong>Warning:</strong> This action will convert all images. Make sure you have backups enabled if you want to keep originals.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Compression Settings -->
                <div id="compression-settings" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary text-3xl">tune</span>
                        Compression Settings
                    </h2>
                    
                    <div class="space-y-6">
                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">compress</span>
                                Image Compression Quality
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Control the balance between image quality and file size.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quality Levels:</p>
                                <ul class="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li><strong>40-60%:</strong> High compression, smaller file size, noticeable quality loss</li>
                                    <li><strong>60-80%:</strong> Balanced compression, good quality with reasonable file size (recommended)</li>
                                    <li><strong>80-100%:</strong> Low compression, larger file size, minimal quality loss</li>
                                </ul>
                            </div>
                            <div class="mt-3 flex items-start gap-2 text-sm text-blue-600 dark:text-blue-400">
                                <span class="material-symbols-outlined text-base">lightbulb</span>
                                <span><strong>Recommended:</strong> 80-85% provides an excellent balance between quality and file size for most websites.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backup & Recovery -->
                <div id="backup-settings" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary text-3xl">backup</span>
                        Backup & Recovery
                    </h2>
                    
                    <div class="space-y-6">
                        <!-- Enable Backup -->
                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">save</span>
                                Enable Data Backup
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Keep original images as backup after conversion. This allows you to revert conversions if needed.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Benefits:</p>
                                <ul class="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li>Original images are preserved in a backup folder</li>
                                    <li>You can revert any conversion at any time</li>
                                    <li>Provides safety net for all conversions</li>
                                </ul>
                            </div>
                            <div class="mt-3 flex items-start gap-2 text-sm text-yellow-600 dark:text-yellow-400">
                                <span class="material-symbols-outlined text-base">warning</span>
                                <span><strong>Note:</strong> Backups use additional storage space. Monitor your disk usage if you have many images.</span>
                            </div>
                        </div>

                        <!-- Backup Reminder -->
                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">notifications</span>
                                Backup Reminder
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Get notified before backup data is automatically deleted.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">When enabled, you'll receive admin notices 3 days before backups are scheduled for deletion, giving you time to take action if needed.</p>
                            </div>
                        </div>

                        <!-- Auto Delete Backup -->
                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">schedule</span>
                                Automatically Delete Original Data from Backup
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Set how long to keep backup files before automatic deletion.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Options:</p>
                                <ul class="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li><strong>7-365 days:</strong> Keep backups for a specific duration</li>
                                    <li><strong>Custom:</strong> Set your own duration (1-3650 days)</li>
                                    <li><strong>Never:</strong> Keep backups permanently (uses more storage)</li>
                                </ul>
                            </div>
                            <div class="mt-3 flex items-start gap-2 text-sm text-red-600 dark:text-red-400">
                                <span class="material-symbols-outlined text-base">error</span>
                                <span><strong>Critical:</strong> Once deleted, original images cannot be recovered. Ensure you have backups elsewhere if needed.</span>
                            </div>
                        </div>

                        <!-- Revert All -->
                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">settings_backup_restore</span>
                                All Data Recovery (Revert All)
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Revert all converted images back to their original format.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">This action will restore all converted WebP images to their original JPEG/PNG format using the backup files. Only works if backups are enabled and available.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Auto Features -->
                <div id="auto-features" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary text-3xl">auto_awesome</span>
                        Auto Features
                    </h2>
                    
                    <div class="space-y-6">
                        <!-- Auto Convert -->
                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">sync</span>
                                Auto-convert New Uploads
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Automatically convert images to WebP when they are uploaded to the media library.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">How it works:</p>
                                <ul class="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li>When enabled, all new JPEG/PNG uploads are automatically converted</li>
                                    <li>Conversion happens in the background after upload</li>
                                    <li>Original images are kept as backup (if backup is enabled)</li>
                                    <li>No manual intervention required</li>
                                </ul>
                            </div>
                            <div class="mt-3 flex items-start gap-2 text-sm text-blue-600 dark:text-blue-400">
                                <span class="material-symbols-outlined text-base">info</span>
                                <span><strong>Requirement:</strong> Auto-convert requires backup to be enabled. If backup is disabled, auto-convert will also be disabled.</span>
                            </div>
                        </div>

                        <!-- Lazy Loading -->
                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">slow_motion_video</span>
                                Enable Lazy Loading
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Improve page load times by loading images only when they enter the viewport.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Benefits:</p>
                                <ul class="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li>Faster initial page load times</li>
                                    <li>Reduced bandwidth usage</li>
                                    <li>Improved performance on mobile devices</li>
                                    <li>Better user experience on image-heavy pages</li>
                                </ul>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-3">Lazy loading adds the native "loading='lazy'" attribute to all img tags. Modern browsers support this natively without additional JavaScript.</p>
                            </div>
                        </div>

                        <!-- Serve WebP -->
                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">cloud_download</span>
                                Serve WebP Images
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Automatically serve WebP images to browsers that support them for better performance.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">How it works:</p>
                                <ul class="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li>Browsers that support WebP receive the optimized WebP version</li>
                                    <li>Older browsers automatically receive the original format</li>
                                    <li>No changes needed to your theme or content</li>
                                    <li>Works transparently in the background</li>
                                </ul>
                            </div>
                            <div class="mt-3 flex items-start gap-2 text-sm text-green-600 dark:text-green-400">
                                <span class="material-symbols-outlined text-base">check_circle</span>
                                <span><strong>Compatibility:</strong> All modern browsers support WebP. Older browsers will automatically fall back to original formats.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Log -->
                <div id="activity-log" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary text-3xl">history</span>
                        Activity Log
                    </h2>
                    
                    <div class="space-y-6">
                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">list</span>
                                Viewing Activity Log
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Track all conversion activities, including successful conversions, failures, and reversions.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Information displayed:</p>
                                <ul class="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li><strong>Original Image:</strong> Name of the source image</li>
                                    <li><strong>WebP Image:</strong> Name of the converted WebP file</li>
                                    <li><strong>Status:</strong> Conversion status (Converted, Failed, Reverted)</li>
                                    <li><strong>Date:</strong> When the action occurred</li>
                                </ul>
                            </div>
                        </div>

                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">refresh</span>
                                Revert Single Image
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Revert a single converted image back to its original format.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Click the "Revert" button next to any converted image in the Activity Log to restore it to its original format. Only available for successfully converted images with backups.</p>
                            </div>
                        </div>

                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">replay</span>
                                Retry Failed Conversion
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Retry a conversion that previously failed.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Click the "Retry" button next to any failed conversion in the Activity Log to attempt the conversion again. Useful if the failure was due to temporary issues.</p>
                            </div>
                        </div>

                        <div class="border-l-4 border-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">delete</span>
                                Clear Logs
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Clear all activity log entries.</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Use the "Clear Logs" button to remove all activity log entries. This action cannot be undone but does not affect your images.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Options -->
                <div id="advanced-options" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary text-3xl">settings</span>
                        Advanced Options
                    </h2>
                    
                    <div class="space-y-6">
                        <div class="border-l-4 border-red-500 pl-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-red-500">refresh</span>
                                Reset Everything
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-3">Reset all plugin settings and data to default values.</p>
                            <div class="bg-red-50 dark:bg-red-900/30 rounded-lg p-4 border border-red-200 dark:border-red-800/60">
                                <p class="text-sm font-medium text-red-800 dark:text-red-300 mb-2">This action will:</p>
                                <ul class="list-disc list-inside space-y-1 text-sm text-red-700 dark:text-red-400">
                                    <li>Revert all converted images to original format</li>
                                    <li>Clear all activity logs</li>
                                    <li>Reset all settings to default</li>
                                    <li>Delete all backup files</li>
                                </ul>
                                <p class="text-sm font-bold text-red-800 dark:text-red-300 mt-3">⚠️ This action cannot be undone!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips & Best Practices -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg shadow-sm border border-blue-200 dark:border-blue-800 p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-3xl">lightbulb</span>
                        Tips & Best Practices
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-500">check_circle</span>
                                Recommended Settings
                            </h3>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <li>• Enable backups for safety</li>
                                <li>• Set compression quality to 80-85%</li>
                                <li>• Enable auto-convert for new uploads</li>
                                <li>• Enable lazy loading for better performance</li>
                            </ul>
                        </div>
                        
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-green-500">speed</span>
                                Performance Tips
                            </h3>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <li>• Use batch conversion for many images</li>
                                <li>• Monitor disk space with backups enabled</li>
                                <li>• Check Activity Log regularly</li>
                                <li>• Test compression quality on sample images first</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Support -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary text-3xl">support</span>
                        Need More Help?
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">If you need additional assistance or have questions, please visit our support resources:</p>
                    <div class="flex flex-wrap gap-3">
                        <a href="https://soovex.com/" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                            <span class="material-symbols-outlined">language</span>
                            <span>Visit Website</span>
                        </a>
                        <a href="https://soovex.com/" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            <span class="material-symbols-outlined">description</span>
                            <span>Documentation</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>
        <footer class="mt-auto">
            <div class="mx-auto max-w-7xl py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">❤️ Made with love by Soovex IT Agency</p>
            </div>
        </footer>
        
        <?php WebP_CP_Settings::get_instance()->render_settings_modal(); ?>
    </div>
</div>
