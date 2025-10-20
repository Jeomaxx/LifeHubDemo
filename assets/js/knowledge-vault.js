document.addEventListener('DOMContentLoaded', function() {
    loadArticles();
    initSearch();
});

function initSearch() {
    const searchInput = document.getElementById('searchArticles');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterArticles(this.value);
        });
    }
}

async function loadArticles() {
    try {
        const response = await fetch('/api/knowledge_vault.php?action=get_articles');
        const result = await response.json();
        if (result.success) {
            renderArticles(result.articles || []);
        }
    } catch (error) {
        console.error('Error loading articles:', error);
    }
}

function renderArticles(articles) {
    const container = document.getElementById('articlesContainer');
    if (!container) return;
    
    if (articles.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">No articles yet. Add your first knowledge article!</p>';
        return;
    }
    
    container.innerHTML = articles.map(article => `
        <div class="article-card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <h3 class="text-xl font-semibold">${escapeHtml(article.title)}</h3>
                ${article.category ? `<span class="badge badge-${article.category}">${article.category}</span>` : ''}
            </div>
            <p class="text-gray-600 dark:text-gray-400 mb-4">${escapeHtml(article.summary || '').substring(0, 150)}...</p>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">${formatDate(article.created_at)}</span>
                <div class="flex gap-2">
                    <button onclick="viewArticle(${article.id})" class="btn btn-sm btn-primary">View</button>
                    <button onclick="editArticle(${article.id})" class="btn btn-sm btn-secondary">Edit</button>
                    <button onclick="deleteArticle(${article.id})" class="btn btn-sm btn-danger">Delete</button>
                </div>
            </div>
        </div>
    `).join('');
}

function filterArticles(query) {
    const cards = document.querySelectorAll('.article-card');
    cards.forEach(card => {
        const title = card.querySelector('h3').textContent.toLowerCase();
        const visible = title.includes(query.toLowerCase());
        card.style.display = visible ? 'block' : 'none';
    });
}

function showAddArticleModal() {
    const modal = document.getElementById('addArticleModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeAddArticleModal() {
    const modal = document.getElementById('addArticleModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

async function saveArticle() {
    const title = document.getElementById('articleTitle').value;
    const content = document.getElementById('articleContent').value;
    const category = document.getElementById('articleCategory').value;
    const tags = document.getElementById('articleTags').value;
    
    if (!title || !content) {
        showToast('error', 'Error', 'Please fill in all required fields');
        return;
    }
    
    try {
        const response = await fetch('/api/knowledge_vault.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'add_article',
                title: title,
                content: content,
                category: category,
                tags: tags
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Article saved successfully');
            closeAddArticleModal();
            loadArticles();
        } else {
            showToast('error', 'Error', result.message || 'Failed to save article');
        }
    } catch (error) {
        console.error('Error saving article:', error);
        showToast('error', 'Error', 'Failed to save article');
    }
}

async function viewArticle(id) {
    try {
        const response = await fetch(`/api/knowledge_vault.php?action=get_article&id=${id}`);
        const result = await response.json();
        if (result.success && result.article) {
            showArticleModal(result.article);
        }
    } catch (error) {
        console.error('Error loading article:', error);
    }
}

function showArticleModal(article) {
    const modalHtml = `
        <div class="modal-content">
            <h2>${escapeHtml(article.title)}</h2>
            <div class="article-content">${article.content}</div>
            <button onclick="closeModal()" class="btn btn-primary mt-4">Close</button>
        </div>
    `;
    
    let modal = document.getElementById('viewArticleModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'viewArticleModal';
        modal.className = 'modal flex';
        document.body.appendChild(modal);
    }
    modal.innerHTML = modalHtml;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

async function editArticle(id) {
    try {
        const response = await fetch(`/api/knowledge_vault.php?action=get_article&id=${id}`);
        const result = await response.json();
        if (result.success && result.article) {
            populateEditForm(result.article);
        }
    } catch (error) {
        console.error('Error loading article:', error);
    }
}

function populateEditForm(article) {
    document.getElementById('articleTitle').value = article.title;
    document.getElementById('articleContent').value = article.content;
    document.getElementById('articleCategory').value = article.category || '';
    document.getElementById('articleTags').value = article.tags || '';
    
    const form = document.getElementById('articleForm');
    form.dataset.editId = article.id;
    showAddArticleModal();
}

async function deleteArticle(id) {
    if (!confirm('Delete this article?')) return;
    
    try {
        const response = await fetch('/api/knowledge_vault.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'delete_article',
                id: id
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Article deleted');
            loadArticles();
        }
    } catch (error) {
        console.error('Error deleting article:', error);
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function closeModal() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });
}
