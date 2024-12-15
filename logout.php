<?php
// Start the session
session_start();

// Store username for success message
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page with success message
header("Location: login.php");
exit();
?>