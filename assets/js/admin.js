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
            
            // Start conversion session
            $.ajax({
                url: webp_cp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'webp_cp_convert_multiple',
                    image_ids: Array.isArray(imageIds) ? imageIds.join(',') : imageIds,
                    nonce: webp_cp_vars.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        if (typeof webp_cp_show_progress === 'function') {
                            webp_cp_show_progress(response.data.progress_key, response.data.total);
                        }
                    } else {
                        var errorMsg = (response.data && response.data.message) ? response.data.message : 'Error starting conversion.';
                        alert('Error: ' + errorMsg);
                    }
                },
                error: function() {
                    alert('An error occurred while starting the conversion.');
                }
            });
        });
    });
    
})(jQuery);