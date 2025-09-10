<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to check if user is admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Function to get current user ID
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Function to get current user data
function get_current_user_data() {
    if (!is_logged_in()) {
        return null;
    }
    
    require_once __DIR__ . '/../../config/database.php';
    $user_id = get_current_user_id();
    $sql = "SELECT id, name, email, role FROM users WHERE id = '$user_id'";
    return get_single_row($sql);
}

// Function to require login
function require_login() {
    if (!is_logged_in()) {
        header('Location: /login');
        exit();
    }
}

// Function to require admin
function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: /');
        exit();
    }
}

// Function to logout
function logout() {
    session_destroy();
    header('Location: /');
    exit();
}
?>
