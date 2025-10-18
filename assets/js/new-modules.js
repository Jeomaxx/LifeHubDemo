// Comprehensive JavaScript for New Modules
// Handles Debts, Recipes, Vehicles, Medications, Symptoms, Contacts, Events

// ========== UTILITY FUNCTIONS ==========
const API = {
    async call(endpoint, action, data = null, method = 'POST') {
        const url = `/api/${endpoint}.php?action=${action}`;
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
            }
        };
        
        if (data && method === 'POST') {
            options.body = JSON.stringify(data);
        }
        
        const response = await fetch(url, options);
        return await response.json();
    },
    
    get(endpoint, action) {
        return this.call(endpoint, action, null, 'GET');
    },
    
    post(endpoint, action, data) {
        return this.call(endpoint, action, data, 'POST');
    }
};

function showModal(modalId) {
    document.getElementById(modalId)?.classList.remove('hidden');
}

function hideModal(modalId) {
    document.getElementById(modalId)?.classList.add('hidden');
}

function showToast(message, type = 'success') {
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
    } else {
        alert(message);
    }
}

// ========== DEBTS MODULE ==========
window.showAddDebtModal = () => showModal('addDebtModal');
window.closeAddDebtModal = () => hideModal('addDebtModal');

if (document.getElementById('debtForm')) {
    document.getElementById('debtForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            id: document.getElementById('debtId').value,
            name: document.getElementById('debtName').value,
            debt_type: document.getElementById('debtType').value,
            principal_amount: parseFloat(document.getElementById('principalAmount').value),
            current_balance: parseFloat(document.getElementById('currentBalance').value),
            interest_rate: parseFloat(document.getElementById('interestRate').value),
            minimum_payment: parseFloat(document.getElementById('minimumPayment').value)
        };
        
        const action = formData.id ? 'update' : 'add';
        const result = await API.post('debts', action, formData);
        
        if (result.success) {
            showToast(result.message);
            window.location.reload();
        } else {
            showToast(result.message, 'error');
        }
    });
}

window.deleteDebt = async (id) => {
    if (!confirm('Delete this debt?')) return;
    const result = await API.post('debts', 'delete', { id });
    if (result.success) {
        showToast(result.message);
        window.location.reload();
    }
};

window.recordPayment = (debtId) => {
    document.getElementById('paymentDebtId').value = debtId;
    showModal('paymentModal');
};

window.closePaymentModal = () => hideModal('paymentModal');

if (document.getElementById('paymentForm')) {
    document.getElementById('paymentForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            debt_id: parseInt(document.getElementById('paymentDebtId').value),
            amount: parseFloat(document.getElementById('paymentAmount').value),
            payment_date: document.getElementById('paymentDate').value,
            notes: document.getElementById('paymentNotes').value
        };
        
        const result = await API.post('debts', 'record_payment', formData);
        if (result.success) {
            showToast(result.message);
            window.location.reload();
        }
    });
}

// ========== RECIPES MODULE ==========
window.showAddRecipeModal = () => showModal('addRecipeModal');
window.closeAddRecipeModal = () => hideModal('addRecipeModal');

if (document.getElementById('recipeForm')) {
    document.getElementById('recipeForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            id: document.getElementById('recipeId').value,
            name: document.getElementById('recipeName').value,
            description: document.getElementById('recipeDescription').value,
            category: document.getElementById('recipeCategory').value,
            cuisine: document.getElementById('recipeCuisine').value,
            prep_time: parseInt(document.getElementById('prepTime').value) || 0,
            cook_time: parseInt(document.getElementById('cookTime').value) || 0,
            servings: parseInt(document.getElementById('servings').value) || 1,
            ingredients: document.getElementById('ingredients').value,
            instructions: document.getElementById('instructions').value,
            image_url: document.getElementById('imageUrl').value
        };
        
        const action = formData.id ? 'update' : 'add';
        const result = await API.post('recipes', action, formData);
        
        if (result.success) {
            showToast(result.message);
            window.location.reload();
        }
    });
}

// ========== VEHICLES MODULE ==========
window.showAddVehicleModal = () => showModal('addVehicleModal');
window.closeAddVehicleModal = () => hideModal('addVehicleModal');

if (document.getElementById('vehicleForm')) {
    document.getElementById('vehicleForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            id: document.getElementById('vehicleId').value,
            make: document.getElementById('make').value,
            model: document.getElementById('model').value,
            year: parseInt(document.getElementById('year').value) || null,
            vin: document.getElementById('vin').value,
            license_plate: document.getElementById('licensePlate').value,
            current_mileage: parseInt(document.getElementById('currentMileage').value) || 0,
            color: document.getElementById('color').value
        };
        
        const action = formData.id ? 'update' : 'add';
        const result = await API.post('vehicles', action, formData);
        
        if (result.success) {
            showToast(result.message);
            window.location.reload();
        }
    });
}

window.deleteVehicle = async (id) => {
    if (!confirm('Delete this vehicle?')) return;
    const result = await API.post('vehicles', 'delete', { id });
    if (result.success) {
        showToast(result.message);
        window.location.reload();
    }
};

// ========== MEDICATIONS MODULE ==========
window.showAddMedicationModal = () => showModal('addMedicationModal');
window.closeAddMedicationModal = () => hideModal('addMedicationModal');

