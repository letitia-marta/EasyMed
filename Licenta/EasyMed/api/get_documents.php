<?php
/**
 * API Endpoint pentru obținerea listei de documente medicale ale unui pacient
 *
 * Acest endpoint returnează toate documentele încărcate pentru un pacient:
 * - Primește patient_id prin GET
 * - Returnează lista documentelor cu detalii (titlu, tip, nume fișier, dată upload)
 * - Folosește prepared statements pentru interogare sigură
 * - Returnează răspuns JSON cu toate documentele găsite
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
require_once '../db_connection.php';

try {
    // Permite doar metoda GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method not allowed');
    }

    // Extrage patient_id din GET
    $patientId = $_GET['patient_id'] ?? null;
    
    if (!$patientId) {
        throw new Exception('Patient ID is required');
    }
        
    // Interoghează documentele pentru pacient
    $sql = "SELECT id, titlu, tip_document, nume_fisier, data_upload, pacient_id 
            FROM documente 
            WHERE pacient_id = ? 
            ORDER BY data_upload DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $documents = [];
    // Formatează rezultatele
    while ($row = $result->fetch_assoc()) {
        $documents[] = [
            'id' => $row['id'],
            'titlu' => $row['titlu'],
            'tip_document' => $row['tip_document'],
            'nume_fisier' => $row['nume_fisier'],
            'data_upload' => $row['data_upload'],
            'pacient_id' => $row['pacient_id']
        ];
    }
    
    $stmt->close();
    
    // Returnează lista documentelor în format JSON
    echo json_encode($documents);
    
} catch (Exception $e) {
    // Gestionare excepții
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 