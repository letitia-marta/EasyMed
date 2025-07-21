<?php
/**
 * API Endpoint pentru obținerea consultațiilor medicale ale unui pacient
 *
 * Acest endpoint returnează lista consultațiilor pentru un pacient:
 * - Primește patient_id prin GET
 * - Returnează lista consultațiilor cu detalii despre medic, diagnostic și dată
 * - Folosește prepared statements pentru interogare sigură
 * - Returnează răspuns JSON cu toate consultațiile găsite
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
            'error' => 'Missing required parameter: patient_id'
        ]);
        exit;
    }

    // Interoghează consultațiile pentru pacient
    $sql = "SELECT c.ID, c.Data, c.Ora, m.nume AS medic_nume, m.prenume AS medic_prenume, m.specializare AS medic_specializare, cb.denumire_boala AS denumire_diagnostic
            FROM consultatii c
            INNER JOIN medici m ON c.id_medic = m.id
            LEFT JOIN coduri_boli cb ON c.Diagnostic = cb.cod_999
            WHERE c.CNPPacient = (SELECT CNP FROM pacienti WHERE id = ?)
            ORDER BY c.Data DESC, c.Ora DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    $result = $stmt->get_result();

    $consultations = [];
    // Formatează rezultatele
    while ($row = $result->fetch_assoc()) {
        $consultations[] = [
            'id' => (int)$row['ID'],
            'date' => $row['Data'],
            'time' => $row['Ora'],
            'doctor_name' => $row['medic_nume'] . ' ' . $row['medic_prenume'],
            'specialty' => $row['medic_specializare'],
            'diagnosis' => $row['denumire_diagnostic'] ?? 'N/A'
        ];
    }

    // Returnează lista consultațiilor în format JSON
    echo json_encode($consultations);

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