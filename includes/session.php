<?php
/**
 * File: includes/session.php
 * Purpose: Session initialization and hygiene (ID regeneration, idle timeout).
 * Notes: If you serve over HTTPS, ensure cookies are Secure (set in a higher-level bootstrap if needed).
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional: Regenerate session ID once per session for security
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// === Session Timeout (30 minutes) ===
$timeout_duration = 1800; // 30 minutes

if (isset($_SESSION['LAST_ACTIVITY']) && 
    (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    
    // Destroy session and redirect to login with timeout flag
    session_unset();
    session_destroy();

    header("Location: /edutrack/index.php?timeout=true");
    exit();
}

// Update activity timestamp
$_SESSION['LAST_ACTIVITY'] = time();
?>
