<?php
/**
 * Script pentru actualizarea programărilor medicale
 * 
 * Acest script permite actualizarea detaliilor unei programări existente:
 * - Validează datele primite prin POST
 * - Actualizează programarea în baza de date
 * - Redirecționează utilizatorul înapoi cu mesaje de succes/eroare
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

error_log("updateAppointment.php called with method: " . $_SERVER['REQUEST_METHOD']);

include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        error_log("POST data received: " . print_r($_POST, true));
        
        $appointmentId = $_POST['appointmentId'] ?? null;
        $pacientId = $_POST['pacient_id'] ?? null;
        $medicId = $_POST['medic_id'] ?? null;
        $dataProgramare = $_POST['data_programare'] ?? null;
        $oraProgramare = $_POST['ora_programare'] ?? null;
        $status = $_POST['status'] ?? 'programat';
        $motivConsultatie = $_POST['motiv_consultatie'] ?? null;

        $missingFields = [];
        if (!$appointmentId) $missingFields[] = 'appointmentId';
        if (!$pacientId) $missingFields[] = 'pacient_id';
        if (!$medicId) $missingFields[] = 'medic_id';
        if (!$dataProgramare) $missingFields[] = 'data_programare';
        if (!$oraProgramare) $missingFields[] = 'ora_programare';
        if (!$motivConsultatie) $missingFields[] = 'motiv_consultatie';
        
        if (!empty($missingFields)) {
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=1&message=" . urlencode("Toate câmpurile sunt obligatorii"));
            exit;
        }

        $sql = "UPDATE programari SET 
                pacient_id = ?, 
                medic_id = ?, 
                data_programare = ?, 
                ora_programare = ?, 
                status = ?, 
                motiv_consultatie = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissssi", 
            $pacientId, 
            $medicId, 
            $dataProgramare, 
            $oraProgramare, 
            $status, 
            $motivConsultatie, 
            $appointmentId
        );

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                header("Location: " . $_SERVER['HTTP_REFERER'] . "?success=1&message=" . urlencode("Programarea a fost actualizată cu succes"));
                exit();
            } else {
                header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=1&message=" . urlencode("Nu s-a găsit programarea sau nu au fost făcute modificări"));
                exit();
            }
        } else {
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=1&message=" . urlencode("Eroare la actualizarea programării: " . $stmt->error));
            exit();
        }

        $stmt->close();
        $conn->close();

    } catch (Exception $e) {    
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=1&message=" . urlencode("Eroare server: " . $e->getMessage()));
        exit();
    }
} else {
    header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=1&message=" . urlencode("Metoda HTTP nu este permisă"));
    exit();
}
?> 