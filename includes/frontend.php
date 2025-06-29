<?php
/**
 * Frontend functionality for Recipe Sharing Plugin
 */

class Recipe_Frontend {
    
    public function __construct() {
        add_action('wp_ajax_nopriv_recipe_submit', array($this, 'ajax_submit_recipe'));
        add_action('wp_ajax_nopriv_recipe_rate', array($this, 'ajax_rate_recipe'));
        add_action('wp_ajax_nopriv_recipe_comment', array($this, 'ajax_add_comment'));
        add_shortcode('recipe_list', array($this, 'recipe_list_shortcode'));
        add_shortcode('recipe_submit', array($this, 'recipe_submit_shortcode'));
        add_shortcode('recipe_detail', array($this, 'recipe_detail_shortcode'));
    }
    
    /**
     * Recipe list shortcode
     */
    public function recipe_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 10,
            'orderby' => 'created_at',
            'order' => 'DESC'
        ), $atts);
        
        $recipes = Recipe_Database::get_recipes(array(
            'limit' => intval($atts['limit']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order']
        ));
        
        ob_start();
        ?>
        <div class="recipe-list-container">
            <h2>Recipe Collection</h2>
            <div class="recipe-grid">
                <?php if (empty($recipes)): ?>
                    <p>No recipes found.</p>
                <?php else: ?>
                    <?php foreach ($recipes as $recipe): ?>
                        <div class="recipe-card">
                            <div class="recipe-image">
                                <?php if ($recipe->image_url): ?>
                                    <img src="<?php echo esc_url($recipe->image_url); ?>" alt="<?php echo esc_attr($recipe->title); ?>">
                                <?php else: ?>
                                    <div class="recipe-placeholder">🍳</div>
                                <?php endif; ?>
                            </div>
                            <div class="recipe-content">
                                <h3><?php echo esc_html($recipe->title); ?></h3>
                                <p class="recipe-meta">
                                    By <?php echo esc_html($recipe->author_name); ?> • 
                                    <?php echo esc_html(ucfirst($recipe->difficulty)); ?> • 
                                    <?php echo $recipe->cooking_time ? $recipe->cooking_time . ' min' : 'N/A'; ?>
                                </p>
                                <p class="recipe-description"><?php echo wp_trim_words($recipe->description, 20); ?></p>
                                <div class="recipe-rating">
                                    <?php if ($recipe->avg_rating): ?>
                                        <span class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star <?php echo $i <= $recipe->avg_rating ? 'filled' : ''; ?>">★</span>
                                            <?php endfor; ?>
                                        </span>
                                        <span class="rating-text">(<?php echo $recipe->rating_count; ?> ratings)</span>
                                    <?php else: ?>
                                        <span class="no-rating">No ratings yet</span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?php echo add_query_arg('recipe_id', $recipe->id, get_permalink()); ?>" class="recipe-link">View Recipe</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Recipe submit shortcode
     */
    public function recipe_submit_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please <a href="' . wp_login_url() . '">login</a> to submit a recipe.</p>';
        }
        
        ob_start();
        ?>
        <div class="recipe-submit-container">
            <h2>Submit Your Recipe</h2>
            <form id="recipe-submit-form" class="recipe-form">
                <div class="form-group">
                    <label for="recipe-title">Recipe Title *</label>
                    <input type="text" id="recipe-title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="recipe-description">Description *</label>
                    <textarea id="recipe-description" name="description" rows="3" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="recipe-cooking-time">Cooking Time (minutes)</label>
                        <input type="number" id="recipe-cooking-time" name="cooking_time" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="recipe-difficulty">Difficulty</label>
                        <select id="recipe-difficulty" name="difficulty">
                            <option value="easy">Easy</option>
                            <option value="medium" selected>Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="recipe-servings">Servings</label>
                        <input type="number" id="recipe-servings" name="servings" min="1" value="1">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="recipe-ingredients">Ingredients *</label>
                    <textarea id="recipe-ingredients" name="ingredients" rows="5" required placeholder="List each ingredient on a new line"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="recipe-instructions">Instructions *</label>
                    <textarea id="recipe-instructions" name="instructions" rows="8" required placeholder="List each step on a new line"></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="submit-button">Submit Recipe</button>
                </div>
                
                <div id="recipe-submit-message"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Recipe detail shortcode
     */
    public function recipe_detail_shortcode($atts) {
        $recipe_id = isset($_GET['recipe_id']) ? intval($_GET['recipe_id']) : 0;
        
        if (!$recipe_id) {
            return '<p>Recipe not found.</p>';
        }
        
        $recipe = Recipe_Database::get_recipe($recipe_id);
        
        if (!$recipe) {
            return '<p>Recipe not found.</p>';
        }
        
        $comments = Recipe_Database::get_comments($recipe_id);
        
        ob_start();
        ?>
        <div class="recipe-detail-container">
            <div class="recipe-header">
                <h1><?php echo esc_html($recipe->title); ?></h1>
                <div class="recipe-meta">
                    <span>By <?php echo esc_html($recipe->author_name); ?></span>
                    <span>•</span>
                    <span><?php echo esc_html(ucfirst($recipe->difficulty)); ?></span>
                    <span>•</span>
                    <span><?php echo $recipe->cooking_time ? $recipe->cooking_time . ' minutes' : 'N/A'; ?></span>
                    <span>•</span>
                    <span><?php echo $recipe->servings; ?> servings</span>
                </div>
                
                <?php if ($recipe->avg_rating): ?>
                    <div class="recipe-rating">
                        <span class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?php echo $i <= $recipe->avg_rating ? 'filled' : ''; ?>">★</span>
                            <?php endfor; ?>
                        </span>
                        <span class="rating-text"><?php echo number_format($recipe->avg_rating, 1); ?> (<?php echo $recipe->rating_count; ?> ratings)</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($recipe->image_url): ?>
                <div class="recipe-image">
                    <img src="<?php echo esc_url($recipe->image_url); ?>" alt="<?php echo esc_attr($recipe->title); ?>">
                </div>
            <?php endif; ?>
            
            <div class="recipe-description">
                <h3>Description</h3>
                <p><?php echo nl2br(esc_html($recipe->description)); ?></p>
            </div>
            
            <div class="recipe-content">
                <div class="recipe-ingredients">
                    <h3>Ingredients</h3>
                    <ul>
                        <?php 
                        $ingredients = explode("\n", $recipe->ingredients);
                        foreach ($ingredients as $ingredient):
                            $ingredient = trim($ingredient);
                            if (!empty($ingredient)):
                        ?>
                            <li><?php echo esc_html($ingredient); ?></li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </ul>
                </div>
                
                <div class="recipe-instructions">
                    <h3>Instructions</h3>
                    <ol>
                        <?php 
                        $instructions = explode("\n", $recipe->instructions);
                        foreach ($instructions as $instruction):
                            $instruction = trim($instruction);
                            if (!empty($instruction)):
                        ?>
                            <li><?php echo esc_html($instruction); ?></li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </ol>
                </div>
            </div>
            
            <?php if (is_user_logged_in()): ?>
                <div class="recipe-actions">
                    <div class="rating-section">
                        <h3>Rate this Recipe</h3>
                        <div class="rating-stars" data-recipe-id="<?php echo $recipe->id; ?>">
                            <span class="star" data-rating="1">★</span>
                            <span class="star" data-rating="2">★</span>
                            <span class="star" data-rating="3">★</span>
                            <span class="star" data-rating="4">★</span>
                            <span class="star" data-rating="5">★</span>
                        </div>
                    </div>
                    
                    <div class="comment-section">
                        <h3>Add a Comment</h3>
                        <form id="recipe-comment-form" class="comment-form">
                            <input type="hidden" name="recipe_id" value="<?php echo $recipe->id; ?>">
                            <textarea name="comment" rows="3" placeholder="Share your thoughts about this recipe..." required></textarea>
                            <button type="submit">Post Comment</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="recipe-comments">
                <h3>Comments (<?php echo count($comments); ?>)</h3>
                <?php if (empty($comments)): ?>
                    <p>No comments yet. Be the first to comment!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <div class="comment-meta">
                                <strong><?php echo esc_html($comment->author_name); ?></strong>
                                <span><?php echo date('M j, Y', strtotime($comment->created_at)); ?></span>
                            </div>
                            <div class="comment-content">
                                <?php echo nl2br(esc_html($comment->comment)); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * AJAX submit recipe (for non-logged in users)
     */
    public function ajax_submit_recipe() {
        wp_send_json_error('Please login to submit a recipe');
    }
    
    /**
     * AJAX rate recipe (for non-logged in users)
     */
    public function ajax_rate_recipe() {
        wp_send_json_error('Please login to rate recipes');
    }
    
    /**
     * AJAX add comment (for non-logged in users)
     */
    public function ajax_add_comment() {
        wp_send_json_error('Please login to add comments');
    }
} 