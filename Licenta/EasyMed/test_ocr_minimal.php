<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

error_log("Test OCR Minimal: Request received at " . date('Y-m-d H:i:s'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) {
    try {
        $file = $_FILES['document_file'];
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            error_log("Test OCR Minimal: File upload OK");
            $tempPath = $file['tmp_name'];
            $originalName = $file['name'];
            $fileType = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            
            error_log("Test OCR Minimal: Temp path: $tempPath");
            error_log("Test OCR Minimal: Original name: $originalName");
            error_log("Test OCR Minimal: File type: $fileType");
            
            if (!file_exists($tempPath)) {
                throw new Exception("Uploaded file not found at: $tempPath");
            }
            
            $extractedText = '';
            $extractionMethod = 'None';
            
            if (in_array($fileType, ['jpg', 'jpeg', 'png'])) {
                $extractionMethod = 'OCR (Tesseract)';
                
                if (function_exists('shell_exec')) {
                    $testCmd = 'tesseract --version 2>&1';
                    $testOutput = shell_exec($testCmd);
                    
                    if ($testOutput && !strpos($testOutput, 'command not found')) {
                        $cmd = "tesseract \"$tempPath\" stdout -l ron+eng 2>&1";
                        error_log("Test OCR Minimal: Running OCR command: $cmd");
                        $extractedText = shell_exec($cmd);
                        error_log("Test OCR Minimal: OCR raw output: " . ($extractedText ? $extractedText : 'null'));
                        $extractedText = trim($extractedText);
                        
                        if (empty($extractedText)) {
                            error_log("Test OCR Minimal: Trying different OCR settings...");
                            
                            $psmModes = [6, 3, 8, 13];
                            foreach ($psmModes as $psm) {
                                $cmd = "tesseract \"$tempPath\" stdout -l ron+eng --psm $psm 2>&1";
                                error_log("Test OCR Minimal: Trying PSM $psm: $cmd");
                                $testText = shell_exec($cmd);
                                $testText = trim($testText);
                                
                                if (!empty($testText) && $testText !== 'No text extracted from image') {
                                    $extractedText = $testText;
                                    error_log("Test OCR Minimal: Success with PSM $psm, text: $extractedText");
                                    break;
                                }
                            }
                            
                            if (empty($extractedText)) {
                                $extractedText = 'No text extracted from image';
                                error_log("Test OCR Minimal: No text extracted - possible reasons:");
                                error_log("Test OCR Minimal: 1. Image contains no text");
                                error_log("Test OCR Minimal: 2. Text quality too poor for OCR");
                                error_log("Test OCR Minimal: 3. Text is too small or unclear");
                                error_log("Test OCR Minimal: 4. Image is too dark or blurry");
                            }
                        } else {
                            error_log("Test OCR Minimal: Text extracted successfully, length: " . strlen($extractedText));
                        }
                    } else {
                        $extractedText = 'Tesseract not available';
                    }
                } else {
                    $extractedText = 'shell_exec not available';
                }
            } elseif ($fileType === 'pdf') {
                $extractionMethod = 'PDF Text Extraction';
                $extractedText = 'PDF extraction not implemented in minimal test';
            } elseif (in_array($fileType, ['doc', 'docx'])) {
                $extractionMethod = 'Word Document Extraction';
                $extractedText = 'Word extraction not implemented in minimal test';
            } else {
                $extractionMethod = 'Unknown';
                $extractedText = 'Unsupported file type';
            }
            
            $response = [
                'success' => true,
                'file_type' => $fileType,
                'text_length' => strlen($extractedText),
                'extraction_method' => $extractionMethod,
                'extracted_text' => $extractedText,
                'file_size' => filesize($tempPath),
                'file_exists' => file_exists($tempPath),
                'has_text' => !empty($extractedText) && $extractedText !== 'No text extracted from image'
            ];
            
            $jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            if ($jsonResponse === false) {
                error_log("Test OCR Minimal: JSON encoding failed: " . json_last_error_msg());
                echo json_encode([
                    'success' => false,
                    'error' => 'JSON encoding failed: ' . json_last_error_msg()
                ]);
            } else {
                error_log("Test OCR Minimal: JSON response: " . $jsonResponse);
                echo $jsonResponse;
            }
            
        } else {
            error_log("Test OCR Minimal: File upload error: " . $file['error']);
            echo json_encode([
                'success' => false,
                'error' => 'File upload error: ' . $file['error']
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Test OCR Minimal: Exception caught: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Extraction failed: ' . $e->getMessage()
        ]);
    }
} else {
    error_log("Test OCR Minimal: Invalid request method or no file uploaded");
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method or no file uploaded'
    ]);
}
?> 