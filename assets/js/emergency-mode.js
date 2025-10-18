// Emergency Mode Functions

async function activateEmergency() {
    if (!confirm('ACTIVATE EMERGENCY MODE?\n\nThis will immediately notify all your emergency contacts with your location and health information.\n\nOnly activate in genuine emergencies.')) {
        return;
    }
    
    try {
        // Get current location
        const position = await getCurrentLocation();
        
        // Send emergency alert
        const response = await fetch('/api/emergency.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'activate',
                latitude: position.latitude,
                longitude: position.longitude,
                timestamp: new Date().toISOString()
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showEmergencyConfirmation(result);
        } else {
            alert('Failed to activate emergency mode: ' + result.message);
        }
    } catch (error) {
        console.error('Emergency activation error:', error);
        alert('Error activating emergency mode');
    }
}

function getCurrentLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error('Geolocation not supported'));
            return;
        }
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                resolve({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                });
            },
            (error) => {
                reject(error);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });
}

function showEmergencyConfirmation(result) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg p-8 max-w-md w-full text-center">
            <div class="w-20 h-20 bg-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check text-white text-4xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Emergency Mode Activated</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">${result.message}</p>
            <p class="text-sm text-gray-500 dark:text-gray-500 mb-6">
                ${result.contacts_notified} contact(s) have been notified with your location and health information
            </p>
            <button onclick="this.closest('.fixed').remove()" class="btn bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-bold">
                Close
            </button>
        </div>
    `;
    document.body.appendChild(modal);
}

async function addEmergencyContact() {
    const name = prompt('Contact Name:');
    if (!name) return;
    
    const phone = prompt('Phone Number:');
    if (!phone) return;
    
    const relationship = prompt('Relationship (optional):');
    
    try {
        const response = await fetch('/api/emergency.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'add_contact',
                name,
                phone,
                relationship: relationship || ''
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert('Failed to add contact: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to add contact');
    }
}

async function removeContact(contactId) {
    if (!confirm('Remove this emergency contact?')) return;
    
    try {
        const response = await fetch('/api/emergency.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'remove_contact',
                contact_id: contactId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert('Failed to remove contact');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to remove contact');
    }
}

async function testNotify(contactId) {
    if (!confirm('Send a test notification to this contact?')) return;
    
    try {
        const response = await fetch('/api/emergency.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'test_notify',
                contact_id: contactId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Test notification sent successfully');
        } else {
            alert('Failed to send test notification');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to send notification');
    }
}

// Health Profile Form
document.getElementById('healthProfileForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/api/emergency.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'save_health_profile',
                ...data
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Health profile saved successfully');
        } else {
            alert('Failed to save health profile');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to save health profile');
    }
});
