let currentDate = new Date();
let currentView = 'month';
let events = [];
let selectedFilters = ['custom', 'task', 'bill', 'birthday', 'gym'];
let selectedEventId = null;
let selectedColor = '#3b82f6';

document.addEventListener('DOMContentLoaded', function() {
    loadEvents();
    initCalendar();
    initializeFilters();
    setupColorPickers();
    checkSyncStatus();
    
    document.getElementById('eventForm')?.addEventListener('submit', saveEvent);
    document.getElementById('prevPeriod')?.addEventListener('click', () => navigate(-1));
    document.getElementById('nextPeriod')?.addEventListener('click', () => navigate(1));
    document.getElementById('today')?.addEventListener('click', goToToday);
    document.getElementById('viewMode')?.addEventListener('change', function() {
        currentView = this.value;
        renderCalendar();
    });
    document.getElementById('filterBtn')?.addEventListener('click', toggleFilterDropdown);
});

async function loadEvents() {
    try {
        const month = currentDate.getMonth() + 1;
        const year = currentDate.getFullYear();
        const response = await fetch(`/api/calendar.php?action=month&month=${month}&year=${year}`);
        const result = await response.json();
        if (result.success) {
            events = result.events || [];
            renderCalendar();
        }
    } catch (error) {
        console.error('Error loading events:', error);
    }
}

function initCalendar() {
    renderCalendar();
}

function renderCalendar() {
    const calendarView = document.getElementById('calendarView');
    if (!calendarView) return;
    
    updatePeriodTitle();
    
    if (currentView === 'month') {
        renderMonthView(calendarView);
    } else if (currentView === 'week') {
        renderWeekView(calendarView);
    } else if (currentView === 'day') {
        renderDayView(calendarView);
    }
}

