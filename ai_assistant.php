<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'AI Assistant';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6 h-screen flex flex-col">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-robot text-primary"></i>
                AI Assistant
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Your personal AI-powered assistant</p>
        </div>
        <a href="/ai_briefing.php" class="btn bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-newspaper"></i>
            <span>Daily Briefing</span>
        </a>
    </div>

    <!-- Chat Interface -->
    <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg shadow-sm flex flex-col">
        <!-- Chat Header -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-robot text-white"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">AI Assistant</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Online • Ready to help</p>
                </div>
            </div>
        </div>

        <!-- Chat Messages -->
        <div id="chatMessages" class="flex-1 overflow-y-auto p-6 space-y-4">
            <div class="flex gap-3">
                <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-robot text-white text-sm"></i>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 max-w-2xl">
                    <p class="text-gray-900 dark:text-white">Hello! I'm your AI assistant. I can help you with:</p>
                    <ul class="mt-2 space-y-1 text-sm text-gray-700 dark:text-gray-300">
                        <li>• Analyzing your finances and expenses</li>
                        <li>• Tracking your health and fitness goals</li>
                        <li>• Managing tasks and reminders</li>
                        <li>• Providing daily briefings and insights</li>
                        <li>• Answering questions about your data</li>
                    </ul>
                    <p class="mt-2 text-gray-900 dark:text-white">How can I help you today?</p>
                </div>
            </div>
        </div>

        <!-- Chat Input -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex gap-2">
                <input 
                    type="text" 
                    id="messageInput" 
                    placeholder="Type your message..." 
                    class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white"
                    onkeypress="if(event.key === 'Enter') sendMessage()"
                >
                <button onclick="sendMessage()" class="btn bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                <i class="fas fa-info-circle"></i> AI features require API configuration in settings
            </p>
        </div>
    </div>
</div>

<script>
function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    
    if (!message) return;
    
    const chatMessages = document.getElementById('chatMessages');
    
    // Add user message
    const userDiv = document.createElement('div');
    userDiv.className = 'flex gap-3 justify-end';
    userDiv.innerHTML = `
        <div class="bg-primary text-white rounded-lg p-4 max-w-2xl">
            <p>${message}</p>
        </div>
        <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center flex-shrink-0">
            <i class="fas fa-user text-gray-700 dark:text-gray-300 text-sm"></i>
        </div>
    `;
    chatMessages.appendChild(userDiv);
    
    input.value = '';
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    // Simulate AI response
    setTimeout(() => {
        const aiDiv = document.createElement('div');
        aiDiv.className = 'flex gap-3';
        aiDiv.innerHTML = `
            <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-robot text-white text-sm"></i>
            </div>
            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 max-w-2xl">
                <p class="text-gray-900 dark:text-white">I'm currently in demo mode. To enable full AI capabilities, please configure your AI API settings in the admin panel.</p>
            </div>
        `;
        chatMessages.appendChild(aiDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }, 1000);
}
</script>

<?php include 'includes/footer.php'; ?>
