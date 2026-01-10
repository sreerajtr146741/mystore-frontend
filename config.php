<?php
// ================================
// MyStore Frontend Configuration
// ================================

// Backend Base URL (Render Deployment)
define('BACKEND_URL', 'https://mystore-backend-gk8t.onrender.com');

// Backend API Base URL
define('API_BASE_URL', BACKEND_URL . '/api/');

// Application Name
define('APP_NAME', 'MyStore');

// -------------------------------
// Session Configuration
// -------------------------------

// Start session only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();

    // Security improvements
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
}

// -------------------------------
// Error Reporting (Debug Mode)
// Uncomment only for debugging
// -------------------------------
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

?>
