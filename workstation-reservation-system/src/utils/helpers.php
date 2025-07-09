<?php
// Utility functions for the Workstation Reservation System

/**
 * Sanitize input data to prevent XSS and SQL injection.
 *
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Redirect to a specified URL.
 *
 * @param string $url
 */
function redirectTo($url) {
    header("Location: $url");
    exit();
}

/**
 * Check if the user is logged in.
 *
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get the current user's role.
 *
 * @return string|null
 */
function getUserRole() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

/**
 * Flash a message to the session for one-time display.
 *
 * @param string $message
 */
function flashMessage($message) {
    $_SESSION['flash_message'] = $message;
}

/**
 * Get the flashed message from the session.
 *
 * @return string|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}
?>