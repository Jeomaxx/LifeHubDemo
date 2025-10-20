document.addEventListener('DOMContentLoaded', function() {
    loadUserProfile();
    loadMealPlans();
    initGenerateButton();
});

function loadUserProfile() {
    const saved = localStorage.getItem('nutritionProfile');
    if (saved) {
        const profile = JSON.parse(saved);
        populateProfileForm(profile);
    }
}

function populateProfileForm(profile) {
    if (profile.age) document.getElementById('age').value = profile.age;
    if (profile.weight) document.getElementById('weight').value = profile.weight;
    if (profile.height) document.getElementById('height').value = profile.height;
    if (profile.activity_level) document.getElementById('activityLevel').value = profile.activity_level;
    if (profile.goal) document.getElementById('goal').value = profile.goal;
    if (profile.dietary_restrictions) document.getElementById('dietaryRestrictions').value = profile.dietary_restrictions;
    if (profile.allergies) document.getElementById('allergies').value = profile.allergies;
}

function initGenerateButton() {
    const btn = document.getElementById('generateMealPlanBtn');
    if (btn) {
        btn.addEventListener('click', generateMealPlan);
    }
}

async function saveProfile() {
    const profile = {
        age: document.getElementById('age').value,
        weight: document.getElementById('weight').value,
        height: document.getElementById('height').value,
        activity_level: document.getElementById('activityLevel').value,
        goal: document.getElementById('goal').value,
        dietary_restrictions: document.getElementById('dietaryRestrictions').value,
        allergies: document.getElementById('allergies').value
    };
    
    if (!profile.age || !profile.weight || !profile.height) {
        showToast('error', 'Error', 'Please fill in all required fields');
        return;
    }
    
    localStorage.setItem('nutritionProfile', JSON.stringify(profile));
    
    try {
        const response = await fetch('/api/nutrition_ai.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'save_profile',
                ...profile
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Profile saved successfully');
        } else {
            showToast('error', 'Error', result.message || 'Failed to save profile');
        }
    } catch (error) {
        console.error('Error saving profile:', error);
        showToast('error', 'Error', 'Failed to save profile');
    }
}

async function generateMealPlan() {
    const profile = {
        age: document.getElementById('age').value,
        weight: document.getElementById('weight').value,
        height: document.getElementById('height').value,
        activity_level: document.getElementById('activityLevel').value,
        goal: document.getElementById('goal').value,
        dietary_restrictions: document.getElementById('dietaryRestrictions').value,
        allergies: document.getElementById('allergies').value
    };
    
    if (!profile.age || !profile.weight || !profile.height) {
        showToast('error', 'Error', 'Please complete your profile first');
        return;
    }
    
    try {
        showToast('info', 'Generating', 'Creating your personalized meal plan...');
        
        const response = await fetch('/api/nutrition_ai.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'generate_meal_plan',
                ...profile
            })
        });
        
        const result = await response.json();
        if (result.success) {
            displayMealPlan(result.meal_plan);
            showToast('success', 'Success', 'Meal plan generated successfully!');
        } else {
            showToast('error', 'Error', result.message || 'Failed to generate meal plan');
        }
    } catch (error) {
        console.error('Error generating meal plan:', error);
        showToast('error', 'Error', 'Failed to generate meal plan');
    }
}

function displayMealPlan(mealPlan) {
    const container = document.getElementById('mealPlanContainer');
    if (!container) return;
    
    container.innerHTML = `
        <div class="meal-plan-result">
            <h3 class="text-xl font-bold mb-4">Your Personalized Meal Plan</h3>
            ${mealPlan.daily_calories ? `<p class="mb-2"><strong>Daily Calories:</strong> ${mealPlan.daily_calories} kcal</p>` : ''}
            ${mealPlan.macros ? `
                <div class="macros mb-4">
                    <h4 class="font-semibold mb-2">Macronutrient Breakdown:</h4>
                    <p>Protein: ${mealPlan.macros.protein}g | Carbs: ${mealPlan.macros.carbs}g | Fat: ${mealPlan.macros.fat}g</p>
                </div>
            ` : ''}
            ${mealPlan.meals ? renderMeals(mealPlan.meals) : ''}
            ${mealPlan.tips ? `
                <div class="tips mt-4">
                    <h4 class="font-semibold mb-2">Nutrition Tips:</h4>
                    <ul class="list-disc pl-5">
                        ${mealPlan.tips.map(tip => `<li>${escapeHtml(tip)}</li>`).join('')}
                    </ul>
                </div>
            ` : ''}
        </div>
    `;
}

function renderMeals(meals) {
    return `
        <div class="meals-container grid grid-cols-1 md:grid-cols-2 gap-4 my-4">
            ${Object.entries(meals).map(([mealType, meal]) => `
                <div class="meal-card bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                    <h4 class="font-semibold text-lg mb-2 capitalize">${mealType}</h4>
                    <p class="text-gray-600 dark:text-gray-400">${escapeHtml(meal)}</p>
                </div>
            `).join('')}
        </div>
    `;
}

async function loadMealPlans() {
    try {
        const response = await fetch('/api/nutrition_ai.php?action=get_meal_plans');
        const result = await response.json();
        if (result.success && result.meal_plans) {
            displayMealPlanHistory(result.meal_plans);
        }
    } catch (error) {
        console.error('Error loading meal plans:', error);
    }
}

function displayMealPlanHistory(mealPlans) {
    const container = document.getElementById('mealPlanHistory');
    if (!container || mealPlans.length === 0) return;
    
    container.innerHTML = `
        <h3 class="text-xl font-bold mb-4">Previous Meal Plans</h3>
        <div class="space-y-2">
            ${mealPlans.map(plan => `
                <div class="plan-history-item p-3 bg-gray-100 dark:bg-gray-700 rounded">
                    <div class="flex justify-between items-center">
                        <span>${formatDate(plan.created_at)}</span>
                        <button onclick="viewMealPlan(${plan.id})" class="btn btn-sm btn-primary">View</button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

async function viewMealPlan(id) {
    try {
        const response = await fetch(`/api/nutrition_ai.php?action=get_meal_plan&id=${id}`);
        const result = await response.json();
        if (result.success && result.meal_plan) {
            displayMealPlan(JSON.parse(result.meal_plan.plan_data));
        }
    } catch (error) {
        console.error('Error loading meal plan:', error);
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
