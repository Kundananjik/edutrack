<?php

/**
 * File: includes/preload.php
 * Purpose: Register global error/exception handlers, apply security headers, and bootstrap the app
 *          so any PHP entry point can behave consistently.
 * Includes: includes/error_handlers.php, includes/security_headers.php, config/bootstrap.php
 * Defines:  BASE_PATH (project root) if not already defined
 */

// Ensure error handlers are registered immediately
require_once __DIR__ . '/error_handlers.php';
// Apply security headers
require_once __DIR__ . '/security_headers.php';

// Locate and include config/bootstrap.php by walking up the tree
$__et_dir = __DIR__;
for ($__i = 0; $__i < 6; $__i++) {
    $__candidate = $__et_dir . '/config/bootstrap.php';
    if (file_exists($__candidate)) {
        require_once $__candidate;
        break;
    }
    $__et_dir = dirname($__et_dir);
}
unset($__candidate, $__et_dir, $__i);

// Optional: define BASE_PATH if bootstrap didn't
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/..'));
}
