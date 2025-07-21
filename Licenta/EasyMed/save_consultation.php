<?php
/**
 * Script pentru salvarea consultațiilor medicale
 * 
 * Acest script gestionează salvarea completă a consultațiilor medicale:
 * - Validează autentificarea medicului
 * - Procesează datele consultației (data, ora, simptome, diagnostic)
 * - Salvează biletele de trimitere către specialiști
 * - Salvează biletele pentru investigații medicale
 * - Salvează rețetele medicale și medicamentele
 * - Folosește tranzacții pentru a asigura consistența datelor
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */
session_start();
require_once 'db_connection.php';

    // Verifică dacă utilizatorul este autentificat și există date POST
    if (!isset($_SESSION['user_id']) || empty($_POST))
    {
    header("Location: mediciLogin.php");
    exit();
}

// Obține ID-ul medicului din sesiune
$stmt = $conn->prepare("SELECT id FROM medici WHERE utilizator_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();
    $id_medic = $doctor['id'];
$stmt->close();

    try
    {
    // Începe o tranzacție pentru a asigura consistența datelor
    $conn->begin_transaction();

        // Procesează și validează data și ora consultației
        $inputDate = $_POST['Data'];
        $inputTime = $_POST['Ora'];
        
        error_log("Input date: " . $inputDate . " (Type: " . gettype($inputDate) . ")");
        error_log("Input time: " . $inputTime . " (Type: " . gettype($inputTime) . ")");
        
        // Validează formatul datei (dd-mm-yyyy)
        if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $inputDate)) {
            throw new Exception("Invalid date format. Expected dd-mm-yyyy, got: " . $inputDate);
        }
        
        // Validează formatul orei (HH:MM sau HH:MM:SS)
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $inputTime)) {
            throw new Exception("Invalid time format. Expected HH:MM or HH:MM:SS, got: " . $inputTime);
        }
        
        // Adaugă secunde dacă lipsesc
        if (strlen($inputTime) === 5) {
            $inputTime .= ':00';
        }
        
        error_log("Time after formatting: " . $inputTime);
        
        // Parsează și validează componentele datei
        $dateParts = explode('-', $inputDate);
        if (count($dateParts) !== 3) {
            throw new Exception("Invalid date format. Expected dd-mm-yyyy, got: " . $inputDate);
        }
        
        $day = $dateParts[0];
        $month = $dateParts[1];
        $year = $dateParts[2];
        
        // Verifică dacă data este validă
        if (!checkdate((int)$month, (int)$day, (int)$year)) {
            throw new Exception("Invalid date: " . $inputDate);
        }
        
        // Formatează data pentru baza de date (YYYY-MM-DD)
        $formattedDate = $year . '-' . $month . '-' . $day;
        $formattedTime = $inputTime;
        
        $dateTimeString = $formattedDate . ' ' . $formattedTime;
        error_log("Final date/time string: " . $dateTimeString);
        
        // Validează combinația dată/oră
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString);
        if (!$dateTime) {
            throw new Exception("Invalid date/time combination after conversion: " . $formattedDate . " " . $formattedTime);
        }
        
        error_log("Formatted date: " . $formattedDate);
        error_log("Formatted time: " . $formattedTime);

        // Salvează consultația principală
        $stmt = $conn->prepare("INSERT INTO consultatii (CNPPacient, Data, Ora, Simptome, Diagnostic, id_medic) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $_POST['CNPPacient'], $formattedDate, $formattedTime, $_POST['Simptome'], $_POST['diagnostic'], $id_medic);
    $stmt->execute();
    $consultatie_id = $conn->insert_id;
    $stmt->close();

        // Salvează biletele de trimitere către specialiști
        if (!empty($_POST['trimitere_specializare']))
        {
            $stmt = $conn->prepare("INSERT INTO bilete_trimitere (Consultatie, Cod, Specializare, Data) VALUES (?, ?, ?, ?)");
            foreach ($_POST['trimitere_specializare'] as $key => $specializare)
            {
                $codTrimitere = $_POST['trimitere_cod'][$key];
                if (empty($codTrimitere))
                    continue;
                $stmt->bind_param("isss", $consultatie_id, $codTrimitere, $specializare, $formattedDate);
            $stmt->execute();
        }
        $stmt->close();
    }

        // Salvează biletele pentru investigații medicale
        if (!empty($_POST['investigatie_cod']))
        {
            $stmtInvestigatii = $conn->prepare("INSERT INTO investigatii (CodBilet, investigatie) VALUES (?, ?)");
            $stmtBilet = $conn->prepare("INSERT INTO bilete_investigatii (CodBilet, Data, Consultatie) VALUES (?, ?, ?)");
            
            foreach ($_POST as $key => $value)
            {
                if (strpos($key, 'investigatie_cod_') === 0)
                {
                    $groupId = substr($key, strlen('investigatie_cod_'));
                    $codBilet = $value;
                    if (empty($codBilet)) continue;
                    
                    // Salvează investigațiile asociate codului de bilet
                    if (isset($_POST['investigatie_display'][$groupId]))
                    {
                        foreach ($_POST['investigatie_display'][$groupId] as $investigatieId)
                        {
                            if (!empty($investigatieId))
                            {
                                $stmtInvestigatii->bind_param("si", $codBilet, $investigatieId);
                                $stmtInvestigatii->execute();
                            }
                        }
                    }
                    
                    // Salvează biletul de investigație
                    $stmtBilet->bind_param("ssi", $codBilet, $formattedDate, $consultatie_id);
                    $stmtBilet->execute();
                }
            }
            $stmtInvestigatii->close();
            $stmtBilet->close();
        }

        // Salvează rețetele medicale și medicamentele
        if (!empty($_POST['reteta_cod']))
        {
            $stmtReteta = $conn->prepare("INSERT INTO retete_medicale (Cod, Consultatie, Data) VALUES (?, ?, ?)");
            $stmtMedicament = $conn->prepare("INSERT INTO medicamente (CodReteta, Medicamente, FormaFarmaceutica, Cantitate, Durata) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($_POST['reteta_cod'] as $index => $codReteta)
            {
                if (empty($codReteta))
                    continue;
                
                error_log("Cod reteta: " . $codReteta . " (Type: " . gettype($codReteta) . ")");
                
                // Salvează rețeta principală
                $stmtReteta->bind_param("sis", $codReteta, $consultatie_id, $formattedDate);
                if (!$stmtReteta->execute()) {
                    error_log("Nu s-a putut salva reteta: " . $stmtReteta->error);
                    throw new Exception("Error saving prescription: " . $stmtReteta->error);
                }
                
                // Salvează medicamentele din rețetă
                if (isset($_POST['medicament'][$index]))
                {
                    foreach ($_POST['medicament'][$index] as $medIndex => $medicament) {
                        if (!empty($medicament))
                        {
                            $stmtMedicament->bind_param("sssss", 
                                $codReteta,
                                $medicament,
                                $_POST['forma_farmaceutica'][$index][$medIndex],
                                $_POST['cantitate'][$index][$medIndex],
                                $_POST['durata'][$index][$medIndex]
                            );
                            if (!$stmtMedicament->execute())
                            {
                                error_log("Nu s-au putut salva medicamentele: " . $stmtMedicament->error);
                                throw new Exception("Error saving medication: " . $stmtMedicament->error);
                            }
                        }
                    }
                }
            }
            $stmtReteta->close();
            $stmtMedicament->close();
        }

        // Confirmă tranzacția și redirecționează
        $conn->commit();
        header("Location: registru.php");
        exit();
    }
    catch (Exception $e)
    {
    // Anulează tranzacția în caz de eroare
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>