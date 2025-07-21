<?php
/**
 * API Endpoint pentru obținerea listei de medici
 *
 * Acest endpoint returnează lista medicilor disponibili, cu opțiuni de filtrare și sortare:
 * - Permite filtrarea după nume, specializare, sau doar medicii asociați pacientului
 * - Permite sortarea după nume sau specializare, ascendent/descendent
 * - Folosește prepared statements pentru interogare sigură
 * - Returnează răspuns JSON cu lista medicilor
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
    // Extrage parametrii de filtrare și sortare din GET
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $specialty = isset($_GET['specialty']) ? $_GET['specialty'] : '';
    $myDoctors = isset($_GET['my_doctors']) ? $_GET['my_doctors'] === 'true' : false;
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'nume';
    $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
    
    // Validează parametrii de sortare
    if (!in_array($sort, ['nume', 'specializare'])) {
        $sort = 'nume';
    }
    
    if (!in_array(strtoupper($order), ['ASC', 'DESC'])) {
        $order = 'ASC';
    }
    
    // Construiește query-ul SQL cu filtrele selectate
    $sql = "SELECT DISTINCT m.id, m.nume, m.prenume, m.specializare 
            FROM medici m";
    
    $params = [];
    $types = "";
    
    if ($myDoctors) {
        $sql .= " INNER JOIN doctor_pacient dp ON m.id = dp.doctor_id 
                  WHERE dp.pacient_id = ?";
        $params[] = 1; // Exemplu: se poate înlocui cu ID-ul pacientului autentificat
        $types .= "i";
    } else {
        $sql .= " WHERE 1=1";
    }
    
    if (!empty($search)) {
        $sql .= " AND (m.nume LIKE ? OR m.prenume LIKE ? OR m.specializare LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "sss";
    }
    
    if (!empty($specialty)) {
        $sql .= " AND m.specializare = ?";
        $params[] = $specialty;
        $types .= "s";
    }
    
    $sql .= " ORDER BY $sort $order";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $doctors = [];
    // Formatează rezultatele
    while ($row = $result->fetch_assoc()) {
        $doctors[] = [
            'id' => (int)$row['id'],
            'nume' => $row['nume'],
            'prenume' => $row['prenume'],
            'specializare' => $row['specializare']
        ];
    }
    
    // Returnează lista medicilor în format JSON
    echo json_encode([
        'success' => true,
        'doctors' => $doctors
    ]);
    
} catch (Exception $e) {
    // Gestionare excepții
    http_response_code(500);
    echo json_encode([
        'success' => false,
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