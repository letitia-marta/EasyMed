<?php
/**
 * Script pentru obținerea programărilor pentru o dată și medic specific
 * 
 * Acest script returnează toate programările pentru un medic într-o dată specifică:
 * - Primește parametrii date și doctor_id prin GET
 * - Validează prezența parametrilor obligatorii
 * - Interoghează baza de date pentru programările din ziua specificată
 * - Returnează lista cu sloturile de timp ocupate în format JSON
 * - Include logging pentru debugging și monitorizare
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

// Curăță buffer-ul de output și inițializează sesiunea
ob_clean();
session_start();
require_once 'db_connection.php';

// Setează header-ul pentru răspuns JSON
header('Content-Type: application/json');
error_reporting(E_ERROR | E_PARSE);

// Extrage parametrii din cererea GET
$date = isset($_GET['date']) ? $_GET['date'] : null;
$doctor_id = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : null;

// Loghează parametrii primiți pentru debugging
error_log("Received date: " . $date);
error_log("Received doctor_id: " . $doctor_id);

// Validează prezența parametrilor obligatorii
if (!$date || !$doctor_id) {
    error_log("Missing parameters - date: " . $date . ", doctor_id: " . $doctor_id);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

try {
    // Interogare pentru obținerea programărilor din ziua specificată
    $query = "SELECT TIME_FORMAT(ora_programare, '%H:%i') as time_slot FROM programari 
              WHERE DATE(data_programare) = ? AND medic_id = ?";
    error_log("Query: " . $query);
    
    // Pregătește și execută interogarea
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        // Leagă parametrii la interogare
        mysqli_stmt_bind_param($stmt, "si", $date, $doctor_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        // Verifică dacă interogarea a fost executată cu succes
        if (!$result) {
            error_log("Query execution failed: " . mysqli_error($conn));
            echo json_encode(['error' => 'Query execution failed']);
            exit;
        }
        
        // Colectează toate programările găsite
        $appointments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $appointments[] = $row;
            error_log("Found appointment time: " . $row['time_slot']);
        }
        
        // Loghează toate programările găsite și returnează rezultatul
        error_log("All appointments for date $date: " . print_r($appointments, true));
        echo json_encode($appointments);
        mysqli_stmt_close($stmt);
    } else {
        // Eroare la pregătirea interogării
        error_log("Statement preparation failed: " . mysqli_error($conn));
        echo json_encode(['error' => 'Error preparing statement']);
    }
} catch (Exception $e) {
    // Gestionare excepții
    error_log("Exception occurred: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

// Închide conexiunea la baza de date
mysqli_close($conn);
?> 