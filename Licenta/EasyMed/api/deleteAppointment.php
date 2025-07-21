<?php
/**
 * API Endpoint pentru ștergerea programărilor medicale
 * 
 * Acest endpoint permite ștergerea programărilor din sistem:
 * - Primește ID-ul programării prin POST request
 * - Validează existența programării înainte de ștergere
 * - Execută ștergerea din baza de date cu prepared statements
 * - Returnează răspuns JSON cu rezultatul operației
 * - Include logging detaliat pentru debugging și audit
 * - Gestionează erorile de baza de date și validare
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
error_log("deleteAppointment.php called with method: " . $_SERVER['REQUEST_METHOD']);

// Include conexiunea la baza de date
include('../db_connection.php');

// Procesează cererea POST pentru ștergerea programării
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        error_log("POST data received: " . print_r($_POST, true));
        
        // Extrage ID-ul programării din request
        $appointmentId = $_POST['appointmentId'] ?? null;

        // Validează dacă ID-ul programării este furnizat
        if (!$appointmentId) {
            $response = [
                'success' => false,
                'error' => 'ID-ul programării este obligatoriu'
            ];
            error_log("Sending validation error response: " . json_encode($response));
            echo json_encode($response);
            exit;
        }

        // Query pentru ștergerea programării
        $sql = "DELETE FROM programari WHERE id = ?";
        
        // Pregătește și execută statement-ul cu prepared statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $appointmentId);

        // Execută ștergerea și verifică rezultatul
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Programarea a fost ștearsă cu succes
                $response = [
                    'success' => true,
                    'message' => 'Programarea a fost ștearsă cu succes'
                ];
                error_log("Sending success response: " . json_encode($response));
                echo json_encode($response);
                exit;
            } else {
                // Nu s-a găsit programarea sau nu a fost ștearsă
                $response = [
                    'success' => false,
                    'error' => 'Nu s-a găsit programarea sau nu a fost ștearsă'
                ];
                error_log("Sending error response: " . json_encode($response));
                echo json_encode($response);
                exit;
            }
        } else {
            // Eroare la executarea query-ului
            $response = [
                'success' => false,
                'error' => 'Eroare la ștergerea programării: ' . $stmt->error
            ];
            error_log("Sending error response: " . json_encode($response));
            echo json_encode($response);
            exit;
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