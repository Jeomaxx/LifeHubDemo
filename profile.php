<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!$auth->validateCSRFToken($csrfToken)) {
        $message = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $name = sanitize($_POST['name']);
            $telegram_chat_id = sanitize($_POST['telegram_chat_id'] ?? '');
            
            $db->update('users', [
                'name' => $name,
                'telegram_chat_id' => $telegram_chat_id
            ], 'id = :id', [':id' => $userId]);
            
            $_SESSION['user_name'] = $name;
            $message = 'Profile updated successfully';
        } elseif ($action === 'change_password') {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            
            if (password_verify($current_password, $user['password'])) {
                $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                $db->update('users', ['password' => $hashed], 'id = :id', [':id' => $userId]);
                $message = 'Password changed successfully';
            } else {
                $message = 'Current password is incorrect';
            }
        }
    }
    
    $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
}

$csrfToken = $auth->generateCSRFToken();

$pageTitle = 'Profile Settings';
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-user-cog"></i> Profile Settings</h1>
    <p class="page-subtitle">Manage your account settings and preferences</p>
</div>

<?php if ($message): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <?php echo $message; ?>
</div>
<?php endif; ?>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-user"></i> Profile Information</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="update_profile">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" value="<?php echo sanitize($user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?php echo sanitize($user['email']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Telegram Chat ID</label>
                    <input type="text" name="telegram_chat_id" value="<?php echo sanitize($user['telegram_chat_id'] ?? ''); ?>" placeholder="Optional">
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-lock"></i> Change Password</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" minlength="6" required>
                </div>
                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
