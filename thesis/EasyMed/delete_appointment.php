<?php
/**
 * Script pentru ștergerea programărilor medicale
 * 
 * Acest script permite medicilor să șteargă programările lor din sistem.
 * Include verificări de securitate, validări și gestionarea relațiilor doctor-pacient.
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acces neautorizat']);
    exit();
}

// Citește datele JSON din request
$rawInput = file_get_contents('php://input');
error_log("Date brute primite: " . $rawInput);

// Decodifică JSON-ul în array
$input = json_decode($rawInput, true);
error_log("Date decodificate: " . print_r($input, true));

// Extrage ID-ul programării din request
$appointmentId = isset($input['appointment_id']) ? (int)$input['appointment_id'] : null;
error_log("ID Programare: " . $appointmentId);

// Validează ID-ul programării
if (!$appointmentId) {
    echo json_encode(['success' => false, 'message' => 'ID-ul programării este obligatoriu']);
    exit();
}

// Obține ID-ul medicului din sesiune
$doctorQuery = "SELECT id FROM medici WHERE utilizator_id = ?";
$doctorStmt = mysqli_prepare($conn, $doctorQuery);
mysqli_stmt_bind_param($doctorStmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($doctorStmt);
$doctorResult = mysqli_stmt_get_result($doctorStmt);
$doctor = mysqli_fetch_assoc($doctorResult);
$doctorId = $doctor['id'];
mysqli_stmt_close($doctorStmt);

// Verifică dacă medicul există
if (!$doctorId) {
    echo json_encode(['success' => false, 'message' => 'Medicul nu a fost găsit']);
    exit();
}

try {
    // Verifică dacă programarea există și aparține medicului
    $verifyQuery = "SELECT id, pacient_id, data_programare, ora_programare 
                    FROM programari 
                    WHERE id = ? AND medic_id = ?";
    $verifyStmt = mysqli_prepare($conn, $verifyQuery);
    mysqli_stmt_bind_param($verifyStmt, "ii", $appointmentId, $doctorId);
    mysqli_stmt_execute($verifyStmt);
    $verifyResult = mysqli_stmt_get_result($verifyStmt);
    $appointment = mysqli_fetch_assoc($verifyResult);
    mysqli_stmt_close($verifyStmt);

    // Verifică dacă programarea a fost găsită
    if (!$appointment) {
        echo json_encode(['success' => false, 'message' => 'Programarea nu a fost găsită sau nu aveți permisiunea să o ștergeți']);
        exit();
    }

    // Verifică dacă programarea nu este din trecut
    $appointmentDateTime = $appointment['data_programare'] . ' ' . $appointment['ora_programare'];
    $currentDateTime = date('Y-m-d H:i:s');
    
    if (strtotime($appointmentDateTime) < strtotime($currentDateTime)) {
        echo json_encode(['success' => false, 'message' => 'Nu puteți șterge o programare din trecut']);
        exit();
    }

    // Șterge programarea din baza de date
    $deleteQuery = "DELETE FROM programari WHERE id = ? AND medic_id = ?";
    $deleteStmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($deleteStmt, "ii", $appointmentId, $doctorId);
    
    if (mysqli_stmt_execute($deleteStmt)) {
        error_log("Programarea a fost ștearsă cu succes. Rânduri afectate: " . mysqli_stmt_affected_rows($deleteStmt));
        
        // Verifică dacă mai există programări între medic și pacient
        $checkRelQuery = "SELECT COUNT(*) as appointment_count 
                         FROM programari 
                         WHERE medic_id = ? AND pacient_id = ?";
        $checkRelStmt = mysqli_prepare($conn, $checkRelQuery);
        mysqli_stmt_bind_param($checkRelStmt, "ii", $doctorId, $appointment['pacient_id']);
        mysqli_stmt_execute($checkRelStmt);
        $checkRelResult = mysqli_stmt_get_result($checkRelStmt);
        $appointmentCount = mysqli_fetch_assoc($checkRelResult)['appointment_count'];
        mysqli_stmt_close($checkRelStmt);

        // Dacă nu mai există programări, șterge relația doctor-pacient
        if ($appointmentCount == 0) {
            $deleteRelQuery = "DELETE FROM doctor_pacient WHERE doctor_id = ? AND pacient_id = ?";
            $deleteRelStmt = mysqli_prepare($conn, $deleteRelQuery);
            mysqli_stmt_bind_param($deleteRelStmt, "ii", $doctorId, $appointment['pacient_id']);
            mysqli_stmt_execute($deleteRelStmt);
            mysqli_stmt_close($deleteRelStmt);
        }

        // Returnează răspuns de succes
        $response = ['success' => true, 'message' => 'Programarea a fost ștearsă cu succes'];
        error_log("Se trimite răspuns de succes: " . json_encode($response));
        echo json_encode($response);
    } else {
        // Returnează eroare în caz de eșec
        $error = mysqli_error($conn);
        error_log("Ștergerea a eșuat: " . $error);
        echo json_encode(['success' => false, 'message' => 'Eroare la ștergerea programării: ' . $error]);
    }
    
    mysqli_stmt_close($deleteStmt);

} catch (Exception $e) {
    // Gestionare excepții
    error_log("Eroare la ștergerea programării: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Eroare internă la ștergerea programării']);
}

mysqli_close($conn);
?> 