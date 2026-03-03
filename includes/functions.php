<?php

/**
 * File: includes/functions.php
 * Purpose: Common helpers (sanitization, redirects, auth checks, tokens, flash messages).
 */

/**
 * Sanitize user-supplied input for safe HTML output.
 * Trims, strips slashes, and encodes HTML special characters.
 */
if (!function_exists('cleanInput')) {
    function cleanInput($data)
    {
        $data = trim($data);               // Remove whitespace from beginning/end
        $data = stripslashes($data);       // Remove backslashes
        $data = htmlspecialchars($data);   // Escape HTML special chars to prevent XSS
        return $data;
    }
}

/**
 * Redirect helper to simplify and secure HTTP redirects.
 * Falls back to JS redirect when headers have already been sent.
 */
if (!function_exists('redirect')) {
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
}
// functions.php

// Add your custom functions here

if (!function_exists('isLoggedIn')) {
    function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }
}

// Generate a random token (for password resets, CSRF tokens, etc.)
if (!function_exists('generateToken')) {
    function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }
}

// Password hashing (wrapper, can switch algorithms easily)
if (!function_exists('hashPassword')) {
    function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

// Verify password
if (!function_exists('verifyPassword')) {
    function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}

// Generate versioned asset URL for cache busting (uses BASE_URL + ASSET_VERSION if defined)
if (!function_exists('asset_url')) {
    function asset_url($path)
    {
        $base = defined('BASE_URL') ? BASE_URL : '/';
        $base = rtrim($base, '/') . '/';
        $ver = defined('ASSET_VERSION') ? ASSET_VERSION : null;
        $url = $base . ltrim($path, '/');
        if ($ver) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . 'v=' . $ver;
        }
        return $url;
    }
}

// Flash message setter (stores in session for next request)
if (!function_exists('setFlashMessage')) {
    function setFlashMessage($key, $message)
    {
        if (!session_id()) {
            session_start();
        }
        $_SESSION['flash_messages'][$key] = $message;
    }
}

// Flash message getter (and clears it)
if (!function_exists('getFlashMessage')) {
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
}

// Debug helper — prints readable variable info and exits
if (!function_exists('dd')) {
    function dd($var)
    {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        exit();
    }
}
