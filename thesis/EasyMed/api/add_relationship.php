<?php
/**
 * API Endpoint pentru adăugarea relațiilor între pacienți
 * 
 * Acest endpoint permite adăugarea relațiilor familiale între pacienți:
 * - Primește datele relației prin JSON (pacient_id, pacient_relat_id, tip_relatie_id)
 * - Validează existența pacienților și tipului de relație
 * - Verifică compatibilitatea sexului cu tipul de relație
 * - Previne duplicarea relațiilor existente
 * - Returnează răspuns JSON cu rezultatul operației
 * - Include gestionarea erorilor și validări de securitate
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
    // Decodifică datele JSON din request
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Extrage și validează parametrii din request
    $pacientId = isset($input['pacient_id']) ? (int)$input['pacient_id'] : 0;
    $pacientRelatId = isset($input['pacient_relat_id']) ? (int)$input['pacient_relat_id'] : 0;
    $tipRelatieId = isset($input['tip_relatie_id']) ? (int)$input['tip_relatie_id'] : 0;
    
    // Verifică dacă toți parametrii sunt valizi
    if ($pacientId <= 0 || $pacientRelatId <= 0 || $tipRelatieId <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Missing or invalid parameters'
        ]);
        exit;
    }

    // Obține sexul primului pacient pentru validare
    $sqlSex = "SELECT sex FROM pacienti WHERE id = ?";
    $stmtSex = $conn->prepare($sqlSex);
    $stmtSex->bind_param("i", $pacientId);
    $stmtSex->execute();
    $resultSex = $stmtSex->get_result();
    if (!$rowSex = $resultSex->fetch_assoc()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Pacientul nu a fost găsit'
        ]);
        exit;
    }
    $sex = $rowSex['sex'];
    $stmtSex->close();

    // Obține denumirea tipului de relație pentru validare
    $sqlType = "SELECT denumire FROM tipuri_relatii WHERE id = ?";
    $stmtType = $conn->prepare($sqlType);
    $stmtType->bind_param("i", $tipRelatieId);
    $stmtType->execute();
    $resultType = $stmtType->get_result();
    if (!$rowType = $resultType->fetch_assoc()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Tipul de relație nu a fost găsit'
        ]);
        exit;
    }
    $denumire = $rowType['denumire'];
    $stmtType->close();

    // Validează compatibilitatea sexului cu tipul de relație
    if ($sex === 'F' && !in_array($denumire, ['Mama', 'Fiica', 'Sora'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Tipul de relație nu este permis pentru pacientă (F)'
        ]);
        exit;
    }
    if ($sex === 'M' && !in_array($denumire, ['Tata', 'Fiu', 'Frate'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Tipul de relație nu este permis pentru pacient (M)'
        ]);
        exit;
    }

    // Verifică dacă relația există deja între acești pacienți
    $sqlCheck = "SELECT id FROM relatii_pacienti 
                 WHERE (pacient_id = ? AND pacient_relat_id = ?) 
                 OR (pacient_id = ? AND pacient_relat_id = ?)";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("iiii", $pacientId, $pacientRelatId, $pacientRelatId, $pacientId);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    
    if ($resultCheck->num_rows > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Relația există deja între acești pacienți'
        ]);
        exit;
    }

    // Adaugă relația în baza de date
    $sqlAdd = "INSERT INTO relatii_pacienti (pacient_id, pacient_relat_id, tip_relatie_id) 
                VALUES (?, ?, ?)";
    $stmtAdd = $conn->prepare($sqlAdd);
    $stmtAdd->bind_param("iii", $pacientId, $pacientRelatId, $tipRelatieId);
    
    // Returnează rezultatul operației
    if ($stmtAdd->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Relația a fost adăugată cu succes!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Eroare la adăugarea relației'
        ]);
    }

} catch (Exception $e) {
    // Gestionare excepții
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    // Închide toate statement-urile și conexiunea
    if (isset($stmtCheck)) {
        $stmtCheck->close();
    }
    if (isset($stmtAdd)) {
        $stmtAdd->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?> 
