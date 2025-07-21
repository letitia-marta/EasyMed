<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

error_log("=== JSON TEST ENDPOINT ===");
error_log("Request received at " . date('Y-m-d H:i:s'));
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
error_log("User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'not set'));

$response = [
    'success' => true,
    'suggested_title' => 'Test Document',
    'document_type' => 'scrisori',
    'confidence' => 0.8,
    'error' => null,
    'debug' => [
        'test' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]
];

$jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
error_log("JSON response length: " . strlen($jsonResponse));
error_log("JSON response: " . $jsonResponse);

echo $jsonResponse;
error_log("=== END JSON TEST ENDPOINT ===");
?> 