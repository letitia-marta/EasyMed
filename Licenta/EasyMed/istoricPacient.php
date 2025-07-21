<?php
    session_start();
    require_once 'db_connection.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: pacientiLogin.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM pacienti WHERE utilizator_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
    $id_pacient = $patient['id'];
    $stmt->close();

    $sql = "SELECT c.*, 
               m.nume AS medic_nume, 
               m.prenume AS medic_prenume,
               m.specializare AS medic_specializare,
               cb.cod_999 AS cod_diagnostic,
               cb.denumire_boala AS denumire_diagnostic,
               GROUP_CONCAT(DISTINCT bt.Cod) AS trimiteri,
               GROUP_CONCAT(DISTINCT bi.CodBilet) AS investigatii,
               GROUP_CONCAT(DISTINCT rm.Cod) AS retete
            FROM consultatii c
            INNER JOIN medici m ON c.id_medic = m.id
            LEFT JOIN coduri_boli cb ON c.Diagnostic = cb.cod_999
            LEFT JOIN bilete_trimitere bt ON c.ID = bt.Consultatie
            LEFT JOIN bilete_investigatii bi ON c.ID = bi.Consultatie
            LEFT JOIN retete_medicale rm ON c.ID = rm.Consultatie
            WHERE c.CNPPacient = (SELECT CNP FROM pacienti WHERE id = ?)
            GROUP BY c.ID
            ORDER BY c.Data DESC, c.Ora DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_pacient);
    $stmt->execute();
    $consultations = $stmt->get_result();
    $stmt->close();

    $upcomingSql = "SELECT p.*, m.nume AS medic_nume, m.prenume AS medic_prenume, m.specializare AS medic_specializare, p.motiv_consultatie
                    FROM programari p
                    INNER JOIN medici m ON p.medic_id = m.id
                    WHERE p.pacient_id = ? 
                    AND (p.data_programare > CURDATE() 
                         OR (p.data_programare = CURDATE() AND p.ora_programare > CURTIME()))
                    ORDER BY p.data_programare ASC, p.ora_programare ASC";
    
    $stmt = $conn->prepare($upcomingSql);
    $stmt->bind_param("i", $id_pacient);
    $stmt->execute();
    $upcomingAppointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Istoric Consultații - EasyMed</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .istoric-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 2rem;
            background: #13181d;
            border-radius: 10px;
            color: white;
        }

        .istoric-container h2 {
            color: #5cf9c8;
            margin-bottom: 1.5rem;
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

        .consultations-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .consultations-table th,
        .consultations-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #2A363F;
            word-wrap: break-word;
        }

        .consultations-table th:last-child,
        .consultations-table td:last-child {
            min-width: 120px;
            text-align: center;
        }

        .consultations-table th {
            background: #2A363F;
        }

        .consultations-table tr:hover {
            background: #1a2128;
        }

        .view-details-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            min-width: 120px;
            margin: 0 2px;
            white-space: nowrap;
            background: #5cf9c8 !important;
            color: black !important;
        }

        .view-details-btn:hover,
        .view-details-btn:focus,
        .view-details-btn:active {
            background: #5cf9c8 !important;
            color: black !important;
            transform: none !important;
            box-shadow: none !important;
        }

        * {
            transition: none !important;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background: #13181d;
            margin: 2rem auto;
            padding: 2rem;
            width: 90%;
            max-width: 800px;
            border-radius: 10px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .close-modal {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: white;
        }

        .details-section {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #2A363F;
            border-radius: 8px;
        }

        .details-section h3 {
            margin-top: 0;
            color: #5cf9c8;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .detail-item {
            margin-bottom: 0.5rem;
        }

        .detail-label {
            color: #888;
            font-size: 0.9rem;
        }

        .detail-value {
            color: white;
            font-weight: 500;
        }

        .no-consultations {
            text-align: center;
            padding: 2rem;
            color: #888;
            font-size: 1.2rem;
        }

        .appointments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .appointment-card {
            background: #2A363F;
            border-radius: 8px;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .appointment-date {
            color: #5cf9c8;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .appointment-time {
            margin-left: 0.5rem;
            font-size: 0.9rem;
        }

        .appointment-details {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .doctor-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .specialty {
            color: #888;
            font-size: 0.9rem;
        }

        .appointment-status {
            background: #5cf9c8;
            color: black;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .no-appointments {
            text-align: center;
            padding: 1rem;
            color: #888;
            background: #2A363F;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .edit-appointment-btn {
            background: #5cf9c8;
            color: black;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            width: 100%;
            display: block;
            box-sizing: border-box;
            margin-bottom: 0.25rem;
        }
        .edit-appointment-btn:last-child {
            margin-bottom: 0;
        }

        .edit-appointment-btn:hover {
            background: #5cf9c8;
            transform: none;
        }

        .time-slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .time-slot {
            background: #2A363F;
            border: 1px solid #5cf9c8;
            color: white;
            padding: 0.5rem;
            text-align: center;
            cursor: pointer;
            border-radius: 4px;
        }

        .time-slot.unavailable {
            background: #1a2128;
            border-color: #ff4444;
            color: #ff4444;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .time-slot.occupied {
            background: #ff4444 !important;
            border-color: #ff4444 !important;
            color: white !important;
            cursor: not-allowed !important;
        }

        .time-slot.selected {
            background: #5cf9c8;
            color: black;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #5cf9c8;
        }

        .form-group input[type="date"] {
            width: 100%;
            padding: 0.5rem;
            background: #2A363F;
            border: 1px solid #5cf9c8;
            color: white;
            border-radius: 4px;
        }

        .submit-btn {
            background: #5cf9c8;
            color: black;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
        }

        .submit-btn:hover {
            background: #5cf9c8;
            transform: none;
        }

        .consultation-reason {
            color: #888;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            font-style: italic;
        }

        input[type="date"] {
            color-scheme: dark;
            background: #2A363F;
            border: 1px solid #5cf9c8;
            color: white;
            padding: 0.5rem;
            border-radius: 4px;
            width: 100%;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }

        input[type="date"]::-webkit-datetime-edit {
            color: white;
        }

        input[type="date"]::-webkit-datetime-edit-fields-wrapper {
            color: white;
        }

        input[type="date"]::-webkit-datetime-edit-text {
            color: white;
        }
        input[type="date"]::-webkit-datetime-edit-month-field {
            color: white;
        }
        input[type="date"]::-webkit-datetime-edit-day-field {
            color: white;
        }
        input[type="date"]::-webkit-datetime-edit-year-field {
            color: white;
        }

        input[type="date"]::-webkit-datetime-edit-fields-wrapper {
            color: transparent !important;
        }

        input[type="date"]::-webkit-datetime-edit-text {
            color: transparent !important;
        }

        input[type="date"]::-webkit-datetime-edit-month-field {
            color: transparent !important;
        }

        input[type="date"]::-webkit-datetime-edit-day-field {
            color: transparent !important;
        }

        input[type="date"]::-webkit-datetime-edit-year-field {
            color: transparent !important;
        }

        input[type="date"]:not([value])::before {
            content: "zz-ll-aaaa";
            color: #888;
            font-family: monospace;
            position: absolute;
            pointer-events: none;
        }

        input[type="date"]:not([value]) {
            position: relative;
        }

        .documents-section {
            margin-top: 2rem;
        }

        .upload-section {
            background: #2A363F;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .upload-section h3 {
            color: #5cf9c8;
            margin-top: 0;
            margin-bottom: 1rem;
        }

        .upload-form {
            display: grid;
            gap: 1rem;
        }

        .documents-list h3 {
            color: #5cf9c8;
            margin-bottom: 1rem;
        }

        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }

        .document-card {
            background: #2A363F;
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

        .document-date, .document-size {
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

        .download-btn, .delete-btn {
            padding: 0.5rem 0.75rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            text-decoration: none;
            text-align: center;
            white-space: nowrap;
            min-width: 70px;
        }

        .download-btn {
            background: #5cf9c8;
            color: black;
        }

        .delete-btn {
            background: #ff4444;
            color: white;
        }

        .download-btn:hover, .delete-btn:hover {
            opacity: 0.8;
        }

        .no-documents {
            text-align: center;
            padding: 2rem;
            color: #888;
            background: #2A363F;
            border-radius: 8px;
        }

        .form-group input[type="file"] {
            background: #2A363F;
            border: 1px solid #5cf9c8;
            color: white;
            padding: 0.5rem;
            border-radius: 4px;
            width: 100%;
        }

        .form-group input[type="file"]::-webkit-file-upload-button {
            background: #5cf9c8;
            color: black;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 1rem;
        }

        .form-group select {
            background: #2A363F;
            border: 1px solid #5cf9c8;
            color: white;
            padding: 0.5rem;
            border-radius: 4px;
            width: 100%;
        }

        .form-group select option {
            background: #2A363F;
            color: white;
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
                <li><a href="dashboardPacienti.php">Acasă</a></li>
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
        <div class="istoric-container">
            <?php
            if (isset($_GET['success']) && $_GET['success'] === 'document_uploaded') {
                echo '<div style="background: #4CAF50; color: white; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">Document încărcat cu succes!</div>';
            }
            if (isset($_GET['success']) && $_GET['success'] === '1' && isset($_GET['message'])) {
                echo '<div style="background: #4CAF50; color: white; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">' . htmlspecialchars($_GET['message']) . '</div>';
            }
            if (isset($_GET['error'])) {
                $errorMessages = [
                    'missing_fields' => 'Toate câmpurile sunt obligatorii.',
                    'file_upload_error' => 'Eroare la încărcarea fișierului.',
                    'file_too_large' => 'Fișierul este prea mare (maxim 10MB).',
                    'invalid_file_type' => 'Tip de fișier neacceptat.',
                    'database_error' => 'Eroare la salvarea în baza de date.',
                    'file_move_error' => 'Eroare la salvarea fișierului.',
                    'file_not_found' => 'Fișierul nu a fost găsit.',
                    'document_not_found' => 'Documentul nu a fost găsit.'
                ];
                $error = $_GET['error'];
                if (isset($errorMessages[$error])) {
                    echo '<div style="background: #f44336; color: white; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">' . $errorMessages[$error] . '</div>';
                } elseif (isset($_GET['message'])) {
                    echo '<div style="background: #f44336; color: white; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">' . htmlspecialchars($_GET['message']) . '</div>';
                }
            }
            ?>
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <a href="dashboardPacienti.php" class="back-btn">← Înapoi la dashboard</a>
            </div>
            <h2>Programări Viitoare</h2>
            <?php if ($upcomingAppointments->num_rows > 0): ?>
                <div class="appointments-grid">
                    <?php while ($appointment = $upcomingAppointments->fetch_assoc()): ?>
                        <div class="appointment-card">
                            <div class="appointment-date">
                                <?php echo date('d.m.Y', strtotime($appointment['data_programare'])); ?>
                                <span class="appointment-time"><?php echo date('H:i', strtotime($appointment['ora_programare'])); ?></span>
                            </div>
                            <div class="appointment-details">
                                <div class="doctor-info">
                                    <strong>Dr. <?php echo htmlspecialchars($appointment['medic_nume'] . ' ' . $appointment['medic_prenume']); ?></strong>
                                    <div class="specialty"><?php echo htmlspecialchars($appointment['medic_specializare']); ?></div>
                                    <div class="consultation-reason"><?php echo htmlspecialchars($appointment['motiv_consultatie']); ?></div>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <button class="edit-appointment-btn" onclick="openEditModal(<?php echo $appointment['id']; ?>, '<?php echo $appointment['medic_id']; ?>')">
                                        Editează
                                    </button>
                                    <form method="POST" action="deleteAppointment.php" class="delete-appointment-form">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <button type="submit" class="edit-appointment-btn" style="background: #ff4444; color: white;">Șterge</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-appointments">
                    Nu aveți programări viitoare.
                </div>
            <?php endif; ?>

            <h2>Istoric Consultații</h2>
            <?php if ($consultations->num_rows > 0): ?>
                <table class="consultations-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Ora</th>
                            <th>Medic</th>
                            <th>Specializare</th>
                            <th>Diagnostic</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($consultation = $consultations->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d.m.Y', strtotime($consultation['Data'])); ?></td>
                                <td><?php echo date('H:i', strtotime($consultation['Ora'])); ?></td>
                                <td>Dr. <?php echo htmlspecialchars($consultation['medic_nume'] . ' ' . $consultation['medic_prenume']); ?></td>
                                <td><?php echo htmlspecialchars($consultation['medic_specializare']); ?></td>
                                <td><?php echo htmlspecialchars($consultation['denumire_diagnostic'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="detaliiConsultatiePacient.php?id=<?php echo $consultation['ID']; ?>" class="view-details-btn">
                                        Detalii
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-consultations">
                    Nu aveți consultații înregistrate.
                </div>
            <?php endif; ?>

            <h2>Documente Medicale</h2>
            <div class="documents-section">
                <div class="upload-section">
                    <h3>Încarcă Document Nou</h3>
                    <form action="upload_document.php" method="POST" enctype="multipart/form-data" class="upload-form">
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

                <div class="documents-list">
                    <h3>Documente Încărcate</h3>
                    <?php
                    $documentsSql = "SELECT * FROM documente WHERE pacient_id = ? ORDER BY data_upload DESC";
                    $stmt = $conn->prepare($documentsSql);
                    $stmt->bind_param("i", $id_pacient);
                    $stmt->execute();
                    $documents = $stmt->get_result();
                    ?>
                    
                    <?php if ($documents->num_rows > 0): ?>
                        <div class="documents-grid">
                            <?php while ($document = $documents->fetch_assoc()): ?>
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
                                        <button onclick="deleteDocument(<?php echo $document['id']; ?>)" class="delete-btn">Șterge</button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-documents">
                            Nu aveți documente încărcate.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="editAppointmentModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeEditModal()">&times;</span>
            <h2>Modifică Programarea</h2>
            <form id="editAppointmentForm" method="POST" action="updateAppointment.php">
                <input type="hidden" id="appointmentId" name="appointmentId">
                <input type="hidden" id="medicId" name="medic_id">
                <input type="hidden" id="pacientId" name="pacient_id" value="<?php echo $id_pacient; ?>">
                <input type="hidden" id="selectedTime" name="ora_programare">
                <input type="hidden" id="motivConsultatie" name="motiv_consultatie" value="Consultație programată">
                
                <div class="form-group">
                    <label for="appointmentDate">Data:</label>
                    <input type="date" id="appointmentDate" name="data_programare" required>
                </div>

                <div class="form-group">
                    <label>Ora:</label>
                    <div id="timeSlots" class="time-slots-grid">
                    </div>
                </div>

                <button type="submit" class="submit-btn">Salvează Modificările</button>
            </form>
        </div>
    </div>

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

        /**
         * Deschide modalul pentru editarea programării
         * @param {number} appointmentId - ID-ul programării de editat
         * @param {string} medicId - ID-ul medicului
         */
        function openEditModal(appointmentId, medicId) {
            const modal = document.getElementById('editAppointmentModal');
            document.getElementById('appointmentId').value = appointmentId;
            document.getElementById('medicId').value = medicId;
            
            // Set min date to today
            const today = new Date().toISOString().split('T')[0];
            const dateInput = document.getElementById('appointmentDate');
            dateInput.min = today;
            
            modal.style.display = 'block';
        }

        /**
         * Închide modalul pentru editarea programării
         */
        function closeEditModal() {
            const modal = document.getElementById('editAppointmentModal');
            modal.style.display = 'none';
        }

        /**
         * Încarcă sloturile de timp disponibile pentru medicul selectat
         * @param {string} medicId - ID-ul medicului
         */
        function loadAvailableTimeSlots(medicId) {
            const dateInput = document.getElementById('appointmentDate');
            const date = dateInput.value;
            if (!date) return;

            const currentDate = new Date();
            const selectedDate = new Date(date);
            const isToday = selectedDate.toDateString() === currentDate.toDateString();

            fetch(`getAvailableTimeSlots.php?medic_id=${medicId}&date=${date}`)
                .then(response => response.json())
                .then(data => {
                    // Log debug information
                    if (data[0] && data[0]._debug) {
                        console.log('Debug Information:', {
                            'Raw times from database': data[0]._debug.raw_times_from_db,
                            'SQL Query': data[0]._debug.query,
                            'Parameters': data[0]._debug.params,
                            'Full SQL': data[0]._debug.sql
                        });
                    }

                    const timeSlotsContainer = document.getElementById('timeSlots');
                    timeSlotsContainer.innerHTML = '';

                    data.forEach(slot => {
                        const timeSlot = document.createElement('div');
                        const [hours, minutes] = slot.time.split(':');
                        const slotTime = new Date(selectedDate);
                        slotTime.setHours(parseInt(hours), parseInt(minutes));

                        const isPastTime = isToday && slotTime < currentDate;
                        const isUnavailable = !slot.available || isPastTime;

                        // Set classes based on availability
                        if (!slot.available) {
                            timeSlot.className = 'time-slot occupied';
                        } else if (isPastTime) {
                            timeSlot.className = 'time-slot unavailable';
                        } else {
                            timeSlot.className = 'time-slot';
                            timeSlot.onclick = () => selectTimeSlot(timeSlot);
                        }

                        timeSlot.textContent = slot.time;
                        timeSlotsContainer.appendChild(timeSlot);
                    });
                })
                .catch(error => {
                    console.error('Error loading time slots:', error);
                });
        }

        /**
         * Selectează un slot de timp pentru programare
         * @param {HTMLElement} element - Elementul slot de timp selectat
         */
        function selectTimeSlot(element) {
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('selected');
            });
            element.classList.add('selected');
            document.getElementById('selectedTime').value = element.textContent;
        }

        document.getElementById('editAppointmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const selectedTime = document.getElementById('selectedTime').value;
            if (!selectedTime) {
                alert('Vă rugăm să selectați o oră.');
                return;
            }

            this.submit();
        });

        window.onclick = function(event) {
            const modal = document.getElementById('editAppointmentModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }

        document.getElementById('appointmentDate').addEventListener('change', function(e) {
            const medicId = document.getElementById('medicId').value;
            loadAvailableTimeSlots(medicId);
        });

        document.querySelectorAll('.delete-appointment-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!confirm('Sigur doriți să ștergeți această programare?')) return;
                const formData = new FormData(form);
                fetch('deleteAppointment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Eroare la ștergere.');
                    }
                })
                .catch(() => alert('Eroare la ștergere.'));
            });
        });

        /**
         * Șterge un document medical
         * @param {number} documentId - ID-ul documentului de șters
         */
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

        const typeLabels = {
            'analize': 'Analize medicale',
            'imagistica_medicala': 'Imagistica medicala',
            'observatie': 'Foaie de observatie',
            'scrisori': 'Scrisoare medicala',
            'externari': 'Bilet de externare',
            'alte': 'Altele'
        };

        document.getElementById('analyze_btn').addEventListener('click', function() {
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
    </script>

    <footer>
        <div class="wrapper">
            <p>EasyMed © 2024</p>
        </div>
    </footer>
</body>
</html>
