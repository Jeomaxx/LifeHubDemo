async function addCourse(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    try {
        const response = await fetch('/api/learning_center.php?action=add_course', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Course added successfully');
            closeModal('addCourseModal');
            event.target.reset();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', 'Error', result.message || 'Failed to add course');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to add course');
    }
}

async function updateProgress(courseId) {
    const progress = prompt('Enter progress percentage (0-100):');
    
    if (progress === null || progress === '') return;
    
    const progressNum = parseInt(progress);
    if (isNaN(progressNum) || progressNum < 0 || progressNum > 100) {
        showToast('error', 'Invalid', 'Please enter a number between 0 and 100');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('course_id', courseId);
        formData.append('progress_percentage', progressNum);
        
        const response = await fetch('/api/learning_center.php?action=update_progress', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Updated', 'Progress updated successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', 'Error', result.message || 'Failed to update progress');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to update progress');
    }
}

async function viewNotes(courseId) {
    try {
        const response = await fetch(`/api/learning_center.php?action=get_notes&course_id=${courseId}`);
        const result = await response.json();
        
        if (result.success) {
            showNotesModal(courseId, result.notes);
        } else {
            showToast('error', 'Error', result.message || 'Failed to load notes');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to load notes');
    }
}

function showNotesModal(courseId, notes) {
    const modal = document.getElementById('notesModal');
    if (modal) modal.remove();
    
    const notesHtml = notes.map(note => `
        <div class="note-item">
            <h5>${note.note_title}</h5>
            <p>${note.note_content}</p>
            <small class="text-muted">${new Date(note.created_at).toLocaleDateString()}</small>
        </div>
    `).join('');
    
    const modalHtml = `
        <div id="notesModal" class="modal" style="display: flex;">
            <div class="modal-content" style="max-width: 700px;">
                <div class="modal-header">
                    <h2><i class="fas fa-sticky-note"></i> Course Notes</h2>
                    <button class="modal-close" onclick="closeModal('notesModal')">&times;</button>
                </div>
                <div class="modal-body">
                    ${notes.length > 0 ? notesHtml : '<p class="text-muted">No notes yet</p>'}
                    <hr>
                    <h4>Add New Note</h4>
                    <form onsubmit="addNote(event, ${courseId})">
                        <div class="form-group">
                            <label>Note Title</label>
                            <input type="text" name="note_title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Note Content</label>
                            <textarea name="note_content" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Note
                        </button>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

async function addNote(event, courseId) {
    event.preventDefault();
    const formData = new FormData(event.target);
    formData.append('course_id', courseId);
    
    try {
        const response = await fetch('/api/learning_center.php?action=add_note', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Note added successfully');
            viewNotes(courseId);
        } else {
            showToast('error', 'Error', result.message || 'Failed to add note');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to add note');
    }
}

async function getAIRecommendations() {
    showToast('info', 'Processing', 'AI is analyzing your learning path...');
    
    try {
        const response = await fetch('/api/learning_center.php?action=ai_recommendations');
        const result = await response.json();
        
        if (result.success) {
            showRecommendationsModal(result.recommendations);
        } else {
            showToast('error', 'Error', result.message || 'Failed to get recommendations');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to get recommendations');
    }
}

function showRecommendationsModal(recommendations) {
    const modal = document.getElementById('recommendationsModal');
    if (modal) modal.remove();
    
    const recsHtml = recommendations.map(rec => `
        <div class="recommendation-item">
            <h5>${rec.skill_name}</h5>
            <p>${rec.reason}</p>
            <div class="rec-meta">
                <span class="badge badge-${rec.priority === 'high' ? 'danger' : rec.priority === 'medium' ? 'warning' : 'info'}">
                    ${rec.priority} Priority
                </span>
                <span class="text-muted"><i class="fas fa-clock"></i> ${rec.estimated_time}</span>
            </div>
        </div>
    `).join('');
    
    const modalHtml = `
        <div id="recommendationsModal" class="modal" style="display: flex;">
            <div class="modal-content" style="max-width: 600px;">
                <div class="modal-header">
                    <h2><i class="fas fa-magic"></i> AI Skill Recommendations</h2>
                    <button class="modal-close" onclick="closeModal('recommendationsModal')">&times;</button>
                </div>
                <div class="modal-body">
                    ${recsHtml}
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function showAddCourseModal() {
    openModal('addCourseModal');
}
