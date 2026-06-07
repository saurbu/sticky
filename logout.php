<?php
session_start();

// 1. Clear all session memory variables
$_SESSION = array();

// 2. Erase the session cookie completely from the browser cache
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session on the server
session_destroy();

// 4. Redirect safely using a relative directory pointer
header("Location: ./login.php");
exit();
?>