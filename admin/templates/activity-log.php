<?php
/**
 * Activity log template
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
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <h1 class="text-3xl font-bold tracking-tight">Activity Log</h1>
                    <button id="webp-cp-clear-logs" class="mt-4 sm:mt-0 flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-red-600 text-white text-sm font-bold hover:bg-red-700 transition-colors">
                        <span class="material-symbols-outlined">delete</span>
                        <span>Clear Logs</span>
                    </button>
                </div>
                <div class="flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <div class="overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400" scope="col">Original Image</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400" scope="col">WebP Image</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400" scope="col">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400" scope="col">Date</th>
                                            <th class="relative px-6 py-3" scope="col">
                                                <span class="sr-only">Action</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-background-light dark:bg-background-dark" id="webp-cp-activity-log-container">
                                        <!-- Activity log will be loaded here via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 sm:px-6 mt-4" id="webp-cp-pagination-container">
                    <!-- Pagination will be loaded here via AJAX -->
                </div>
            </div>
        </main>
        <footer class="mt-auto">
            <div class="mx-auto max-w-7xl py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">❤️ Made with love by Soovex IT Agency</p>
            </div>
        </footer>
        
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