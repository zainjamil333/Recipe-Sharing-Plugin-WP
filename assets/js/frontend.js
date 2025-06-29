jQuery(document).ready(function($) {
    
    // Recipe Submit Form
    $('#recipe-submit-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('.submit-button');
        var messageDiv = $('#recipe-submit-message');
        
        // Disable submit button
        submitBtn.prop('disabled', true).text('Submitting...');
        
        // Get form data
        var formData = new FormData(this);
        formData.append('action', 'recipe_submit');
        formData.append('nonce', recipe_ajax.nonce);
        
        // Submit via AJAX
        $.ajax({
            url: recipe_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    messageDiv.removeClass('error').addClass('success').html(response.data.message);
                    form[0].reset();
                } else {
                    messageDiv.removeClass('success').addClass('error').html(response.data);
                }
            },
            error: function() {
                messageDiv.removeClass('success').addClass('error').html('An error occurred. Please try again.');
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Submit Recipe');
            }
        });
    });
    
    // Recipe Rating Stars
    $('.rating-stars').on('click', '.star', function() {
        var star = $(this);
        var rating = star.data('rating');
        var recipeId = star.closest('.rating-stars').data('recipe-id');
        var stars = star.siblings('.star').addBack();
        
        // Update visual state
        stars.removeClass('active');
        star.addClass('active');
        star.prevAll('.star').addClass('active');
        
        // Submit rating via AJAX
        $.ajax({
            url: recipe_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'recipe_rate',
                recipe_id: recipeId,
                rating: rating,
                nonce: recipe_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showMessage('Rating submitted successfully!', 'success');
                } else {
                    showMessage(response.data, 'error');
                    // Reset stars
                    stars.removeClass('active');
                }
            },
            error: function() {
                showMessage('Failed to submit rating. Please try again.', 'error');
                // Reset stars
                stars.removeClass('active');
            }
        });
    });
    
    // Recipe Comment Form
    $('#recipe-comment-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var textarea = form.find('textarea[name="comment"]');
        
        // Disable submit button
        submitBtn.prop('disabled', true).text('Posting...');
        
        // Submit via AJAX
        $.ajax({
            url: recipe_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'recipe_comment',
                recipe_id: form.find('input[name="recipe_id"]').val(),
                comment: textarea.val(),
                nonce: recipe_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Clear form
                    textarea.val('');
                    showMessage('Comment posted successfully!', 'success');
                    
                    // Reload page to show new comment
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showMessage(response.data, 'error');
                }
            },
            error: function() {
                showMessage('Failed to post comment. Please try again.', 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Post Comment');
            }
        });
    });
    
    // Star hover effects
    $('.rating-stars').on('mouseenter', '.star', function() {
        var star = $(this);
        var stars = star.siblings('.star').addBack();
        
        stars.removeClass('hover');
        star.addClass('hover');
        star.prevAll('.star').addClass('hover');
    }).on('mouseleave', '.star', function() {
        $('.star').removeClass('hover');
    });
    
    // Recipe card hover effects
    $('.recipe-card').hover(
        function() {
            $(this).find('.recipe-link').addClass('hover');
        },
        function() {
            $(this).find('.recipe-link').removeClass('hover');
        }
    );
    
    // Form validation
    $('.recipe-form input[required], .recipe-form textarea[required]').on('blur', function() {
        var field = $(this);
        var value = field.val().trim();
        
        if (value === '') {
            field.addClass('error');
            showFieldError(field, 'This field is required.');
        } else {
            field.removeClass('error');
            hideFieldError(field);
        }
    });
    
    // Real-time form validation
    $('.recipe-form input[required], .recipe-form textarea[required]').on('input', function() {
        var field = $(this);
        var value = field.val().trim();
        
        if (value !== '') {
            field.removeClass('error');
            hideFieldError(field);
        }
    });
    
    // Helper function to show messages
    function showMessage(message, type) {
        var messageDiv = $('<div class="message ' + type + '">' + message + '</div>');
        $('body').append(messageDiv);
        
        // Auto remove after 3 seconds
        setTimeout(function() {
            messageDiv.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    // Helper function to show field error
    function showFieldError(field, message) {
        var errorDiv = field.siblings('.field-error');
        if (errorDiv.length === 0) {
            errorDiv = $('<div class="field-error">' + message + '</div>');
            field.after(errorDiv);
        } else {
            errorDiv.text(message);
        }
        errorDiv.show();
    }
    
    // Helper function to hide field error
    function hideFieldError(field) {
        field.siblings('.field-error').hide();
    }
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 800);
        }
    });
    
    // Add loading states to buttons
    $('.recipe-form button, .comment-form button').on('click', function() {
        var btn = $(this);
        if (!btn.prop('disabled')) {
            btn.addClass('loading');
        }
    });
    
    // Remove loading state when form is reset
    $('.recipe-form').on('reset', function() {
        $(this).find('button').removeClass('loading');
        $(this).find('.field-error').hide();
        $(this).find('input, textarea').removeClass('error');
    });
    
    // Add CSS for additional styles
    var additionalCSS = `
        <style>
            .message {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 5px;
                color: white;
                font-weight: 500;
                z-index: 9999;
                animation: slideIn 0.3s ease;
            }
            
            .message.success {
                background: #28a745;
            }
            
            .message.error {
                background: #dc3545;
            }
            
            .field-error {
                color: #dc3545;
                font-size: 0.9em;
                margin-top: 5px;
                display: none;
            }
            
            .recipe-form input.error,
            .recipe-form textarea.error {
                border-color: #dc3545;
            }
            
            .star.hover {
                color: #ffd700;
            }
            
            .recipe-link.hover {
                transform: translateY(-2px);
            }
            
            button.loading {
                opacity: 0.7;
                cursor: not-allowed;
            }
            
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        </style>
    `;
    
    $('head').append(additionalCSS);
    
}); 