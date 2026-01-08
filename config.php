<?php
// Global Configuration
define("BACKEND_URL", "https://mystore-backend-gk8t.onrender.com");
define("API_URL", BACKEND_URL . "/api");

// Session Configuration (ensure session starts only once)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
