<?php
// Get the directory where this file is located
$includes_dir = dirname(__FILE__) . '/../includes';
require_once $includes_dir . '/functions.php';

// Log admin action if admin is logging out
if (isLoggedIn() && isAdmin()) {
    logAdminAction($_SESSION['user_id'], 'Admin logged out');
}

// Destroy session
session_destroy();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to home page
redirect('../index.php', 'You have been successfully logged out.', 'success');
?> 