<?php
/**
 * Script pentru descărcarea documentelor medicale
 * 
 * Acest script permite utilizatorilor să descarce documentele medicale
 * încărcate în sistem:
 * - Verifică autentificarea utilizatorului
 * - Validează că documentul aparține pacientului autentificat
 * - Setează headerele HTTP pentru descărcare
 * - Transmite fișierul către browser
 * - Gestionează erorile (fișier inexistent, document negăsit)
 * 
 * @package EasyMed
 * @author EasyMed Team
 * @version 1.0
 */

session_start();
require_once 'db_connection.php';

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    header("Location: pacientiLogin.php");
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

// Verifică dacă s-a furnizat ID-ul documentului
if (isset($_GET['id'])) {
    $document_id = (int)$_GET['id'];
    
    // Obține informațiile despre document și verifică proprietatea
    $stmt = $conn->prepare("SELECT * FROM documente WHERE id = ? AND pacient_id = ?");
    $stmt->bind_param("ii", $document_id, $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $document = $result->fetch_assoc();
    $stmt->close();
    
    if ($document) {
        $filePath = 'uploads/documents/' . $document['nume_fisier'];
        
        // Verifică dacă fișierul există pe server
        if (file_exists($filePath)) {
            // Setează headerele HTTP pentru descărcare
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $document['titlu'] . '.' . pathinfo($document['nume_fisier'], PATHINFO_EXTENSION) . '"');
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            
            // Transmite fișierul către browser
            readfile($filePath);
            exit();
        } else {
            // Fișierul nu există pe server
            header("Location: istoricPacient.php?error=file_not_found");
        }
    } else {
        // Documentul nu a fost găsit sau nu aparține pacientului
        header("Location: istoricPacient.php?error=document_not_found");
    }
} else {
    // Nu s-a furnizat ID-ul documentului
    header("Location: istoricPacient.php");
}
?> 