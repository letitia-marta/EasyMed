<?php
/**
 * Script pentru încărcarea documentelor medicale
 * 
 * Acest script permite pacienților să încarce documente medicale:
 * - Verifică autentificarea utilizatorului
 * - Validează tipul și dimensiunea fișierului
 * - Salvează fișierul pe server
 * - Înregistrează documentul în baza de date
 * - Gestionează erorile de încărcare și validare
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

// Procesează cererea POST pentru încărcarea documentului
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['document_title']);
    $type = $_POST['document_type'];
    
    // Validează câmpurile obligatorii
    if (empty($title) || empty($type)) {
        header("Location: istoricPacient.php?error=missing_fields");
        exit();
    }
    
    // Verifică dacă fișierul a fost încărcat corect
    if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
        header("Location: istoricPacient.php?error=file_upload_error");
        exit();
    }
    
    // Extrage informațiile despre fișier
    $file = $_FILES['document_file'];
    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileTmpName = $file['tmp_name'];
    $fileType = $file['type'];
    
    // Verifică dimensiunea fișierului (maxim 10MB)
    if ($fileSize > 10 * 1024 * 1024) {
        header("Location: istoricPacient.php?error=file_too_large");
        exit();
    }
    
    // Verifică tipul fișierului
    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedTypes)) {
        header("Location: istoricPacient.php?error=invalid_file_type");
        exit();
    }
    
    // Creează directorul de încărcare dacă nu există
    $uploadDir = 'uploads/documents/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generează un nume unic pentru fișier
    $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $uniqueFileName;
    
    // Mută fișierul încărcat în directorul destinat
    if (move_uploaded_file($fileTmpName, $uploadPath)) {
        // Salvează informațiile despre document în baza de date
        $stmt = $conn->prepare("INSERT INTO documente (pacient_id, medic_id, titlu, tip_document, nume_fisier, data_upload) VALUES (?, NULL, ?, ?, ?, NOW())");
        $stmt->bind_param("isss", $patient_id, $title, $type, $uniqueFileName);
        
        if ($stmt->execute()) {
            // Încărcare reușită
            header("Location: istoricPacient.php?success=document_uploaded");
        } else {
            // Eroare la salvarea în baza de date - șterge fișierul
            unlink($uploadPath);
            header("Location: istoricPacient.php?error=database_error");
        }
        $stmt->close();
    } else {
        // Eroare la mutarea fișierului
        header("Location: istoricPacient.php?error=file_move_error");
    }
} else {
    // Metoda HTTP nu este POST
    header("Location: istoricPacient.php");
}
?> 