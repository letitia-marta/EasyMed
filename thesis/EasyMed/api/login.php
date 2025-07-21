<?php
/**
 * API Endpoint pentru autentificarea utilizatorilor
 *
 * Acest endpoint gestionează autentificarea utilizatorilor prin API:
 * - Acceptă cereri POST și GET
 * - Validează credențialele (email și parolă)
 * - Verifică parola hash-uită în baza de date
 * - Returnează răspuns JSON cu statusul autentificării
 * - Include ID-ul utilizatorului în răspunsul de succes
 *
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

// Setează headerele pentru răspuns JSON
header('Content-Type: application/json');
// Include conexiunea la baza de date
include('../db_connection.php');
session_start();

// Procesează cererile POST și GET
if ($_SERVER["REQUEST_METHOD"] == "POST" || $_SERVER["REQUEST_METHOD"] == "GET") {
    // Extrage credențialele din POST sau GET
    $email = $_POST['email'] ?? $_GET['email'] ?? '';
    $password = $_POST['password'] ?? $_GET['password'] ?? '';

    // Validează prezența credențialelor
    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Email și parolă sunt necesare."]);
        //exit;
    }

    // Caută utilizatorul în baza de date după email
    $sql = "SELECT id, email, parola FROM utilizatori WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Utilizatorul a fost găsit - obține datele
        $stmt->bind_result($id, $stored_email, $stored_password);
        $stmt->fetch();

        // Verifică dacă parola este corectă
        if (password_verify($password, $stored_password)) {
            // Autentificare reușită
            echo json_encode(["status" => "success", "user_id" => $id]);
        } else {
            // Parolă incorectă
            echo json_encode(["status" => "error", "message" => "Parolă incorectă"]);
        }
    } else {
        // Email-ul nu există în baza de date
        echo json_encode(["status" => "error", "message" => "Emailul nu există"]);
    }

    $stmt->close();
    $conn->close();
} else {
    // Metoda HTTP nu este suportată
    echo json_encode(["status" => "error", "message" => "Cerere invalidă"]);
}
?>