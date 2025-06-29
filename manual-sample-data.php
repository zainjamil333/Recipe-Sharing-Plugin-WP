<?php
/**
 * Manual Sample Data - Access this file directly to add sample recipes
 * URL: yoursite.com/wp-content/plugins/recipe-sharing/manual-sample-data.php
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

// Process form submission
if (isset($_POST['add_sample_data'])) {
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
    
    $success_message = "Successfully added $inserted_count sample recipes with ratings and comments!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Sample Data - Recipe Sharing Plugin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f1f1f1; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .button { background: #0073aa; color: white; padding: 15px 30px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .button:hover { background: #005a87; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .recipe-preview { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🍳 Recipe Sharing Plugin - Add Sample Data</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="success">
                <strong>✅ Success!</strong> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <h3>What this will do:</h3>
            <ul>
                <li>Add 3 sample recipes (Margherita Pizza, Chocolate Chip Cookies, Grilled Chicken Salad)</li>
                <li>Add random ratings (3-5 stars) for each recipe</li>
                <li>Add sample comments for each recipe</li>
                <li>All recipes will be automatically approved</li>
            </ul>
        </div>
        
        <h3>Sample Recipes Preview:</h3>
        <?php foreach ($sample_recipes as $recipe): ?>
            <div class="recipe-preview">
                <h4><?php echo esc_html($recipe['title']); ?></h4>
                <p><strong>Description:</strong> <?php echo esc_html($recipe['description']); ?></p>
                <p><strong>Difficulty:</strong> <?php echo esc_html(ucfirst($recipe['difficulty'])); ?> | 
                   <strong>Cooking Time:</strong> <?php echo $recipe['cooking_time']; ?> minutes | 
                   <strong>Servings:</strong> <?php echo $recipe['servings']; ?></p>
            </div>
        <?php endforeach; ?>
        
        <form method="post" style="margin-top: 30px;">
            <button type="submit" name="add_sample_data" class="button">
                🍳 Add Sample Recipes
            </button>
        </form>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <h3>Next Steps:</h3>
            <ol>
                <li>After adding sample data, go to <strong>WordPress Admin → Recipes</strong> to see the recipes</li>
                <li>Create pages with these shortcodes:
                    <ul>
                        <li><code>[recipe_list]</code> - Display recipe collection</li>
                        <li><code>[recipe_submit]</code> - Recipe submission form</li>
                        <li><code>[recipe_detail]</code> - Recipe detail page</li>
                    </ul>
                </li>
                <li>Test the frontend by visiting your recipe pages</li>
            </ol>
        </div>
    </div>
</body>
</html> 