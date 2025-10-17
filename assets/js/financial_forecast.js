let forecastChart = null;

function showForecastModal() {
    document.getElementById('forecastModal').style.display = 'flex';
}

async function generateForecast(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);
    
    try {
        showToast('info', 'Processing', 'AI is analyzing your financial data...');
        
        const response = await fetch('/api/financial_forecast.php?action=generate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Financial forecast generated successfully!');
            closeModal('forecastModal');
            updateForecastDisplay(result.forecast);
        } else {
            showToast('error', 'Error', result.message || 'Failed to generate forecast');
        }
    } catch (error) {
        console.error('Error generating forecast:', error);
        showToast('error', 'Error', 'Failed to generate forecast');
    }
}

function updateForecastDisplay(forecast) {
    document.getElementById('forecast-balance').textContent = '$' + parseFloat(forecast.predicted_balance || 0).toLocaleString();
    document.getElementById('forecast-income').textContent = '$' + parseFloat(forecast.predicted_income || 0).toLocaleString();
    document.getElementById('forecast-expenses').textContent = '$' + parseFloat(forecast.predicted_expenses || 0).toLocaleString();
    
    const savings = parseFloat(forecast.predicted_income || 0) - parseFloat(forecast.predicted_expenses || 0);
    document.getElementById('forecast-savings').textContent = '$' + savings.toLocaleString();
    
    const riskContainer = document.getElementById('risk-alerts');
    if (forecast.risks) {
        riskContainer.innerHTML = `<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> ${forecast.risks}</div>`;
    }
    
    const recoContainer = document.getElementById('recommendations');
    if (forecast.recommendations) {
        recoContainer.innerHTML = `<div class="alert alert-info"><i class="fas fa-lightbulb"></i> ${forecast.recommendations}</div>`;
    }
    
    loadForecastChart();
}

async function loadForecastChart() {
    try {
        const response = await fetch('/api/financial_forecast.php?action=get_forecasts');
        const result = await response.json();
        
        if (result.success && result.forecasts.length > 0) {
            const labels = result.forecasts.reverse().map(f => new Date(f.forecast_date).toLocaleDateString());
            const balances = result.forecasts.map(f => parseFloat(f.predicted_balance || 0));
            const income = result.forecasts.map(f => parseFloat(f.predicted_income || 0));
            const expenses = result.forecasts.map(f => parseFloat(f.predicted_expenses || 0));
            
            const ctx = document.getElementById('forecastChart').getContext('2d');
            
            if (forecastChart) {
                forecastChart.destroy();
            }
            
            forecastChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Predicted Balance',
                            data: balances,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Predicted Income',
                            data: income,
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Predicted Expenses',
                            data: expenses,
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error loading forecast chart:', error);
    }
}

async function exportForecast() {
    window.location.href = '/api/financial_forecast.php?action=export';
}

document.addEventListener('DOMContentLoaded', function() {
    loadForecastChart();
});
