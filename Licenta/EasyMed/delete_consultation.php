<?php
/**
 * Script pentru ștergerea consultațiilor medicale
 * 
 * Acest script permite medicilor să șteargă consultațiile lor din sistem.
 * Include ștergerea în cascadă a tuturor datelor asociate (bilete, investigații, rețete).
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */
    session_start();
    require_once 'db_connection.php';

    // Verifică dacă utilizatorul este autentificat și ID-ul consultației este furnizat
    if (!isset($_SESSION['user_id']) || !isset($_POST['consultation_id']))
    {
        echo json_encode(['success' => false, 'message' => 'Acces neautorizat']);
        exit();
    }

    // Extrage ID-ul consultației din request
    $consultation_id = $_POST['consultation_id'];

    try
    {
        // Începe o tranzacție pentru a asigura consistența datelor
        $conn->begin_transaction();

        // Obține ID-ul medicului din sesiune
        $stmt = $conn->prepare("SELECT id FROM medici WHERE utilizator_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $doctor = $result->fetch_assoc();
        $id_medic = $doctor['id'];
        $stmt->close();

        // Verifică dacă consultația există și aparține medicului
        $stmt = $conn->prepare("SELECT id FROM consultatii WHERE ID = ? AND id_medic = ?");
        $stmt->bind_param("ii", $consultation_id, $id_medic);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0)
        {
            throw new Exception('Consultația nu a fost găsită sau nu aveți permisiunea de a o șterge.');
        }
        $stmt->close();

        // Obține codurile biletelor de investigație asociate consultației
        $stmt = $conn->prepare("SELECT CodBilet FROM bilete_investigatii WHERE Consultatie = ?");
        $stmt->bind_param("i", $consultation_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $referralCodes = [];
        while ($row = $result->fetch_assoc())
        {
            $referralCodes[] = $row['CodBilet'];
        }
        $stmt->close();

        // Șterge biletele de trimitere asociate consultației
        $stmt = $conn->prepare("DELETE FROM bilete_trimitere WHERE Consultatie = ?");
        $stmt->bind_param("i", $consultation_id);
        $stmt->execute();
        $stmt->close();

        // Șterge biletele de investigație asociate consultației
        $stmt = $conn->prepare("DELETE FROM bilete_investigatii WHERE Consultatie = ?");
        $stmt->bind_param("i", $consultation_id);
        $stmt->execute();
        $stmt->close();

        // Șterge investigațiile asociate codurilor de bilet
        if (!empty($referralCodes))
        {
            $placeholders = str_repeat('?,', count($referralCodes) - 1) . '?';
            $stmt = $conn->prepare("DELETE FROM investigatii WHERE CodBilet IN ($placeholders)");
            $stmt->bind_param(str_repeat('s', count($referralCodes)), ...$referralCodes);
            $stmt->execute();
            $stmt->close();
        }

        // Șterge rețetele medicale asociate consultației
        $stmt = $conn->prepare("DELETE FROM retete_medicale WHERE Consultatie = ?");
        $stmt->bind_param("i", $consultation_id);
        $stmt->execute();
        $stmt->close();

        // Șterge consultația principală
        $stmt = $conn->prepare("DELETE FROM consultatii WHERE ID = ?");
        $stmt->bind_param("i", $consultation_id);
        $stmt->execute();
        $stmt->close();

        // Confirmă tranzacția
        $conn->commit();
        echo json_encode(['success' => true]);
    }
    catch (Exception $e)
    {
        // Anulează tranzacția în caz de eroare
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $conn->close();
?> 