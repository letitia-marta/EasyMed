<?php
/**
 * Pagină pentru afișarea și editarea detaliilor consultațiilor medicale
 * 
 * Această pagină oferă o interfață completă pentru vizualizarea și editarea consultațiilor:
 * - Afișează informațiile complete ale consultației (medic, pacient, diagnostic)
 * - Permite editarea câmpurilor pentru medici (data, ora, simptome, diagnostic)
 * - Afișează biletele de trimitere către specialiști
 * - Afișează biletele pentru investigații medicale
 * - Afișează rețetele medicale și medicamentele
 * - Implementează autorizare bazată pe rol (medic/pacient)
 * - Include funcționalități de salvare și validare
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

    include('db_connection.php');
    session_start();

    // Verifică dacă utilizatorul este autentificat
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // Verifică dacă s-a furnizat ID-ul consultației
    if (!isset($_GET['id'])) {
        header("Location: listaPacienti.php");
        exit();
    }

    // Extrage ID-ul consultației și utilizatorului
    $consultatie_id = $_GET['id'];
    $utilizator_id = $_SESSION['user_id'];

    // Verifică dacă utilizatorul este medic
    $sqlDoctor = "SELECT id FROM medici WHERE utilizator_id = ?";
    $stmtDoctor = $conn->prepare($sqlDoctor);
    $stmtDoctor->bind_param("i", $utilizator_id);
    $stmtDoctor->execute();
    $resultDoctor = $stmtDoctor->get_result();
    $isDoctor = $resultDoctor->num_rows > 0;
    $doctor_id = $isDoctor ? $resultDoctor->fetch_assoc()['id'] : null;
    $stmtDoctor->close();

    // Verifică dacă utilizatorul este pacient
    $sqlPatient = "SELECT CNP FROM pacienti WHERE utilizator_id = ?";
    $stmtPatient = $conn->prepare($sqlPatient);
    $stmtPatient->bind_param("i", $utilizator_id);
    $stmtPatient->execute();
    $resultPatient = $stmtPatient->get_result();
    $isPatient = $resultPatient->num_rows > 0;
    $patient_cnp = $isPatient ? $resultPatient->fetch_assoc()['CNP'] : null;
    $stmtPatient->close();

    // Interogare principală pentru obținerea datelor consultației
    $sqlConsultatie = "SELECT c.*, m.nume as nume_doctor, m.prenume as prenume_doctor, m.specializare, m.cod_parafa,
                      p.nume as nume_pacient, p.prenume as prenume_pacient, p.CNP as CNP_pacient,
                      cb.cod_999 as cod_diagnostic, cb.denumire_boala as nume_diagnostic,
                      GROUP_CONCAT(DISTINCT bt.Cod SEPARATOR ', ') as coduri_bilet_trimitere,
                      GROUP_CONCAT(DISTINCT bi.CodBilet SEPARATOR ', ') as coduri_bilet_investigatii,
                      GROUP_CONCAT(DISTINCT rm.Cod SEPARATOR ', ') as coduri_reteta_medicala
                      FROM consultatii c
                      INNER JOIN medici m ON c.id_medic = m.id
                      INNER JOIN pacienti p ON c.CNPPacient = p.CNP
                      LEFT JOIN coduri_boli cb ON c.Diagnostic = cb.cod_999
                      LEFT JOIN bilete_trimitere bt ON bt.Consultatie = c.ID
                      LEFT JOIN bilete_investigatii bi ON bi.Consultatie = c.ID
                      LEFT JOIN retete_medicale rm ON rm.Consultatie = c.ID
                      WHERE c.ID = ? " . 
                      ($isDoctor ? "AND c.id_medic = ?" : "AND c.CNPPacient = ?");
    $stmtConsultatie = $conn->prepare($sqlConsultatie);
    
    // Setează parametrii în funcție de rolul utilizatorului
    $access_id = $isDoctor ? $doctor_id : $patient_cnp;
    $stmtConsultatie->bind_param("ii", $consultatie_id, $access_id);
    $stmtConsultatie->execute();
    $resultConsultatie = $stmtConsultatie->get_result();

    // Verifică dacă consultația există și utilizatorul are acces
    if ($resultConsultatie->num_rows === 0) {
        header("Location: " . ($isDoctor ? "listaPacienti.php" : "istoricPacient.php"));
        exit();
    }

    $consultatie = $resultConsultatie->fetch_assoc();

    // Obține biletele de trimitere asociate consultației
    $sqlBileteTrimitere = "SELECT * FROM bilete_trimitere WHERE Consultatie = ?";
    $stmtBileteTrimitere = $conn->prepare($sqlBileteTrimitere);
    $stmtBileteTrimitere->bind_param("i", $consultatie_id);
    $stmtBileteTrimitere->execute();
    $resultBileteTrimitere = $stmtBileteTrimitere->get_result();
    $bileteTrimitere = $resultBileteTrimitere->fetch_all(MYSQLI_ASSOC);

    // Obține biletele pentru investigații asociate consultației
    $sqlBileteInvestigatii = "SELECT bi.CodBilet, GROUP_CONCAT(CONCAT(ca.cod, ' - ', ca.denumire) ORDER BY ca.denumire SEPARATOR '||') as nume_investigatii
                             FROM bilete_investigatii bi
                             LEFT JOIN investigatii i ON bi.CodBilet = i.CodBilet
                             LEFT JOIN coduri_analize ca ON i.investigatie = ca.id
                             WHERE bi.Consultatie = ?
                             GROUP BY bi.CodBilet";
    $stmtBileteInvestigatii = $conn->prepare($sqlBileteInvestigatii);
    $stmtBileteInvestigatii->bind_param("i", $consultatie_id);
    $stmtBileteInvestigatii->execute();
    $resultBileteInvestigatii = $stmtBileteInvestigatii->get_result();
    $bileteInvestigatii = $resultBileteInvestigatii->fetch_all(MYSQLI_ASSOC);

    // Obține rețetele medicale și medicamentele asociate consultației
    $sqlRetete = "SELECT rm.*, m.Medicamente, m.FormaFarmaceutica, m.Cantitate, m.Durata
                  FROM retete_medicale rm
                  LEFT JOIN medicamente m ON rm.Cod = m.CodReteta
                  WHERE rm.Consultatie = ?";
    $stmtRetete = $conn->prepare($sqlRetete);
    $stmtRetete->bind_param("i", $consultatie_id);
    $stmtRetete->execute();
    $resultRetete = $stmtRetete->get_result();
    $retete = $resultRetete->fetch_all(MYSQLI_ASSOC);

    // Obține lista de diagnostice disponibile pentru dropdown
    $sqlDiagnostice = "SELECT cod_999, denumire_boala FROM coduri_boli ORDER BY denumire_boala";
    $stmtDiagnostice = $conn->prepare($sqlDiagnostice);
    $stmtDiagnostice->execute();
    $resultDiagnostice = $stmtDiagnostice->get_result();
    $diagnostice = $resultDiagnostice->fetch_all(MYSQLI_ASSOC);
    $stmtDiagnostice->close();
?>

<!DOCTYPE html>
<html lang="ro">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Detalii Consultație - EasyMed</title>
        <link rel="stylesheet" href="style.css">
        <style>
            /* Container-ul principal pentru detalii */
            .details-container {
                max-width: 1400px;
                width: 1400px;
                margin: 2rem auto;
                padding: 2rem;
                background: #13181d;
                border-radius: 10px;
                color: white;
            }

            /* Secțiunea cu informațiile consultației */
            .consultation-info {
                background: #2A363F;
                padding: 2rem;
                border-radius: 8px;
                margin-bottom: 2rem;
                width: 100%;
            }

            .consultation-info h2 {
                color: #5cf9c8;
                margin-top: 0;
                margin-bottom: 1.5rem;
            }

            /* Secțiuni pentru diferite tipuri de detalii */
            .details-section {
                margin-top: 2rem;
                padding-top: 1rem;
                border-top: 1px solid #3A4A5F;
                width: 100%;
            }

            .details-section h3 {
                color: #5cf9c8;
                margin-bottom: 1rem;
            }

            /* Grid pentru afișarea informațiilor */
            .info-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1.5rem;
                width: 100%;
            }

            /* Element individual pentru informații */
            .info-item {
                margin-bottom: 1rem;
                min-width: 250px;
            }

            /* Eticheta pentru câmpurile de informații */
            .info-label {
                color: #888;
                font-size: 0.9rem;
                margin-bottom: 0.3rem;
            }

            /* Valoarea pentru câmpurile de informații */
            .info-value {
                color: white;
                font-weight: 500;
                font-size: 1.1rem;
            }

            /* Câmpuri editabile pentru medici */
            .editable-field {
                width: 100%;
                padding: 0.5rem;
                border: 1px solid #2A363F;
                border-radius: 4px;
                background: #13181d;
                color: white;
                font-size: 1.1rem;
                font-weight: 500;
                transition: border-color 0.3s;
            }

            .editable-field:focus {
                border-color: #5cf9c8;
                outline: none;
            }

            .editable-field:disabled {
                border: none;
                background: transparent;
                padding: 0;
            }

            /* Butonul pentru salvare */
            .save-all-btn {
                padding: 0.8rem 1.5rem;
                background: #5cf9c8;
                color: #13181d;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 500;
                transition: background-color 0.3s;
            }

            .save-all-btn:hover {
                background: #4ad7a8;
            }

            .save-all-btn.show {
                display: inline-block;
            }

            .card {
                background: #1a2228;
                border-radius: 8px;
                margin-bottom: 1rem;
                overflow: hidden;
                width: 100%;
            }

            .card-header {
                background: #2A363F;
                padding: 1rem;
                border-bottom: 1px solid #3A4A5F;
            }

            .card-header h4 {
                color: #5cf9c8;
                margin: 0;
                font-size: 1.1rem;
            }

            .card-content {
                padding: 1rem;
            }

            .back-btn {
                display: inline-block;
                padding: 0.8rem 1.5rem;
                background: #2A363F;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                margin-bottom: 1rem;
                transition: background-color 0.3s;
            }

            .back-btn:hover {
                background: #3A4A5F;
            }
        
            .profile-dropdown {
                position: relative;
                display: inline-block;
            }

            .dropdown-menu {
                display: none;
                position: absolute;
                right: 0;
                top: 100%;
                background-color: #13181d;
                min-width: 160px;
                box-shadow: 0 8px 16px rgba(0,0,0,0.2);
                z-index: 1000;
                border-radius: 8px;
                margin-top: 10px;
            }

            .dropdown-menu.show {
                display: block;
            }

            .dropdown-item {
                color: white;
                padding: 12px 16px;
                text-decoration: none;
                display: block;
                transition: background-color 0.3s;
            }

            .dropdown-item:hover {
                background-color: #2A363F;
            }

            .dropdown-item:first-child {
                border-radius: 8px 8px 0 0;
            }

            .dropdown-item:last-child {
                border-radius: 0 0 8px 8px;
            }

            input[type="time"]::-webkit-calendar-picker-indicator {
                filter: invert(1);
            }
            
            input[type="time"] {
                -moz-appearance: textfield;
            }
            
            input[type="time"]::-webkit-inner-spin-button,
            input[type="time"]::-webkit-outer-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            .custom-time-input {
                position: relative;
            }

            .time-picker {
                position: absolute;
                top: 100%;
                left: 0;
                background: #2A363F;
                border: 1px solid #3A4A5F;
                border-radius: 4px;
                padding: 10px;
                z-index: 1000;
                display: flex;
                gap: 10px;
                margin-top: 5px;
            }

            .time-picker-hours,
            .time-picker-minutes {
                max-height: 200px;
                overflow-y: auto;
                background: #13181d;
                border-radius: 4px;
                padding: 5px;
            }

            .time-option {
                padding: 5px 10px;
                cursor: pointer;
                color: white;
                border-radius: 4px;
            }

            .time-option:hover {
                background: #3A4A5F;
            }

            .time-option.selected {
                background: #5cf9c8;
                color: #13181d;
            }

            .time-picker-hours::-webkit-scrollbar,
            .time-picker-minutes::-webkit-scrollbar {
                width: 0px;
                background: transparent;
            }

            select.editable-field {
                width: 100%;
                padding: 0.5rem;
                border: 1px solid #2A363F;
                border-radius: 4px;
                background: #13181d;
                color: white;
                font-size: 1.1rem;
                font-weight: 500;
                transition: border-color 0.3s;
                cursor: pointer;
            }

            select.editable-field:focus {
                border-color: #5cf9c8;
                outline: none;
            }

            select.editable-field option {
                background: #13181d;
                color: white;
                padding: 0.5rem;
            }

            select.editable-field option:hover {
                background: #2A363F;
            }

            .modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
            }

            .modal-content {
                background-color: #13181d;
                margin: 5% auto;
                padding: 20px;
                border-radius: 8px;
                position: relative;
            }

            .patient-search-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1rem;
            }

            .patient-search-header h2 {
                color: #5cf9c8;
                margin: 0;
            }

            .close-modal {
                color: white;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
            }

            .patient-search-box {
                margin-bottom: 1rem;
            }

            .patient-search-box input {
                width: 100%;
                padding: 0.5rem;
                background: #2A363F;
                color: white;
                border: 1px solid #2A363F;
                border-radius: 4px;
            }

            .diagnostic-category {
                margin-bottom: 1rem;
                border: 1px solid #2A363F;
                border-radius: 4px;
            }

            .category-header {
                background: #2A363F;
                padding: 0.5rem 1rem;
                cursor: pointer;
                display: flex;
                justify-content: space-between;
                align-items: center;
                transition: background-color 0.3s;
            }

            .category-header:hover {
                background: #3A4A5F;
            }

            .category-content {
                padding: 1rem;
                background: #13181d;
            }

            .category-search-box {
                display: flex;
                gap: 0.5rem;
                margin-bottom: 1rem;
            }

            .category-search-input {
                flex: 1;
                padding: 0.5rem;
                background: #2A363F;
                color: white;
                border: 1px solid #2A363F;
                border-radius: 4px;
            }

            .category-search-btn {
                padding: 0.5rem 1rem;
                background: #5cf9c8;
                color: #13181d;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .category-search-btn:hover {
                background: #4ad7a8;
            }

            .add-item-btn {
                margin-top: 0.5rem;
                padding: 0.5rem 1rem;
                background: #5cf9c8;
                color: #13181d;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                width: 100%;
            }

            .add-item-btn:hover {
                background: #4ad8b7;
            }

            .patients-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 1rem;
            }

            .patients-table th,
            .patients-table td {
                padding: 0.75rem;
                text-align: left;
                border-bottom: 1px solid #2A363F;
            }

            .patients-table th {
                background: #2A363F;
                position: sticky;
                top: 0;
                z-index: 1;
            }

            .patients-table tr:hover {
                background: #1a2128;
                cursor: pointer;
            }

            .view-details-btn {
                padding: 0.5rem 1rem;
                background: #5cf9c8;
                color: #13181d;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .view-details-btn:hover {
                background: #4ad7a8;
            }

            .toggle-icon {
                transition: transform 0.3s;
            }

            .category-header.collapsed .toggle-icon {
                transform: rotate(-90deg);
            }
        </style>
    </head>

    <body>
        <section class="navigation">
            <a href="index.php">
                <img src="images/logo.png" width="70" alt="">
            </a>
            <h1><a href="index.php">EasyMed</a></h1>
            <nav aria-label="Main Navigation">
                <ul class="menu-opened test">
                    <li><a href="dashboardMedici.php">Acasă</a></li>
                    <li class="profile-link" style="position: relative;">
                        <div class="profile-dropdown">
                            <img src="images/user.png" width="70" alt="Profil" id="profileIcon" style="cursor: pointer;">
                            <div class="dropdown-menu">
                                <a href="profile.php" class="dropdown-item">
                                    Profil
                                </a>
                                <a href="logout.php" class="dropdown-item">
                                    Deconectare
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>
            </nav>
        </section>

        <div class="content">
            <div class="details-container">
                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <?php echo '<a href="detaliiPacient.php?cnp=' . $consultatie['CNPPacient'] . '" class="back-btn">Detalii pacient</a>'; ?>
                    <a href="registru.php" class="back-btn">Registru consultații</a>
                </div>

                <div class="consultation-info">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2>Detalii Consultație</h2>
                        <button id="save-all-btn" class="save-all-btn" onclick="saveAllChanges(event)" style="display: none;">
                            Salvează modificările
                        </button>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">ID Consultație</div>
                            <div class="info-value"><?php echo htmlspecialchars($consultatie['ID']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Data</div>
                            <?php if ($isDoctor): ?>
                                <input type="date" class="editable-field" id="data" name="data" value="<?php echo date('Y-m-d', strtotime($consultatie['Data'])); ?>" onchange="fieldChanged()" max="<?php echo date('Y-m-d'); ?>">
                            <?php else: ?>
                            <div class="info-value"><?php echo date('d.m.Y', strtotime($consultatie['Data'])); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Ora</div>
                            <?php if ($isDoctor): ?>
                                <div class="custom-time-input">
                                    <input type="text" class="editable-field" id="ora" name="ora" value="<?php echo date('H:i', strtotime($consultatie['Ora'])); ?>" pattern="[0-9]{2}:[0-9]{2}" onchange="fieldChanged()" style="font-family: monospace;" placeholder="HH:MM">
                                    <div class="time-picker" id="timePicker" style="display: none;">
                                        <div class="time-picker-hours">
                                            <?php for($i = 0; $i < 24; $i++): ?>
                                                <div class="time-option" data-hour="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></div>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="time-picker-minutes">
                                            <?php for($i = 0; $i < 60; $i++): ?>
                                                <div class="time-option" data-minute="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></div>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                            <div class="info-value"><?php echo date('H:i', strtotime($consultatie['Ora'])); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Pacient</div>
                            <div class="info-value"><?php echo htmlspecialchars($consultatie['nume_pacient'] . ' ' . $consultatie['prenume_pacient']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">CNP Pacient</div>
                            <div class="info-value"><?php echo htmlspecialchars($consultatie['CNP_pacient']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Medic</div>
                            <div class="info-value"><?php echo htmlspecialchars($consultatie['nume_doctor'] . ' ' . $consultatie['prenume_doctor']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Specializare</div>
                            <div class="info-value"><?php echo htmlspecialchars($consultatie['specializare']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Cod Parafă</div>
                            <div class="info-value"><?php echo htmlspecialchars($consultatie['cod_parafa']); ?></div>
                        </div>
                    </div>

                    <div class="details-section" style="margin-top: 2rem;">
                        <h3>Simptome</h3>
                        <?php if ($isDoctor): ?>
                            <textarea class="editable-field" id="simptome" name="simptome" rows="4" onchange="fieldChanged()"><?php echo htmlspecialchars($consultatie['Simptome'] ?? ''); ?></textarea>
                        <?php else: ?>
                        <div class="info-value"><?php echo nl2br(htmlspecialchars($consultatie['Simptome'] ?? 'Nu există simptome înregistrate')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="details-section">
                        <h3>Diagnostic</h3>
                        <?php if ($isDoctor): ?>
                            <input type="hidden" name="diagnostic" id="selectedDiagnosticCode" value="<?php echo htmlspecialchars($consultatie['Diagnostic']); ?>">
                            <input type="text" 
                                   id="selectedDiagnosticDisplay" 
                                   class="editable-field"
                                   readonly 
                                   value="<?php echo htmlspecialchars($consultatie['cod_diagnostic'] . ' - ' . $consultatie['nume_diagnostic']); ?>"
                                   placeholder="Selectează diagnosticul"
                                   onclick="openDiagnosticSearch()"
                                   style="cursor: pointer;">
                        <?php else: ?>
                        <div class="info-value">
                            <?php 
                            if ($consultatie['cod_diagnostic']) {
                                echo htmlspecialchars($consultatie['cod_diagnostic'] . ' - ' . $consultatie['nume_diagnostic']);
                            } else {
                                echo htmlspecialchars($consultatie['Diagnostic']);
                            }
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($bileteTrimitere)): ?>
                    <div class="details-section">
                        <h3>Bilete Trimitere</h3>
                        <?php foreach ($bileteTrimitere as $bilet): ?>
                            <div class="card">
                                <div class="card-header">
                                    <h4>Bilet Trimitere #<?php echo htmlspecialchars($bilet['Cod']); ?></h4>
                                </div>
                                <div class="card-content">
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <div class="info-label">Specializare</div>
                                            <div class="info-value"><?php echo htmlspecialchars($bilet['Specializare']); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($bileteInvestigatii)): ?>
                    <div class="details-section">
                        <h3>Bilete Investigații</h3>
                        <?php foreach ($bileteInvestigatii as $bilet): ?>
                            <div class="card">
                                <div class="card-header">
                                    <h4>Bilet Investigații #<?php echo htmlspecialchars($bilet['CodBilet']); ?></h4>
                                </div>
                                <div class="card-content">
                                    <div class="info-grid">
                                        <div class="info-item" style="width: 100%;">
                                            <div class="info-label">Investigații</div>
                                            <table style="width: 100%; border-collapse: collapse; margin-top: 0.5rem;">
                                                <tbody>
                                                    <?php
                                                    $investigatii = explode('||', $bilet['nume_investigatii']);
                                                    foreach ($investigatii as $investigatie) {
                                                        echo '<tr>';
                                                        echo '<td style="padding: 0.5rem; border-bottom: 1px solid #3A4A5F;">' . htmlspecialchars($investigatie) . '</td>';
                                                        echo '</tr>';
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($retete)): ?>
                    <div class="details-section">
                        <h3>Rețete Medicale</h3>
                        <?php foreach ($retete as $reteta): ?>
                            <div class="card">
                                <div class="card-header">
                                    <h4>Rețetă Medicală #<?php echo htmlspecialchars($reteta['Cod']); ?></h4>
                                </div>
                                <div class="card-content">
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <div class="info-label">Medicament</div>
                                            <div class="info-value"><?php echo htmlspecialchars($reteta['Medicamente']); ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Formă farmaceutică</div>
                                            <div class="info-value"><?php echo htmlspecialchars($reteta['FormaFarmaceutica']); ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Cantitate</div>
                                            <div class="info-value"><?php echo htmlspecialchars($reteta['Cantitate']); ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Durata tratamentului</div>
                                            <div class="info-value"><?php echo htmlspecialchars($reteta['Durata']); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Diagnostic Search Modal -->
        <div id="diagnosticSearchModal" class="modal">
            <div class="modal-content" style="max-width: 1000px;">
                <div class="patient-search-header">
                    <h2>Selectează Diagnostic</h2>
                    <span class="close-modal" onclick="closeDiagnosticSearch()">&times;</span>
                </div>

                <div class="patient-search-box">
                    <input type="text" 
                           id="diagnosticSearchInput" 
                           placeholder="Caută după cod sau diagnostic..." 
                           oninput="filterDiagnostics()">
                </div>

                <div style="max-height: 600px; overflow-y: auto; margin-top: 1rem;">
                    <?php
                    $categories = [
                        ['name' => 'Bolile infectioase si parazitare', 'range' => '1-79'],
                        ['name' => 'Tumori', 'range' => '80-202'],
                        ['name' => 'Bolile sangelui, ale organelor hematopoietice si unele tulburari ale mecanismului imunitar', 'range' => '203-233'],
                        ['name' => 'Boli endocrine, de nutritie si metabolism', 'range' => '234-298'],
                        ['name' => 'Tulburari mentale si de comportament', 'range' => '299-355'],
                        ['name' => 'Bolile sistemului nervos', 'range' => '356-397'],
                        ['name' => 'Bolile ochiului si anexelor sale', 'range' => '398-427'],
                        ['name' => 'Bolile urechii si apofizei mastoide', 'range' => '428-444'],
                        ['name' => 'Bolile aparatului circulator', 'range' => '445-497'],
                        ['name' => 'Bolile aparatului respirator', 'range' => '498-542'],
                        ['name' => 'Bolile aparatului digestiv', 'range' => '543-591'],
                        ['name' => 'Bolile pielii si testutului celular subcutanat', 'range' => '592-625'],
                        ['name' => 'Bolile sistemului osteoarticular, ale muschilor si tesutului conjunctiv', 'range' => '626-669'],
                        ['name' => 'Bolile aparatului genitourinar', 'range' => '670-732'],
                        ['name' => 'Sarcina, nasterea si lauzia', 'range' => '733-777'],
                        ['name' => 'Unele afectiuni ale caror origine se situeaza in perioada perinatala', 'range' => '778-820'],
                        ['name' => 'Malformatii congenitale, deformatii si anomalii cromozomiale', 'range' => '821-868'],
                        ['name' => 'Simtome si rezultate anormale ale investigatiilor clinice si de laborator, neclasate la alte locuri', 'range' => '869-878'],
                        ['name' => 'Leziuni traumatice, otraviri si alte consecinte ale cauzelor externe', 'range' => '879-975'],
                        ['name' => 'Cauze externe ale morbiditatii si mortalitatii', 'range' => '976-992'],
                        ['name' => 'Factorii influentand starea de sanatate si contactul cu serviciile de sanatate', 'range' => '993-999']
                    ];

                    foreach ($categories as $index => $category):
                        $range = explode('-', $category['range']);
                        $min = (int)$range[0];
                        $max = (int)$range[1];
                    ?>
                    <div class="diagnostic-category">
                        <div class="category-header" onclick="toggleCategory(<?php echo $index; ?>)">
                            <span class="category-title"><?php echo ($index + 1) . '. ' . $category['name'] . ' (' . $category['range'] . ')'; ?></span>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="category-content" id="category-<?php echo $index; ?>" style="display: none;">
                            <div class="category-search-box">
                                <input type="text" 
                                    id="categorySearch-<?php echo $index; ?>" 
                                    placeholder="Caută în această categorie..." 
                                    class="category-search-input">
                                <button type="button" 
                                        class="category-search-btn" 
                                        onclick="filterCategory(<?php echo $index; ?>)">
                                    Caută
                                </button>
                            </div>
                            <table class="patients-table">
                                <thead>
                                    <tr>
                                        <th>Cod</th>
                                        <th>Diagnostic</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT cod_999 as code, denumire_boala as diagnostic_ro 
                                        FROM coduri_boli 
                                        WHERE CAST(SUBSTRING(cod_999, 1, 3) AS UNSIGNED) BETWEEN ? AND ?
                                        ORDER BY cod_999";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("ii", $min, $max);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while ($row = $result->fetch_assoc()):
                                    ?>
                                    <tr class="diagnostic-row" 
                                        data-code="<?php echo htmlspecialchars($row['code']); ?>"
                                        data-diagnostic="<?php echo htmlspecialchars($row['diagnostic_ro']); ?>">
                                        <td><?php echo htmlspecialchars($row['code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['diagnostic_ro']); ?></td>
                                        <td>
                                            <button class="view-details-btn" 
                                                    onclick="selectDiagnostic('<?php echo htmlspecialchars($row['code']); ?>', 
                                                                    '<?php echo htmlspecialchars(addslashes($row['diagnostic_ro'])); ?>')">
                                                Selectează
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <footer>
            <div class="wrapper">
                <p>EasyMed © 2024</p>
            </div>
        </footer>

        <script>
            const profileIcon = document.getElementById('profileIcon');
            const dropdownMenu = document.querySelector('.dropdown-menu');

            if (profileIcon && dropdownMenu) {
                profileIcon.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });

                document.addEventListener('click', function(e) {
                    if (!profileIcon.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });
            }

            const originalValues = {};

            document.addEventListener('DOMContentLoaded', function() {
                const dataInput = document.getElementById('data');
                const oraInput = document.getElementById('ora');
                const simptomeInput = document.getElementById('simptome');
                const diagnosticInput = document.getElementById('selectedDiagnosticCode');

                if (dataInput) {
                    originalValues['data'] = dataInput.value;
                    dataInput.addEventListener('change', fieldChanged);
                }
                if (oraInput) {
                    originalValues['ora'] = oraInput.value;
                    oraInput.addEventListener('change', fieldChanged);
                }
                if (simptomeInput) {
                    originalValues['simptome'] = simptomeInput.value;
                    simptomeInput.addEventListener('input', fieldChanged);
                }
                if (diagnosticInput) {
                    originalValues['diagnostic'] = diagnosticInput.value;
                    diagnosticInput.addEventListener('change', fieldChanged);
                }
            });

            function fieldChanged() {
                const saveButton = document.getElementById('save-all-btn');
                let hasChanges = false;

                const dataInput = document.getElementById('data');
                if (dataInput && dataInput.value !== originalValues['data']) {
                    hasChanges = true;
                }

                const oraInput = document.getElementById('ora');
                if (oraInput && oraInput.value !== originalValues['ora']) {
                    hasChanges = true;
                }

                const simptomeInput = document.getElementById('simptome');
                if (simptomeInput && simptomeInput.value !== originalValues['simptome']) {
                    hasChanges = true;
                }

                const diagnosticInput = document.getElementById('selectedDiagnosticCode');
                if (diagnosticInput && diagnosticInput.value !== originalValues['diagnostic']) {
                    hasChanges = true;
                }

                if (hasChanges) {
                    saveButton.style.display = 'inline-block';
                } else {
                    saveButton.style.display = 'none';
                }
            }

            function selectDiagnostic(code, diagnostic) {
                document.getElementById('selectedDiagnosticCode').value = code;
                document.getElementById('selectedDiagnosticDisplay').value = `${code} - ${diagnostic}`;
                fieldChanged();
                closeDiagnosticSearch();
            }

            document.addEventListener('DOMContentLoaded', function() {
                const timeInput = document.getElementById('ora');
                const timePicker = document.getElementById('timePicker');
                let selectedHour = '00';
                let selectedMinute = '00';

                if (timeInput && timePicker) {      
                    const [hours, minutes] = timeInput.value.split(':');
                    selectedHour = hours;
                    selectedMinute = minutes;

                    timeInput.addEventListener('focus', function() {
                        timePicker.style.display = 'flex';
                        highlightSelectedTime();
                    });

                    document.addEventListener('click', function(e) {
                        if (!timeInput.contains(e.target) && !timePicker.contains(e.target)) {
                            timePicker.style.display = 'none';
                        }
                    });

                    timePicker.querySelector('.time-picker-hours').addEventListener('click', function(e) {
                        if (e.target.classList.contains('time-option')) {
                            selectedHour = e.target.dataset.hour;
                            updateTimeInput();
                            highlightSelectedTime();
                        }
                    });

                    timePicker.querySelector('.time-picker-minutes').addEventListener('click', function(e) {
                        if (e.target.classList.contains('time-option')) {
                            selectedMinute = e.target.dataset.minute;
                            updateTimeInput();
                            highlightSelectedTime();
                        }
                    });

                    function updateTimeInput() {
                        timeInput.value = `${selectedHour}:${selectedMinute}`;
                        fieldChanged();
                    }

                    function highlightSelectedTime() {
                        timePicker.querySelectorAll('.time-option').forEach(option => {
                            option.classList.remove('selected');
                        });

                        timePicker.querySelector(`.time-option[data-hour="${selectedHour}"]`)?.classList.add('selected');
                        timePicker.querySelector(`.time-option[data-minute="${selectedMinute}"]`)?.classList.add('selected');
                    }

                    highlightSelectedTime();
                }
            });

            function saveAllChanges(event) {
                event.preventDefault();
                const promises = [];

                const dataInput = document.getElementById('data');
                if (dataInput && dataInput.value !== originalValues['data']) {
                    promises.push(saveField('data', dataInput.value));
                }

                const oraInput = document.getElementById('ora');
                if (oraInput && oraInput.value !== originalValues['ora']) {
                    promises.push(saveField('ora', oraInput.value));
                }

                const simptomeInput = document.getElementById('simptome');
                if (simptomeInput && simptomeInput.value !== originalValues['simptome']) {
                    promises.push(saveField('simptome', simptomeInput.value));
                }

                const diagnosticInput = document.getElementById('selectedDiagnosticCode');
                if (diagnosticInput && diagnosticInput.value !== originalValues['diagnostic']) {
                    promises.push(saveField('diagnostic', diagnosticInput.value));
                }

                Promise.all(promises)
                    .then(() => {
                        alert('Modificările au fost salvate cu succes!');
                        document.getElementById('save-all-btn').style.display = 'none';
                        if (dataInput) originalValues['data'] = dataInput.value;
                        if (oraInput) originalValues['ora'] = oraInput.value;
                        if (simptomeInput) originalValues['simptome'] = simptomeInput.value;
                        if (diagnosticInput) originalValues['diagnostic'] = diagnosticInput.value;
                    })
                    .catch(error => {
                        alert('Eroare la salvarea modificărilor: ' + error.message);
                    });

                return false;
            }

            function saveField(field, value) {
                return fetch('update_consultatie.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `field=${field}&value=${encodeURIComponent(value)}&id=<?php echo $consultatie['ID']; ?>`
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Eroare la actualizare');
                    }
                });
            }

            function openDiagnosticSearch() {
                document.getElementById('diagnosticSearchModal').style.display = 'block';
            }

            function closeDiagnosticSearch() {
                document.getElementById('diagnosticSearchModal').style.display = 'none';
            }

            function toggleCategory(index) {
                const content = document.getElementById(`category-${index}`);
                const header = content.previousElementSibling;
                const icon = header.querySelector('.toggle-icon');
                
                if (content.style.display === 'none') {
                    content.style.display = 'block';
                    header.classList.remove('collapsed');
                } else {
                    content.style.display = 'none';
                    header.classList.add('collapsed');
                }
            }

            function filterCategory(categoryIndex) {
                const searchInput = document.getElementById(`categorySearch-${categoryIndex}`);
                const searchText = searchInput.value.toLowerCase();
                const categoryContent = document.getElementById(`category-${categoryIndex}`);
                const rows = categoryContent.getElementsByClassName('diagnostic-row');
                
                for (let row of rows) {
                    const code = row.getAttribute('data-code').toLowerCase();
                    const diagnostic = row.getAttribute('data-diagnostic').toLowerCase();
                    
                    if (code.includes(searchText) || diagnostic.includes(searchText)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            }

            function filterDiagnostics() {
                const searchText = document.getElementById('diagnosticSearchInput').value.toLowerCase();
                const rows = document.getElementsByClassName('diagnostic-row');
                let foundAny = false;
                
                document.querySelectorAll('.category-search-input').forEach(input => {
                    input.value = '';
                });
                
                for (let row of rows) {
                    const code = row.getAttribute('data-code').toLowerCase();
                    const diagnostic = row.getAttribute('data-diagnostic').toLowerCase();
                    const category = row.closest('.category-content');
                
                    if (code.includes(searchText) || diagnostic.includes(searchText)) {
                        row.style.display = '';
                        if (category) {
                            category.style.display = 'block';
                            category.previousElementSibling.classList.remove('collapsed');
                        }
                        foundAny = true;
                    } else {
                        row.style.display = 'none';
                    }
                }
                
                if (searchText) {
                    document.querySelectorAll('.category-content').forEach(content => {
                        content.style.display = 'block';
                        content.previousElementSibling.classList.remove('collapsed');
                    });
                }
            }

            window.onclick = function(event) {
                const modal = document.getElementById('diagnosticSearchModal');
                if (event.target === modal) {
                    closeDiagnosticSearch();
                }
            }
        </script>
    </body>
</html>

<?php
    $conn->close();
?> 