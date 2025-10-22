document.addEventListener('DOMContentLoaded', function() {
    initTaxYear();
    loadTaxData();
    initExportButtons();
});

function loadTaxData() {
    loadStats();
    loadCategories();
    loadDocuments();
    loadReports();
}

async function loadStats() {
    const year = document.getElementById('yearFilter')?.value || new Date().getFullYear();
    try {
        const response = await fetch(`/api/tax_reports.php?action=stats&year=${year}`);
        const result = await response.json();
        if (result.success) {
            document.getElementById('totalIncome').textContent = formatCurrency(result.data.total_income);
            document.getElementById('totalDeductions').textContent = formatCurrency(result.data.total_deductions);
            document.getElementById('totalDocuments').textContent = result.data.total_documents;
            document.getElementById('estimatedTax').textContent = formatCurrency(result.data.estimated_tax);
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadCategories() {
    try {
        const response = await fetch('/api/tax_reports.php?type=categories');
        const result = await response.json();
        if (result.success) {
            displayCategories(result.data);
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

function displayCategories(categories) {
    const container = document.getElementById('categoriesList');
    if (!container) return;
    
    if (categories.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">No categories yet</p>';
        return;
    }
    
    container.innerHTML = categories.map(cat => `
        <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg mb-2">
            <div>
                <p class="font-medium">${escapeHtml(cat.category_name)}</p>
                <p class="text-xs text-gray-500">${cat.deductible ? 'Deductible' : 'Non-deductible'}</p>
            </div>
            <button onclick="deleteCategory(${cat.id})" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `).join('');
}

async function loadDocuments() {
    const year = document.getElementById('yearFilter')?.value || new Date().getFullYear();
    try {
        const response = await fetch(`/api/tax_reports.php?type=documents&year=${year}`);
        const result = await response.json();
        if (result.success) {
            displayDocuments(result.data);
        }
    } catch (error) {
        console.error('Error loading documents:', error);
    }
}

function displayDocuments(documents) {
    const container = document.getElementById('documentsList');
    if (!container) return;
    
    if (documents.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">No documents uploaded yet</p>';
        return;
    }
    
    container.innerHTML = documents.map(doc => `
        <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
            <div>
                <p class="font-medium">${escapeHtml(doc.document_type)}</p>
                <p class="text-sm text-gray-600">${doc.category_name || 'Uncategorized'}</p>
                ${doc.amount ? `<p class="text-sm font-semibold">${formatCurrency(doc.amount)}</p>` : ''}
            </div>
            <button onclick="deleteDocument(${doc.id})" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `).join('');
}

async function loadReports() {
    try {
        const response = await fetch('/api/tax_reports.php?type=reports');
        const result = await response.json();
        if (result.success) {
            displayReports(result.data);
        }
    } catch (error) {
        console.error('Error loading reports:', error);
    }
}

function displayReports(reports) {
    const container = document.getElementById('reportsList');
    if (!container) return;
    
    if (reports.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">No reports generated yet</p>';
        return;
    }
    
    container.innerHTML = reports.map(report => `
        <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
            <div>
                <p class="font-medium">Tax Year ${report.tax_year}</p>
                <p class="text-sm text-gray-600">Generated: ${new Date(report.generated_at).toLocaleDateString()}</p>
                <p class="text-sm">Income: ${formatCurrency(report.total_income)} | Tax: ${formatCurrency(report.estimated_tax)}</p>
            </div>
        </div>
    `).join('');
}

function openCategoryModal() {
    const modalHtml = `
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" id="categoryModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold mb-4">Add Tax Category</h3>
                <form id="categoryForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Category Name</label>
                        <input type="text" name="category_name" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="deductible" class="mr-2">
                            <span class="text-sm">Deductible</span>
                        </label>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg">Add Category</button>
                        <button type="button" onclick="closeModal('categoryModal')" class="flex-1 bg-gray-300 dark:bg-gray-600 px-4 py-2 rounded-lg">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    document.getElementById('categoryForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = {
            category_name: formData.get('category_name'),
            deductible: formData.get('deductible') ? true : false
        };
        
        try {
            const response = await fetch('/api/tax_reports.php?type=category', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                closeModal('categoryModal');
                loadCategories();
                showToast('success', 'Success', result.message);
            } else {
                showToast('error', 'Error', result.message);
            }
        } catch (error) {
            showToast('error', 'Error', 'Failed to add category');
        }
    });
}

function openDocumentModal() {
    const modalHtml = `
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" id="documentModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold mb-4">Upload Tax Document</h3>
                <form id="documentForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Document Type</label>
                        <input type="text" name="document_type" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Amount</label>
                        <input type="number" step="0.01" name="amount" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700"></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg">Upload</button>
                        <button type="button" onclick="closeModal('documentModal')" class="flex-1 bg-gray-300 dark:bg-gray-600 px-4 py-2 rounded-lg">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    document.getElementById('documentForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = {
            document_type: formData.get('document_type'),
            amount: formData.get('amount') || null,
            notes: formData.get('notes') || null
        };
        
        try {
            const response = await fetch('/api/tax_reports.php?type=document', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                closeModal('documentModal');
                loadDocuments();
                loadStats();
                showToast('success', 'Success', result.message);
            } else {
                showToast('error', 'Error', result.message);
            }
        } catch (error) {
            showToast('error', 'Error', 'Failed to upload document');
        }
    });
}

async function generateReport() {
    const year = document.getElementById('yearFilter')?.value || new Date().getFullYear();
    
    try {
        showToast('info', 'Generating', 'Creating tax report...');
        const response = await fetch('/api/tax_reports.php?action=generate-report', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ tax_year: year })
        });
        const result = await response.json();
        if (result.success) {
            loadReports();
            loadStats();
            showToast('success', 'Success', result.message);
        } else {
            showToast('error', 'Error', result.message);
        }
    } catch (error) {
        showToast('error', 'Error', 'Failed to generate report');
    }
}

async function deleteCategory(id) {
    if (!confirm('Delete this category?')) return;
    try {
        const response = await fetch(`/api/tax_reports.php?type=category&id=${id}`, { method: 'DELETE' });
        const result = await response.json();
        if (result.success) {
            loadCategories();
            showToast('success', 'Success', result.message);
        } else {
            showToast('error', 'Error', result.message);
        }
    } catch (error) {
        showToast('error', 'Error', 'Failed to delete category');
    }
}

async function deleteDocument(id) {
    if (!confirm('Delete this document?')) return;
    try {
        const response = await fetch(`/api/tax_reports.php?type=document&id=${id}`, { method: 'DELETE' });
        const result = await response.json();
        if (result.success) {
            loadDocuments();
            loadStats();
            showToast('error', 'Success', result.message);
        } else {
            showToast('error', 'Error', result.message);
        }
    } catch (error) {
        showToast('error', 'Error', 'Failed to delete document');
    }
}

function closeModal(modalId) {
    document.getElementById(modalId)?.remove();
}

function initTaxYear() {
    const yearSelect = document.getElementById('yearFilter');
    if (yearSelect) {
        yearSelect.addEventListener('change', function() {
            loadTaxData();
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
    const selectedYear = year || document.getElementById('yearFilter')?.value || new Date().getFullYear();
    
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
    const year = document.getElementById('yearFilter')?.value || new Date().getFullYear();
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
    const year = document.getElementById('yearFilter')?.value || new Date().getFullYear();
    
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
