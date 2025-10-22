document.addEventListener('DOMContentLoaded', function() {
    enableDragDrop();
    loadDashboard();
});

function enableDragDrop() {
    const grid = document.getElementById('dashboardGrid');
    if (!grid) return;
    
    let draggedItem = null;
    
    grid.addEventListener('dragstart', function(e) {
        if (e.target.classList.contains('dashboard-widget')) {
            draggedItem = e.target;
            e.target.style.opacity = '0.4';
        }
    });
    
    grid.addEventListener('dragend', function(e) {
        if (e.target.classList.contains('dashboard-widget')) {
            e.target.style.opacity = '1';
            saveDashboardLayout();
        }
    });
    
    grid.addEventListener('dragover', function(e) {
        e.preventDefault();
    });
    
    grid.addEventListener('drop', function(e) {
        e.preventDefault();
        if (draggedItem && e.target.classList.contains('dashboard-widget')) {
            const parent = draggedItem.parentNode;
            const dropTarget = e.target;
            parent.insertBefore(draggedItem, dropTarget);
        }
    });
}

async function loadDashboard() {
    try {
        const response = await fetch('/api/custom_dashboards.php?action=load');
        const result = await response.json();
        if (result.success && result.widgets) {
            renderWidgets(result.widgets);
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
}

function renderWidgets(widgets) {
    const grid = document.getElementById('dashboardGrid');
    if (!grid) return;
    
    grid.innerHTML = widgets.map(widget => `
        <div class="dashboard-widget" draggable="true" data-widget-id="${widget.id}">
            <div class="widget-header">
                <h3>${escapeHtml(widget.title)}</h3>
                <button onclick="removeWidget(${widget.id})" class="text-red-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="widget-content">
                ${widget.content || 'Loading...'}
            </div>
        </div>
    `).join('');
    
    loadWidgetData();
}

async function loadWidgetData() {
    const widgets = document.querySelectorAll('.dashboard-widget');
    for (const widget of widgets) {
        const widgetId = widget.dataset.widgetId;
        const response = await fetch(`/api/custom_dashboards.php?action=widget_data&id=${widgetId}`);
        const result = await response.json();
        if (result.success) {
            const content = widget.querySelector('.widget-content');
            content.innerHTML = result.html || result.data;
        }
    }
}

async function saveDashboardLayout() {
    const widgets = Array.from(document.querySelectorAll('.dashboard-widget')).map((w, index) => ({
        id: w.dataset.widgetId,
        order: index
    }));
    
    try {
        await fetch('/api/custom_dashboards.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'save_layout',
                widgets: widgets
            })
        });
    } catch (error) {
        console.error('Error saving layout:', error);
    }
}

async function addWidget(type) {
    try {
        const response = await fetch('/api/custom_dashboards.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'add_widget',
                widget_type: type
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Widget added successfully');
            loadDashboard();
        }
    } catch (error) {
        console.error('Error adding widget:', error);
        showToast('error', 'Error', 'Failed to add widget');
    }
}

async function removeWidget(id) {
    if (!confirm('Remove this widget?')) return;
    
    try {
        const response = await fetch('/api/custom_dashboards.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'remove_widget',
                id: id
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Widget removed');
            loadDashboard();
        }
    } catch (error) {
        console.error('Error removing widget:', error);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
