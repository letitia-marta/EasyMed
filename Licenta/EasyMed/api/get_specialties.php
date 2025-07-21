<?php
/**
 * API Endpoint pentru obținerea specializărilor medicale
 *
 * Acest endpoint returnează lista specializărilor disponibile:
 * - Interoghează baza de date pentru specializări unice
 * - Returnează lista specializărilor în format JSON
 * - Folosește interogări sigure și optimizate
 * - Gestionează erorile de bază de date
 * - Include gestionarea conexiunii la baza de date
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
    // Interoghează specializările unice din baza de date
    $sql = "SELECT DISTINCT specializare FROM medici ORDER BY specializare";
    $result = $conn->query($sql);
    
    $specialties = [];
    // Formatează rezultatele
    while ($row = $result->fetch_assoc()) {
        $specialties[] = $row['specializare'];
    }
    
    // Returnează lista specializărilor în format JSON
    echo json_encode($specialties);
    

} catch (Exception $e) {
    // Gestionare excepții
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    // Închide conexiunea la baza de date
    if (isset($conn)) {
        $conn->close();
    }
}
?> 