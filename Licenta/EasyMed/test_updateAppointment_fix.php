<?php
/**
 * Script de test pentru verificarea conexiunii la baza de date
 * 
 * Acest script simplu testează conexiunea la baza de date:
 * - Verifică dacă conexiunea la MySQL este funcțională
 * - Returnează răspuns JSON cu statusul conexiunii
 * - Include timestamp-ul pentru debugging
 * - Folosit pentru diagnosticarea problemelor de conexiune
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

header('Content-Type: application/json');

include 'db_connection.php';

// Verifică dacă conexiunea la baza de date a eșuat
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

// Returnează răspuns de succes cu timestamp
echo json_encode([
    'success' => true,
    'message' => 'Database connection successful',
    'timestamp' => date('Y-m-d H:i:s')
]);

$conn->close();
?> 