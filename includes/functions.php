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

// Security helpers for rotating, signed attendance QR tokens
if (!function_exists('et_qr_token_secret')) {
    function et_qr_token_secret()
    {
        $secret = $_ENV['QR_TOKEN_SECRET'] ?? (getenv('QR_TOKEN_SECRET') ?: '');
        if ($secret === '') {
            $secret = $_ENV['APP_KEY'] ?? (getenv('APP_KEY') ?: '');
        }
        if ($secret === '') {
            $secret = 'edutrack-default-qr-secret-change-me';
        }
        return (string)$secret;
    }
}

if (!function_exists('et_generate_session_qr_token')) {
    function et_generate_session_qr_token($sessionId, $sessionCode, $timestamp = null)
    {
        $sessionId = (int)$sessionId;
        $sessionCode = (string)$sessionCode;
        $timestamp = $timestamp === null ? time() : (int)$timestamp;
        $ttlSeconds = 20;
        $bucket = (int)floor($timestamp / $ttlSeconds);

        $payload = $sessionId . '|' . $bucket . '|' . $sessionCode;
        $signature = substr(hash_hmac('sha256', $payload, et_qr_token_secret()), 0, 20);

        return 'ETQR1.' . $sessionId . '.' . $bucket . '.' . $signature;
    }
}

if (!function_exists('et_validate_session_qr_token')) {
    function et_validate_session_qr_token($token, $sessionId, $sessionCode)
    {
        $token = trim((string)$token);
        $sessionId = (int)$sessionId;
        $sessionCode = (string)$sessionCode;

        if (!preg_match('/^ETQR1\.(\d+)\.(\d+)\.([a-f0-9]{20})$/i', $token, $m)) {
            return false;
        }

        $tokenSessionId = (int)$m[1];
        $bucket = (int)$m[2];
        $signature = strtolower($m[3]);

        if ($tokenSessionId !== $sessionId) {
            return false;
        }

        $currentBucket = (int)floor(time() / 20);
        if (abs($currentBucket - $bucket) > 1) {
            return false;
        }

        $payload = $sessionId . '|' . $bucket . '|' . $sessionCode;
        $expected = strtolower(substr(hash_hmac('sha256', $payload, et_qr_token_secret()), 0, 20));

        return hash_equals($expected, $signature);
    }
}

if (!function_exists('et_env_bool')) {
    function et_env_bool($key, $default = false)
    {
        $raw = $_ENV[$key] ?? (getenv($key) ?: null);
        if ($raw === null || $raw === '') {
            return (bool)$default;
        }
        $value = strtolower(trim((string)$raw));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('et_client_ip')) {
    function et_client_ip()
    {
        $candidates = [
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
            $_SERVER['HTTP_X_REAL_IP'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        foreach ($candidates as $entry) {
            if (!$entry) {
                continue;
            }
            $first = trim(explode(',', $entry)[0]);
            if (filter_var($first, FILTER_VALIDATE_IP)) {
                return $first;
            }
        }
        return '';
    }
}

if (!function_exists('et_device_fingerprint_hash')) {
    function et_device_fingerprint_hash()
    {
        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        $lang = (string)($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
        $ip = et_client_ip();
        $material = implode('|', [$ua, $lang, $ip]);
        return hash('sha256', $material);
    }
}

if (!function_exists('et_db_column_exists')) {
    function et_db_column_exists($pdo, $table, $column)
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        try {
            $stmt = $pdo->prepare('
                SELECT COUNT(*) 
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = ?
            ');
            $stmt->execute([$table, $column]);
            $exists = ((int)$stmt->fetchColumn()) > 0;
            $cache[$key] = $exists;
            return $exists;
        } catch (Throwable $e) {
            $cache[$key] = false;
            return false;
        }
    }
}
