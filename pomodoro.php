<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$userId = $auth->getUserId();
$pageTitle = 'Pomodoro Timer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="pomodoro-container">
            <h1><i class="fas fa-stopwatch"></i> Pomodoro Timer</h1>
            
            <div class="pomodoro-stats">
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <h3 id="todayPomodoros">0</h3>
                        <p>Today's Sessions</p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-fire"></i>
                    <div>
                        <h3 id="weeklyPomodoros">0</h3>
                        <p>This Week</p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h3 id="totalMinutes">0</h3>
                        <p>Total Minutes</p>
                    </div>
                </div>
            </div>
            
            <div class="timer-card">
                <div class="mode-selector">
                    <button class="mode-btn active" data-mode="work" onclick="setMode('work')">
                        <i class="fas fa-briefcase"></i> Work
                    </button>
                    <button class="mode-btn" data-mode="short" onclick="setMode('short')">
                        <i class="fas fa-coffee"></i> Short Break
                    </button>
                    <button class="mode-btn" data-mode="long" onclick="setMode('long')">
                        <i class="fas fa-couch"></i> Long Break
                    </button>
                </div>
                
                <div class="timer-display">
                    <div class="timer-circle">
                        <svg width="300" height="300">
                            <circle cx="150" cy="150" r="140" fill="none" stroke="#e0e0e0" stroke-width="8"/>
                            <circle id="progressCircle" cx="150" cy="150" r="140" fill="none" stroke="#4a90e2" 
                                    stroke-width="8" stroke-linecap="round" transform="rotate(-90 150 150)"
                                    stroke-dasharray="879.64" stroke-dashoffset="879.64"/>
                        </svg>
                        <div class="timer-text">
                            <span id="timerDisplay">25:00</span>
                            <span id="modeText">Work Session</span>
                        </div>
                    </div>
                </div>
                
                <div class="timer-controls">
                    <button class="btn btn-large btn-primary" id="startBtn" onclick="startTimer()">
                        <i class="fas fa-play"></i> Start
                    </button>
                    <button class="btn btn-large btn-secondary" id="pauseBtn" onclick="pauseTimer()" style="display: none;">
                        <i class="fas fa-pause"></i> Pause
                    </button>
                    <button class="btn btn-large btn-secondary" onclick="resetTimer()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
                
                <div class="task-selector">
                    <label><i class="fas fa-tasks"></i> Link to Task (Optional)</label>
                    <select id="taskSelector">
                        <option value="">No task linked</option>
                    </select>
                </div>
            </div>
            
            <div class="session-history">
                <h2>Recent Sessions</h2>
                <div id="sessionList" class="session-list">
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin"></i> Loading sessions...
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <audio id="alertSound" src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUKvm8LJnHwU2jdXzzn0vBSp+zPLaizsKGGS46+mnVhMJQ5zd8sFuJAUuhM/y2Ik3CBxqvfDinE8MDlGr5O+xZB8FN47V88t8MAUrfsry2os5CRdku+vpp1YTCkKc3fK+bSQFLYTP8tmJNwgdar3w4pxPDA5Rq+Twq2UfBTeX1fPLfDAGK37K8tqLOQkXZbvr6KdWEwo/nN7yvW4lBiyFzvLZiTYIG2q88OGcTwwPUavu8LFjHwU5ltXzyH4yBSt8yvPaizgJF2a76+enVxIKP53e8rxuJQYsgs7y2YkWCBlqu/DinE4MDk+r5/CxYx8FOJbW88h+MgYrfMry2Ys4CRdnvOvop1cTCj+d3vK8bSUGLIHO8tmJNwgcarvw4pxODA5Qq+fwsWMfBTiW1vPIfjIGK3zK8tmKOAkXZ7zr6KdXEwo/nd7yvG4lBiyBzvLZiTcIHGq78OKcTwwOUKvn8LFjHwU4ltbzyH4yBSt8yvLZijgJF2e86+mnVhMKP53e8rxuJQYsgc7y2Yk3CBxqu/DinE8MDlCr5/CxYx8FOJbW88h+MgUrfMry2Yo4CRdnvOvop1YTCj+d3vK8biUGLIHO8tmJNwgcarvw4pxPDA5Qq+fwsWMfBTiW1vPIfjIFK3zK8tmKOAkXZ7zr6KdWEwo/nd7yvG4lBiyBzvLZiTcIHGq78OKcTwwOUKvn8LFjHwU4ltbzyH4yBSt8yvLZijgJF2e86+mnVhMKP53e8rxuJQYsgc7y2Yk3CBxqu/DinE8MDlCr5/CxYx8FOJbW88h+MgUrfMry2Yo4CRdnvOvop1YTCj+d3vK8biUGLIHO8tmJNwgcarvw4pxPDA5Qq+fwsWMfBTiW1vPIfjIFK3zK8tmKOAkXZ7zr6KdWEwo/nd7yvG4lBiyBzvLZiTcIHGq78OKcTwwOUKvn8LFjHwU4ltbzyH4yBSt8yvLZijgJF2e86+mnVhMKP53e8rxuJQYsgc7y2Yk3CBxqu/DinE8MDlCr5/CxYx8FOJbW88h+MgUrfMry2Yo4CRdnvOvop1YTCj+d3vK8biUGLIHO8tmJNwgcarvw4pxPDA5Qq+fwsWMfBTiW1vPIfjIFK3zK8tmKOAkXZ7zr6KdWEwo/nd7yvG4lBiyBzvLZiTcIHGq78OKcTwwOUKvn8LFjHwU4ltbzyH4yBSt8yvLZijgJF2e86+mnVhMKP53e8rxuJQYsgc7y2Yk3CBxqu/DinE8MDlCr5/CxYx8FOJbW88h+MgUrfMry2Yo4CRdnvOvop1YTCj+d3vK8biUGLIHO8tmJNwgcarvw4pxPDA5Qq+fwsWMfBQ==" preload="auto"></audio>
    
    <script>
        let timer = null;
        let timeLeft = 25 * 60;
        let isRunning = false;
        let currentMode = 'work';
        let currentTaskId = null;
        let sessionStartTime = null;
        
        const modes = {
            work: { duration: 25 * 60, label: 'Work Session', color: '#4a90e2' },
            short: { duration: 5 * 60, label: 'Short Break', color: '#27ae60' },
            long: { duration: 15 * 60, label: 'Long Break', color: '#e74c3c' }
        };
        
        function setMode(mode) {
            if (isRunning) {
                if (!confirm('Timer is running. Stop and switch mode?')) return;
                pauseTimer();
            }
            
            currentMode = mode;
            timeLeft = modes[mode].duration;
            
            document.querySelectorAll('.mode-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-mode="${mode}"]`).classList.add('active');
            document.getElementById('modeText').textContent = modes[mode].label;
            document.getElementById('progressCircle').style.stroke = modes[mode].color;
            
            updateDisplay();
        }
        
        function startTimer() {
            if (!isRunning) {
                isRunning = true;
                sessionStartTime = new Date();
                currentTaskId = document.getElementById('taskSelector').value || null;
                
                document.getElementById('startBtn').style.display = 'none';
                document.getElementById('pauseBtn').style.display = 'inline-flex';
                
                timer = setInterval(() => {
                    timeLeft--;
                    updateDisplay();
                    
                    if (timeLeft <= 0) {
                        completeSession();
                    }
                }, 1000);
            }
        }
        
        function pauseTimer() {
            isRunning = false;
            clearInterval(timer);
            document.getElementById('startBtn').style.display = 'inline-flex';
            document.getElementById('pauseBtn').style.display = 'none';
        }
        
        function resetTimer() {
            pauseTimer();
            timeLeft = modes[currentMode].duration;
            updateDisplay();
        }
        
        function updateDisplay() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('timerDisplay').textContent = 
                `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            
            const totalTime = modes[currentMode].duration;
            const progress = (totalTime - timeLeft) / totalTime;
            const circumference = 879.64;
            const offset = circumference - (progress * circumference);
            document.getElementById('progressCircle').style.strokeDashoffset = offset;
        }
        
        async function completeSession() {
            pauseTimer();
            document.getElementById('alertSound').play();
            
            const duration = Math.floor(modes[currentMode].duration / 60);
            
            await fetch('/api/tasks_advanced.php?action=pomodoro_complete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    task_id: currentTaskId,
                    duration_minutes: duration,
                    completed_at: new Date().toISOString()
                })
            });
            
            showNotification(`${modes[currentMode].label} complete! Great work!`, 'success');
            loadStats();
            loadSessions();
            
            if (currentMode === 'work') {
                if (confirm('Work session complete! Take a short break?')) {
                    setMode('short');
                    startTimer();
                }
            } else {
                if (confirm('Break complete! Ready to work?')) {
                    setMode('work');
                }
            }
        }
        
        async function loadStats() {
            try {
                const response = await fetch('/api/tasks_advanced.php?action=pomodoro_stats');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('todayPomodoros').textContent = data.stats.today || 0;
                    document.getElementById('weeklyPomodoros').textContent = data.stats.week || 0;
                    document.getElementById('totalMinutes').textContent = data.stats.total_minutes || 0;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }
        
        async function loadSessions() {
            try {
                const response = await fetch('/api/tasks_advanced.php?action=pomodoro_history');
                const data = await response.json();
                
                const list = document.getElementById('sessionList');
                
                if (data.success && data.sessions.length > 0) {
                    list.innerHTML = data.sessions.map(session => `
                        <div class="session-item">
                            <div class="session-icon">
                                <i class="fas fa-${session.task_title ? 'tasks' : 'stopwatch'}"></i>
                            </div>
                            <div class="session-info">
                                <strong>${session.task_title || 'Free Session'}</strong>
                                <span>${session.duration_minutes} minutes</span>
                            </div>
                            <div class="session-time">
                                ${formatDateTime(session.completed_at)}
                            </div>
                        </div>
                    `).join('');
                } else {
                    list.innerHTML = '<div class="empty-state">No sessions yet. Start your first Pomodoro!</div>';
                }
            } catch (error) {
                console.error('Error loading sessions:', error);
            }
        }
        
        async function loadTasks() {
            try {
                const response = await fetch('/api/tasks.php?action=list&status=pending');
                const data = await response.json();
                
                const selector = document.getElementById('taskSelector');
                
                if (data.success && data.tasks.length > 0) {
                    const options = data.tasks.map(task => 
                        `<option value="${task.id}">${task.title}</option>`
                    ).join('');
                    selector.innerHTML = '<option value="">No task linked</option>' + options;
                }
            } catch (error) {
                console.error('Error loading tasks:', error);
            }
        }
        
        function formatDateTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            
            if (diff < 60000) return 'Just now';
            if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`;
            if (diff < 86400000) return `${Math.floor(diff / 3600000)}h ago`;
            return date.toLocaleDateString();
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
        
        // Initialize
        updateDisplay();
        loadStats();
        loadSessions();
        loadTasks();
        
        // Update stats every minute
        setInterval(loadStats, 60000);
    </script>
    
    <style>
        .pomodoro-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .pomodoro-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stat-card i {
            font-size: 36px;
            color: #4a90e2;
        }
        
        .stat-card h3 {
            margin: 0;
            font-size: 32px;
            color: #333;
        }
        
        .stat-card p {
            margin: 0;
            color: #666;
        }
        
        .timer-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .mode-selector {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 40px;
        }
        
        .mode-btn {
            padding: 10px 20px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .mode-btn.active {
            background: #4a90e2;
            color: white;
            border-color: #4a90e2;
        }
        
        .timer-display {
            margin: 40px 0;
        }
        
        .timer-circle {
            position: relative;
            display: inline-block;
        }
        
        .timer-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
        
        #timerDisplay {
            font-size: 48px;
            font-weight: bold;
            display: block;
        }
        
        #modeText {
            font-size: 16px;
            color: #666;
        }
        
        .timer-controls {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 30px 0;
        }
        
        .btn-large {
            padding: 15px 40px;
            font-size: 18px;
        }
        
        .task-selector {
            margin-top: 30px;
            text-align: left;
        }
        
        .task-selector label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .task-selector select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .session-history {
            margin-top: 40px;
        }
        
        .session-list {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .session-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .session-item:last-child {
            border-bottom: none;
        }
        
        .session-icon {
            width: 40px;
            height: 40px;
            background: #4a90e2;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .session-info {
            flex: 1;
        }
        
        .session-info strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .session-info span {
            color: #666;
            font-size: 14px;
        }
        
        .session-time {
            color: #999;
            font-size: 14px;
        }
    </style>
</body>
</html>
