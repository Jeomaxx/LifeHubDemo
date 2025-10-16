<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Import / Export Data';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="fas fa-exchange-alt text-primary"></i>
            Import / Export Data
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Import and export your data across all modules</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Import Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h2 class="text-2xl font-bold dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-file-import text-green-600"></i>
                Import Data
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2 dark:text-gray-300">Select Module</label>
                    <select id="importModule" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="">-- Choose Module --</option>
                        <option value="gifts">Gifts</option>
                        <option value="bills">Bills</option>
                        <option value="tasks">Tasks</option>
                        <option value="finance">Finance</option>
                        <option value="goals">Goals</option>
                        <option value="habits">Habits</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 dark:text-gray-300">Upload CSV File</label>
                    <input type="file" id="importFile" accept=".csv" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Tip:</strong> Download the template first to see the correct format!
                    </p>
                </div>

                <button onclick="importData()" class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium">
                    <i class="fas fa-upload mr-2"></i>
                    Import Data
                </button>

                <div id="importResult" class="hidden mt-4"></div>
            </div>
        </div>

        <!-- Export Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h2 class="text-2xl font-bold dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-file-export text-blue-600"></i>
                Export Data
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2 dark:text-gray-300">Select Module</label>
                    <select id="exportModule" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="">-- Choose Module --</option>
                        <option value="gifts">Gifts</option>
                        <option value="bills">Bills</option>
                        <option value="tasks">Tasks</option>
                        <option value="finance">Finance</option>
                        <option value="gym">Gym Routines</option>
                        <option value="diet">Diet Plans</option>
                        <option value="water">Water Intake</option>
                        <option value="goals">Goals</option>
                        <option value="habits">Habits</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button onclick="downloadTemplate()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium">
                        <i class="fas fa-file-download mr-2"></i>
                        Download Template
                    </button>
                    <button onclick="exportData()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                        <i class="fas fa-download mr-2"></i>
                        Export Data
                    </button>
                </div>

                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Note:</strong> Export includes only your personal data for the selected module
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Instructions -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
        <h3 class="text-xl font-bold dark:text-white mb-4">How to Import Data</h3>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-300">
            <li>Download the template for your desired module</li>
            <li>Fill in your data following the template format (required fields are marked with *)</li>
            <li>Save the file as CSV</li>
            <li>Select the module and upload your file</li>
            <li>Review the import results and fix any errors</li>
        </ol>
    </div>
</div>

<script>
async function downloadTemplate() {
    const module = document.getElementById('exportModule').value;
    if (!module) {
        showToast('Please select a module', 'error');
        return;
    }
    
    window.location.href = `/api/universal_export.php?module=${module}&format=template`;
}

async function exportData() {
    const module = document.getElementById('exportModule').value;
    if (!module) {
        showToast('Please select a module', 'error');
        return;
    }
    
    window.location.href = `/api/universal_export.php?module=${module}&format=csv`;
}

async function importData() {
    const module = document.getElementById('importModule').value;
    const file = document.getElementById('importFile').files[0];
    
    if (!module) {
        showToast('Please select a module', 'error');
        return;
    }
    
    if (!file) {
        showToast('Please select a file to import', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('module', module);
    formData.append('file', file);
    
    try {
        const response = await fetch('/api/universal_import.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        const resultDiv = document.getElementById('importResult');
        resultDiv.classList.remove('hidden');
        
        if (data.success) {
            let html = `
                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                    <h4 class="font-bold text-green-800 dark:text-green-200 mb-2">
                        <i class="fas fa-check-circle mr-1"></i>
                        ${data.message}
                    </h4>
                    <p class="text-sm text-green-700 dark:text-green-300">
                        Imported: ${data.imported} | Skipped: ${data.skipped}
                    </p>
            `;
            
            if (data.errors && data.errors.length > 0) {
                html += '<div class="mt-3"><p class="text-sm font-semibold text-orange-800 dark:text-orange-200">Errors:</p><ul class="text-xs mt-1 space-y-1">';
                data.errors.forEach(error => {
                    html += `<li class="text-orange-700 dark:text-orange-300">• ${error}</li>`;
                });
                html += '</ul></div>';
            }
            
            html += '</div>';
            resultDiv.innerHTML = html;
            
            // Clear form
            document.getElementById('importFile').value = '';
        } else {
            resultDiv.innerHTML = `
                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                    <p class="text-red-800 dark:text-red-200">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        ${data.message}
                    </p>
                </div>
            `;
        }
    } catch (error) {
        showToast('Import failed: ' + error.message, 'error');
    }
}

function showToast(message, type = 'info') {
    // Use existing toast notification system
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg ${
        type === 'error' ? 'bg-red-600' : type === 'success' ? 'bg-green-600' : 'bg-blue-600'
    } text-white z-50`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

<?php include 'includes/footer.php'; ?>
