document.addEventListener('DOMContentLoaded', function() {
    initTheme();
    initSidebar();
    initModals();
    initSearch();
    initNotifications();
    initToastContainer();
    initAnimations();
    initFormValidation();
    initTooltips();
});

function initTheme() {
    const themeToggle = document.getElementById('themeToggle');
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateThemeIcon(currentTheme);
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const theme = document.documentElement.getAttribute('data-theme');
            const newTheme = theme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
            
            showToast('success', 'Theme Changed', `Switched to ${newTheme} mode`);
        });
    }
}

function updateThemeIcon(theme) {
    const icon = document.querySelector('#themeToggle i');
    if (icon) {
        icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
    }
}

function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
        });
    }
    
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (mobileToggle && !sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
    
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(4px)';
        });
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
}

function initModals() {
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        const closeBtn = modal.querySelector('.modal-close');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                closeModalWithAnimation(modal);
            });
        }
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModalWithAnimation(modal);
            }
        });
    });
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            // Check for both active and flex modal patterns
            const activeModal = document.querySelector('.modal.active, .modal.flex');
            if (activeModal) {
                closeModalWithAnimation(activeModal);
            }
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        // Support both 'active' and 'flex/hidden' modal patterns
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        } else {
            modal.classList.add('active');
        }
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        // Support both 'active' and 'flex/hidden' modal patterns
        if (modal.classList.contains('flex')) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        } else {
            closeModalWithAnimation(modal);
        }
    }
}

function closeModalWithAnimation(modal) {
    const modalContent = modal.querySelector('.modal-content');
    
    // Detect modal pattern: flex/hidden or active
    const isFlexPattern = modal.classList.contains('flex');
    
    if (modalContent) {
        modalContent.style.animation = 'scaleOut 0.3s ease-out';
        setTimeout(() => {
            if (isFlexPattern) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            } else {
                modal.classList.remove('active');
            }
            document.body.style.overflow = '';
            modalContent.style.animation = '';
        }, 300);
    } else {
        if (isFlexPattern) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        } else {
            modal.classList.remove('active');
        }
        document.body.style.overflow = '';
    }
}

function initSearch() {
    const searchInput = document.getElementById('globalSearch');
    
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) return;
            
            searchTimeout = setTimeout(() => {
                performGlobalSearch(query);
            }, 500);
        });
        
        searchInput.addEventListener('focus', function() {
            this.parentElement.classList.add('search-focused');
        });
        
        searchInput.addEventListener('blur', function() {
            this.parentElement.classList.remove('search-focused');
        });
    }
}

function performGlobalSearch(query) {
    console.log('Searching for:', query);
    showToast('info', 'Searching...', `Looking for "${query}"`);
}

function initNotifications() {
    loadNotifications();
    setInterval(loadNotifications, 60000);
}

function loadNotifications() {
    fetch('/api/notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationBadge(data.count);
            }
        })
        .catch(error => console.error('Error loading notifications:', error));
}

function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationCount');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'block' : 'none';
    }
}

function initToastContainer() {
    if (!document.querySelector('.toast-container')) {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
}

function showToast(type, title, message, duration = 4000) {
    const container = document.querySelector('.toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const iconMap = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle',
        warning: 'fa-exclamation-triangle'
    };
    
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas ${iconMap[type] || 'fa-info-circle'}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function initAnimations() {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) return;
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    const elements = document.querySelectorAll('.stat-card, .dashboard-card, .chart-card');
    elements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'all 0.6s ease-out';
        observer.observe(el);
    });
}

function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateInput(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateInput(this);
                }
            });
        });
        
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            inputs.forEach(input => {
                if (!validateInput(input)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showToast('error', 'Validation Error', 'Please fill in all required fields correctly');
            }
        });
    });
}

