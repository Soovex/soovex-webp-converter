/**
 * Progress Bar JavaScript for WebP Converter Pro
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Progress bar HTML
    var progressBarHTML = `
        <div id="webp-cp-progress-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-2xl border border-gray-200/20 dark:border-gray-700/20">
                <!-- Header with gradient background -->
                <div class="relative bg-gradient-to-r from-primary/10 via-primary/5 to-transparent dark:from-primary/20 dark:via-primary/10 dark:to-transparent p-6 border-b border-gray-200/50 dark:border-gray-700/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/20 dark:bg-primary/30 flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary text-xl animate-pulse">sync</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Converting Images</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400" id="webp-cp-progress-text">Preparing conversion...</p>
                            </div>
                        </div>
                        <button id="webp-cp-progress-close" class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-all duration-200">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>
                </div>
                
                <!-- Progress Section -->
                <div class="p-6 space-y-6">
                    <!-- Progress Bar -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress</span>
                            <span id="webp-cp-progress-percentage" class="text-lg font-bold text-primary">0%</span>
                        </div>
                        <div class="relative">
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                                <div id="webp-cp-progress-bar" class="h-full bg-gradient-to-r from-primary to-primary/80 rounded-full transition-all duration-500 ease-out relative overflow-hidden" style="width: 0%">
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border border-gray-200/50 dark:border-gray-600/50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-sm">image</span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Images</p>
                                    <p id="webp-cp-total" class="text-lg font-bold text-gray-900 dark:text-white">0</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border border-gray-200/50 dark:border-gray-600/50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-sm">check_circle</span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
                                    <p id="webp-cp-completed" class="text-lg font-bold text-gray-900 dark:text-white">0</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border border-gray-200/50 dark:border-gray-600/50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-sm">done</span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Success</p>
                                    <p id="webp-cp-success" class="text-lg font-bold text-gray-900 dark:text-white">0</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border border-gray-200/50 dark:border-gray-600/50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-sm">error</span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Failed</p>
                                    <p id="webp-cp-failed" class="text-lg font-bold text-gray-900 dark:text-white">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Image Processing -->
                    <div id="webp-cp-current-image" class="bg-gradient-to-r from-primary/5 to-transparent dark:from-primary/10 dark:to-transparent rounded-xl p-4 border border-primary/20 dark:border-primary/30" style="display: none;">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-primary/20 dark:bg-primary/30 flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary text-sm animate-spin">refresh</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Currently Processing:</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 truncate" id="webp-cp-current-image-name"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Control Buttons -->
                    <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button id="webp-cp-pause-btn" class="flex-1 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-sm">pause</span>
                            <span>Pause</span>
                        </button>
                        <button id="webp-cp-resume-btn" class="flex-1 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2" style="display: none;">
                            <span class="material-symbols-outlined text-sm">play_arrow</span>
                            <span>Resume</span>
                        </button>
                        <button id="webp-cp-stop-btn" class="flex-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-sm">stop</span>
                            <span>Stop</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add progress bar to page
    $('body').append(progressBarHTML);
    
    var progressModal = $('#webp-cp-progress-modal');
    var progressBar = $('#webp-cp-progress-bar');
    var progressText = $('#webp-cp-progress-text');
    var progressPercentage = $('#webp-cp-progress-percentage');
    var currentImageContainer = $('#webp-cp-current-image');
    var currentImageName = $('#webp-cp-current-image-name');
    var totalEl = $('#webp-cp-total');
    var completedEl = $('#webp-cp-completed');
    var successEl = $('#webp-cp-success');
    var failedEl = $('#webp-cp-failed');
    
    var progressInterval = null;
    var currentProgressKey = null;
    
    // Close progress modal
    $('#webp-cp-progress-close').on('click', function() {
        hideProgressModal();
    });
    
    // Pause button
    $('#webp-cp-pause-btn').on('click', function() {
        if (currentProgressKey) {
            pauseConversion();
        }
    });
    
    // Resume button
    $('#webp-cp-resume-btn').on('click', function() {
        if (currentProgressKey) {
            resumeConversion();
        }
    });
    
    // Stop button
    $('#webp-cp-stop-btn').on('click', function() {
        if (currentProgressKey && confirm('Are you sure you want to stop the conversion? Progress will be saved but no more images will be processed.')) {
            stopConversion();
        }
    });
    
    // Show progress modal
    function showProgressModal(progressKey, total) {
        currentProgressKey = progressKey;
        progressModal.show();
        
        // Update initial stats
        totalEl.text(total);
        completedEl.text('0');
        successEl.text('0');
        failedEl.text('0');
        progressText.text('Starting conversion...');
        progressPercentage.text('0%');
        progressBar.css('width', '0%');
        currentImageContainer.hide();
        currentImageName.text('');
        
        // Reset control buttons
        $('#webp-cp-pause-btn').show();
        $('#webp-cp-resume-btn').hide();
        $('#webp-cp-stop-btn').show();
        
        // Start progress polling
        startProgressPolling();
    }
    
    // Hide progress modal
    function hideProgressModal() {
        progressModal.hide();
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
        currentProgressKey = null;
    }
    
    // Start progress polling
    function startProgressPolling() {
        if (progressInterval) {
            clearInterval(progressInterval);
        }
        
        progressInterval = setInterval(function() {
            if (!currentProgressKey) {
                return;
            }
            
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_get_conversion_progress',
                    progress_key: currentProgressKey,
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        updateProgress(response.data);
                        
                        // Update button states based on status
                        if (response.data.status === 'paused') {
                            $('#webp-cp-pause-btn').hide();
                            $('#webp-cp-resume-btn').show();
                            progressText.text('Conversion paused (' + response.data.completed + '/' + response.data.total + ')');
                        } else if (response.data.status === 'stopped') {
                            $('#webp-cp-pause-btn').hide();
                            $('#webp-cp-resume-btn').hide();
                            $('#webp-cp-stop-btn').hide();
                            progressText.text('Conversion stopped (' + response.data.completed + '/' + response.data.total + ')');
                        } else if (response.data.status === 'completed') {
                            clearInterval(progressInterval);
                            progressInterval = null;
                            
                            // Hide control buttons
                            $('#webp-cp-pause-btn').hide();
                            $('#webp-cp-resume-btn').hide();
                            $('#webp-cp-stop-btn').hide();
                            
                            // Show completion message
                            progressText.text('Conversion completed!');
                            progressPercentage.text('100%');
                            progressBar.css('width', '100%');
                            currentImageContainer.hide();
                            
                            // Auto-hide after 3 seconds
                            setTimeout(function() {
                                hideProgressModal();
                                
                                // Show detailed success message with reload button
                                showDetailedMessage(
                                    'Conversion Completed Successfully!',
                                    'Successfully converted ' + response.data.success + ' images. ' + 
                                    (response.data.failed > 0 ? response.data.failed + ' images failed to convert. ' : '') +
                                    'Storage space has been optimized.',
                                    'success',
                                    response.data.success,
                                    response.data.failed
                                );
                            }, 3000);
                        } else {
                            // Processing - show pause button, hide resume
                            $('#webp-cp-pause-btn').show();
                            $('#webp-cp-resume-btn').hide();
                        }
                    } else {
                        // Progress check failed - show error message
                        clearInterval(progressInterval);
                        progressInterval = null;
                        
                        hideProgressModal();
                        
                        showDetailedMessage(
                            'Conversion Failed',
                            'An error occurred during the conversion process. Please try again.',
                            'error',
                            0,
                            0
                        );
                    }
                },
                error: function() {
                    // Progress check failed - show error message
                    clearInterval(progressInterval);
                    progressInterval = null;
                    
                    hideProgressModal();
                    
                    showDetailedMessage(
                        'Connection Error',
                        'Unable to check conversion progress. Please check your connection and try again.',
                        'error',
                        0,
                        0
                    );
                }
            });
        }, 1000); // Check every second
    }
    
    // Update progress display
    function updateProgress(data) {
        var percentage = Math.round((data.completed / data.total) * 100);
        
        progressBar.css('width', percentage + '%');
        progressPercentage.text(percentage + '%');
        completedEl.text(data.completed);
        successEl.text(data.success);
        failedEl.text(data.failed);
        
        if (data.current_image) {
            currentImageName.text(data.current_image);
            currentImageContainer.show();
        } else {
            currentImageContainer.hide();
        }
        
        if (data.completed < data.total && data.status === 'processing') {
            progressText.text('Converting images... (' + data.completed + '/' + data.total + ')');
        }
    }
    
    // Pause conversion
    function pauseConversion() {
        if (!currentProgressKey) {
            return;
        }
        
        $.ajax({
            url: webp_cp_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'webp_cp_pause_conversion',
                progress_key: currentProgressKey,
                nonce: webp_cp_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#webp-cp-pause-btn').hide();
                    $('#webp-cp-resume-btn').show();
                    progressText.text('Conversion paused...');
                }
            }
        });
    }
    
    // Resume conversion
    function resumeConversion() {
        if (!currentProgressKey) {
            return;
        }
        
        $.ajax({
            url: webp_cp_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'webp_cp_resume_conversion',
                progress_key: currentProgressKey,
                nonce: webp_cp_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#webp-cp-pause-btn').show();
                    $('#webp-cp-resume-btn').hide();
                    progressText.text('Resuming conversion...');
                }
            }
        });
    }
    
    // Stop conversion
    function stopConversion() {
        if (!currentProgressKey) {
            return;
        }
        
        $.ajax({
            url: webp_cp_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'webp_cp_stop_conversion',
                progress_key: currentProgressKey,
                nonce: webp_cp_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    clearInterval(progressInterval);
                    progressInterval = null;
                    
                    $('#webp-cp-pause-btn').hide();
                    $('#webp-cp-resume-btn').hide();
                    $('#webp-cp-stop-btn').hide();
                    
                    progressText.text('Conversion stopped');
                    
                    // Auto-hide after 2 seconds
                    setTimeout(function() {
                        hideProgressModal();
                        
                        showDetailedMessage(
                            'Conversion Stopped',
                            'Conversion has been stopped. ' + response.data.completed + ' images were processed before stopping.',
                            'warning',
                            response.data.success || 0,
                            response.data.failed || 0
                        );
                    }, 2000);
                }
            }
        });
    }
    
    // Show message function
    function showMessage(message, type) {
        // Remove existing messages
        $('.webp-cp-message').remove();
        
        // Create message element
        var $message = $('<div class="webp-cp-message notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        
        // Add to page
        $('body').append($message);
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            $message.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Show detailed message with reload functionality
    function showDetailedMessage(title, message, type, successCount, failedCount) {
        // Remove existing messages
        $('.webp-cp-message').remove();
        
        // Create detailed message element
        var iconClass = type === 'success' ? 'check_circle' : 'error';
        var iconColor = type === 'success' ? 'text-green-600' : 'text-red-600';
        var bgColor = type === 'success' ? 'bg-green-50 dark:bg-green-900/30' : 'bg-red-50 dark:bg-red-900/30';
        var borderColor = type === 'success' ? 'border-green-200 dark:border-green-800/60' : 'border-red-200 dark:border-red-800/60';
        var textColor = type === 'success' ? 'text-green-800 dark:text-green-300' : 'text-red-800 dark:text-red-300';
        
        var $message = $(`
            <div class="webp-cp-message fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200/20 dark:border-gray-700/20">
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full ${bgColor} flex items-center justify-center">
                            <span class="material-symbols-outlined ${iconColor} text-4xl">${iconClass}</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">${title}</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">${message}</p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <button class="webp-cp-reload-page px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium">
                                OK
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        // Add to page
        $('body').append($message);
        
        // Handle reload button click
        $message.find('.webp-cp-reload-page').on('click', function() {
            location.reload();
        });
    }
    
    // Expose functions globally
    window.webp_cp_show_progress = showProgressModal;
    window.webp_cp_hide_progress = hideProgressModal;
    window.startProgressPolling = startProgressPolling;
});
