<?php
/**
 * API Endpoint pentru testarea conectivității API
 *
 * Acest endpoint simplu oferă o răspuns de test pentru conectivitate:
 * - Returnează un răspuns JSON cu timestamp
 * - Folosit pentru testarea conectivității API
 * - Include headere CORS pentru testarea din browser
 * - Oferă confirmare că API-ul este funcțional
 *
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

// Setează headerele pentru răspuns JSON și CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Returnează răspuns de test cu timestamp
echo json_encode([
    'success' => true,
    'message' => 'API connection test successful',
    'timestamp' => date('Y-m-d H:i:s')
]);
?> 