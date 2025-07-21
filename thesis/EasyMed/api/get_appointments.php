<?php
/**
 * API Endpoint pentru obținerea programărilor medicale
 *
 * Acest endpoint returnează programările pentru un pacient sau pentru un medic într-o anumită zi:
 * - Primește parametri GET: patient_id sau (doctor_id și date)
 * - Returnează lista programărilor viitoare pentru pacient sau intervalele ocupate pentru medic
 * - Folosește prepared statements pentru interogări sigure
 * - Returnează răspuns JSON cu detalii despre programări
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
    // Extrage parametrii din GET
    $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
    $doctorId = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
    $date = isset($_GET['date']) ? $_GET['date'] : '';
    
    // Verifică ce tip de interogare se face (pacient sau medic)
    if ($patientId > 0) {
        $sql = "SELECT p.id, p.pacient_id, p.medic_id, p.data_programare, p.ora_programare, p.motiv_consultatie, m.nume AS medic_nume, m.prenume AS medic_prenume, m.specializare AS medic_specializare
                FROM programari p
                INNER JOIN medici m ON p.medic_id = m.id
                WHERE p.pacient_id = ?
                AND (p.data_programare > CURDATE() OR (p.data_programare = CURDATE() AND p.ora_programare > CURTIME()))
                ORDER BY p.data_programare ASC, p.ora_programare ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $patientId);
    } elseif ($doctorId > 0 && !empty($date)) {
        $sql = "SELECT p.id, p.pacient_id, p.medic_id, p.data_programare, p.ora_programare, p.motiv_consultatie
                FROM programari p
                WHERE p.medic_id = ? AND p.data_programare = ?
                ORDER BY p.ora_programare ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $doctorId, $date);
    } else {
        // Parametri lipsă sau invalizi
        http_response_code(400);
        echo json_encode([
            'error' => 'Missing required parameters: either patient_id or both doctor_id and date'
        ]);
        exit;
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $appointments = [];
    // Formatează rezultatele în funcție de tipul interogării
    while ($row = $result->fetch_assoc()) {
        if ($patientId > 0) {
            $appointments[] = [
                'id' => (int)$row['id'],
                'patient_id' => (int)$row['pacient_id'],
                'doctor_id' => (int)$row['medic_id'],
                'date' => $row['data_programare'],
                'time_slot' => $row['ora_programare'],
                'consultation_type' => $row['motiv_consultatie'],
                'doctor_name' => $row['medic_nume'] . ' ' . $row['medic_prenume'],
                'specialty' => $row['medic_specializare']
            ];
        } else {
            $appointments[] = [
                'id' => (int)$row['id'],
                'time_slot' => $row['ora_programare']
            ];
        }
    }

    // Returnează lista programărilor în format JSON
    echo json_encode($appointments);

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