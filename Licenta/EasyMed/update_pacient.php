<?php
/**
 * Script pentru actualizarea datelor pacienților
 * 
 * Acest script permite medicilor să actualizeze informațiile pacienților lor:
 * - Verifică autentificarea medicului
 * - Validează că pacientul aparține medicului
 * - Actualizează câmpurile permise (nume, prenume, adresa, grupa_sanguina)
 * - Returnează răspuns JSON cu rezultatul operației
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */
    include('db_connection.php');
    session_start();

    // Verifică dacă utilizatorul este autentificat
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Utilizator neautentificat']);
        exit();
    }

    // Verifică prezența parametrilor obligatorii
    if (!isset($_POST['field']) || !isset($_POST['value']) || !isset($_POST['cnp'])) {
        echo json_encode(['success' => false, 'message' => 'Parametri lipsă']);
        exit();
    }

    // Extrage datele din request
    $field = $_POST['field'];
    $value = $_POST['value'];
    $cnp = $_POST['cnp'];

    // Definește câmpurile permise pentru actualizare
    $allowed_fields = ['nume', 'prenume', 'adresa', 'grupa_sanguina'];
    if (!in_array($field, $allowed_fields)) {
        echo json_encode(['success' => false, 'message' => 'Câmp invalid']);
        exit();
    }

    // Obține ID-ul medicului din sesiune
    $utilizator_id = $_SESSION['user_id'];
    $sqlDoctor = "SELECT id FROM medici WHERE utilizator_id = ?";
    $stmtDoctor = $conn->prepare($sqlDoctor);
    $stmtDoctor->bind_param("i", $utilizator_id);
    $stmtDoctor->execute();
    $stmtDoctor->bind_result($doctor_id);
    $stmtDoctor->fetch();
    $stmtDoctor->close();

    // Verifică dacă pacientul aparține medicului
    $sqlDoctorPatient = "SELECT 1 FROM doctor_pacient WHERE doctor_id = ? AND pacient_id = (SELECT id FROM pacienti WHERE CNP = ?)";
    $stmtDoctorPatient = $conn->prepare($sqlDoctorPatient);
    $stmtDoctorPatient->bind_param("is", $doctor_id, $cnp);
    $stmtDoctorPatient->execute();
    $stmtDoctorPatient->store_result();
    $is_my_patient = $stmtDoctorPatient->num_rows > 0;
    $stmtDoctorPatient->close();

    // Verifică dacă medicul are acces la pacient
    if (!$is_my_patient) {
        echo json_encode(['success' => false, 'message' => 'Nu aveți acces la acest pacient']);
        exit();
    }

    // Actualizează câmpul specificat pentru pacient
    $sql = "UPDATE pacienti SET $field = ? WHERE CNP = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $value, $cnp);
    
    // Returnează rezultatul operației
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Eroare la actualizare']);
    }

    $stmt->close();
    $conn->close();
?> 