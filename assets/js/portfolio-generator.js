let portfolioData = {
    skills: [],
    projects: [],
    milestones: []
};

document.addEventListener('DOMContentLoaded', function() {
    loadPortfolioData();
    initTabs();
});

function initTabs() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;
            
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('active', 'text-primary', 'border-primary', 'border-b-2');
                b.classList.add('text-gray-600', 'dark:text-gray-400');
            });
            
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            this.classList.add('active', 'text-primary', 'border-primary', 'border-b-2');
            this.classList.remove('text-gray-600', 'dark:text-gray-400');
            document.getElementById(tab + 'Tab').classList.remove('hidden');
        });
    });
}

async function loadPortfolioData() {
    try {
        const response = await fetch('/api/portfolio.php?action=get_data');
        const result = await response.json();
        
        if (result.success) {
            portfolioData = result.data;
            updateStats();
            renderSkills();
            renderProjects();
            renderMilestones();
        }
    } catch (error) {
        console.error('Error loading portfolio data:', error);
    }
}

function updateStats() {
    document.getElementById('totalSkills').textContent = portfolioData.skills.length;
    document.getElementById('totalProjects').textContent = portfolioData.projects.length;
    document.getElementById('totalMilestones').textContent = portfolioData.milestones.length;
    
    const totalExperience = portfolioData.milestones
        .filter(m => m.milestone_type === 'work_experience')
        .reduce((sum, m) => sum + (parseFloat(m.duration_years) || 0), 0);
    document.getElementById('totalExperience').textContent = totalExperience.toFixed(1) + ' years';
}

