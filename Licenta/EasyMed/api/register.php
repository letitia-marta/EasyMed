<?php
/**
 * API Endpoint pentru înregistrarea utilizatorilor noi
 *
 * Acest endpoint gestionează înregistrarea utilizatorilor noi în sistem:
 * - Acceptă datele de înregistrare prin POST
 * - Validează datele (email, parolă, confirmare parolă, CNP, etc.)
 * - Creează contul de utilizator cu parolă hash-uită
 * - Creează profilul de pacient asociat
 * - Returnează răspuns JSON cu statusul înregistrării
 * - Gestionează erorile de validare și baza de date
 *
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */
error_reporting(0);
ini_set('display_errors', 0);

// Setează headerele pentru CORS și JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Parametrii de conexiune la baza de date
$host = 'localhost';
$db = 'easymed';
$user = 'root';
$pass = '';

// Stabilește conexiunea la baza de date
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

// Extrage datele din cererea POST
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Datele personale ale pacientului
$cnp = $_POST['cnp'] ?? '';
$nume = $_POST['nume'] ?? '';
$prenume = $_POST['prenume'] ?? '';
$data_nasterii = $_POST['data_nasterii'] ?? '';
$sex = $_POST['sex'] ?? '';

// Validează că parolele se potrivesc
if ($password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Parolele nu se potrivesc"]);
    exit();
}

// Hash-uiește parola pentru securitate
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Creează contul de utilizator
$sql_user = "INSERT INTO utilizatori (email, parola, rol) VALUES (?, ?, ?)";
$stmt_user = $conn->prepare($sql_user);
$rol = "pacient";
$stmt_user->bind_param("sss", $email, $hashed_password, $rol);

if ($stmt_user->execute()) {
    // Obține ID-ul utilizatorului creat
    $utilizator_id = $stmt_user->insert_id;

    // Creează profilul de pacient
    $sql_pacient = "INSERT INTO pacienti (utilizator_id, CNP, nume, prenume, data_nasterii, sex) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_pacient = $conn->prepare($sql_pacient);
    $stmt_pacient->bind_param("isssss", $utilizator_id, $cnp, $nume, $prenume, $data_nasterii, $sex);

    if ($stmt_pacient->execute()) {
        // Înregistrare completă cu succes
        echo json_encode(["status" => "success", "message" => "Înregistrare reușită!"]);
    } else {
        // Eroare la crearea profilului de pacient
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Eroare la pacient: " . $stmt_pacient->error]);
    }

    $stmt_pacient->close();
} else {
    // Eroare la crearea contului de utilizator
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Eroare la utilizator: " . $stmt_user->error]);
}

$stmt_user->close();
$conn->close();
?>