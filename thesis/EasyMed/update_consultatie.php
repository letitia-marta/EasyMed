<?php
/**
 * Script pentru actualizarea consultațiilor medicale
 * 
 * Acest script permite medicilor să actualizeze informațiile consultațiilor lor:
 * - Verifică autentificarea medicului
 * - Validează că consultația aparține medicului
 * - Actualizează câmpurile permise (data, ora, simptome, diagnostic)
 * - Validează formatul orei pentru câmpul 'ora'
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
    if (!isset($_POST['field']) || !isset($_POST['value']) || !isset($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'Parametri lipsă']);
        exit();
    }

    // Extrage datele din request
    $field = $_POST['field'];
    $value = $_POST['value'];
    $consultatie_id = $_POST['id'];

    // Definește câmpurile permise pentru actualizare
    $allowed_fields = ['data', 'ora', 'simptome', 'diagnostic'];
    if (!in_array($field, $allowed_fields)) {
        echo json_encode(['success' => false, 'message' => 'Câmp invalid']);
        exit();
    }

    // Validează formatul orei pentru câmpul 'ora'
    if ($field === 'ora') {
        $time = DateTime::createFromFormat('H:i', $value);
        if ($time) {
            $value = $time->format('H:i:s');
        } else {
            echo json_encode(['success' => false, 'message' => 'Format oră invalid']);
            exit();
        }
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

    // Verifică dacă consultația aparține medicului
    $sqlConsultation = "SELECT 1 FROM consultatii WHERE ID = ? AND id_medic = ?";
    $stmtConsultation = $conn->prepare($sqlConsultation);
    $stmtConsultation->bind_param("ii", $consultatie_id, $doctor_id);
    $stmtConsultation->execute();
    $stmtConsultation->store_result();
    $is_my_consultation = $stmtConsultation->num_rows > 0;
    $stmtConsultation->close();

    // Verifică dacă medicul are acces la consultație
    if (!$is_my_consultation) {
        echo json_encode(['success' => false, 'message' => 'Nu aveți acces la această consultație']);
        exit();
    }

    // Actualizează câmpul specificat pentru consultație
    $sql = "UPDATE consultatii SET $field = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $value, $consultatie_id);
    
    // Returnează rezultatul operației
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Eroare la actualizare']);
    }

    $stmt->close();
    $conn->close();
?> 