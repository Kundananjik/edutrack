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
    unset($env_app_url, $http_host, $is_lan_host, $env_is_localhost, $https, $scheme, $host, $script_name, $base_path, $app_url, $m);
}
