<?php

/**
 * File: config/database.php
 * Purpose: Create a shared PDO connection ($pdo) using environment variables.
 * Env keys (preferred): DB_HOST, DB_NAME, DB_USER, DB_PASS
 * Behavior: Throws in non-production; logs and shows generic message in production.
 */

// Using environment variables (recommended for production)
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env file if it exists
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad(); // safeLoad prevents errors if .env is missing

// Friendly guard for missing PDO MySQL driver
if (!extension_loaded('pdo_mysql')) {
    error_log('pdo_mysql extension is not loaded.');
    if (function_exists('et_simple_error_page')) {
        et_simple_error_page('Missing PHP extension', 'The pdo_mysql extension is required to connect to MySQL.');
    } else {
        die('The pdo_mysql extension is required to connect to MySQL.');
    }
}

$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbName = $_ENV['DB_NAME'] ?? 'edutrack_db';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? '';

try {
    // DSN string
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

    // PDO instance
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays by default
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
    ]);

} catch (PDOException $e) {
    // Log the error instead of displaying it in production
    error_log('Database connection error: ' . $e->getMessage());
    if (($_ENV['APP_ENV'] ?? 'development') === 'production') {
        die('A database connection error occurred. Please try again later.');
    }
    // Show detailed message in non-production to help debugging
    die('Database connection failed: ' . $e->getMessage());
}
