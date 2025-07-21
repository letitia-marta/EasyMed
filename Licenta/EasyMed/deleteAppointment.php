<?php
/**
 * Script pentru ștergerea programărilor medicale
 * 
 * Acest script permite utilizatorilor să șteargă programările lor:
 * - Verifică autentificarea utilizatorului
 * - Validează că programarea aparține utilizatorului autentificat
 * - Șterge programarea din baza de date
 * - Returnează răspuns JSON cu rezultatul operației
 * - Gestionează erorile de autorizare și validare
 * 
 * @package EasyMed
 * @author EasyMed Team
 * @version 1.0
 */

require_once 'db_connection.php';
session_start();

header('Content-Type: application/json');

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Neautorizat.']);
    exit();
}

// Verifică metoda HTTP și prezența ID-ului programării
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['appointment_id'])) {
    echo json_encode(['success' => false, 'message' => 'Cerere invalidă.']);
    exit();
}

$appointment_id = intval($_POST['appointment_id']);

// Obține ID-ul pacientului pentru programarea de șters
$stmt = $conn->prepare('SELECT pacient_id FROM programari WHERE id = ?');
$stmt->bind_param('i', $appointment_id);
$stmt->execute();
$stmt->bind_result($pacient_id);
$stmt->fetch();
$stmt->close();

// Verifică dacă programarea există
if (!isset($pacient_id) || $pacient_id == null) {
    echo json_encode(['success' => false, 'message' => 'Programarea nu a fost găsită.']);
    exit();
}

// Obține ID-ul pacientului al utilizatorului autentificat
$stmt = $conn->prepare('SELECT id FROM pacienti WHERE utilizator_id = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($user_pacient_id);
$stmt->fetch();
$stmt->close();

// Verifică dacă utilizatorul are permisiunea să șteargă programarea
if ($user_pacient_id != $pacient_id) {
    echo json_encode(['success' => false, 'message' => 'Nu aveți permisiunea să ștergeți această programare.']);
    exit();
}

// Șterge programarea din baza de date
$stmt = $conn->prepare('DELETE FROM programari WHERE id = ?');
$stmt->bind_param('i', $appointment_id);
$success = $stmt->execute();
$stmt->close();

// Returnează rezultatul operației
if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Eroare la ștergere.']);
}
$conn->close();
?> 