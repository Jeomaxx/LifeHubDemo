<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Career Portfolio & Resume Generator';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i data-lucide="award" class="text-primary"></i>
                Career Portfolio & Resume Generator
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Showcase your skills, projects, and achievements</p>
        </div>
        <div class="flex gap-2">
            <button onclick="generatePortfolio()" class="btn bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                <i data-lucide="globe" class="w-4 h-4 inline"></i> Generate Portfolio Website
            </button>
            <button onclick="generateResume()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg">
                <i data-lucide="file-text" class="w-4 h-4 inline"></i> Generate Resume PDF
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Skills</p>
                    <p id="totalSkills" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="code" class="text-blue-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Projects</p>
                    <p id="totalProjects" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="folder" class="text-green-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Milestones</p>
                    <p id="totalMilestones" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="trophy" class="text-purple-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Experience</p>
                    <p id="totalExperience" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0 years</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="briefcase" class="text-orange-600 w-6 h-6"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md mb-6">
        <div class="flex border-b border-gray-200 dark:border-gray-700">
            <button class="tab-btn active px-6 py-4 font-medium text-primary border-b-2 border-primary" data-tab="skills">Skills</button>
            <button class="tab-btn px-6 py-4 font-medium text-gray-600 dark:text-gray-400" data-tab="projects">Projects</button>
            <button class="tab-btn px-6 py-4 font-medium text-gray-600 dark:text-gray-400" data-tab="milestones">Milestones</button>
            <button class="tab-btn px-6 py-4 font-medium text-gray-600 dark:text-gray-400" data-tab="preview">Portfolio Preview</button>
        </div>

        <div id="skillsTab" class="tab-content p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Technical Skills</h2>
                <button onclick="addSkill()" class="btn bg-primary text-white px-4 py-2 rounded-lg">
                    <i data-lucide="plus" class="w-4 h-4 inline"></i> Add Skill
                </button>
            </div>
            <div id="skillsList"></div>
        </div>

        <div id="projectsTab" class="tab-content p-6 hidden">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Portfolio Projects</h2>
                <button onclick="addProject()" class="btn bg-primary text-white px-4 py-2 rounded-lg">
                    <i data-lucide="plus" class="w-4 h-4 inline"></i> Add Project
                </button>
            </div>
            <div id="projectsList"></div>
        </div>

        <div id="milestonesTab" class="tab-content p-6 hidden">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Career Milestones</h2>
                <button onclick="addMilestone()" class="btn bg-primary text-white px-4 py-2 rounded-lg">
                    <i data-lucide="plus" class="w-4 h-4 inline"></i> Add Milestone
                </button>
            </div>
            <div id="milestonesList"></div>
        </div>

        <div id="previewTab" class="tab-content p-6 hidden">
            <h2 class="text-xl font-semibold mb-4">Portfolio Preview</h2>
            <div class="border-4 border-dashed border-gray-300 rounded-lg p-12 text-center">
                <i data-lucide="eye" class="w-16 h-16 mx-auto mb-4 text-gray-400"></i>
                <h3 class="text-lg font-semibold mb-2">Portfolio Preview</h3>
                <p class="text-gray-600 mb-4">Click "Generate Portfolio Website" to create your professional portfolio</p>
                <button onclick="generatePortfolio()" class="btn bg-primary text-white px-6 py-3 rounded-lg">
                    Generate Now
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/portfolio-generator.js"></script>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>

<?php include 'includes/footer.php'; ?>
