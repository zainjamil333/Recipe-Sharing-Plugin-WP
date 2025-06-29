<?php
/**
 * Admin interface for Recipe Sharing Plugin
 */

class Recipe_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_admin_approve_recipe', array($this, 'ajax_approve_recipe'));
        add_action('wp_ajax_admin_delete_recipe', array($this, 'ajax_delete_recipe'));
        add_action('wp_ajax_admin_update_recipe', array($this, 'ajax_update_recipe'));
        add_action('wp_ajax_admin_get_recipe', array($this, 'ajax_get_recipe'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Recipe Management',
            'Recipes',
            'manage_options',
            'recipe-management',
            array($this, 'admin_page'),
            'dashicons-food',
            30
        );
        
        add_submenu_page(
            'recipe-management',
            'All Recipes',
            'All Recipes',
            'manage_options',
            'recipe-management',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'recipe-management',
            'Pending Recipes',
            'Pending Recipes',
            'manage_options',
            'recipe-pending',
            array($this, 'pending_page')
        );
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'recipe') !== false) {
            wp_enqueue_script('recipe-admin', RECIPE_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), '1.0.0', true);
            wp_enqueue_style('recipe-admin', RECIPE_PLUGIN_URL . 'assets/css/admin.css', array(), '1.0.0');
            
            wp_localize_script('recipe-admin', 'recipe_admin_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('recipe_admin_nonce')
            ));
        }
    }
    
    /**
     * Main admin page
     */
    public function admin_page() {
        // Show success messages
        if (isset($_GET['sample_data_added'])) {
            $count = intval($_GET['sample_data_added']);
            echo '<div class="notice notice-success is-dismissible"><p>✅ Successfully added ' . $count . ' sample recipes with ratings and comments!</p></div>';
        }
        
        if (isset($_GET['recipe_added'])) {
            echo '<div class="notice notice-success is-dismissible"><p>✅ Recipe added successfully!</p></div>';
        }
        
        $recipes = Recipe_Database::get_recipes(array('status' => 'approved', 'limit' => 50));
        ?>
        <div class="wrap">
            <h1>Recipe Management</h1>
            
            <!-- PROMINENT SAMPLE DATA BUTTON -->
            <div style="background: #fff; padding: 20px; margin: 20px 0; border: 2px solid #0073aa; border-radius: 5px;">
                <h2 style="margin-top: 0; color: #0073aa;">🚀 Quick Start - Add Sample Data</h2>
                <p style="font-size: 16px; margin-bottom: 15px;">To test the frontend, add some sample recipes with ratings and comments:</p>
                <a href="<?php echo admin_url('admin.php?page=recipe-management&action=add_sample_data'); ?>" 
                   class="button button-primary button-hero" 
                   style="font-size: 16px; padding: 10px 20px; height: auto; line-height: 1.4;"
                   onclick="return confirm('This will add 3 sample recipes with ratings and comments. Continue?')">
                    🍳 Add Sample Recipes
                </a>
                <p style="margin-top: 10px; color: #666; font-style: italic;">This will add 3 delicious recipes: Margherita Pizza, Chocolate Chip Cookies, and Grilled Chicken Salad.</p>
            </div>
            
            <!-- ADD NEW RECIPE BUTTON -->
            <div style="background: #fff; padding: 20px; margin: 20px 0; border: 2px solid #28a745; border-radius: 5px;">
                <h2 style="margin-top: 0; color: #28a745;">➕ Add New Recipe</h2>
                <p style="font-size: 16px; margin-bottom: 15px;">Add a new recipe directly from the admin panel:</p>
                <a href="<?php echo admin_url('admin.php?page=recipe-management&action=add_new_recipe'); ?>" 
                   class="button button-primary button-hero" 
                   style="font-size: 16px; padding: 10px 20px; height: auto; line-height: 1.4; background: #28a745; border-color: #28a745;">
                    ➕ Add New Recipe
                </a>
                <p style="margin-top: 10px; color: #666; font-style: italic;">Or customers can submit recipes using the frontend form on your "Submit Recipe" page.</p>
            </div>
            
            <div class="recipe-admin-container">
                <div class="recipe-stats">
                    <div class="stat-box">
                        <h3>Total Recipes</h3>
                        <p><?php echo count($recipes); ?></p>
                    </div>
                    <div class="stat-box">
                        <h3>Pending Approval</h3>
                        <p><?php echo count(Recipe_Database::get_recipes(array('status' => 'pending', 'limit' => 1000))); ?></p>
                    </div>
                </div>
                
                <div class="recipe-list">
                    <h2>Approved Recipes</h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Difficulty</th>
                                <th>Cooking Time</th>
                                <th>Rating</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recipes)): ?>
                            <tr>
                                <td colspan="7">
                                    <div style="text-align: center; padding: 40px;">
                                        <h3 style="color: #666;">No recipes found yet</h3>
                                        <p style="margin-bottom: 20px;">Get started by adding some sample recipes:</p>
                                        <a href="<?php echo admin_url('admin.php?page=recipe-management&action=add_sample_data'); ?>" 
                                           class="button button-primary"
                                           onclick="return confirm('This will add 3 sample recipes with ratings and comments. Continue?')">
                                            🍳 Add Sample Recipes
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($recipes as $recipe): ?>
                                <tr>
                                    <td><?php echo esc_html($recipe->title); ?></td>
                                    <td><?php echo esc_html($recipe->author_name); ?></td>
                                    <td><?php echo esc_html(ucfirst($recipe->difficulty)); ?></td>
                                    <td><?php echo $recipe->cooking_time ? $recipe->cooking_time . ' min' : 'N/A'; ?></td>
                                    <td>
                                        <?php if ($recipe->avg_rating): ?>
                                            <?php echo number_format($recipe->avg_rating, 1); ?> (<?php echo $recipe->rating_count; ?>)
                                        <?php else: ?>
                                            No ratings
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($recipe->created_at)); ?></td>
                                    <td>
                                        <button class="button edit-recipe" data-id="<?php echo $recipe->id; ?>">Edit</button>
                                        <button class="button button-link-delete delete-recipe" data-id="<?php echo $recipe->id; ?>">Delete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Pending recipes page
     */
    public function pending_page() {
        $recipes = Recipe_Database::get_recipes(array('status' => 'pending', 'limit' => 50));
        ?>
        <div class="wrap">
            <h1>Pending Recipes</h1>
            
            <div class="recipe-list">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Difficulty</th>
                            <th>Cooking Time</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recipes)): ?>
                        <tr>
                            <td colspan="6">No pending recipes</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($recipes as $recipe): ?>
                            <tr>
                                <td><?php echo esc_html($recipe->title); ?></td>
                                <td><?php echo esc_html($recipe->author_name); ?></td>
                                <td><?php echo esc_html(ucfirst($recipe->difficulty)); ?></td>
                                <td><?php echo $recipe->cooking_time ? $recipe->cooking_time . ' min' : 'N/A'; ?></td>
                                <td><?php echo date('M j, Y', strtotime($recipe->created_at)); ?></td>
                                <td>
                                    <button class="button button-primary approve-recipe" data-id="<?php echo $recipe->id; ?>">Approve</button>
                                    <button class="button edit-recipe" data-id="<?php echo $recipe->id; ?>">Edit</button>
                                    <button class="button button-link-delete delete-recipe" data-id="<?php echo $recipe->id; ?>">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX approve recipe
     */
    public function ajax_approve_recipe() {
        check_ajax_referer('recipe_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $recipe_id = intval($_POST['recipe_id']);
        $result = Recipe_Database::update_recipe($recipe_id, array('status' => 'approved'));
        
        if ($result) {
            wp_send_json_success('Recipe approved successfully');
        } else {
            wp_send_json_error('Failed to approve recipe');
        }
    }
    
    /**
     * AJAX delete recipe
     */
    public function ajax_delete_recipe() {
        check_ajax_referer('recipe_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $recipe_id = intval($_POST['recipe_id']);
        $result = Recipe_Database::delete_recipe($recipe_id);
        
        if ($result) {
            wp_send_json_success('Recipe deleted successfully');
        } else {
            wp_send_json_error('Failed to delete recipe');
        }
    }
    
    /**
     * AJAX update recipe
     */
    public function ajax_update_recipe() {
        check_ajax_referer('recipe_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $recipe_id = intval($_POST['recipe_id']);
        $recipe_data = array(
            'title' => sanitize_text_field($_POST['title']),
            'description' => sanitize_textarea_field($_POST['description']),
            'ingredients' => sanitize_textarea_field($_POST['ingredients']),
            'instructions' => sanitize_textarea_field($_POST['instructions']),
            'cooking_time' => isset($_POST['cooking_time']) ? intval($_POST['cooking_time']) : 0,
            'difficulty' => isset($_POST['difficulty']) ? sanitize_text_field($_POST['difficulty']) : 'medium',
            'servings' => isset($_POST['servings']) ? intval($_POST['servings']) : 1,
            'status' => isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'pending'
        );
        
        $result = Recipe_Database::update_recipe($recipe_id, $recipe_data);
        
        if ($result) {
            wp_send_json_success('Recipe updated successfully');
        } else {
            wp_send_json_error('Failed to update recipe');
        }
    }
    
    /**
     * AJAX get recipe data
     */
    public function ajax_get_recipe() {
        check_ajax_referer('recipe_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $recipe_id = intval($_POST['recipe_id']);
        $recipe = Recipe_Database::get_recipe($recipe_id);
        
        if ($recipe) {
            wp_send_json_success($recipe);
        } else {
            wp_send_json_error('Recipe not found');
        }
    }
    
    /**
     * Add sample data
     */
    public function add_sample_data() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Sample recipes data
        $sample_recipes = array(
            array(
                'title' => 'Classic Margherita Pizza',
                'description' => 'A traditional Italian pizza with fresh mozzarella, basil, and tomato sauce.',
                'ingredients' => "2 cups all-purpose flour\n1 cup warm water\n1 tsp active dry yeast\n1 tsp salt\n1 tbsp olive oil\n1/2 cup tomato sauce\n8 oz fresh mozzarella\nFresh basil leaves\nSalt and pepper to taste",
                'instructions' => "1. Mix flour, yeast, and salt in a large bowl\n2. Add warm water and olive oil, knead for 10 minutes\n3. Let dough rise for 1 hour\n4. Roll out dough and add toppings\n5. Bake at 450°F for 12-15 minutes",
                'cooking_time' => 90,
                'difficulty' => 'medium',
                'servings' => 4,
                'status' => 'approved'
            ),
            array(
                'title' => 'Chocolate Chip Cookies',
                'description' => 'Soft and chewy chocolate chip cookies that are perfect for any occasion.',
                'ingredients' => "2 1/4 cups all-purpose flour\n1 tsp baking soda\n1 tsp salt\n1 cup butter, softened\n3/4 cup granulated sugar\n3/4 cup brown sugar\n2 large eggs\n1 tsp vanilla extract\n2 cups chocolate chips",
                'instructions' => "1. Preheat oven to 375°F\n2. Mix flour, baking soda, and salt\n3. Cream butter and sugars until fluffy\n4. Add eggs and vanilla, mix well\n5. Stir in chocolate chips\n6. Drop rounded tablespoons onto baking sheet\n7. Bake for 9-11 minutes",
                'cooking_time' => 25,
                'difficulty' => 'easy',
                'servings' => 24,
                'status' => 'approved'
            ),
            array(
                'title' => 'Grilled Chicken Salad',
                'description' => 'A healthy and delicious salad with grilled chicken, fresh vegetables, and a light dressing.',
                'ingredients' => "2 chicken breasts\nMixed salad greens\n1 cucumber, sliced\n1 tomato, diced\n1/4 cup red onion, sliced\n1/4 cup olive oil\n2 tbsp lemon juice\n1 tsp Dijon mustard\nSalt and pepper to taste",
                'instructions' => "1. Season chicken with salt and pepper\n2. Grill chicken for 6-8 minutes per side\n3. Let chicken rest for 5 minutes, then slice\n4. Arrange salad greens on plates\n5. Top with chicken and vegetables\n6. Whisk together dressing ingredients\n7. Drizzle dressing over salad",
                'cooking_time' => 20,
                'difficulty' => 'easy',
                'servings' => 2,
                'status' => 'approved'
            )
        );
        
        // Insert sample recipes
        $inserted_count = 0;
        foreach ($sample_recipes as $recipe_data) {
            // Set user_id to current admin user
            $recipe_data['user_id'] = get_current_user_id();
            
            // Insert recipe
            $recipe_id = Recipe_Database::insert_recipe($recipe_data);
            
            if ($recipe_id) {
                $inserted_count++;
                
                // Add some sample ratings
                for ($i = 0; $i < rand(3, 8); $i++) {
                    $rating = rand(3, 5); // Random rating between 3-5
                    Recipe_Database::add_rating($recipe_id, get_current_user_id(), $rating);
                }
                
                // Add some sample comments
                $sample_comments = array(
                    "This recipe is amazing! I've made it several times.",
                    "Great recipe, very easy to follow.",
                    "Delicious! My family loved it.",
                    "Perfect for beginners, highly recommend!",
                    "This has become a family favorite."
                );
                
                for ($i = 0; $i < rand(1, 3); $i++) {
                    $comment = $sample_comments[array_rand($sample_comments)];
                    Recipe_Database::add_comment($recipe_id, get_current_user_id(), $comment);
                }
            }
        }
        
        // Redirect back with success message
        wp_redirect(admin_url('admin.php?page=recipe-management&sample_data_added=' . $inserted_count));
        exit;
    }
    
    /**
     * Show add recipe form
     */
    public function show_add_recipe_form() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Handle form submission
        if (isset($_POST['submit_recipe'])) {
            $recipe_data = array(
                'title' => sanitize_text_field($_POST['title']),
                'description' => sanitize_textarea_field($_POST['description']),
                'ingredients' => sanitize_textarea_field($_POST['ingredients']),
                'instructions' => sanitize_textarea_field($_POST['instructions']),
                'cooking_time' => isset($_POST['cooking_time']) ? intval($_POST['cooking_time']) : 0,
                'difficulty' => isset($_POST['difficulty']) ? sanitize_text_field($_POST['difficulty']) : 'medium',
                'servings' => isset($_POST['servings']) ? intval($_POST['servings']) : 1,
                'status' => isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'approved',
                'user_id' => get_current_user_id()
            );
            
            $recipe_id = Recipe_Database::insert_recipe($recipe_data);
            
            if ($recipe_id) {
                wp_redirect(admin_url('admin.php?page=recipe-management&recipe_added=1'));
                exit;
            } else {
                $error_message = 'Failed to add recipe. Please try again.';
            }
        }
        
        ?>
        <div class="wrap">
            <h1>Add New Recipe</h1>
            
            <?php if (isset($error_message)): ?>
                <div class="notice notice-error"><p><?php echo $error_message; ?></p></div>
            <?php endif; ?>
            
            <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <form method="post" action="">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="title">Recipe Title *</label></th>
                            <td><input type="text" id="title" name="title" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="description">Description *</label></th>
                            <td><textarea id="description" name="description" rows="3" class="large-text" required></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cooking_time">Cooking Time (minutes)</label></th>
                            <td><input type="number" id="cooking_time" name="cooking_time" class="small-text" min="0"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="difficulty">Difficulty</label></th>
                            <td>
                                <select id="difficulty" name="difficulty">
                                    <option value="easy">Easy</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="servings">Servings</label></th>
                            <td><input type="number" id="servings" name="servings" class="small-text" min="1" value="1"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="status">Status</label></th>
                            <td>
                                <select id="status" name="status">
                                    <option value="approved" selected>Approved</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ingredients">Ingredients *</label></th>
                            <td>
                                <textarea id="ingredients" name="ingredients" rows="5" class="large-text" required placeholder="List each ingredient on a new line"></textarea>
                                <p class="description">Enter each ingredient on a separate line</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="instructions">Instructions *</label></th>
                            <td>
                                <textarea id="instructions" name="instructions" rows="8" class="large-text" required placeholder="List each step on a new line"></textarea>
                                <p class="description">Enter each step on a separate line</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="submit_recipe" class="button button-primary" value="Add Recipe">
                        <a href="<?php echo admin_url('admin.php?page=recipe-management'); ?>" class="button">Cancel</a>
                    </p>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Handle admin actions
     */
    public function handle_admin_actions() {
        if (isset($_GET['page']) && $_GET['page'] === 'recipe-management' && 
            isset($_GET['action']) && $_GET['action'] === 'add_sample_data') {
            $this->add_sample_data();
        }
        
        if (isset($_GET['page']) && $_GET['page'] === 'recipe-management' && 
            isset($_GET['action']) && $_GET['action'] === 'add_new_recipe') {
            $this->show_add_recipe_form();
        }
    }
} 