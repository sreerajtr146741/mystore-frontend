<?php
// =====================================
// MyStore Frontend Configuration
// =====================================

// Backend Base URL (Render)
define('BACKEND_URL', 'https://mystore-backend-gk8t.onrender.com');

// Backend API Base URL
define('API_BASE_URL', BACKEND_URL . '/api/');

// Start the session *only once* and NEVER call ini_set while session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// (REMOVE ini_set — it caused the warnings)

// Uncomment during debugging only:
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

?>
