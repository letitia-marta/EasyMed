<?php
/**
 * API Endpoint pentru obținerea profilului unui pacient
 *
 * Acest endpoint returnează datele de profil ale unui pacient:
 * - Primește patient_id prin GET
 * - Returnează datele personale și de contact ale pacientului
 * - Folosește prepared statements pentru interogare sigură
 * - Returnează răspuns JSON cu datele profilului sau eroare
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
    // Extrage patient_id din GET
    $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
    if ($patientId <= 0) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Missing or invalid patient_id parameter'
        ]);
        exit;
    }

    // Caută datele de profil ale pacientului
    $stmt = $conn->prepare('SELECT p.id, p.nume, p.prenume, p.CNP, p.sex, p.data_nasterii, p.adresa, p.grupa_sanguina, u.email FROM pacienti p JOIN utilizatori u ON p.utilizator_id = u.id WHERE p.id = ?');
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    $stmt->bind_result($id, $nume, $prenume, $CNP, $sex, $data_nasterii, $adresa, $grupa_sanguina, $email);
    if (!$stmt->fetch()) {
        // Nu există profil pentru acest pacient
        http_response_code(404);
        echo json_encode(['error' => 'Patient profile not found']);
        exit;
    }

    // Returnează datele profilului în format JSON
    echo json_encode([
        'id' => (int)$id,
        'nume' => $nume,
        'prenume' => $prenume,
        'cnp' => $CNP,
        'sex' => $sex,
        'data_nasterii' => $data_nasterii,
        'adresa' => $adresa,
        'grupa_sanguina' => $grupa_sanguina,
        'email' => $email
    ]);
        
} catch (Exception $e) {
    // Gestionare excepții
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    // Închide statement-ul și conexiunea
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
} 