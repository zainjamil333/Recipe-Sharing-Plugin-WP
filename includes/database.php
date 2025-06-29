<?php
/**
 * Database operations for Recipe Sharing Plugin
 */

class Recipe_Database {
    
    /**
     * Create database tables on plugin activation
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Recipes table
        $recipes_table = $wpdb->prefix . 'recipe_recipes';
        $sql_recipes = "CREATE TABLE $recipes_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            title varchar(255) NOT NULL,
            description text NOT NULL,
            ingredients text NOT NULL,
            instructions text NOT NULL,
            cooking_time int(11) DEFAULT 0,
            difficulty varchar(50) DEFAULT 'medium',
            servings int(11) DEFAULT 1,
            image_url varchar(255) DEFAULT '',
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";
        
        // Ratings table
        $ratings_table = $wpdb->prefix . 'recipe_ratings';
        $sql_ratings = "CREATE TABLE $ratings_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            recipe_id mediumint(9) NOT NULL,
            user_id bigint(20) NOT NULL,
            rating int(1) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY recipe_user (recipe_id, user_id),
            KEY recipe_id (recipe_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        // Comments table
        $comments_table = $wpdb->prefix . 'recipe_comments';
        $sql_comments = "CREATE TABLE $comments_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            recipe_id mediumint(9) NOT NULL,
            user_id bigint(20) NOT NULL,
            comment text NOT NULL,
            status varchar(20) DEFAULT 'approved',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY recipe_id (recipe_id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";
        
        // Include WordPress upgrade functions
        if (!function_exists('dbDelta')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        }
        
        // Create tables
        $result1 = dbDelta($sql_recipes);
        $result2 = dbDelta($sql_ratings);
        $result3 = dbDelta($sql_comments);
        
        // Check for errors
        if (empty($result1) && empty($result2) && empty($result3)) {
            // Tables might already exist, which is fine
            return true;
        }
        
        return true;
    }
    
    /**
     * Get all recipes with optional filters
     */
    public static function get_recipes($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'status' => 'approved',
            'limit' => 10,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $recipes_table = $wpdb->prefix . 'recipe_recipes';
        $users_table = $wpdb->users;
        
        $sql = "SELECT r.*, u.display_name as author_name, 
                (SELECT AVG(rating) FROM {$wpdb->prefix}recipe_ratings WHERE recipe_id = r.id) as avg_rating,
                (SELECT COUNT(*) FROM {$wpdb->prefix}recipe_ratings WHERE recipe_id = r.id) as rating_count
                FROM $recipes_table r
                LEFT JOIN $users_table u ON r.user_id = u.ID
                WHERE r.status = %s
                ORDER BY r.{$args['orderby']} {$args['order']}
                LIMIT %d OFFSET %d";
        
        return $wpdb->get_results($wpdb->prepare($sql, $args['status'], $args['limit'], $args['offset']));
    }
    
    /**
     * Get single recipe by ID
     */
    public static function get_recipe($id) {
        global $wpdb;
        
        $recipes_table = $wpdb->prefix . 'recipe_recipes';
        $users_table = $wpdb->users;
        
        $sql = "SELECT r.*, u.display_name as author_name,
                (SELECT AVG(rating) FROM {$wpdb->prefix}recipe_ratings WHERE recipe_id = r.id) as avg_rating,
                (SELECT COUNT(*) FROM {$wpdb->prefix}recipe_ratings WHERE recipe_id = r.id) as rating_count
                FROM $recipes_table r
                LEFT JOIN $users_table u ON r.user_id = u.ID
                WHERE r.id = %d";
        
        return $wpdb->get_row($wpdb->prepare($sql, $id));
    }
    
    /**
     * Insert new recipe
     */
    public static function insert_recipe($data) {
        global $wpdb;
        
        $recipes_table = $wpdb->prefix . 'recipe_recipes';
        
        $defaults = array(
            'user_id' => get_current_user_id(),
            'status' => 'pending'
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert($recipes_table, $data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update recipe
     */
    public static function update_recipe($id, $data) {
        global $wpdb;
        
        $recipes_table = $wpdb->prefix . 'recipe_recipes';
        
        return $wpdb->update($recipes_table, $data, array('id' => $id));
    }
    
    /**
     * Delete recipe
     */
    public static function delete_recipe($id) {
        global $wpdb;
        
        $recipes_table = $wpdb->prefix . 'recipe_recipes';
        
        return $wpdb->delete($recipes_table, array('id' => $id));
    }
    
    /**
     * Add rating
     */
    public static function add_rating($recipe_id, $user_id, $rating) {
        global $wpdb;
        
        $ratings_table = $wpdb->prefix . 'recipe_ratings';
        
        return $wpdb->replace($ratings_table, array(
            'recipe_id' => $recipe_id,
            'user_id' => $user_id,
            'rating' => $rating
        ));
    }
    
    /**
     * Get comments for recipe
     */
    public static function get_comments($recipe_id) {
        global $wpdb;
        
        $comments_table = $wpdb->prefix . 'recipe_comments';
        $users_table = $wpdb->users;
        
        $sql = "SELECT c.*, u.display_name as author_name
                FROM $comments_table c
                LEFT JOIN $users_table u ON c.user_id = u.ID
                WHERE c.recipe_id = %d AND c.status = 'approved'
                ORDER BY c.created_at DESC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $recipe_id));
    }
    
    /**
     * Add comment
     */
    public static function add_comment($recipe_id, $user_id, $comment) {
        global $wpdb;
        
        $comments_table = $wpdb->prefix . 'recipe_comments';
        
        return $wpdb->insert($comments_table, array(
            'recipe_id' => $recipe_id,
            'user_id' => $user_id,
            'comment' => $comment
        ));
    }
} 