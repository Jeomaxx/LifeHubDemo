<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Voice Assistant';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-microphone text-primary"></i>
                Voice Assistant
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Control your life atlas with voice commands</p>
        </div>
    </div>

    <!-- Voice Control Card -->
    <div class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg p-8 text-white mb-6">
        <div class="flex flex-col items-center justify-center text-center">
            <div id="micButton" class="w-32 h-32 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center cursor-pointer transition-all mb-6" onclick="toggleVoiceRecognition()">
                <i class="fas fa-microphone text-6xl" id="micIcon"></i>
            </div>
            <h2 class="text-2xl font-bold mb-2" id="voiceStatus">Click to start listening</h2>
            <p class="opacity-90" id="voiceTranscript"></p>
        </div>
    </div>

    <!-- Command History -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-history"></i> Command History
        </h3>
        <div id="commandHistory" class="space-y-2">
            <p class="text-gray-500 dark:text-gray-400 text-center py-4">No commands yet</p>
        </div>
    </div>

    <!-- Available Commands -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-list"></i> Available Voice Commands
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Task Management</h4>
                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li>• "Add task [task name]"</li>
                    <li>• "Show my tasks"</li>
                    <li>• "Complete task [task name]"</li>
                </ul>
            </div>
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Goals & Habits</h4>
                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li>• "Show today's goals"</li>
                    <li>• "Track habit [habit name]"</li>
                    <li>• "Show my progress"</li>
                </ul>
            </div>
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Finance</h4>
                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li>• "Show my balance"</li>
                    <li>• "Add expense [amount] for [category]"</li>
                    <li>• "Show spending this month"</li>
                </ul>
            </div>
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Reports & Analytics</h4>
                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li>• "Send daily report"</li>
                    <li>• "Show analytics"</li>
                    <li>• "Generate summary"</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/voice-assistant.js"></script>

<?php include 'includes/footer.php'; ?>
