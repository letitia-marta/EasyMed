<?php
/**
 * Script pentru verificarea disponibilității unui interval orar
 * 
 * Acest script verifică dacă un interval orar specific este disponibil
 * pentru o programare cu un medic anumit:
 * - Primește datele prin JSON (doctor_id, date, time)
 * - Verifică în baza de date dacă intervalul este ocupat
 * - Returnează răspuns JSON cu statusul disponibilității
 * 
 * @package EasyMed
 * @author EasyMed Team
 * @version 1.0
 */

session_start();
require_once 'db_connection.php';

// Extrage datele din corpul cererii JSON
$data = json_decode(file_get_contents('php://input'), true);

// Extrage parametrii din datele primite
$doctor_id = $data['doctor_id'];
$date = $data['date'];
$time = $data['time'];

// Interogare pentru verificarea disponibilității intervalului orar
$stmt = $conn->prepare("SELECT id FROM programari 
    WHERE medic_id = ? 
    AND data_programare = ? 
    AND ora_programare = ? 
    AND status != 'anulat'");

$stmt->bind_param("iss", $doctor_id, $date, $time);
$stmt->execute();
$result = $stmt->get_result();
$isBooked = $result->num_rows > 0;

// Returnează răspunsul în format JSON
echo json_encode([
    'isBooked' => $isBooked
]);

$stmt->close();
$conn->close();
?>