function renderMonthView(container) {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDay = firstDay.getDay();
    const daysInMonth = lastDay.getDate();
    
    let html = '<div class="grid grid-cols-7 gap-1">';
    
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    dayNames.forEach(day => {
        html += `<div class="text-center font-semibold text-gray-600 dark:text-gray-400 p-2">${day}</div>`;
    });
    
    for (let i = 0; i < startDay; i++) {
        html += '<div class="min-h-[100px] p-2 bg-gray-50 dark:bg-gray-900/50 rounded"></div>';
    }
    
    for (let day = 1; day <= daysInMonth; day++) {
        const currentDayDate = new Date(year, month, day);
        const isToday = isDateToday(currentDayDate);
        const dayEvents = getEventsForDate(currentDayDate);
        
        html += `
            <div class="min-h-[100px] p-2 bg-white dark:bg-gray-700 rounded border ${isToday ? 'border-primary border-2' : 'border-gray-200 dark:border-gray-600'} hover:shadow-md cursor-pointer" onclick="selectDate('${currentDayDate.toISOString()}')">
                <div class="text-sm font-semibold mb-1 ${isToday ? 'text-primary' : 'text-gray-900 dark:text-white'}">${day}</div>
                <div class="space-y-1">
                    ${dayEvents.slice(0, 3).map(event => `
                        <div class="text-xs p-1 rounded truncate ${getEventClass(event)}" 
                             style="background-color: ${event.color || '#3b82f6'}20; border-left: 3px solid ${event.color || '#3b82f6'}"
                             onclick="event.stopPropagation(); showEventDetail(${event.id})">
                            ${event.title}
                        </div>
                    `).join('')}
                    ${dayEvents.length > 3 ? `<div class="text-xs text-gray-500">+${dayEvents.length - 3} more</div>` : ''}
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    container.innerHTML = html;
}

function renderWeekView(container) {
    const startOfWeek = getStartOfWeek(currentDate);
    const days = [];
    
    for (let i = 0; i < 7; i++) {
        const day = new Date(startOfWeek);
        day.setDate(startOfWeek.getDate() + i);
        days.push(day);
    }
    
    let html = '<div class="grid grid-cols-7 gap-2">';
    
    days.forEach(day => {
        const isToday = isDateToday(day);
        const dayEvents = getEventsForDate(day);
        
        html += `
            <div class="bg-white dark:bg-gray-700 rounded-lg p-3 ${isToday ? 'ring-2 ring-primary' : ''}">
                <div class="text-center mb-3">
                    <div class="text-xs text-gray-500 dark:text-gray-400">${day.toLocaleDateString('en-US', { weekday: 'short' })}</div>
                    <div class="text-xl font-bold ${isToday ? 'text-primary' : 'text-gray-900 dark:text-white'}">${day.getDate()}</div>
                </div>
                <div class="space-y-2">
                    ${dayEvents.map(event => `
                        <div class="text-sm p-2 rounded cursor-pointer hover:shadow" 
                             style="background-color: ${event.color || '#3b82f6'}20; border-left: 3px solid ${event.color || '#3b82f6'}"
                             onclick="showEventDetail(${event.id})">
                            <div class="font-semibold">${event.title}</div>
                            <div class="text-xs text-gray-600">${formatEventTime(event)}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function renderDayView(container) {
    const dayEvents = getEventsForDate(currentDate).sort((a, b) => {
        return new Date(a.start_datetime) - new Date(b.start_datetime);
    });
    
    let html = `
        <div class="bg-white dark:bg-gray-700 rounded-lg p-6">
            <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">
                ${currentDate.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}
            </h3>
            <div class="space-y-3">
                ${dayEvents.length > 0 ? dayEvents.map(event => `
                    <div class="p-4 rounded-lg cursor-pointer hover:shadow-md" 
                         style="background-color: ${event.color || '#3b82f6'}10; border-left: 4px solid ${event.color || '#3b82f6'}"
                         onclick="showEventDetail(${event.id})">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="font-semibold text-lg">${event.title}</h4>
                                ${event.description ? `<p class="text-sm text-gray-600 dark:text-gray-400 mt-1">${event.description}</p>` : ''}
                                <p class="text-sm text-gray-500 mt-2">
                                    <i class="fas fa-clock mr-1"></i> ${formatEventTime(event)}
                                </p>
                                ${event.location ? `<p class="text-sm text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i> ${event.location}</p>` : ''}
                            </div>
                            <span class="px-3 py-1 text-xs rounded-full bg-gray-200 dark:bg-gray-600">${event.source || 'Custom'}</span>
                        </div>
                    </div>
                `).join('') : '<p class="text-gray-500 text-center py-8">No events scheduled for this day</p>'}
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

function getEventsForDate(date) {
    return events.filter(event => {
        if (event.source && !selectedFilters.includes(event.source)) return false;
        
        const eventDate = new Date(event.start_time);
        return eventDate.toDateString() === date.toDateString();
    });
}

function isDateToday(date) {
    const today = new Date();
    return date.toDateString() === today.toDateString();
}

function getStartOfWeek(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day;
    return new Date(d.setDate(diff));
}

function formatEventTime(event) {
    const start = new Date(event.start_time);
    const end = event.end_time ? new Date(event.end_time) : null;
    
    if (event.is_all_day) {
        return 'All day';
    }
    
    if (end) {
        return `${start.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })} - ${end.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })}`;
    }
    return start.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}

function getEventClass(event) {
    const classes = {
        'custom': 'text-blue-700',
        'task': 'text-purple-700',
        'bill': 'text-red-700',
        'birthday': 'text-pink-700',
        'gym': 'text-green-700'
    };
    return classes[event.source] || 'text-gray-700';
}

function updatePeriodTitle() {
    const titleEl = document.getElementById('currentPeriod');
    if (!titleEl) return;
    
    if (currentView === 'month') {
        titleEl.textContent = currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    } else if (currentView === 'week') {
        const startOfWeek = getStartOfWeek(currentDate);
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);
        titleEl.textContent = `${startOfWeek.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${endOfWeek.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
    } else {
        titleEl.textContent = currentDate.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
    }
}

function navigate(direction) {
    if (currentView === 'month') {
        currentDate.setMonth(currentDate.getMonth() + direction);
    } else if (currentView === 'week') {
        currentDate.setDate(currentDate.getDate() + (direction * 7));
    } else {
        currentDate.setDate(currentDate.getDate() + direction);
    }
    renderCalendar();
}

function goToToday() {
    currentDate = new Date();
    renderCalendar();
}

function selectDate(dateStr) {
    const date = new Date(dateStr);
    currentDate = date;
    if (currentView !== 'day') {
        currentView = 'day';
        document.getElementById('viewMode').value = 'day';
    }
    renderCalendar();
}

function showAddEventModal() {
    document.getElementById('eventId').value = '';
    document.getElementById('eventForm').reset();
    document.getElementById('modalTitle').textContent = 'Add Event';
    document.getElementById('addEventModal').classList.remove('hidden');
}

function closeAddEventModal() {
    document.getElementById('addEventModal').classList.add('hidden');
}

function setupColorPickers() {
    document.querySelectorAll('.color-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('border-gray-800', 'dark:border-white'));
            this.classList.add('border-gray-800', 'dark:border-white');
            selectedColor = this.dataset.color;
            document.getElementById('eventColor').value = selectedColor;
        });
    });
}

