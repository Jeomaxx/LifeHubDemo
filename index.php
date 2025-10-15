<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

header('Location: /login.php');
exit;
