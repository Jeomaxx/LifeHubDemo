<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = Database::getInstance();

$usersExist = $db->fetchColumn("SELECT COUNT(*) FROM users") > 0;

if ($usersExist && !isset($_GET['force'])) {
    header('Location: /login.php');
    exit;
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['step']) {
        case '1':
            $name = trim($_POST['name'] ?? '');
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (!$email) {
                $error = 'Invalid email address';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match';
            } else {
                try {
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $db->execute(
                        "INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, TRUE)",
                        [$name, $email, $hashedPassword]
                    );
                    
                    $_SESSION['setup_user_created'] = true;
                    header('Location: /setup.php?step=2');
                    exit;
                } catch (Exception $e) {
                    $error = 'Email already exists or database error';
                }
            }
            break;
        
        case '2':
            $smtpHost = trim($_POST['smtp_host'] ?? '');
            $smtpPort = trim($_POST['smtp_port'] ?? '587');
            $smtpUsername = trim($_POST['smtp_username'] ?? '');
            $smtpPassword = $_POST['smtp_password'] ?? '';
            $smtpFromEmail = trim($_POST['smtp_from_email'] ?? '');
            
            $configContent = file_get_contents('includes/config.php');
            
            $configContent = preg_replace(
                "/define\('SMTP_HOST',\s*'[^']*'\);/",
                "define('SMTP_HOST', '{$smtpHost}');",
                $configContent
            );
            $configContent = preg_replace(
                "/define\('SMTP_PORT',\s*\d+\);/",
                "define('SMTP_PORT', {$smtpPort});",
                $configContent
            );
            $configContent = preg_replace(
                "/define\('SMTP_USERNAME',\s*'[^']*'\);/",
                "define('SMTP_USERNAME', '{$smtpUsername}');",
                $configContent
            );
            $configContent = preg_replace(
                "/define\('SMTP_PASSWORD',\s*'[^']*'\);/",
                "define('SMTP_PASSWORD', '{$smtpPassword}');",
                $configContent
            );
            $configContent = preg_replace(
                "/define\('SMTP_FROM_EMAIL',\s*'[^']*'\);/",
                "define('SMTP_FROM_EMAIL', '{$smtpFromEmail}');",
                $configContent
            );
            
            file_put_contents('includes/config.php', $configContent);
            
            $_SESSION['setup_smtp_configured'] = true;
            header('Location: /setup.php?step=3');
            exit;
            break;
        
        case '3':
            $telegramBotToken = trim($_POST['telegram_bot_token'] ?? '');
            
            if ($telegramBotToken) {
                $configContent = file_get_contents('includes/config.php');
                $configContent = preg_replace(
                    "/define\('TELEGRAM_BOT_TOKEN',\s*'[^']*'\);/",
                    "define('TELEGRAM_BOT_TOKEN', '{$telegramBotToken}');",
                    $configContent
                );
                file_put_contents('includes/config.php', $configContent);
            }
            
            $_SESSION['setup_complete'] = true;
            header('Location: /setup.php?step=4');
            exit;
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Life Atlas Organizer</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <i class="fas fa-atlas"></i>
            <h1>Life Atlas Organizer Setup</h1>
            <p>Let's get your personal management system configured</p>
        </div>
        
        <div class="setup-progress">
            <div class="progress-step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                <div class="step-number">1</div>
                <div class="step-label">Admin Account</div>
            </div>
            <div class="progress-step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                <div class="step-number">2</div>
                <div class="step-label">Email Setup</div>
            </div>
            <div class="progress-step <?php echo $step >= 3 ? 'active' : ''; ?> <?php echo $step > 3 ? 'completed' : ''; ?>">
                <div class="step-number">3</div>
                <div class="step-label">Notifications</div>
            </div>
            <div class="progress-step <?php echo $step >= 4 ? 'active' : ''; ?>">
                <div class="step-number">4</div>
                <div class="step-label">Complete</div>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($step == 1): ?>
            <div class="setup-card">
                <h2><i class="fas fa-user-shield"></i> Create Admin Account</h2>
                <p>This will be the main administrator account for the system.</p>
                
                <form method="POST">
                    <input type="hidden" name="step" value="1">
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        Continue <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        <?php elseif ($step == 2): ?>
            <div class="setup-card">
                <h2><i class="fas fa-envelope"></i> Email Configuration</h2>
                <p>Configure SMTP settings for email notifications (optional - can be configured later).</p>
                
                <form method="POST">
                    <input type="hidden" name="step" value="2">
                    
                    <div class="form-group">
                        <label for="smtp_host">SMTP Host</label>
                        <input type="text" id="smtp_host" name="smtp_host" placeholder="smtp.gmail.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="smtp_port">SMTP Port</label>
                        <input type="number" id="smtp_port" name="smtp_port" value="587">
                    </div>
                    
                    <div class="form-group">
                        <label for="smtp_username">SMTP Username</label>
                        <input type="text" id="smtp_username" name="smtp_username">
                    </div>
                    
                    <div class="form-group">
                        <label for="smtp_password">SMTP Password</label>
                        <input type="password" id="smtp_password" name="smtp_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="smtp_from_email">From Email Address</label>
                        <input type="email" id="smtp_from_email" name="smtp_from_email">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        Continue <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="button" class="btn btn-secondary btn-block" onclick="window.location.href='/setup.php?step=3'">
                        Skip for Now
                    </button>
                </form>
            </div>
        <?php elseif ($step == 3): ?>
            <div class="setup-card">
                <h2><i class="fas fa-paper-plane"></i> Telegram Notifications</h2>
                <p>Configure Telegram Bot for instant notifications (optional - can be configured later).</p>
                
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>How to get a Telegram Bot Token:</strong>
                        <ol>
                            <li>Open Telegram and search for @BotFather</li>
                            <li>Send /newbot command</li>
                            <li>Follow the instructions to create your bot</li>
                            <li>Copy the token and paste it below</li>
                        </ol>
                    </div>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="step" value="3">
                    
                    <div class="form-group">
                        <label for="telegram_bot_token">Telegram Bot Token</label>
                        <input type="text" id="telegram_bot_token" name="telegram_bot_token" placeholder="123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        Complete Setup <i class="fas fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-secondary btn-block" onclick="window.location.href='/setup.php?step=4'">
                        Skip for Now
                    </button>
                </form>
            </div>
        <?php elseif ($step == 4): ?>
            <div class="setup-card setup-complete">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Setup Complete!</h2>
                <p>Your Life Atlas Organizer is ready to use.</p>
                
                <div class="next-steps">
                    <h3>Next Steps:</h3>
                    <ul>
                        <li><i class="fas fa-check"></i> Log in with your admin account</li>
                        <li><i class="fas fa-check"></i> Explore the dashboard and modules</li>
                        <li><i class="fas fa-check"></i> Configure cron jobs for automation (see README)</li>
                        <li><i class="fas fa-check"></i> Enable 2FA for enhanced security</li>
                    </ul>
                </div>
                
                <a href="/login.php" class="btn btn-primary btn-block">
                    Go to Login <i class="fas fa-sign-in-alt"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <style>
        .setup-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .setup-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .setup-header i {
            font-size: 64px;
            color: #4a90e2;
            margin-bottom: 20px;
        }
        
        .setup-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }
        
        .setup-progress::before {
            content: '';
            position: absolute;
            top: 30px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: #ddd;
            z-index: 0;
        }
        
        .progress-step {
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .step-number {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #f5f5f5;
            border: 3px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: #999;
            margin: 0 auto 10px;
        }
        
        .progress-step.active .step-number {
            background: #4a90e2;
            border-color: #4a90e2;
            color: white;
        }
        
        .progress-step.completed .step-number {
            background: #27ae60;
            border-color: #27ae60;
            color: white;
        }
        
        .step-label {
            font-size: 14px;
            color: #666;
        }
        
        .setup-card {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .setup-card h2 {
            margin-bottom: 10px;
        }
        
        .setup-card > p {
            color: #666;
            margin-bottom: 30px;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
        }
        
        .info-box i {
            color: #2196f3;
            font-size: 24px;
        }
        
        .info-box ol {
            margin: 10px 0 0 20px;
        }
        
        .setup-complete {
            text-align: center;
        }
        
        .success-icon i {
            font-size: 80px;
            color: #27ae60;
            margin-bottom: 20px;
        }
        
        .next-steps {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: left;
        }
        
        .next-steps ul {
            list-style: none;
            padding: 0;
        }
        
        .next-steps li {
            padding: 8px 0;
        }
        
        .next-steps li i {
            color: #27ae60;
            margin-right: 10px;
        }
    </style>
</body>
</html>