async function saveEvent(e) {
    e.preventDefault();
    
    const eventData = {
        id: document.getElementById('eventId').value,
        title: document.getElementById('eventTitle').value,
        description: document.getElementById('eventDescription').value,
        start_time: document.getElementById('eventStart').value,
        end_time: document.getElementById('eventEnd').value,
        is_all_day: document.getElementById('eventAllDay').checked,
        location: document.getElementById('eventLocation').value,
        color: document.getElementById('eventColor').value,
        reminder_minutes: document.getElementById('eventReminder').value,
        source: 'custom'
    };
    
    try {
        const action = eventData.id ? 'update' : 'create';
        const response = await fetch(`/api/calendar.php?action=${action}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify(eventData)
        });
        
        const result = await response.json();
        if (result.success) {
            closeAddEventModal();
            loadEvents();
            if (typeof showToast === 'function') {
                showToast('success', 'Success', eventData.id ? 'Event updated successfully' : 'Event created successfully');
            }
        } else {
            alert('Error: ' + (result.message || 'Failed to save event'));
        }
    } catch (error) {
        console.error('Error saving event:', error);
        alert('Failed to save event');
    }
}

function showEventDetail(eventId) {
    const event = events.find(e => e.id === eventId);
    if (!event) return;
    
    selectedEventId = eventId;
    
    document.getElementById('detailTitle').textContent = event.title;
    document.getElementById('detailTime').textContent = formatEventTime(event);
    document.getElementById('detailDescription').innerHTML = event.description ? 
        `<p class="text-gray-600 dark:text-gray-400">${event.description}</p>` : '';
    document.getElementById('detailLocation').innerHTML = event.location ? 
        `<p class="text-sm text-gray-500"><i class="fas fa-map-marker-alt mr-2"></i>${event.location}</p>` : '';
    document.getElementById('detailSource').textContent = `Source: ${event.source || 'Custom'}`;
    
    if (event.source !== 'custom') {
        document.getElementById('detailActions').style.display = 'none';
    } else {
        document.getElementById('detailActions').style.display = 'flex';
    }
    
    document.getElementById('eventDetailModal').classList.remove('hidden');
}

function closeEventDetail() {
    document.getElementById('eventDetailModal').classList.add('hidden');
    selectedEventId = null;
}

function editEvent() {
    const event = events.find(e => e.id === selectedEventId);
    if (!event) return;
    
    document.getElementById('eventId').value = event.id;
    document.getElementById('eventTitle').value = event.title;
    document.getElementById('eventDescription').value = event.description || '';
    document.getElementById('eventStart').value = event.start_time.slice(0, 16);
    document.getElementById('eventEnd').value = event.end_time ? event.end_time.slice(0, 16) : '';
    document.getElementById('eventAllDay').checked = event.is_all_day || false;
    document.getElementById('eventLocation').value = event.location || '';
    document.getElementById('eventColor').value = event.color || '#3b82f6';
    document.getElementById('eventReminder').value = event.reminder_minutes || 30;
    
    document.getElementById('modalTitle').textContent = 'Edit Event';
    closeEventDetail();
    document.getElementById('addEventModal').classList.remove('hidden');
}

async function deleteEvent() {
    if (!selectedEventId) return;
    
    if (!confirm('Are you sure you want to delete this event?')) return;
    
    try {
        const response = await fetch(`/api/calendar.php?action=delete&id=${selectedEventId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
            }
        });
        
        const result = await response.json();
        if (result.success) {
            closeEventDetail();
            loadEvents();
            if (typeof showToast === 'function') {
                showToast('success', 'Success', 'Event deleted successfully');
            }
        } else {
            alert('Error: ' + (result.message || 'Failed to delete event'));
        }
    } catch (error) {
        console.error('Error deleting event:', error);
        alert('Failed to delete event');
    }
}

