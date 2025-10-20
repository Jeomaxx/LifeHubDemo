let searchTimeout;
let currentResults = {};

document.addEventListener('DOMContentLoaded', function() {
    initSearchInput();
    initFilters();
});

function initSearchInput() {
    const searchInput = document.getElementById('unifiedSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                clearResults();
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performUnifiedSearch(query);
            }, 500);
        });
        
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performUnifiedSearch(this.value.trim());
            }
        });
    }
}

function initFilters() {
    const filterBtns = document.querySelectorAll('.search-filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filterResults(this.dataset.filter);
        });
    });
}

async function performUnifiedSearch(query) {
    if (!query) return;
    
    showSearchLoading();
    
    try {
        const response = await fetch('/api/unified_search.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'search',
                query: query
            })
        });
        
        const result = await response.json();
        if (result.success) {
            currentResults = result.results;
            displayResults(result.results);
        } else {
            showNoResults(result.message || 'No results found');
        }
    } catch (error) {
        console.error('Search error:', error);
        showSearchError();
    }
}

function displayResults(results) {
    const container = document.getElementById('searchResults');
    if (!container) return;
    
    let totalResults = 0;
    let html = '';
    
    const categories = {
        'tasks': {title: 'Tasks', icon: 'fa-tasks', color: 'blue'},
        'transactions': {title: 'Transactions', icon: 'fa-dollar-sign', color: 'green'},
        'notes': {title: 'Notes', icon: 'fa-sticky-note', color: 'yellow'},
        'contacts': {title: 'Contacts', icon: 'fa-user', color: 'purple'},
        'events': {title: 'Events', icon: 'fa-calendar', color: 'red'},
        'documents': {title: 'Documents', icon: 'fa-file-alt', color: 'gray'},
        'goals': {title: 'Goals', icon: 'fa-bullseye', color: 'orange'},
        'habits': {title: 'Habits', icon: 'fa-check-circle', color: 'teal'}
    };
    
    for (const [category, config] of Object.entries(categories)) {
        const items = results[category] || [];
        totalResults += items.length;
        
        if (items.length > 0) {
            html += `
                <div class="result-category mb-6" data-category="${category}">
                    <h3 class="text-lg font-semibold flex items-center gap-2 mb-3">
                        <i class="fas ${config.icon} text-${config.color}-500"></i>
                        ${config.title} <span class="text-sm text-gray-500">(${items.length})</span>
                    </h3>
                    <div class="space-y-2">
                        ${renderCategoryResults(category, items)}
                    </div>
                </div>
            `;
        }
    }
    
    if (totalResults === 0) {
        showNoResults();
    } else {
        container.innerHTML = `
            <div class="search-summary mb-4">
                <p class="text-gray-600 dark:text-gray-400">Found ${totalResults} result${totalResults !== 1 ? 's' : ''}</p>
            </div>
            ${html}
        `;
    }
}

function renderCategoryResults(category, items) {
    return items.map(item => {
        const title = item.title || item.name || item.description || 'Untitled';
        const details = getItemDetails(category, item);
        
        return `
            <div class="result-item p-3 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="openResult('${category}', ${item.id})">
                <h4 class="font-semibold text-gray-900 dark:text-white">${highlightMatch(title)}</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">${highlightMatch(details)}</p>
                ${item.date || item.created_at ? `<span class="text-xs text-gray-500 mt-2 inline-block">${formatDate(item.date || item.created_at)}</span>` : ''}
            </div>
        `;
    }).join('');
}

function getItemDetails(category, item) {
    switch(category) {
        case 'tasks':
            return item.description || 'Task';
        case 'transactions':
            return `${item.type} - $${parseFloat(item.amount || 0).toFixed(2)} - ${item.category || 'Uncategorized'}`;
        case 'notes':
            return (item.content || '').substring(0, 100) + '...';
        case 'contacts':
            return item.email || item.phone || 'Contact';
        case 'events':
            return item.description || 'Event';
        case 'documents':
            return item.description || 'Document';
        case 'goals':
            return `${item.current_progress || 0}% complete`;
        case 'habits':
            return item.description || 'Habit';
        default:
            return '';
    }
}

function highlightMatch(text) {
    const searchQuery = document.getElementById('unifiedSearchInput')?.value.trim();
    if (!searchQuery || !text) return escapeHtml(text);
    
    const regex = new RegExp(`(${searchQuery})`, 'gi');
    return escapeHtml(text).replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-700">$1</mark>');
}

function filterResults(filter) {
    const categories = document.querySelectorAll('.result-category');
    if (filter === 'all') {
        categories.forEach(cat => cat.style.display = 'block');
    } else {
        categories.forEach(cat => {
            cat.style.display = cat.dataset.category === filter ? 'block' : 'none';
        });
    }
}

function openResult(category, id) {
    const urlMap = {
        'tasks': '/tasks.php?id=',
        'transactions': '/finance.php?id=',
        'notes': '/notes.php?id=',
        'contacts': '/contacts.php?id=',
        'events': '/events.php?id=',
        'documents': '/documents.php?id=',
        'goals': '/goals.php?id=',
        'habits': '/habits.php?id='
    };
    
    const url = urlMap[category];
    if (url) {
        window.location.href = url + id;
    }
}

function showSearchLoading() {
    const container = document.getElementById('searchResults');
    if (container) {
        container.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i><p class="text-gray-500 mt-2">Searching...</p></div>';
    }
}

function showNoResults(message = 'No results found') {
    const container = document.getElementById('searchResults');
    if (container) {
        container.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-search text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">${escapeHtml(message)}</p>
            </div>
        `;
    }
}

function showSearchError() {
    const container = document.getElementById('searchResults');
    if (container) {
        container.innerHTML = '<div class="text-center py-8"><i class="fas fa-exclamation-circle text-3xl text-red-500"></i><p class="text-red-500 mt-2">Search error. Please try again.</p></div>';
    }
}

function clearResults() {
    const container = document.getElementById('searchResults');
    if (container) {
        container.innerHTML = '';
    }
    currentResults = {};
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
