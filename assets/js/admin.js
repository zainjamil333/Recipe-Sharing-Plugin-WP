jQuery(document).ready(function($) {
    
    // Approve Recipe
    $('.approve-recipe').on('click', function() {
        var button = $(this);
        var recipeId = button.data('id');
        var row = button.closest('tr');
        
        if (!confirm('Are you sure you want to approve this recipe?')) {
            return;
        }
        
        // Disable button and show loading
        button.prop('disabled', true).text('Approving...').addClass('loading');
        
        $.ajax({
            url: recipe_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'admin_approve_recipe',
                recipe_id: recipeId,
                nonce: recipe_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showAdminMessage('Recipe approved successfully!', 'success');
                    // Remove row from pending table
                    row.fadeOut(function() {
                        $(this).remove();
                        // Check if table is empty
                        if ($('tbody tr').length === 0) {
                            $('tbody').html('<tr><td colspan="6">No pending recipes</td></tr>');
                        }
                    });
                } else {
                    showAdminMessage(response.data, 'error');
                }
            },
            error: function() {
                showAdminMessage('Failed to approve recipe. Please try again.', 'error');
            },
            complete: function() {
                button.prop('disabled', false).text('Approve').removeClass('loading');
            }
        });
    });
    
    // Delete Recipe
    $('.delete-recipe').on('click', function() {
        var button = $(this);
        var recipeId = button.data('id');
        var row = button.closest('tr');
        
        if (!confirm('Are you sure you want to delete this recipe? This action cannot be undone.')) {
            return;
        }
        
        // Disable button and show loading
        button.prop('disabled', true).text('Deleting...').addClass('loading');
        
        $.ajax({
            url: recipe_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'admin_delete_recipe',
                recipe_id: recipeId,
                nonce: recipe_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showAdminMessage('Recipe deleted successfully!', 'success');
                    // Remove row
                    row.fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    showAdminMessage(response.data, 'error');
                }
            },
            error: function() {
                showAdminMessage('Failed to delete recipe. Please try again.', 'error');
            },
            complete: function() {
                button.prop('disabled', false).text('Delete').removeClass('loading');
            }
        });
    });
    
    // Edit Recipe (Modal)
    $('.edit-recipe').on('click', function() {
        var button = $(this);
        var recipeId = button.data('id');
        
        // Show loading
        button.prop('disabled', true).text('Loading...').addClass('loading');
        
        // Fetch recipe data and show edit modal
        $.ajax({
            url: recipe_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'admin_get_recipe',
                recipe_id: recipeId,
                nonce: recipe_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showEditModal(response.data);
                } else {
                    showAdminMessage(response.data, 'error');
                }
            },
            error: function() {
                showAdminMessage('Failed to load recipe data. Please try again.', 'error');
            },
            complete: function() {
                button.prop('disabled', false).text('Edit').removeClass('loading');
            }
        });
    });
    
    // Bulk Actions
    $('#bulk-action-selector-top, #bulk-action-selector-bottom').on('change', function() {
        var action = $(this).val();
        var submitButton = $(this).siblings('.button');
        
        if (action && action !== '-1') {
            submitButton.prop('disabled', false);
        } else {
            submitButton.prop('disabled', true);
        }
    });
    
    // Select All Checkboxes
    $('#cb-select-all-1, #cb-select-all-2').on('change', function() {
        var isChecked = $(this).prop('checked');
        var table = $(this).closest('table');
        
        table.find('input[name="recipe_ids[]"]').prop('checked', isChecked);
    });
    
    // Individual Checkbox Change
    $('input[name="recipe_ids[]"]').on('change', function() {
        var totalCheckboxes = $('input[name="recipe_ids[]"]').length;
        var checkedCheckboxes = $('input[name="recipe_ids[]"]:checked').length;
        
        // Update "select all" checkbox
        if (checkedCheckboxes === 0) {
            $('#cb-select-all-1, #cb-select-all-2').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#cb-select-all-1, #cb-select-all-2').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#cb-select-all-1, #cb-select-all-2').prop('indeterminate', true);
        }
    });
    
    // Search and Filter
    $('#recipe-search').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        var table = $('.wp-list-table');
        
        table.find('tbody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            
            if (text.indexOf(searchTerm) > -1) {
                row.show();
            } else {
                row.hide();
            }
        });
    });
    
    // Status Filter
    $('#recipe-status-filter').on('change', function() {
        var status = $(this).val();
        var table = $('.wp-list-table');
        
        if (status === 'all') {
            table.find('tbody tr').show();
        } else {
            table.find('tbody tr').each(function() {
                var row = $(this);
                var rowStatus = row.find('.recipe-status').text().toLowerCase();
                
                if (rowStatus === status) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        }
    });
    
    // Helper function to show admin messages
    function showAdminMessage(message, type) {
        var messageDiv = $('<div class="admin-message ' + type + '">' + message + '</div>');
        $('.wrap h1').after(messageDiv);
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            messageDiv.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Helper function to show edit modal
    function showEditModal(recipe) {
        var modal = $('<div class="recipe-edit-modal">' +
            '<div class="modal-content">' +
                '<div class="modal-header">' +
                    '<h3>Edit Recipe: ' + recipe.title + '</h3>' +
                    '<span class="modal-close">&times;</span>' +
                '</div>' +
                '<div class="modal-body">' +
                    '<form id="recipe-edit-form">' +
                        '<input type="hidden" name="recipe_id" value="' + recipe.id + '">' +
                        '<div class="form-group">' +
                            '<label>Title</label>' +
                            '<input type="text" name="title" value="' + recipe.title + '" required>' +
                        '</div>' +
                        '<div class="form-group">' +
                            '<label>Description</label>' +
                            '<textarea name="description" rows="3" required>' + recipe.description + '</textarea>' +
                        '</div>' +
                        '<div class="form-row">' +
                            '<div class="form-group">' +
                                '<label>Cooking Time (minutes)</label>' +
                                '<input type="number" name="cooking_time" value="' + recipe.cooking_time + '" min="0">' +
                            '</div>' +
                            '<div class="form-group">' +
                                '<label>Difficulty</label>' +
                                '<select name="difficulty">' +
                                    '<option value="easy"' + (recipe.difficulty === 'easy' ? ' selected' : '') + '>Easy</option>' +
                                    '<option value="medium"' + (recipe.difficulty === 'medium' ? ' selected' : '') + '>Medium</option>' +
                                    '<option value="hard"' + (recipe.difficulty === 'hard' ? ' selected' : '') + '>Hard</option>' +
                                '</select>' +
                            '</div>' +
                            '<div class="form-group">' +
                                '<label>Servings</label>' +
                                '<input type="number" name="servings" value="' + recipe.servings + '" min="1">' +
                            '</div>' +
                        '</div>' +
                        '<div class="form-group">' +
                            '<label>Ingredients</label>' +
                            '<textarea name="ingredients" rows="5" required>' + recipe.ingredients + '</textarea>' +
                        '</div>' +
                        '<div class="form-group">' +
                            '<label>Instructions</label>' +
                            '<textarea name="instructions" rows="8" required>' + recipe.instructions + '</textarea>' +
                        '</div>' +
                        '<div class="form-group">' +
                            '<label>Status</label>' +
                            '<select name="status">' +
                                '<option value="pending"' + (recipe.status === 'pending' ? ' selected' : '') + '>Pending</option>' +
                                '<option value="approved"' + (recipe.status === 'approved' ? ' selected' : '') + '>Approved</option>' +
                                '<option value="rejected"' + (recipe.status === 'rejected' ? ' selected' : '') + '>Rejected</option>' +
                            '</select>' +
                        '</div>' +
                        '<div class="form-actions">' +
                            '<button type="submit" class="button button-primary">Update Recipe</button>' +
                            '<button type="button" class="button modal-cancel">Cancel</button>' +
                        '</div>' +
                    '</form>' +
                '</div>' +
            '</div>' +
        '</div>');
        
        $('body').append(modal);
        
        // Handle modal close
        modal.find('.modal-close, .modal-cancel').on('click', function() {
            modal.remove();
        });
        
        // Handle form submission
        modal.find('#recipe-edit-form').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            
            submitBtn.prop('disabled', true).text('Updating...');
            
            $.ajax({
                url: recipe_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'admin_update_recipe',
                    recipe_id: form.find('input[name="recipe_id"]').val(),
                    title: form.find('input[name="title"]').val(),
                    description: form.find('textarea[name="description"]').val(),
                    ingredients: form.find('textarea[name="ingredients"]').val(),
                    instructions: form.find('textarea[name="instructions"]').val(),
                    cooking_time: form.find('input[name="cooking_time"]').val(),
                    difficulty: form.find('select[name="difficulty"]').val(),
                    servings: form.find('input[name="servings"]').val(),
                    status: form.find('select[name="status"]').val(),
                    nonce: recipe_admin_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showAdminMessage('Recipe updated successfully!', 'success');
                        modal.remove();
                        // Reload page to show updated data
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showAdminMessage(response.data, 'error');
                    }
                },
                error: function() {
                    showAdminMessage('Failed to update recipe. Please try again.', 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text('Update Recipe');
                }
            });
        });
    }
    
    // Add CSS for modal
    var modalCSS = `
        <style>
            .recipe-edit-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 100000;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .modal-content {
                background: white;
                border-radius: 8px;
                max-width: 800px;
                width: 90%;
                max-height: 90vh;
                overflow-y: auto;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            }
            
            .modal-header {
                padding: 20px;
                border-bottom: 1px solid #e1e5e9;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .modal-header h3 {
                margin: 0;
                color: #333;
            }
            
            .modal-close {
                font-size: 24px;
                cursor: pointer;
                color: #666;
                line-height: 1;
            }
            
            .modal-close:hover {
                color: #333;
            }
            
            .modal-body {
                padding: 20px;
            }
            
            .form-actions {
                margin-top: 20px;
                text-align: right;
            }
            
            .form-actions .button {
                margin-left: 10px;
            }
        </style>
    `;
    
    $('head').append(modalCSS);
    
}); 