function initializeFilters() {
    document.querySelectorAll('.filter-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const source = this.dataset.source;
            if (this.checked) {
                if (!selectedFilters.includes(source)) {
                    selectedFilters.push(source);
                }
            } else {
                selectedFilters = selectedFilters.filter(f => f !== source);
            }
            renderCalendar();
        });
    });
}

function toggleFilterDropdown() {
    const dropdown = document.getElementById('filterDropdown');
    dropdown.classList.toggle('hidden');
}

document.addEventListener('click', function(e) {
    const filterBtn = document.getElementById('filterBtn');
    const filterDropdown = document.getElementById('filterDropdown');
    if (filterBtn && filterDropdown && !filterBtn.contains(e.target) && !filterDropdown.contains(e.target)) {
        filterDropdown.classList.add('hidden');
    }
});

async function checkSyncStatus() {
    try {
        const response = await fetch('/api/calendar_sync.php?action=status');
        const result = await response.json();
        if (result.success && result.connected) {
            document.getElementById('syncStatusCard').classList.remove('hidden');
            if (result.last_sync) {
                document.getElementById('lastSyncTime').textContent = new Date(result.last_sync).toLocaleString();
            }
        }
    } catch (error) {
        console.error('Error checking sync status:', error);
    }
}

function showCalendarSyncModal() {
    const modal = document.getElementById('calendarSyncModal');
    if (modal) modal.classList.remove('hidden');
}

async function syncCalendar() {
    try {
        if (typeof showToast === 'function') {
            showToast('info', 'Syncing', 'Syncing with Google Calendar...');
        }
        const response = await fetch('/api/calendar_sync.php?action=sync', { method: 'POST' });
        const result = await response.json();
        if (result.success) {
            loadEvents();
            if (typeof showToast === 'function') {
                showToast('success', 'Success', 'Calendar synced successfully');
            }
        }
    } catch (error) {
        console.error('Error syncing calendar:', error);
    }
}

async function disconnectCalendar() {
    if (!confirm('Are you sure you want to disconnect Google Calendar?')) return;
    
    try {
        const response = await fetch('/api/calendar_sync.php?action=disconnect', { method: 'POST' });
        const result = await response.json();
        if (result.success) {
            document.getElementById('syncStatusCard').classList.add('hidden');
            if (typeof showToast === 'function') {
                showToast('success', 'Success', 'Google Calendar disconnected');
            }
        }
    } catch (error) {
        console.error('Error disconnecting calendar:', error);
    }
}
