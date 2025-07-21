<?php
/**
 * API Endpoint pentru actualizarea profilului pacientului
 *
 * Acest endpoint permite actualizarea datelor personale ale pacientului:
 * - Primește datele actualizate prin POST request
 * - Validează ID-ul pacientului
 * - Actualizează profilul în baza de date
 * - Returnează răspuns JSON cu rezultatul operației
 * - Gestionează erorile de validare și de bază de date
 *
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

// Setează headerele pentru răspuns JSON și CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestionare pentru cereri OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include conexiunea la baza de date
include('../db_connection.php');

try {
    // Extrage datele din POST
    $patientId = isset($_POST['patient_id']) ? (int)$_POST['patient_id'] : 0;
    $cnp = isset($_POST['cnp']) ? $_POST['cnp'] : '';
    $nume = isset($_POST['nume']) ? $_POST['nume'] : '';
    $prenume = isset($_POST['prenume']) ? $_POST['prenume'] : '';
    $data_nasterii = isset($_POST['data_nasterii']) ? $_POST['data_nasterii'] : '';
    $sex = isset($_POST['sex']) ? $_POST['sex'] : '';
    $adresa = isset($_POST['adresa']) ? $_POST['adresa'] : '';
    $grupa_sanguina = isset($_POST['grupa_sanguina']) ? $_POST['grupa_sanguina'] : '';

    // Validează ID-ul pacientului
    if ($patientId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing or invalid patient_id']);
        exit;
    }

    // Actualizează profilul pacientului în baza de date
    $stmt = $conn->prepare('UPDATE pacienti SET CNP=?, nume=?, prenume=?, data_nasterii=?, sex=?, adresa=?, grupa_sanguina=? WHERE id=?');
    $stmt->bind_param('sssssssi', $cnp, $nume, $prenume, $data_nasterii, $sex, $adresa, $grupa_sanguina, $patientId);

    if ($stmt->execute()) {
        // Profilul a fost actualizat cu succes
        echo json_encode(['success' => true]);
    } else {
        // Eroare la actualizarea în baza de date
        http_response_code(500);
        echo json_encode(['error' => 'Database update failed']);
    }
    $stmt->close();
} catch (Exception $e) {
    // Gestionare excepții
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} finally {
    // Închide statement-ul și conexiunea
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
} 