<?php
/**
 * Script de test pentru verificarea instalării Tesseract OCR
 * 
 * Acest script testează disponibilitatea și funcționalitatea Tesseract OCR:
 * - Verifică dacă funcția shell_exec este disponibilă
 * - Testează diferite căi de instalare pentru Tesseract
 * - Detectează versiunea Tesseract instalată
 * - Returnează răspuns JSON cu statusul și informațiile Tesseract
 * - Include logging pentru debugging
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestionare pentru cereri OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

error_log("Test Tesseract: Request received at " . date('Y-m-d H:i:s'));

// Inițializează răspunsul cu valori implicite
$response = [
    'available' => false,
    'version' => null,
    'error' => null
];

// Verifică dacă funcția shell_exec este disponibilă
if (function_exists('shell_exec')) {
    error_log("Test Tesseract: shell_exec function exists");
    
    // Lista de căi posibile pentru Tesseract
    $tesseractPaths = [
        'tesseract',
        'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
        'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe'
    ];
    
    // Testează fiecare cale pentru Tesseract
    foreach ($tesseractPaths as $tesseract) {
        error_log("Test Tesseract: testing path: $tesseract");
        
        // Comandă pentru verificarea versiunii Tesseract
        $cmd = "\"$tesseract\" --version 2>&1";
        $output = shell_exec($cmd);
        
        // Verifică dacă Tesseract a fost găsit și funcționează
        if ($output && !strpos($output, 'command not found') && !strpos($output, 'is not recognized')) {
            error_log("Test Tesseract: found working tesseract at: $tesseract");
            error_log("Test Tesseract: version output: " . $output);
            
            // Actualizează răspunsul cu informațiile găsite
            $response['available'] = true;
            $response['version'] = trim($output);
            $response['path'] = $tesseract;
            break;
        } else {
            error_log("Test Tesseract: tesseract not found at: $tesseract");
        }
    }
    
    // Dacă Tesseract nu a fost găsit în nicio locație
    if (!$response['available']) {
        $response['error'] = 'Tesseract not found in any of the expected locations';
        error_log("Test Tesseract: Tesseract not found in any location");
    }
    
} else {
    // Funcția shell_exec nu este disponibilă
    $response['error'] = 'shell_exec function is not available';
    error_log("Test Tesseract: shell_exec function not available");
}

// Returnează răspunsul JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?> 