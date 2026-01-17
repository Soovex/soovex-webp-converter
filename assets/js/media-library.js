/**
 * Media Library JavaScript for WebP Converter Pro
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Handle convert action
    $(document).on('click', '.webp-cp-convert-media', function(e) {
        e.preventDefault();
        
        var $link = $(this);
        var attachmentId = $link.data('attachment-id');
        var nonce = $link.data('nonce');
        
        // Confirm action
        if (!confirm(webp_cp_media_vars.strings.confirm_convert)) {
            return;
        }
        
        // Update link text
        var originalText = $link.text();
        $link.text(webp_cp_media_vars.strings.converting);
        $link.addClass('webp-cp-processing');
        
        // Make AJAX request
        $.ajax({
            url: webp_cp_media_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'webp_cp_convert_media',
                attachment_id: attachmentId,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showMessage(webp_cp_media_vars.strings.success + ': ' + response.data.message, 'success');
                    
                    // Reload page to update status
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    // Show error message
                    showMessage(webp_cp_media_vars.strings.error + ': ' + response.data.message, 'error');
                    
                    // Restore link text
                    $link.text(originalText);
                    $link.removeClass('webp-cp-processing');
                }
            },
            error: function() {
                // Show error message
                showMessage(webp_cp_media_vars.strings.error + ': ' + 'Network error occurred', 'error');
                
                // Restore link text
                $link.text(originalText);
                $link.removeClass('webp-cp-processing');
            }
        });
    });
    
    // Handle revert action
    $(document).on('click', '.webp-cp-revert-media', function(e) {
        e.preventDefault();
        
        var $link = $(this);
        var attachmentId = $link.data('attachment-id');
        var nonce = $link.data('nonce');
        
        // Confirm action
        if (!confirm(webp_cp_media_vars.strings.confirm_revert)) {
            return;
        }
        
        // Update link text
        var originalText = $link.text();
        $link.text(webp_cp_media_vars.strings.reverting);
        $link.addClass('webp-cp-processing');
        
        // Make AJAX request
        $.ajax({
            url: webp_cp_media_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'webp_cp_revert_media',
                attachment_id: attachmentId,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showMessage(webp_cp_media_vars.strings.success + ': ' + response.data.message, 'success');
                    
                    // Reload page to update status
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    // Show error message
                    showMessage(webp_cp_media_vars.strings.error + ': ' + response.data.message, 'error');
                    
                    // Restore link text
                    $link.text(originalText);
                    $link.removeClass('webp-cp-processing');
                }
            },
            error: function() {
                // Show error message
                showMessage(webp_cp_media_vars.strings.error + ': ' + 'Network error occurred', 'error');
                
                // Restore link text
                $link.text(originalText);
                $link.removeClass('webp-cp-processing');
            }
        });
    });
    
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
    
    // Add processing styles
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .webp-cp-processing {
                opacity: 0.6;
                pointer-events: none;
            }
            .webp-cp-message {
                position: fixed;
                top: 32px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
            }
            .webp-status-converted {
                color: #00a32a;
                font-weight: 500;
            }
            .webp-status-converted-no-backup {
                color: #f0b849;
                font-weight: 500;
            }
            .webp-status-available {
                color: #f0b849;
                font-weight: 500;
            }
            .webp-status-not-converted {
                color: #d63638;
                font-weight: 500;
            }
            .webp-status-not-applicable {
                color: #8c8f94;
            }
        `)
        .appendTo('head');
});
