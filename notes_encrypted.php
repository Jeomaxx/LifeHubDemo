<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$userId = $auth->getUserId();
$pageTitle = 'Encrypted Notes';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-lock"></i> <?= $pageTitle ?></h1>
            <button class="btn btn-primary" onclick="showNoteModal()">
                <i class="fas fa-plus"></i> New Encrypted Note
            </button>
        </div>
        
        <div class="info-card">
            <i class="fas fa-shield-alt"></i>
            <div>
                <strong>End-to-End Encryption</strong>
                <p>Your notes are encrypted with AES-256-GCM encryption before being stored. Only you can decrypt and read them.</p>
            </div>
        </div>
        
        <div class="notes-grid" id="notesGrid">
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i> Loading encrypted notes...
            </div>
        </div>
    </div>
    
    <!-- Note Modal -->
    <div id="noteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">New Encrypted Note</h2>
                <span class="close" onclick="closeNoteModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="noteForm" onsubmit="saveNote(event)">
                    <input type="hidden" id="noteId">
                    
                    <div class="form-group">
                        <label for="noteTitle">
                            <i class="fas fa-heading"></i> Title
                        </label>
                        <input type="text" id="noteTitle" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="noteContent">
                            <i class="fas fa-file-alt"></i> Content
                        </label>
                        <textarea id="noteContent" rows="10" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="notePassword">
                            <i class="fas fa-key"></i> Encryption Password
                        </label>
                        <input type="password" id="notePassword" required minlength="6" placeholder="Enter a strong password to encrypt this note">
                        <small>You'll need this password to decrypt and view the note later</small>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeNoteModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Encrypted
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Decrypt Modal -->
    <div id="decryptModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Decrypt Note</h2>
                <span class="close" onclick="closeDecryptModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="decryptPassword">
                        <i class="fas fa-key"></i> Enter Decryption Password
                    </label>
                    <input type="password" id="decryptPassword" placeholder="Enter the password you used to encrypt">
                </div>
                <div id="decryptError" class="alert alert-error" style="display: none;"></div>
                <div id="decryptedContent" style="display: none;">
                    <h3 id="decryptedTitle"></h3>
                    <div id="decryptedText" class="note-content"></div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeDecryptModal()">Close</button>
                    <button type="button" class="btn btn-primary" id="decryptBtn" onclick="decryptNote()">
                        <i class="fas fa-unlock"></i> Decrypt
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let currentNoteId = null;
        
        async function loadNotes() {
            try {
                const response = await fetch('/api/notes.php?action=list');
                const data = await response.json();
                
                const grid = document.getElementById('notesGrid');
                
                if (data.success && data.notes.length > 0) {
                    grid.innerHTML = data.notes.map(note => `
                        <div class="note-card encrypted">
                            <div class="note-header">
                                <h3><i class="fas fa-lock"></i> ${escapeHtml(note.title)}</h3>
                                <div class="note-actions">
                                    <button class="btn-icon" onclick="viewNote(${note.id})" title="Decrypt & View">
                                        <i class="fas fa-unlock-alt"></i>
                                    </button>
                                    <button class="btn-icon" onclick="deleteNote(${note.id})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="note-meta">
                                <span><i class="fas fa-calendar"></i> ${formatDate(note.created_at)}</span>
                                <span class="encrypted-badge"><i class="fas fa-shield-alt"></i> Encrypted</span>
                            </div>
                        </div>
                    `).join('');
                } else {
                    grid.innerHTML = '<div class="empty-state"><i class="fas fa-lock"></i><p>No encrypted notes yet. Create your first secure note!</p></div>';
                }
            } catch (error) {
                console.error('Error loading notes:', error);
                showNotification('Failed to load notes', 'error');
            }
        }
        
        function showNoteModal() {
            document.getElementById('modalTitle').textContent = 'New Encrypted Note';
            document.getElementById('noteForm').reset();
            document.getElementById('noteId').value = '';
            document.getElementById('noteModal').style.display = 'flex';
        }
        
        function closeNoteModal() {
            document.getElementById('noteModal').style.display = 'none';
        }
        
        async function saveNote(event) {
            event.preventDefault();
            
            const title = document.getElementById('noteTitle').value;
            const content = document.getElementById('noteContent').value;
            const password = document.getElementById('notePassword').value;
            
            const encrypted = await encryptContent(content, password);
            
            try {
                const response = await fetch('/api/notes.php?action=create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        title,
                        content: encrypted,
                        encrypted: true
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Note encrypted and saved successfully!', 'success');
                    closeNoteModal();
                    loadNotes();
                } else {
                    showNotification(data.message || 'Failed to save note', 'error');
                }
            } catch (error) {
                console.error('Error saving note:', error);
                showNotification('Failed to save note', 'error');
            }
        }
        
        function viewNote(noteId) {
            currentNoteId = noteId;
            document.getElementById('decryptPassword').value = '';
            document.getElementById('decryptError').style.display = 'none';
            document.getElementById('decryptedContent').style.display = 'none';
            document.getElementById('decryptBtn').style.display = 'block';
            document.getElementById('decryptModal').style.display = 'flex';
        }
        
        async function decryptNote() {
            const password = document.getElementById('decryptPassword').value;
            const errorDiv = document.getElementById('decryptError');
            const contentDiv = document.getElementById('decryptedContent');
            
            if (!password) {
                errorDiv.textContent = 'Please enter the decryption password';
                errorDiv.style.display = 'block';
                return;
            }
            
            try {
                const response = await fetch(`/api/notes.php?action=get&id=${currentNoteId}`);
                const data = await response.json();
                
                if (data.success) {
                    const decrypted = await decryptContent(data.note.content, password);
                    
                    document.getElementById('decryptedTitle').textContent = data.note.title;
                    document.getElementById('decryptedText').textContent = decrypted;
                    contentDiv.style.display = 'block';
                    errorDiv.style.display = 'none';
                    document.getElementById('decryptBtn').style.display = 'none';
                } else {
                    errorDiv.textContent = 'Failed to load note';
                    errorDiv.style.display = 'block';
                }
            } catch (error) {
                errorDiv.textContent = 'Incorrect password or corrupted data';
                errorDiv.style.display = 'block';
            }
        }
        
        function closeDecryptModal() {
            document.getElementById('decryptModal').style.display = 'none';
        }
        
        async function deleteNote(id) {
            if (!confirm('Are you sure you want to delete this encrypted note? This action cannot be undone.')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/notes.php?action=delete&id=${id}`, {
                    method: 'DELETE'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Note deleted successfully', 'success');
                    loadNotes();
                } else {
                    showNotification(data.message || 'Failed to delete note', 'error');
                }
            } catch (error) {
                console.error('Error deleting note:', error);
                showNotification('Failed to delete note', 'error');
            }
        }
        
        // AES-256-GCM Encryption using Web Crypto API
        async function encryptContent(text, password) {
            const enc = new TextEncoder();
            const keyMaterial = await crypto.subtle.importKey(
                'raw',
                enc.encode(password),
                'PBKDF2',
                false,
                ['deriveBits', 'deriveKey']
            );
            
            const salt = crypto.getRandomValues(new Uint8Array(16));
            const key = await crypto.subtle.deriveKey(
                {
                    name: 'PBKDF2',
                    salt: salt,
                    iterations: 100000,
                    hash: 'SHA-256'
                },
                keyMaterial,
                { name: 'AES-GCM', length: 256 },
                false,
                ['encrypt']
            );
            
            const iv = crypto.getRandomValues(new Uint8Array(12));
            const encrypted = await crypto.subtle.encrypt(
                { name: 'AES-GCM', iv: iv },
                key,
                enc.encode(text)
            );
            
            const combined = new Uint8Array(salt.length + iv.length + encrypted.byteLength);
            combined.set(salt, 0);
            combined.set(iv, salt.length);
            combined.set(new Uint8Array(encrypted), salt.length + iv.length);
            
            return btoa(String.fromCharCode(...combined));
        }
        
        async function decryptContent(encrypted, password) {
            const enc = new TextEncoder();
            const dec = new TextDecoder();
            
            const combined = Uint8Array.from(atob(encrypted), c => c.charCodeAt(0));
            const salt = combined.slice(0, 16);
            const iv = combined.slice(16, 28);
            const data = combined.slice(28);
            
            const keyMaterial = await crypto.subtle.importKey(
                'raw',
                enc.encode(password),
                'PBKDF2',
                false,
                ['deriveBits', 'deriveKey']
            );
            
            const key = await crypto.subtle.deriveKey(
                {
                    name: 'PBKDF2',
                    salt: salt,
                    iterations: 100000,
                    hash: 'SHA-256'
                },
                keyMaterial,
                { name: 'AES-GCM', length: 256 },
                false,
                ['decrypt']
            );
            
            const decrypted = await crypto.subtle.decrypt(
                { name: 'AES-GCM', iv: iv },
                key,
                data
            );
            
            return dec.decode(decrypted);
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
        
        // Load notes on page load
        loadNotes();
    </script>
    
    <style>
        .notes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .note-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .note-card.encrypted {
            border-left: 4px solid #f39c12;
        }
        
        .note-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        
        .note-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .note-header h3 {
            margin: 0;
            font-size: 18px;
        }
        
        .note-actions {
            display: flex;
            gap: 5px;
        }
        
        .note-meta {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }
        
        .encrypted-badge {
            background: #f39c12;
            color: white;
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 12px;
        }
        
        .note-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            white-space: pre-wrap;
        }
        
        .info-card {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
        }
        
        .info-card i {
            font-size: 24px;
            color: #2196f3;
        }
    </style>
</body>
</html>
