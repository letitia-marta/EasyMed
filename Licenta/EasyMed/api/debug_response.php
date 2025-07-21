<?php
/**
 * API Endpoint pentru debugging și testare
 * 
 * Acest endpoint simplu oferă o răspuns de test pentru debugging:
 * - Returnează un răspuns JSON cu timestamp și date POST
 * - Folosit pentru testarea conectivității API
 * - Include headere CORS pentru testarea din browser
 * - Oferă informații despre cererea primită
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

// Setează headerele pentru răspuns JSON și CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Returnează răspuns de debug cu timestamp și date POST
echo json_encode([
    'success' => true,
    'message' => 'Debug test',
    'timestamp' => date('Y-m-d H:i:s'),
    'post_data' => $_POST
]);
?> 