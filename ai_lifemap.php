<?php
require_once 'includes/auth.php';
$auth = new Auth();
requireLogin();
$pageTitle = 'AI Life Map';
include 'includes/header.php';
?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8 flex items-center gap-3">
        <i data-lucide="compass"></i>
        AI Life Map Dashboard
    </h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="stat-card hover-lift">
            <h3 class="text-lg font-semibold">Finance Score</h3>
            <p class="text-3xl font-bold text-green-500">85%</p>
        </div>
        <div class="stat-card hover-lift">
            <h3 class="text-lg font-semibold">Health Score</h3>
            <p class="text-3xl font-bold text-blue-500">78%</p>
        </div>
        <div class="stat-card hover-lift">
            <h3 class="text-lg font-semibold">Productivity</h3>
            <p class="text-3xl font-bold text-purple-500">92%</p>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">AI Weekly Insights</h2>
        <p class="text-gray-600">Your personalized AI analysis of life balance and recommendations will appear here</p>
    </div>
</div>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
<?php include 'includes/footer.php'; ?>
