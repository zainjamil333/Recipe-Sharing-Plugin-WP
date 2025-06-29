<?php
/**
 * Debug Script - Check if recipes exist
 * Access this file to debug recipe issues
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('You need to be an administrator to run this script.');
}

// Check if Recipe_Database class exists
if (!class_exists('Recipe_Database')) {
    wp_die('Recipe Sharing Plugin is not active. Please activate the plugin first.');
}

// Check database tables
global $wpdb;
$recipes_table = $wpdb->prefix . 'recipe_recipes';
$ratings_table = $wpdb->prefix . 'recipe_ratings';
$comments_table = $wpdb->prefix . 'recipe_comments';

$tables_exist = array(
    'Recipes Table' => $wpdb->get_var("SHOW TABLES LIKE '$recipes_table'") == $recipes_table,
    'Ratings Table' => $wpdb->get_var("SHOW TABLES LIKE '$ratings_table'") == $ratings_table,
    'Comments Table' => $wpdb->get_var("SHOW TABLES LIKE '$comments_table'") == $comments_table
);

// Count recipes
$total_recipes = $wpdb->get_var("SELECT COUNT(*) FROM $recipes_table");
$approved_recipes = $wpdb->get_var("SELECT COUNT(*) FROM $recipes_table WHERE status = 'approved'");
$pending_recipes = $wpdb->get_var("SELECT COUNT(*) FROM $recipes_table WHERE status = 'pending'");

// Get all recipes
$all_recipes = $wpdb->get_results("SELECT * FROM $recipes_table ORDER BY created_at DESC");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Recipe Debug - Recipe Sharing Plugin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f1f1f1; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .status-approved { color: #28a745; font-weight: bold; }
        .status-pending { color: #ffc107; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Recipe Debug Information</h1>
        
        <h2>Database Tables Status</h2>
        <table>
            <tr><th>Table</th><th>Exists</th></tr>
            <?php foreach ($tables_exist as $table_name => $exists): ?>
            <tr>
                <td><?php echo $table_name; ?></td>
                <td><?php echo $exists ? '✅ Yes' : '❌ No'; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <h2>Recipe Counts</h2>
        <div class="info">
            <p><strong>Total Recipes:</strong> <?php echo $total_recipes; ?></p>
            <p><strong>Approved Recipes:</strong> <?php echo $approved_recipes; ?></p>
            <p><strong>Pending Recipes:</strong> <?php echo $pending_recipes; ?></p>
        </div>
        
        <?php if ($total_recipes > 0): ?>
            <h2>All Recipes in Database</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Author</th>
                    <th>Created</th>
                </tr>
                <?php foreach ($all_recipes as $recipe): ?>
                <tr>
                    <td><?php echo $recipe->id; ?></td>
                    <td><?php echo esc_html($recipe->title); ?></td>
                    <td class="status-<?php echo $recipe->status; ?>"><?php echo ucfirst($recipe->status); ?></td>
                    <td><?php echo get_userdata($recipe->user_id)->display_name; ?></td>
                    <td><?php echo date('M j, Y', strtotime($recipe->created_at)); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <div class="error">
                <h3>❌ No Recipes Found in Database</h3>
                <p>You need to add some recipes first. Use one of these methods:</p>
                <ol>
                    <li>Go to <strong>WordPress Admin → Recipes</strong> and click "Add Sample Recipes"</li>
                    <li>Or visit: <code>yoursite.com/wp-content/plugins/recipe-sharing/manual-sample-data.php</code></li>
                </ol>
            </div>
        <?php endif; ?>
        
        <h2>Test Shortcode Output</h2>
        <div class="info">
            <p><strong>Shortcode:</strong> <code>[recipe_list]</code></p>
            <p><strong>Expected Output:</strong> Should show <?php echo $approved_recipes; ?> approved recipes</p>
        </div>
        
        <?php if ($approved_recipes > 0): ?>
            <div class="success">
                <h3>✅ Recipes Found!</h3>
                <p>You have <?php echo $approved_recipes; ?> approved recipes. The shortcode should work.</p>
                <p><strong>Try this on your page:</strong> <code>[recipe_list limit="10"]</code></p>
            </div>
        <?php else: ?>
            <div class="error">
                <h3>❌ No Approved Recipes</h3>
                <p>You have <?php echo $total_recipes; ?> recipes but none are approved.</p>
                <p>Go to <strong>WordPress Admin → Recipes → Pending Recipes</strong> to approve them.</p>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <h3>Next Steps:</h3>
            <ol>
                <li>If no recipes exist: Add sample data using the methods above</li>
                <li>If recipes exist but aren't approved: Approve them in the admin</li>
                <li>If recipes are approved: Check your shortcode syntax on the page</li>
                <li>Try the shortcode: <code>[recipe_list limit="5"]</code></li>
            </ol>
        </div>
    </div>
</body>
</html> 