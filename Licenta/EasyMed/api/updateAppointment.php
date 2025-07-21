<?php
/**
 * API Endpoint pentru actualizarea programărilor medicale
 *
 * Acest endpoint permite actualizarea programărilor existente:
 * - Primește datele actualizate prin POST request
 * - Validează toate câmpurile obligatorii
 * - Actualizează programarea în baza de date
 * - Returnează răspuns JSON cu rezultatul operației
 * - Include logging detaliat pentru debugging și audit
 * - Gestionează erorile de validare și de bază de date
 *
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

// Dezactivează afișarea erorilor pentru producție
error_reporting(0);
ini_set('display_errors', 0);

// Setează headerele pentru răspuns JSON și CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestionare pentru cereri OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Logging pentru debugging și audit
error_log("updateAppointment.php called with method: " . $_SERVER['REQUEST_METHOD']);

// Include conexiunea la baza de date
include('../db_connection.php');

// Procesează cererea POST pentru actualizarea programării
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        error_log("POST data received: " . print_r($_POST, true));
        
        // Extrage datele din POST
        $appointmentId = $_POST['appointmentId'] ?? null;
        $pacientId = $_POST['pacient_id'] ?? null;
        $medicId = $_POST['medic_id'] ?? null;
        $dataProgramare = $_POST['data_programare'] ?? null;
        $oraProgramare = $_POST['ora_programare'] ?? null;
        $status = $_POST['status'] ?? 'programat';
        $motivConsultatie = $_POST['motiv_consultatie'] ?? null;

        // Validează câmpurile obligatorii
        if (!$appointmentId || !$pacientId || !$medicId || !$dataProgramare || !$oraProgramare || !$motivConsultatie) {
            $response = [
                'success' => false,
                'error' => 'Toate câmpurile sunt obligatorii'
            ];
            error_log("Sending validation error response: " . json_encode($response));
            echo json_encode($response);
            exit;
        }

        // Query pentru actualizarea programării
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

        // Execută actualizarea și verifică rezultatul
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Programarea a fost actualizată cu succes
                $response = [
                    'success' => true,
                    'message' => 'Programarea a fost actualizată cu succes'
                ];
                error_log("Sending success response: " . json_encode($response));
                echo json_encode($response);
                exit;
            } else {
                // Nu s-a găsit programarea sau nu au fost făcute modificări
                $response = [
                    'success' => false,
                    'error' => 'Nu s-a găsit programarea sau nu au fost făcute modificări'
                ];
                error_log("Sending error response: " . json_encode($response));
                echo json_encode($response);
                exit;
            }
        } else {
            // Eroare la executarea query-ului
            $response = [
                'success' => false,
                'error' => 'Eroare la actualizarea programării: ' . $stmt->error
            ];
            error_log("Sending error response: " . json_encode($response));
            echo json_encode($response);
            exit;
        }

        $stmt->close();
        $conn->close();

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
} else {
    // Metodă HTTP nepermisă
    echo json_encode([
        'success' => false,
        'error' => 'Metoda HTTP nu este permisă'
    ]);
}
?> 