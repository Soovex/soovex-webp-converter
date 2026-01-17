/**
 * Admin JavaScript for the plugin
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Initialize tooltips if needed
        // ...
        
        // Handle modal close buttons
        $(document).on('click', '.webp-cp-close-modal, .webp-cp-close-popup', function() {
            $(this).closest('.fixed.inset-0.z-50').hide();
        });
        
        // Handle bulk conversion with progress bar
        $(document).on('click', '.webp-cp-convert-bulk', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var imageIds = $button.data('image-ids');
            
            if (!imageIds || imageIds.length === 0) {
                alert('No images selected for conversion.');
                return;
            }
            
            // Show progress modal
            if (typeof webp_cp_show_progress === 'function') {
                var progressKey = 'webp_cp_conversion_progress_' + Date.now();
                webp_cp_show_progress(progressKey, imageIds.length);
            }
            
            // Start conversion
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_convert_multiple',
                    image_ids: imageIds.join(','),
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Progress modal is already shown, conversion will be tracked
                    } else {
                        // Hide progress modal and show error
                        if (typeof webp_cp_hide_progress === 'function') {
                            webp_cp_hide_progress();
                        }
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    // Hide progress modal and show error
                    if (typeof webp_cp_hide_progress === 'function') {
                        webp_cp_hide_progress();
                    }
                    alert('An error occurred while starting the conversion.');
                }
            });
        });
    });
    
})(jQuery);