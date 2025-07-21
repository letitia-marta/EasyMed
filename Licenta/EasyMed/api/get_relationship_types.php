<?php
/**
 * API Endpoint pentru obținerea tipurilor de relații posibile
 *
 * Acest endpoint returnează lista tipurilor de relații de familie:
 * - Permite filtrarea după sexul pacientului (sau id pacient)
 * - Returnează lista tipurilor de relații disponibile
 * - Folosește interogări sigure și filtrare logică
 * - Returnează răspuns JSON cu toate tipurile de relații
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
    // Extrage sexul sau id-ul pacientului din GET
    $sex = isset($_GET['sex']) ? $_GET['sex'] : null;
    $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;

    // Dacă nu e specificat sexul, îl extrage din baza de date dacă există patient_id
    if (!$sex && $patientId > 0) {
        $sqlSex = "SELECT sex FROM pacienti WHERE id = ?";
        $stmtSex = $conn->prepare($sqlSex);
        $stmtSex->bind_param("i", $patientId);
        $stmtSex->execute();
        $resultSex = $stmtSex->get_result();
        if ($rowSex = $resultSex->fetch_assoc()) {
            $sex = $rowSex['sex'];
        }
        $stmtSex->close();
    }

    // Interoghează toate tipurile de relații
    $sql = "SELECT * FROM tipuri_relatii ORDER BY denumire";
    $result = $conn->query($sql);
    
    $relationshipTypes = [];
    // Filtrează tipurile de relații în funcție de sex
    while ($row = $result->fetch_assoc()) {
        $denumire = $row['denumire'];
        if ($sex === 'F' && !in_array($denumire, ['Mama', 'Fiica', 'Sora'])) continue;
        if ($sex === 'M' && !in_array($denumire, ['Tata', 'Fiu', 'Frate'])) continue;
        $relationshipTypes[] = [
            'id' => (int)$row['id'],
            'denumire' => $denumire
        ];
    }

    // Dacă nu e specificat sexul, returnează toate tipurile de relații
    if (!$sex) {
        $result = $conn->query("SELECT * FROM tipuri_relatii ORDER BY denumire");
        $relationshipTypes = [];
        while ($row = $result->fetch_assoc()) {
            $relationshipTypes[] = [
                'id' => (int)$row['id'],
                'denumire' => $row['denumire']
            ];
        }
    }

    // Returnează lista tipurilor de relații în format JSON
    echo json_encode($relationshipTypes);

} catch (Exception $e) {
    // Gestionare excepții
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    // Închide conexiunea
    if (isset($conn)) {
        $conn->close();
    }
}
?> 