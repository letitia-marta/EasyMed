<?php
/**
 * API Endpoint pentru salvarea programărilor medicale
 *
 * Acest endpoint permite crearea de programări noi:
 * - Primește datele programării prin POST request
 * - Validează toate câmpurile obligatorii
 * - Verifică disponibilitatea intervalului orar
 * - Salvează programarea în baza de date
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
    $patient_id = isset($_POST['patient_id']) ? (int)$_POST['patient_id'] : 0;
    $doctor_id = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : 0;
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $time = isset($_POST['time']) ? $_POST['time'] : '';
    $consultation_type = isset($_POST['consultation_type']) ? $_POST['consultation_type'] : '';

    // Validează câmpurile obligatorii
    if (!$patient_id || !$doctor_id || !$date || !$time || !$consultation_type) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }

    // Verifică dacă intervalul orar este disponibil
    $stmt = $conn->prepare("SELECT id FROM programari WHERE medic_id = ? AND data_programare = ? AND ora_programare = ?");
    $stmt->bind_param("iss", $doctor_id, $date, $time);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Slot already occupied.']);
        $stmt->close();
        exit;
    }
    $stmt->close();
    
    // Salvează programarea în baza de date
    $stmt = $conn->prepare("INSERT INTO programari (pacient_id, medic_id, data_programare, ora_programare, status, motiv_consultatie) VALUES (?, ?, ?, ?, 'programat', ?)");
    $stmt->bind_param("iisss", $patient_id, $doctor_id, $date, $time, $consultation_type);
    if ($stmt->execute()) {
        // Programarea a fost salvată cu succes
        echo json_encode(['success' => true]);
    } else {
        // Eroare la salvarea în baza de date
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // Gestionare excepții
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?> 