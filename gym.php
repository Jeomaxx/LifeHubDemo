<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$auth = new Auth();
$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Gym Routines';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-dumbbell text-primary"></i>
                <?php echo t('Gym Routines'); ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo t('Track your workout routines and progress'); ?></p>
        </div>
        <button onclick="openRoutineModal()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i>
            <span><?php echo t('Add Routine'); ?></span>
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Total Workouts</p>
                    <p id="totalWorkouts" class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                </div>
                <i class="fas fa-dumbbell text-3xl text-blue-500"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">This Week</p>
                    <p id="weekWorkouts" class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                </div>
                <i class="fas fa-calendar-week text-3xl text-green-500"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Total Duration</p>
                    <p id="totalDuration" class="text-2xl font-bold text-gray-900 dark:text-white">0h</p>
                </div>
                <i class="fas fa-clock text-3xl text-orange-500"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Avg Per Week</p>
                    <p id="avgPerWeek" class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                </div>
                <i class="fas fa-chart-line text-3xl text-purple-500"></i>
            </div>
        </div>
    </div>

    <!-- Routines List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="p-4 border-b dark:border-gray-700">
            <div class="flex gap-4">
                <input type="text" id="searchRoutines" placeholder="Search routines..." 
                    class="flex-1 px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <select id="filterMuscle" class="px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    <option value="">All Muscle Groups</option>
                    <option value="chest">Chest</option>
                    <option value="back">Back</option>
                    <option value="legs">Legs</option>
                    <option value="shoulders">Shoulders</option>
                    <option value="arms">Arms</option>
                    <option value="core">Core</option>
                </select>
            </div>
        </div>
        <div id="routinesContainer" class="divide-y dark:divide-gray-700"></div>
    </div>
</div>

<!-- Routine Modal -->
<div id="routineModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
        <h2 class="text-2xl font-bold mb-4 dark:text-white">Add Gym Routine</h2>
        <form id="routineForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Routine Name*</label>
                    <input type="text" name="routine_name" required class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Target Muscle*</label>
                    <select name="target_muscle" required class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="">Select muscle group</option>
                        <option value="chest">Chest</option>
                        <option value="back">Back</option>
                        <option value="legs">Legs</option>
                        <option value="shoulders">Shoulders</option>
                        <option value="arms">Arms</option>
                        <option value="core">Core</option>
                        <option value="full_body">Full Body</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Sets</label>
                    <input type="number" name="sets" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Reps</label>
                    <input type="number" name="reps" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Duration (min)</label>
                    <input type="number" name="duration_minutes" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Notes</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeRoutineModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg dark:text-gray-400 dark:hover:bg-gray-700">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">Save Routine</button>
            </div>
        </form>
    </div>
</div>

<script>
let routines = [];

async function loadRoutines() {
    const response = await fetch('/api/gym.php?action=list');
    const data = await response.json();
    if (data.success) {
        routines = data.routines;
        updateStats(data.routines);
        renderRoutines();
    }
}

function updateStats(routines) {
    const total = routines.length;
    const weekStart = new Date();
    weekStart.setDate(weekStart.getDate() - 7);
    const weekRoutines = routines.filter(r => new Date(r.created_at) >= weekStart).length;
    const totalMinutes = routines.reduce((sum, r) => sum + (parseInt(r.duration_minutes) || 0), 0);
    const hours = Math.floor(totalMinutes / 60);
    
    document.getElementById('totalWorkouts').textContent = total;
    document.getElementById('weekWorkouts').textContent = weekRoutines;
    document.getElementById('totalDuration').textContent = hours + 'h';
    document.getElementById('avgPerWeek').textContent = Math.round(weekRoutines);
}

function renderRoutines() {
    const container = document.getElementById('routinesContainer');
    const search = document.getElementById('searchRoutines').value.toLowerCase();
    const muscle = document.getElementById('filterMuscle').value;
    
    const filtered = routines.filter(r => {
        const matchSearch = !search || r.routine_name.toLowerCase().includes(search);
        const matchMuscle = !muscle || r.target_muscle === muscle;
        return matchSearch && matchMuscle;
    });
    
    container.innerHTML = filtered.map(routine => `
        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <h3 class="font-semibold dark:text-white">${routine.routine_name}</h3>
                    <div class="flex gap-4 mt-2 text-sm text-gray-600 dark:text-gray-400">
                        <span><i class="fas fa-bullseye text-primary"></i> ${routine.target_muscle || 'N/A'}</span>
                        ${routine.sets ? `<span><i class="fas fa-repeat"></i> ${routine.sets} sets</span>` : ''}
                        ${routine.reps ? `<span><i class="fas fa-hashtag"></i> ${routine.reps} reps</span>` : ''}
                        ${routine.duration_minutes ? `<span><i class="fas fa-clock"></i> ${routine.duration_minutes} min</span>` : ''}
                    </div>
                    ${routine.notes ? `<p class="text-sm text-gray-500 dark:text-gray-500 mt-2">${routine.notes}</p>` : ''}
                </div>
                <button onclick="deleteRoutine(${routine.id})" class="text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 px-3 py-1 rounded">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('') || '<div class="p-8 text-center text-gray-500">No routines found</div>';
}

function openRoutineModal() {
    document.getElementById('routineModal').classList.remove('hidden');
    document.getElementById('routineForm').reset();
}

function closeRoutineModal() {
    document.getElementById('routineModal').classList.add('hidden');
}

document.getElementById('routineForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    const response = await fetch('/api/gym.php?action=create', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    if (result.success) {
        closeRoutineModal();
        loadRoutines();
        showToast('Routine added successfully', 'success');
    }
});

async function deleteRoutine(id) {
    if (!confirm('Delete this routine?')) return;
    const response = await fetch(`/api/gym.php?id=${id}`, {method: 'DELETE'});
    const result = await response.json();
    if (result.success) {
        loadRoutines();
        showToast('Routine deleted', 'success');
    }
}

document.getElementById('searchRoutines').addEventListener('input', renderRoutines);
document.getElementById('filterMuscle').addEventListener('change', renderRoutines);

loadRoutines();
</script>

<?php include 'includes/footer.php'; ?>
