<?php
// api-test.php - Test API with different HTTP methods

// Start session
session_start();

// Set CORS headers to allow testing from other origins
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle OPTIONS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Capture raw input
$rawInput = file_get_contents('php://input');

// Prepare response
$response = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'time' => date('Y-m-d H:i:s'),
    'sessionActive' => session_status() === PHP_SESSION_ACTIVE,
    'sessionId' => session_id(),
    'get' => $_GET,
    'post' => $_POST,
    'rawInput' => $rawInput,
    'headers' => getallheaders(),
    'authHeader' => isset(getallheaders()['Authorization']) ? getallheaders()['Authorization'] : 'Not present'
];

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
