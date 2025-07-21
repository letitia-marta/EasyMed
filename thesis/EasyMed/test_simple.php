<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

error_log("Test Simple: Request received at " . date('Y-m-d H:i:s'));
error_log("Test Simple: Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Test Simple: Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

$response = [
    'success' => true,
    'message' => 'Simple test successful',
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) {
    $file = $_FILES['document_file'];
    $response['file_info'] = [
        'name' => $file['name'],
        'size' => $file['size'],
        'type' => $file['type'],
        'error' => $file['error']
    ];
    error_log("Test Simple: File uploaded: " . $file['name']);
} else {
    $response['file_info'] = 'No file uploaded';
    error_log("Test Simple: No file uploaded");
}

$jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if ($jsonResponse === false) {
    error_log("Test Simple: JSON encoding failed: " . json_last_error_msg());
    echo json_encode([
        'success' => false,
        'error' => 'JSON encoding failed: ' . json_last_error_msg()
    ]);
} else {
    error_log("Test Simple: Response sent: " . $jsonResponse);
    echo $jsonResponse;
}
?> 