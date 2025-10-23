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

$pageTitle = 'Knowledge Graph';
$activePage = 'memory';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-brain"></i> Knowledge Graph & Memory Center</h1>
        <p>Your AI-powered personal knowledge base with semantic search</p>
    </div>

    <div class="knowledge-search">
        <input type="text" id="semanticSearch" placeholder="Search everything... (e.g., 'show everything related to stress management')" />
        <button class="btn btn-primary" onclick="performSemanticSearch()">
            <i class="fas fa-search"></i> Search
        </button>
    </div>

    <div class="knowledge-tabs">
        <button class="tab-btn active" onclick="showKnowledgeTab('nodes')">Knowledge Nodes</button>
        <button class="tab-btn" onclick="showKnowledgeTab('graph')">Mind Map</button>
        <button class="tab-btn" onclick="showKnowledgeTab('relationships')">Relationships</button>
    </div>

    <div id="nodes-tab" class="tab-content active">
        <div id="knowledgeNodesContainer"></div>
    </div>

    <div id="graph-tab" class="tab-content">
        <div id="mindMapContainer" style="height: 600px; background: white; border-radius: 8px;"></div>
    </div>

    <div id="relationships-tab" class="tab-content">
        <div id="relationshipsContainer"></div>
    </div>
</div>

<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<script>
function loadKnowledgeNodes() {
    fetch('api/get_nodes.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayKnowledgeNodes(data.nodes);
            }
        });
}

function displayKnowledgeNodes(nodes) {
    const container = document.getElementById('knowledgeNodesContainer');
    if (nodes.length === 0) {
        container.innerHTML = '<p class="no-data">No knowledge nodes yet. Your activities will automatically create knowledge nodes.</p>';
        return;
    }

    container.innerHTML = nodes.map(node => `
        <div class="knowledge-node ${node.node_type}">
            <div class="node-header">
                <h4>${escapeHtml(node.title)}</h4>
                <span class="node-type">${node.node_type}</span>
            </div>
            <p>${escapeHtml(node.content ? node.content.substring(0, 150) + '...' : '')}</p>
            <div class="node-tags">
                ${(node.tags || []).map(tag => `<span class="tag">${tag}</span>`).join('')}
            </div>
        </div>
    `).join('');
}

function showKnowledgeTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
    
    if (tabName === 'graph') {
        loadMindMap();
    }
}

function loadMindMap() {
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadKnowledgeNodes();
</script>

<style>
.knowledge-search {
    display: flex;
    gap: 10px;
    margin: 20px 0;
}

.knowledge-search input {
    flex: 1;
    padding: 12px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
}

.knowledge-tabs {
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

.knowledge-node {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid #667eea;
}

.node-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.node-type {
    background: #667eea;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    text-transform: capitalize;
}

.node-tags {
    display: flex;
    gap: 8px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.tag {
    background: #f0f0f0;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 12px;
}
</style>

<?php include '../../includes/footer.php'; ?>
