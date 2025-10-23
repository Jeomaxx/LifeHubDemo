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

$pageTitle = 'Integrations';
$activePage = 'integrations';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-plug"></i> External Integrations Layer</h1>
        <p>Connect with your favorite apps and services</p>
    </div>

    <div class="integrations-dashboard">
        <div class="available-integrations">
            <h3>Available Integrations</h3>
            <div class="integrations-grid">
                <div class="integration-card" onclick="connectIntegration('google_fit')">
                    <i class="fab fa-google"></i>
                    <h4>Google Fit</h4>
                    <p>Sync health and fitness data</p>
                    <button class="btn btn-sm btn-primary">Connect</button>
                </div>

                <div class="integration-card" onclick="connectIntegration('notion')">
                    <i class="fas fa-book"></i>
                    <h4>Notion</h4>
                    <p>Sync notes and databases</p>
                    <button class="btn btn-sm btn-primary">Connect</button>
                </div>

                <div class="integration-card" onclick="connectIntegration('drive')">
                    <i class="fab fa-google-drive"></i>
                    <h4>Google Drive</h4>
                    <p>Access and store files</p>
                    <button class="btn btn-sm btn-primary">Connect</button>
                </div>

                <div class="integration-card" onclick="connectIntegration('telegram')">
                    <i class="fab fa-telegram"></i>
                    <h4>Telegram</h4>
                    <p>Get notifications via Telegram</p>
                    <button class="btn btn-sm btn-primary">Connect</button>
                </div>

                <div class="integration-card" onclick="connectIntegration('stripe')">
                    <i class="fab fa-stripe"></i>
                    <h4>Stripe</h4>
                    <p>Payment processing</p>
                    <button class="btn btn-sm btn-primary">Connect</button>
                </div>

                <div class="integration-card" onclick="connectIntegration('openai')">
                    <i class="fas fa-robot"></i>
                    <h4>OpenAI</h4>
                    <p>AI-powered features</p>
                    <button class="btn btn-sm btn-primary">Connect</button>
                </div>
            </div>
        </div>

        <div class="active-integrations">
            <h3>Active Connections</h3>
            <div id="activeIntegrationsContainer"></div>
        </div>

        <div class="webhooks-section">
            <h3><i class="fas fa-code"></i> Webhooks</h3>
            <button class="btn btn-primary" onclick="showCreateWebhookModal()">
                <i class="fas fa-plus"></i> Create Webhook
            </button>
            <div id="webhooksContainer"></div>
        </div>
    </div>
</div>

<script>
function loadActiveIntegrations() {
    fetch('api/get_integrations.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayActiveIntegrations(data.integrations);
            }
        });
}

function displayActiveIntegrations(integrations) {
    const container = document.getElementById('activeIntegrationsContainer');
    if (integrations.length === 0) {
        container.innerHTML = '<p class="no-data">No active integrations. Connect your first service!</p>';
        return;
    }

    container.innerHTML = integrations.map(integration => `
        <div class="active-integration-card">
            <div class="integration-info">
                <h4>${escapeHtml(integration.connection_name || integration.integration_type)}</h4>
                <span class="status-badge ${integration.is_active ? 'active' : 'inactive'}">
                    ${integration.is_active ? 'Active' : 'Inactive'}
                </span>
            </div>
            <p>Last sync: ${formatDate(integration.last_sync_at)}</p>
            <div class="integration-actions">
                <button class="btn btn-sm" onclick="syncIntegration(${integration.id})">Sync Now</button>
                <button class="btn btn-sm btn-danger" onclick="disconnectIntegration(${integration.id})">Disconnect</button>
            </div>
        </div>
    `).join('');
}

function connectIntegration(type) {
    showNotification(`${type} integration coming soon!`, 'info');
}

function formatDate(date) {
    if (!date) return 'Never';
    return new Date(date).toLocaleString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadActiveIntegrations();
</script>

<style>
.integrations-dashboard {
    margin-top: 20px;
}

.available-integrations, .active-integrations, .webhooks-section {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.integrations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.integration-card {
    background: #f9f9f9;
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}

.integration-card:hover {
    border-color: #667eea;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.integration-card i {
    font-size: 48px;
    color: #667eea;
    margin-bottom: 15px;
}

.active-integration-card {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;
}

.integration-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
}

.status-badge.active {
    background: #4CAF50;
    color: white;
}

.status-badge.inactive {
    background: #9E9E9E;
    color: white;
}

.integration-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}
</style>

<?php include '../../includes/footer.php'; ?>
