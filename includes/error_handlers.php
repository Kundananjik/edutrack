<?php
/**
 * File: includes/error_handlers.php
 * Purpose: Centralized error/exception handling for the application.
 * Behavior:
 *  - Honors APP_ENV and APP_DEBUG to decide whether to display detailed errors.
 *  - Converts PHP errors to ErrorException (set_error_handler) for consistent catching.
 *  - Renders user-friendly pages for uncaught exceptions and fatal errors.
 * Helper:
 *  - et_simple_error_page(string $title, string $message, array $context = []): quick HTML error page.
 */

// Determine environment/debug mode as early as possible
$env = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'development';
$debugFlag = $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG');
$debug = !($env === 'production' && ($debugFlag === '0' || strtolower((string)$debugFlag) === 'false'));

// Sensible defaults
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');

function et_simple_error_page(string $title, string $message, array $context = []): void {
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    $ctx = '';
    if (!empty($context)) {
        $ctx = '<pre style="background:#f8f9fa;padding:12px;border:1px solid #eee;overflow:auto;">' . htmlspecialchars(print_r($context, true)) . '</pre>';
    }
    echo '<!doctype html><html><head><meta charset="utf-8"><title>' . htmlspecialchars($title) . '</title>' .
         '<style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu;max-width:820px;margin:40px auto;padding:0 16px;color:#222} .box{border:1px solid #e5e7eb;border-radius:8px;padding:16px;background:#fff} h1{font-size:20px;margin:0 0 8px} .muted{color:#6b7280}</style>' .
         '</head><body><div class="box"><h1>' . htmlspecialchars($title) . '</h1><div class="muted">' . $message . '</div>' . $ctx . '</div></body></html>';
}

// Convert PHP errors to ErrorException so they can be caught
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false; // Use PHP's internal handler
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Handle uncaught exceptions
set_exception_handler(function (Throwable $e) use ($debug) {
    error_log('Uncaught Exception: ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if ($debug) {
        et_simple_error_page('Application Error', htmlspecialchars($e->getMessage()), [
            'type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    } else {
        et_simple_error_page('Something went wrong', 'An unexpected error occurred. Please try again later.');
    }
});

// Catch fatal errors on shutdown
register_shutdown_function(function () use ($debug) {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('Fatal Error: ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']);
        if ($debug) {
            et_simple_error_page('Fatal Error', htmlspecialchars($err['message']), $err);
        } else {
            et_simple_error_page('Service temporarily unavailable', 'Please try again later.');
        }
    }
});
