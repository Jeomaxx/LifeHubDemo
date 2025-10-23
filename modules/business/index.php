<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    redirect('/login.php');
}

$db = getDB();
$userId = $auth->getUserId();

$pageTitle = 'Business Suite';
$activePage = 'business';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-briefcase"></i> Smart Finance & Business Suite</h1>
        <p>Manage your freelance work, invoices, and business finances</p>
    </div>

    <div class="business-tabs">
        <button class="tab-btn active" onclick="showBusinessTab('invoices')">Invoices</button>
        <button class="tab-btn" onclick="showBusinessTab('expenses')">Expenses</button>
        <button class="tab-btn" onclick="showBusinessTab('clients')">Clients</button>
        <button class="tab-btn" onclick="showBusinessTab('reports')">Reports</button>
    </div>

    <div id="invoices-tab" class="tab-content active">
        <div class="tab-header">
            <h3>Invoices</h3>
            <button class="btn btn-primary" onclick="showCreateInvoiceModal()">
                <i class="fas fa-plus"></i> Create Invoice
            </button>
        </div>
        <div id="invoicesContainer"></div>
    </div>

    <div id="expenses-tab" class="tab-content">
        <div class="tab-header">
            <h3>Business Expenses</h3>
            <button class="btn btn-primary" onclick="showAddExpenseModal()">
                <i class="fas fa-plus"></i> Add Expense
            </button>
        </div>
        <div id="expensesContainer"></div>
    </div>

    <div id="clients-tab" class="tab-content">
        <h3>Client Management</h3>
        <div id="clientsContainer"></div>
    </div>

    <div id="reports-tab" class="tab-content">
        <h3>Financial Reports</h3>
        <div class="reports-grid">
            <div class="report-card">
                <h4>Total Revenue (YTD)</h4>
                <div class="report-value" id="totalRevenue">$0.00</div>
            </div>
            <div class="report-card">
                <h4>Total Expenses (YTD)</h4>
                <div class="report-value" id="totalExpenses">$0.00</div>
            </div>
            <div class="report-card">
                <h4>Net Profit (YTD)</h4>
                <div class="report-value" id="netProfit">$0.00</div>
            </div>
            <div class="report-card">
                <h4>Pending Invoices</h4>
                <div class="report-value" id="pendingInvoices">0</div>
            </div>
        </div>
    </div>
</div>

<script>
function showBusinessTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
}

function loadInvoices() {
    fetch('api/get_invoices.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayInvoices(data.invoices);
            }
        });
}

function displayInvoices(invoices) {
    const container = document.getElementById('invoicesContainer');
    if (invoices.length === 0) {
        container.innerHTML = '<p class="no-data">No invoices yet. Create your first invoice!</p>';
        return;
    }

    container.innerHTML = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                ${invoices.map(invoice => `
                    <tr>
                        <td>${invoice.invoice_number}</td>
                        <td>${invoice.client_name}</td>
                        <td>${invoice.invoice_date}</td>
                        <td>$${parseFloat(invoice.total_amount).toFixed(2)}</td>
                        <td><span class="status-badge ${invoice.status}">${invoice.status}</span></td>
                        <td>
                            <button class="btn-icon" onclick="viewInvoice(${invoice.id})"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

loadInvoices();
</script>

<style>
.business-tabs {
    display: flex;
    gap: 10px;
    margin: 20px 0;
    border-bottom: 2px solid #ddd;
}

.tab-btn {
    padding: 10px 20px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
}

.tab-btn.active {
    color: #667eea;
    font-weight: bold;
    border-bottom: 2px solid #667eea;
    margin-bottom: -2px;
}

.tab-content {
    display: none;
    padding: 20px 0;
}

.tab-content.active {
    display: block;
}

.tab-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.reports-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.report-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}

.report-value {
    font-size: 32px;
    font-weight: bold;
    color: #667eea;
    margin-top: 10px;
}

.data-table {
    width: 100%;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.data-table th {
    background: #f5f5f5;
    padding: 12px;
    text-align: left;
    font-weight: 600;
}

.data-table td {
    padding: 12px;
    border-top: 1px solid #eee;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    text-transform: capitalize;
}

.status-badge.paid {
    background: #4CAF50;
    color: white;
}

.status-badge.pending {
    background: #FFC107;
    color: white;
}

.status-badge.draft {
    background: #9E9E9E;
    color: white;
}

@media (max-width: 768px) {
    .reports-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<?php include '../../includes/footer.php'; ?>
