<?php
/**
 * Plugin Name: Recipe Sharing Plugin
 * Description: A simple recipe sharing platform with user registration, recipe management, and rating system
 * Version: 1.0.0
 * Author: Your Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RECIPE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RECIPE_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Check if required files exist before including
$required_files = array(
    'includes/database.php',
    'includes/api.php',
    'includes/admin.php',
    'includes/frontend.php'
);

foreach ($required_files as $file) {
    $file_path = RECIPE_PLUGIN_PATH . $file;
    if (!file_exists($file_path)) {
        add_action('admin_notices', function() use ($file) {
            echo '<div class="notice notice-error"><p>Recipe Sharing Plugin: Required file missing: ' . esc_html($file) . '</p></div>';
        });
        return;
    }
}

// Include required files
require_once RECIPE_PLUGIN_PATH . 'includes/database.php';
require_once RECIPE_PLUGIN_PATH . 'includes/api.php';
require_once RECIPE_PLUGIN_PATH . 'includes/admin.php';
require_once RECIPE_PLUGIN_PATH . 'includes/frontend.php';

// Activation hook
register_activation_hook(__FILE__, 'recipe_plugin_activate');

function recipe_plugin_activate() {
    // Check if required classes exist
    if (!class_exists('Recipe_Database')) {
        wp_die('Recipe Sharing Plugin: Required class Recipe_Database not found. Please reinstall the plugin.');
    }
    
    try {
        // Create database tables
        Recipe_Database::create_tables();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Add activation flag
        add_option('recipe_sharing_activated', true);
        
    } catch (Exception $e) {
        wp_die('Recipe Sharing Plugin: Error during activation: ' . $e->getMessage());
    } catch (Error $e) {
        wp_die('Recipe Sharing Plugin: Fatal error during activation: ' . $e->getMessage());
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'recipe_plugin_deactivate');

function recipe_plugin_deactivate() {
    flush_rewrite_rules();
}

// Initialize plugin
function recipe_plugin_init() {
    // Check if required classes exist
    if (!class_exists('Recipe_Database') || !class_exists('Recipe_API') || 
        !class_exists('Recipe_Admin') || !class_exists('Recipe_Frontend')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>Recipe Sharing Plugin: Required classes not found. Please reinstall the plugin.</p></div>';
        });
        return;
    }
    
    // Initialize admin
    if (is_admin()) {
        new Recipe_Admin();
    }
    
    // Initialize frontend
    new Recipe_Frontend();
    
    // Initialize API
    new Recipe_API();
}

add_action('init', 'recipe_plugin_init');

// Enqueue scripts and styles
function recipe_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('recipe-frontend', RECIPE_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), '1.0.0', true);
    wp_enqueue_style('recipe-styles', RECIPE_PLUGIN_URL . 'assets/css/style.css', array(), '1.0.0');
    
    // Localize script for AJAX
    wp_localize_script('recipe-frontend', 'recipe_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('recipe_nonce')
    ));
}

add_action('wp_enqueue_scripts', 'recipe_enqueue_scripts'); 