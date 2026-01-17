<?php
/**
 * Dashboard template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>


<div class="wrap">
    <div x-data="{ serverHealthModalOpen: false, settingsModalOpen: false }" class="font-display bg-background-light dark:bg-background-dark">
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
                <button @click="settingsModalOpen = true" class="flex size-10 items-center justify-center rounded-full bg-background-light dark:bg-background-dark hover:bg-gray-200 dark:hover:bg-gray-700">
                    <span class="material-symbols-outlined"> settings </span>
                </button>
            </div>
        </header>
        <main class="flex-1 px-4 sm:px-6 lg:px-10 py-8">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl font-bold text-black/90 dark:text-white/90 mb-6">Dashboard</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8" id="webp-cp-stats-container">
                    <!-- Stats will be loaded here via AJAX -->
                </div>
                
                <!-- Server Status Check -->
                <div class="bg-white dark:bg-background-dark rounded-xl p-6 shadow-sm border border-black/5 dark:border-white/5 mb-8">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-xl font-bold text-black/90 dark:text-white/90">Server Health Check</h3>
                        <button @click="serverHealthModalOpen = true" class="flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-colors">
                            <span class="material-symbols-outlined">health_and_safety</span>
                            <span>View Full Server Health Check</span>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php
                        $server_status = webp_cp_get_server_status();
                        $webp_supported = webp_cp_is_webp_supported();
                        $memory_ok = wp_convert_hr_to_bytes($server_status['memory_limit']) >= (128 * 1024 * 1024);
                        ?>
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full <?php echo $server_status['gd_loaded'] ? 'bg-green-500' : 'bg-red-500'; ?>"></div>
                            <span class="text-sm font-medium">GD Extension: <?php echo $server_status['gd_loaded'] ? 'Enabled' : 'Disabled'; ?></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full <?php echo $webp_supported ? 'bg-green-500' : 'bg-red-500'; ?>"></div>
                            <span class="text-sm font-medium">WebP Support: <?php echo $webp_supported ? 'Available' : 'Not Available'; ?></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full <?php echo $memory_ok ? 'bg-green-500' : 'bg-yellow-500'; ?>"></div>
                            <span class="text-sm font-medium">Memory: <?php echo esc_html($server_status['memory_limit']); ?></span>
                        </div>
                    </div>
                    <?php if (!$webp_supported): ?>
                        <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800/60 rounded-lg">
                            <p class="text-sm text-red-800 dark:text-red-300">
                                <strong>Warning:</strong> WebP conversion is not supported on this server. Please contact your hosting provider to enable GD extension with WebP support.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="bg-white dark:bg-background-dark rounded-xl p-6 shadow-sm border border-black/5 dark:border-white/5 mb-8">
                    <h3 class="text-xl font-bold text-black/90 dark:text-white/90 mb-5">Conversion Options</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <button class="webp-cp-convert-single flex items-center justify-center gap-2 rounded-lg h-12 px-5 bg-primary/10 dark:bg-primary/20 text-primary text-sm font-bold hover:bg-primary/20 dark:hover:bg-primary/30 transition-colors">
                            <span class="material-symbols-outlined">image</span>
                            <span class="truncate">Convert Single Image</span>
                        </button>
                        <button class="webp-cp-convert-multiple flex items-center justify-center gap-2 rounded-lg h-12 px-5 bg-primary/10 dark:bg-primary/20 text-primary text-sm font-bold hover:bg-primary/20 dark:hover:bg-primary/30 transition-colors">
                            <span class="material-symbols-outlined">collections</span>
                            <span class="truncate">Convert Multiple Images</span>
                        </button>
                        <button class="webp-cp-convert-url flex items-center justify-center gap-2 rounded-lg h-12 px-5 bg-primary/10 dark:bg-primary/20 text-primary text-sm font-bold hover:bg-primary/20 dark:hover:bg-primary/30 transition-colors">
                            <span class="material-symbols-outlined">link</span>
                            <span class="truncate">Convert by URL</span>
                        </button>
                        <button class="webp-cp-convert-all flex items-center justify-center gap-2 rounded-lg h-12 px-5 bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-colors">
                            <span class="material-symbols-outlined">bolt</span>
                            <span class="truncate">Convert All Media</span>
                        </button>
                    </div>
                    <h3 class="text-xl font-bold text-black/90 dark:text-white/90 mb-5 pt-4 border-t border-black/10 dark:border-white/10">WebP Compression</h3>
                    <div x-data="{ compressionLevel: <?php echo absint(get_option('webp_cp_compression_quality', 82)); ?> }" class="space-y-4">
                        <label class="block text-sm font-medium text-black/60 dark:text-white/60" for="compression-level">Image Compression Quality</label>
                        
                        <!-- Enhanced Compression Level Display -->
                        <div class="relative">
                            <!-- Enhanced Slider -->
                            <div class="relative">
                                <input x-model="compressionLevel" @change="saveCompressionLevel(compressionLevel)" 
                                       class="w-full h-3 bg-gradient-to-r from-red-200 via-yellow-200 to-green-200 dark:from-red-900/30 dark:via-yellow-900/30 dark:to-green-900/30 rounded-lg appearance-none cursor-pointer slider-thumb-enhanced" 
                                       id="compression-level" max="100" min="40" type="range" />
                                
                                <!-- Current Value Display -->
                                <div class="absolute -top-12 left-1/2 transform -translate-x-1/2">
                                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1 shadow-lg">
                                        <span class="text-lg font-bold text-gray-900 dark:text-white" x-text="`${compressionLevel}%`"></span>
                                    </div>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-white dark:border-t-gray-800"></div>
                                </div>
                            </div>
                            
                            <!-- Enhanced Labels -->
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-3">
                                <div class="text-center">
                                    <div class="font-medium">40%</div>
                                    <div class="text-xs">High Compression</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-medium">70%</div>
                                    <div class="text-xs">Balanced</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-medium">100%</div>
                                    <div class="text-xs">Lossless</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-background-dark rounded-xl shadow-sm border border-black/5 dark:border-white/5">
                    <div class="flex flex-col sm:flex-row items-center justify-between p-6">
                        <h3 class="text-xl font-bold text-black/90 dark:text-white/90 mb-4 sm:mb-0">Activity Log</h3>
                        <a class="flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold hover:bg-primary/90 focus:bg-primary/90 transition-colors" href="<?php echo esc_url(admin_url('admin.php?page=webp-converter-pro-activity-log')); ?>">
                            <span class="material-symbols-outlined">wysiwyg</span>
                            <span>Full View</span>
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-black/80 dark:text-white/80">
                            <thead class="text-xs text-black/60 dark:text-white/60 uppercase bg-black/5 dark:bg-white/5">
                                <tr>
                                    <th class="px-6 py-3" scope="col">Original Image</th>
                                    <th class="px-6 py-3" scope="col">WebP Image</th>
                                    <th class="px-6 py-3" scope="col">Status</th>
                                    <th class="px-6 py-3" scope="col">Date</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody id="webp-cp-activity-log-container">
                                <!-- Activity log will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Backup Status Widget -->
                <div class="bg-white dark:bg-background-dark rounded-xl p-6 shadow-sm border border-black/5 dark:border-white/5 mt-8">
                    <h3 class="text-xl font-bold text-black/90 dark:text-white/90 mb-5">Backup Status</h3>
                    <?php
                    $backup_enabled = get_option('webp_cp_enable_backup', 1);
                    $backup_reminder = get_option('webp_cp_backup_reminder', 0);
                    $deletion_duration = get_option('webp_cp_backup_deletion_duration', '30');
                    $deletion_date = get_option('webp_cp_backup_deletion_date', '');
                    
                    if ($backup_enabled) {
                        if ($deletion_duration === 'Never') {
                            echo '<div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800/60 rounded-lg">';
                            echo '<span class="material-symbols-outlined text-green-600">check_circle</span>';
                            echo '<div>';
                            echo '<p class="text-sm font-medium text-green-800 dark:text-green-300">Backups are enabled and will be kept permanently</p>';
                            echo '<p class="text-xs text-green-600 dark:text-green-400">Your original images are safe</p>';
                            echo '</div>';
                            echo '</div>';
                        } else {
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
                            
                            $days_until_deletion = (strtotime($deletion_date) - time()) / DAY_IN_SECONDS;
                            
                            if ($days_until_deletion <= 3 && $days_until_deletion > 0) {
                                $days = floor($days_until_deletion);
                                $hours = floor(($days_until_deletion - $days) * 24);
                                $time_text = $days > 0 ? $days . ' day' . ($days > 1 ? 's' : '') : $hours . ' hour' . ($hours > 1 ? 's' : '');
                                
                                echo '<div class="flex items-center gap-3 p-4 bg-orange-50 dark:bg-orange-900/30 border border-orange-200 dark:border-orange-800/60 rounded-lg">';
                                echo '<span class="material-symbols-outlined text-orange-600">warning</span>';
                                echo '<div>';
                                echo '<p class="text-sm font-medium text-orange-800 dark:text-orange-300">Backup deletion in ' . esc_html($time_text) . '</p>';
                                echo '<p class="text-xs text-orange-600 dark:text-orange-400">Scheduled for ' . esc_html(date('M j, Y', strtotime($deletion_date))) . '</p>';
                                echo '</div>';
                                echo '</div>';
                            } else {
                                echo '<div class="flex items-center gap-3 p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800/60 rounded-lg">';
                                echo '<span class="material-symbols-outlined text-blue-600">info</span>';
                                echo '<div>';
                                echo '<p class="text-sm font-medium text-blue-800 dark:text-blue-300">Backups enabled for ' . esc_html($deletion_duration) . ' days</p>';
                                echo '<p class="text-xs text-blue-600 dark:text-blue-400">Next deletion: ' . esc_html(date('M j, Y', strtotime($deletion_date))) . '</p>';
                                echo '</div>';
                                echo '</div>';
                            }
                        }
                    } else {
                        echo '<div class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800/60 rounded-lg">';
                        echo '<span class="material-symbols-outlined text-red-600">error</span>';
                        echo '<div>';
                        echo '<p class="text-sm font-medium text-red-800 dark:text-red-300">Backups are disabled</p>';
                        echo '<p class="text-xs text-red-600 dark:text-red-400">Original images will not be preserved</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </main>
        <footer class="mt-auto">
            <div class="mx-auto max-w-7xl py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">❤️ Made with love by Soovex IT Agency</p>
            </div>
        </footer>
        
        <!-- Server Health Check Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-show="serverHealthModalOpen" style="display: none;">
            <div @click="serverHealthModalOpen = false" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm" x-show="serverHealthModalOpen"></div>
            <div @click.outside="serverHealthModalOpen = false" class="relative w-full max-w-4xl overflow-hidden rounded-xl bg-background-light dark:bg-background-dark shadow-2xl max-h-[90vh] overflow-y-auto" x-show="serverHealthModalOpen">
                <header class="flex items-center justify-between whitespace-nowrap border-b border-gray-200 dark:border-gray-800 px-10 py-3 sticky top-0 bg-background-light dark:bg-background-dark z-10">
                    <div class="flex items-center gap-4">
                        <div class="size-6 text-primary">
                            <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                                <path clip-rule="evenodd" d="M24 4H42V17.3333V30.6667H24V44H6V30.6667V17.3333H24V4Z" fill="currentColor" fill-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold">Full Server Health Check</h2>
                    </div>
                    <button @click="serverHealthModalOpen = false" class="flex size-10 items-center justify-center rounded-full bg-background-light dark:bg-background-dark hover:bg-gray-200 dark:hover:bg-gray-700">
                        <span class="material-symbols-outlined"> close </span>
                    </button>
                </header>
                <main class="p-10">
                    <?php
                    $health = webp_cp_get_detailed_server_health();
                    ?>
                    <div class="space-y-6">
                        <!-- PHP Information -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-bold text-black/90 dark:text-white/90 mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined">code</span>
                                PHP Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">PHP Version:</span>
                                    <span class="ml-2 text-sm font-medium <?php echo $health['php_version_ok'] ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo esc_html($health['php_version']); ?>
                                        <?php echo $health['php_version_ok'] ? '✓' : '✗'; ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Max Execution Time:</span>
                                    <span class="ml-2 text-sm font-medium"><?php echo esc_html($health['max_execution_time']); ?>s</span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Max Input Time:</span>
                                    <span class="ml-2 text-sm font-medium"><?php echo esc_html($health['max_input_time']); ?>s</span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Max Input Vars:</span>
                                    <span class="ml-2 text-sm font-medium"><?php echo esc_html($health['max_input_vars']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Memory Information -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-bold text-black/90 dark:text-white/90 mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined">memory</span>
                                Memory Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">PHP Memory Limit:</span>
                                    <span class="ml-2 text-sm font-medium <?php echo $health['memory_limit_ok'] ? 'text-green-600' : 'text-yellow-600'; ?>">
                                        <?php echo esc_html($health['memory_limit']); ?>
                                        <?php echo $health['memory_limit_ok'] ? '✓' : '⚠'; ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">WP Memory Limit:</span>
                                    <span class="ml-2 text-sm font-medium"><?php echo esc_html($health['wp_memory_limit']); ?></span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">WP Max Memory Limit:</span>
                                    <span class="ml-2 text-sm font-medium"><?php echo esc_html($health['wp_max_memory_limit']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- GD Extension Information -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-bold text-black/90 dark:text-white/90 mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined">image</span>
                                GD Extension Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">GD Extension:</span>
                                    <span class="ml-2 text-sm font-medium <?php echo $health['gd_loaded'] ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo $health['gd_loaded'] ? 'Loaded ✓' : 'Not Loaded ✗'; ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">WebP Support:</span>
                                    <span class="ml-2 text-sm font-medium <?php echo $health['webp_supported'] ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo $health['webp_supported'] ? 'Available ✓' : 'Not Available ✗'; ?>
                                    </span>
                                </div>
                                <?php if ($health['gd_loaded'] && !empty($health['gd_info'])): ?>
                                    <div class="md:col-span-2">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">GD Version:</span>
                                        <span class="ml-2 text-sm font-medium"><?php echo esc_html($health['gd_info']['GD Version'] ?? 'Unknown'); ?></span>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">JPEG Support:</span>
                                        <span class="ml-2 text-sm font-medium <?php echo !empty($health['gd_info']['JPEG Support']) ? 'text-green-600' : 'text-red-600'; ?>">
                                            <?php echo !empty($health['gd_info']['JPEG Support']) ? 'Yes ✓' : 'No ✗'; ?>
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">PNG Support:</span>
                                        <span class="ml-2 text-sm font-medium <?php echo !empty($health['gd_info']['PNG Support']) ? 'text-green-600' : 'text-red-600'; ?>">
                                            <?php echo !empty($health['gd_info']['PNG Support']) ? 'Yes ✓' : 'No ✗'; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Upload Information -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-bold text-black/90 dark:text-white/90 mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined">upload_file</span>
                                Upload Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Upload Max Filesize:</span>
                                    <span class="ml-2 text-sm font-medium"><?php echo esc_html($health['upload_max_filesize']); ?></span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Post Max Size:</span>
                                    <span class="ml-2 text-sm font-medium"><?php echo esc_html($health['post_max_size']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Server Information -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-bold text-black/90 dark:text-white/90 mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined">dns</span>
                                Server Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Server Software:</span>
                                    <span class="ml-2 text-sm font-medium"><?php echo esc_html($health['server_software']); ?></span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">WordPress Version:</span>
                                    <span class="ml-2 text-sm font-medium"><?php echo esc_html($health['wp_version']); ?></span>
                                </div>
                                <?php if ($health['disk_free_space'] !== false): ?>
                                    <div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Free Disk Space:</span>
                                        <span class="ml-2 text-sm font-medium"><?php echo esc_html(webp_cp_format_filesize($health['disk_free_space'])); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($health['disk_total_space'] !== false): ?>
                                    <div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Disk Space:</span>
                                        <span class="ml-2 text-sm font-medium"><?php echo esc_html(webp_cp_format_filesize($health['disk_total_space'])); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        
        <!-- Convert Single Image Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" id="webp-cp-convert-single-modal" style="display: none;">
            <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm" id="webp-cp-convert-single-modal-backdrop"></div>
            <div class="relative w-full max-w-2xl overflow-hidden rounded-xl bg-background-light dark:bg-background-dark shadow-2xl">
                <header class="flex items-center justify-between whitespace-nowrap border-b border-gray-200 dark:border-gray-800 px-10 py-3">
                    <div class="flex items-center gap-4">
                        <div class="size-6 text-primary">
                            <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                                <path clip-rule="evenodd" d="M24 4H42V17.3333V30.6667H24V44H6V30.6667V17.3333H24V4Z" fill="currentColor" fill-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold">Convert Single Image</h2>
                    </div>
                    <button class="webp-cp-close-modal flex size-10 items-center justify-center rounded-full bg-background-light dark:bg-background-dark hover:bg-gray-200 dark:hover:bg-gray-700">
                        <span class="material-symbols-outlined"> close </span>
                    </button>
                </header>
                <main class="p-10">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-black/60 dark:text-white/60 mb-2" for="webp-cp-single-image">Select Image</label>
                        <div class="flex items-center gap-4">
                            <button id="webp-cp-select-single-image" class="flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-colors">
                                <span class="material-symbols-outlined">upload_file</span>
                                <span>Select Image</span>
                            </button>
                            <div id="webp-cp-single-image-name" class="text-sm text-black/80 dark:text-white/80"></div>
                            <input type="hidden" id="webp-cp-single-image-id" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-4">
                        <button class="webp-cp-close-modal flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-black/10 dark:bg-white/10 text-black/80 dark:text-white/80 text-sm font-bold hover:bg-black/20 dark:hover:bg-white/20 transition-colors">
                            <span>Cancel</span>
                        </button>
                        <button id="webp-cp-convert-single-image-btn" class="flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-colors">
                            <span>Convert</span>
                        </button>
                    </div>
                </main>
            </div>
        </div>
        
        <!-- Convert Multiple Images Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" id="webp-cp-convert-multiple-modal" style="display: none;">
            <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm" id="webp-cp-convert-multiple-modal-backdrop"></div>
            <div class="relative w-full max-w-2xl overflow-hidden rounded-xl bg-background-light dark:bg-background-dark shadow-2xl">
                <header class="flex items-center justify-between whitespace-nowrap border-b border-gray-200 dark:border-gray-800 px-10 py-3">
                    <div class="flex items-center gap-4">
                        <div class="size-6 text-primary">
                            <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                                <path clip-rule="evenodd" d="M24 4H42V17.3333V30.6667H24V44H6V30.6667V17.3333H24V4Z" fill="currentColor" fill-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold">Convert Multiple Images</h2>
                    </div>
                    <button class="webp-cp-close-modal flex size-10 items-center justify-center rounded-full bg-background-light dark:bg-background-dark hover:bg-gray-200 dark:hover:bg-gray-700">
                        <span class="material-symbols-outlined"> close </span>
                    </button>
                </header>
                <main class="p-10">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-black/60 dark:text-white/60 mb-2" for="webp-cp-multiple-images">Select Images</label>
                        <div class="flex items-center gap-4 mb-3">
                            <button id="webp-cp-select-multiple-images" class="flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-colors">
                                <span class="material-symbols-outlined">upload_file</span>
                                <span>Select Images</span>
                            </button>
                            <div id="webp-cp-multiple-images-count" class="text-sm text-black/80 dark:text-white/80"></div>
                            <input type="hidden" id="webp-cp-multiple-images-ids" />
                        </div>
                        <div class="flex items-start gap-2 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800/60 rounded-lg">
                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-lg mt-0.5">info</span>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-1">How to select multiple images:</p>
                                <p class="text-xs text-blue-700 dark:text-blue-400">
                                    Click the "Select Images" button above, then in the media library, hold <kbd class="px-1.5 py-0.5 bg-blue-100 dark:bg-blue-800 border border-blue-300 dark:border-blue-700 rounded text-xs font-mono">Ctrl</kbd> (Windows/Linux) or <kbd class="px-1.5 py-0.5 bg-blue-100 dark:bg-blue-800 border border-blue-300 dark:border-blue-700 rounded text-xs font-mono">Cmd</kbd> (Mac) while clicking images to select multiple files.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-4">
                        <button class="webp-cp-close-modal flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-black/10 dark:bg-white/10 text-black/80 dark:text-white/80 text-sm font-bold hover:bg-black/20 dark:hover:bg-white/20 transition-colors">
                            <span>Cancel</span>
                        </button>
                        <button id="webp-cp-convert-multiple-images-btn" class="flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-colors">
                            <span>Convert</span>
                        </button>
                    </div>
                </main>
            </div>
        </div>
        
        <!-- Convert by URL Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" id="webp-cp-convert-url-modal" style="display: none;">
            <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm" id="webp-cp-convert-url-modal-backdrop"></div>
            <div class="relative w-full max-w-2xl overflow-hidden rounded-xl bg-background-light dark:bg-background-dark shadow-2xl">
                <header class="flex items-center justify-between whitespace-nowrap border-b border-gray-200 dark:border-gray-800 px-10 py-3">
                    <div class="flex items-center gap-4">
                        <div class="size-6 text-primary">
                            <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                                <path clip-rule="evenodd" d="M24 4H42V17.3333V30.6667H24V44H6V30.6667V17.3333H24V4Z" fill="currentColor" fill-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold">Convert by URL</h2>
                    </div>
                    <button class="webp-cp-close-modal flex size-10 items-center justify-center rounded-full bg-background-light dark:bg-background-dark hover:bg-gray-200 dark:hover:bg-gray-700">
                        <span class="material-symbols-outlined"> close </span>
                    </button>
                </header>
                <main class="p-10">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-black/60 dark:text-white/60 mb-2" for="webp-cp-image-url">Image URL</label>
                        <input type="url" id="webp-cp-image-url" class="w-full px-4 py-2 bg-black/5 dark:bg-white/5 rounded-lg border border-black/10 dark:border-white/10 focus:border-primary focus:ring-1 focus:ring-primary" placeholder="https://example.com/image.jpg" />
                    </div>
                    <div class="flex justify-end gap-4">
                        <button class="webp-cp-close-modal flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-black/10 dark:bg-white/10 text-black/80 dark:text-white/80 text-sm font-bold hover:bg-black/20 dark:hover:bg-white/20 transition-colors">
                            <span>Cancel</span>
                        </button>
                        <button id="webp-cp-convert-url-btn" class="flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-colors">
                            <span>Convert</span>
                        </button>
                    </div>
                </main>
            </div>
        </div>
        
        <!-- Convert All Media Confirmation Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" id="webp-cp-convert-all-modal" style="display: none;">
            <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm" id="webp-cp-convert-all-modal-backdrop"></div>
            <div class="relative w-full max-w-md overflow-hidden rounded-xl bg-background-light dark:bg-background-dark shadow-2xl">
                <header class="flex items-center justify-between whitespace-nowrap border-b border-gray-200 dark:border-gray-800 px-10 py-3">
                    <div class="flex items-center gap-4">
                        <div class="size-6 text-primary">
                            <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                                <path clip-rule="evenodd" d="M24 4H42V17.3333V30.6667H24V44H6V30.6667V17.3333H24V4Z" fill="currentColor" fill-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold">Convert All Media</h2>
                    </div>
                    <button class="webp-cp-close-modal flex size-10 items-center justify-center rounded-full bg-background-light dark:bg-background-dark hover:bg-gray-200 dark:hover:bg-gray-700">
                        <span class="material-symbols-outlined"> close </span>
                    </button>
                </header>
                <main class="p-10">
                    <div class="mb-6">
                        <p class="text-black/80 dark:text-white/80">Are you sure you want to convert all images in your media library to WebP format? This process may take some time depending on the number of images.</p>
                    </div>
                    <div class="flex justify-end gap-4">
                        <button class="webp-cp-close-modal flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-black/10 dark:bg-white/10 text-black/80 dark:text-white/80 text-sm font-bold hover:bg-black/20 dark:hover:bg-white/20 transition-colors">
                            <span>Cancel</span>
                        </button>
                        <button id="webp-cp-convert-all-btn" class="flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-colors">
                            <span>Convert All</span>
                        </button>
                    </div>
                </main>
            </div>
        </div>
        
        <?php WebP_CP_Settings::get_instance()->render_settings_modal(); ?>
        
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
                    <div class="w-16 h-16 flex items-center justify-center bg-error/20 rounded-full">
                        <span class="material-symbols-outlined text-error text-4xl">error</span>
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
    </div>
</div>