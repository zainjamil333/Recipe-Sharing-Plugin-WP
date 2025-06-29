# Recipe Sharing Plugin

A comprehensive WordPress plugin for creating a community-driven recipe sharing platform with user registration, recipe management, ratings, and comments.

## Features

### Core Functionality
- **User Registration & Login**: WordPress native user system with custom recipe submission
- **Recipe Management**: Add, edit, delete, and approve recipes
- **Rating System**: 1-5 star rating system for recipes
- **Comment System**: User comments on recipes with moderation
- **Admin Interface**: Complete admin panel for managing recipes and users

### Technical Features
- **REST API**: Custom REST endpoints for recipe operations
- **AJAX Support**: Smooth user interactions without page reloads
- **Responsive Design**: Mobile-friendly interface
- **Security**: Nonce verification and data sanitization
- **Database**: Custom tables for recipes, ratings, and comments

## Installation

1. **Upload Plugin**: Copy the plugin folder to `/wp-content/plugins/recipe-sharing/`
2. **Activate**: Go to WordPress Admin → Plugins → Activate "Recipe Sharing Plugin"
3. **Database Setup**: Plugin will automatically create required database tables
4. **Configure**: Access recipe management via WordPress Admin → Recipes

## Usage

### Shortcodes

#### Display Recipe List
```
[recipe_list limit="10" orderby="created_at" order="DESC"]
```

#### Recipe Submission Form
```
[recipe_submit]
```

#### Recipe Detail Page
```
[recipe_detail]
```

### Admin Interface

1. **Recipe Management**: WordPress Admin → Recipes
   - View all approved recipes
   - Manage pending recipes
   - Edit recipe details
   - Delete recipes

2. **Pending Recipes**: WordPress Admin → Recipes → Pending Recipes
   - Approve or reject submitted recipes
   - Edit before approval

### User Features

1. **Submit Recipes**: Users can submit recipes through the submission form
2. **Rate Recipes**: Logged-in users can rate recipes (1-5 stars)
3. **Comment**: Users can leave comments on recipes
4. **Browse Recipes**: View all approved recipes in a responsive grid

## Database Structure

### Tables Created

#### `wp_recipe_recipes`
- `id` - Primary key
- `user_id` - WordPress user ID
- `title` - Recipe title
- `description` - Recipe description
- `ingredients` - Recipe ingredients (text)
- `instructions` - Cooking instructions (text)
- `cooking_time` - Cooking time in minutes
- `difficulty` - Easy/Medium/Hard
- `servings` - Number of servings
- `image_url` - Recipe image URL
- `status` - Pending/Approved/Rejected
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

#### `wp_recipe_ratings`
- `id` - Primary key
- `recipe_id` - Recipe ID
- `user_id` - WordPress user ID
- `rating` - Rating (1-5)
- `created_at` - Rating timestamp

#### `wp_recipe_comments`
- `id` - Primary key
- `recipe_id` - Recipe ID
- `user_id` - WordPress user ID
- `comment` - Comment text
- `status` - Approved/Pending
- `created_at` - Comment timestamp

## API Endpoints

### GET Endpoints
- `GET /wp-json/recipe-sharing/v1/recipes` - Get all recipes
- `GET /wp-json/recipe-sharing/v1/recipes/{id}` - Get single recipe

### POST Endpoints (Require Authentication)
- `POST /wp-json/recipe-sharing/v1/recipes` - Create new recipe
- `POST /wp-json/recipe-sharing/v1/recipes/{id}/rate` - Rate recipe
- `POST /wp-json/recipe-sharing/v1/recipes/{id}/comments` - Add comment

## File Structure

```
recipe-sharing/
├── recipe-sharing.php          # Main plugin file
├── includes/
│   ├── database.php            # Database operations
│   ├── api.php                 # REST API endpoints
│   ├── admin.php               # Admin interface
│   └── frontend.php            # Frontend functionality
├── assets/
│   ├── css/
│   │   ├── style.css           # Frontend styles
│   │   └── admin.css           # Admin styles
│   └── js/
│       ├── frontend.js         # Frontend JavaScript
│       └── admin.js            # Admin JavaScript
└── README.md                   # This file
```

## Customization

### Styling
- Modify `assets/css/style.css` for frontend styling
- Modify `assets/css/admin.css` for admin interface styling

### Functionality
- Extend classes in `includes/` directory
- Add new shortcodes in `includes/frontend.php`
- Create new API endpoints in `includes/api.php`

### Database
- Add new fields to existing tables
- Create new tables for additional features
- Modify queries in `includes/database.php`

## Security Features

