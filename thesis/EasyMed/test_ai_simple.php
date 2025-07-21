<?php
/**
 * Script de test simplu pentru funcționalitatea AI
 * 
 * Acest script oferă un endpoint de test simplu pentru verificarea funcționalității:
 * - Primește fișiere prin POST request
 * - Returnează un răspuns JSON de test fără analiză reală
 * - Include logging detaliat pentru debugging
 * - Gestionează erorile de încărcare
 * - Suportă CORS pentru testarea din browser
 * - Folosit pentru testarea conectivității și funcționalității de bază
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

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
error_log("Test AI Simple: Request received at " . date('Y-m-d H:i:s'));
error_log("Test AI Simple: Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Test AI Simple: Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
error_log("Test AI Simple: Files array: " . print_r($_FILES, true));

// Procesează cererea POST cu fișier încărcat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) {
    $file = $_FILES['document_file'];
    
    // Verifică dacă încărcarea a fost reușită
    if ($file['error'] === UPLOAD_ERR_OK) {
        error_log("Test AI Simple: File upload OK");
        error_log("Test AI Simple: File name: " . $file['name']);
        error_log("Test AI Simple: File size: " . $file['size']);
        error_log("Test AI Simple: File type: " . $file['type']);
        error_log("Test AI Simple: Temp path: " . $file['tmp_name']);
        
        // Returnează răspuns de test fără analiză reală
        $response = [
            'success' => true,
            'suggested_title' => 'Test Document - ' . $file['name'],
            'document_type' => 'alte',
            'confidence' => 0.8,
            'debug' => [
                'file_name' => $file['name'],
                'file_size' => $file['size'],
                'file_type' => $file['type'],
                'message' => 'Test endpoint working correctly'
            ]
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    } else {
        // Eroare la încărcarea fișierului
        error_log("Test AI Simple: File upload error: " . $file['error']);
        echo json_encode([
            'success' => false,
            'error' => 'File upload error: ' . $file['error'],
            'suggested_title' => 'Test Document',
            'document_type' => 'alte'
        ]);
    }
} else {
    // Cerere invalidă sau lipsă fișier
    error_log("Test AI Simple: Invalid request");
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method or no file uploaded',
        'suggested_title' => 'Test Document',
        'document_type' => 'alte'
    ]);
}
?> 