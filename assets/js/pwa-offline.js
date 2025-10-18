// PWA Offline Mode with IndexedDB
const DB_NAME = 'LifeAtlasOfflineDB';
const DB_VERSION = 1;
let db = null;

// Initialize IndexedDB
function initializeDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            db = request.result;
            resolve(db);
        };
        
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            
            // Create object stores
            if (!db.objectStoreNames.contains('tasks')) {
                const taskStore = db.createObjectStore('tasks', { keyPath: 'id', autoIncrement: true });
                taskStore.createIndex('status', 'status', { unique: false });
                taskStore.createIndex('syncStatus', 'syncStatus', { unique: false });
            }
            
            if (!db.objectStoreNames.contains('notes')) {
                const noteStore = db.createObjectStore('notes', { keyPath: 'id', autoIncrement: true });
                noteStore.createIndex('syncStatus', 'syncStatus', { unique: false });
            }
            
            if (!db.objectStoreNames.contains('calendar')) {
                const calendarStore = db.createObjectStore('calendar', { keyPath: 'id', autoIncrement: true });
                calendarStore.createIndex('date', 'date', { unique: false });
                calendarStore.createIndex('syncStatus', 'syncStatus', { unique: false });
            }
            
            if (!db.objectStoreNames.contains('syncQueue')) {
                db.createObjectStore('syncQueue', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

// Save Data Offline
async function saveOfflineData(storeName, data) {
    if (!db) await initializeDB();
    
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        
        data.syncStatus = 'pending';
        data.lastModified = new Date().toISOString();
        
        const request = store.add(data);
        
        request.onsuccess = () => {
            addToSyncQueue(storeName, 'add', request.result);
            resolve(request.result);
        };
        request.onerror = () => reject(request.error);
    });
}

// Get Offline Data
async function getOfflineData(storeName, filter = null) {
    if (!db) await initializeDB();
    
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readonly');
        const store = transaction.objectStore(storeName);
        const request = store.getAll();
        
        request.onsuccess = () => {
            let data = request.result;
            if (filter) {
                data = data.filter(filter);
            }
            resolve(data);
        };
        request.onerror = () => reject(request.error);
    });
}

// Update Offline Data
async function updateOfflineData(storeName, id, updates) {
    if (!db) await initializeDB();
    
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        const getRequest = store.get(id);
        
        getRequest.onsuccess = () => {
            const data = getRequest.result;
            if (!data) {
                reject(new Error('Record not found'));
                return;
            }
            
            Object.assign(data, updates);
            data.syncStatus = 'pending';
            data.lastModified = new Date().toISOString();
            
            const updateRequest = store.put(data);
            updateRequest.onsuccess = () => {
                addToSyncQueue(storeName, 'update', id);
                resolve(data);
            };
            updateRequest.onerror = () => reject(updateRequest.error);
        };
        getRequest.onerror = () => reject(getRequest.error);
    });
}

// Delete Offline Data
async function deleteOfflineData(storeName, id) {
    if (!db) await initializeDB();
    
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.delete(id);
        
        request.onsuccess = () => {
            addToSyncQueue(storeName, 'delete', id);
            resolve();
        };
        request.onerror = () => reject(request.error);
    });
}

// Add to Sync Queue
async function addToSyncQueue(storeName, action, recordId) {
    if (!db) await initializeDB();
    
    const transaction = db.transaction(['syncQueue'], 'readwrite');
    const store = transaction.objectStore('syncQueue');
    
    store.add({
        storeName,
        action,
        recordId,
        timestamp: new Date().toISOString()
    });
}

// Sync with Server
async function syncWithServer() {
    if (!navigator.onLine) {
        console.log('Offline: sync postponed');
        return;
    }
    
    if (!db) await initializeDB();
    
    try {
        const transaction = db.transaction(['syncQueue'], 'readonly');
        const store = transaction.objectStore('syncQueue');
        const queueItems = await new Promise((resolve, reject) => {
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
        
        console.log(`Syncing ${queueItems.length} items...`);
        
        for (const item of queueItems) {
            await syncItem(item);
        }
        
        // Clear sync queue after successful sync
        const clearTransaction = db.transaction(['syncQueue'], 'readwrite');
        const clearStore = clearTransaction.objectStore('syncQueue');
        clearStore.clear();
        
        console.log('Sync completed successfully');
        showSyncNotification('All changes synced successfully');
    } catch (error) {
        console.error('Sync error:', error);
        showSyncNotification('Sync failed. Will retry later.', 'error');
    }
}

// Sync Individual Item
async function syncItem(item) {
    const { storeName, action, recordId } = item;
    
    const dataTransaction = db.transaction([storeName], 'readonly');
    const dataStore = dataTransaction.objectStore(storeName);
    
    return new Promise((resolve, reject) => {
        const request = dataStore.get(recordId);
        
        request.onsuccess = async () => {
            const data = request.result;
            if (!data) {
                resolve();
                return;
            }
            
            try {
                const apiEndpoint = getApiEndpoint(storeName);
                const response = await fetch(apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ action, ...data })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Mark as synced
                    const updateTransaction = db.transaction([storeName], 'readwrite');
                    const updateStore = updateTransaction.objectStore(storeName);
                    data.syncStatus = 'synced';
                    updateStore.put(data);
                }
                
                resolve(result);
            } catch (error) {
                reject(error);
            }
        };
        
        request.onerror = () => reject(request.error);
    });
}

// Get API Endpoint for Store
function getApiEndpoint(storeName) {
    const endpoints = {
        'tasks': '/api/tasks.php',
        'notes': '/api/notes.php',
        'calendar': '/api/calendar.php'
    };
    return endpoints[storeName] || '/api/sync.php';
}

// Show Sync Notification
function showSyncNotification(message, type = 'success') {
    if ('showNotification' in window) {
        window.showNotification(message, type);
    } else {
        console.log(`Sync: ${message}`);
    }
}

// Check Online Status
function updateOnlineStatus() {
    const statusElement = document.getElementById('onlineStatus');
    if (statusElement) {
        if (navigator.onLine) {
            statusElement.innerHTML = '<i class="fas fa-wifi"></i> Online';
            statusElement.className = 'text-green-600';
            syncWithServer();
        } else {
            statusElement.innerHTML = '<i class="fas fa-wifi-slash"></i> Offline';
            statusElement.className = 'text-red-600';
        }
    }
}

// Initialize PWA
async function initializePWA() {
    await initializeDB();
    
    // Listen for online/offline events
    window.addEventListener('online', () => {
        console.log('Back online');
        updateOnlineStatus();
    });
    
    window.addEventListener('offline', () => {
        console.log('Gone offline');
        updateOnlineStatus();
    });
    
    // Initial status check
    updateOnlineStatus();
    
    // Periodic sync
    setInterval(() => {
        if (navigator.onLine) {
            syncWithServer();
        }
    }, 60000); // Sync every minute when online
}

// Initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePWA);
} else {
    initializePWA();
}

// Export functions for global use
window.offlineDB = {
    save: saveOfflineData,
    get: getOfflineData,
    update: updateOfflineData,
    delete: deleteOfflineData,
    sync: syncWithServer
};