function validateInput(input) {
    const value = input.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    if (input.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'This field is required';
    } else if (input.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address';
        }
    } else if (input.type === 'number' && value) {
        if (input.hasAttribute('min') && parseFloat(value) < parseFloat(input.min)) {
            isValid = false;
            errorMessage = `Minimum value is ${input.min}`;
        }
        if (input.hasAttribute('max') && parseFloat(value) > parseFloat(input.max)) {
            isValid = false;
            errorMessage = `Maximum value is ${input.max}`;
        }
    }
    
    const errorElement = input.parentElement.querySelector('.error-message');
    
    if (isValid) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        if (errorElement) errorElement.remove();
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        
        if (!errorElement) {
            const error = document.createElement('div');
            error.className = 'error-message';
            error.style.color = 'var(--danger)';
            error.style.fontSize = '0.875rem';
            error.style.marginTop = '0.25rem';
            error.textContent = errorMessage;
            input.parentElement.appendChild(error);
        } else {
            errorElement.textContent = errorMessage;
        }
    }
    
    return isValid;
}

function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        const tooltipText = element.getAttribute('data-tooltip');
        const tooltip = document.createElement('span');
        tooltip.className = 'tooltip-text';
        tooltip.textContent = tooltipText;
        
        element.classList.add('tooltip');
        element.appendChild(tooltip);
    });
}

function showLoading() {
    if (document.querySelector('.loading-overlay')) return;
    
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => overlay.remove(), 300);
    }
}

function getCSRFToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
}

async function apiRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCSRFToken()
        }
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    showLoading();
    
    try {
        const response = await fetch(url, options);
        const result = await response.json();
        hideLoading();
        return result;
    } catch (error) {
        hideLoading();
        console.error('API request failed:', error);
        showToast('error', 'Request Failed', 'An error occurred while processing your request');
        return { success: false, message: 'Request failed' };
    }
}

async function createItem(module, data) {
    const result = await apiRequest(`/api/crud.php?action=create&module=${module}`, 'POST', data);
    
    if (result.success) {
        showToast('success', 'Success', 'Item created successfully');
        setTimeout(() => location.reload(), 1000);
    } else {
        showToast('error', 'Error', result.message || 'Failed to create item');
    }
    
    return result;
}

async function updateItem(module, id, data) {
    data.id = id;
    const result = await apiRequest(`/api/crud.php?action=update&module=${module}`, 'POST', data);
    
    if (result.success) {
        showToast('success', 'Success', 'Item updated successfully');
        setTimeout(() => location.reload(), 1000);
    } else {
        showToast('error', 'Error', result.message || 'Failed to update item');
    }
    
    return result;
}

async function deleteItem(module, id) {
    if (!confirm('Are you sure you want to delete this item?')) {
        return;
    }
    
    const result = await apiRequest(`/api/crud.php?action=delete&module=${module}`, 'POST', { id });
    
    if (result.success) {
        showToast('success', 'Success', 'Item deleted successfully');
        setTimeout(() => location.reload(), 1000);
    } else {
        showToast('error', 'Error', result.message || 'Failed to delete item');
    }
    
    return result;
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
        ${message}
    `;
    
    const container = document.querySelector('.content-wrapper') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.style.opacity = '0';
        setTimeout(() => alertDiv.remove(), 300);
    }, 5000);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function animateNumber(element, start, end, duration = 1000) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = Math.round(current);
    }, 16);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function smoothScrollTo(element) {
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-primary') || e.target.closest('.btn-primary')) {
        const btn = e.target.classList.contains('btn-primary') ? e.target : e.target.closest('.btn-primary');
        btn.style.transform = 'scale(0.95)';
        setTimeout(() => {
            btn.style.transform = '';
        }, 200);
    }
});

window.addEventListener('load', function() {
    document.querySelectorAll('.stat-info h3').forEach((el, index) => {
        const value = parseInt(el.textContent.replace(/[^0-9]/g, ''));
        if (!isNaN(value)) {
            setTimeout(() => {
                animateNumber(el, 0, value, 1000);
            }, index * 100);
        }
    });
});
