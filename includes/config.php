<?php

// includes/config.php

// Register error handlers when config is used standalone
$eh = __DIR__ . '/error_handlers.php';
if (file_exists($eh)) {
    require_once $eh;
}

// Composer autoload with fallback
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    if (function_exists('et_simple_error_page')) {
        et_simple_error_page('Missing dependencies', 'Composer autoload not found. Run <code>composer install</code>.');
        exit(1);
    } else {
        die('Composer autoload not found. Run composer install.');
    }
}
require_once $autoload;

use Dotenv\Dotenv;

// Load environment variables from .env (in project root)
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad(); // safeLoad() won't throw errors if .env is missing
} catch (Throwable $e) {
    error_log('Dotenv load error (includes/config.php): ' . $e->getMessage());
}

// Database credentials from .env, with backward-compatible fallbacks
// Prefer: DB_HOST, DB_NAME, DB_USER, DB_PASS
// Fallbacks: DB_DATABASE -> DB_NAME, DB_USERNAME -> DB_USER, DB_PASSWORD -> DB_PASS
$__DB_HOST = $_ENV['DB_HOST'] ?? 'localhost';
$__DB_NAME = $_ENV['DB_NAME'] ?? ($_ENV['DB_DATABASE'] ?? 'edutrack_db');
$__DB_USER = $_ENV['DB_USER'] ?? ($_ENV['DB_USERNAME'] ?? 'root');
$__DB_PASS = $_ENV['DB_PASS'] ?? ($_ENV['DB_PASSWORD'] ?? '');

define('DB_HOST', $__DB_HOST);
define('DB_NAME', $__DB_NAME);
define('DB_USER', $__DB_USER);
define('DB_PASS', $__DB_PASS);
unset($__DB_HOST, $__DB_NAME, $__DB_USER, $__DB_PASS);

// Base URL for redirects or asset linking
define('BASE_URL', $_ENV['APP_URL'] ?? 'http://localhost/edutrack/');
