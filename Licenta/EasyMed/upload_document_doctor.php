<?php
/**
 * Script pentru încărcarea documentelor medicale de către medici
 * 
 * Acest script permite medicilor să încarce documente pentru pacienții lor:
 * - Verifică autentificarea medicului
 * - Validează că medicul are acces la pacientul specificat
 * - Validează tipul și dimensiunea fișierului
 * - Salvează fișierul pe server și înregistrează în baza de date
 * - Redirecționează cu mesaje de succes/eroare
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

session_start();
require_once 'db_connection.php';

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obține ID-ul medicului din sesiune
$stmt = $conn->prepare("SELECT id FROM medici WHERE utilizator_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();
$doctor_id = $doctor['id'];
$stmt->close();

// Procesează cererea POST pentru încărcarea documentului
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extrage datele din formular
    $patient_id = $_POST['pacient_id'];
    $medic_id = $_POST['medic_id'];
    $title = trim($_POST['document_title']);
    $type = $_POST['document_type'];
    
    // Validează câmpurile obligatorii
    if (empty($title) || empty($type) || empty($patient_id) || empty($medic_id)) {
        header("Location: detaliiPacient.php?cnp=" . urlencode($_GET['cnp'] ?? '') . "&error=missing_fields");
        exit();
    }
    
    // Verifică dacă medicul are acces la pacientul specificat
    $stmt = $conn->prepare("SELECT 1 FROM doctor_pacient WHERE doctor_id = ? AND pacient_id = ?");
    $stmt->bind_param("ii", $doctor_id, $patient_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        header("Location: detaliiPacient.php?cnp=" . urlencode($_GET['cnp'] ?? '') . "&error=unauthorized");
        exit();
    }
    $stmt->close();
    
    // Verifică dacă fișierul a fost încărcat corect
    if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
        header("Location: detaliiPacient.php?cnp=" . urlencode($_GET['cnp'] ?? '') . "&error=file_upload_error");
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
        header("Location: detaliiPacient.php?cnp=" . urlencode($_GET['cnp'] ?? '') . "&error=file_too_large");
        exit();
    }
    
    // Verifică tipul fișierului
    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedTypes)) {
        header("Location: detaliiPacient.php?cnp=" . urlencode($_GET['cnp'] ?? '') . "&error=invalid_file_type");
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
        $stmt = $conn->prepare("INSERT INTO documente (pacient_id, medic_id, titlu, tip_document, nume_fisier, data_upload) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iisss", $patient_id, $medic_id, $title, $type, $uniqueFileName);
        
        if ($stmt->execute()) { 
            // Obține CNP-ul pacientului pentru redirecționare
            $stmt = $conn->prepare("SELECT CNP FROM pacienti WHERE id = ?");
            $stmt->bind_param("i", $patient_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $patient = $result->fetch_assoc();
            $stmt->close();
            
            // Redirecționează cu mesaj de succes
            header("Location: detaliiPacient.php?cnp=" . urlencode($patient['CNP']) . "&success=document_uploaded");
        } else {
            // Eroare la salvarea în baza de date - șterge fișierul
            unlink($uploadPath);
            header("Location: detaliiPacient.php?cnp=" . urlencode($_GET['cnp'] ?? '') . "&error=database_error");
        }
        $stmt->close();
    } else {
        // Eroare la mutarea fișierului
        header("Location: detaliiPacient.php?cnp=" . urlencode($_GET['cnp'] ?? '') . "&error=file_move_error");
    }
} else {
    // Metoda HTTP nu este POST - redirecționează la lista de pacienți
    header("Location: listaPacienti.php");
}
?> 