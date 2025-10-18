// Receipt Scanner - OCR Module
let currentImageFile = null;
let cameraStream = null;

// Handle File Upload
function handleFileUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    currentImageFile = file;
    const reader = new FileReader();
    
    reader.onload = function(e) {
        document.getElementById('previewImg').src = e.target.result;
        document.getElementById('imagePreview').classList.remove('hidden');
        document.getElementById('scanResults').classList.add('hidden');
    };
    
    reader.readAsDataURL(file);
}

// Open Camera
async function openCamera() {
    const modal = document.getElementById('cameraModal');
    const video = document.getElementById('cameraVideo');
    
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'environment' } 
        });
        video.srcObject = cameraStream;
        modal.classList.remove('hidden');
    } catch (error) {
        console.error('Camera error:', error);
        showToast('Could not access camera', 'error');
    }
}

// Close Camera
function closeCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
    document.getElementById('cameraModal').classList.add('hidden');
}

// Capture Photo
function capturePhoto() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);
    
    canvas.toBlob(function(blob) {
        currentImageFile = blob;
        const url = URL.createObjectURL(blob);
        
        document.getElementById('previewImg').src = url;
        document.getElementById('imagePreview').classList.remove('hidden');
        document.getElementById('scanResults').classList.add('hidden');
        
        closeCamera();
    }, 'image/jpeg');
}

// Scan Receipt with OCR
async function scanReceipt() {
    if (!currentImageFile) {
        showToast('Please select an image first', 'error');
        return;
    }
    
    document.getElementById('scanningLoader').classList.remove('hidden');
    document.getElementById('scanResults').classList.add('hidden');
    
    try {
        const imageUrl = URL.createObjectURL(currentImageFile);
        
        const result = await Tesseract.recognize(imageUrl, 'eng', {
            logger: m => {
                if (m.status === 'recognizing text') {
                    console.log(`OCR Progress: ${Math.round(m.progress * 100)}%`);
                }
            }
        });
        
        const text = result.data.text;
        console.log('OCR Result:', text);
        
        // Extract information from text
        const extractedData = extractReceiptData(text);
        
        // Populate form
        document.getElementById('amount').value = extractedData.amount || '';
        document.getElementById('date').value = extractedData.date || new Date().toISOString().split('T')[0];
        document.getElementById('category').value = extractedData.category || 'Other';
        document.getElementById('merchant').value = extractedData.merchant || '';
        document.getElementById('description').value = text.substring(0, 200);
        
        document.getElementById('scanningLoader').classList.add('hidden');
        document.getElementById('scanResults').classList.remove('hidden');
        
        showToast('Receipt scanned successfully', 'success');
    } catch (error) {
        console.error('OCR Error:', error);
        document.getElementById('scanningLoader').classList.add('hidden');
        showToast('Failed to scan receipt. Please try again.', 'error');
    }
}

// Extract Receipt Data from OCR Text
function extractReceiptData(text) {
    const data = {
        amount: null,
        date: null,
        category: 'Other',
        merchant: null
    };
    
    // Extract amount (look for $ or common patterns)
    const amountPatterns = [
        /\$?\s*(\d+\.\d{2})\s*(?:total|amount|balance|due)/i,
        /(?:total|amount|balance|due)\s*\$?\s*(\d+\.\d{2})/i,
        /\$\s*(\d+\.\d{2})/
    ];
    
    for (const pattern of amountPatterns) {
        const match = text.match(pattern);
        if (match) {
            data.amount = parseFloat(match[1]);
            break;
        }
    }
    
    // Extract date
    const datePatterns = [
        /(\d{1,2}\/\d{1,2}\/\d{2,4})/,
        /(\d{4}-\d{2}-\d{2})/,
        /(\d{1,2}-\d{1,2}-\d{2,4})/
    ];
    
    for (const pattern of datePatterns) {
        const match = text.match(pattern);
        if (match) {
            const dateStr = match[1];
            const date = new Date(dateStr);
            if (!isNaN(date.getTime())) {
                data.date = date.toISOString().split('T')[0];
                break;
            }
        }
    }
    
    // Extract merchant (usually at the top)
    const lines = text.split('\n').filter(line => line.trim().length > 0);
    if (lines.length > 0) {
        data.merchant = lines[0].trim().substring(0, 100);
    }
    
    // Auto-categorize based on keywords
    const lowerText = text.toLowerCase();
    if (lowerText.match(/walmart|costco|grocery|supermarket|food/)) {
        data.category = 'Groceries';
    } else if (lowerText.match(/restaurant|cafe|coffee|starbucks|mcdonald/)) {
        data.category = 'Dining';
    } else if (lowerText.match(/gas|fuel|shell|chevron|exxon/)) {
        data.category = 'Transportation';
    } else if (lowerText.match(/amazon|target|shopping|store/)) {
        data.category = 'Shopping';
    }
    
    return data;
}

// Save Expense
async function saveExpense(event) {
    event.preventDefault();
    
    const data = {
        action: 'add',
        amount: document.getElementById('amount').value,
        date: document.getElementById('date').value,
        category: document.getElementById('category').value,
        description: `${document.getElementById('merchant').value || 'Receipt'} - ${document.getElementById('description').value}`.trim(),
        type: 'expense'
    };
    
    try {
        const response = await fetch('/api/finance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Expense saved successfully', 'success');
            addToRecentScans(data);
            resetForm();
        } else {
            showToast(result.message || 'Failed to save expense', 'error');
        }
    } catch (error) {
        console.error('Save error:', error);
        showToast('Failed to save expense', 'error');
    }
}

// Add to Recent Scans
function addToRecentScans(data) {
    const container = document.getElementById('recentScans');
    
    if (container.querySelector('.text-center')) {
        container.innerHTML = '';
    }
    
    const scanItem = document.createElement('div');
    scanItem.className = 'flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg';
    scanItem.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="fas fa-receipt text-primary text-xl"></i>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">$${parseFloat(data.amount).toFixed(2)}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">${data.category} • ${data.date}</p>
            </div>
        </div>
        <span class="text-green-600 dark:text-green-400">
            <i class="fas fa-check-circle"></i>
        </span>
    `;
    
    container.insertBefore(scanItem, container.firstChild);
}

// Reset Form
function resetForm() {
    document.getElementById('expenseForm').reset();
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('scanResults').classList.add('hidden');
    currentImageFile = null;
}

// Show Toast Notification
function showToast(message, type = 'info') {
    if (typeof window.showNotification === 'function') {
        window.showNotification(message, type);
    } else {
        alert(message);
    }
}
