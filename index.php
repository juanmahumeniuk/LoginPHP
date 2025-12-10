<?php
// index.php - Main entry point
// Enable error reporting for debugging (disable in production later)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the base directory
define('BASE_DIR', __DIR__);

// Route to Backend controller
require_once BASE_DIR . '/Backend/database.php';
require_once BASE_DIR . '/Backend/auth.php';

// Handle the request
$action = $_GET['action'] ?? '';

// If it's a Google OAuth callback (has 'code' parameter)
if (isset($_GET['code'])) {
    // Handle Google Callback
    $code = $_GET['code'];
    $tokenData = getGoogleAccessToken($code);

    if (isset($tokenData['access_token'])) {
        $userData = getGoogleUserInfo($tokenData['access_token']);

        if (isset($userData['email'])) {
            if (loginWithGoogle($userData['email'])) {
                // Redirect to dashboard on success
                header("Location: /Frontend/index.html");
                exit;
            }
        }
    }

    // On failure
    header("Location: /Frontend/login.html?error=google_failed");
    exit;
}

// If it's a login_google action
if ($action === 'login_google') {
    header("Location: " . getGoogleLoginUrl());
    exit;
}

// For all other requests, redirect to Backend/index.php
if (is_logged_in()) {
    readfile(BASE_DIR . '/Frontend/index.html');
} else {
    readfile(BASE_DIR . '/Frontend/login.html');
}
?>