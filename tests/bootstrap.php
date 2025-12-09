<?php
// tests/bootstrap.php
$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/vendor/autoload.php';

// Load .env if present
if (file_exists($projectRoot . '/.env')) {
    Dotenv\Dotenv::createImmutable($projectRoot)->safeLoad();
}

// Default to testing
$_ENV['APP_ENV'] = $_ENV['APP_ENV'] ?? 'testing';
$_ENV['APP_DEBUG'] = $_ENV['APP_DEBUG'] ?? '1';

// Ensure constants and helpers are loaded
require_once $projectRoot . '/includes/error_handlers.php';
require_once $projectRoot . '/includes/security_headers.php';
require_once $projectRoot . '/includes/config.php';
