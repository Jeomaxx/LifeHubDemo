<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$auth = new Auth();
$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Diet Plans';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-utensils text-primary"></i>
                <?php echo t('Diet Plans'); ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo t('Track your meals and nutrition goals'); ?></p>
        </div>
        <button onclick="openDietModal()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i>
            <span><?php echo t('Add Meal Plan'); ?></span>
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Daily Calorie Goal</p>
                    <p id="calorieGoal" class="text-2xl font-bold text-gray-900 dark:text-white">2000</p>
                </div>
                <i class="fas fa-fire text-3xl text-red-500"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Today's Intake</p>
                    <p id="todayCalories" class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                </div>
                <i class="fas fa-chart-pie text-3xl text-green-500"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Meals Today</p>
                    <p id="mealsCount" class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                </div>
                <i class="fas fa-utensils text-3xl text-blue-500"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Remaining</p>
                    <p id="remaining" class="text-2xl font-bold text-gray-900 dark:text-white">2000</p>
                </div>
                <i class="fas fa-balance-scale text-3xl text-purple-500"></i>
            </div>
        </div>
    </div>

    <!-- Diet Plans List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="p-4 border-b dark:border-gray-700">
            <div class="flex gap-4">
                <input type="date" id="filterDate" value="<?php echo date('Y-m-d'); ?>" 
                    class="px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <select id="filterMeal" class="px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    <option value="">All Meals</option>
                    <option value="breakfast">Breakfast</option>
                    <option value="lunch">Lunch</option>
                    <option value="dinner">Dinner</option>
                    <option value="snack">Snack</option>
                </select>
            </div>
        </div>
        <div id="plansContainer" class="divide-y dark:divide-gray-700"></div>
    </div>
</div>

<!-- Diet Modal -->
<div id="dietModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full p-6">
        <h2 class="text-2xl font-bold mb-4 dark:text-white">Add Meal Plan</h2>
        <form id="dietForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Meal Type*</label>
                    <select name="meal_type" required class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="">Select meal type</option>
                        <option value="breakfast">Breakfast</option>
                        <option value="lunch">Lunch</option>
                        <option value="dinner">Dinner</option>
                        <option value="snack">Snack</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Date*</label>
                    <input type="date" name="plan_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Foods*</label>
                <textarea name="foods" required rows="2" placeholder="e.g., Oatmeal, Banana, Almonds" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Calories</label>
                    <input type="number" name="calories" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Protein (g)</label>
                    <input type="number" name="protein" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Carbs (g)</label>
                    <input type="number" name="carbs" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Notes</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeDietModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg dark:text-gray-400 dark:hover:bg-gray-700">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">Save Meal</button>
            </div>
        </form>
    </div>
</div>

<script>
let plans = [];
const dailyGoal = 2000;

async function loadPlans() {
    const date = document.getElementById('filterDate').value;
    const response = await fetch(`/api/diet.php?action=day&date=${date}`);
    const data = await response.json();
    if (data.success) {
        plans = data.plans;
        updateStats(data.plans);
        renderPlans();
    }
}

function updateStats(plans) {
    const todayCalories = plans.reduce((sum, p) => sum + (parseInt(p.calories) || 0), 0);
    const mealsCount = plans.length;
    const remaining = dailyGoal - todayCalories;
    
    document.getElementById('calorieGoal').textContent = dailyGoal;
    document.getElementById('todayCalories').textContent = todayCalories;
    document.getElementById('mealsCount').textContent = mealsCount;
    document.getElementById('remaining').textContent = remaining;
}

function renderPlans() {
    const container = document.getElementById('plansContainer');
    const meal = document.getElementById('filterMeal').value;
    
    const filtered = plans.filter(p => !meal || p.meal_type === meal);
    
    container.innerHTML = filtered.map(plan => `
        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold dark:text-white capitalize">${plan.meal_type}</h3>
                        <span class="text-sm text-gray-500">${new Date(plan.plan_date).toLocaleDateString()}</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">${plan.foods}</p>
                    <div class="flex gap-4 mt-2 text-sm text-gray-600 dark:text-gray-400">
                        ${plan.calories ? `<span><i class="fas fa-fire text-orange-500"></i> ${plan.calories} cal</span>` : ''}
                        ${plan.protein ? `<span><i class="fas fa-drumstick-bite text-red-500"></i> ${plan.protein}g protein</span>` : ''}
                        ${plan.carbs ? `<span><i class="fas fa-bread-slice text-yellow-500"></i> ${plan.carbs}g carbs</span>` : ''}
                    </div>
                </div>
                <button onclick="deletePlan(${plan.id})" class="text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 px-3 py-1 rounded">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('') || '<div class="p-8 text-center text-gray-500">No meals logged for this day</div>';
}

function openDietModal() {
    document.getElementById('dietModal').classList.remove('hidden');
    document.getElementById('dietForm').reset();
}

function closeDietModal() {
    document.getElementById('dietModal').classList.add('hidden');
}

document.getElementById('dietForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    const response = await fetch('/api/diet.php?action=create', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    if (result.success) {
        closeDietModal();
        loadPlans();
        showToast('Meal plan added successfully', 'success');
    }
});

async function deletePlan(id) {
    if (!confirm('Delete this meal plan?')) return;
    const response = await fetch(`/api/diet.php?id=${id}`, {method: 'DELETE'});
    const result = await response.json();
    if (result.success) {
        loadPlans();
        showToast('Meal plan deleted', 'success');
    }
}

document.getElementById('filterDate').addEventListener('change', loadPlans);
document.getElementById('filterMeal').addEventListener('change', renderPlans);

loadPlans();
</script>

<?php include 'includes/footer.php'; ?>
