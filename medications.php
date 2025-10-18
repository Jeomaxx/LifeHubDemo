<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$medications = $db->fetchAll("SELECT * FROM medications WHERE user_id = ? AND is_active = TRUE ORDER BY name", [$userId]);

$pageTitle = 'Medication & Supplement Tracker';
$extraScripts = ['/assets/js/new-modules.js'];
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-pills text-primary"></i>
                Medication & Supplement Tracker
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Track medications, supplements, and dosages</p>
        </div>
        <button onclick="showAddMedicationModal()" class="btn btn-primary flex items-center gap-2">
            <i class="fas fa-plus"></i>
            Add Medication
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Active Medications</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo count(array_filter($medications, fn($m) => $m['medication_type'] == 'medication')); ?></h3>
                </div>
                <i class="fas fa-capsules text-3xl text-blue-500"></i>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Supplements</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo count(array_filter($medications, fn($m) => $m['medication_type'] == 'supplement')); ?></h3>
                </div>
                <i class="fas fa-tablets text-3xl text-green-500"></i>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Due Today</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white" id="dueToday">0</h3>
                </div>
                <i class="fas fa-clock text-3xl text-orange-500"></i>
            </div>
        </div>
    </div>

    <?php if (empty($medications)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-pills text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No medications tracked yet. Add your first medication to get started!</p>
        </div>
    <?php else: ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Your Medications</h2>
                <div class="space-y-4">
                    <?php foreach ($medications as $med): ?>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-bold text-lg text-gray-900 dark:text-white"><?php echo sanitize($med['name']); ?></h3>
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $med['medication_type'] == 'medication' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo ucfirst($med['medication_type']); ?>
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo sanitize($med['dosage']); ?> - <?php echo sanitize($med['frequency']); ?></p>
                                <?php if ($med['purpose']): ?>
                                <p class="text-sm text-gray-500 mt-1"><?php echo sanitize($med['purpose']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="logIntake(<?php echo $med['id']; ?>)" class="text-green-600 hover:text-green-700" title="Log intake">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                <button onclick="editMedication(<?php echo $med['id']; ?>)" class="text-blue-600 hover:text-blue-700">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteMedication(<?php echo $med['id']; ?>)" class="text-red-600 hover:text-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Schedule</p>
                                <p class="font-semibold"><?php echo sanitize($med['time_of_day'] ?? 'Anytime'); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Current Qty</p>
                                <p class="font-semibold"><?php echo $med['current_quantity'] ?? 'N/A'; ?></p>
                            </div>
                            <?php if ($med['prescribing_doctor']): ?>
                            <div>
                                <p class="text-gray-500">Prescribed by</p>
                                <p class="font-semibold"><?php echo sanitize($med['prescribing_doctor']); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if ($med['start_date']): ?>
                            <div>
                                <p class="text-gray-500">Started</p>
                                <p class="font-semibold"><?php echo date('M d, Y', strtotime($med['start_date'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($med['current_quantity'] && $med['refill_reminder_quantity'] && $med['current_quantity'] <= $med['refill_reminder_quantity']): ?>
                        <div class="mt-3 p-2 bg-orange-100 dark:bg-orange-900 rounded text-orange-800 dark:text-orange-200 text-sm">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Low supply - Consider refilling soon
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div id="addMedicationModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold">Add Medication</h2>
        </div>
        <form id="medicationForm" class="p-6 space-y-4">
            <input type="hidden" id="medId">
            
            <div>
                <label class="block text-sm font-medium mb-1">Medication Name *</label>
                <input type="text" id="medName" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Type *</label>
                <select id="medType" required class="w-full px-3 py-2 border rounded-lg">
                    <option value="medication">Prescription Medication</option>
                    <option value="supplement">Supplement/Vitamin</option>
                </select>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Dosage *</label>
                    <input type="text" id="dosage" required class="w-full px-3 py-2 border rounded-lg" placeholder="e.g., 10mg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Frequency *</label>
                    <select id="frequency" required class="w-full px-3 py-2 border rounded-lg">
                        <option value="once_daily">Once Daily</option>
                        <option value="twice_daily">Twice Daily</option>
                        <option value="three_times_daily">Three Times Daily</option>
                        <option value="as_needed">As Needed</option>
                        <option value="weekly">Weekly</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Time of Day</label>
                <select id="timeOfDay" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">Anytime</option>
                    <option value="morning">Morning</option>
                    <option value="afternoon">Afternoon</option>
                    <option value="evening">Evening</option>
                    <option value="bedtime">Bedtime</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Purpose/Condition</label>
                <input type="text" id="purpose" class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Current Quantity</label>
                    <input type="number" id="currentQuantity" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Refill Alert At</label>
                    <input type="number" id="refillReminder" class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Prescribing Doctor</label>
                <input type="text" id="doctor" class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeAddMedicationModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg">Save</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
