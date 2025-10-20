<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
requireLogin();

$db = Database::getInstance();
$userId = $auth->getUserId();

$pageTitle = 'Travel Journal';
include 'includes/header.php';

$journalEntries = $db->fetchAll("SELECT * FROM travel_journal WHERE user_id = ? ORDER BY entry_date DESC", [$userId]) ?: [];

$stats = [
    'total_entries' => count($journalEntries),
    'countries_visited' => $db->fetchColumn("SELECT COUNT(DISTINCT country) FROM travel_journal WHERE user_id = ?", [$userId]) ?? 0,
    'cities_visited' => $db->fetchColumn("SELECT COUNT(DISTINCT city) FROM travel_journal WHERE user_id = ?", [$userId]) ?? 0,
];
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-book-open text-primary"></i>
                Travel Journal
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Document your travel experiences and memories</p>
        </div>
        <button id="addEntryBtn" class="btn-primary">
            <i class="fas fa-plus mr-2"></i>
            New Entry
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Entries</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
                        <?php echo $stats['total_entries']; ?>
                    </h3>
                </div>
                <i class="fas fa-book text-blue-500 text-4xl"></i>
            </div>
        </div>

        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Countries Visited</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
                        <?php echo $stats['countries_visited']; ?>
                    </h3>
                </div>
                <i class="fas fa-flag text-green-500 text-4xl"></i>
            </div>
        </div>

        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Cities Visited</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
                        <?php echo $stats['cities_visited']; ?>
                    </h3>
                </div>
                <i class="fas fa-city text-purple-500 text-4xl"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <?php if (empty($journalEntries)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-plane-departure text-gray-400 text-6xl mb-4"></i>
                <p class="text-gray-500 text-lg">No journal entries yet. Start documenting your travels!</p>
            </div>
        <?php else: ?>
            <?php foreach ($journalEntries as $entry): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover-lift">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                            <?php echo htmlspecialchars($entry['title']); ?>
                        </h3>
                        <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                            <span><i class="fas fa-map-marker-alt mr-1"></i><?php echo htmlspecialchars($entry['city'] . ', ' . $entry['country']); ?></span>
                            <span><i class="fas fa-calendar mr-1"></i><?php echo date('M d, Y', strtotime($entry['entry_date'])); ?></span>
                            <?php if ($entry['rating']): ?>
                                <span>
                                    <i class="fas fa-star text-yellow-500 mr-1"></i>
                                    <?php echo $entry['rating']; ?>/5
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="editEntry(<?php echo $entry['id']; ?>)" class="text-primary hover:text-primary/80">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteEntry(<?php echo $entry['id']; ?>)" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <?php if ($entry['description']): ?>
                    <p class="text-gray-700 dark:text-gray-300 mb-4 whitespace-pre-wrap">
                        <?php echo htmlspecialchars($entry['description']); ?>
                    </p>
                <?php endif; ?>

                <?php if ($entry['highlights']): ?>
                    <div class="mb-4">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Highlights:</h4>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                            <?php echo htmlspecialchars($entry['highlights']); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($entry['expenses']): ?>
                    <div class="inline-block bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400 px-3 py-1 rounded-full text-sm">
                        <i class="fas fa-dollar-sign mr-1"></i>
                        Expenses: $<?php echo number_format($entry['expenses'], 2); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Entry Modal -->
<div id="entryModal" class="modal hidden">
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Travel Journal Entry</h3>
            <button class="modal-close text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="entryForm">
            <input type="hidden" id="entryId" value="">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Entry Title *</label>
                <input type="text" id="entryTitle" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="A Day in Paris">
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">City *</label>
                    <input type="text" id="entryCity" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="Paris">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Country *</label>
                    <input type="text" id="entryCountry" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="France">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date *</label>
                    <input type="date" id="entryDate" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rating</label>
                    <select id="entryRating" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                        <option value="">No rating</option>
                        <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                        <option value="4">⭐⭐⭐⭐ Great</option>
                        <option value="3">⭐⭐⭐ Good</option>
                        <option value="2">⭐⭐ Fair</option>
                        <option value="1">⭐ Poor</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                <textarea id="entryDescription" rows="4" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="Describe your experience..."></textarea>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Highlights</label>
                <textarea id="entryHighlights" rows="2" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="Visited Eiffel Tower, tried amazing croissants..."></textarea>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Expenses (Optional)</label>
                <input type="number" id="entryExpenses" step="0.01" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="0.00">
            </div>
            
            <div class="flex gap-2">
                <button type="submit" class="btn-primary flex-1">Save Entry</button>
                <button type="button" class="modal-close btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('addEntryBtn').addEventListener('click', () => {
    document.getElementById('entryForm').reset();
    document.getElementById('entryId').value = '';
    document.getElementById('entryDate').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('entryModal').classList.remove('hidden');
});

document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('entryModal').classList.add('hidden');
    });
});

document.getElementById('entryForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', document.getElementById('entryId').value ? 'update_entry' : 'add_entry');
    if (document.getElementById('entryId').value) {
        formData.append('id', document.getElementById('entryId').value);
    }
    formData.append('title', document.getElementById('entryTitle').value);
    formData.append('city', document.getElementById('entryCity').value);
    formData.append('country', document.getElementById('entryCountry').value);
    formData.append('entry_date', document.getElementById('entryDate').value);
    formData.append('rating', document.getElementById('entryRating').value);
    formData.append('description', document.getElementById('entryDescription').value);
    formData.append('highlights', document.getElementById('entryHighlights').value);
    formData.append('expenses', document.getElementById('entryExpenses').value);
    
    const response = await fetch('/api/travel.php', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
        showNotification('Entry saved successfully!', 'success');
        setTimeout(() => location.reload(), 1000);
    } else {
        showNotification('Failed to save entry', 'error');
    }
});

async function editEntry(id) {
    const response = await fetch(`/api/travel.php?action=get_entry&id=${id}`);
    const result = await response.json();
    
    if (result.success && result.entry) {
        const entry = result.entry;
        document.getElementById('entryId').value = entry.id;
        document.getElementById('entryTitle').value = entry.title;
        document.getElementById('entryCity').value = entry.city;
        document.getElementById('entryCountry').value = entry.country;
        document.getElementById('entryDate').value = entry.entry_date;
        document.getElementById('entryRating').value = entry.rating || '';
        document.getElementById('entryDescription').value = entry.description || '';
        document.getElementById('entryHighlights').value = entry.highlights || '';
        document.getElementById('entryExpenses').value = entry.expenses || '';
        document.getElementById('entryModal').classList.remove('hidden');
    }
}

async function deleteEntry(id) {
    if (!confirm('Are you sure you want to delete this entry?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_entry');
    formData.append('id', id);
    
    const response = await fetch('/api/travel.php', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
        showNotification('Entry deleted', 'success');
        setTimeout(() => location.reload(), 1000);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
