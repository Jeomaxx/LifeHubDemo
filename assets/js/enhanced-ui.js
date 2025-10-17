// Enhanced UI/UX JavaScript for Life Atlas Organizer v2

// Initialize Lucide Icons
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Initialize scroll reveal animations
    initScrollReveal();
    
    // Initialize progress rings
    initProgressRings();
    
    // Initialize counter animations
    initCounterAnimations();
});

// Scroll Reveal Animation
function initScrollReveal() {
    const reveals = document.querySelectorAll('.reveal');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, {
        threshold: 0.15
    });
    
    reveals.forEach(reveal => observer.observe(reveal));
}

// Progress Ring Animation
function initProgressRings() {
    const rings = document.querySelectorAll('.progress-ring__circle');
    
    rings.forEach(ring => {
        const percent = parseFloat(ring.getAttribute('data-percent')) || 0;
        const radius = ring.r.baseVal.value;
        const circumference = radius * 2 * Math.PI;
        const offset = circumference - (percent / 100) * circumference;
        
        ring.style.strokeDasharray = `${circumference} ${circumference}`;
        ring.style.strokeDashoffset = circumference;
        
        setTimeout(() => {
            ring.style.strokeDashoffset = offset;
        }, 100);
    });
}

// Counter Animation
function initCounterAnimations() {
    const counters = document.querySelectorAll('.counter');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                const target = parseInt(entry.target.getAttribute('data-target')) || 0;
                animateCounter(entry.target, 0, target, 2000);
                entry.target.classList.add('counted');
            }
        });
    }, {
        threshold: 0.5
    });
    
    counters.forEach(counter => observer.observe(counter));
}

function animateCounter(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= end) {
            element.textContent = end;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

// Create Progress Ring SVG
function createProgressRing(percent, size = 120, strokeWidth = 8, color = '#3b82f6') {
    const radius = (size - strokeWidth) / 2;
    const circumference = radius * 2 * Math.PI;
    const offset = circumference - (percent / 100) * circumference;
    
    return `
        <svg class="progress-ring" width="${size}" height="${size}">
            <circle
                class="progress-ring__circle"
                stroke="#e5e7eb"
                stroke-width="${strokeWidth}"
                fill="transparent"
                r="${radius}"
                cx="${size/2}"
                cy="${size/2}"
            />
            <circle
                class="progress-ring__circle"
                stroke="${color}"
                stroke-width="${strokeWidth}"
                fill="transparent"
                r="${radius}"
                cx="${size/2}"
                cy="${size/2}"
                data-percent="${percent}"
                style="stroke-dasharray: ${circumference} ${circumference}; stroke-dashoffset: ${offset};"
            />
        </svg>
    `;
}

// Add ripple effect to buttons
document.addEventListener('click', (e) => {
    const button = e.target.closest('.btn-interactive');
    if (button) {
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');
        
        button.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }
});

// Export functions for use in other modules
window.LifeAtlasUI = {
    createProgressRing,
    initScrollReveal,
    initProgressRings,
    initCounterAnimations
};
