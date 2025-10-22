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

async function loadDocuments() {
    try {
        const response = await fetch('/api/documents.php?action=list');
        const result = await response.json();
        
        if (result.success) {
            displayDocuments(result.documents || []);
            updateStats(result.documents || []);
        }
    } catch (error) {
        console.error('Error loading documents:', error);
    }
}

function displayDocuments(documents) {
    const container = document.getElementById('documentsList');
    
    if (documents.length === 0) {
        container.innerHTML = '<div class="text-center py-12 text-gray-500 dark:text-gray-400"><i class="fas fa-folder-open text-4xl mb-3"></i><p>No documents yet. Upload your first document to get started.</p></div>';
        return;
    }
    
    container.innerHTML = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">' + documents.map(doc => `
        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:shadow-md transition">
            <div class="flex items-start justify-between mb-2">
                <div class="flex items-center gap-2">
                    <i class="fas ${getFileIcon(doc.file_type)} text-2xl text-primary"></i>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">${doc.title || doc.document_name || 'Untitled'}</h3>
                        <p class="text-xs text-gray-500">${doc.category || 'Uncategorized'}</p>
                    </div>
                </div>
            </div>
            ${doc.description ? `<p class="text-sm text-gray-600 dark:text-gray-400 mb-2">${doc.description}</p>` : ''}
            ${doc.tags ? `<div class="flex gap-1 flex-wrap mb-2">${doc.tags.split(',').map(tag => `<span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-xs rounded">${tag.trim()}</span>`).join('')}</div>` : ''}
            <div class="flex items-center justify-between text-xs text-gray-500 mt-2">
                <span>${new Date(doc.created_at).toLocaleDateString()}</span>
                <span>${formatFileSize(doc.file_size)}</span>
            </div>
            <div class="flex gap-2 mt-3">
                <button onclick="viewDocument(${doc.id})" class="flex-1 px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">View</button>
                <button onclick="downloadDocument(${doc.id})" class="px-3 py-1 bg-gray-500 text-white rounded text-sm hover:bg-gray-600"><i class="fas fa-download"></i></button>
                <button onclick="deleteDocument(${doc.id})" class="px-3 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    `).join('') + '</div>';
}

function updateStats(documents) {
    document.getElementById('totalDocs').textContent = documents.length;
    const totalSize = documents.reduce((sum, doc) => sum + (doc.file_size || 0), 0);
    document.getElementById('storageUsed').textContent = formatFileSize(totalSize);
    const categories = new Set(documents.map(d => d.category).filter(c => c));
    document.getElementById('categoryCount').textContent = categories.size;
    const recentCount = documents.filter(d => {
        const created = new Date(d.created_at);
        const weekAgo = new Date();
        weekAgo.setDate(weekAgo.getDate() - 7);
        return created > weekAgo;
    }).length;
    document.getElementById('recentUploads').textContent = recentCount;
}

function getFileIcon(fileType) {
    if (!fileType) return 'fa-file';
    if (fileType.includes('pdf')) return 'fa-file-pdf';
    if (fileType.includes('word') || fileType.includes('document')) return 'fa-file-word';
    if (fileType.includes('excel') || fileType.includes('spreadsheet')) return 'fa-file-excel';
    if (fileType.includes('image')) return 'fa-file-image';
    if (fileType.includes('video')) return 'fa-file-video';
    return 'fa-file';
}

function formatFileSize(bytes) {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

async function viewDocument(id) {
    window.open(`/api/documents.php?action=view&id=${id}`, '_blank');
}

async function downloadDocument(id) {
    window.location.href = `/api/documents.php?action=download&id=${id}`;
}

async function deleteDocument(id) {
    if (!confirm('Are you sure you want to delete this document?')) return;
    
    try {
        const response = await fetch(`/api/documents.php?action=delete&id=${id}`, {
            method: 'DELETE',
            headers: {'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content}
        });
        const result = await response.json();
        
        if (result.success) {
            loadDocuments();
        } else {
            alert('Error: ' + (result.message || 'Failed to delete document'));
        }
    } catch (error) {
        console.error('Error deleting document:', error);
        alert('Failed to delete document');
    }
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
        closeUploadModal();
        loadDocuments();
        if (typeof showToast === 'function') {
            showToast('success', 'Success', 'Document uploaded successfully');
        }
    } else {
        alert('Error: ' + (result.message || 'Failed to upload document'));
    }
});

document.getElementById('searchDocs')?.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#documentsList > div > div').forEach(doc => {
        const text = doc.textContent.toLowerCase();
        doc.style.display = text.includes(query) ? '' : 'none';
    });
});

document.getElementById('filterCategory')?.addEventListener('change', function() {
    loadDocuments();
});

// Load documents when page loads
loadDocuments();
</script>

<?php include 'includes/footer.php'; ?>
