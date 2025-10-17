<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
requireLogin();

$pageTitle = 'Career Center';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8 reveal">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i data-lucide="briefcase" class="text-primary"></i>
                Work & Career Center
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Track job applications, interviews, and career growth</p>
        </div>
        <button onclick="openAddJobModal()" class="btn-interactive bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-lg flex items-center gap-2 shine-effect">
            <i data-lucide="plus" class="w-5 h-5"></i>
            Add Job Application
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="stat-card hover-lift" id="totalApplicationsCard">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Applications</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white counter" data-target="0">0</h3>
                </div>
                <i data-lucide="file-text" class="text-blue-500 icon-hover w-12 h-12"></i>
            </div>
        </div>

        <div class="stat-card hover-lift" id="interviewsCard">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Interviews Scheduled</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white counter" data-target="0">0</h3>
                </div>
                <i data-lucide="calendar" class="text-green-500 icon-hover w-12 h-12"></i>
            </div>
        </div>

        <div class="stat-card hover-lift" id="offersCard">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Offers Received</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white counter" data-target="0">0</h3>
                </div>
                <i data-lucide="gift" class="text-yellow-500 icon-hover w-12 h-12"></i>
            </div>
        </div>

        <div class="stat-card hover-lift" id="activeCard">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Active Applications</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white counter" data-target="0">0</h3>
                </div>
                <i data-lucide="activity" class="text-purple-500 icon-hover w-12 h-12"></i>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md mb-6 reveal">
        <div class="flex border-b border-gray-200 dark:border-gray-700">
            <button class="tab-button active px-6 py-4 flex items-center gap-2" data-tab="jobs">
                <i data-lucide="briefcase" class="w-5 h-5"></i>
                Job Applications
            </button>
            <button class="tab-button px-6 py-4 flex items-center gap-2" data-tab="interviews">
                <i data-lucide="video" class="w-5 h-5"></i>
                Interviews
            </button>
            <button class="tab-button px-6 py-4 flex items-center gap-2" data-tab="certifications">
                <i data-lucide="award" class="w-5 h-5"></i>
                Certifications
            </button>
            <button class="tab-button px-6 py-4 flex items-center gap-2" data-tab="resumes">
                <i data-lucide="file-text" class="w-5 h-5"></i>
                Resume Versions
            </button>
        </div>
    </div>

    <!-- Tab Content -->
    <div id="jobsTab" class="tab-content">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Job Applications</h2>
                <select id="statusFilter" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    <option value="">All Statuses</option>
                    <option value="applied">Applied</option>
                    <option value="interview">Interview</option>
                    <option value="assessment">Assessment</option>
                    <option value="offer">Offer</option>
                    <option value="rejected">Rejected</option>
                    <option value="withdrawn">Withdrawn</option>
                </select>
            </div>
            <div id="jobsList"></div>
        </div>
    </div>

    <div id="interviewsTab" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Interviews</h2>
            <div id="interviewsList"></div>
        </div>
    </div>

    <div id="certificationsTab" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Certifications & Skills</h2>
                <button onclick="openAddCertModal()" class="btn-interactive bg-primary text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Add Certification
                </button>
            </div>
            <div id="certificationsList"></div>
        </div>
    </div>

    <div id="resumesTab" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Resume Versions</h2>
                <button onclick="openAddResumeModal()" class="btn-interactive bg-primary text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Add Resume Version
                </button>
            </div>
            <div id="resumesList"></div>
        </div>
    </div>
</div>

<!-- Add Job Modal -->
<div id="addJobModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Add Job Application</h2>
            <form id="jobForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Company Name *</label>
                        <input type="text" name="company_name" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Position *</label>
                        <input type="text" name="position" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Job URL</label>
                    <input type="url" name="job_url" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Application Date *</label>
                        <input type="date" name="application_date" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Follow-up Date</label>
                        <input type="date" name="follow_up_date" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                            <option value="applied">Applied</option>
                            <option value="interview">Interview</option>
                            <option value="assessment">Assessment</option>
                            <option value="offer">Offer</option>
                            <option value="rejected">Rejected</option>
                            <option value="withdrawn">Withdrawn</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Job Type</label>
                        <select name="job_type" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                            <option value="">Select Type</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Contract">Contract</option>
                            <option value="Freelance">Freelance</option>
                            <option value="Internship">Internship</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Location</label>
                        <input type="text" name="location" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Salary Range</label>
                    <input type="text" name="salary_range" placeholder="e.g., $80k - $100k" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"></textarea>
                </div>
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark transition-colors">
                        Add Application
                    </button>
                    <button type="button" onclick="closeModal('addJobModal')" class="flex-1 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white px-6 py-3 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/assets/js/career.js"></script>

<?php include 'includes/footer.php'; ?>
