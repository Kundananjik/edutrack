<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);

// logout.php

// Start a new session or resume the existing one.
session_start();

// Destroy all session variables.
// This is the most secure way to ensure the user is logged out.
$_SESSION = [];

// Destroy the session itself.
// This clears the session file on the server.
session_destroy();

// Redirect the user to the login page.
// The header() function must be called before any output is sent to the browser.
header("Location: auth/login.php");

// It's a good practice to exit the script after a redirect to prevent any further execution.
exit;

?>
