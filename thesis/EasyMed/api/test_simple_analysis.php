<?php
/**
 * API Endpoint pentru testarea analizei simple de documente
 *
 * Acest endpoint de test simulează analiza AI a documentelor:
 * - Returnează un răspuns JSON fix pentru testare
 * - Simulează rezultatul analizei unui document medical
 * - Include logging detaliat pentru debugging
 * - Validează formatul JSON înainte de trimitere
 * - Folosit pentru testarea funcționalității de analiză
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
error_log("=== SIMPLE ANALYSIS TEST ===");
error_log("Request received at " . date('Y-m-d H:i:s'));
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
error_log("User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'not set'));

// Răspuns de test simulat
$response = [
    'success' => true,
    'suggested_title' => 'Scrisoare medicala',
    'document_type' => 'scrisori',
    'confidence' => 0.9,
    'error' => null
];

// Validează și trimite răspunsul JSON
$jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
error_log("JSON response length: " . strlen($jsonResponse));
error_log("JSON response: " . $jsonResponse);

// Verifică validitatea JSON-ului
$testDecode = json_decode($jsonResponse, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON validation failed: " . json_last_error_msg());
} else {
    error_log("JSON validation passed");
}

// Trimite răspunsul
echo $jsonResponse;
error_log("=== END SIMPLE ANALYSIS TEST ===");
?> 