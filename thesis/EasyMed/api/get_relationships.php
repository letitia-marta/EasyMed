<?php
/**
 * API Endpoint pentru obținerea relațiilor unui pacient
 *
 * Acest endpoint returnează lista relațiilor de familie pentru un pacient:
 * - Primește patient_id prin GET
 * - Returnează lista relațiilor cu detalii despre tip, nume, CNP, sex
 * - Folosește prepared statements pentru interogare sigură
 * - Returnează răspuns JSON cu toate relațiile găsite
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
    
    // Validează patient_id
    if ($patientId <= 0) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Missing or invalid patient_id parameter'
        ]);
        exit;
    }

    // Interoghează relațiile pentru pacient
    $sql = "SELECT 
        r.id,
        r.pacient_id,
        r.pacient_relat_id,
        t.denumire as tip_relatie,
        p.nume,
        p.prenume,
        p.CNP,
        p.sex
    FROM relatii_pacienti r
    INNER JOIN pacienti p ON p.id = CASE 
        WHEN r.pacient_id = ? THEN r.pacient_relat_id
        ELSE r.pacient_id
    END
    INNER JOIN tipuri_relatii t ON r.tip_relatie_id = t.id
    WHERE r.pacient_id = ? OR r.pacient_relat_id = ?
    ORDER BY t.denumire, p.nume, p.prenume";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $patientId, $patientId, $patientId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $relationships = [];
    // Formatează rezultatele și ajustează tipul de relație în funcție de sex
    while ($row = $result->fetch_assoc()) {
        $isRelatedPatient = ($row['pacient_relat_id'] == $patientId);
        $tip_relatie = $row['tip_relatie'];
        $sex = $row['sex'];
        if ($isRelatedPatient) {
            if ($tip_relatie === 'Frate') {
                $tip_relatie = ($sex === 'M') ? 'Frate' : 'Soră';
            } else if ($tip_relatie === 'Sora') {
                $tip_relatie = ($sex === 'M') ? 'Frate' : 'Soră';
            }
        } else {
            switch($tip_relatie) {
                case 'Mama':
                case 'Tata':
                    $tip_relatie = ($sex === 'M') ? 'Fiu' : 'Fiică';
                    break;
                case 'Frate':
                case 'Sora':
                    $tip_relatie = ($sex === 'M') ? 'Frate' : 'Soră';
                    break;
                case 'Fiu':
                    $tip_relatie = 'Mama';
                    break;
                case 'Fiica':
                    $tip_relatie = 'Mama';
                    break;
                default:
            }
        }
        $relationships[] = [
            'id' => (int)$row['id'],
            'pacient_id' => (int)$row['pacient_id'],
            'pacient_relat_id' => (int)$row['pacient_relat_id'],
            'tip_relatie' => $tip_relatie,
            'nume' => $row['nume'],
            'prenume' => $row['prenume'],
            'cnp' => $row['CNP'],
            'sex' => $row['sex']
        ];
    }

    // Returnează lista relațiilor în format JSON
    echo json_encode($relationships);

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