document.addEventListener('DOMContentLoaded', function() {
    renderFinanceChart();
    renderBillsChart();
    renderGoalsChart();
    renderHabitsChart();
    renderAssetsChart();
    renderMoodChart();
    renderLearningChart();
});

function renderFinanceChart() {
    if (typeof financeData === 'undefined' || !financeData.length) return;
    
    const months = [...new Set(financeData.map(d => d.month))].sort();
    const income = months.map(month => {
        const item = financeData.find(d => d.month === month && d.type === 'income');
        return item ? parseFloat(item.total) : 0;
    });
    const expenses = months.map(month => {
        const item = financeData.find(d => d.month === month && d.type === 'expense');
        return item ? parseFloat(item.total) : 0;
    });
    
    const labels = months.map(m => {
        const [year, month] = m.split('-');
        const date = new Date(year, month - 1);
        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    });
    
    createLineChart('financeChart', labels, [
        {
            label: 'Income',
            data: income,
            borderColor: chartColors.success,
            backgroundColor: chartGradients.createGradient('financeChart', chartColors.success, 0.2)
        },
        {
            label: 'Expenses',
            data: expenses,
            borderColor: chartColors.danger,
            backgroundColor: chartGradients.createGradient('financeChart', chartColors.danger, 0.2)
        }
    ]);
}

function renderBillsChart() {
    if (typeof billsData === 'undefined' || !billsData.length) return;
    
    const labels = billsData.map(b => b.status.charAt(0).toUpperCase() + b.status.slice(1));
    const data = billsData.map(b => parseInt(b.count));
    
    createPieChart('billsChart', data, labels);
}

function renderGoalsChart() {
    if (typeof goalsData === 'undefined' || !goalsData.length) return;
    
    const labels = goalsData.map(g => g.status.charAt(0).toUpperCase() + g.status.slice(1));
    const data = goalsData.map(g => parseInt(g.count));
    
    createPieChart('goalsChart', data, labels);
}

function renderHabitsChart() {
    if (typeof habitsData === 'undefined' || !habitsData.length) return;
    
    const labels = habitsData.map(h => h.name);
    const data = habitsData.map(h => parseInt(h.completions));
    
    createBarChart('habitsChart', labels, [{
        label: 'Completions',
        data: data,
        backgroundColor: chartGradients.createGradient('habitsChart', chartColors.primary, 0.8)
    }]);
}

function renderAssetsChart() {
    if (typeof assetsData === 'undefined' || !assetsData.length) return;
    
    const labels = assetsData.map(a => a.category || 'Uncategorized');
    const data = assetsData.map(a => parseFloat(a.total_value || 0));
    
    createPieChart('assetsChart', data, labels);
}

function renderMoodChart() {
    if (typeof moodData === 'undefined' || !moodData.length) return;
    
    const moodEmojis = {
        'happy': '😊 Happy',
        'sad': '😢 Sad',
        'neutral': '😐 Neutral',
        'excited': '🤩 Excited',
        'anxious': '😰 Anxious',
        'calm': '😌 Calm',
        'angry': '😠 Angry',
        'tired': '😴 Tired'
    };
    
    const labels = moodData.map(m => moodEmojis[m.mood] || m.mood);
    const data = moodData.map(m => parseInt(m.count));
    
    createPieChart('moodChart', data, labels);
}

function renderLearningChart() {
    if (typeof learningData === 'undefined' || !learningData.length) return;
    
    const labels = learningData.map(l => {
        const title = l.title.length > 30 ? l.title.substring(0, 30) + '...' : l.title;
        return title;
    });
    const data = learningData.map(l => parseInt(l.progress));
    
    createBarChart('learningChart', labels, [{
        label: 'Progress (%)',
        data: data,
        backgroundColor: chartGradients.createGradient('learningChart', chartColors.secondary, 0.8)
    }]);
}
