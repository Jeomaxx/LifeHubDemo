let moodChart = null;
let selectedEmoji = '😐';

function showAddMoodModal() {
    document.getElementById('addMoodModal').style.display = 'flex';
}

function selectEmoji(emoji) {
    selectedEmoji = emoji;
    document.getElementById('mood_emoji').value = emoji;
    
    document.querySelectorAll('.emoji-btn').forEach(btn => btn.classList.remove('selected'));
    event.target.classList.add('selected');
}

async function addMoodEntry(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);
    
    try {
        showToast('info', 'Processing', 'AI is analyzing your mood...');
        
        const response = await fetch('/api/mood_tracker.php?action=add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Mood entry saved successfully!');
            closeModal('addMoodModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', 'Error', result.message || 'Failed to save mood entry');
        }
    } catch (error) {
        console.error('Error saving mood:', error);
        showToast('error', 'Error', 'Failed to save mood entry');
    }
}

async function loadTrends() {
    try {
        const response = await fetch('/api/mood_tracker.php?action=get_trends&days=30');
        const result = await response.json();
        
        if (result.success) {
            updateMoodChart(result.trends);
            updateSentimentStats(result.sentiments);
        }
    } catch (error) {
        console.error('Error loading trends:', error);
    }
}

function updateMoodChart(trends) {
    const labels = trends.map(t => new Date(t.mood_date).toLocaleDateString());
    const ratings = trends.map(t => parseFloat(t.avg_rating));
    
    const ctx = document.getElementById('moodTrendsChart').getContext('2d');
    
    if (moodChart) {
        moodChart.destroy();
    }
    
    moodChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Mood Rating',
                data: ratings,
                borderColor: 'rgb(139, 92, 246)',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

function updateSentimentStats(sentiments) {
    const sentimentMap = {
        'positive': '😊 Positive',
        'neutral': '😐 Neutral',
        'negative': '😔 Negative'
    };
    
    if (sentiments.length > 0) {
        const dominant = sentiments.reduce((a, b) => a.count > b.count ? a : b);
        document.getElementById('ai-sentiment').textContent = sentimentMap[dominant.ai_sentiment] || 'Unknown';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadTrends();
});
