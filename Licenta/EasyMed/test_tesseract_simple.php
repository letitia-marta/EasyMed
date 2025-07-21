<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

error_log("Test Tesseract Simple: Request received at " . date('Y-m-d H:i:s'));

$response = [
    'shell_exec_available' => false,
    'tesseract_available' => false,
    'tesseract_version' => null,
    'tesseract_path' => null,
    'test_command' => null,
    'test_output' => null,
    'error' => null
];

if (function_exists('shell_exec')) {
    $response['shell_exec_available'] = true;
    error_log("Test Tesseract Simple: shell_exec is available");
    
    $tesseractPaths = [
        'tesseract',
        'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
        'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe'
    ];
    
    foreach ($tesseractPaths as $tesseract) {
        error_log("Test Tesseract Simple: testing path: $tesseract");
        
        $cmd = "\"$tesseract\" --version 2>&1";
        $output = shell_exec($cmd);
        
        error_log("Test Tesseract Simple: command: $cmd");
        error_log("Test Tesseract Simple: output: " . ($output ? $output : 'null'));
        
        if ($output && !strpos($output, 'command not found') && !strpos($output, 'is not recognized')) {
            $response['tesseract_available'] = true;
            $response['tesseract_version'] = trim($output);
            $response['tesseract_path'] = $tesseract;
            $response['test_command'] = $cmd;
            $response['test_output'] = $output;
            error_log("Test Tesseract Simple: Tesseract found at: $tesseract");
            break;
        }
    }
    
    if (!$response['tesseract_available']) {
        $response['error'] = 'Tesseract not found in any of the expected locations';
        error_log("Test Tesseract Simple: Tesseract not found");
    }
    
} else {
    $response['error'] = 'shell_exec function is not available';
    error_log("Test Tesseract Simple: shell_exec not available");
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?> 