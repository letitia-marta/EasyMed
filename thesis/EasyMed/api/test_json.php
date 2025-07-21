<?php
/**
 * API Endpoint pentru testarea funcționalității JSON
 *
 * Acest endpoint de test verifică funcționalitatea JSON:
 * - Testează codificarea și decodificarea JSON
 * - Returnează un răspuns JSON cu timestamp
 * - Include informații de debug pentru testare
 * - Verifică validitatea JSON-ului înainte de trimitere
 * - Folosit pentru testarea conectivității și formatului JSON
 *
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

// Inițializează buffer-ul de output
ob_start();
ob_clean();

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
error_log("Test JSON: Request received at " . date('Y-m-d H:i:s'));

// Răspuns de test cu timestamp
$response = [
    'success' => true,
    'suggested_title' => 'Test Document - ' . date('H:i:s'),
    'document_type' => 'alte',
    'confidence' => 0.9,
    'error' => null,
    'debug' => [
        'message' => 'JSON test successful',
        'timestamp' => date('Y-m-d H:i:s')
    ]
];

// Codifică răspunsul în JSON
$jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// Verifică dacă codificarea JSON a reușit
if ($jsonResponse === false) {
    error_log("Test JSON: JSON encoding failed: " . json_last_error_msg());
    echo json_encode([
        'success' => false,
        'error' => 'JSON encoding failed: ' . json_last_error_msg()
    ]);
} else {
    error_log("Test JSON: Response sent successfully");
    echo $jsonResponse;
}

// Trimite buffer-ul de output
ob_end_flush();
?> 