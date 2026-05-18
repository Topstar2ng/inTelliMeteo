<?php

// If the user is not logged in, redirect them to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>