<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Documents Hub';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-folder-open text-primary"></i>
                Documents Hub
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Organize and manage your documents</p>
        </div>
        <button onclick="openUploadModal()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-upload"></i>
            <span>Upload Document</span>
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Documents</p>
                    <p id="totalDocs" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Storage Used</p>
                    <p id="storageUsed" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0 MB</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hdd text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Categories</p>
                    <p id="categoryCount" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tags text-green-600 dark:text-green-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Recent Uploads</p>
                    <p id="recentUploads" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600 dark:text-orange-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Grid -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Your Documents</h2>
            <div class="flex gap-2">
                <input type="text" placeholder="Search documents..." class="px-4 py-2 border border-gray-300 rounded-lg" id="searchDocs">
                <select class="px-4 py-2 border border-gray-300 rounded-lg" id="filterCategory">
                    <option value="">All Categories</option>
                    <option value="Personal">Personal</option>
                    <option value="Financial">Financial</option>
                    <option value="Legal">Legal</option>
                    <option value="Medical">Medical</option>
                </select>
            </div>
        </div>
        <div id="documentsList" class="p-6">
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <i class="fas fa-folder-open text-4xl mb-3"></i>
                <p>No documents yet. Upload your first document to get started.</p>
            </div>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div id="uploadModal" class="modal hidden">
    <div class="modal-content bg-white dark:bg-gray-800 rounded-lg w-full max-w-md mx-4">
        <div class="modal-header p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold">Upload Document</h3>
            <button onclick="closeUploadModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form id="uploadForm" class="modal-body p-6" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Document Name</label>
                <input type="text" id="docName" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                <select id="docCategory" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="Personal">Personal</option>
                    <option value="Financial">Financial</option>
                    <option value="Legal">Legal</option>
                    <option value="Medical">Medical</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">File</label>
                <input type="file" id="docFile" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tags (comma separated)</label>
                <input type="text" id="docTags" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="e.g. important, 2024, tax">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                <textarea id="docDescription" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-primary hover:bg-blue-600 text-white px-6 py-2 rounded-lg">Upload</button>
                <button type="button" onclick="closeUploadModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUploadModal() {
    const modal = document.getElementById('uploadModal');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
}

function closeUploadModal() {
    const modal = document.getElementById('uploadModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    document.getElementById('uploadForm').reset();
}

document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('document_name', document.getElementById('docName').value);
    formData.append('category', document.getElementById('docCategory').value);
    formData.append('file', document.getElementById('docFile').files[0]);
    formData.append('tags', document.getElementById('docTags').value);
    formData.append('description', document.getElementById('docDescription').value);
    
    const response = await fetch('/api/documents.php?action=create', {
        method: 'POST',
        headers: {'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content},
        body: formData
    });
    
    const result = await response.json();
    if (result.success) {
        alert('Document uploaded successfully');
        window.location.reload();
    } else {
        alert('Error: ' + (result.message || 'Failed to upload document'));
    }
});
</script>

<?php include 'includes/footer.php'; ?>
