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

$pageTitle = 'Life Roadmap';
$activePage = 'roadmap';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-map-marked-alt"></i> Life Navigation & Planning</h1>
        <p>Chart your long-term vision with 1-year, 5-year, and 10-year roadmaps</p>
    </div>

    <div class="roadmap-timeline">
        <button class="timeline-btn active" onclick="showTimeline('1year')">1 Year</button>
        <button class="timeline-btn" onclick="showTimeline('5year')">5 Year</button>
        <button class="timeline-btn" onclick="showTimeline('10year')">10 Year</button>
        <button class="timeline-btn" onclick="showTimeline('lifetime')">Lifetime</button>
    </div>

    <div class="roadmap-container">
        <button class="btn btn-primary" onclick="showCreateRoadmapModal()">
            <i class="fas fa-plus"></i> Create Vision Plan
        </button>
        
        <div id="roadmapContent" class="roadmap-content"></div>
    </div>
</div>

<script>
let currentTimeline = '1year';

function showTimeline(timeline) {
    currentTimeline = timeline;
    document.querySelectorAll('.timeline-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    loadRoadmaps(timeline);
}

function loadRoadmaps(timeline) {
    fetch(`api/get_roadmaps.php?timeline=${timeline}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRoadmaps(data.roadmaps);
            }
        });
}

function displayRoadmaps(roadmaps) {
    const container = document.getElementById('roadmapContent');
    if (roadmaps.length === 0) {
        container.innerHTML = '<p class="no-data">No vision plans for this timeline yet. Create your first one!</p>';
        return;
    }

    container.innerHTML = roadmaps.map(roadmap => `
        <div class="roadmap-card">
            <div class="roadmap-header">
                <h3>${escapeHtml(roadmap.title)}</h3>
                <span class="category-badge ${roadmap.category}">${roadmap.category}</span>
            </div>
            <p>${escapeHtml(roadmap.description || '')}</p>
            <div class="progress-bar">
                <div class="progress-fill" style="width: ${roadmap.progress_percentage}%"></div>
            </div>
            <p class="progress-text">${roadmap.progress_percentage}% Complete</p>
        </div>
    `).join('');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadRoadmaps('1year');
</script>

<style>
.roadmap-timeline {
    display: flex;
    gap: 10px;
    margin: 20px 0;
}

.timeline-btn {
    padding: 10px 20px;
    background: white;
    border: 2px solid #ddd;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s;
}

.timeline-btn.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.roadmap-content {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.roadmap-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.roadmap-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.category-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    text-transform: capitalize;
}

.category-badge.career { background: #2196F3; color: white; }
.category-badge.finance { background: #4CAF50; color: white; }
.category-badge.health { background: #FF5722; color: white; }
.category-badge.relationships { background: #E91E63; color: white; }
.category-badge.personal { background: #9C27B0; color: white; }

.progress-bar {
    height: 8px;
    background: #eee;
    border-radius: 4px;
    overflow: hidden;
    margin: 15px 0 5px;
}

.progress-fill {
    height: 100%;
    background: #667eea;
    transition: width 0.3s;
}

.progress-text {
    font-size: 12px;
    color: #666;
}
</style>

<?php include '../../includes/footer.php'; ?>
