<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];
$page_title = 'Secure Notes';

$notes = $db->query(
    "SELECT id, title, is_encrypted, created_at, updated_at FROM secure_notes WHERE user_id = ? ORDER BY updated_at DESC",
    [$user_id]
);

include 'includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-lock"></i> Secure Notes</h1>
    <button onclick="showNoteModal()" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Note
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="notes-grid">
            <?php if (empty($notes)): ?>
                <div class="empty-state">
                    <i class="fas fa-sticky-note"></i>
                    <p>No notes yet. Create your first secure note!</p>
                </div>
            <?php else: ?>
                <?php foreach ($notes as $note): ?>
                    <div class="note-card" data-note-id="<?= $note['id'] ?>">
                        <div class="note-header">
                            <h3><?= htmlspecialchars($note['title']) ?></h3>
                            <?php if ($note['is_encrypted']): ?>
                                <span class="encrypted-badge" title="Encrypted">
                                    <i class="fas fa-lock"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="note-meta">
                            <small><?= date('M d, Y h:i A', strtotime($note['updated_at'])) ?></small>
                        </div>
                        <div class="note-actions">
                            <button onclick="viewNote(<?= $note['id'] ?>, <?= $note['is_encrypted'] ? 'true' : 'false' ?>)" 
                                    class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button onclick="deleteNote(<?= $note['id'] ?>)" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="noteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">New Note</h2>
            <span class="close" onclick="closeNoteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="noteForm">
                <input type="hidden" id="noteId" name="id">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" id="noteTitle" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <textarea id="noteContent" name="content" class="form-control" 
                              rows="10" required></textarea>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="noteEncrypted" name="is_encrypted">
                        <span>Encrypt this note (AES-256)</span>
                    </label>
                </div>
                <div id="encryptionKeyGroup" class="form-group" style="display: none;">
                    <label>Encryption Password</label>
                    <input type="password" id="encryptionKey" class="form-control" 
                           placeholder="Enter encryption password">
                    <small class="form-text">
                        This password is not stored. You'll need it to decrypt the note.
                    </small>
                </div>
                <button type="submit" class="btn btn-primary">Save Note</button>
            </form>
        </div>
    </div>
</div>

<div id="viewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="viewTitle"></h2>
            <span class="close" onclick="closeViewModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="decryptionForm" style="display: none;">
                <div class="form-group">
                    <label>Enter decryption password:</label>
                    <input type="password" id="decryptionKey" class="form-control">
                </div>
                <button onclick="decryptNote()" class="btn btn-primary">Decrypt</button>
            </div>
            <div id="noteContentView"></div>
        </div>
    </div>
</div>

<style>
.notes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.note-card {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 20px;
    background: var(--card-bg);
    transition: transform 0.2s, box-shadow 0.2s;
}

.note-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.note-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 10px;
}

.note-header h3 {
    margin: 0;
    font-size: 18px;
    flex: 1;
}

.encrypted-badge {
    color: var(--warning-color);
    font-size: 18px;
}

.note-meta {
    color: var(--text-muted);
    margin-bottom: 15px;
}

.note-actions {
    display: flex;
    gap: 10px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
<script>
let currentNoteId = null;

document.getElementById('noteEncrypted').addEventListener('change', function() {
    document.getElementById('encryptionKeyGroup').style.display = 
        this.checked ? 'block' : 'none';
});

document.getElementById('noteForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        id: formData.get('id'),
        title: formData.get('title'),
        content: formData.get('content'),
        is_encrypted: formData.get('is_encrypted') ? 1 : 0
    };
    
    if (data.is_encrypted) {
        const key = document.getElementById('encryptionKey').value;
        if (!key) {
            showToast('Please enter an encryption password', 'error');
            return;
        }
        data.content = CryptoJS.AES.encrypt(data.content, key).toString();
    }
    
    const response = await fetch('/api/notes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    if (result.success) {
        showToast('Note saved successfully', 'success');
        location.reload();
    } else {
        showToast(result.error || 'Failed to save note', 'error');
    }
});

function showNoteModal() {
    document.getElementById('modalTitle').textContent = 'New Note';
    document.getElementById('noteForm').reset();
    document.getElementById('noteId').value = '';
    document.getElementById('noteModal').style.display = 'block';
}

function closeNoteModal() {
    document.getElementById('noteModal').style.display = 'none';
}

function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}

async function viewNote(id, isEncrypted) {
    currentNoteId = id;
    const response = await fetch(`/api/notes.php?id=${id}`);
    const note = await response.json();
    
    document.getElementById('viewTitle').textContent = note.title;
    
    if (isEncrypted) {
        document.getElementById('decryptionForm').style.display = 'block';
        document.getElementById('noteContentView').innerHTML = 
            '<p class="text-muted"><i class="fas fa-lock"></i> This note is encrypted</p>';
    } else {
        document.getElementById('decryptionForm').style.display = 'none';
        document.getElementById('noteContentView').innerHTML = 
            '<div class="note-content">' + escapeHtml(note.content) + '</div>';
    }
    
    document.getElementById('viewModal').style.display = 'block';
}

async function decryptNote() {
    const key = document.getElementById('decryptionKey').value;
    if (!key) {
        showToast('Please enter the decryption password', 'error');
        return;
    }
    
    const response = await fetch(`/api/notes.php?id=${currentNoteId}`);
    const note = await response.json();
    
    try {
        const decrypted = CryptoJS.AES.decrypt(note.content, key).toString(CryptoJS.enc.Utf8);
        if (!decrypted) {
            showToast('Incorrect decryption password', 'error');
            return;
        }
        document.getElementById('decryptionForm').style.display = 'none';
        document.getElementById('noteContentView').innerHTML = 
            '<div class="note-content">' + escapeHtml(decrypted) + '</div>';
    } catch (e) {
        showToast('Incorrect decryption password', 'error');
    }
}

async function deleteNote(id) {
    if (!confirm('Are you sure you want to delete this note?')) return;
    
    const response = await fetch('/api/notes.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ id: id })
    });
    
    const result = await response.json();
    if (result.success) {
        showToast('Note deleted successfully', 'success');
        location.reload();
    } else {
        showToast(result.error || 'Failed to delete note', 'error');
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
</script>

<?php include 'includes/footer.php'; ?>
