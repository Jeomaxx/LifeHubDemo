<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

// Get notifications
$notifications = $db->fetchAll(
    "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
    [$userId]
);

$unreadCount = $db->fetchColumn(
    "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE",
    [$userId]
) ?? 0;

$pageTitle = 'Notifications';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-bell text-primary"></i>
                Notifications
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1 unread-count">
                <?php echo $unreadCount; ?> unread notification<?php echo $unreadCount != 1 ? 's' : ''; ?>
            </p>
        </div>
        <?php if ($unreadCount > 0): ?>
        <button data-action="mark-all-read" onclick="markAllAsRead()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-check-double"></i>
            <span>Mark All as Read</span>
        </button>
        <?php endif; ?>
    </div>

    <!-- Notifications List -->
    <div class="space-y-3">
        <?php if (empty($notifications)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-12 text-center">
                <i class="fas fa-bell-slash text-gray-400 text-5xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">No Notifications</h3>
                <p class="text-gray-500 dark:text-gray-400">You're all caught up! No new notifications.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div data-notification-id="<?php echo $notification['id']; ?>" class="notification-card bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm <?php echo !$notification['is_read'] ? 'border-l-4 border-primary' : ''; ?>">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4 flex-1">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-<?php 
                                    echo match($notification['type']) {
                                        'bill' => 'file-invoice-dollar',
                                        'birthday' => 'birthday-cake',
                                        'task' => 'tasks',
                                        'goal' => 'bullseye',
                                        'subscription' => 'sync',
                                        'reminder' => 'bell',
                                        default => 'info-circle'
                                    };
                                ?> text-primary"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($notification['title']); ?></h3>
                                    <?php if (!$notification['is_read']): ?>
                                        <span class="unread-dot w-2 h-2 rounded-full bg-primary"></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2"><?php echo htmlspecialchars($notification['message']); ?></p>
                                <p class="text-gray-500 dark:text-gray-500 text-xs">
                                    <i class="far fa-clock"></i>
                                    <?php echo timeAgo($notification['created_at']); ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <?php if (!$notification['is_read']): ?>
                                <button data-action="mark-read" onclick="markAsRead(<?php echo $notification['id']; ?>)" class="text-gray-400 hover:text-primary" title="Mark as read">
                                    <i class="fas fa-check"></i>
                                </button>
                            <?php endif; ?>
                            <button onclick="deleteNotification(<?php echo $notification['id']; ?>)" class="text-gray-400 hover:text-red-500" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

async function markAsRead(notificationId) {
    try {
        const response = await fetch('/api/notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'mark_read',
                id: notificationId,
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Success', data.message);
            updateNotificationBadge(data.count);
            
            // Update DOM - remove the mark as read button and border
            const notificationCard = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationCard) {
                notificationCard.classList.remove('border-l-4', 'border-primary');
                const markReadBtn = notificationCard.querySelector('[data-action="mark-read"]');
                if (markReadBtn) markReadBtn.remove();
                const unreadDot = notificationCard.querySelector('.unread-dot');
                if (unreadDot) unreadDot.remove();
            }
            
            // Update unread count in page header
            const unreadText = document.querySelector('.unread-count');
            if (unreadText && data.count !== undefined) {
                unreadText.textContent = `${data.count} unread notification${data.count != 1 ? 's' : ''}`;
            }
            
            // Hide mark all button if no unread notifications
            if (data.count === 0) {
                const markAllBtn = document.querySelector('[data-action="mark-all-read"]');
                if (markAllBtn) markAllBtn.style.display = 'none';
            }
        } else {
            showToast('error', 'Error', data.message);
        }
    } catch (error) {
        showToast('error', 'Error', 'Failed to mark notification as read');
        console.error('Error:', error);
    }
}

async function markAllAsRead() {
    try {
        const response = await fetch('/api/notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'mark_all_read',
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Success', data.message);
            updateNotificationBadge(data.count);
            
            // Update all notification cards
            document.querySelectorAll('.notification-card').forEach(card => {
                card.classList.remove('border-l-4', 'border-primary');
                const markReadBtn = card.querySelector('[data-action="mark-read"]');
                if (markReadBtn) markReadBtn.remove();
                const unreadDot = card.querySelector('.unread-dot');
                if (unreadDot) unreadDot.remove();
            });
            
            // Update unread count
            const unreadText = document.querySelector('.unread-count');
            if (unreadText) {
                unreadText.textContent = '0 unread notifications';
            }
            
            // Hide mark all button
            const markAllBtn = document.querySelector('[data-action="mark-all-read"]');
            if (markAllBtn) markAllBtn.style.display = 'none';
        } else {
            showToast('error', 'Error', data.message);
        }
    } catch (error) {
        showToast('error', 'Error', 'Failed to mark all notifications as read');
        console.error('Error:', error);
    }
}

async function deleteNotification(notificationId) {
    if (!confirm('Are you sure you want to delete this notification?')) {
        return;
    }
    
    try {
        const response = await fetch('/api/notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete',
                id: notificationId,
                csrf_token: getCSRFToken()
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Success', data.message);
            updateNotificationBadge(data.count);
            
            // Remove notification card from DOM with animation
            const notificationCard = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationCard) {
                notificationCard.style.transition = 'opacity 0.3s, transform 0.3s';
                notificationCard.style.opacity = '0';
                notificationCard.style.transform = 'translateX(100%)';
                setTimeout(() => notificationCard.remove(), 300);
            }
            
            // Update unread count
            const unreadText = document.querySelector('.unread-count');
            if (unreadText && data.count !== undefined) {
                unreadText.textContent = `${data.count} unread notification${data.count != 1 ? 's' : ''}`;
            }
            
            // Hide mark all button if no unread notifications
            if (data.count === 0) {
                const markAllBtn = document.querySelector('[data-action="mark-all-read"]');
                if (markAllBtn) markAllBtn.style.display = 'none';
            }
            
            // Check if there are no more notifications
            setTimeout(() => {
                const remaining = document.querySelectorAll('.notification-card').length;
                if (remaining === 0) {
                    location.reload(); // Only reload if all notifications are deleted to show empty state
                }
            }, 350);
        } else {
            showToast('error', 'Error', data.message);
        }
    } catch (error) {
        showToast('error', 'Error', 'Failed to delete notification');
        console.error('Error:', error);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
