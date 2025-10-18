<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$symptoms = $db->fetchAll("SELECT * FROM symptoms WHERE user_id = ? ORDER BY name", [$userId]);

$pageTitle = 'Symptom Tracker';
$extraScripts = ['/assets/js/new-modules.js'];
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-heartbeat text-primary"></i>
                Symptom Tracker
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Track symptoms and identify patterns</p>
        </div>
        <div class="flex gap-2">
            <button onclick="showAddSymptomModal()" class="btn btn-secondary flex items-center gap-2">
                <i class="fas fa-plus"></i>
                New Symptom Type
            </button>
            <button onclick="showLogSymptomModal()" class="btn btn-primary flex items-center gap-2">
                <i class="fas fa-clipboard-list"></i>
                Log Symptom
            </button>
        </div>
    </div>

    <?php if (empty($symptoms)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-notes-medical text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No symptoms tracked yet. Add a symptom type to begin tracking!</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($symptoms as $symptom): 
                $recentLogs = $db->fetchAll(
                    "SELECT * FROM symptom_logs WHERE symptom_id = ? ORDER BY log_date DESC, log_time DESC LIMIT 5", 
                    [$symptom['id']]
                );
                $logCount = $db->fetchColumn("SELECT COUNT(*) FROM symptom_logs WHERE symptom_id = ?", [$symptom['id']]);
            ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white"><?php echo sanitize($symptom['name']); ?></h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo ucfirst($symptom['category']); ?></p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="editSymptom(<?php echo $symptom['id']; ?>)" class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteSymptom(<?php echo $symptom['id']; ?>)" class="text-red-600 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 mb-2">Total Occurrences</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $logCount; ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 mb-2">Recent Logs</p>
                        <?php if (empty($recentLogs)): ?>
                            <p class="text-sm text-gray-400 italic">No logs yet</p>
                        <?php else: ?>
                            <div class="space-y-2">
                                <?php foreach (array_slice($recentLogs, 0, 3) as $log): ?>
                                <div class="text-sm border-l-4 border-orange-500 pl-2">
                                    <p class="font-semibold">Severity: <?php echo $log['severity']; ?>/10</p>
                                    <p class="text-gray-600 dark:text-gray-400 text-xs"><?php echo date('M d, Y', strtotime($log['log_date'])); ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex gap-2">
                        <button onclick="logSymptomForType(<?php echo $symptom['id']; ?>)" class="flex-1 px-3 py-2 bg-primary text-white rounded hover:bg-blue-600">
                            Log Now
                        </button>
                        <button onclick="viewSymptomHistory(<?php echo $symptom['id']; ?>)" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-100">
                            <i class="fas fa-history"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="addSymptomModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-md">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold">Add Symptom Type</h2>
        </div>
        <form id="symptomForm" class="p-6 space-y-4">
            <input type="hidden" id="symptomId">
            
            <div>
                <label class="block text-sm font-medium mb-1">Symptom Name *</label>
                <input type="text" id="symptomName" required class="w-full px-3 py-2 border rounded-lg" placeholder="e.g., Headache, Nausea">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Category *</label>
                <select id="symptomCategory" required class="w-full px-3 py-2 border rounded-lg">
                    <option value="pain">Pain</option>
                    <option value="digestive">Digestive</option>
                    <option value="respiratory">Respiratory</option>
                    <option value="mental">Mental/Emotional</option>
                    <option value="skin">Skin</option>
                    <option value="neurological">Neurological</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeAddSymptomModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg">Save</button>
            </div>
        </form>
    </div>
</div>

<div id="logSymptomModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-lg">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold">Log Symptom</h2>
        </div>
        <form id="logSymptomForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Symptom *</label>
                <select id="logSymptomId" required class="w-full px-3 py-2 border rounded-lg">
                    <option value="">Select a symptom</option>
                    <?php foreach ($symptoms as $symptom): ?>
                    <option value="<?php echo $symptom['id']; ?>"><?php echo sanitize($symptom['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Date *</label>
                    <input type="date" id="logDate" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Time</label>
                    <input type="time" id="logTime" value="<?php echo date('H:i'); ?>" class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Severity (1-10) *</label>
                <input type="range" id="severity" min="1" max="10" value="5" required class="w-full">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Mild (1)</span>
                    <span id="severityValue">5</span>
                    <span>Severe (10)</span>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Duration (minutes)</label>
                <input type="number" id="duration" class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Possible Triggers</label>
                <textarea id="triggers" rows="2" class="w-full px-3 py-2 border rounded-lg" placeholder="Food, stress, weather, etc."></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Notes</label>
                <textarea id="symptomNotes" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
            </div>
            
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeLogSymptomModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg">Save Log</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('severity').addEventListener('input', (e) => {
    document.getElementById('severityValue').textContent = e.target.value;
});
</script>

<?php include 'includes/footer.php'; ?>
