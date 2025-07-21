<?php
    include('db_connection.php');
    session_start();

    if (!isset($_SESSION['user_id']))
    {
        header("Location: login.php");
        exit();
    }

    $utilizator_id = $_SESSION['user_id'];

    $search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    $patients_per_page = 10;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($current_page - 1) * $patients_per_page;

    $sqlDoctor = "SELECT id FROM medici WHERE utilizator_id = ?";
    $stmtDoctor = $conn->prepare($sqlDoctor);
    $stmtDoctor->bind_param("i", $utilizator_id);
    $stmtDoctor->execute();
    $stmtDoctor->bind_result($doctor_id);
    $stmtDoctor->fetch();
    $stmtDoctor->close();

    if (!empty($search_query)) {
        $sqlCount = "SELECT COUNT(*) as total
                     FROM pacienti p
                     INNER JOIN doctor_pacient dp ON p.id = dp.pacient_id
                     WHERE dp.doctor_id = ? 
                     AND (p.nume LIKE ? OR p.prenume LIKE ? OR CONCAT(p.nume, ' ', p.prenume) LIKE ?)";
        $search_param = "%$search_query%";
        $stmtCount = $conn->prepare($sqlCount);
        $stmtCount->bind_param("isss", $doctor_id, $search_param, $search_param, $search_param);
    } else {
        $sqlCount = "SELECT COUNT(*) as total
                     FROM pacienti p
                     INNER JOIN doctor_pacient dp ON p.id = dp.pacient_id
                     WHERE dp.doctor_id = ?";
        $stmtCount = $conn->prepare($sqlCount);
        $stmtCount->bind_param("i", $doctor_id);
    }
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $totalPatients = $resultCount->fetch_assoc()['total'];
    $stmtCount->close();

    $total_pages = ceil($totalPatients / $patients_per_page);

    if ($current_page < 1) {
        $current_page = 1;
    } elseif ($current_page > $total_pages && $total_pages > 0) {
        $current_page = $total_pages;
    }

    if (!empty($search_query)) {
        $sql = "SELECT p.id, p.nume, p.prenume, p.CNP, p.sex, p.data_nasterii, p.grupa_sanguina,
                (SELECT COUNT(*) FROM consultatii c WHERE c.CNPPacient = p.CNP) as numar_consultatii
                FROM pacienti p
                INNER JOIN doctor_pacient dp ON p.id = dp.pacient_id
                WHERE dp.doctor_id = ? 
                AND (p.nume LIKE ? OR p.prenume LIKE ? OR CONCAT(p.nume, ' ', p.prenume) LIKE ?)
                ORDER BY p.nume, p.prenume
                LIMIT ? OFFSET ?";
        $search_param = "%$search_query%";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssii", $doctor_id, $search_param, $search_param, $search_param, $patients_per_page, $offset);
    } else {
        $sql = "SELECT p.id, p.nume, p.prenume, p.CNP, p.sex, p.data_nasterii, p.grupa_sanguina,
                (SELECT COUNT(*) FROM consultatii c WHERE c.CNPPacient = p.CNP) as numar_consultatii
                FROM pacienti p
                INNER JOIN doctor_pacient dp ON p.id = dp.pacient_id
                WHERE dp.doctor_id = ?
                ORDER BY p.nume, p.prenume
                LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $doctor_id, $patients_per_page, $offset);
    }
    $stmt->execute();
    $result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ro">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Lista Pacienți - EasyMed</title>
        <link rel="stylesheet" href="style.css">
        <style>
            .lista-container {
                max-width: 1400px;
                margin: 2rem auto;
                padding: 2rem;
                background: #13181d;
                border-radius: 10px;
                color: white;
            }

            .patients-table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            .patients-table th,
            .patients-table td {
                padding: 1rem;
                text-align: left;
                border-bottom: 1px solid #2A363F;
                word-wrap: break-word;
            }

            .patients-table th:last-child,
            .patients-table td:last-child {
                min-width: 120px;
                text-align: center;
            }

            .patients-table th {
                background: #2A363F;
            }

            .patients-table tr:hover {
                background: #1a2128;
            }

            .view-details-btn {
                padding: 0.5rem 1rem;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.3s;
                min-width: 120px;
                margin: 0 2px;
                white-space: nowrap;
                background: #5cf9c8;
                color: #13181d;
                text-decoration: none;
                display: inline-block;
                font-weight: 500;
            }

            .view-details-btn:hover {
                background: #4ad8b7;
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

            .no-patients {
                text-align: center;
                padding: 2rem;
                color: #888;
                font-size: 1.2rem;
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

            .lista-container h2 {
                color: #5cf9c8;
                margin-bottom: 1.5rem;
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
                font-weight: bold;
                font-size: 1rem;
            }
            .back-btn:hover {
                background: #3A4A5F;
            }

            .pagination-container {
                display: flex;
                justify-content: center;
                align-items: center;
                margin-top: 2rem;
                gap: 0.5rem;
            }

            .pagination-info {
                color: #888;
                margin: 0 1rem;
                font-size: 0.9rem;
            }

            .pagination-btn {
                padding: 0.5rem 1rem;
                background: #2A363F;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                text-decoration: none;
                font-size: 0.9rem;
                transition: background-color 0.3s;
            }

            .pagination-btn:hover {
                background: #3A4A5F;
            }

            .pagination-btn.active {
                background: #5cf9c8;
                color: black;
            }

            .pagination-btn.disabled {
                background: #1a2128;
                color: #666;
                cursor: not-allowed;
            }

            .pagination-btn.disabled:hover {
                background: #1a2128;
            }

            .pagination-dots {
                color: #888;
                padding: 0.5rem;
                font-size: 0.9rem;
            }

            .search-container {
                background: #2A363F;
                padding: 1.5rem;
                border-radius: 8px;
                margin-bottom: 2rem;
            }

            .search-form {
                display: flex;
                gap: 1rem;
                align-items: center;
                flex-wrap: wrap;
            }

            .search-input {
                flex: 1;
                min-width: 250px;
                padding: 0.8rem 1rem;
                border: 1px solid #5cf9c8;
                border-radius: 4px;
                background: #13181d;
                color: white;
                font-size: 1rem;
            }

            .search-input:focus {
                outline: none;
                border-color: #4ad8b7;
                box-shadow: 0 0 0 2px rgba(92, 249, 200, 0.2);
            }

            .search-input::placeholder {
                color: #888;
            }

            .search-btn {
                padding: 0.8rem 1.5rem;
                background: #5cf9c8;
                color: black;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 500;
                font-size: 1rem;
                transition: background-color 0.3s;
            }

            .search-btn:hover {
                background: #4ad8b7;
            }

            .clear-btn {
                padding: 0.8rem 1.5rem;
                background: #2A363F;
                color: white;
                border: 1px solid #5cf9c8;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 500;
                font-size: 1rem;
                transition: background-color 0.3s;
            }

            .clear-btn:hover {
                background: #3A4A5F;
            }

            .search-results-info {
                color: #888;
                font-size: 0.9rem;
                margin-bottom: 1rem;
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

        <section class="content">
            <div class="wrapper">
                <div class="lista-container">
                    <a href="dashboardMedici.php" class="back-btn">&larr; Înapoi la dashboard</a>
                    <h2>Lista Pacienți</h2>
                    
                    <div class="search-container">
                        <form method="GET" class="search-form">
                            <input type="text" 
                                   name="search" 
                                   class="search-input" 
                                   placeholder="Căutați pacienți după nume..." 
                                   value="<?php echo htmlspecialchars($search_query); ?>">
                            <button type="submit" class="search-btn">🔍 Caută</button>
                            <?php if (!empty($search_query)): ?>
                                <a href="listaPacienti.php" class="clear-btn">❌ Șterge</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <?php if (!empty($search_query)): ?>
                        <div class="search-results-info">
                            Rezultate pentru: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong>
                            (<?php echo $totalPatients; ?> pacienți găsiți)
                        </div>
                    <?php endif; ?>

                    <?php if ($result->num_rows > 0): ?>
                        <table class="patients-table">
                            <thead>
                                <tr>
                                    <th>Nume</th>
                                    <th>CNP</th>
                                    <th>Sex</th>
                                    <th>Data nașterii</th>
                                    <th>Grupa sanguină</th>
                                    <th>Nr. consultații</th>
                                    <th>Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['nume'] . ' ' . $row['prenume']); ?></td>
                                        <td><?php echo htmlspecialchars($row['CNP']); ?></td>
                                        <td><?php echo $row['sex'] === 'M' ? 'Masculin' : 'Feminin'; ?></td>
                                        <td><?php echo date('d.m.Y', strtotime($row['data_nasterii'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['grupa_sanguina']); ?></td>
                                        <td><?php echo $row['numar_consultatii']; ?></td>
                                        <td>
                                            <?php echo '<a href="detaliiPacient.php?cnp=' . $row['CNP'] . '" class="view-details-btn">Detalii</a>'; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <?php if ($total_pages > 1): ?>
                            <div class="pagination-container">
                                <?php
                                $query_params = [];
                                if (!empty($search_query)) {
                                    $query_params['search'] = $search_query;
                                }
                                
                                if ($current_page > 1): 
                                    $query_params['page'] = $current_page - 1;
                                    $prev_url = '?' . http_build_query($query_params);
                                    unset($query_params['page']);
                                ?>
                                    <a href="<?php echo $prev_url; ?>" class="pagination-btn">← Anterior</a>
                                <?php else: ?>
                                    <span class="pagination-btn disabled">← Anterior</span>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $current_page + 2);

                                if ($start_page > 1): 
                                    $query_params['page'] = 1;
                                    $first_url = '?' . http_build_query($query_params);
                                    unset($query_params['page']);
                                ?>
                                    <a href="<?php echo $first_url; ?>" class="pagination-btn">1</a>
                                    <?php if ($start_page > 2): ?>
                                        <span class="pagination-dots">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <?php if ($i == $current_page): ?>
                                        <span class="pagination-btn active"><?php echo $i; ?></span>
                                    <?php else: 
                                        $query_params['page'] = $i;
                                        $page_url = '?' . http_build_query($query_params);
                                        unset($query_params['page']);
                                    ?>
                                        <a href="<?php echo $page_url; ?>" class="pagination-btn"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php
                                if ($end_page < $total_pages): 
                                    $query_params['page'] = $total_pages;
                                    $last_url = '?' . http_build_query($query_params);
                                    unset($query_params['page']);
                                ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                        <span class="pagination-dots">...</span>
                                    <?php endif; ?>
                                    <a href="<?php echo $last_url; ?>" class="pagination-btn"><?php echo $total_pages; ?></a>
                                <?php endif; ?>

                                <?php
                                if ($current_page < $total_pages): 
                                    $query_params['page'] = $current_page + 1;
                                    $next_url = '?' . http_build_query($query_params);
                                    unset($query_params['page']);
                                ?>
                                    <a href="<?php echo $next_url; ?>" class="pagination-btn">Următor →</a>
                                <?php else: ?>
                                    <span class="pagination-btn disabled">Următor →</span>
                                <?php endif; ?>

                                <div class="pagination-info">
                                    Pagina <?php echo $current_page; ?> din <?php echo $total_pages; ?>
                                    (<?php echo $totalPatients; ?> pacienți în total)
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-patients">
                            Nu aveți pacienți alocați momentan.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <div id="patientModal" class="modal">
            <div class="modal-content">
                <span class="close-modal" onclick="closeModal()">&times;</span>
                <div id="patientDetails"></div>
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

            function showDetails(patient) {
                const modal = document.getElementById('patientModal');
                const detailsContainer = document.getElementById('patientDetails');
                
                const birthDate = new Date(patient.data_nasterii);
                const today = new Date();
                const age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }

                const detailsHTML = `
                    <div class="details-section">
                        <h3>Informații Pacient</h3>
                        <div class="details-grid">
                            <div class="detail-item">
                                <div class="detail-label">Nume complet</div>
                                <div class="detail-value">${patient.nume} ${patient.prenume}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">CNP</div>
                                <div class="detail-value">${patient.CNP}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Sex</div>
                                <div class="detail-value">${patient.sex === 'M' ? 'Masculin' : 'Feminin'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Data nașterii</div>
                                <div class="detail-value">${new Date(patient.data_nasterii).toLocaleDateString('ro-RO')}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Vârstă</div>
                                <div class="detail-value">${age} ani</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Grupa sanguină</div>
                                <div class="detail-value">${patient.grupa_sanguina}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Număr consultații</div>
                                <div class="detail-value">${patient.numar_consultatii}</div>
                            </div>
                        </div>
                    </div>`;

                detailsContainer.innerHTML = detailsHTML;
                modal.style.display = 'block';
            }

            function closeModal() {
                const modal = document.getElementById('patientModal');
                modal.style.display = 'none';
            }

            window.onclick = function(event) {
                const modal = document.getElementById('patientModal');
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        </script>
    </body>
</html>

<?php
    $conn->close();
?>