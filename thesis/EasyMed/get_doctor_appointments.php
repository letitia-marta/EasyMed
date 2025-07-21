<?php
/**
 * Script pentru obținerea programărilor detaliate ale unui medic
 * 
 * Acest script returnează programările detaliate pentru un medic autentificat:
 * - Verifică autentificarea medicului
 * - Obține ID-ul medicului din sesiune
 * - Validează accesul la programările solicitate
 * - Returnează programările cu informații complete despre pacienți
 * - Include data, ora, numele pacientului, CNP și motivul consultației
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

session_start();
require_once 'db_connection.php';

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
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

// Extrage parametrii din cererea GET (data și ID-ul medicului solicitat)
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$requestedDoctorId = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : $doctorId;

// Verifică dacă medicul are acces la programările solicitate
if ($requestedDoctorId !== $doctorId) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access to appointments']);
    exit();
}

// Interogare pentru obținerea programărilor cu informații complete despre pacienți
$appointmentsQuery = "SELECT 
                        p.id,
                        p.data_programare,
                        p.ora_programare,
                        p.motiv_consultatie,
                        CONCAT(pat.nume, ' ', pat.prenume) as patient_name,
                        pat.CNP as patient_cnp
                     FROM programari p
                     INNER JOIN pacienti pat ON p.pacient_id = pat.id
                     WHERE p.medic_id = ? 
                     AND DATE(p.data_programare) = ?
                     ORDER BY p.ora_programare ASC";

// Pregătește și execută interogarea
$stmt = mysqli_prepare($conn, $appointmentsQuery);
mysqli_stmt_bind_param($stmt, "is", $doctorId, $date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Colectează toate programările cu informațiile complete
$appointments = [];
while ($row = mysqli_fetch_assoc($result)) {
    $timeSlot = $row['ora_programare'];
    
    // Construiește array-ul cu informațiile programării
    $appointments[] = [
        'id' => $row['id'],
        'date' => $row['data_programare'],
        'time_slot' => $timeSlot,
        'patient_name' => $row['patient_name'],
        'patient_cnp' => $row['patient_cnp'],
        'consultation_type' => $row['motiv_consultatie']
    ];
}

mysqli_stmt_close($stmt);

// Returnează rezultatul în format JSON
header('Content-Type: application/json');
echo json_encode($appointments);
?> 