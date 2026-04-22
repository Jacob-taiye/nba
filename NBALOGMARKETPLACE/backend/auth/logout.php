<?php
/**
 * VirtualHub Pro — Logout
 * Works for both users and admins
 */
session_start();
session_unset();
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

header('Location: /NBALOGMARKETPLACE/frontend/pages/login.php');
exit;