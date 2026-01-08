<?php

function api_client($endpoint, $method = 'GET', $data = []) {
    $url = API_URL . '/' . ltrim($endpoint, '/');
    
    $headers = [
        "Content-Type: application/json",
        "Accept: application/json"
    ];
    
    // Attach Token if available
    if (isset($_SESSION['api_token'])) {
        $headers[] = "Authorization: Bearer " . $_SESSION['api_token'];
    }
    
    $options = [
        "http" => [
            "header" => implode("\r\n", $headers),
            "method" => $method,
            "ignore_errors" => true, // Fetch body even on 4xx/5xx
            "timeout" => 15 // Timeout in seconds
        ],
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false
        ]
    ];
    
    if ($method !== 'GET') {
        $options['http']['content'] = json_encode($data);
    }
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        // Fallback or Error Handling
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
