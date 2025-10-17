<?php
require_once 'includes/auth.php';
$auth = new Auth();
requireLogin();
$pageTitle = 'Finance Advanced';
include 'includes/header.php';
?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8 flex items-center gap-3">
        <i data-lucide="trending-up"></i>
        Finance Advanced
    </h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Investment Portfolio</h2>
            <p class="text-gray-600">Track stocks, crypto, and other investments with AI projections</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Tax Manager</h2>
            <p class="text-gray-600">Organize receipts and track tax documents</p>
        </div>
    </div>
</div>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
<?php include 'includes/footer.php'; ?>
