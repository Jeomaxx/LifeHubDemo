<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
requireLogin();

$pageTitle = 'Learning Hub';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i data-lucide="graduation-cap" class="text-primary"></i>
                Learning & Knowledge Hub
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Track courses, books, and build knowledge</p>
        </div>
        <button onclick="addCourse()" class="btn-interactive bg-primary text-white px-6 py-3 rounded-lg">
            <i data-lucide="plus"></i> Add Course
        </button>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Courses</p>
                    <h3 class="text-3xl font-bold" id="totalCourses">0</h3>
                </div>
                <i data-lucide="book-open" class="text-blue-500 w-12 h-12"></i>
            </div>
        </div>
        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Books Reading</p>
                    <h3 class="text-3xl font-bold" id="totalBooks">0</h3>
                </div>
                <i data-lucide="library" class="text-green-500 w-12 h-12"></i>
            </div>
        </div>
        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Flashcards</p>
                    <h3 class="text-3xl font-bold" id="totalFlashcards">0</h3>
                </div>
                <i data-lucide="layers" class="text-purple-500 w-12 h-12"></i>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">My Courses</h2>
        <div id="coursesList"></div>
    </div>
</div>

<script>
async function loadData() {
    const stats = await fetch('/api/learning.php?type=stats').then(r => r.json());
    if (stats.success) {
        document.getElementById('totalCourses').textContent = stats.data.total_courses;
        document.getElementById('totalBooks').textContent = stats.data.total_books;
    }
    
    const courses = await fetch('/api/learning.php?type=courses').then(r => r.json());
    if (courses.success) {
        document.getElementById('coursesList').innerHTML = courses.data.length > 0 
            ? courses.data.map(c => `<div class="p-4 border-b">${c.title}</div>`).join('') 
            : '<p class="text-gray-500">No courses yet. Add your first course!</p>';
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function addCourse() {
    alert('Add course functionality coming soon!');
}

document.addEventListener('DOMContentLoaded', loadData);
</script>

<?php include 'includes/footer.php'; ?>
