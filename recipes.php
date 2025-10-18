<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$recipes = $db->fetchAll("SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC", [$userId]);

$pageTitle = 'Recipe Book & Meal Planner';
$extraScripts = ['/assets/js/new-modules.js'];
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-utensils text-primary"></i>
                Recipe Book & Meal Planner
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Organize recipes and plan your meals</p>
        </div>
        <div class="flex gap-2">
            <button onclick="showAddRecipeModal()" class="btn btn-primary flex items-center gap-2">
                <i class="fas fa-plus"></i>
                Add Recipe
            </button>
            <button onclick="viewMealPlan()" class="btn btn-secondary flex items-center gap-2">
                <i class="fas fa-calendar-alt"></i>
                Meal Plan
            </button>
            <button onclick="viewShoppingList()" class="btn btn-secondary flex items-center gap-2">
                <i class="fas fa-shopping-cart"></i>
                Shopping List
            </button>
        </div>
    </div>

    <div class="mb-6">
        <div class="flex gap-2 flex-wrap">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="breakfast">Breakfast</button>
            <button class="filter-btn" data-filter="lunch">Lunch</button>
            <button class="filter-btn" data-filter="dinner">Dinner</button>
            <button class="filter-btn" data-filter="snack">Snack</button>
            <button class="filter-btn" data-filter="dessert">Dessert</button>
        </div>
    </div>

    <div id="recipesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($recipes as $recipe): ?>
        <div class="recipe-card bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow" data-category="<?php echo $recipe['category']; ?>">
            <?php if ($recipe['image_url']): ?>
            <img src="<?php echo sanitize($recipe['image_url']); ?>" alt="<?php echo sanitize($recipe['name']); ?>" class="w-full h-48 object-cover">
            <?php else: ?>
            <div class="w-full h-48 bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center">
                <i class="fas fa-utensils text-white text-6xl"></i>
            </div>
            <?php endif; ?>
            
            <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white"><?php echo sanitize($recipe['name']); ?></h3>
                    <?php if ($recipe['is_favorite']): ?>
                    <i class="fas fa-heart text-red-500"></i>
                    <?php endif; ?>
                </div>
                
                <?php if ($recipe['description']): ?>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3"><?php echo sanitize(substr($recipe['description'], 0, 100)); ?>...</p>
                <?php endif; ?>
                
                <div class="flex gap-4 text-sm text-gray-600 dark:text-gray-400 mb-3">
                    <span><i class="far fa-clock mr-1"></i><?php echo $recipe['prep_time'] + $recipe['cook_time']; ?> min</span>
                    <span><i class="fas fa-users mr-1"></i><?php echo $recipe['servings']; ?> servings</span>
                </div>
                
                <div class="flex gap-2">
                    <button onclick="viewRecipe(<?php echo $recipe['id']; ?>)" class="flex-1 px-3 py-2 bg-primary text-white rounded hover:bg-blue-600">
                        View
                    </button>
                    <button onclick="addToMealPlan(<?php echo $recipe['id']; ?>)" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-100">
                        <i class="fas fa-calendar-plus"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="addRecipeModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold">Add Recipe</h2>
        </div>
        <form id="recipeForm" class="p-6 space-y-4">
            <input type="hidden" id="recipeId">
            
            <div>
                <label class="block text-sm font-medium mb-1">Recipe Name *</label>
                <input type="text" id="recipeName" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea id="recipeDescription" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Category *</label>
                    <select id="recipeCategory" required class="w-full px-3 py-2 border rounded-lg">
                        <option value="breakfast">Breakfast</option>
                        <option value="lunch">Lunch</option>
                        <option value="dinner">Dinner</option>
                        <option value="snack">Snack</option>
                        <option value="dessert">Dessert</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Cuisine</label>
                    <input type="text" id="recipeCuisine" class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Prep Time (min)</label>
                    <input type="number" id="prepTime" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Cook Time (min)</label>
                    <input type="number" id="cookTime" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Servings</label>
                    <input type="number" id="servings" class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Ingredients (one per line)</label>
                <textarea id="ingredients" rows="5" class="w-full px-3 py-2 border rounded-lg" placeholder="2 cups flour&#10;1 cup sugar&#10;3 eggs"></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Instructions</label>
                <textarea id="instructions" rows="5" class="w-full px-3 py-2 border rounded-lg"></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Image URL</label>
                <input type="url" id="imageUrl" class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeAddRecipeModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg">Save Recipe</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
