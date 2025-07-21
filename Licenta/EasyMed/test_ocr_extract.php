<?php
require_once 'ai_document_analyzer.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

error_log("Test OCR Extract: Request received at " . date('Y-m-d H:i:s'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) {
    try {
        $analyzer = new DocumentAnalyzer();
        $file = $_FILES['document_file'];
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            error_log("Test OCR Extract: File upload OK");
            $tempPath = $file['tmp_name'];
            $originalName = $file['name'];
            $fileType = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            
            error_log("Test OCR Extract: Temp path: $tempPath");
            error_log("Test OCR Extract: Original name: $originalName");
            error_log("Test OCR Extract: File type: $fileType");
            
            if (!file_exists($tempPath)) {
                throw new Exception("Uploaded file not found at: $tempPath");
            }
            
            error_log("Test OCR Extract: Starting text extraction...");
            $extractedText = $analyzer->extractTextFromDocument($tempPath, $originalName);
            error_log("Test OCR Extract: Text extraction completed, length: " . strlen($extractedText));
                
            $extractionMethod = "Unknown";
            switch ($fileType) {
                case 'pdf':
                    $extractionMethod = "PDF Text Extraction (pdftotext)";
                    break;
                case 'jpg':
                case 'jpeg':
                case 'png':
                    $extractionMethod = "OCR (Tesseract)";
                    break;
                case 'doc':
                case 'docx':
                    $extractionMethod = "Word Document Extraction";
                    break;
            }
            
            $response = [
                'success' => true,
                'file_type' => $fileType,
                'text_length' => strlen($extractedText),
                'extraction_method' => $extractionMethod,
                'extracted_text' => $extractedText,
                'file_size' => filesize($tempPath),
                'file_exists' => file_exists($tempPath),
                'has_text' => !empty($extractedText),
                'ocr_failed' => empty($extractedText) && in_array($fileType, ['jpg', 'jpeg', 'png'])
            ];
            
            $jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            if ($jsonResponse === false) {
                error_log("Test OCR Extract: JSON encoding failed: " . json_last_error_msg());
                echo json_encode([
                    'success' => false,
                    'error' => 'JSON encoding failed: ' . json_last_error_msg(),
                    'raw_response' => $response
                ]);
            } else {
                error_log("Test OCR Extract: JSON response: " . $jsonResponse);
                echo $jsonResponse;
            }
            
        } else {
            error_log("Test OCR Extract: File upload error: " . $file['error']);
            echo json_encode([
                'success' => false,
                'error' => 'File upload error: ' . $file['error']
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Test OCR Extract: Exception caught: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Extraction failed: ' . $e->getMessage()
        ]);
    }
} else {
    error_log("Test OCR Extract: Invalid request method or no file uploaded");
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method or no file uploaded'
    ]);
}
?> 