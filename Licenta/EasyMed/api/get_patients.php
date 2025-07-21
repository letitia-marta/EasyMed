<?php
/**
 * API Endpoint pentru obținerea listei de pacienți (exclusiv pacientul curent)
 *
 * Acest endpoint returnează lista tuturor pacienților, cu excepția celui curent:
 * - Primește current_patient_id prin GET
 * - Returnează lista pacienților cu detalii (nume, prenume, CNP, sex)
 * - Folosește prepared statements pentru interogare sigură
 * - Returnează răspuns JSON cu toți pacienții găsiți
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
    // Extrage current_patient_id din GET
    $currentPatientId = isset($_GET['current_patient_id']) ? (int)$_GET['current_patient_id'] : 0;
    
    if ($currentPatientId <= 0) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Missing or invalid current_patient_id parameter'
        ]);
        exit;
    }

    // Interoghează toți pacienții, exclusiv pacientul curent
    $sql = "SELECT id, nume, prenume, CNP, sex FROM pacienti WHERE id != ? ORDER BY nume, prenume";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $currentPatientId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $patients = [];
    // Formatează rezultatele
    while ($row = $result->fetch_assoc()) {
        $patients[] = [
            'id' => (int)$row['id'],
            'nume' => $row['nume'],
            'prenume' => $row['prenume'],
            'cnp' => $row['CNP'],
            'sex' => $row['sex']
        ];
    }
    
    // Returnează lista pacienților în format JSON
    echo json_encode($patients);

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
?> 