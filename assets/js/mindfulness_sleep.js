async function logSleep(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    try {
        const response = await fetch('/api/mindfulness_sleep.php?action=log_sleep', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Sleep logged successfully');
            closeModal('logSleepModal');
            event.target.reset();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', 'Error', result.message || 'Failed to log sleep');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to log sleep');
    }
}

async function logMeditation(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    try {
        const response = await fetch('/api/mindfulness_sleep.php?action=log_meditation', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Meditation logged successfully');
            closeModal('logMeditationModal');
            event.target.reset();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', 'Error', result.message || 'Failed to log meditation');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to log meditation');
    }
}

async function loadSleepChart() {
    try {
        const response = await fetch('/api/mindfulness_sleep.php?action=get_sleep_data&days=7');
        const result = await response.json();
        
        if (result.success && result.sleep_data.length > 0) {
            const ctx = document.getElementById('sleepChart');
            if (!ctx) return;
            
            const labels = result.sleep_data.map(d => new Date(d.sleep_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric'}));
            const qualityData = result.sleep_data.map(d => d.sleep_quality_rating);
            const durationData = result.sleep_data.map(d => d.sleep_duration_hours);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Sleep Quality (1-10)',
                        data: qualityData,
                        borderColor: 'rgb(139, 92, 246)',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y'
                    }, {
                        label: 'Sleep Duration (hours)',
                        data: durationData,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            min: 0,
                            max: 10,
                            title: {
                                display: true,
                                text: 'Quality (1-10)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            min: 0,
                            max: 12,
                            title: {
                                display: true,
                                text: 'Hours'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        },
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error loading chart:', error);
    }
}

async function viewInsights() {
    showToast('info', 'Processing', 'AI is analyzing your wellness data...');
    
    try {
        const response = await fetch('/api/mindfulness_sleep.php?action=insights');
        const result = await response.json();
        
        if (result.success) {
            showInsightsModal(result.insights);
        } else {
            showToast('error', 'Error', result.message || 'Failed to get insights');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to get insights');
    }
}

function showInsightsModal(insights) {
    const modal = document.getElementById('insightsModal');
    if (modal) modal.remove();
    
    const recsHtml = insights.recommendations?.map(rec => `<li>${rec}</li>`).join('') || '';
    
    const modalHtml = `
        <div id="insightsModal" class="modal" style="display: flex;">
            <div class="modal-content" style="max-width: 700px;">
                <div class="modal-header">
                    <h2><i class="fas fa-brain"></i> AI Wellness Insights</h2>
                    <button class="modal-close" onclick="closeModal('insightsModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="insight-section">
                        <h4><i class="fas fa-chart-line"></i> Summary</h4>
                        <p>${insights.summary || 'No summary available'}</p>
                    </div>
                    
                    ${insights.sleep_insights ? `
                    <div class="insight-section">
                        <h4><i class="fas fa-bed"></i> Sleep Insights</h4>
                        <p>${insights.sleep_insights}</p>
                    </div>
                    ` : ''}
                    
                    ${insights.meditation_insights ? `
                    <div class="insight-section">
                        <h4><i class="fas fa-om"></i> Meditation Insights</h4>
                        <p>${insights.meditation_insights}</p>
                    </div>
                    ` : ''}
                    
                    ${insights.correlation_findings ? `
                    <div class="insight-section">
                        <h4><i class="fas fa-link"></i> Correlations</h4>
                        <p>${insights.correlation_findings}</p>
                    </div>
                    ` : ''}
                    
                    ${recsHtml ? `
                    <div class="insight-section">
                        <h4><i class="fas fa-lightbulb"></i> Recommendations</h4>
                        <ul>${recsHtml}</ul>
                    </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function showLogSleepModal() {
    openModal('logSleepModal');
}

function showLogMeditationModal() {
    openModal('logMeditationModal');
}

document.addEventListener('DOMContentLoaded', () => {
    loadSleepChart();
});
