document.addEventListener('DOMContentLoaded', function() {
    initTaxYear();
    loadTaxSummary();
    initExportButtons();
});

function initTaxYear() {
    const yearSelect = document.getElementById('taxYear');
    if (yearSelect) {
        yearSelect.addEventListener('change', function() {
            loadTaxSummary(this.value);
        });
    }
}

function initExportButtons() {
    const exportBtn = document.getElementById('exportTaxReport');
    if (exportBtn) {
        exportBtn.addEventListener('click', exportTaxReport);
    }
}

async function loadTaxSummary(year = null) {
    const selectedYear = year || document.getElementById('taxYear')?.value || new Date().getFullYear();
    
    try {
        const response = await fetch(`/api/tax_reports.php?action=get_summary&year=${selectedYear}`);
        const result = await response.json();
        if (result.success) {
            displayTaxSummary(result.summary);
        }
    } catch (error) {
        console.error('Error loading tax summary:', error);
    }
}

function displayTaxSummary(summary) {
    if (summary.total_income !== undefined) {
        document.getElementById('totalIncome').textContent = formatCurrency(summary.total_income);
    }
    if (summary.deductible_expenses !== undefined) {
        document.getElementById('deductibleExpenses').textContent = formatCurrency(summary.deductible_expenses);
    }
    if (summary.taxable_income !== undefined) {
        document.getElementById('taxableIncome').textContent = formatCurrency(summary.taxable_income);
    }
    if (summary.estimated_tax !== undefined) {
        document.getElementById('estimatedTax').textContent = formatCurrency(summary.estimated_tax);
    }
    
    if (summary.income_by_category) {
        renderIncomeBreakdown(summary.income_by_category);
    }
    if (summary.expenses_by_category) {
        renderExpenseBreakdown(summary.expenses_by_category);
    }
}

function renderIncomeBreakdown(incomeData) {
    const container = document.getElementById('incomeBreakdown');
    if (!container) return;
    
    container.innerHTML = `
        <h3 class="text-lg font-semibold mb-3">Income Breakdown</h3>
        <div class="space-y-2">
            ${Object.entries(incomeData).map(([category, amount]) => `
                <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                    <span>${escapeHtml(category)}</span>
                    <span class="font-semibold">${formatCurrency(amount)}</span>
                </div>
            `).join('')}
        </div>
    `;
}

function renderExpenseBreakdown(expenseData) {
    const container = document.getElementById('expenseBreakdown');
    if (!container) return;
    
    container.innerHTML = `
        <h3 class="text-lg font-semibold mb-3">Deductible Expenses</h3>
        <div class="space-y-2">
            ${Object.entries(expenseData).map(([category, amount]) => `
                <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                    <span>${escapeHtml(category)}</span>
                    <span class="font-semibold">${formatCurrency(amount)}</span>
                </div>
            `).join('')}
        </div>
    `;
}

async function exportTaxReport() {
    const year = document.getElementById('taxYear')?.value || new Date().getFullYear();
    const format = document.getElementById('exportFormat')?.value || 'pdf';
    
    try {
        showToast('info', 'Exporting', 'Generating tax report...');
        
        const response = await fetch('/api/tax_reports.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'export_report',
                year: year,
                format: format
            })
        });
        
        const result = await response.json();
        if (result.success) {
            if (result.download_url) {
                window.open(result.download_url, '_blank');
                showToast('success', 'Success', 'Tax report exported successfully');
            } else {
                showToast('success', 'Success', result.message || 'Report generated');
            }
        } else {
            showToast('error', 'Error', result.message || 'Failed to export report');
        }
    } catch (error) {
        console.error('Error exporting report:', error);
        showToast('error', 'Error', 'Failed to export report');
    }
}

async function generateTaxEstimate() {
    const year = document.getElementById('taxYear')?.value || new Date().getFullYear();
    
    try {
        showToast('info', 'Calculating', 'Estimating tax liability...');
        
        const response = await fetch('/api/tax_reports.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'estimate_tax',
                year: year
            })
        });
        
        const result = await response.json();
        if (result.success) {
            displayTaxEstimate(result.estimate);
            showToast('success', 'Success', 'Tax estimate calculated');
        } else {
            showToast('error', 'Error', result.message || 'Failed to calculate estimate');
        }
    } catch (error) {
        console.error('Error generating estimate:', error);
        showToast('error', 'Error', 'Failed to generate estimate');
    }
}

function displayTaxEstimate(estimate) {
    const modalHtml = `
        <div class="modal-content">
            <h3 class="text-xl font-bold mb-4">Tax Estimate</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span>Gross Income:</span>
                    <span class="font-semibold">${formatCurrency(estimate.gross_income)}</span>
                </div>
                <div class="flex justify-between">
                    <span>Deductions:</span>
                    <span class="font-semibold">-${formatCurrency(estimate.deductions)}</span>
                </div>
                <div class="flex justify-between border-t pt-2">
                    <span class="font-bold">Taxable Income:</span>
                    <span class="font-bold">${formatCurrency(estimate.taxable_income)}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-bold">Estimated Tax:</span>
                    <span class="font-bold text-red-600">${formatCurrency(estimate.estimated_tax)}</span>
                </div>
            </div>
            <button onclick="closeModal()" class="btn btn-primary mt-4">Close</button>
        </div>
    `;
    
    let modal = document.getElementById('estimateModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'estimateModal';
        modal.className = 'modal flex';
        document.body.appendChild(modal);
    }
    modal.innerHTML = modalHtml;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function formatCurrency(amount) {
    return '$' + parseFloat(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function closeModal() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });
}
