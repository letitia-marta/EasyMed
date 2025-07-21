<?php
    include('db_connection.php');
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: pacientiLogin.php");
        exit();
    }

    $utilizator_id = $_SESSION['user_id'];

    $sqlPatient = "SELECT id, nume, prenume, CNP, sex FROM pacienti WHERE utilizator_id = ?";
    $stmtPatient = $conn->prepare($sqlPatient);
    $stmtPatient->bind_param("i", $utilizator_id);
    $stmtPatient->execute();
    $resultPatient = $stmtPatient->get_result();
    
    if ($resultPatient->num_rows === 0) {
        header("Location: dashboardPacienti.php");
        exit();
    }
    
    $pacient = $resultPatient->fetch_assoc();
    $stmtPatient->close();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'add') {
                $pacient_relat_id = $_POST['pacient_relat_id'];
                $tip_relatie_id = $_POST['tip_relatie_id'];

                $sqlAdd = "INSERT INTO relatii_pacienti (pacient_id, pacient_relat_id, tip_relatie_id) 
                          VALUES (?, ?, ?)";
                $stmtAdd = $conn->prepare($sqlAdd);
                $stmtAdd->bind_param("iii", $pacient['id'], $pacient_relat_id, $tip_relatie_id);
                
                try {
                    $stmtAdd->execute();
                    $success_message = "Relația a fost adăugată cu succes!";
                } catch (Exception $e) {
                    $error_message = "Eroare la adăugarea relației: " . $e->getMessage();
                }
                $stmtAdd->close();
            } elseif ($_POST['action'] === 'delete') {
                $relatie_id = $_POST['relatie_id'];

                $sqlDelete = "DELETE FROM relatii_pacienti WHERE id = ? AND pacient_id = ?";
                $stmtDelete = $conn->prepare($sqlDelete);
                $stmtDelete->bind_param("ii", $relatie_id, $pacient['id']);
                
                try {
                    $stmtDelete->execute();
                    $success_message = "Relația a fost ștearsă cu succes!";
                } catch (Exception $e) {
                    $error_message = "Eroare la ștergerea relației: " . $e->getMessage();
                }
                $stmtDelete->close();
            }
        }
    }

    $sqlTipuri = "SELECT * FROM tipuri_relatii ORDER BY denumire";
    $resultTipuri = $conn->query($sqlTipuri);
    $tipuri_relatii = $resultTipuri->fetch_all(MYSQLI_ASSOC);

    $sqlPacienti = "SELECT id, nume, prenume, CNP FROM pacienti WHERE id != ? ORDER BY nume, prenume";
    $stmtPacienti = $conn->prepare($sqlPacienti);
    $stmtPacienti->bind_param("i", $pacient['id']);
    $stmtPacienti->execute();
    $resultPacienti = $stmtPacienti->get_result();
    $pacienti = $resultPacienti->fetch_all(MYSQLI_ASSOC);
    $stmtPacienti->close();

    $sqlRelatii = "SELECT 
        r.id,
        r.pacient_id,
        r.pacient_relat_id,
        t.denumire as tip_relatie,
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
?>

