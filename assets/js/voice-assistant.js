// Voice Assistant - Speech Recognition Module
let recognition = null;
let isListening = false;

// Initialize Speech Recognition
if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = true;
    recognition.lang = 'en-US';
} else {
    console.error('Speech recognition not supported');
    showToast('Voice commands are not supported in this browser', 'error');
}

// Toggle Voice Recognition
function toggleVoiceRecognition() {
    if (!recognition) {
        showToast('Speech recognition not supported in this browser', 'error');
        return;
    }

    if (isListening) {
        stopListening();
    } else {
        startListening();
    }
}

// Start Listening
function startListening() {
    if (!recognition) return;

    isListening = true;
    recognition.start();
    
    const micButton = document.getElementById('micButton');
    const micIcon = document.getElementById('micIcon');
    const voiceStatus = document.getElementById('voiceStatus');
    
    micButton.classList.add('animate-pulse');
    micIcon.classList.add('text-red-500');
    voiceStatus.textContent = 'Listening...';
    
    recognition.onresult = handleSpeechResult;
    recognition.onerror = handleSpeechError;
    recognition.onend = handleSpeechEnd;
}

// Stop Listening
function stopListening() {
    if (!recognition) return;

    isListening = false;
    recognition.stop();
    
    const micButton = document.getElementById('micButton');
    const micIcon = document.getElementById('micIcon');
    const voiceStatus = document.getElementById('voiceStatus');
    
    micButton.classList.remove('animate-pulse');
    micIcon.classList.remove('text-red-500');
    voiceStatus.textContent = 'Click to start listening';
}

// Handle Speech Result
function handleSpeechResult(event) {
    const transcript = Array.from(event.results)
        .map(result => result[0].transcript)
        .join('');
    
    document.getElementById('voiceTranscript').textContent = transcript;
    
    if (event.results[0].isFinal) {
        processVoiceCommand(transcript);
    }
}

// Handle Speech Error
function handleSpeechError(event) {
    console.error('Speech recognition error:', event.error);
    showToast('Voice recognition error: ' + event.error, 'error');
    stopListening();
}

// Handle Speech End
function handleSpeechEnd() {
    stopListening();
}

// Process Voice Command
async function processVoiceCommand(command) {
    const lowerCommand = command.toLowerCase().trim();
    
    addCommandToHistory(command);
    
    try {
        // Task commands
        if (lowerCommand.startsWith('add task')) {
            const taskName = command.replace(/add task/i, '').trim();
            await executeCommand('add_task', { name: taskName });
        } 
        else if (lowerCommand.includes('show') && lowerCommand.includes('task')) {
            window.location.href = '/kanban.php';
        }
        else if (lowerCommand.startsWith('complete task')) {
            const taskName = command.replace(/complete task/i, '').trim();
            await executeCommand('complete_task', { name: taskName });
        }
        // Goal commands
        else if (lowerCommand.includes('show') && lowerCommand.includes('goal')) {
            window.location.href = '/goals.php';
        }
        else if (lowerCommand.startsWith('track habit')) {
            const habitName = command.replace(/track habit/i, '').trim();
            await executeCommand('track_habit', { name: habitName });
        }
        // Finance commands
        else if (lowerCommand.includes('balance')) {
            window.location.href = '/finance.php';
        }
        else if (lowerCommand.startsWith('add expense')) {
            const expenseMatch = command.match(/add expense (\d+(?:\.\d+)?)(?: for (.+))?/i);
            if (expenseMatch) {
                const amount = parseFloat(expenseMatch[1]);
                const category = expenseMatch[2] || 'General';
                await executeCommand('add_expense', { amount, category });
            }
        }
        // Report commands
        else if (lowerCommand.includes('send') && lowerCommand.includes('report')) {
            await executeCommand('send_report', {});
        }
        else if (lowerCommand.includes('show') && lowerCommand.includes('analytic')) {
            window.location.href = '/analytics_dashboard.php';
        }
        else if (lowerCommand.includes('summary') || lowerCommand.includes('briefing')) {
            window.location.href = '/ai_briefing.php';
        }
        else {
            showToast('Command not recognized: ' + command, 'warning');
        }
    } catch (error) {
        console.error('Error processing command:', error);
        showToast('Error executing command', 'error');
    }
}

// Execute Command via API
async function executeCommand(action, data) {
    try {
        const response = await fetch('/api/voice_commands.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action, ...data })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message || 'Command executed successfully', 'success');
            speak(result.message || 'Done');
        } else {
            showToast(result.message || 'Command failed', 'error');
        }
    } catch (error) {
        console.error('API error:', error);
        showToast('Failed to execute command', 'error');
    }
}

// Text-to-Speech Response
function speak(text) {
    if ('speechSynthesis' in window) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'en-US';
        utterance.rate = 1.0;
        utterance.pitch = 1.0;
        window.speechSynthesis.speak(utterance);
    }
}

// Add Command to History
function addCommandToHistory(command) {
    const historyContainer = document.getElementById('commandHistory');
    
    if (historyContainer.querySelector('.text-center')) {
        historyContainer.innerHTML = '';
    }
    
    const commandItem = document.createElement('div');
    commandItem.className = 'flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg';
    commandItem.innerHTML = `
        <i class="fas fa-microphone text-primary"></i>
        <div class="flex-1">
            <p class="text-sm text-gray-900 dark:text-white">${escapeHtml(command)}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">${new Date().toLocaleTimeString()}</p>
        </div>
    `;
    
    historyContainer.insertBefore(commandItem, historyContainer.firstChild);
    
    // Keep only last 10 commands
    const items = historyContainer.querySelectorAll('div');
    if (items.length > 10) {
        items[items.length - 1].remove();
    }
}

// Utility function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Show Toast Notification
function showToast(message, type = 'info') {
    if (typeof window.showNotification === 'function') {
        window.showNotification(message, type);
    } else {
        alert(message);
    }
}
