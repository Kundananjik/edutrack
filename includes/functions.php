<?php

/**
 * File: includes/functions.php
 * Purpose: Common helpers (sanitization, redirects, auth checks, tokens, flash messages).
 */

/**
 * Sanitize user-supplied input for safe HTML output.
 * Trims, strips slashes, and encodes HTML special characters.
 */
function cleanInput($data)
{
    $data = trim($data);               // Remove whitespace from beginning/end
    $data = stripslashes($data);       // Remove backslashes
    $data = htmlspecialchars($data);   // Escape HTML special chars to prevent XSS
    return $data;
}

/**
 * Redirect helper to simplify and secure HTTP redirects.
 * Falls back to JS redirect when headers have already been sent.
 */
function redirect($url)
{
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    } else {
        // Fallback if headers already sent
        echo '<script ' . et_csp_attr('script') . ">window.location.href='" . addslashes($url) . "';</script>";
        exit();
    }
}
// functions.php

// Add your custom functions here

function isLoggedIn()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Generate a random token (for password resets, CSRF tokens, etc.)
function generateToken($length = 32)
{
    return bin2hex(random_bytes($length));
}

// Password hashing (wrapper, can switch algorithms easily)
function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

// Flash message setter (stores in session for next request)
function setFlashMessage($key, $message)
{
    if (!session_id()) {
        session_start();
    }
    $_SESSION['flash_messages'][$key] = $message;
}

// Flash message getter (and clears it)
function getFlashMessage($key)
{
    if (!session_id()) {
        session_start();
    }
    if (isset($_SESSION['flash_messages'][$key])) {
        $msg = $_SESSION['flash_messages'][$key];
        unset($_SESSION['flash_messages'][$key]);
        return $msg;
    }
    return null;
}

// Debug helper — prints readable variable info and exits
function dd($var)
{
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    exit();
}
