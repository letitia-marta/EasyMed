<?php
/**
 * Script de test pentru compararea funcționalității AI analyzer
 * 
 * Acest script oferă o interfață simplificată pentru testarea analizatorului AI:
 * - Primește fișiere prin POST request
 * - Apelează DocumentAnalyzer pentru analiza documentelor
 * - Returnează rezultatele în format JSON
 * - Include logging detaliat pentru debugging
 * - Gestionează erorile de încărcare și analiză
 * - Suportă CORS pentru testarea din browser
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

require_once 'ai_document_analyzer.php';

// Setează headerele pentru răspuns JSON și CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestionare pentru cereri OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Logging pentru debugging
error_log("=== AI COMPARISON TEST ===");
error_log("Request received at " . date('Y-m-d H:i:s'));
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
error_log("User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'not set'));
error_log("Files array: " . print_r($_FILES, true));

// Procesează cererea POST cu fișier încărcat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) {
    try {
        // Inițializează analizatorul AI
        $analyzer = new DocumentAnalyzer();
        $file = $_FILES['document_file'];
        
        // Verifică dacă încărcarea a fost reușită
        if ($file['error'] === UPLOAD_ERR_OK) {
            error_log("File upload OK");
            $tempPath = $file['tmp_name'];
            $originalName = $file['name'];
            
            error_log("Temp path: $tempPath");
            error_log("Original name: $originalName");
            
            // Verifică dacă fișierul există pe server
            if (!file_exists($tempPath)) {
                throw new Exception("Uploaded file not found at: $tempPath");
            }
            
            // Execută analiza documentului
            error_log("Starting analysis...");
            $result = $analyzer->analyzeDocument($tempPath, $originalName);
            error_log("Analysis completed, result: " . print_r($result, true));
            
            // Returnează rezultatul în format JSON
            echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
        } else {
            // Eroare la încărcarea fișierului
            error_log("File upload error: " . $file['error']);
            echo json_encode([
                'success' => false,
                'error' => 'File upload error: ' . $file['error'],
                'suggested_title' => 'Document Medical',
                'document_type' => 'alte'
            ]);
        }
        
    } catch (Exception $e) {
        // Gestionare excepții
        error_log("Exception caught: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Analysis failed: ' . $e->getMessage(),
            'suggested_title' => 'Document Medical',
            'document_type' => 'alte'
        ]);
    }
} else {
    // Cerere invalidă sau lipsă fișier
    error_log("Invalid request method or no file uploaded");
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method or no file uploaded',
        'suggested_title' => 'Document Medical',
        'document_type' => 'alte'
    ]);
}

error_log("=== END AI COMPARISON TEST ===");
?> 