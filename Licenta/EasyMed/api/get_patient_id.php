<?php
/**
 * API Endpoint pentru obținerea ID-ului pacientului pe baza utilizatorului
 *
 * Acest endpoint returnează ID-ul pacientului asociat unui utilizator:
 * - Primește user_id prin GET
 * - Returnează patient_id dacă există asociere
 * - Folosește prepared statements pentru interogare sigură
 * - Returnează răspuns JSON cu ID-ul pacientului sau eroare
 * - Gestionează erorile de validare și de bază de date
 *
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

// Setează headerele pentru răspuns JSON și CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestionare pentru cereri OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include conexiunea la baza de date
include('../db_connection.php');

try {
    // Extrage user_id din GET
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    if ($userId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing or invalid user_id parameter']);
        exit;
    }

    // Caută ID-ul pacientului asociat utilizatorului
    $stmt = $conn->prepare('SELECT id FROM pacienti WHERE utilizator_id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($patientId);
    if ($stmt->fetch()) {
        // Returnează patient_id dacă există asociere
        echo json_encode(['patient_id' => (int)$patientId]);
    } else {
        // Nu există asociere
        http_response_code(404);
        echo json_encode(['error' => 'Patient not found for this user_id']);
    }
} catch (Exception $e) {
    // Gestionare excepții
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} finally {
    // Închide statement-ul și conexiunea
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
} 