function renderSkills() {
    const container = document.getElementById('skillsList');
    if (!portfolioData.skills || portfolioData.skills.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">No skills added yet</p>';
        return;
    }
    
    container.innerHTML = portfolioData.skills.map(skill => `
        <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg mb-2">
            <div class="flex-1">
                <h4 class="font-semibold text-gray-900 dark:text-white">${escapeHtml(skill.skill_name)}</h4>
                <div class="flex items-center gap-2 mt-2">
                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-primary h-2 rounded-full" style="width: ${skill.proficiency_level}%"></div>
                    </div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">${skill.proficiency_level}%</span>
                </div>
                ${skill.category ? `<span class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded mt-2 inline-block">${skill.category}</span>` : ''}
            </div>
            <button onclick="deleteSkill(${skill.id})" class="ml-4 text-red-600 hover:text-red-700">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `).join('');
}

function renderProjects() {
    const container = document.getElementById('projectsList');
    if (!portfolioData.projects || portfolioData.projects.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">No projects added yet</p>';
        return;
    }
    
    container.innerHTML = portfolioData.projects.map(project => `
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4">
            <h4 class="font-semibold text-gray-900 dark:text-white">${escapeHtml(project.project_name)}</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">${escapeHtml(project.description || '')}</p>
            ${project.technologies_used ? `<div class="flex flex-wrap gap-1 mt-2">
                ${project.technologies_used.split(',').map(tech => `<span class="text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-1 rounded">${tech.trim()}</span>`).join('')}
            </div>` : ''}
            ${project.project_url ? `<a href="${project.project_url}" target="_blank" class="text-primary text-sm mt-2 inline-block"><i class="fas fa-external-link-alt"></i> View Project</a>` : ''}
            <button onclick="deleteProject(${project.id})" class="float-right text-red-600 hover:text-red-700">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `).join('');
}

function renderMilestones() {
    const container = document.getElementById('milestonesList');
    if (!portfolioData.milestones || portfolioData.milestones.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">No milestones added yet</p>';
        return;
    }
    
    container.innerHTML = portfolioData.milestones.map(milestone => `
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 dark:text-white">${escapeHtml(milestone.title)}</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">${escapeHtml(milestone.description || '')}</p>
                    <div class="flex gap-4 mt-2 text-sm text-gray-500">
                        ${milestone.achievement_date ? `<span><i class="fas fa-calendar"></i> ${new Date(milestone.achievement_date).toLocaleDateString()}</span>` : ''}
                        ${milestone.milestone_type ? `<span class="capitalize">${milestone.milestone_type.replace('_', ' ')}</span>` : ''}
                    </div>
                </div>
                <button onclick="deleteMilestone(${milestone.id})" class="text-red-600 hover:text-red-700">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
}

function addSkill() {
    const skillName = prompt('Enter skill name:');
    if (!skillName) return;
    
    const proficiency = prompt('Enter proficiency level (0-100):');
    if (!proficiency) return;
    
    const category = prompt('Enter category (optional):');
    
    saveSkill({
        skill_name: skillName,
        proficiency_level: Math.min(100, Math.max(0, parseInt(proficiency) || 50)),
        category: category || ''
    });
}

async function saveSkill(skillData) {
    try {
        const response = await fetch('/api/portfolio.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'add_skill',
                ...skillData
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Skill added successfully');
            loadPortfolioData();
        } else {
            showToast('error', 'Error', result.message || 'Failed to add skill');
        }
    } catch (error) {
        console.error('Error saving skill:', error);
        showToast('error', 'Error', 'Failed to save skill');
    }
}

function addProject() {
    const projectName = prompt('Enter project name:');
    if (!projectName) return;
    
    const description = prompt('Enter project description:');
    const technologies = prompt('Enter technologies (comma-separated):');
    const url = prompt('Enter project URL (optional):');
    
    saveProject({
        project_name: projectName,
        description: description || '',
        technologies_used: technologies || '',
        project_url: url || ''
    });
}

async function saveProject(projectData) {
    try {
        const response = await fetch('/api/portfolio.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'add_project',
                ...projectData
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Project added successfully');
            loadPortfolioData();
        } else {
            showToast('error', 'Error', result.message || 'Failed to add project');
        }
    } catch (error) {
        console.error('Error saving project:', error);
        showToast('error', 'Error', 'Failed to save project');
    }
}

function addMilestone() {
    const title = prompt('Enter milestone title:');
    if (!title) return;
    
    const description = prompt('Enter description:');
    const date = prompt('Enter achievement date (YYYY-MM-DD):');
    const type = prompt('Enter type (certification, work_experience, achievement):');
    
    saveMilestone({
        title: title,
        description: description || '',
        achievement_date: date || new Date().toISOString().split('T')[0],
        milestone_type: type || 'achievement'
    });
}

async function saveMilestone(milestoneData) {
    try {
        const response = await fetch('/api/portfolio.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'add_milestone',
                ...milestoneData
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Milestone added successfully');
            loadPortfolioData();
        } else {
            showToast('error', 'Error', result.message || 'Failed to add milestone');
        }
    } catch (error) {
        console.error('Error saving milestone:', error);
        showToast('error', 'Error', 'Failed to save milestone');
    }
}

async function deleteSkill(id) {
    if (!confirm('Delete this skill?')) return;
    
    try {
        const response = await fetch('/api/portfolio.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'delete_skill',
                id: id
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Skill deleted');
            loadPortfolioData();
        }
    } catch (error) {
        console.error('Error deleting skill:', error);
    }
}

async function deleteProject(id) {
    if (!confirm('Delete this project?')) return;
    
    try {
        const response = await fetch('/api/portfolio.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'delete_project',
                id: id
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Project deleted');
            loadPortfolioData();
        }
    } catch (error) {
        console.error('Error deleting project:', error);
    }
}

async function deleteMilestone(id) {
    if (!confirm('Delete this milestone?')) return;
    
    try {
        const response = await fetch('/api/portfolio.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'delete_milestone',
                id: id
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Milestone deleted');
            loadPortfolioData();
        }
    } catch (error) {
        console.error('Error deleting milestone:', error);
    }
}

async function generatePortfolio() {
    if (portfolioData.skills.length === 0 && portfolioData.projects.length === 0) {
        showToast('warning', 'Warning', 'Please add some skills and projects first');
        return;
    }
    
    try {
        const response = await fetch('/api/portfolio.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'generate_portfolio'
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Portfolio website generated successfully!');
            if (result.portfolio_url) {
                window.open(result.portfolio_url, '_blank');
            }
        } else {
            showToast('error', 'Error', result.message || 'Failed to generate portfolio');
        }
    } catch (error) {
        console.error('Error generating portfolio:', error);
        showToast('error', 'Error', 'Failed to generate portfolio');
    }
}

async function generateResume() {
    if (portfolioData.skills.length === 0 && portfolioData.projects.length === 0) {
        showToast('warning', 'Warning', 'Please add some skills and projects first');
        return;
    }
    
    try {
        const response = await fetch('/api/portfolio.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'generate_resume'
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Resume generated successfully!');
            if (result.resume_url) {
                window.open(result.resume_url, '_blank');
            } else {
                showToast('info', 'Info', 'Resume data compiled. Professional PDF generation coming soon!');
            }
        } else {
            showToast('error', 'Error', result.message || 'Failed to generate resume');
        }
    } catch (error) {
        console.error('Error generating resume:', error);
        showToast('error', 'Error', 'Failed to generate resume');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
