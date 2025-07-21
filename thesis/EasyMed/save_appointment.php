<?php
/**
 * Script pentru salvarea programărilor medicale
 * 
 * Acest script gestionează crearea de noi programări:
 * - Validează datele primite prin POST
 * - Verifică existența pacientului și medicului
 * - Verifică disponibilitatea intervalului orar
 * - Salvează programarea în baza de date
 * - Creează relația medic-pacient dacă nu există
 * - Returnează răspuns JSON cu rezultatul operației
 */

session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

$tableQuery = "SHOW CREATE TABLE programari";
$tableResult = mysqli_query($conn, $tableQuery);
if ($tableResult) {
    $row = mysqli_fetch_assoc($tableResult);
    error_log("Programari table structure: " . print_r($row['Create Table'], true));
}

$patient_id = isset($_POST['patient_id']) ? $_POST['patient_id'] : null;
$date = isset($_POST['date']) ? $_POST['date'] : null;
$time = isset($_POST['time']) ? $_POST['time'] : null;
$doctor_id = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : null;
$consultation_type = isset($_POST['consultation_type']) ? $_POST['consultation_type'] : null;

error_log("Raw POST data: " . print_r($_POST, true));
error_log("Received time value: '" . $time . "'");
error_log("Time value type: " . gettype($time));
error_log("Time value length: " . strlen($time));

if (!$patient_id || !$date || !$time || !$doctor_id || !$consultation_type) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

$directQuery = "SELECT * FROM pacienti WHERE id = ?";
$directStmt = mysqli_prepare($conn, $directQuery);
if ($directStmt) {
    mysqli_stmt_bind_param($directStmt, "i", $patient_id);
    mysqli_stmt_execute($directStmt);
    $result = mysqli_stmt_get_result($directStmt);
    $patient = mysqli_fetch_assoc($result);
    error_log("Direct query result for patient ID $patient_id: " . print_r($patient, true));
    mysqli_stmt_close($directStmt);

    if (!$patient) {
        error_log("Patient ID $patient_id not found in database");
        echo json_encode(['success' => false, 'message' => 'Pacientul nu a fost găsit în baza de date']);
        exit;
}
}

$doctorQuery = "SELECT * FROM medici WHERE id = ?";
$doctorStmt = mysqli_prepare($conn, $doctorQuery);
if ($doctorStmt) {
    mysqli_stmt_bind_param($doctorStmt, "i", $doctor_id);
    mysqli_stmt_execute($doctorStmt);
    $result = mysqli_stmt_get_result($doctorStmt);
    $doctor = mysqli_fetch_assoc($result);
    error_log("Doctor query result for ID $doctor_id: " . print_r($doctor, true));
    mysqli_stmt_close($doctorStmt);

    if (!$doctor) {
        error_log("Doctor ID $doctor_id not found in database");
        echo json_encode(['success' => false, 'message' => 'Medicul nu a fost găsit în baza de date']);
        exit;
    }
}

error_log("Attempting to create datetime from date: '$date' and time: '$time'");
$datetime = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
if (!$datetime) {
    error_log("Error creating datetime from: " . $date . ' ' . $time);
    echo json_encode(['success' => false, 'message' => 'Format dată/ora invalid']);
    exit;
}
$formatted_datetime = $datetime->format('Y-m-d H:i:s');
error_log("Formatted datetime: " . $formatted_datetime);

try {
    $checkQuery = "SELECT id FROM programari WHERE data_programare = ? AND medic_id = ?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    
    if ($checkStmt) {
        mysqli_stmt_bind_param($checkStmt, "si", $formatted_datetime, $doctor_id);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);

        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            echo json_encode(['success' => false, 'message' => 'Acest interval orar este deja ocupat.']);
            mysqli_stmt_close($checkStmt);
            exit;
}
        mysqli_stmt_close($checkStmt);
    }

    $query = "INSERT INTO programari (pacient_id, medic_id, data_programare, ora_programare, status, motiv_consultatie) VALUES (?, ?, ?, ?, 'programat', ?)";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iisss", $patient_id, $doctor_id, $formatted_datetime, $time, $consultation_type);
        
        if (mysqli_stmt_execute($stmt)) {
            $checkRelQuery = "SELECT id FROM doctor_pacient WHERE doctor_id = ? AND pacient_id = ?";
            $checkRelStmt = mysqli_prepare($conn, $checkRelQuery);
            
            if ($checkRelStmt) {
                mysqli_stmt_bind_param($checkRelStmt, "ii", $doctor_id, $patient_id);
                mysqli_stmt_execute($checkRelStmt);
                mysqli_stmt_store_result($checkRelStmt);
                    
                if (mysqli_stmt_num_rows($checkRelStmt) === 0) {
                    $relQuery = "INSERT INTO doctor_pacient (doctor_id, pacient_id) VALUES (?, ?)";
                    $relStmt = mysqli_prepare($conn, $relQuery);
                    
                    if ($relStmt) {
                        mysqli_stmt_bind_param($relStmt, "ii", $doctor_id, $patient_id);
                        mysqli_stmt_execute($relStmt);
                        mysqli_stmt_close($relStmt);
                    }
                }
                
                mysqli_stmt_close($checkRelStmt);
            }
            
            echo json_encode(['success' => true, 'message' => 'Programarea a fost salvată cu succes.']);
        } else {
            error_log("Error inserting appointment: " . mysqli_error($conn));
            echo json_encode(['success' => false, 'message' => 'Eroare la salvarea programării: ' . mysqli_error($conn)]);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error preparing appointment insert statement: " . mysqli_error($conn));
        echo json_encode(['success' => false, 'message' => 'Eroare la pregătirea interogării: ' . mysqli_error($conn)]);
}
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Eroare: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>