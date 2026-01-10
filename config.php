<?php
// Global Configuration for MyStore Frontend

// Backend URL (Render Deployment)
define("BACKEND_URL", "https://mystore-backend-gk8t.onrender.com");

// API Base URL
define("API_URL", BACKEND_URL . "/api");

// Application Name
define("APP_NAME", "MyStore");

// Session Configuration
// Ensure session is started only once to prevent errors
if (session_status() === PHP_SESSION_NONE) {
    // Optional: Set session cookie params standard
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Error Reporting (Enable for Debugging, Disable for Production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