<!DOCTYPE html>
<html lang="ro">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Relații Pacient - EasyMed</title>
        <link rel="stylesheet" href="style.css">
        <style>
            .container {
                max-width: 1200px;
                margin: 2rem auto;
                padding: 2rem;
                background: #13181d;
                border-radius: 10px;
                color: white;
            }

            .section {
                background: #2A363F;
                padding: 2rem;
                border-radius: 8px;
                margin-bottom: 2rem;
            }

            .section h2 {
                color: #5cf9c8;
                margin-top: 0;
                margin-bottom: 1.5rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .form-group label {
                display: block;
                margin-bottom: 0.5rem;
                color: #888;
            }

            .form-group select {
                width: 100%;
                padding: 0.5rem;
                border: 1px solid #3A4A5F;
                border-radius: 4px;
                background: #1a2228;
                color: white;
            }

            .btn {
                display: inline-block;
                padding: 0.8rem 1.5rem;
                background: #5cf9c8;
                color: #13181d;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                text-decoration: none;
                font-weight: bold;
                transition: background-color 0.3s;
            }

            .btn:hover {
                background: #4ad8b7;
            }

            .btn-danger {
                background: #ff4444;
                color: white;
            }

            .btn-danger:hover {
                background: #cc0000;
            }

            .alert {
                padding: 1rem;
                border-radius: 4px;
                margin-bottom: 1rem;
            }

            .alert-success {
                background: #28a745;
                color: white;
            }

            .alert-danger {
                background: #dc3545;
                color: white;
            }

            .relationships-list {
                width: 100%;
                border-collapse: collapse;
            }

            .relationships-list th,
            .relationships-list td {
                padding: 1rem;
                text-align: left;
                border-bottom: 1px solid #3A4A5F;
            }

            .relationships-list th {
                color: #5cf9c8;
                font-weight: bold;
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

            .popup {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
                z-index: 1000;
            }

            .popup-content {
                position: relative;
                background-color: #13181d;
                margin: 5% auto;
                padding: 20px;
                width: 80%;
                max-width: 800px;
                border-radius: 8px;
                max-height: 80vh;
                overflow-y: auto;
            }

            .close-popup {
                position: absolute;
                right: 20px;
                top: 10px;
                font-size: 24px;
                cursor: pointer;
                color: #5cf9c8;
            }

            .search-container {
                margin-bottom: 20px;
            }

            .search-container input {
                width: 100%;
                padding: 10px;
                border: 1px solid #3A4A5F;
                border-radius: 4px;
                background: #1a2228;
                color: white;
            }

            .patient-list {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 15px;
            }

            .patient-card {
                background: #2A363F;
                padding: 15px;
                border-radius: 8px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .patient-card:hover {
                background: #3A4A5F;
            }

            .patient-card h3 {
                margin: 0 0 10px 0;
                color: #5cf9c8;
            }

            .patient-card p {
                margin: 5px 0;
                color: #888;
            }

            .select-patient-btn {
                display: inline-block;
                padding: 8px 15px;
                background: #5cf9c8;
                color: #13181d;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                text-decoration: none;
                font-weight: bold;
                transition: background-color 0.3s;
            }

            .select-patient-btn:hover {
                background: #4ad8b7;
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
            <div class="container">
                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <a href="dashboardPacienti.php" class="back-btn">← Înapoi la dashboard</a>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <div class="section">
                    <h2>Adaugă Relație Nouă</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label for="pacient_relat_id">Pacient cu care doriți să stabiliți relația</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" id="selected_patient" readonly placeholder="Selectează un pacient" style="flex-grow: 1;">
                                <input type="hidden" name="pacient_relat_id" id="pacient_relat_id" required>
                                <button type="button" class="btn" onclick="openPatientPopup()">Selectează Pacient</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tip_relatie_id">Tip Relație (din perspectiva dvs.)</label>
                            <select name="tip_relatie_id" id="tip_relatie_id" required>
                                <option value="">Selectează tipul de relație</option>
                                <?php
                                $sex = $pacient['sex'];
                                foreach ($tipuri_relatii as $tip) {
                                    $denumire = $tip['denumire'];
                                    if ($sex === 'F' && !in_array($denumire, ['Mama', 'Fiica', 'Sora'])) continue;
                                    if ($sex === 'M' && !in_array($denumire, ['Tata', 'Fiu', 'Frate'])) continue;
                                ?>
                                    <option value="<?php echo $tip['id']; ?>">
                                        <?php echo htmlspecialchars($denumire); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <small style="color: #888; display: block; margin-top: 5px;">
                                Exemplu: dacă selectați "Mamă", înseamnă că sunteți mama persoanei selectate
                            </small>
                        </div>
                        <button type="submit" class="btn">Adaugă Relație</button>
                    </form>
                </div>

                <div class="section">
                    <h2>Relații Existente</h2>
                    <?php if (empty($relatii)): ?>
                        <p>Nu există relații înregistrate.</p>
                    <?php else: ?>
                        <table class="relationships-list">
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
                                    <tr>
                                        <td>
                                            <?php 
                                                $isRelatedPatient = $relatie['pacient_relat_id'] == $pacient['id'];
                                                
                                                if ($isRelatedPatient) {
                                                    if ($relatie['tip_relatie'] === 'Frate') {
                                                        echo ($relatie['sex'] === 'M') ? 'Frate' : 'Soră';
                                                    } else if ($relatie['tip_relatie'] === 'Sora') {
                                                        echo ($relatie['sex'] === 'M') ? 'Frate' : 'Soră';
                                                    } else {
                                                        echo htmlspecialchars($relatie['tip_relatie']);
                                                    }
                                                } else {
                                                    switch($relatie['tip_relatie']) {
                                                        case 'Mama':
                                                        case 'Tata':
                                                            echo ($relatie['sex'] === 'M') ? 'Fiu' : 'Fiică';
                                                            break;
                                                        case 'Frate':
                                                        case 'Sora':
                                                            echo ($relatie['sex'] === 'M') ? 'Frate' : 'Soră';
                                                            break;
                                                        case 'Fiu':
                                                            echo 'Mama';
                                                            break;
                                                        case 'Fiica':
                                                            echo 'Mama';
                                                            break;
                                                        default:
                                                            echo htmlspecialchars($relatie['tip_relatie']);
                                                    }
                                                }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($relatie['nume']); ?></td>
                                        <td><?php echo htmlspecialchars($relatie['prenume']); ?></td>
                                        <td><?php echo htmlspecialchars($relatie['CNP']); ?></td>
                                        <td>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="relatie_id" value="<?php echo $relatie['id']; ?>">
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Sigur doriți să ștergeți această relație?')">
                                                    Șterge
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="patientPopup" class="popup">
            <div class="popup-content">
                <span class="close-popup" onclick="closePatientPopup()">&times;</span>
                <h2>Selectează Pacient</h2>
                <div class="search-container">
                    <input type="text" id="patientSearch" placeholder="Caută după nume, prenume sau CNP..." onkeyup="filterPatients()">
                </div>
                <div class="patient-list">
                    <?php foreach ($pacienti as $p): ?>
                        <div class="patient-card" onclick="selectPatient(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars($p['nume'] . ' ' . $p['prenume'] . ' (CNP: ' . $p['CNP'] . ')'); ?>')">
                            <h3><?php echo htmlspecialchars($p['nume'] . ' ' . $p['prenume']); ?></h3>
                            <p>CNP: <?php echo htmlspecialchars($p['CNP']); ?></p>
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

            function openPatientPopup() {
                document.getElementById('patientPopup').style.display = 'block';
            }

            function closePatientPopup() {
                document.getElementById('patientPopup').style.display = 'none';
            }

            function selectPatient(id, name) {
                document.getElementById('pacient_relat_id').value = id;
                document.getElementById('selected_patient').value = name;
                closePatientPopup();
            }

            function filterPatients() {
                const input = document.getElementById('patientSearch');
                const filter = input.value.toLowerCase();
                const cards = document.getElementsByClassName('patient-card');

                for (let card of cards) {
                    const text = card.textContent.toLowerCase();
                    card.style.display = text.includes(filter) ? '' : 'none';
                }
            }

            window.onclick = function(event) {
                const popup = document.getElementById('patientPopup');
                if (event.target == popup) {
                    closePatientPopup();
                }
            }
        </script>
    </body>
</html>

<?php
    $conn->close();
?> 