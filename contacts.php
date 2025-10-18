<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$contacts = $db->fetchAll("SELECT * FROM contacts WHERE user_id = ? ORDER BY name", [$userId]);

$pageTitle = 'Personal CRM - Contacts';
$extraScripts = ['/assets/js/new-modules.js'];
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-address-book text-primary"></i>
                Personal CRM - Contacts
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage relationships and stay connected</p>
        </div>
        <button onclick="showAddContactModal()" class="btn btn-primary flex items-center gap-2">
            <i class="fas fa-plus"></i>
            Add Contact
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Contacts</p>
                    <h3 class="text-2xl font-bold"><?php echo count($contacts); ?></h3>
                </div>
                <i class="fas fa-users text-3xl text-blue-500"></i>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">VIP Contacts</p>
                    <h3 class="text-2xl font-bold"><?php echo count(array_filter($contacts, fn($c) => $c['importance'] == 'vip')); ?></h3>
                </div>
                <i class="fas fa-star text-3xl text-yellow-500"></i>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Favorites</p>
                    <h3 class="text-2xl font-bold"><?php echo count(array_filter($contacts, fn($c) => $c['is_favorite'])); ?></h3>
                </div>
                <i class="fas fa-heart text-3xl text-red-500"></i>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Reminders Due</p>
                    <h3 class="text-2xl font-bold" id="remindersDue">0</h3>
                </div>
                <i class="fas fa-bell text-3xl text-orange-500"></i>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md mb-6 p-4">
        <div class="flex gap-2 flex-wrap">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="family">Family</button>
            <button class="filter-btn" data-filter="friend">Friends</button>
            <button class="filter-btn" data-filter="professional">Professional</button>
            <button class="filter-btn" data-filter="vip">VIP</button>
        </div>
    </div>

    <?php if (empty($contacts)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-address-book text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No contacts yet. Add your first contact to start building your network!</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($contacts as $contact): 
                $interactionCount = $db->fetchColumn("SELECT COUNT(*) FROM contact_interactions WHERE contact_id = ?", [$contact['id']]);
                $lastInteraction = $db->fetchOne("SELECT * FROM contact_interactions WHERE contact_id = ? ORDER BY interaction_date DESC LIMIT 1", [$contact['id']]);
            ?>
            <div class="contact-card bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden" data-relationship="<?php echo $contact['relationship']; ?>" data-importance="<?php echo $contact['importance']; ?>">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl">
                                <?php echo strtoupper(substr($contact['name'], 0, 1)); ?>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900 dark:text-white"><?php echo sanitize($contact['name']); ?></h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo ucfirst($contact['relationship']); ?></p>
                            </div>
                        </div>
                        <div class="flex gap-1">
                            <?php if ($contact['is_favorite']): ?>
                            <i class="fas fa-heart text-red-500" title="Favorite"></i>
                            <?php endif; ?>
                            <?php if ($contact['importance'] == 'vip'): ?>
                            <i class="fas fa-star text-yellow-500" title="VIP"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="space-y-2 mb-4 text-sm">
                        <?php if ($contact['email']): ?>
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <i class="fas fa-envelope w-4"></i>
                            <a href="mailto:<?php echo $contact['email']; ?>" class="hover:text-primary"><?php echo sanitize($contact['email']); ?></a>
                        </div>
                        <?php endif; ?>
                        <?php if ($contact['phone']): ?>
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <i class="fas fa-phone w-4"></i>
                            <a href="tel:<?php echo $contact['phone']; ?>" class="hover:text-primary"><?php echo sanitize($contact['phone']); ?></a>
                        </div>
                        <?php endif; ?>
                        <?php if ($contact['company']): ?>
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <i class="fas fa-building w-4"></i>
                            <span><?php echo sanitize($contact['company']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($contact['birthday']): ?>
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <i class="fas fa-birthday-cake w-4"></i>
                            <span><?php echo date('M d', strtotime($contact['birthday'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($lastInteraction): ?>
                    <div class="text-xs text-gray-500 mb-3 p-2 bg-gray-50 dark:bg-gray-900 rounded">
                        Last contact: <?php echo timeAgo($lastInteraction['interaction_date']); ?> (<?php echo ucfirst($lastInteraction['interaction_type']); ?>)
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex gap-2">
                        <button onclick="viewContact(<?php echo $contact['id']; ?>)" class="flex-1 px-3 py-2 bg-primary text-white rounded hover:bg-blue-600 text-sm">
                            View Details
                        </button>
                        <button onclick="logInteraction(<?php echo $contact['id']; ?>)" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-100 text-sm">
                            <i class="fas fa-comment"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="addContactModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold">Add Contact</h2>
        </div>
        <form id="contactForm" class="p-6 space-y-4">
            <input type="hidden" id="contactId">
            
            <div>
                <label class="block text-sm font-medium mb-1">Name *</label>
                <input type="text" id="contactName" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Relationship *</label>
                    <select id="relationship" required class="w-full px-3 py-2 border rounded-lg">
                        <option value="family">Family</option>
                        <option value="friend">Friend</option>
                        <option value="professional">Professional</option>
                        <option value="acquaintance">Acquaintance</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Importance</label>
                    <select id="importance" class="w-full px-3 py-2 border rounded-lg">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="vip">VIP</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" id="email" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input type="tel" id="phone" class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Company</label>
                    <input type="text" id="company" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Job Title</label>
                    <input type="text" id="jobTitle" class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Birthday</label>
                <input type="date" id="birthday" class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Address</label>
                <textarea id="address" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Notes</label>
                <textarea id="contactNotes" rows="3" class="w-full px-3 py-2 border rounded-lg"></textarea>
            </div>
            
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="isFavorite" class="rounded">
                    <span class="text-sm">Mark as Favorite</span>
                </label>
            </div>
            
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeAddContactModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg">Save Contact</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
