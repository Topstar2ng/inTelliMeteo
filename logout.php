<?php
require_once 'includes/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    // Clear token from database
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

// Destroy all session arrays
$_SESSION = array();
session_destroy();

// Delete the tracking cookie explicitly by setting its expiration date in the past
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

// Redirect to login page with a success message
header("Location: login.php?msg=logged_out");
exit();
?>