if (document.getElementById('medicationForm')) {
    document.getElementById('medicationForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            id: document.getElementById('medId').value,
            name: document.getElementById('medName').value,
            medication_type: document.getElementById('medType').value,
            dosage: document.getElementById('dosage').value,
            frequency: document.getElementById('frequency').value,
            time_of_day: document.getElementById('timeOfDay').value,
            purpose: document.getElementById('purpose').value,
            current_quantity: parseInt(document.getElementById('currentQuantity').value) || null,
            refill_reminder_quantity: parseInt(document.getElementById('refillReminder').value) || null,
            prescribing_doctor: document.getElementById('doctor').value
        };
        
        const action = formData.id ? 'update' : 'add';
        const result = await API.post('medications', action, formData);
        
        if (result.success) {
            showToast(result.message);
            window.location.reload();
        }
    });
}

window.deleteMedication = async (id) => {
    if (!confirm('Delete this medication?')) return;
    const result = await API.post('medications', 'delete', { id });
    if (result.success) {
        showToast(result.message);
        window.location.reload();
    }
};

window.logIntake = async (medicationId) => {
    const result = await API.post('medications', 'log_intake', {
        medication_id: medicationId,
        log_date: new Date().toISOString().split('T')[0],
        log_time: new Date().toTimeString().split(' ')[0]
    });
    if (result.success) {
        showToast('Intake logged!');
    }
};

// ========== SYMPTOMS MODULE ==========
window.showAddSymptomModal = () => showModal('addSymptomModal');
window.closeAddSymptomModal = () => hideModal('addSymptomModal');
window.showLogSymptomModal = () => showModal('logSymptomModal');
window.closeLogSymptomModal = () => hideModal('logSymptomModal');

if (document.getElementById('symptomForm')) {
    document.getElementById('symptomForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            name: document.getElementById('symptomName').value,
            category: document.getElementById('symptomCategory').value
        };
        
        const result = await API.post('symptoms', 'add', formData);
        if (result.success) {
            showToast(result.message);
            window.location.reload();
        }
    });
}

if (document.getElementById('logSymptomForm')) {
    document.getElementById('logSymptomForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            symptom_id: parseInt(document.getElementById('logSymptomId').value),
            log_date: document.getElementById('logDate').value,
            log_time: document.getElementById('logTime').value,
            severity: parseInt(document.getElementById('severity').value),
            duration_minutes: parseInt(document.getElementById('duration').value) || null,
            triggers: document.getElementById('triggers').value,
            notes: document.getElementById('symptomNotes').value
        };
        
        const result = await API.post('symptoms', 'log', formData);
        if (result.success) {
            showToast(result.message);
            window.location.reload();
        }
    });
}

window.deleteSymptom = async (id) => {
    if (!confirm('Delete this symptom type?')) return;
    const result = await API.post('symptoms', 'delete', { id });
    if (result.success) {
        showToast(result.message);
        window.location.reload();
    }
};

window.logSymptomForType = (symptomId) => {
    document.getElementById('logSymptomId').value = symptomId;
    showModal('logSymptomModal');
};

// ========== CONTACTS MODULE ==========
window.showAddContactModal = () => showModal('addContactModal');
window.closeAddContactModal = () => hideModal('addContactModal');

if (document.getElementById('contactForm')) {
    document.getElementById('contactForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            id: document.getElementById('contactId').value,
            name: document.getElementById('contactName').value,
            relationship: document.getElementById('relationship').value,
            importance: document.getElementById('importance').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            company: document.getElementById('company').value,
            job_title: document.getElementById('jobTitle').value,
            birthday: document.getElementById('birthday').value,
            address: document.getElementById('address').value,
            notes: document.getElementById('contactNotes').value,
            is_favorite: document.getElementById('isFavorite').checked
        };
        
        const action = formData.id ? 'update' : 'add';
        const result = await API.post('contacts', action, formData);
        
        if (result.success) {
            showToast(result.message);
            window.location.reload();
        }
    });
}

// ========== EVENTS MODULE ==========
window.showAddEventModal = () => showModal('addEventModal');
window.closeAddEventModal = () => hideModal('addEventModal');

if (document.getElementById('eventForm')) {
    document.getElementById('eventForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            id: document.getElementById('eventId').value,
            name: document.getElementById('eventName').value,
            event_type: document.getElementById('eventType').value,
            description: document.getElementById('eventDescription').value,
            event_date: document.getElementById('eventDate').value,
            event_time: document.getElementById('eventTime').value,
            location: document.getElementById('eventLocation').value,
            budget: parseFloat(document.getElementById('eventBudget').value) || 0
        };
        
        const action = formData.id ? 'update' : 'add';
        const result = await API.post('events', action, formData);
        
        if (result.success) {
            showToast(result.message);
            window.location.reload();
        }
    });
}

window.deleteEvent = async (id) => {
    if (!confirm('Delete this event?')) return;
    const result = await API.post('events', 'delete', { id });
    if (result.success) {
        showToast(result.message);
        window.location.reload();
    }
};

// Initialize filter buttons
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        
        document.querySelectorAll('.recipe-card, .contact-card').forEach(card => {
            if (filter === 'all' || card.dataset.category === filter || card.dataset.relationship === filter || card.dataset.importance === filter) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

console.log('New modules JavaScript loaded successfully');
