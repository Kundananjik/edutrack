<?php

/**
 * File: public/index.php
 * Purpose: Front controller and basic router. All requests are rewritten here by .htaccess.
 * Routing strategy:
 *  - '' or '/' -> root index.php
 *  - Allow-listed root pages -> require directly
 *  - Allow-listed directory prefixes -> map to corresponding .php file
 *  - Otherwise 404
 */
require_once __DIR__ . '/../includes/preload.php';

$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$requested = $_GET['path'] ?? '';
$requested = trim($requested, "/\t\n\r\0\x0B");

// Helper: safe join within project base
function et_path(string $rel): string
{
    return realpath(BASE_PATH . '/' . $rel) ?: BASE_PATH . '/' . $rel;
}

// 1) Root route
if ($requested === '' || $requested === '/') {
    require et_path('index.php');
    exit;
}

// 2) Allowlist of root pages without directory
$rootPages = [
    'about', 'help', 'contact', 'privacy-policy', 'terms-of-service',
    'send_message', 'send_messages', 'logout'
];
if (in_array($requested, $rootPages, true)) {
    $candidate = et_path($requested . '.php');
    if (is_file($candidate)) {
        require $candidate;
        exit;
    }
}

// 3) Allow-listed directories
$allowedPrefixes = [
    'auth',
    'pages/admin',
    'pages/lecturer',
    'pages/student',
    'controllers/student',
];

foreach ($allowedPrefixes as $prefix) {
    if ($requested === $prefix) {
        // default to index.php under the directory if present
        $candidate = et_path($prefix . '/index.php');
        if (is_file($candidate)) {
            require $candidate;
            exit;
        }
    }
    if (str_starts_with($requested, $prefix . '/')) {
        $candidate = et_path($requested . '.php');
        if (is_file($candidate)) {
            require $candidate;
            exit;
        }
    }
}

// 4) Not found
http_response_code(404);
et_simple_error_page('Not Found', 'The requested page could not be found.');
