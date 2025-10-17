// Career Center JavaScript

let currentEditId = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    loadStats();
    loadJobs();
    initializeTabs();
    initializeFilters();
    
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Set default application date to today
    const today = new Date().toISOString().split('T')[0];
    document.querySelector('[name="application_date"]').value = today;
});

// Tab functionality
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabName = button.getAttribute('data-tab');
            
            // Update active button
            tabButtons.forEach(btn => btn.classList.remove('active', 'text-primary', 'border-b-2', 'border-primary'));
            button.classList.add('active', 'text-primary', 'border-b-2', 'border-primary');
            
            // Show active tab content
            document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
            document.getElementById(`${tabName}Tab`).classList.remove('hidden');
            
            // Load corresponding data
            if (tabName === 'jobs') loadJobs();
            else if (tabName === 'interviews') loadInterviews();
            else if (tabName === 'certifications') loadCertifications();
            else if (tabName === 'resumes') loadResumes();
        });
    });
}

// Filters
function initializeFilters() {
    document.getElementById('statusFilter').addEventListener('change', (e) => {
        loadJobs(e.target.value);
    });
}

// Load statistics
async function loadStats() {
    try {
        const response = await fetch('/api/career.php?type=stats');
        const data = await response.json();
        
        if (data.success) {
            document.querySelector('#totalApplicationsCard .counter').setAttribute('data-target', data.data.total_applications);
            document.querySelector('#interviewsCard .counter').setAttribute('data-target', data.data.interviews_scheduled);
            document.querySelector('#offersCard .counter').setAttribute('data-target', data.data.offers_received);
            document.querySelector('#activeCard .counter').setAttribute('data-target', data.data.active_applications);
            
            // Re-initialize counters
            window.LifeAtlasUI.initCounterAnimations();
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Load job applications
async function loadJobs(status = '') {
    try {
        const url = `/api/career.php?type=jobs${status ? `&status=${status}` : ''}`;
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('jobsList');
            
            if (data.data.length === 0) {
                container.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-center py-8">No job applications yet. Add your first application above!</p>';
                return;
            }
            
            container.innerHTML = data.data.map(job => `
                <div class="card-hover bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-3">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">${job.position}</h3>
                            <p class="text-gray-600 dark:text-gray-300">${job.company_name}</p>
                            <div class="flex flex-wrap gap-2 mt-2">
                                ${job.location ? `<span class="badge-modern bg-blue-500 text-white">${job.location}</span>` : ''}
                                ${job.job_type ? `<span class="badge-modern bg-green-500 text-white">${job.job_type}</span>` : ''}
                                ${job.salary_range ? `<span class="badge-modern bg-purple-500 text-white">${job.salary_range}</span>` : ''}
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Applied: ${formatDate(job.application_date)}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-sm ${getStatusColor(job.status)}">${job.status}</span>
                            <button onclick="editJob(${job.id})" class="text-blue-500 hover:text-blue-700">
                                <i data-lucide="edit" class="w-5 h-5"></i>
                            </button>
                            <button onclick="deleteJob(${job.id})" class="text-red-500 hover:text-red-700">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    ${job.notes ? `<p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">${job.notes}</p>` : ''}
                </div>
            `).join('');
            
            lucide.createIcons();
        }
    } catch (error) {
        console.error('Error loading jobs:', error);
    }
}

// Load interviews
async function loadInterviews() {
    try {
        const response = await fetch('/api/career.php?type=interviews');
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('interviewsList');
            
            if (data.data.length === 0) {
                container.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-center py-8">No interviews scheduled yet.</p>';
                return;
            }
            
            container.innerHTML = data.data.map(interview => `
                <div class="card-hover bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">${interview.company_name} - ${interview.position}</h3>
                            <p class="text-gray-600 dark:text-gray-300">${interview.interview_type || 'Interview'}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                <i data-lucide="calendar" class="w-4 h-4 inline"></i>
                                ${formatDateTime(interview.interview_date)}
                            </p>
                            ${interview.interviewer_name ? `<p class="text-sm text-gray-500 dark:text-gray-400">Interviewer: ${interview.interviewer_name}</p>` : ''}
                        </div>
                        <div class="flex items-center gap-2">
                            ${interview.outcome ? `<span class="px-3 py-1 rounded-full text-sm ${getOutcomeColor(interview.outcome)}">${interview.outcome}</span>` : ''}
                            <button onclick="deleteInterview(${interview.id})" class="text-red-500 hover:text-red-700">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    ${interview.interview_notes ? `<p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">${interview.interview_notes}</p>` : ''}
                </div>
            `).join('');
            
            lucide.createIcons();
        }
    } catch (error) {
        console.error('Error loading interviews:', error);
    }
}

// Load certifications
async function loadCertifications() {
    try {
        const response = await fetch('/api/career.php?type=certifications');
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('certificationsList');
            
            if (data.data.length === 0) {
                container.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-center py-8">No certifications added yet.</p>';
                return;
            }
            
            container.innerHTML = data.data.map(cert => `
                <div class="card-hover bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-3">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">${cert.name}</h3>
                            <p class="text-gray-600 dark:text-gray-300">${cert.issuing_organization || 'N/A'}</p>
                            <div class="flex gap-4 mt-2 text-sm text-gray-500 dark:text-gray-400">
                                ${cert.issue_date ? `<span>Issued: ${formatDate(cert.issue_date)}</span>` : ''}
                                ${cert.expiry_date ? `<span>Expires: ${formatDate(cert.expiry_date)}</span>` : ''}
                            </div>
                            ${cert.credential_url ? `<a href="${cert.credential_url}" target="_blank" class="text-blue-500 hover:underline text-sm mt-1 inline-block">View Credential</a>` : ''}
                        </div>
                        <button onclick="deleteCertification(${cert.id})" class="text-red-500 hover:text-red-700">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
            `).join('');
            
            lucide.createIcons();
        }
    } catch (error) {
        console.error('Error loading certifications:', error);
    }
}

// Load resumes
async function loadResumes() {
    try {
        const response = await fetch('/api/career.php?type=resumes');
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('resumesList');
            
            if (data.data.length === 0) {
                container.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-center py-8">No resume versions saved yet.</p>';
                return;
            }
            
            container.innerHTML = data.data.map(resume => `
                <div class="card-hover bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">${resume.version_name}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Created: ${formatDateTime(resume.created_at)}</p>
                        </div>
                        <button onclick="deleteResume(${resume.id})" class="text-red-500 hover:text-red-700">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
            `).join('');
            
            lucide.createIcons();
        }
    } catch (error) {
        console.error('Error loading resumes:', error);
    }
}

// Add job form submission
document.getElementById('jobForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    data.type = 'job';
    
    try {
        const response = await fetch('/api/career.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeModal('addJobModal');
            e.target.reset();
            loadJobs();
            loadStats();
            showToast('Job application added successfully!', 'success');
        } else {
            showToast(result.message || 'Error adding job application', 'error');
        }
    } catch (error) {
        showToast('Error adding job application', 'error');
        console.error(error);
    }
});

// Delete job
async function deleteJob(id) {
    if (!confirm('Are you sure you want to delete this job application?')) return;
    
    try {
        const response = await fetch(`/api/career.php?id=${id}&type=job`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            loadJobs();
            loadStats();
            showToast('Job application deleted successfully!', 'success');
        }
    } catch (error) {
        showToast('Error deleting job application', 'error');
    }
}

// Delete interview
async function deleteInterview(id) {
    if (!confirm('Are you sure you want to delete this interview?')) return;
    
    try {
        const response = await fetch(`/api/career.php?id=${id}&type=interview`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            loadInterviews();
            loadStats();
            showToast('Interview deleted successfully!', 'success');
        }
    } catch (error) {
        showToast('Error deleting interview', 'error');
    }
}

// Delete certification
async function deleteCertification(id) {
    if (!confirm('Are you sure you want to delete this certification?')) return;
    
    try {
        const response = await fetch(`/api/career.php?id=${id}&type=certification`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            loadCertifications();
            showToast('Certification deleted successfully!', 'success');
        }
    } catch (error) {
        showToast('Error deleting certification', 'error');
    }
}

// Delete resume
async function deleteResume(id) {
    if (!confirm('Are you sure you want to delete this resume version?')) return;
    
    try {
        const response = await fetch(`/api/career.php?id=${id}&type=resume`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            loadResumes();
            showToast('Resume version deleted successfully!', 'success');
        }
    } catch (error) {
        showToast('Error deleting resume', 'error');
    }
}

// Modal functions
function openAddJobModal() {
    document.getElementById('addJobModal').classList.remove('hidden');
}

function openAddCertModal() {
    showToast('Certification form coming soon!', 'info');
}

function openAddResumeModal() {
    showToast('Resume upload coming soon!', 'info');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Utility functions
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
}

function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString();
}

function getStatusColor(status) {
    const colors = {
        'applied': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'interview': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'assessment': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        'offer': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'rejected': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'withdrawn': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function getOutcomeColor(outcome) {
    const colors = {
        'passed': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'failed': 'bg-red-100 text-red-800'
    };
    return colors[outcome] || 'bg-gray-100 text-gray-800';
}

function showToast(message, type = 'info') {
    console.log(`${type.toUpperCase()}: ${message}`);
}
