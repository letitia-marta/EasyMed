<?php
/**
 * Pagină pentru afișarea și editarea detaliilor pacienților
 * 
 * Această pagină oferă o interfață completă pentru gestionarea informațiilor pacienților:
 * - Afișează informațiile personale ale pacientului (nume, prenume, CNP, etc.)
 * - Permite editarea câmpurilor pentru medici (nume, prenume, adresa, grupa sanguină)
 * - Afișează relațiile familiale ale pacientului
 * - Afișează istoricul consultațiilor medicale
 * - Afișează documentele medicale încărcate
 * - Implementează autorizare pentru a permite accesul doar la pacienții medicului
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

    // Verifică dacă s-a furnizat CNP-ul pacientului
    if (!isset($_GET['cnp'])) {
        header("Location: listaPacienti.php");
        exit();
    }

    // Extrage CNP-ul pacientului și ID-ul utilizatorului
    $pacient_cnp = $_GET['cnp'];
    $utilizator_id = $_SESSION['user_id'];

    // Verifică dacă utilizatorul este medic și obține ID-ul medicului
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
    $stmtDoctorPatient->bind_param("is", $doctor_id, $pacient_cnp);
    $stmtDoctorPatient->execute();
    $stmtDoctorPatient->store_result();
    $is_my_patient = $stmtDoctorPatient->num_rows > 0;
    $stmtDoctorPatient->close();

    // Obține informațiile complete ale pacientului
    $sqlPacient = "SELECT p.*, 
                   (SELECT COUNT(*) FROM consultatii c WHERE c.CNPPacient = p.CNP) as numar_consultatii
                   FROM pacienti p
                   WHERE p.CNP = ?";
    $stmtPacient = $conn->prepare($sqlPacient);
    $stmtPacient->bind_param("s", $pacient_cnp);
    $stmtPacient->execute();
    $resultPacient = $stmtPacient->get_result();

    // Verifică dacă pacientul există
    if ($resultPacient->num_rows === 0) {
        header("Location: listaPacienti.php");
        exit();
    }

    $pacient = $resultPacient->fetch_assoc();

    // Obține relațiile familiale ale pacientului
    $sqlRelatii = "SELECT 
        r.id,
        r.pacient_id,
        r.pacient_relat_id,
        t.denumire as tip_relatie,
        p.id as id_pacient_ruda,
        p.nume,
        p.prenume,
        p.CNP,
        p.sex
    FROM relatii_pacienti r
    INNER JOIN pacienti p ON p.id = CASE 
        WHEN r.pacient_id = ? THEN r.pacient_relat_id
        ELSE r.pacient_id
    END
    INNER JOIN tipuri_relatii t ON r.tip_relatie_id = t.id
    WHERE r.pacient_id = ? OR r.pacient_relat_id = ?
    ORDER BY t.denumire, p.nume, p.prenume";
    
    $stmtRelatii = $conn->prepare($sqlRelatii);
    $stmtRelatii->bind_param("iii", $pacient['id'], $pacient['id'], $pacient['id']);
    $stmtRelatii->execute();
    $resultRelatii = $stmtRelatii->get_result();
    $relatii = $resultRelatii->fetch_all(MYSQLI_ASSOC);
    $stmtRelatii->close();

    // Verifică care rude sunt pacienți ai medicului
    $relative_ids = array_column($relatii, 'id_pacient_ruda');
    $relatives_of_doctor = [];
    if (!empty($relative_ids)) {
        $placeholders = implode(',', array_fill(0, count($relative_ids), '?'));
        $types = str_repeat('i', count($relative_ids) + 1);
        $sqlCheck = "SELECT pacient_id FROM doctor_pacient WHERE doctor_id = ? AND pacient_id IN ($placeholders)";
        $stmtCheck = $conn->prepare($sqlCheck);
        $params = array_merge([$doctor_id], $relative_ids);
        $stmtCheck->bind_param($types, ...$params);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        while ($row = $resultCheck->fetch_assoc()) {
            $relatives_of_doctor[$row['pacient_id']] = true;
        }
        $stmtCheck->close();
    }

    // Obține istoricul consultațiilor pacientului
    $sqlConsultatii = "SELECT c.*, m.nume as nume_doctor, m.prenume as prenume_doctor, m.specializare,
                      GROUP_CONCAT(DISTINCT bt.Cod SEPARATOR ', ') as cod_bilet_trimitere,
                      GROUP_CONCAT(DISTINCT bi.CodBilet SEPARATOR ', ') as cod_bilet_investigatii,
                      GROUP_CONCAT(DISTINCT rm.Cod SEPARATOR ', ') as cod_reteta_medicala
                      FROM consultatii c
                      INNER JOIN medici m ON c.id_medic = m.id
                      LEFT JOIN bilete_trimitere bt ON bt.Consultatie = c.ID
                      LEFT JOIN bilete_investigatii bi ON bi.Consultatie = c.ID
                      LEFT JOIN retete_medicale rm ON rm.Consultatie = c.ID
                      WHERE c.CNPPacient = ?
                      GROUP BY c.ID
                      ORDER BY c.Data DESC, c.Ora DESC";
    $stmtConsultatii = $conn->prepare($sqlConsultatii);
    $stmtConsultatii->bind_param("s", $pacient['CNP']);
    $stmtConsultatii->execute();
    $resultConsultatii = $stmtConsultatii->get_result();

    // Obține documentele medicale ale pacientului
    $sqlDocuments = "SELECT * FROM documente WHERE pacient_id = ? ORDER BY data_upload DESC";
    $stmtDocuments = $conn->prepare($sqlDocuments);
    $stmtDocuments->bind_param("i", $pacient['id']);
    $stmtDocuments->execute();
    $resultDocuments = $stmtDocuments->get_result();
?>

<!DOCTYPE html>
<html lang="ro">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Detalii Pacient - EasyMed</title>
        <link rel="stylesheet" href="style.css">
        <style>
            /* Container-ul principal pentru detalii */
            .details-container {
                width: 1400px;
                margin: 2rem auto;
                padding: 2rem;
                background: #13181d;
                border-radius: 10px;
                color: white;
            }

            /* Secțiunea cu informațiile pacientului */
            .patient-info {
                background: #2A363F;
                padding: 2rem;
                border-radius: 8px;
                margin-bottom: 2rem;
            }

            .patient-info h2 {
                color: #5cf9c8;
                margin-top: 0;
                margin-bottom: 1.5rem;
            }

            /* Grid pentru afișarea informațiilor */
            .info-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1.5rem;
            }

            /* Element individual pentru informații */
            .info-item {
                margin-bottom: 1rem;
            }

            .info-item.full-width {
                grid-column: 1 / -1;
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

            /* Stilizarea pentru dropdown-uri */
            select.editable-field {
                cursor: pointer;
                appearance: none;
                background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right 0.7rem center;
                background-size: 1em;
                padding-right: 2.5rem;
            }

            select.editable-field option {
                background: #13181d;
                color: white;
            }

            .save-all-btn {
                display: none;
                padding: 0.8rem 1.5rem;
                background: #5cf9c8;
                color: #13181d;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 500;
                margin-top: 1rem;
                transition: background-color 0.3s;
            }

            .save-all-btn:hover {
                background: #4ad8b7;
            }

            .save-all-btn.show {
                display: inline-block;
            }

            .consultations-section {
                margin-top: 2rem;
            }

            .consultations-section h3 {
                color: #5cf9c8;
                margin-bottom: 1rem;
            }

            .consultations-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 1rem;
            }

            .consultations-table th,
            .consultations-table td {
                padding: 1rem;
                text-align: left;
                border-bottom: 1px solid #2A363F;
            }

            .consultations-table th {
                background: #2A363F;
                font-weight: 500;
            }

            .consultations-table tr:hover {
                background: #1a2128;
            }

            .view-details-btn {
                display: inline-block;
                padding: 0.5rem 1rem;
                background: #5cf9c8;
                color: #13181d;
                text-decoration: none;
                border-radius: 4px;
                transition: background-color 0.3s;
                border: none;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
            }

            .view-details-btn:hover {
                background: #4ad8b7;
                text-decoration: none;
                color: #13181d;
            }

            .consultations-table td {
                padding: 1rem;
                text-align: left;
                border-bottom: 1px solid #2A363F;
                vertical-align: middle;
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

            .no-consultations {
                text-align: center;
                padding: 2rem;
                color: #888;
                font-size: 1.2rem;
                background: #2A363F;
                border-radius: 8px;
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

            .relationships-section {
                background: #2A363F;
                padding: 2rem;
                border-radius: 8px;
                margin-bottom: 2rem;
            }

            .relationships-section h3 {
                color: #5cf9c8;
                margin-top: 0;
                margin-bottom: 1.5rem;
            }

            .relationships-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 1rem;
            }

            .relationships-table th,
            .relationships-table td {
                padding: 1rem;
                text-align: left;
                border-bottom: 1px solid #2A363F;
            }

            .relationships-table th {
                color: white;
                font-weight: bold;
            }

            .relationships-table tr:hover {
                background: #1a2128;
            }

            .edit-btn {
                display: inline-block;
                padding: 0.5rem 1rem;
                background: #5cf9c8;
                color: #13181d;
                text-decoration: none;
                border-radius: 4px;
                transition: background-color 0.3s;
                border: none;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                margin-left: 1rem;
            }

            .edit-btn:hover {
                background: #4ad8b7;
            }

            .edit-form {
                display: none;
                margin-top: 1rem;
                padding: 1rem;
                background: #1a2128;
                border-radius: 4px;
            }

            .edit-form.show {
                display: block;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .form-group label {
                display: block;
                margin-bottom: 0.5rem;
                color: #888;
            }

            .form-group input {
                width: 100%;
                padding: 0.5rem;
                border: 1px solid #2A363F;
                border-radius: 4px;
                background: #13181d;
                color: white;
            }

            .form-actions {
                margin-top: 1rem;
                display: flex;
                gap: 1rem;
            }

            .save-btn, .cancel-btn {
                padding: 0.5rem 1rem;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 500;
            }

            .save-btn {
                background: #5cf9c8;
                color: #13181d;
            }

            .save-btn:hover {
                background: #4ad8b7;
            }

            .cancel-btn {
                background: #2A363F;
                color: white;
            }

            .cancel-btn:hover {
                background: #3A4A5F;
            }

            .documents-section {
                background: #2A363F;
                padding: 2rem;
                border-radius: 8px;
                margin-bottom: 2rem;
            }

            .documents-section h3 {
                color: #5cf9c8;
                margin-top: 0;
                margin-bottom: 1.5rem;
            }

            .documents-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 1rem;
            }

            .document-card {
                background: #13181d;
                border-radius: 8px;
                padding: 1rem;
                display: flex;
                align-items: flex-start;
                gap: 1rem;
                min-height: 100px;
                overflow: hidden;
            }

            .document-icon {
                flex-shrink: 0;
            }

            .document-info {
                flex: 1;
                min-width: 0;
                overflow: hidden;
            }

            .document-info h4 {
                margin: 0 0 0.5rem 0;
                color: white;
                font-size: 1rem;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }

            .document-type {
                color: #5cf9c8;
                margin: 0.25rem 0;
                font-size: 0.9rem;
            }

            .document-date {
                color: #888;
                margin: 0.25rem 0;
                font-size: 0.8rem;
            }

            .document-actions {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
                flex-shrink: 0;
                min-width: 80px;
            }

            .download-btn {
                padding: 0.5rem 0.75rem;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 0.8rem;
                text-decoration: none;
                text-align: center;
                white-space: nowrap;
                min-width: 70px;
                background: #5cf9c8;
                color: black;
            }

            .download-btn:hover {
                background: #4ad8b7;
                text-decoration: none;
                color: black;
            }

            .no-documents {
                text-align: center;
                padding: 2rem;
                color: #888;
                background: #13181d;
                border-radius: 8px;
            }

            .upload-section {
                background: #13181d;
                padding: 1.5rem;
                border-radius: 8px;
                margin-bottom: 2rem;
                border: 1px solid #2A363F;
            }

            .upload-section h4 {
                color: #5cf9c8;
                margin-top: 0;
                margin-bottom: 1rem;
            }

            .upload-form {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .form-group label {
                display: block;
                margin-bottom: 0.5rem;
                color: #888;
                font-weight: 500;
            }

            .submit-btn {
                background: #5cf9c8;
                color: black;
                border: none;
                padding: 0.8rem 1.5rem;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 500;
                font-size: 1rem;
                transition: background-color 0.3s;
            }

            .submit-btn:hover {
                background: #4ad8b7;
            }

            .analyze-btn:hover {
                background: #4ad8b7 !important;
            }

            .analyze-btn:disabled {
                background: #666 !important;
                cursor: not-allowed;
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
                <a href="listaPacienti.php" class="back-btn">← Înapoi la lista de pacienți</a>

                <?php
                if (isset($_GET['success']) && $_GET['success'] === 'document_uploaded') {
                    echo '<div style="background: #4CAF50; color: white; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">Document încărcat cu succes!</div>';
                }
                if (isset($_GET['error'])) {
                    $errorMessages = [
                        'missing_fields' => 'Toate câmpurile sunt obligatorii.',
                        'file_upload_error' => 'Eroare la încărcarea fișierului.',
                        'file_too_large' => 'Fișierul este prea mare (maxim 10MB).',
                        'invalid_file_type' => 'Tip de fișier neacceptat.',
                        'database_error' => 'Eroare la salvarea în baza de date.',
                        'file_move_error' => 'Eroare la salvarea fișierului.',
                        'unauthorized' => 'Nu aveți permisiunea să încărcați documente pentru acest pacient.',
                        'file_not_found' => 'Fișierul nu a fost găsit.',
                        'document_not_found' => 'Documentul nu a fost găsit.'
                    ];
                    $error = $_GET['error'];
                    if (isset($errorMessages[$error])) {
                        echo '<div style="background: #f44336; color: white; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">' . $errorMessages[$error] . '</div>';
                    }
                }
                ?>

                <div class="patient-info">
                    <h2>Informații Pacient</h2>
                    <form id="edit-form" onsubmit="return saveAllChanges(event);">
                    <div class="info-grid">
                        <?php
                            $birthDate = new DateTime($pacient['data_nasterii']);
                            $today = new DateTime();
                            $age = $birthDate->diff($today)->y;
                        ?>
                        <div class="info-item">
                            <div class="info-label">Nume</div>
                                <?php if ($is_my_patient): ?>
                                    <input type="text" class="editable-field" id="nume" name="nume" value="<?php echo htmlspecialchars($pacient['nume']); ?>" onchange="fieldChanged()">
                                <?php else: ?>
                            <div class="info-value">(Nedisponibil)</div>
                                <?php endif; ?>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Prenume</div>
                                <?php if ($is_my_patient): ?>
                                    <input type="text" class="editable-field" id="prenume" name="prenume" value="<?php echo htmlspecialchars($pacient['prenume']); ?>" onchange="fieldChanged()">
                                <?php else: ?>
                            <div class="info-value">(Nedisponibil)</div>
                                <?php endif; ?>
                        </div>
                        <div class="info-item">
                            <div class="info-label">CNP</div>
                            <div class="info-value"><?php echo $is_my_patient ? htmlspecialchars($pacient['CNP']) : '(Nedisponibil)'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Sex</div>
                            <div class="info-value"><?php echo htmlspecialchars($pacient['sex']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Data Nașterii</div>
                            <div class="info-value"><?php echo htmlspecialchars($pacient['data_nasterii']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Vârstă</div>
                            <div class="info-value"><?php echo $age; ?> ani</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Grupa sanguină</div>
                                <?php if ($is_my_patient): ?>
                                    <?php
                                        $current_blood_type = trim($pacient['grupa_sanguina']);
                                       
                                        $blood_type_mapping = [
                                            'A+' => 'A(II)+',
                                            'A-' => 'A(II)-',
                                            'B+' => 'B(III)+',
                                            'B-' => 'B(III)-',
                                            'AB+' => 'AB(IV)+',
                                            'AB-' => 'AB(IV)-',
                                            '0+' => 'O(I)+',
                                            '0-' => 'O(I)-',
                                            'O+' => 'O(I)+',
                                            'O-' => 'O(I)-'
                                        ];
                                        if (isset($blood_type_mapping[$current_blood_type])) {
                                            $current_blood_type = $blood_type_mapping[$current_blood_type];
                                        }
                                        $blood_types = [
                                            'O(I)+', 'O(I)-',
                                            'A(II)+', 'A(II)-',
                                            'B(III)+', 'B(III)-',
                                            'AB(IV)+', 'AB(IV)-'
                                        ];
                                    ?>
                                    <select class="editable-field" id="grupa_sanguina" name="grupa_sanguina" onchange="fieldChanged()">
                                        <?php
                                        $has_valid_blood_type = in_array($current_blood_type, $blood_types);
                                        if (!$has_valid_blood_type && !empty($current_blood_type)) {
                                            echo '<option value="' . htmlspecialchars($current_blood_type) . '" selected>' . htmlspecialchars($current_blood_type) . '</option>';
                                        }
                                        ?>
                                        <?php foreach ($blood_types as $type): ?>
                                            <option value="<?php echo $type; ?>" <?php echo $current_blood_type === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div style="display: none;">
                                        Original DB value: "<?php echo htmlspecialchars($pacient['grupa_sanguina']); ?>"<br>
                                        Mapped value: "<?php echo htmlspecialchars($current_blood_type); ?>"
                                    </div>
                                <?php else: ?>
                            <div class="info-value"><?php echo htmlspecialchars($pacient['grupa_sanguina']); ?></div>
                                <?php endif; ?>
                        </div>
                        <?php if ($is_my_patient): ?>
                        <div class="info-item">
                            <div class="info-label">Număr Consultații</div>
                            <div class="info-value"><?php echo htmlspecialchars($pacient['numar_consultatii']); ?></div>
                        </div>
                        <?php endif; ?>
                            <div class="info-item full-width">
                                <div class="info-label">Adresă</div>
                                <?php if ($is_my_patient): ?>
                                    <input type="text" class="editable-field" id="adresa" name="adresa" value="<?php echo htmlspecialchars($pacient['adresa']); ?>" onchange="fieldChanged()">
                                <?php else: ?>
                                    <div class="info-value">(Nedisponibil)</div>
                                <?php endif; ?>
                            </div>
                    </div>
                        <button type="submit" class="save-all-btn" id="save-all-btn">Salvează modificările</button>
                    </form>
                </div>

                <div class="relationships-section">
                    <h3>Relații Familiale</h3>
                    <?php if (empty($relatii)): ?>
                        <p>Nu există relații înregistrate.</p>
                    <?php else: ?>
                        <table class="relationships-table">
                            <thead>
                                <tr>
                                    <th>Relație</th>
                                    <th>Nume</th>
                                    <th>Prenume</th>
                                    <th>CNP</th>
                                    <th>Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($relatii as $relatie): ?>
                                    <?php 
                                        $relative_id = $relatie['id_pacient_ruda'];
                                        $is_visible = isset($relatives_of_doctor[$relative_id]);
                                    ?>
                                    <tr>
                                        <td>
                                            <?php 
                                                $isRelatedPatient = $relatie['pacient_relat_id'] == $pacient['id'];
                                                
                                                if ($isRelatedPatient) {
                                                    echo htmlspecialchars($relatie['tip_relatie']);
                                                } else {
                                                    if ($pacient['sex'] === 'F') {
                                                        switch($relatie['tip_relatie']) {
                                                            case 'Mama': echo 'Fiică'; break;
                                                            case 'Tata': echo 'Fiică'; break;
                                                            case 'Frate': echo 'Soră'; break;
                                                            case 'Sora': echo 'Soră'; break;
                                                            case 'Sot': echo 'Soție'; break;
                                                            case 'Sotie': echo 'Soț'; break;
                                                            case 'Fiu': echo 'Mama'; break;
                                                            case 'Fiica': echo 'Mama'; break;
                                                            default: echo htmlspecialchars($relatie['tip_relatie']);
                                                        }
                                                    } else {
                                                        switch($relatie['tip_relatie']) {
                                                            case 'Mama': echo 'Fiu'; break;
                                                            case 'Tata': echo 'Fiu'; break;
                                                            case 'Frate': echo 'Frate'; break;
                                                            case 'Sora': echo 'Frate'; break;
                                                            case 'Sot': echo 'Soție'; break;
                                                            case 'Sotie': echo 'Soț'; break;
                                                            case 'Fiu': echo 'Tata'; break;
                                                            case 'Fiica': echo 'Tata'; break;
                                                            default: echo htmlspecialchars($relatie['tip_relatie']);
                                                        }
                                                    }
                                                }
                                            ?>
                                        </td>
                                        <td><?php echo $is_visible ? htmlspecialchars($relatie['nume']) : '(Nedisponibil)'; ?></td>
                                        <td><?php echo $is_visible ? htmlspecialchars($relatie['prenume']) : '(Nedisponibil)'; ?></td>
                                        <td><?php echo $is_visible ? htmlspecialchars($relatie['CNP']) : '(Nedisponibil)'; ?></td>
                                        <td>
                                            <a href="detaliiPacient.php?cnp=<?php echo htmlspecialchars($relatie['CNP']); ?>" class="view-details-btn">
                                                Detalii
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <?php if ($is_my_patient): ?>
                <div class="consultations-section">
                    <h3>Istoric Consultații</h3>
                    <?php if ($resultConsultatii->num_rows > 0): ?>
                        <table class="consultations-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Data</th>
                                    <th>Ora</th>
                                    <th>Diagnostic</th>
                                    <th>Bilet trimitere</th>
                                    <th>Bilet investigații</th>
                                    <th>Rețetă medicală</th>
                                    <th>Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($consultatie = $resultConsultatii->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($consultatie['ID']); ?></td>
                                        <td><?php echo date('d.m.Y', strtotime($consultatie['Data'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($consultatie['Ora'])); ?></td>
                                        <td><?php echo htmlspecialchars($consultatie['Diagnostic']); ?></td>
                                        <td><?php echo $consultatie['cod_bilet_trimitere'] ? htmlspecialchars($consultatie['cod_bilet_trimitere']) : '-'; ?></td>
                                        <td><?php echo $consultatie['cod_bilet_investigatii'] ? htmlspecialchars($consultatie['cod_bilet_investigatii']) : '-'; ?></td>
                                        <td><?php echo $consultatie['cod_reteta_medicala'] ? htmlspecialchars($consultatie['cod_reteta_medicala']) : '-'; ?></td>
                                        <td><?php echo '<a href="detaliiConsultatie.php?id=' . $consultatie['ID'] . '" class="view-details-btn">Detalii</a>'; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-consultations">
                            Nu există consultații înregistrate pentru acest pacient.
                        </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="consultations-section">
                    <h3>Diagnostice</h3>
                    <?php
                        $sqlDiagnostics = "SELECT c.Data, c.Diagnostic, m.nume, m.prenume, m.specializare,
                                         cb.cod_999, cb.denumire_boala
                                         FROM consultatii c
                                         INNER JOIN medici m ON c.id_medic = m.id
                                         LEFT JOIN coduri_boli cb ON c.Diagnostic = cb.cod_999
                                         WHERE c.CNPPacient = ?
                                         ORDER BY c.Data DESC, c.Ora DESC";
                        $stmtDiagnostics = $conn->prepare($sqlDiagnostics);
                        $stmtDiagnostics->bind_param("s", $pacient['CNP']);
                        $stmtDiagnostics->execute();
                        $resultDiagnostics = $stmtDiagnostics->get_result();
                    ?>
                    <?php if ($resultDiagnostics->num_rows > 0): ?>
                        <table class="consultations-table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Diagnostic</th>
                                    <th>Medic</th>
                                    <th>Specializare</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($diagnostic = $resultDiagnostics->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d.m.Y', strtotime($diagnostic['Data'])); ?></td>
                                        <td>
                                            <?php 
                                                if ($diagnostic['cod_999'] && $diagnostic['denumire_boala']) {
                                                    echo htmlspecialchars($diagnostic['cod_999'] . ' - ' . $diagnostic['denumire_boala']);
                                                } else {
                                                    echo htmlspecialchars($diagnostic['Diagnostic']);
                                                }
                                            ?>
                                        </td>
                                        <td>Dr. <?php echo htmlspecialchars($diagnostic['nume'] . ' ' . $diagnostic['prenume']); ?></td>
                                        <td><?php echo htmlspecialchars($diagnostic['specializare']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-consultations">
                            Nu există diagnostice înregistrate pentru acest pacient.
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="documents-section">
                    <h3>Documente Pacient</h3>
                    
                    <?php if ($is_my_patient): ?>
                    <div class="upload-section">
                        <h4>Încarcă Document Nou</h4>
                        <form action="upload_document_doctor.php" method="POST" enctype="multipart/form-data" class="upload-form">
                            <input type="hidden" name="pacient_id" value="<?php echo $pacient['id']; ?>">
                            <input type="hidden" name="medic_id" value="<?php echo $doctor_id; ?>">
                            
                            <div class="form-group">
                                <label for="document_file">Fișier:</label>
                                <input type="file" id="document_file" name="document_file" required 
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                       style="background: #2A363F; border: 1px solid #5cf9c8; color: white; padding: 0.5rem; border-radius: 4px; width: 100%;">
                                <small style="color: #888; font-size: 0.8rem;">Formate acceptate: PDF, JPG, PNG, DOC, DOCX (max 10MB)</small>
                                <button type="button" id="analyze_btn" class="analyze-btn" style="margin-top: 0.5rem; background: #5cf9c8; color: black; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">
                                    🤖 Analizează cu AI
                                </button>
                            </div>
                            <div class="form-group">
                                <label for="document_title">Titlu document:</label>
                                <input type="text" id="document_title" name="document_title" required 
                                       style="background: #2A363F; border: 1px solid #5cf9c8; color: white; padding: 0.5rem; border-radius: 4px; width: 100%;">
                                <div id="ai_suggestion" style="display: none; margin-top: 0.5rem; padding: 0.5rem; background: #2A363F; border-radius: 4px; border-left: 3px solid #5cf9c8;">
                                    <small style="color: #5cf9c8;">💡 Sugestie AI: <span id="suggested_title"></span></small>
                                    <button type="button" id="use_suggestion" style="margin-left: 0.5rem; background: #5cf9c8; color: black; border: none; padding: 0.25rem 0.5rem; border-radius: 2px; cursor: pointer; font-size: 0.8rem;">Folosește</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="document_type">Tip document:</label>
                                <select id="document_type" name="document_type" required 
                                        style="background: #2A363F; border: 1px solid #5cf9c8; color: white; padding: 0.5rem; border-radius: 4px; width: 100%;">
                                    <option value="">Selectează tipul</option>
                                    <option value="analize">Analize medicale</option>
                                    <option value="imagistica_medicala">Imagistica medicala</option>
                                    <option value="observatie">Foaie de observatie</option>
                                    <option value="scrisori">Scrisoare medicala</option>
                                    <option value="externari">Bilet de externare</option>
                                    <option value="alte">Altele</option>
                                </select>
                                <div id="ai_type_suggestion" style="display: none; margin-top: 0.5rem; padding: 0.5rem; background: #2A363F; border-radius: 4px; border-left: 3px solid #5cf9c8;">
                                    <small style="color: #5cf9c8;">🤖 Tip detectat: <span id="suggested_type"></span></small>
                                    <button type="button" id="use_type_suggestion" style="margin-left: 0.5rem; background: #5cf9c8; color: black; border: none; padding: 0.25rem 0.5rem; border-radius: 2px; cursor: pointer; font-size: 0.8rem;">Folosește</button>
                                </div>
                            </div>
                            <button type="submit" class="submit-btn">Încarcă Document</button>
                        </form>
                    </div>
                    <?php endif; ?>

                    <?php if ($resultDocuments->num_rows > 0): ?>
                        <div class="documents-grid">
                            <?php while ($document = $resultDocuments->fetch_assoc()): ?>
                                <div class="document-card">
                                    <div class="document-icon">
                                        <?php
                                        $extension = pathinfo($document['nume_fisier'], PATHINFO_EXTENSION);
                                        $icon = '📄';
                                        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) $icon = '🖼️';
                                        elseif (strtolower($extension) == 'pdf') $icon = '📋';
                                        elseif (in_array(strtolower($extension), ['doc', 'docx'])) $icon = '📝';
                                        ?>
                                        <span style="font-size: 2rem;"><?php echo $icon; ?></span>
                                    </div>
                                    <div class="document-info">
                                        <h4><?php echo htmlspecialchars($document['titlu']); ?></h4>
                                        <p class="document-type"><?php echo ucfirst(htmlspecialchars($document['tip_document'])); ?></p>
                                        <p class="document-date"><?php echo date('d.m.Y H:i', strtotime($document['data_upload'])); ?></p>
                                    </div>
                                    <div class="document-actions">
                                        <a href="download_document.php?id=<?php echo $document['id']; ?>" class="download-btn">Descarcă</a>
                                        <?php if ($is_my_patient): ?>
                                        <button onclick="deleteDocument(<?php echo $document['id']; ?>)" class="delete-btn" style="background: #ff4444; color: white; border: none; padding: 0.5rem 0.75rem; border-radius: 4px; cursor: pointer; font-size: 0.8rem; text-align: center; white-space: nowrap; min-width: 70px;">Șterge</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-documents">
                            Nu există documente încărcate pentru acest pacient.
                        </div>
                    <?php endif; ?>
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
            const editableFields = ['nume', 'prenume', 'grupa_sanguina', 'adresa'];

            document.addEventListener('DOMContentLoaded', function() {
                editableFields.forEach(field => {
                    const input = document.getElementById(field);
                    if (input) {
                        originalValues[field] = input.value;
                        if (field !== 'grupa_sanguina') {
                            input.addEventListener('input', fieldChanged);
                        } else {
                            input.addEventListener('change', fieldChanged);
                        }
                    }
                });
            });

            function fieldChanged() {
                const saveButton = document.getElementById('save-all-btn');
                let hasChanges = false;

                editableFields.forEach(field => {
                    const input = document.getElementById(field);
                    if (input && input.value !== originalValues[field]) {
                        hasChanges = true;
                    }
                });

                if (hasChanges) {
                    saveButton.classList.add('show');
                } else {
                    saveButton.classList.remove('show');
                }
            }

            function saveAllChanges(event) {
                event.preventDefault();
                const promises = [];

                editableFields.forEach(field => {
                    const input = document.getElementById(field);
                    if (input && input.value !== originalValues[field]) {
                        const promise = fetch('update_pacient.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `field=${field}&value=${encodeURIComponent(input.value)}&cnp=<?php echo $pacient['CNP']; ?>`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                throw new Error(data.message || 'Eroare la actualizare');
                            }
                        });
                        promises.push(promise);
                    }
                });

                Promise.all(promises)
                    .then(() => {
                        alert('Modificările au fost salvate cu succes!');
                        document.getElementById('save-all-btn').classList.remove('show');
                        editableFields.forEach(field => {
                            const input = document.getElementById(field);
                            if (input) {
                                originalValues[field] = input.value;
                            }
                        });
                    })
                    .catch(error => {
                        alert('Eroare la salvarea modificărilor: ' + error.message);
                    });

                return false;
            }

            const typeLabels = {
                'analize': 'Analize medicale',
                'imagistica_medicala': 'Imagistica medicala',
                'observatie': 'Foaie de observatie',
                'scrisori': 'Scrisoare medicala',
                'externari': 'Bilet de externare',
                'alte': 'Altele'
            };

            const analyzeBtn = document.getElementById('analyze_btn');
            if (analyzeBtn) {
                analyzeBtn.addEventListener('click', function() {
                    const fileInput = document.getElementById('document_file');
                    const file = fileInput.files[0];
                    
                    if (!file) {
                        alert('Vă rugăm să selectați un fișier mai întâi.');
                        return;
                    }
                    
                    this.textContent = '🤖 Analizez...';
                    this.disabled = true;
                    
                    const formData = new FormData();
                    formData.append('document_file', file);
                    
                    fetch('ai_document_analyzer.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('suggested_title').textContent = data.suggested_title;
                            document.getElementById('ai_suggestion').style.display = 'block';
                            
                            document.getElementById('suggested_type').textContent = typeLabels[data.document_type] || 'Altele';
                            document.getElementById('ai_type_suggestion').style.display = 'block';

                            if (data.confidence > 0.5) {
                                document.getElementById('ai_suggestion').style.borderLeftColor = '#4CAF50';
                                document.getElementById('ai_type_suggestion').style.borderLeftColor = '#4CAF50';
                            } else {
                                document.getElementById('ai_suggestion').style.borderLeftColor = '#FF9800';
                                document.getElementById('ai_type_suggestion').style.borderLeftColor = '#FF9800';
                            }
                            
                            if (data.debug) {
                                console.log('🤖 AI Analysis Debug Info:', {
                                    'Text Length': data.debug.extracted_text_length + ' characters',
                                    'Text Preview': data.debug.extracted_text_preview,
                                    'Document Type Scores': data.debug.document_type_score,
                                    'Confidence': data.confidence
                                });
                                
                                if (data.debug.extracted_text_length > 0) {
                                    const previewText = data.debug.extracted_text_preview.replace(/\n/g, ' ').substring(0, 100) + '...';
                                    document.getElementById('ai_suggestion').innerHTML += '<br><small style="color: #888; font-size: 0.7rem;">📄 Conținut detectat: ' + previewText + '</small>';
                                }
                            }
                            
                        } else {
                            alert('Eroare la analizarea documentului: ' + (data.error || 'Eroare necunoscută'));
                            if (data.debug) {
                                console.error('AI Analysis Error Details:', data.debug);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Eroare la analizarea documentului.');
                    })
                    .finally(() => {
                        this.textContent = '🤖 Analizează cu AI';
                        this.disabled = false;
                    });
                });
            }

            document.addEventListener('click', function(e) {
                if (e.target && e.target.id === 'use_suggestion') {
                    const suggestedTitle = document.getElementById('suggested_title').textContent;
                    document.getElementById('document_title').value = suggestedTitle;
                }
            });

            document.addEventListener('click', function(e) {
                if (e.target && e.target.id === 'use_type_suggestion') {
                    const suggestedType = document.getElementById('suggested_type').textContent;
                    const typeSelect = document.getElementById('document_type');
                    for (let option of typeSelect.options) {
                        if (typeLabels[option.value] === suggestedType) {
                            typeSelect.value = option.value;
                            break;
                        }
                    }
                }
            });

            function deleteDocument(documentId) {
                if (!confirm('Sigur doriți să ștergeți acest document?')) return;
                
                fetch('delete_document.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ document_id: documentId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Eroare la ștergerea documentului.');
                    }
                })
                .catch(() => alert('Eroare la ștergerea documentului.'));
            }
        </script>
    </body>
</html>

<?php
    $conn->close();
?> 