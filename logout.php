<?php
/**
 * IntelliMeteo Logout Handler
 * Destroys current session and redirects to login.
 */

// Initialize the session
session_start();

// Unset all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Redirect to login page with a success message
header("Location: login.php?msg=logged_out");
exit();
?>