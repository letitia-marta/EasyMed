<?php
/**
 * API Endpoint pentru încărcarea documentelor medicale
 *
 * Acest endpoint permite încărcarea documentelor medicale în sistem:
 * - Primește fișiere prin POST request cu detalii despre document
 * - Validează tipul și dimensiunea fișierului
 * - Salvează fișierul pe server cu nume unic
 * - Înregistrează documentul în baza de date
 * - Returnează răspuns JSON cu rezultatul operației
 * - Include logging detaliat pentru debugging și audit
 *
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

require_once '../db_connection.php';

// Setează headerele pentru răspuns JSON și CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestionare pentru cereri OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Logging pentru debugging și audit
error_log("=== DOCUMENT UPLOAD API ===");
error_log("Request received at " . date('Y-m-d H:i:s'));
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
error_log("User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'not set'));
error_log("Files array: " . print_r($_FILES, true));
error_log("POST data: " . print_r($_POST, true));

$patient_id = 1;

// Procesează cererea POST pentru încărcarea documentului
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Extrage datele din POST
        $title = trim($_POST['document_title'] ?? '');
        $type = $_POST['document_type'] ?? '';
        $patient_id_param = $_POST['pacient_id'] ?? $patient_id;
        
        error_log("Title: $title");
        error_log("Type: $type");
        error_log("Patient ID: $patient_id_param");
        
        // Validează câmpurile obligatorii
        if (empty($title) || empty($type)) {
            throw new Exception("Lipsesc câmpurile obligatorii");
        }
        
        // Verifică dacă fișierul a fost încărcat cu succes
        if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Eroare la încărcarea fișierului: " . ($_FILES['document_file']['error'] ?? 'unknown'));
        }
        
        // Extrage informațiile despre fișier
        $file = $_FILES['document_file'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmpName = $file['tmp_name'];
        $fileType = $file['type'];
        
        error_log("File name: $fileName");
        error_log("File size: $fileSize");
        error_log("File type: $fileType");
        
        // Verifică dimensiunea fișierului (maxim 10MB)
        if ($fileSize > 10 * 1024 * 1024) {
            throw new Exception("Fișierul este prea mare (maxim 10MB)");
        }
        
        // Verifică tipul de fișier permis
        $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedTypes)) {
            throw new Exception("Tip de fișier nepermis");
        }
        
        // Creează directorul de upload dacă nu există
        $uploadDir = '../uploads/documents/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generează nume unic pentru fișier
        $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $uniqueFileName;
        
        error_log("Upload path: $uploadPath");
        
        // Mută fișierul în directorul de upload
        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            error_log("File moved successfully");
            
            // Salvează documentul în baza de date
            $stmt = $conn->prepare("INSERT INTO documente (pacient_id, medic_id, titlu, tip_document, nume_fisier, data_upload) VALUES (?, NULL, ?, ?, ?, NOW())");
            $stmt->bind_param("isss", $patient_id_param, $title, $type, $uniqueFileName);
            
            if ($stmt->execute()) {
                $document_id = $conn->insert_id;
                error_log("Document saved to database with ID: $document_id");
                
                // Returnează răspuns de succes
                echo json_encode([
                    'success' => true,
                    'message' => 'Document încărcat cu succes!',
                    'document_id' => $document_id,
                    'filename' => $uniqueFileName
                ], JSON_UNESCAPED_UNICODE);
            } else {    
                // Șterge fișierul dacă salvarea în baza de date eșuează
                unlink($uploadPath);
                throw new Exception("Eroare la salvarea în baza de date: " . $stmt->error);
            }
            $stmt->close();
        } else {
            throw new Exception("Eroare la mutarea fișierului");
        }
        
    } catch (Exception $e) {
        // Gestionare excepții
        error_log("Exception caught: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    // Metodă HTTP nepermisă
    error_log("Invalid request method");
    echo json_encode([
        'success' => false,
        'message' => 'Metodă de cerere invalidă'
    ], JSON_UNESCAPED_UNICODE);
}

error_log("=== END DOCUMENT UPLOAD API ===");
?> 