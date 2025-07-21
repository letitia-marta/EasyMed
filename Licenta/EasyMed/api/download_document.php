<?php
/**
 * API Endpoint pentru descărcarea documentelor medicale
 *
 * Acest endpoint permite descărcarea fișierelor medicale încărcate:
 * - Primește ID-ul documentului prin GET
 * - Verifică existența documentului și a fișierului pe server
 * - Setează headerele corespunzătoare pentru descărcare (tip, denumire, dimensiune)
 * - Returnează fișierul ca atașament pentru utilizator
 * - Gestionează erorile de validare, acces și sistem de fișiere
 * - Include logging detaliat pentru debugging și audit
 *
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

require_once '../db_connection.php';

// Setează headerele pentru CORS și metode permise
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestionare pentru cereri OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Logging pentru debugging și audit
error_log("=== DOCUMENT DOWNLOAD API ===");
error_log("Request received at " . date('Y-m-d H:i:s'));
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("GET parameters: " . print_r($_GET, true));

// Procesează cererea GET pentru descărcarea documentului
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        // Extrage ID-ul documentului
        $document_id = intval($_GET['id']);
        error_log("Downloading document ID: $document_id");
        
        // Caută documentul în baza de date
        $stmt = $conn->prepare("SELECT nume_fisier, titlu FROM documente WHERE id = ?");
        $stmt->bind_param("i", $document_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            error_log("Document not found: $document_id");
            http_response_code(404);
            echo json_encode(['error' => 'Documentul nu a fost găsit']);
            exit();
        }
        
        $document = $result->fetch_assoc();
        $filename = $document['nume_fisier'];
        $title = $document['titlu'];
        $stmt->close();
        
        error_log("Document found: $filename, title: $title");
        
        // Verifică existența fișierului pe disc
        $file_path = "../uploads/documents/" . $filename;
        
        if (!file_exists($file_path)) {
            error_log("File not found on disk: $file_path");
            http_response_code(404);
            echo json_encode(['error' => 'Fișierul nu a fost găsit pe server']);
            exit();
        }
        
        // Setează tipul de conținut în funcție de extensie
        $file_size = filesize($file_path);
        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $content_type = 'application/octet-stream';
        switch ($file_extension) {
            case 'pdf':
                $content_type = 'application/pdf';
                break;
            case 'jpg':
            case 'jpeg':
                $content_type = 'image/jpeg';
                break;
            case 'png':
                $content_type = 'image/png';
                break;
            case 'doc':
                $content_type = 'application/msword';
                break;
            case 'docx':
                $content_type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                break;
        }
        
        // Setează headerele pentru descărcare
        header('Content-Type: ' . $content_type);
        header('Content-Disposition: attachment; filename="' . $title . '.' . $file_extension . '"');
        header('Content-Length: ' . $file_size);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        error_log("Serving file: $file_path, size: $file_size, type: $content_type");
        
        // Trimite fișierul către client
        readfile($file_path);
        
        error_log("File download completed successfully");
        
    } catch (Exception $e) {
        // Gestionare excepții
        error_log("Exception during download: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Eroare la descărcarea documentului: ' . $e->getMessage()]);
    }
} else {
    // Cerere invalidă sau lipsă ID document
    error_log("Invalid request method or missing document ID");
    http_response_code(400);
    echo json_encode(['error' => 'Cerere invalidă']);
}

error_log("=== END DOCUMENT DOWNLOAD API ===");
?> 