<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Receipt Scanner';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-receipt text-primary"></i>
                Receipt Scanner
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Scan receipts and bills with OCR technology</p>
        </div>
    </div>

    <!-- Scanner Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-camera"></i> Scan Receipt
        </h3>
        
        <!-- Upload or Camera -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center cursor-pointer hover:border-primary transition-colors" onclick="document.getElementById('receiptFile').click()">
                <i class="fas fa-upload text-4xl text-gray-400 dark:text-gray-500 mb-3"></i>
                <p class="text-gray-700 dark:text-gray-300 font-medium">Upload Receipt Image</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Click to browse files</p>
                <input type="file" id="receiptFile" accept="image/*" class="hidden" onchange="handleFileUpload(event)">
            </div>
            
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center cursor-pointer hover:border-primary transition-colors" onclick="openCamera()">
                <i class="fas fa-camera text-4xl text-gray-400 dark:text-gray-500 mb-3"></i>
                <p class="text-gray-700 dark:text-gray-300 font-medium">Take Photo</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Use device camera</p>
            </div>
        </div>

        <!-- Image Preview -->
        <div id="imagePreview" class="hidden mb-6">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Receipt Image</h4>
            <img id="previewImg" src="" alt="Receipt" class="max-w-full h-auto rounded-lg shadow-md">
            <button onclick="scanReceipt()" class="mt-4 btn bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-lg flex items-center gap-2">
                <i class="fas fa-search"></i>
                <span>Scan with OCR</span>
            </button>
        </div>

        <!-- Loading -->
        <div id="scanningLoader" class="hidden text-center py-8">
            <i class="fas fa-spinner fa-spin text-4xl text-primary mb-3"></i>
            <p class="text-gray-700 dark:text-gray-300">Scanning receipt...</p>
        </div>

        <!-- Results -->
        <div id="scanResults" class="hidden">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Extracted Information</h4>
            
            <form id="expenseForm" onsubmit="saveExpense(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount</label>
                        <input type="number" id="amount" step="0.01" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                        <input type="date" id="date" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                        <select id="category" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                            <option value="Groceries">Groceries</option>
                            <option value="Dining">Dining & Restaurants</option>
                            <option value="Transportation">Transportation</option>
                            <option value="Shopping">Shopping</option>
                            <option value="Utilities">Utilities</option>
                            <option value="Healthcare">Healthcare</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Merchant</label>
                        <input type="text" id="merchant" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                    <textarea id="description" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white"></textarea>
                </div>
                
                <button type="submit" class="mt-4 btn bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    <span>Save Expense</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Recent Scans -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-history"></i> Recent Scans
        </h3>
        <div id="recentScans" class="space-y-3">
            <p class="text-gray-500 dark:text-gray-400 text-center py-4">No scans yet</p>
        </div>
    </div>
</div>

<!-- Camera Modal -->
<div id="cameraModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-2xl w-full m-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Take Photo</h3>
            <button onclick="closeCamera()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <video id="cameraVideo" autoplay class="w-full rounded-lg mb-4"></video>
        <button onclick="capturePhoto()" class="w-full btn bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-lg">
            <i class="fas fa-camera"></i> Capture
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@4/dist/tesseract.min.js"></script>
<script src="/assets/js/receipt-scanner.js"></script>

<?php include 'includes/footer.php'; ?>
