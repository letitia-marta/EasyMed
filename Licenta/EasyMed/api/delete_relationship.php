<?php
/**
 * API Endpoint pentru ștergerea relațiilor de familie
 *
 * Acest endpoint permite ștergerea unei relații între pacienți:
 * - Primește relatie_id și pacient_id prin JSON
 * - Verifică existența și permisiunea pentru ștergere
 * - Șterge relația din baza de date
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
    
    // Extrage parametrii din JSON
    $relatieId = isset($input['relatie_id']) ? (int)$input['relatie_id'] : 0;
    $pacientId = isset($input['pacient_id']) ? (int)$input['pacient_id'] : 0;
    
    // Validează parametrii
    if ($relatieId <= 0 || $pacientId <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Missing or invalid parameters'
        ]);
        exit;
    }

    // Șterge relația dacă există și aparține pacientului
    $sqlDelete = "DELETE FROM relatii_pacienti 
                  WHERE id = ? AND (pacient_id = ? OR pacient_relat_id = ?)";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("iii", $relatieId, $pacientId, $pacientId);
    
    if ($stmtDelete->execute() && $stmtDelete->affected_rows > 0) {
        // Relația a fost ștearsă cu succes
        echo json_encode([
            'success' => true,
            'message' => 'Relația a fost ștearsă cu succes!'
        ]);
    } else {
        // Relația nu a fost găsită sau nu există permisiune
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Relația nu a fost găsită sau nu aveți permisiunea să o ștergeți'
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
    // Închide statement-ul și conexiunea
    if (isset($stmtDelete)) {
        $stmtDelete->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?> 