<?php

error_log("Test Document Upload: Request received at " . date('Y-m-d H:i:s'));
error_log("Test Document Upload: Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Test Document Upload: Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    error_log("Test Document Upload: OPTIONS request, exiting");
    exit(0);
}

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$response = [
    'success' => false,
    'message' => '',
    'debug' => []
];

try {
    error_log("Test Document Upload: Processing request");
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $response['debug']['method'] = $_SERVER['REQUEST_METHOD'];
    $response['debug']['content_type'] = $_SERVER['CONTENT_TYPE'] ?? 'not set';
    $response['debug']['files_count'] = isset($_FILES) ? count($_FILES) : 0;
    
    error_log("Test Document Upload: Files count: " . $response['debug']['files_count']);
    error_log("Test Document Upload: FILES array: " . print_r($_FILES, true));
    
    if (isset($_FILES['document_file'])) {
        $file = $_FILES['document_file'];
        $response['debug']['file_info'] = [
            'name' => $file['name'] ?? 'not set',
            'type' => $file['type'] ?? 'not set',
            'size' => $file['size'] ?? 'not set',
            'error' => $file['error'] ?? 'not set',
            'tmp_name' => $file['tmp_name'] ?? 'not set'
        ];
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            $response['success'] = true;
            $response['message'] = 'File uploaded successfully';
            $response['suggested_title'] = 'Test Document - ' . $file['name'];
            $response['document_type'] = 'alte';
            $response['confidence'] = 0.8;
        } else {
            $response['message'] = 'File upload error: ' . $file['error'];
        }
    } else {
        $response['message'] = 'No file uploaded';
        $response['debug']['post_data'] = $_POST ?? 'no post data';
        $response['debug']['raw_input'] = file_get_contents('php://input');
    }
    
} catch (Exception $e) {
    $response['message'] = 'Exception: ' . $e->getMessage();
    $response['debug']['exception'] = $e->getTraceAsString();
}

    error_log("Test Document Upload: Final response: " . json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    error_log("Test Document Upload: Response sent successfully");
?> 