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

// Define BASE_URL for consistent linking when not loaded via includes/config.php
if (!defined('BASE_URL')) {
    $env_app_url = trim((string)($_ENV['APP_URL'] ?? (getenv('APP_URL') ?: '')));
    $http_host = $_SERVER['HTTP_HOST'] ?? '';
    $is_lan_host = $http_host !== '' && stripos($http_host, 'localhost') === false && strpos($http_host, '127.0.0.1') === false;
    $env_is_localhost = $env_app_url !== '' && stripos($env_app_url, 'localhost') !== false;

    if ($env_app_url !== '' && !($env_is_localhost && $is_lan_host)) {
        $app_url = $env_app_url;
    } else {
        $https = $_SERVER['HTTPS'] ?? '';
        $scheme = (!empty($https) && strtolower((string)$https) !== 'off') ? 'https' : 'http';
        $host = $http_host ?: 'localhost';
        $script_name = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
        $base_path = '/edutrack/';
        if ($script_name !== '' && preg_match('#^(.*?/edutrack/)#i', $script_name, $m)) {
            $base_path = $m[1];
        }
        $app_url = $scheme . '://' . $host . $base_path;
    }
    define('BASE_URL', rtrim($app_url, '/') . '/');
    unset($app_url, $env_app_url, $http_host, $is_lan_host, $env_is_localhost, $https, $scheme, $host, $script_name, $base_path, $m);
}
