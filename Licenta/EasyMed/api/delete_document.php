<?php
/**
 * API Endpoint pentru ștergerea documentelor medicale
 * 
 * Acest endpoint permite ștergerea documentelor încărcate de pacienți:
 * - Primește ID-ul documentului și ID-ul pacientului prin JSON
 * - Validează existența pacientului și a documentului
 * - Verifică dreptul de acces la document
 * - Șterge fișierul de pe disc și din baza de date
 * - Returnează răspuns JSON cu rezultatul operației
 * - Gestionează erorile de validare, acces și sistem de fișiere
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

// Setează headerele pentru răspuns JSON și CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestionare pentru cereri OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include conexiunea la baza de date
require_once '../db_connection.php';

// Decodifică datele JSON din request
$input = json_decode(file_get_contents('php://input'), true);

// Validează parametrii necesari
if (!isset($input['document_id']) || !isset($input['patient_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$document_id = (int)$input['document_id'];
$patient_id = (int)$input['patient_id'];

// Verifică validitatea ID-urilor
if ($document_id <= 0 || $patient_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid document ID or patient ID']);
    exit();
}

// Verifică existența pacientului
$stmt = $conn->prepare("SELECT id FROM pacienti WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$stmt->close();

if (!$patient) {
    echo json_encode(['success' => false, 'message' => 'Patient not found']);
    exit();
}

// Verifică existența documentului și dreptul de acces
$stmt = $conn->prepare("SELECT * FROM documente WHERE id = ? AND pacient_id = ?");
$stmt->bind_param("ii", $document_id, $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();
$stmt->close();

if (!$document) {
    echo json_encode(['success' => false, 'message' => 'Document not found or access denied']);
    exit();
}

// Șterge fișierul de pe disc dacă există
$filePath = '../uploads/documents/' . $document['nume_fisier'];
if (file_exists($filePath)) {
    if (!unlink($filePath)) {
        echo json_encode(['success' => false, 'message' => 'Error deleting file from filesystem']);
        exit();
    }
}
    
// Șterge documentul din baza de date
$stmt = $conn->prepare("DELETE FROM documente WHERE id = ? AND pacient_id = ?");
$stmt->bind_param("ii", $document_id, $patient_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Document deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting document from database']);
}

$stmt->close();
?> 