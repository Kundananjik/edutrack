<?php

/**
 * File: includes/db.php
 * Purpose: Lightweight PDO bootstrap using constants from includes/config.php
 * Constants: DB_HOST, DB_NAME, DB_USER, DB_PASS
 * Ensures: pdo_mysql is loaded; errors handled based on APP_ENV
 */

require_once __DIR__ . '/config.php';

// Guard for missing PDO MySQL extension
if (!extension_loaded('pdo_mysql')) {
    error_log('pdo_mysql extension is not loaded.');
    if (function_exists('et_simple_error_page')) {
        et_simple_error_page('Missing PHP extension', 'The pdo_mysql extension is required to connect to MySQL.');
        exit(1);
    }
    die('The pdo_mysql extension is required to connect to MySQL.');
}

try {
    // Use persistent connections for performance (optional)
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
        PDO::ATTR_PERSISTENT         => false,                  // Change to true for persistence
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'     // Ensure UTF-8 support
    ];

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    // Production-friendly error handling
    if (($_ENV['APP_ENV'] ?? 'development') === 'production') {
        error_log('Database Connection Failed: ' . $e->getMessage()); // Log instead of showing
        die('A database error occurred. Please try again later.');
    } else {
        die('Database Connection Failed: ' . $e->getMessage());
    }
}
