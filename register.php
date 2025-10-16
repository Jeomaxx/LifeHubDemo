<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$error = '';
$success = '';

if ($auth->isLoggedIn()) {
    redirect('/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!$auth->validateCSRFToken($csrfToken)) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } else {
            $result = $auth->register($name, $email, $password);
            
            if ($result['success']) {
                $success = 'Registration successful! Please login.';
            } else {
                $error = $result['message'];
            }
        }
    }
}

$csrfToken = $auth->generateCSRFToken();

$pageTitle = 'Register';
include 'includes/header.php';
?>

<div class="auth-box">
    <div class="auth-logo">
        <i class="fas fa-atlas"></i>
        <h1><?php echo SITE_NAME; ?></h1>
    </div>
    
    <h2>Create Account</h2>
    <p class="auth-subtitle">Sign up to start organizing your life</p>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        <div class="form-group">
            <label for="name">
                <i class="fas fa-user"></i>
                Full Name
            </label>
            <input type="text" id="name" name="name" required autocomplete="name" value="<?php echo $name ?? ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="email">
                <i class="fas fa-envelope"></i>
                Email Address
            </label>
            <input type="email" id="email" name="email" required autocomplete="email" value="<?php echo $email ?? ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="password">
                <i class="fas fa-lock"></i>
                Password
            </label>
            <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">
                <i class="fas fa-lock"></i>
                Confirm Password
            </label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6" autocomplete="new-password">
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-user-plus"></i>
            Create Account
        </button>
    </form>
    
    <div class="auth-footer">
        <p>Already have an account? <a href="/login.php">Sign in</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
