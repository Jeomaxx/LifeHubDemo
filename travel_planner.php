<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
requireLogin();

$pageTitle = 'Travel Planner';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i data-lucide="plane" class="text-primary"></i>
                Travel Planner & Journal
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Plan trips, track itineraries, and journal your travels</p>
        </div>
        <button onclick="alert('Add trip coming soon!')" class="btn-interactive bg-primary text-white px-6 py-3 rounded-lg">
            <i data-lucide="plus"></i> Add Trip
        </button>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">My Trips</h2>
        <div id="tripsList"></div>
    </div>
</div>

<script>
async function loadData() {
    const trips = await fetch('/api/travel.php?type=trips').then(r => r.json());
    if (trips.success) {
        document.getElementById('tripsList').innerHTML = trips.data.length > 0 
            ? trips.data.map(t => `<div class="card-hover p-4 mb-3 bg-gray-50 dark:bg-gray-700 rounded-lg"><h3 class="font-bold">${t.destination}</h3><p class="text-sm text-gray-600">${t.start_date} to ${t.end_date}</p></div>`).join('') 
            : '<p class="text-gray-500">No trips planned yet. Start planning your next adventure!</p>';
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

document.addEventListener('DOMContentLoaded', loadData);
</script>

<?php include 'includes/footer.php'; ?>
