<?php
/**
 * REST API endpoints for Recipe Sharing Plugin
 */

class Recipe_API {
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
        add_action('wp_ajax_recipe_submit', array($this, 'ajax_submit_recipe'));
        add_action('wp_ajax_recipe_rate', array($this, 'ajax_rate_recipe'));
        add_action('wp_ajax_recipe_comment', array($this, 'ajax_add_comment'));
    }
    
    /**
     * Register REST routes
     */
    public function register_routes() {
        register_rest_route('recipe-sharing/v1', '/recipes', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_recipes'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('recipe-sharing/v1', '/recipes/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_recipe'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('recipe-sharing/v1', '/recipes', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_recipe'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('recipe-sharing/v1', '/recipes/(?P<id>\d+)/rate', array(
            'methods' => 'POST',
            'callback' => array($this, 'rate_recipe'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        register_rest_route('recipe-sharing/v1', '/recipes/(?P<id>\d+)/comments', array(
            'methods' => 'POST',
            'callback' => array($this, 'add_comment'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
    }
    
    /**
     * Check if user is logged in
     */
    public function check_user_permission() {
        return is_user_logged_in();
    }
    
    /**
     * Get recipes endpoint
     */
    public function get_recipes($request) {
        $params = $request->get_params();
        
        $args = array(
            'status' => isset($params['status']) ? $params['status'] : 'approved',
            'limit' => isset($params['limit']) ? intval($params['limit']) : 10,
            'offset' => isset($params['offset']) ? intval($params['offset']) : 0
        );
        
        $recipes = Recipe_Database::get_recipes($args);
        
        return new WP_REST_Response($recipes, 200);
    }
    
    /**
     * Get single recipe endpoint
     */
    public function get_recipe($request) {
        $recipe_id = $request->get_param('id');
        $recipe = Recipe_Database::get_recipe($recipe_id);
        
        if (!$recipe) {
            return new WP_Error('not_found', 'Recipe not found', array('status' => 404));
        }
        
        // Get comments for this recipe
        $comments = Recipe_Database::get_comments($recipe_id);
        $recipe->comments = $comments;
        
        return new WP_REST_Response($recipe, 200);
    }
    
    /**
     * Create recipe endpoint
     */
    public function create_recipe($request) {
        $params = $request->get_params();
        
        $required_fields = array('title', 'description', 'ingredients', 'instructions');
        
        foreach ($required_fields as $field) {
            if (empty($params[$field])) {
                return new WP_Error('missing_field', "Missing required field: $field", array('status' => 400));
            }
        }
        
        $recipe_data = array(
            'title' => sanitize_text_field($params['title']),
            'description' => sanitize_textarea_field($params['description']),
            'ingredients' => sanitize_textarea_field($params['ingredients']),
            'instructions' => sanitize_textarea_field($params['instructions']),
            'cooking_time' => isset($params['cooking_time']) ? intval($params['cooking_time']) : 0,
            'difficulty' => isset($params['difficulty']) ? sanitize_text_field($params['difficulty']) : 'medium',
            'servings' => isset($params['servings']) ? intval($params['servings']) : 1,
            'image_url' => isset($params['image_url']) ? esc_url_raw($params['image_url']) : ''
        );
        
        $recipe_id = Recipe_Database::insert_recipe($recipe_data);
        
        if ($recipe_id) {
            $recipe = Recipe_Database::get_recipe($recipe_id);
            return new WP_REST_Response($recipe, 201);
        } else {
            return new WP_Error('insert_failed', 'Failed to create recipe', array('status' => 500));
        }
    }
    
    /**
     * Rate recipe endpoint
     */
    public function rate_recipe($request) {
        $recipe_id = $request->get_param('id');
        $params = $request->get_params();
        
        if (!isset($params['rating']) || !is_numeric($params['rating']) || $params['rating'] < 1 || $params['rating'] > 5) {
            return new WP_Error('invalid_rating', 'Rating must be between 1 and 5', array('status' => 400));
        }
        
        $user_id = get_current_user_id();
        $rating = intval($params['rating']);
        
        $result = Recipe_Database::add_rating($recipe_id, $user_id, $rating);
        
        if ($result) {
            return new WP_REST_Response(array('message' => 'Rating added successfully'), 200);
        } else {
            return new WP_Error('rating_failed', 'Failed to add rating', array('status' => 500));
        }
    }
    
    /**
     * Add comment endpoint
     */
    public function add_comment($request) {
        $recipe_id = $request->get_param('id');
        $params = $request->get_params();
        
        if (empty($params['comment'])) {
            return new WP_Error('missing_comment', 'Comment is required', array('status' => 400));
        }
        
        $user_id = get_current_user_id();
        $comment = sanitize_textarea_field($params['comment']);
        
        $result = Recipe_Database::add_comment($recipe_id, $user_id, $comment);
        
        if ($result) {
            return new WP_REST_Response(array('message' => 'Comment added successfully'), 200);
        } else {
            return new WP_Error('comment_failed', 'Failed to add comment', array('status' => 500));
        }
    }
    
    /**
     * AJAX submit recipe
     */
    public function ajax_submit_recipe() {
        check_ajax_referer('recipe_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_die('User not logged in');
        }
        
        $recipe_data = array(
            'title' => sanitize_text_field($_POST['title']),
            'description' => sanitize_textarea_field($_POST['description']),
            'ingredients' => sanitize_textarea_field($_POST['ingredients']),
            'instructions' => sanitize_textarea_field($_POST['instructions']),
            'cooking_time' => isset($_POST['cooking_time']) ? intval($_POST['cooking_time']) : 0,
            'difficulty' => isset($_POST['difficulty']) ? sanitize_text_field($_POST['difficulty']) : 'medium',
            'servings' => isset($_POST['servings']) ? intval($_POST['servings']) : 1
        );
        
        $recipe_id = Recipe_Database::insert_recipe($recipe_data);
        
        if ($recipe_id) {
            wp_send_json_success(array('message' => 'Recipe submitted successfully', 'recipe_id' => $recipe_id));
        } else {
            wp_send_json_error('Failed to submit recipe');
        }
    }
    
    /**
     * AJAX rate recipe
     */
    public function ajax_rate_recipe() {
        check_ajax_referer('recipe_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_die('User not logged in');
        }
        
        $recipe_id = intval($_POST['recipe_id']);
        $rating = intval($_POST['rating']);
        
        if ($rating < 1 || $rating > 5) {
            wp_send_json_error('Invalid rating');
        }
        
        $user_id = get_current_user_id();
        $result = Recipe_Database::add_rating($recipe_id, $user_id, $rating);
        
        if ($result) {
            wp_send_json_success('Rating added successfully');
        } else {
            wp_send_json_error('Failed to add rating');
        }
    }
    
    /**
     * AJAX add comment
     */
    public function ajax_add_comment() {
        check_ajax_referer('recipe_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_die('User not logged in');
        }
        
        $recipe_id = intval($_POST['recipe_id']);
        $comment = sanitize_textarea_field($_POST['comment']);
        
        if (empty($comment)) {
            wp_send_json_error('Comment is required');
        }
        
        $user_id = get_current_user_id();
        $result = Recipe_Database::add_comment($recipe_id, $user_id, $comment);
        
        if ($result) {
            wp_send_json_success('Comment added successfully');
        } else {
            wp_send_json_error('Failed to add comment');
        }
    }
} 