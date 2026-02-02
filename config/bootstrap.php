<?php

/**
 * File: config/bootstrap.php
 * Purpose: Bootstraps the runtime — registers error handlers, loads Composer autoload,
 *          loads environment variables (.env), and includes the database bootstrap.
 * Notes: Uses safeLoad() for Dotenv to avoid fatal errors if .env is absent.
 */

// Register global error/exception handlers early
require_once __DIR__ . '/../includes/error_handlers.php';

// Load Composer's autoloader with a friendly fallback
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    et_simple_error_page(
        'Missing dependencies',
        'Composer autoload not found. Please run <code>composer install</code> in the project root.'
    );
    exit(1);
}
require_once $autoload;

// Load environment variables from the root .env file (non-fatal if missing)
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    // safeLoad prevents exceptions if .env is absent
    $dotenv->safeLoad();
} catch (Throwable $e) {
    // Log and continue; handlers will show details in debug mode
    error_log('Dotenv load error: ' . $e->getMessage());
}

// Include the database connection with fallback
$dbFile = __DIR__ . '/database.php';
if (file_exists($dbFile)) {
    require_once $dbFile;
} else {
    et_simple_error_page(
        'Database bootstrap missing',
        'Expected config/database.php but it was not found.'
    );
}

// Optional: Define a global base path for consistent file referencing
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/../'));
}