- **Nonce Verification**: All AJAX requests use WordPress nonces
- **Data Sanitization**: All user input is sanitized
- **Permission Checks**: Admin functions require proper capabilities
- **SQL Prepared Statements**: All database queries use prepared statements

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Internet Explorer 11+

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- jQuery (included with WordPress)

## Troubleshooting

### Common Issues

1. **Plugin not activating**: Check PHP version and WordPress version
2. **Database tables not created**: Deactivate and reactivate the plugin
3. **AJAX not working**: Check if jQuery is loaded
4. **Styling issues**: Clear browser cache and WordPress cache

### Debug Mode

Enable WordPress debug mode to see detailed error messages:
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

For support and feature requests, please contact the plugin developer.

## License

This plugin is developed for educational purposes as part of a university project.

## Changelog

### Version 1.0.0
- Initial release
- Basic recipe management
- User registration and login
- Rating and comment system
- Admin interface
- REST API endpoints
- Responsive design

## 🚀 Quick Setup Guide

### 1. **Admin Backend** (Already Working)
- Go to **WordPress Admin** → **Recipe Management**
- You'll see:
  - **"Add Sample Recipes"** button (adds 3 test recipes)
  - **"Add New Recipe"** button (admin can add recipes directly)
  - **All Recipes** table (manage existing recipes)
  - **Pending Recipes** table (approve customer submissions)

### 2. **Frontend Pages Setup** (For Customers)

#### **Step 1: Create "Recipe List" Page**
1. Go to **Pages** → **Add New**
2. **Title**: "Recipes" or "All Recipes"
3. **Content**: Add shortcode `[recipe_list]`
4. **Publish**

#### **Step 2: Create "Submit Recipe" Page**
1. Go to **Pages** → **Add New**
2. **Title**: "Submit Recipe" or "Share Your Recipe"
3. **Content**: Add shortcode `[recipe_submit]`
4. **Publish**

#### **Step 3: Create "Recipe Detail" Page**
1. Go to **Pages** → **Add New**
2. **Title**: "Recipe Detail"
3. **Content**: Add shortcode `[recipe_detail]`
4. **Publish**

### 3. **How Customers Add Recipes**

#### **Method 1: Frontend Form (Recommended)**
1. Customer visits your "Submit Recipe" page
2. They fill out the form with:
   - Recipe title
   - Description
   - Ingredients (one per line)
   - Instructions (one step per line)
   - Cooking time
   - Difficulty level
   - Number of servings
3. They click "Submit Recipe"
4. Recipe goes to **"Pending"** status
5. **Admin approves** it in the backend

#### **Method 2: Admin Backend**
1. Admin goes to **Recipe Management**
2. Clicks **"Add New Recipe"** button
3. Fills out the form
4. Sets status to "Approved" or "Pending"
5. Saves the recipe

### 4. **Navigation Setup**
Add these pages to your menu:
1. Go to **Appearance** → **Menus**
2. Add the pages you created above
3. Save the menu

## 📋 Available Shortcodes

- `[recipe_list]` - Shows all approved recipes
- `[recipe_submit]` - Shows recipe submission form
- `[recipe_detail]` - Shows individual recipe details (used with recipe ID)

## 🔧 Troubleshooting

### "No recipes found" on frontend?
1. **Add sample data**: Click "Add Sample Recipes" in admin
2. **Check recipe status**: Make sure recipes are "Approved"
3. **Debug**: Visit `/wp-content/plugins/recipe-sharing/debug-recipes.php`

### Frontend form not working?
1. Make sure you're logged in as a user
2. Check that the shortcode is properly added to the page
3. Verify the plugin is activated

## 🎯 Next Steps

1. **Add sample data** using the admin button
2. **Create the frontend pages** with shortcodes
3. **Test the submission process**:
   - Submit a recipe as a customer
   - Approve it as an admin
   - View it on the frontend
4. **Customize styling** in the CSS files if needed

## 📁 File Structure

```
recipe-sharing/
├── recipe-sharing.php          # Main plugin file
├── includes/
│   ├── database.php           # Database operations
│   ├── admin.php              # Admin interface
│   ├── frontend.php           # Frontend display
│   └── api.php                # REST API endpoints
├── assets/
│   ├── css/
│   │   ├── admin.css          # Admin styles
│   │   └── style.css          # Frontend styles
│   └── js/
│       ├── admin.js           # Admin JavaScript
│       └── frontend.js        # Frontend JavaScript
└── debug-recipes.php          # Debug script
```

## 🆘 Support

If you encounter issues:
1. Check the debug page: `/wp-content/plugins/recipe-sharing/debug-recipes.php`
2. Verify all pages are created with correct shortcodes
3. Ensure recipes are approved in the admin panel