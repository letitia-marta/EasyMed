<?php
/**
 * Script pentru ștergerea documentelor medicale
 * 
 * Acest script permite pacienților să șteargă documentele lor din sistem.
 * Include ștergerea fișierului fizic și a înregistrării din baza de date.
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

// Obține ID-ul pacientului din sesiune
$stmt = $conn->prepare("SELECT id FROM pacienti WHERE utilizator_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$patient_id = $patient['id'];
$stmt->close();

// Citește și decodifică datele JSON din request
$input = json_decode(file_get_contents('php://input'), true);
$document_id = isset($input['document_id']) ? (int)$input['document_id'] : 0;

// Validează ID-ul documentului
if ($document_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID-ul documentului este invalid']);
    exit();
}

// Verifică dacă documentul există și aparține pacientului
$stmt = $conn->prepare("SELECT * FROM documente WHERE id = ? AND pacient_id = ?");
$stmt->bind_param("ii", $document_id, $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();
$stmt->close();

// Verifică dacă documentul a fost găsit
if (!$document) {
    echo json_encode(['success' => false, 'message' => 'Documentul nu a fost găsit']);
    exit();
}

// Șterge fișierul fizic de pe server
$filePath = 'uploads/documents/' . $document['nume_fisier'];
if (file_exists($filePath)) {
    unlink($filePath);
}

// Șterge înregistrarea din baza de date
$stmt = $conn->prepare("DELETE FROM documente WHERE id = ? AND pacient_id = ?");
$stmt->bind_param("ii", $document_id, $patient_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Documentul a fost șters cu succes']);
} else {
    echo json_encode(['success' => false, 'message' => 'Eroare la ștergerea documentului']);
}

$stmt->close();
?> 