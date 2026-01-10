<?php
// mystorefrontend/api.php
// This file acts as a dedicated API Proxy/Handler and Library
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/api_helper.php';

// If accessed directly via browser/fetch, handle request proxying
// Example: /api.php?endpoint=auth/login
if (basename($_SERVER['PHP_SELF']) == 'api.php' && isset($_GET['endpoint'])) {
    
    header('Content-Type: application/json');
    
    // CORS (Optional, if needed for external access)
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }

    $endpoint = $_GET['endpoint'];
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Collect Data
    $data = $_GET;
    unset($data['endpoint']); // Remove endpoint from query params map
    
    if ($method === 'POST' || $method === 'PUT') {
        $input = file_get_contents('php://input');
        $json = json_decode($input, true);
        if (is_array($json)) {
            $data = array_merge($_POST, $json);
        } else {
            $data = array_merge($_POST, $data);
        }
    }

    try {
        $response = api_client($endpoint, $method, $data);
        echo json_encode($response);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
