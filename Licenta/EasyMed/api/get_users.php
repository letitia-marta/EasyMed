<?php
/**
 * API Endpoint pentru obținerea listei de utilizatori
 *
 * Acest endpoint returnează lista utilizatorilor din sistem:
 * - Interoghează baza de date pentru toți utilizatorii
 * - Returnează ID-ul și email-ul utilizatorilor
 * - Folosește conexiune directă la baza de date
 * - Gestionează erorile de conectivitate la baza de date
 * - Include gestionarea conexiunii la baza de date
 *
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

// Setează headerele pentru răspuns JSON
header('Content-Type: application/json');

// Parametrii de conexiune la baza de date
$host = "localhost";
$user = "root";
$password = "";
$dbname = "easymed";

// Stabilește conexiunea la baza de date
$conn = new mysqli($host, $user, $password, $dbname);

// Verifică dacă conexiunea a reușit
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed"]);
    exit();
}

// Interoghează toți utilizatorii din baza de date
$sql = "SELECT id, email FROM utilizatori";
$result = $conn->query($sql);

$users = [];
// Formatează rezultatele
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Returnează lista utilizatorilor în format JSON
echo json_encode($users);

// Închide conexiunea la baza de date
$conn->close();
?>