<?php
/**
 * Pagină pentru afișarea detaliilor consultațiilor pentru pacienți
 * 
 * Această pagină oferă o interfață pentru pacienți pentru vizualizarea consultațiilor lor:
 * - Afișează informațiile complete ale consultației (medic, diagnostic, simptome)
 * - Afișează biletele de trimitere către specialiști
 * - Afișează biletele pentru investigații medicale
 * - Afișează rețetele medicale și medicamentele prescrise
 * - Implementează autorizare pentru a permite accesul doar la propriile consultații
 * - Interfață read-only pentru pacienți (fără funcționalități de editare)
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

    include('db_connection.php');
    session_start();

    // Verifică dacă utilizatorul este autentificat
    if (!isset($_SESSION['user_id'])) {
        header("Location: pacientiLogin.php");
        exit();
    }

    // Verifică dacă s-a furnizat ID-ul consultației
    if (!isset($_GET['id'])) {
        header("Location: istoricPacient.php");
        exit();
    }

    // Extrage ID-ul consultației și utilizatorului
    $consultatie_id = $_GET['id'];
    $utilizator_id = $_SESSION['user_id'];

    // Verifică dacă utilizatorul este pacient și obține CNP-ul
    $sqlPatient = "SELECT CNP FROM pacienti WHERE utilizator_id = ?";
    $stmtPatient = $conn->prepare($sqlPatient);
    $stmtPatient->bind_param("i", $utilizator_id);
    $stmtPatient->execute();
    $resultPatient = $stmtPatient->get_result();
    
    // Verifică dacă utilizatorul este pacient valid
    if ($resultPatient->num_rows === 0) {
        header("Location: istoricPacient.php");
        exit();
    }
    
    $patient_cnp = $resultPatient->fetch_assoc()['CNP'];
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
                      WHERE c.ID = ? AND c.CNPPacient = ?
                      GROUP BY c.ID";
    $stmtConsultatie = $conn->prepare($sqlConsultatie);
    $stmtConsultatie->bind_param("is", $consultatie_id, $patient_cnp);
    $stmtConsultatie->execute();
    $resultConsultatie = $stmtConsultatie->get_result();

    // Verifică dacă consultația există și aparține pacientului
    if ($resultConsultatie->num_rows === 0) {
        header("Location: istoricPacient.php");
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
                line-height: 1.4;
                word-wrap: break-word;
            }

            /* Card-uri pentru afișarea datelor structurate */
            .card {
                background: #1a2228;
                border-radius: 8px;
                margin-bottom: 1rem;
                overflow: hidden;
                width: 100%;
            }

            /* Header-ul pentru card-uri */
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

            /* Conținutul card-urilor */
            .card-content {
                padding: 1rem;
            }

            /* Butonul de înapoi */
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

            /* Stilizarea dropdown-ului pentru profil */
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
            <div class="details-container">
                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <a href="istoricPacient.php" class="back-btn">← Înapoi la istoric</a>
                </div>

                <div class="consultation-info">
                    <h2>Detalii Consultație</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">ID Consultație</div>
                            <div class="info-value"><?php echo htmlspecialchars($consultatie['ID']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Data</div>
                            <div class="info-value"><?php echo date('d.m.Y', strtotime($consultatie['Data'])); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Ora</div>
                            <div class="info-value"><?php echo date('H:i', strtotime($consultatie['Ora'])); ?></div>
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
                        <div class="info-value"><?php echo nl2br(htmlspecialchars($consultatie['Simptome'] ?? 'Nu există simptome înregistrate')); ?></div>
                    </div>

                    <div class="details-section">
                        <h3>Diagnostic</h3>
                        <div class="info-value">
                            <?php 
                            if ($consultatie['cod_diagnostic']) {
                                echo htmlspecialchars($consultatie['cod_diagnostic'] . ' - ' . $consultatie['nume_diagnostic']);
                            } else {
                                echo htmlspecialchars($consultatie['Diagnostic']);
                            }
                            ?>
                        </div>
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
        </script>
    </body>
</html>

<?php
    $conn->close();
?> 