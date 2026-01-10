<?php

function api_client($endpoint, $method = 'GET', $data = []) {
    // Handle specific user config change (API_URL -> API_BASE_URL)
    $baseUrl = defined('API_BASE_URL') ? API_BASE_URL : API_URL;
    $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');
    
    $headers = [
        "Content-Type: application/json",
        "Accept: application/json"
    ];
    
    // Attach Token if available
    if (isset($_SESSION['api_token'])) {
        $headers[] = "Authorization: Bearer " . $_SESSION['api_token'];
    }
    
    // Use cURL instead of file_get_contents for better performance and error handling
    $ch = curl_init();
    
    // Set URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    // Headers
    $curlHeaders = [];
    foreach ($headers as $h) {
        $curlHeaders[] = $h;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
    
    // Method & Data
    if ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    // SSL (Disable verify for dev/test if needed, strictly should be true in prod)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($result === FALSE) {
        // Log error or handle gracefully
        error_log("API Error: $error");
        return null;
    }
    
    $response = json_decode($result);
    // Return object or array depending on need. Using Objects for Blade compatibility ($x->field)
    return $response;
}

// Helper to resolve image URLs from Backend
function backend_img($path) {
    if (!$path) return 'https://via.placeholder.com/400x300?text=No+Image';
    if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
    return BACKEND_URL . '/storage/' . ltrim($path, '/');
}

// Helper to safely access object properties
function safe_get($obj, $prop, $default = null) {
    return $obj->$prop ?? $default;
}

// Helper to detect AJAX triggers or explicitly passed ?ajax=1
function is_ajax() {
    return isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}
