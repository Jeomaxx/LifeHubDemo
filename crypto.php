<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$portfolio = $db->fetchAll("SELECT * FROM crypto_portfolio WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
$alerts = $db->fetchAll("SELECT * FROM crypto_alerts WHERE user_id = ? ORDER BY created_at DESC", [$userId]);

$totalInvested = $db->fetchColumn("SELECT COALESCE(SUM(amount * purchase_price), 0) FROM crypto_portfolio WHERE user_id = ?", [$userId]);
$activeAlerts = $db->fetchColumn("SELECT COUNT(*) FROM crypto_alerts WHERE user_id = ? AND is_active = TRUE", [$userId]);

$pageTitle = 'Cryptocurrency Portfolio';
$extraScripts = ['/assets/js/crypto.js'];
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-bitcoin"></i> Cryptocurrency Portfolio</h1>
    <p class="page-subtitle">Track your crypto investments with live prices and automated alerts</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="stat-info">
            <h3 id="total-holdings">Loading...</h3>
            <p>Total Holdings Value</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
            <h3>$<?php echo number_format($totalInvested, 2); ?></h3>
            <p>Total Invested</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-info">
            <h3 id="total-pnl">Loading...</h3>
            <p>Total Profit/Loss</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-orange">
            <i class="fas fa-bell"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $activeAlerts; ?></h3>
            <p>Active Alerts</p>
        </div>
    </div>
</div>

<div class="action-bar">
    <button class="btn btn-primary" onclick="showAddCryptoModal()">
        <i class="fas fa-plus"></i> Add Crypto
    </button>
    <button class="btn btn-secondary" onclick="showCreateAlertModal()">
        <i class="fas fa-bell"></i> Create Alert
    </button>
    <button class="btn btn-success" onclick="refreshPrices()">
        <i class="fas fa-sync-alt"></i> Refresh Prices
    </button>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card full-width">
        <div class="card-header">
            <h3><i class="fas fa-chart-area"></i> Portfolio Value Chart</h3>
        </div>
        <div class="card-body">
            <canvas id="portfolioChart"></canvas>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-coins"></i> Your Portfolio</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Coin</th>
                            <th>Amount</th>
                            <th>Avg Price</th>
                            <th>Current Price</th>
                            <th>Value</th>
                            <th>P/L</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="portfolio-table">
                        <?php if (empty($portfolio)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                <i class="fas fa-coins fa-3x" style="color: var(--text-light); margin-bottom: 16px;"></i>
                                <p style="color: var(--text-light);">No cryptocurrencies in your portfolio yet. Click "Add Crypto" to get started!</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($portfolio as $holding): ?>
                        <tr data-crypto-id="<?php echo $holding['crypto_id']; ?>" data-holding-id="<?php echo $holding['id']; ?>">
                            <td>
                                <div class="crypto-info">
                                    <strong><?php echo strtoupper(sanitize($holding['crypto_symbol'])); ?></strong>
                                    <span class="crypto-name"><?php echo sanitize($holding['crypto_name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo number_format($holding['amount'], 8); ?></td>
                            <td>$<?php echo number_format($holding['purchase_price'], 2); ?></td>
                            <td class="current-price" data-symbol="<?php echo $holding['crypto_symbol']; ?>">Loading...</td>
                            <td class="current-value">Loading...</td>
                            <td class="pnl-value">Loading...</td>
                            <td>
                                <button class="btn-icon btn-icon-danger" onclick="deleteHolding(<?php echo $holding['id']; ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-bell"></i> Price Alerts</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Coin</th>
                            <th>Type</th>
                            <th>Target Price</th>
                            <th>Current Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="alerts-table">
                        <?php if (empty($alerts)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                <i class="fas fa-bell-slash fa-3x" style="color: var(--text-light); margin-bottom: 16px;"></i>
                                <p style="color: var(--text-light);">No price alerts configured</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($alerts as $alert): ?>
                        <tr data-alert-id="<?php echo $alert['id']; ?>" data-crypto-symbol="<?php echo $alert['crypto_symbol']; ?>">
                            <td><strong><?php echo strtoupper(sanitize($alert['crypto_symbol'])); ?></strong></td>
                            <td>
                                <span class="badge badge-<?php echo $alert['alert_type'] === 'above' ? 'success' : 'danger'; ?>">
                                    <?php echo $alert['alert_type'] === 'above' ? 'Price Above' : 'Price Below'; ?>
                                </span>
                            </td>
                            <td>$<?php echo number_format($alert['target_price'], 2); ?></td>
                            <td class="alert-current-price">Loading...</td>
                            <td>
                                <?php if ($alert['is_triggered']): ?>
                                    <span class="badge badge-success">Triggered</span>
                                <?php elseif ($alert['is_active']): ?>
                                    <span class="badge badge-info">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-icon btn-icon-danger" onclick="deleteAlert(<?php echo $alert['id']; ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="dashboard-card full-width">
        <div class="card-header">
            <h3><i class="fas fa-fire"></i> Top Cryptocurrencies (Live Prices)</h3>
        </div>
        <div class="card-body">
            <div id="top-cryptos-grid" class="crypto-grid">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading market data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="addCryptoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-plus-circle"></i> Add Cryptocurrency</h2>
            <button class="modal-close" onclick="closeModal('addCryptoModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addCryptoForm" onsubmit="addCrypto(event)">
                <div class="form-group">
                    <label>Search Cryptocurrency</label>
                    <input type="text" id="crypto-search" class="form-control" placeholder="Search by name or symbol..." autocomplete="off">
                    <div id="crypto-search-results" class="search-results"></div>
                </div>
                
                <input type="hidden" id="crypto_id" name="crypto_id" required>
                <input type="hidden" id="crypto_symbol" name="crypto_symbol" required>
                <input type="hidden" id="crypto_name" name="crypto_name" required>
                
                <div class="form-group">
                    <label>Amount</label>
                    <input type="number" id="amount" name="amount" class="form-control" step="0.00000001" required>
                </div>
                
                <div class="form-group">
                    <label>Purchase Price (USD)</label>
                    <input type="number" id="purchase_price" name="purchase_price" class="form-control" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Purchase Date</label>
                    <input type="date" id="purchase_date" name="purchase_date" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addCryptoModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add to Portfolio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="createAlertModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-bell"></i> Create Price Alert</h2>
            <button class="modal-close" onclick="closeModal('createAlertModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createAlertForm" onsubmit="createAlert(event)">
                <div class="form-group">
                    <label>Search Cryptocurrency</label>
                    <input type="text" id="alert-crypto-search" class="form-control" placeholder="Search by name or symbol..." autocomplete="off">
                    <div id="alert-search-results" class="search-results"></div>
                </div>
                
                <input type="hidden" id="alert_crypto_id" name="crypto_id" required>
                <input type="hidden" id="alert_crypto_symbol" name="crypto_symbol" required>
                
                <div class="form-group">
                    <label>Alert Type</label>
                    <select id="alert_type" name="alert_type" class="form-control" required>
                        <option value="above">Price Goes Above</option>
                        <option value="below">Price Goes Below</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Target Price (USD)</label>
                    <input type="number" id="target_price" name="target_price" class="form-control" step="0.01" required>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createAlertModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Alert</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.crypto-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
    margin-top: 16px;
}

.crypto-card {
    background: var(--card-bg);
    border-radius: 12px;
    padding: 16px;
    border: 1px solid var(--border);
    transition: all 0.3s ease;
}

.crypto-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.crypto-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.crypto-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.crypto-info h4 {
    margin: 0;
    font-size: 1rem;
}

.crypto-info .crypto-name {
    font-size: 0.875rem;
    color: var(--text-light);
    display: block;
}

.crypto-price {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 8px 0;
}

.crypto-change {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
}

.crypto-change.positive {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
}

.crypto-change.negative {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    box-shadow: var(--shadow-lg);
}

.search-results.active {
    display: block;
}

.search-result-item {
    padding: 12px;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    transition: background 0.2s;
}

.search-result-item:hover {
    background: var(--light);
}

.search-result-item:last-child {
    border-bottom: none;
}

.full-width {
    grid-column: 1 / -1;
}

.loading-spinner {
    text-align: center;
    padding: 40px;
    color: var(--text-light);
}
</style>

<?php include 'includes/footer.php'; ?>
