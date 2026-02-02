<?php

// includes/auth_check.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the user is logged in
 *
 * @return bool
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

/**
 * Check if the current user is an admin
 *
 * @return bool
 */
function is_admin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if the current user has one of the allowed roles
 *
 * @param array $allowed_roles
 * @return bool
 */
function has_role(array $allowed_roles = [])
{
    return isset($_SESSION['role']) && in_array($_SESSION['role'], $allowed_roles, true);
}

/**
 * Redirect user if not logged in
 *
 * @param string $location
 */
function require_login($location = '../auth/login.php')
{
    if (!is_logged_in()) {
        header("Location: {$location}");
        exit();
    }
}

/**
 * Redirect user if they do not have required roles
 *
 * @param array $allowed_roles
 * @param string $redirect_location
 */
function require_role(array $allowed_roles = [], $redirect_location = '../includes/unauthorized.php')
{
    if (!has_role($allowed_roles)) {
        header("Location: {$redirect_location}");
        exit();
    }
}
