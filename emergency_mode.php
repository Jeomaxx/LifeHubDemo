<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Emergency Mode';
include 'includes/header.php';

// Get emergency contacts
$emergencyContacts = $db->fetchAll("SELECT * FROM emergency_contacts WHERE user_id = ? ORDER BY priority", [$userId]) ?: [];

// Get user's health profile
$healthProfile = $db->fetchOne("SELECT * FROM health_profiles WHERE user_id = ?", [$userId]);
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
                Emergency Mode
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage emergency contacts and health data sharing</p>
        </div>
    </div>

    <!-- Emergency Alert Banner -->
    <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-800 rounded-lg p-6 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-red-600 rounded-full flex items-center justify-center text-white flex-shrink-0">
                <i class="fas fa-ambulance text-3xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-xl font-bold text-red-900 dark:text-red-100 mb-2">Emergency Activation</h3>
                <p class="text-sm text-red-700 dark:text-red-300 mb-4">
                    In case of emergency, your pre-approved contacts will receive your location and health information
                </p>
                <button onclick="activateEmergency()" class="btn bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-bold text-lg">
                    <i class="fas fa-exclamation-circle"></i> ACTIVATE EMERGENCY MODE
                </button>
            </div>
        </div>
    </div>

    <!-- Emergency Contacts -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-user-shield"></i> Emergency Contacts
            </h3>
            <button onclick="addEmergencyContact()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus"></i> Add Contact
            </button>
        </div>
        
        <div class="space-y-3">
            <?php if (empty($emergencyContacts)): ?>
            <div class="text-center py-8">
                <i class="fas fa-user-plus text-4xl text-gray-400 mb-3"></i>
                <p class="text-gray-500 dark:text-gray-400">No emergency contacts added</p>
                <button onclick="addEmergencyContact()" class="mt-4 text-primary hover:underline">Add your first contact</button>
            </div>
            <?php else: ?>
            <?php foreach ($emergencyContacts as $contact): ?>
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-red-600 dark:text-red-400 text-xl"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($contact['name']); ?>
                            <?php if ($contact['priority'] == 1): ?>
                            <span class="ml-2 px-2 py-1 text-xs rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                Primary
                            </span>
                            <?php endif; ?>
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($contact['phone']); ?>
                            <?php if ($contact['relationship']): ?>
                            • <?php echo htmlspecialchars($contact['relationship']); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="testNotify(<?php echo $contact['id']; ?>)" class="text-blue-600 hover:text-blue-700">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                    <button onclick="removeContact(<?php echo $contact['id']; ?>)" class="text-red-600 hover:text-red-700">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Health Information Sharing -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-heartbeat"></i> Shared Health Information
        </h3>
        
        <div class="space-y-4">
            <div class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <i class="fas fa-info-circle text-blue-600 text-xl mt-1"></i>
                <div class="flex-1">
                    <p class="font-semibold text-gray-900 dark:text-white mb-1">What will be shared?</p>
                    <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                        <li>✓ Current location (GPS coordinates)</li>
                        <li>✓ Blood type, allergies, and medical conditions</li>
                        <li>✓ Current medications</li>
                        <li>✓ Emergency contact list</li>
                        <li>✓ Last health check data</li>
                    </ul>
                </div>
            </div>
            
            <form id="healthProfileForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Blood Type</label>
                        <select name="blood_type" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                            <option value="">Select...</option>
                            <option value="A+" <?php echo ($healthProfile['blood_type'] ?? '') == 'A+' ? 'selected' : ''; ?>>A+</option>
                            <option value="A-" <?php echo ($healthProfile['blood_type'] ?? '') == 'A-' ? 'selected' : ''; ?>>A-</option>
                            <option value="B+" <?php echo ($healthProfile['blood_type'] ?? '') == 'B+' ? 'selected' : ''; ?>>B+</option>
                            <option value="B-" <?php echo ($healthProfile['blood_type'] ?? '') == 'B-' ? 'selected' : ''; ?>>B-</option>
                            <option value="AB+" <?php echo ($healthProfile['blood_type'] ?? '') == 'AB+' ? 'selected' : ''; ?>>AB+</option>
                            <option value="AB-" <?php echo ($healthProfile['blood_type'] ?? '') == 'AB-' ? 'selected' : ''; ?>>AB-</option>
                            <option value="O+" <?php echo ($healthProfile['blood_type'] ?? '') == 'O+' ? 'selected' : ''; ?>>O+</option>
                            <option value="O-" <?php echo ($healthProfile['blood_type'] ?? '') == 'O-' ? 'selected' : ''; ?>>O-</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Emergency Medical ID</label>
                        <input type="text" name="medical_id" value="<?php echo htmlspecialchars($healthProfile['medical_id'] ?? ''); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Allergies</label>
                    <textarea name="allergies" rows="2" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($healthProfile['allergies'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Medical Conditions</label>
                    <textarea name="conditions" rows="2" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($healthProfile['conditions'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Medications</label>
                    <textarea name="medications" rows="2" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($healthProfile['medications'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-save"></i> Save Health Profile
                </button>
            </form>
        </div>
    </div>

    <!-- Emergency Log -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-history"></i> Emergency Activation Log
        </h3>
        <div id="emergencyLog">
            <p class="text-gray-500 dark:text-gray-400 text-center py-4">No emergency activations on record</p>
        </div>
    </div>
</div>

<script src="/assets/js/emergency-mode.js"></script>

<?php include 'includes/footer.php'; ?>
