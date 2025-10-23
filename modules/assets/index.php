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

$pageTitle = 'Assets & Subscriptions';
$activePage = 'assets';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-box"></i> Asset & Subscription Manager</h1>
        <p>Track owned assets and optimize your subscriptions</p>
    </div>

    <div class="asset-tabs">
        <button class="tab-btn active" onclick="showAssetTab('assets')">My Assets</button>
        <button class="tab-btn" onclick="showAssetTab('subscriptions')">Subscriptions</button>
        <button class="tab-btn" onclick="showAssetTab('analysis')">Cost Analysis</button>
    </div>

    <div id="assets-tab" class="tab-content active">
        <div class="tab-header">
            <h3>Owned Assets</h3>
            <button class="btn btn-primary" onclick="showAddAssetModal()">
                <i class="fas fa-plus"></i> Add Asset
            </button>
        </div>
        <div id="assetsContainer"></div>
    </div>

    <div id="subscriptions-tab" class="tab-content">
        <div class="tab-header">
            <h3>Active Subscriptions</h3>
            <button class="btn btn-primary" onclick="showAddSubscriptionModal()">
                <i class="fas fa-plus"></i> Add Subscription
            </button>
        </div>
        <div class="subscription-summary">
            <div class="summary-card">
                <h4>Total Monthly Cost</h4>
                <div class="summary-value" id="monthlyTotal">$0.00</div>
            </div>
            <div class="summary-card">
                <h4>Annual Cost</h4>
                <div class="summary-value" id="annualTotal">$0.00</div>
            </div>
            <div class="summary-card">
                <h4>Potential Savings</h4>
                <div class="summary-value savings" id="potentialSavings">$0.00</div>
            </div>
        </div>
        <div id="subscriptionsContainer"></div>
    </div>

    <div id="analysis-tab" class="tab-content">
        <h3>Cost Analysis & Optimization</h3>
        <div id="analysisContainer"></div>
    </div>
</div>

<script>
function showAssetTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
    
    if (tabName === 'assets') {
        loadAssets();
    } else if (tabName === 'subscriptions') {
        loadSubscriptions();
    } else if (tabName === 'analysis') {
        loadAnalysis();
    }
}

function loadAssets() {
    fetch('api/get_assets.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAssets(data.assets);
            }
        });
}

function loadSubscriptions() {
    fetch('api/get_subscriptions.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySubscriptions(data.subscriptions);
                calculateSubscriptionTotals(data.subscriptions);
            }
        });
}

function displayAssets(assets) {
    const container = document.getElementById('assetsContainer');
    if (assets.length === 0) {
        container.innerHTML = '<p class="no-data">No assets tracked yet. Add your first asset!</p>';
        return;
    }

    container.innerHTML = assets.map(asset => `
        <div class="asset-card">
            <div class="asset-header">
                <h4><i class="fas fa-${getAssetIcon(asset.asset_type)}"></i> ${escapeHtml(asset.asset_name)}</h4>
                <span class="asset-type">${asset.asset_type}</span>
            </div>
            <div class="asset-details">
                <p><strong>Purchase Price:</strong> $${parseFloat(asset.purchase_price || 0).toFixed(2)}</p>
                <p><strong>Current Value:</strong> $${parseFloat(asset.current_value || 0).toFixed(2)}</p>
                <p><strong>Purchase Date:</strong> ${asset.purchase_date || 'N/A'}</p>
                ${asset.warranty_until ? `<p><strong>Warranty Until:</strong> ${asset.warranty_until}</p>` : ''}
            </div>
        </div>
    `).join('');
}

function displaySubscriptions(subscriptions) {
    const container = document.getElementById('subscriptionsContainer');
    if (subscriptions.length === 0) {
        container.innerHTML = '<p class="no-data">No subscriptions tracked yet. Add your first subscription!</p>';
        return;
    }

    container.innerHTML = subscriptions.map(sub => `
        <div class="subscription-card ${sub.usage_level || ''}">
            <div class="subscription-header">
                <h4>${escapeHtml(sub.service_name)}</h4>
                <span class="cost">$${parseFloat(sub.cost).toFixed(2)}/${sub.billing_cycle}</span>
            </div>
            <div class="subscription-details">
                <span class="category-badge">${sub.category}</span>
                ${sub.usage_level ? `<span class="usage-badge ${sub.usage_level}">Usage: ${sub.usage_level}</span>` : ''}
            </div>
            ${sub.optimization_suggestion ? `<p class="suggestion">${sub.optimization_suggestion}</p>` : ''}
        </div>
    `).join('');
}

function calculateSubscriptionTotals(subscriptions) {
    let monthlyTotal = 0;
    let annualTotal = 0;
    let savings = 0;

    subscriptions.forEach(sub => {
        const cost = parseFloat(sub.cost);
        if (sub.billing_cycle === 'monthly') {
            monthlyTotal += cost;
            annualTotal += cost * 12;
        } else if (sub.billing_cycle === 'annual') {
            annualTotal += cost;
            monthlyTotal += cost / 12;
        }
        savings += parseFloat(sub.potential_savings || 0);
    });

    document.getElementById('monthlyTotal').textContent = '$' + monthlyTotal.toFixed(2);
    document.getElementById('annualTotal').textContent = '$' + annualTotal.toFixed(2);
    document.getElementById('potentialSavings').textContent = '$' + savings.toFixed(2);
}

function getAssetIcon(type) {
    const icons = {
        'device': 'mobile-alt',
        'furniture': 'couch',
        'vehicle': 'car',
        'equipment': 'tools',
        'electronics': 'laptop'
    };
    return icons[type] || 'box';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadAssets();
</script>

<style>
.asset-tabs {
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

.subscription-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.summary-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}

.summary-value {
    font-size: 32px;
    font-weight: bold;
    color: #667eea;
    margin-top: 10px;
}

.summary-value.savings {
    color: #4CAF50;
}

.asset-card, .subscription-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.asset-header, .subscription-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.asset-type, .category-badge {
    background: #667eea;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    text-transform: capitalize;
}

.usage-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    margin-left: 8px;
}

.usage-badge.high { background: #4CAF50; color: white; }
.usage-badge.medium { background: #FFC107; color: white; }
.usage-badge.low { background: #FF5722; color: white; }
.usage-badge.unused { background: #9E9E9E; color: white; }

.suggestion {
    margin-top: 10px;
    padding: 10px;
    background: #FFF9C4;
    border-radius: 6px;
    font-size: 14px;
}

@media (max-width: 768px) {
    .subscription-summary {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>
