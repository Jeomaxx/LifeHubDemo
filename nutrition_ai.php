<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Nutrition AI';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i data-lucide="apple" class="text-primary"></i>
                Nutrition AI
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">AI-powered personalized meal plans and nutrition recommendations</p>
        </div>
        <button onclick="generateMealPlan()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i data-lucide="sparkles" class="w-4 h-4"></i>
            <span>Generate Meal Plan</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Daily Calories</p>
                    <p id="dailyCalories" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">2000</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="flame" class="text-orange-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Protein Target</p>
                    <p id="proteinTarget" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">150g</p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="activity" class="text-red-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Active Meal Plans</p>
                    <p id="activePlans" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="calendar" class="text-green-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Recipes Saved</p>
                    <p id="savedRecipes" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="book-open" class="text-purple-600 w-6 h-6"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Your Meal Plans</h2>
            <div id="mealPlansList"></div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Nutrition Profile</h2>
            <div id="nutritionProfile">
                <div class="space-y-4">
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-400">Age</label>
                        <input type="number" id="age" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 mt-1">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-400">Gender</label>
                        <select id="gender" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 mt-1">
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-400">Height (cm)</label>
                        <input type="number" id="height" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 mt-1">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-400">Current Weight (kg)</label>
                        <input type="number" id="currentWeight" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 mt-1">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-400">Target Weight (kg)</label>
                        <input type="number" id="targetWeight" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 mt-1">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-400">Activity Level</label>
                        <select id="activityLevel" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 mt-1">
                            <option value="sedentary">Sedentary</option>
                            <option value="light">Lightly Active</option>
                            <option value="moderate">Moderately Active</option>
                            <option value="very">Very Active</option>
                            <option value="extra">Extra Active</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-400">Dietary Restrictions</label>
                        <textarea id="restrictions" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 mt-1" rows="2"></textarea>
                    </div>
                    <button onclick="saveNutritionProfile()" class="btn bg-primary text-white w-full py-2 rounded-lg">
                        Save Profile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-r from-green-500 to-teal-600 rounded-lg p-6 text-white">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center">
                <i data-lucide="sparkles" class="w-8 h-8"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-xl font-semibold mb-1">AI-Powered Meal Planning</h3>
                <p class="opacity-90">Get personalized meal plans based on your goals, preferences, and nutritional needs</p>
            </div>
            <button onclick="generateMealPlan()" class="btn bg-white text-green-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100">
                Generate Plan
            </button>
        </div>
    </div>
</div>

<script src="/assets/js/nutrition-ai.js"></script>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>

<?php include 'includes/footer.php'; ?>
