<?php
    session_start();
    require_once 'db_connection.php';

    if (!isset($_SESSION['user_id']))
    {
        header("Location: mediciLogin.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM medici WHERE utilizator_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctor = $result->fetch_assoc();
    $id_medic = $doctor['id'];
    $stmt->close();

    $consultations_per_page = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $consultations_per_page;

    $count_sql = "SELECT COUNT(DISTINCT c.ID) as total 
                  FROM consultatii c 
                  WHERE c.id_medic = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $id_medic);
    $count_stmt->execute();
    $total_result = $count_stmt->get_result();
    $total_consultations = $total_result->fetch_assoc()['total'];
    $total_pages = ceil($total_consultations / $consultations_per_page);
    $count_stmt->close();

    $sql = "SELECT c.*, 
               p.nume AS pacient_nume, 
               p.prenume AS pacient_prenume,
               cb.cod_999 AS cod_diagnostic,
               cb.denumire_boala AS denumire_diagnostic,
               GROUP_CONCAT(DISTINCT bt.Cod) AS trimiteri,
               GROUP_CONCAT(DISTINCT bi.CodBilet) AS investigatii,
               GROUP_CONCAT(DISTINCT rm.Cod) AS retete
            FROM consultatii c
            INNER JOIN pacienti p ON c.CNPPacient = p.CNP
        LEFT JOIN coduri_boli cb ON c.Diagnostic = cb.cod_999
        LEFT JOIN bilete_trimitere bt ON c.ID = bt.Consultatie
        LEFT JOIN bilete_investigatii bi ON c.ID = bi.Consultatie
        LEFT JOIN retete_medicale rm ON c.ID = rm.Consultatie
        WHERE c.id_medic = ?
            GROUP BY c.ID
        ORDER BY c.Data DESC, c.Ora DESC, c.ID DESC
        LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $id_medic, $consultations_per_page, $offset);
    $stmt->execute();
    $consultations = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registru Consultații - EasyMed</title>
    <link rel="stylesheet" href="style.css">
    <style>
            .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 8px;
                align-items: center;
                width: 100%;
                padding: 0 10px;
            }

        .registru-container {
                max-width: 1400px;
            margin: 2rem auto;
            padding: 2rem;
            background: #13181d;
            border-radius: 10px;
            color: white;
        }

        .add-consultation-btn {
            background: #5cf9c8;
            color: black;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin-bottom: 2rem;
            transition: background-color 0.3s;
        }

        .add-consultation-btn:hover {
            background: #4ad7a8;
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

        .consultations-table th:first-child,
        .consultations-table td:first-child {
            width: 80px;
        }

        .consultations-table th:nth-child(3),
        .consultations-table td:nth-child(3) {
            width: 100px;
        }

        .consultations-table th:last-child,
        .consultations-table td:last-child {
            min-width: 250px;
            text-align: center;
        }

        .consultations-table th {
            background: #2A363F;
        }

        .consultations-table tr:hover {
            background: #1a2128;
        }

            .action-buttons {
                display: flex;
                gap: 8px;
                justify-content: center;
                width: 100%;
                padding: 0 10px;
            }

            .view-details-btn, .remove-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
                min-width: 120px;
                margin: 0 2px;
                white-space: nowrap;
            }

            .view-details-btn {
                background: #5cf9c8;
                color: #13181d;
        }

        .view-details-btn:hover {
                background: #4ad7a8;
        }

            .remove-btn {
                background: #ff4444;
                color: white;
            }

            .remove-btn:hover {
                background: #cc0000;
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

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: white;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #2A363F;
            border-radius: 4px;
            background: #2A363F;
            color: white;
        }

        .form-group textarea {
            min-height: 100px;
        }

        .optional-section {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #2A363F;
        }

        .add-item-btn {
            background: #2A363F;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 1rem;
        }

        .patient-search-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
        }

        .patient-search-content {
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

        .patient-search-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .patient-search-box {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .patient-search-box input {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #2A363F;
            border-radius: 4px;
            background: #2A363F;
            color: white;
        }

        .patient-sort-options {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .patient-sort-options select {
            padding: 0.5rem;
            border: 1px solid #2A363F;
            border-radius: 4px;
            background: #2A363F;
            color: white;
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
        }

        .patients-table tr:hover {
            background: #1a2128;
            cursor: pointer;
        }

        .select-patient-btn {
            padding: 0.5rem 1rem;
            background: #5cf9c8;
            color: black;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .select-patient-btn:hover {
            background: #4ad7a8;
        }

        .investigation-group {
            border: 1px solid #2A363F;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .investigation-header {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .investigations-list {
            margin-left: 1rem;
            padding-left: 1rem;
            border-left: 2px solid #2A363F;
        }

        .investigation-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }

        .time-picker {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #13181d;
            border: 1px solid #2A363F;
            border-radius: 4px;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        .time-picker-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 1rem;
            background: #2A363F;
            border-bottom: 1px solid #2A363F;
        }

        .time-picker-content {
            display: flex;
            padding: 1rem;
        }

        .time-column {
            flex: 1;
            text-align: center;
        }

        .time-label {
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #5cf9c8;
        }

        .time-scroll {
            height: 150px;
            overflow-y: auto;
            border: 1px solid #2A363F;
            border-radius: 4px;
            background: #2A363F;
        }

        .time-option {
            padding: 0.5rem;
            cursor: pointer;
            transition: background-color 0.2s;
            border-bottom: 1px solid #1a2128;
        }

        .time-option:hover {
            background: #3A4A5F;
        }

        .time-option.selected {
            background: #5cf9c8;
            color: black;
        }

        .time-picker-footer {
            padding: 0.5rem 1rem;
            border-top: 1px solid #2A363F;
            text-align: center;
        }

        .confirm-time-btn {
            background: #5cf9c8;
            color: black;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .confirm-time-btn:hover {
            background: #4ad7a8;
        }

        .date-picker {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #13181d;
            border: 1px solid #2A363F;
            border-radius: 4px;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            min-width: 280px;
        }

        .date-picker-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 1rem;
            background: #2A363F;
            border-bottom: 1px solid #2A363F;
        }

        .date-picker-content {
            padding: 1rem;
        }

        .date-picker-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .nav-btn {
            background: #2A363F;
            color: white;
            border: none;
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .nav-btn:hover {
            background: #3A4A5F;
        }

        #currentMonthYear {
            font-weight: bold;
            color: #5cf9c8;
        }

        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
            margin-bottom: 0.5rem;
        }

        .calendar-weekdays div {
            text-align: center;
            padding: 0.5rem;
            font-weight: bold;
            color: #5cf9c8;
            font-size: 0.9rem;
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
        }

        .calendar-day {
            text-align: center;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.2s;
            min-height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .calendar-day:hover {
            background: #3A4A5F;
        }

        .calendar-day.selected {
            background: #5cf9c8;
            color: black;
        }

        .calendar-day.other-month {
            color: #666;
        }

        .calendar-day.today {
            border: 2px solid #5cf9c8;
        }

        .calendar-day.past {
            color: #666;
            cursor: not-allowed;
        }

        .calendar-day.past:hover {
            background: transparent;
        }

        .date-picker-footer {
            padding: 0.5rem 1rem;
            border-top: 1px solid #2A363F;
            text-align: center;
        }

        .confirm-date-btn {
            background: #5cf9c8;
            color: black;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .confirm-date-btn:hover {
            background: #4ad7a8;
        }
    
        .pagination-container {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .pagination-info {
            color: #888;
            font-size: 0.9rem;
        }

        .pagination-controls {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination-btn {
            padding: 0.5rem 0.75rem;
            background: #2A363F;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            min-width: 40px;
            text-align: center;
        }

        .pagination-btn:hover {
            background: #3A4A5F;
        }

        .pagination-btn.current {
            background: #5cf9c8;
            color: black;
            cursor: default;
        }

        .pagination-btn.current:hover {
            background: #5cf9c8;
        }

        .pagination-ellipsis {
            color: #888;
            padding: 0.5rem;
            font-weight: bold;
        }
            align-items: center;
        }

        .investigation-number {
            min-width: 24px;
            color: #5cf9c8;
            font-weight: bold;
        }

        .add-investigation-btn {
            background: #2A363F;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 0.5rem;
            cursor: pointer;
            margin-top: 0.5rem;
        }

        .add-investigation-btn:hover {
            background: #3A4A5F;
        }

        .add-investigation-btn:disabled {
            background: #1a2128;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .max-investigations-warning {
            color: #ff4444;
            font-size: 0.9em;
            margin-top: 0.5rem;
            display: none;
        }

        .prescription-group {
            border: 1px solid #2A363F;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .prescription-header {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .medications-list {
            margin-left: 1rem;
            padding-left: 1rem;
            border-left: 2px solid #2A363F;
        }

        .medication-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: start;
        }

        .medication-number {
            min-width: 24px;
            color: #5cf9c8;
            font-weight: bold;
            padding-top: 0.5rem;
        }

        .medication-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            flex: 1;
        }

        .medication-inputs input {
            padding: 0.5rem;
            background: #2A363F;
            color: white;
            border: 1px solid #2A363F;
            border-radius: 4px;
        }

        .max-medications-warning {
            color: #ff4444;
            font-size: 0.9em;
            margin-top: 0.5rem;
            display: none;
        }

        .modal-content {
            background: #13181d;
            color: white;
        }

        .patients-table th {
            position: sticky;
            top: 0;
            background: #2A363F;
            z-index: 1;
        }

        #diagnosticSearchInput {
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
                padding: 1rem;
                cursor: pointer;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .category-title {
                font-weight: bold;
                color: white;
            }

            .toggle-icon {
                color: #5cf9c8;
                transition: transform 0.3s;
            }

            .category-content {
                padding: 1rem;
                background: #13181d;
            }

            .category-header.collapsed .toggle-icon {
                transform: rotate(-90deg);
            }

            .category-search-box {
                display: flex;
                gap: 0.5rem;
                margin-bottom: 1rem;
                padding: 0.5rem;
                background: #1a2128;
                border-radius: 4px;
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
                color: black;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
            }

            .category-search-btn:hover {
                background: #4ad7a8;
            }

            .investigation-selector {
                flex: 1;
                display: flex;
                gap: 0.5rem;
                align-items: center;
            }

            .investigation-select {
                flex: 1;
                padding: 0.5rem;
                background: #2A363F;
                color: white;
                border: 1px solid #2A363F;
                border-radius: 4px;
                cursor: pointer;
            }

            .investigation-select:focus {
                outline: none;
                border-color: #5cf9c8;
            }

            .investigation-select optgroup {
                background: #1a2128;
                color: #5cf9c8;
                font-weight: bold;
                padding: 0.5rem;
            }

            .investigation-select option {
                background: #2A363F;
                color: white;
                padding: 0.5rem;
                margin-left: 1rem;
            }

            .investigation-categories {
                max-height: 400px;
                overflow-y: auto;
                margin-bottom: 1rem;
            }

            .investigation-category {
                margin-bottom: 1rem;
                border: 1px solid #2A363F;
                border-radius: 4px;
            }

            .selected-investigation-display {
                width: 100%;
                padding: 0.5rem;
                background: #2A363F;
                color: white;
                border: 1px solid #2A363F;
                border-radius: 4px;
                margin-top: 1rem;
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
        <div class="wrapper">
            <div class="registru-container">
                <a href="dashboardMedici.php" class="back-btn">&larr; Înapoi la dashboard</a>
                <h2>Registru Consultații</h2>
                <button class="add-consultation-btn" onclick="openAddConsultationModal()">
                    Adaugă Consultație Nouă
                </button>

                <table class="consultations-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                                <th>Ora</th>
                            <th>Pacient</th>
                            <th>Simptome</th>
                            <th>Diagnostic</th>
                            <th>Trimiteri</th>
                            <th>Investigații</th>
                            <th>Rețete</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $consultations->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['ID']); ?></td>
                                <td><?php echo htmlspecialchars(date('d.m.Y', strtotime($row['Data']))); ?></td>
                                    <td><?php echo htmlspecialchars(date('H:i', strtotime($row['Ora']))); ?></td>
                                <td><?php echo htmlspecialchars($row['pacient_nume'] . ' ' . $row['pacient_prenume']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['Simptome'], 0, 50) . '...'); ?></td>
                                    <td><?php echo htmlspecialchars($row['cod_diagnostic'] . ' - ' . $row['denumire_diagnostic']); ?></td>
                                <td><?php echo $row['trimiteri'] ? htmlspecialchars($row['trimiteri']) : '-'; ?></td>
                                <td><?php echo $row['investigatii'] ? htmlspecialchars($row['investigatii']) : '-'; ?></td>
                                    <td><?php echo $row['retete'] ? htmlspecialchars($row['retete']) : '-'; ?></td>
                                <td>
                                        <div class="action-buttons">
                                    <button class="view-details-btn" onclick="viewConsultationDetails(<?php echo $row['ID']; ?>)">
                                        Vezi detalii
                                    </button>
                                        <button class="remove-btn" onclick="deleteConsultation(<?php echo $row['ID']; ?>)">
                                            Șterge
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                <div class="pagination-container">
                    <div class="pagination-info">
                        Afișare <?php echo ($offset + 1); ?> - <?php echo min($offset + $consultations_per_page, $total_consultations); ?> din <?php echo $total_consultations; ?> consultații
                    </div>
                    <div class="pagination-controls">
                        <?php if ($page > 1): ?>
                            <a href="?page=1" class="pagination-btn">Prima</a>
                            <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">Anterior</a>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);

                        if ($start_page > 1): ?>
                            <a href="?page=1" class="pagination-btn">1</a>
                            <?php if ($start_page > 2): ?>
                                <span class="pagination-ellipsis">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="pagination-btn current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>" class="pagination-btn"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <span class="pagination-ellipsis">...</span>
                            <?php endif; ?>
                            <a href="?page=<?php echo $total_pages; ?>" class="pagination-btn"><?php echo $total_pages; ?></a>
                        <?php endif; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">Următor</a>
                            <a href="?page=<?php echo $total_pages; ?>" class="pagination-btn">Ultima</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="addConsultationModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeAddConsultationModal()">&times;</span>
            <h2>Adaugă Consultație Nouă</h2>
                <form id="consultationForm" method="POST" action="save_consultation.php" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="pacient">Pacient</label>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <input type="hidden" name="CNPPacient" id="selectedCNP" required>
                        <input type="text" id="selectedPatientDisplay" readonly placeholder="Niciun pacient selectat" 
                               style="flex: 1; padding: 0.5rem; background: #2A363F; color: white; border: 1px solid #2A363F; border-radius: 4px;">
                        <button type="button" class="add-item-btn" onclick="openPatientSearch()">
                            Selectează Pacient
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="data">Data și Ora Consultației</label>
                    <div style="display: flex; gap: 1rem;">
                        <div style="flex: 1; position: relative;">
                                                    <input type="text" 
                               name="Data" 
                               id="data" 
                               required 
                               placeholder="dd-mm-yyyy"
                               value=""
                               style="width: 100%; padding: 0.5rem; background: #2A363F; color: white; border: 1px solid #2A363F; border-radius: 4px; cursor: pointer;"
                               pattern="\d{2}-\d{2}-\d{4}"
                               title="Format: dd-mm-yyyy"
                               readonly
                               onclick="openDatePicker()"
                               autocomplete="off"
                               data-form-type="other">
                            <div id="datePicker" class="date-picker" style="display: none;">
                                <div class="date-picker-header">
                                    <span>Selectează data</span>
                                    <button type="button" onclick="closeDatePicker()" style="background: none; border: none; color: white; cursor: pointer;">&times;</button>
                                </div>
                                <div class="date-picker-content">
                                    <div class="date-picker-nav">
                                        <button type="button" onclick="previousMonth()" class="nav-btn">&lt;</button>
                                        <span id="currentMonthYear"></span>
                                        <button type="button" onclick="nextMonth()" class="nav-btn">&gt;</button>
                                    </div>
                                    <div class="date-picker-calendar">
                                        <div class="calendar-weekdays">
                                            <div>Lu</div>
                                            <div>Ma</div>
                                            <div>Mi</div>
                                            <div>Jo</div>
                                            <div>Vi</div>
                                            <div>Sa</div>
                                            <div>Du</div>
                                        </div>
                                        <div id="calendarDays" class="calendar-days"></div>
                                    </div>
                                </div>
                                <div class="date-picker-footer">
                                    <button type="button" onclick="confirmDate()" class="confirm-date-btn">Confirmă</button>
                                </div>
                            </div>
                        </div>
                        <div style="flex: 1; position: relative;">
                            <input type="text" 
                                   name="Ora" 
                                   id="ora" 
                                   required 
                                   placeholder="HH:MM"
                                   value=""
                                   style="width: 100%; padding: 0.5rem; background: #2A363F; color: white; border: 1px solid #2A363F; border-radius: 4px; cursor: pointer;"
                                   readonly
                                   onclick="openTimePicker()">
                            <div id="timePicker" class="time-picker" style="display: none;">
                                <div class="time-picker-header">
                                    <span>Selectează ora</span>
                                    <button type="button" onclick="closeTimePicker()" style="background: none; border: none; color: white; cursor: pointer;">&times;</button>
                                </div>
                                <div class="time-picker-content">
                                    <div class="time-column">
                                        <div class="time-label">Ora</div>
                                        <div class="time-scroll" id="hoursScroll">
                                            <?php for($h = 0; $h <= 23; $h++): ?>
                                                <div class="time-option" data-value="<?php echo str_pad($h, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($h, 2, '0', STR_PAD_LEFT); ?></div>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="time-column">
                                        <div class="time-label">Minut</div>
                                        <div class="time-scroll" id="minutesScroll">
                                            <?php for($m = 0; $m <= 59; $m++): ?>
                                                <div class="time-option" data-value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?></div>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="time-picker-footer">
                                    <button type="button" onclick="confirmTime()" class="confirm-time-btn">Confirmă</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="simptome">Simptome</label>
                    <textarea name="Simptome" id="simptome" required></textarea>
                </div>

                <div class="form-group">
                    <label for="diagnostic">Diagnostic</label>
                        <input type="hidden" name="diagnostic" id="selectedDiagnosticCode" required>
                            <input type="text" 
                                   id="selectedDiagnosticDisplay" 
                                   readonly 
                                   placeholder="Selectează diagnosticul" 
                            required>
                        <button type="button" class="add-item-btn" onclick="openDiagnosticSearch()">
                            Selectează Diagnostic
                        </button>
                </div>

                <div class="optional-section">
                    <h3>Bilete de Trimitere</h3>
                    <div id="trimiteriContainer">
                    </div>
                    <button type="button" class="add-item-btn" onclick="addTrimitere()">
                            + Adaugă Bilet de Trimitere
                    </button>
                </div>

                <div class="optional-section">
                    <h3>Bilete de Investigații</h3>
                    <div id="investigatiiContainer">
                    </div>
                    <button type="button" class="add-item-btn" onclick="addInvestigatieGroup()">
                        + Adaugă Bilet de Investigații
                    </button>
                </div>

                <div class="optional-section">
                    <h3>Rețete Medicale</h3>
                    <div id="reteteContainer">
                    </div>
                    <button type="button" class="add-item-btn" onclick="addRetetaGroup()">
                        + Adaugă Rețetă Medicală
                    </button>
                </div>

                <button type="submit" class="add-consultation-btn" style="width: 100%;">
                    Salvează Consultația
                </button>
            </form>
        </div>
    </div>

    <div id="patientSearchModal" class="patient-search-modal">
        <div class="patient-search-content">
            <div class="patient-search-header">
                <h2>Selectează Pacient</h2>
                <span class="close-modal" onclick="closePatientSearch()">&times;</span>
            </div>

            <div class="patient-search-box">
                <input type="text" id="patientSearchInput" placeholder="Caută după nume, prenume sau CNP..." 
                       oninput="filterPatients()">
            </div>

            <div class="patient-sort-options">
                <select id="sortOrder" onchange="sortPatients()">
                    <option value="asc">Nume (A-Z)</option>
                    <option value="desc">Nume (Z-A)</option>
                </select>
            </div>

            <table class="patients-table" id="patientsTable">
                <thead>
                    <tr>
                        <th>Nume</th>
                        <th>Prenume</th>
                        <th>CNP</th>
                        <th>Data Nașterii</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT p.CNP, p.nume, p.prenume, p.data_nasterii 
                            FROM pacienti p 
                            INNER JOIN doctor_pacient dp ON p.id = dp.pacient_id 
                            WHERE dp.doctor_id = ?
                            ORDER BY p.nume ASC";
                    $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id_medic);
                    $stmt->execute();
                    $patients = $stmt->get_result();
                    while ($patient = $patients->fetch_assoc()):
                    ?>
                    <tr class="patient-row" 
                        data-nume="<?php echo htmlspecialchars($patient['nume']); ?>"
                        data-prenume="<?php echo htmlspecialchars($patient['prenume']); ?>"
                        data-cnp="<?php echo htmlspecialchars($patient['CNP']); ?>">
                        <td><?php echo htmlspecialchars($patient['nume']); ?></td>
                        <td><?php echo htmlspecialchars($patient['prenume']); ?></td>
                        <td><?php echo htmlspecialchars($patient['CNP']); ?></td>
                        <td><?php echo htmlspecialchars(date('d.m.Y', strtotime($patient['data_nasterii']))); ?></td>
                        <td>
                            <button class="select-patient-btn" 
                                    onclick="selectPatient('<?php echo htmlspecialchars($patient['CNP']); ?>', 
                                                         '<?php echo htmlspecialchars($patient['nume']); ?>', 
                                                         '<?php echo htmlspecialchars($patient['prenume']); ?>')">
                                Selectează
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

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

        <div id="investigationSearchModal" class="modal">
            <div class="modal-content" style="max-width: 1000px;">
                <div class="patient-search-header">
                    <h2>Selectează Investigație</h2>
                    <span class="close-modal" onclick="closeInvestigationSearch()">&times;</span>
                </div>

                <div class="patient-search-box">
                    <input type="text" 
                        id="investigationSearchInput" 
                        placeholder="Caută după cod sau investigație..." 
                        oninput="filterInvestigations()">
                </div>

                <div style="max-height: 600px; overflow-y: auto; margin-top: 1rem;">
                    <?php
                    $categories = [
                        ['name' => 'Hematologie', 'range' => '1-10'],
                        ['name' => 'Biochimie serica si urinara', 'range' => '11-42'],
                        ['name' => 'Imunologie', 'range' => '43-71'],
                        ['name' => 'Microbiologie', 'range' => '72-92'],
                        ['name' => 'Examinari histopatologice si citologice', 'range' => '93-100'],
                        ['name' => 'Investigatii cu radiatii ionizante', 'range' => '101-125'],
                        ['name' => 'Investigatii neiradiante', 'range' => '126-141'],
                        ['name' => 'Investigatii de inalta performanta', 'range' => '142-198'],
                        ['name' => 'Medicina nucleara', 'range' => '199-208']
                    ];

                    foreach ($categories as $index => $category) {
                        $range = explode('-', $category['range']);
                        $min = (int)$range[0];
                        $max = (int)$range[1];
                    ?>
                    <div class="investigation-category">
                        <div class="category-header" onclick="toggleInvestigationCategory(<?php echo $index; ?>)">
                            <span class="category-title"><?php echo ($index + 1) . '. ' . $category['name'] . ' (' . $category['range'] . ')'; ?></span>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="category-content" id="investigation-category-<?php echo $index; ?>" style="display: none;">
                            <div class="category-search-box">
                                <input type="text" 
                                    id="investigationSearch-<?php echo $index; ?>" 
                                    placeholder="Caută în această categorie..." 
                                    class="category-search-input">
                                <button type="button" 
                                        class="category-search-btn" 
                                        onclick="filterInvestigationCategory(<?php echo $index; ?>)">
                                    Caută
                                </button>
                            </div>
                            <table class="patients-table">
                                <thead>
                                    <tr>
                                        <th>Cod</th>
                                        <th>Investigație</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT id, cod, denumire FROM coduri_analize WHERE id BETWEEN ? AND ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("ii", $min, $max);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while ($row = $result->fetch_assoc()):
                                    ?>
                                    <tr class="investigation-row"
                                        data-id="<?php echo htmlspecialchars($row['id']); ?>"
                                        data-code="<?php echo htmlspecialchars($row['cod']); ?>"
                                        data-investigation="<?php echo htmlspecialchars($row['denumire']); ?>">
                                        <td><?php echo htmlspecialchars($row['cod']); ?></td>
                                        <td><?php echo htmlspecialchars($row['denumire']); ?></td>
                                        <td>
                                            <button class="view-details-btn" 
                                                    onclick="selectInvestigation('<?php echo htmlspecialchars($row['id']); ?>',
                                                                    '<?php echo htmlspecialchars($row['cod']); ?>',
                                                                    '<?php echo htmlspecialchars(addslashes($row['denumire'])); ?>')">
                                                Selectează
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php } ?>
            </div>
        </div>
    </div>

    <script>
        let selectedHour = String(new Date().getHours()).padStart(2, '0');
        let selectedMinute = String(new Date().getMinutes()).padStart(2, '0');

        let currentDate = new Date();
        let selectedDate = new Date();
        let currentMonth = currentDate.getMonth();
        let currentYear = currentDate.getFullYear();

        function setInitialDate() {
            const dateInput = document.getElementById('data');
            if (dateInput) {
                let today = new Date();
                let day = String(today.getDate()).padStart(2, '0');
                let month = String(today.getMonth() + 1).padStart(2, '0');
                let year = today.getFullYear();
                dateInput.value = day + '-' + month + '-' + year;
                selectedDate = new Date(today);
            }
        }

        function openTimePicker() {
            const timePicker = document.getElementById('timePicker');
            timePicker.style.display = 'block';
            
            const now = new Date();
            const currentHour = String(now.getHours()).padStart(2, '0');
            const currentMinute = String(now.getMinutes()).padStart(2, '0');
            
            selectedHour = currentHour;
            selectedMinute = currentMinute;
            
            selectTimeOption('hoursScroll', currentHour);
            selectTimeOption('minutesScroll', currentMinute);
        }

        function closeTimePicker() {
            document.getElementById('timePicker').style.display = 'none';
        }

        function selectTimeOption(containerId, value) {
            const container = document.getElementById(containerId);
            const options = container.getElementsByClassName('time-option');
            
            for (let option of options) {
                option.classList.remove('selected');
            }
            
            for (let option of options) {
                if (option.getAttribute('data-value') === value) {
                    option.classList.add('selected');
                    option.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    break;
                }
            }
        }

        function confirmTime() {
            const hourOption = document.querySelector('#hoursScroll .time-option.selected');
            const minuteOption = document.querySelector('#minutesScroll .time-option.selected');
            
            if (hourOption && minuteOption) {
                selectedHour = hourOption.getAttribute('data-value');
                selectedMinute = minuteOption.getAttribute('data-value');
                
                const timeInput = document.getElementById('ora');
                timeInput.value = `${selectedHour}:${selectedMinute}`;
            }
            
            closeTimePicker();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const hoursScroll = document.getElementById('hoursScroll');
            const minutesScroll = document.getElementById('minutesScroll');
            
            if (hoursScroll) {
                hoursScroll.addEventListener('click', function(e) {
                    if (e.target.classList.contains('time-option')) {
                        const options = hoursScroll.getElementsByClassName('time-option');
                        for (let option of options) {
                            option.classList.remove('selected');
                        }   
                        e.target.classList.add('selected');
                        selectedHour = e.target.getAttribute('data-value');
                    }
                });
            }
            
            if (minutesScroll) {
                minutesScroll.addEventListener('click', function(e) {
                    if (e.target.classList.contains('time-option')) {
                        const options = minutesScroll.getElementsByClassName('time-option');
                        for (let option of options) {
                            option.classList.remove('selected');
                        }
                        e.target.classList.add('selected');
                        selectedMinute = e.target.getAttribute('data-value');
                    }
                });
            }

            setTimeout(() => {
                const dateInput = document.getElementById('data');
                const timeInput = document.getElementById('ora');
                
                if (dateInput) {
                    dateInput.value = '';
                    
                    let today = new Date();
                    let day = String(today.getDate()).padStart(2, '0');
                    let month = String(today.getMonth() + 1).padStart(2, '0');
                    let year = today.getFullYear();
                    dateInput.value = day + '-' + month + '-' + year;
                    selectedDate = new Date(today);
                }
                
                if (timeInput) {
                    const now = new Date();
                    const currentHour = String(now.getHours()).padStart(2, '0');
                    const currentMinute = String(now.getMinutes()).padStart(2, '0');
                    timeInput.value = currentHour + ':' + currentMinute;
                    
                    selectedHour = currentHour;
                    selectedMinute = currentMinute;
                }
            }, 100);
        });

        document.addEventListener('click', function(e) {
            const timePicker = document.getElementById('timePicker');
            const timeInput = document.getElementById('ora');
            
            if (timePicker && timePicker.style.display === 'block') {
                if (!timePicker.contains(e.target) && e.target !== timeInput) {
                    closeTimePicker();
                }
            }
        });

        function openDatePicker() {
            const datePicker = document.getElementById('datePicker');
            datePicker.style.display = 'block';
            renderCalendar();
        }

        function closeDatePicker() {
            document.getElementById('datePicker').style.display = 'none';
        }

        function renderCalendar() {
            const monthNames = [
                'Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Iunie',
                'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'
            ];

            document.getElementById('currentMonthYear').textContent = 
                monthNames[currentMonth] + ' ' + currentYear;

            const firstDay = new Date(currentYear, currentMonth, 1);
            const lastDay = new Date(currentYear, currentMonth + 1, 0);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay() + (firstDay.getDay() === 0 ? -6 : 1));

            const calendarDays = document.getElementById('calendarDays');
            calendarDays.innerHTML = '';

            for (let i = 0; i < 42; i++) {
                const date = new Date(startDate);
                date.setDate(startDate.getDate() + i);

                const dayDiv = document.createElement('div');
                dayDiv.className = 'calendar-day';
                dayDiv.textContent = date.getDate();

                if (date.getMonth() !== currentMonth) {
                    dayDiv.classList.add('other-month');
                }

                if (date.toDateString() === new Date().toDateString()) {
                    dayDiv.classList.add('today');
                }

                if (date.toDateString() === selectedDate.toDateString()) {
                    dayDiv.classList.add('selected');
                }

                if (date < new Date().setHours(0, 0, 0, 0)) {
                    dayDiv.classList.add('past');
                } else {
                    dayDiv.addEventListener('click', function() {
                        document.querySelectorAll('.calendar-day.selected').forEach(day => {
                            day.classList.remove('selected');
                        });
                        
                        dayDiv.classList.add('selected');
                        selectedDate = new Date(date);
                    });
                }

                calendarDays.appendChild(dayDiv);
            }
        }

        function previousMonth() {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            renderCalendar();
        }

        function nextMonth() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCalendar();
        }

        function confirmDate() {
            const dateInput = document.getElementById('data');
            const day = String(selectedDate.getDate()).padStart(2, '0');
            const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
            const year = selectedDate.getFullYear();
            dateInput.value = day + '-' + month + '-' + year;
            closeDatePicker();
        }

        document.addEventListener('click', function(e) {
            const datePicker = document.getElementById('datePicker');
            const dateInput = document.getElementById('data');
            
            if (datePicker && datePicker.style.display === 'block') {
                if (!datePicker.contains(e.target) && e.target !== dateInput) {
                    closeDatePicker();
                }
            }
        });

        function formatDateInput(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length > 8) {
                value = value.substring(0, 8);
            }
            
            if (value.length >= 4) {
                value = value.substring(0, 2) + '-' + value.substring(2, 4) + '-' + value.substring(4);
            } else if (value.length >= 2) {
                value = value.substring(0, 2) + '-' + value.substring(2);
            }
            
            input.value = value;
        }

        function formatDate(input) {
            let value = input.value.trim();
            
            if (!value) return;
            
            let digits = value.replace(/\D/g, '');
            
            if (digits.length === 8) {
                let day = digits.substring(0, 2);
                let month = digits.substring(2, 4);
                let year = digits.substring(4, 8);
                
                let date = new Date(year, month - 1, day);
                if (date.getFullYear() == year && date.getMonth() == month - 1 && date.getDate() == day) {
                    input.value = day + '-' + month + '-' + year;
                    return;
                }
            }
            
            if (/^\d{2}-\d{2}-\d{4}$/.test(value)) {
                let parts = value.split('-');
                let day = parseInt(parts[0]);
                let month = parseInt(parts[1]);
                let year = parseInt(parts[2]);
                
                let date = new Date(year, month - 1, day);
                if (date.getFullYear() == year && date.getMonth() == month - 1 && date.getDate() == day) {
                    return;
                }
            }
            
            let date = new Date(value);
            if (!isNaN(date.getTime())) {
                let day = String(date.getDate()).padStart(2, '0');
                let month = String(date.getMonth() + 1).padStart(2, '0');
                let year = date.getFullYear();
                input.value = day + '-' + month + '-' + year;
            } else { 
                input.value = '';
                alert('Vă rugăm să introduceți o dată validă în formatul dd-mm-yyyy');
            }
        }

        function openAddConsultationModal()
        {
            document.getElementById('addConsultationModal').style.display = 'block';
        }

            function closeAddConsultationModal()
            {
            document.getElementById('addConsultationModal').style.display = 'none';
        }

            function addTrimitere()
            {
            const container = document.getElementById('trimiteriContainer');
            const div = document.createElement('div');
                div.className = 'investigation-group';
            div.innerHTML = `
                    <div class="investigation-header">
                        <input type="text" 
                            name="trimitere_cod[]" 
                            placeholder="Cod Bilet" 
                            style="flex: 1; padding: 0.5rem; background: #2A363F; color: white; border: 1px solid #2A363F; border-radius: 4px;">
                        <button type="button" 
                                class="remove-btn" 
                                onclick="removeTrimitereGroup(this)">
                            Șterge Bilet
                        </button>
                    </div>
                    <div class="investigations-list" id="trimitereList">
                        <div class="investigation-item">
                            <div class="investigation-selector">
                                <select name="trimitere_specializare[]" required style="flex: 1; padding: 0.5rem; background: #2A363F; color: white; border: 1px solid #2A363F; border-radius: 4px;">
                                    <option value="">Selectează specializarea</option>
                                    <option value="Alergologie si imunologie clinica">Alergologie și imunologie clinică</option>
                                    <option value="Boli infectioase">Boli infecțioase</option>
                                    <option value="Cardiologie">Cardiologie</option>
                                    <option value="Cardiologie pediatrica">Cardiologie pediatrică</option>
                                    <option value="Chirurgie cardiovasculara">Chirurgie cardiovasculară</option>
                                    <option value="Chirurgie generala">Chirurgie generală</option>
                                    <option value="Chirurgie orala si maxilo-faciala">Chirurgie orală și maxilo-facială</option>
                                    <option value="Chirurgie pediatrica">Chirurgie pediatrică</option>
                                    <option value="Chirurgie plastica, estetica si microchirurgie reconstructiva">Chirurgie plastică, estetică și microchirurgie reconstructivă</option>
                                    <option value="Chirurgie toracica">Chirurgie toracică</option>
                                    <option value="Chirurgie vasculara">Chirurgie vasculară</option>
                                    <option value="Dermatovenerologie">Dermatovenerologie</option>
                                    <option value="Diabet zaharat, nutritie si boli metabolice">Diabet zaharat, nutriție și boli metabolice</option>
                                    <option value="Endocrinologie">Endocrinologie</option>
                                    <option value="Gastroenterologie">Gastroenterologie</option>
                                    <option value="Gastroenterologie pediatrica">Gastroenterologie pediatrică</option>
                                    <option value="Genetica medicala">Genetică medicală</option>
                                    <option value="Geriatrie si gerontologie">Geriatrie și gerontologie</option>
                                    <option value="Hematologie">Hematologie</option>
                                    <option value="Medicina fizica si de reabilitare">Medicină fizică și de reabilitare</option>
                                    <option value="Medicina interna">Medicină internă</option>
                                    <option value="Nefrologie">Nefrologie</option>
                                    <option value="Nefrologie pediatrica">Nefrologie pediatrică</option>
                                    <option value="Neurochirurgie">Neurochirurgie</option>
                                    <option value="Neurologie">Neurologie</option>
                                    <option value="Neurologie pediatrica">Neurologie pediatrică</option>
                                    <option value="Obstetrica-ginecologie">Obstetrică-ginecologie</option>
                                    <option value="Oftalmologie">Oftalmologie</option>
                                    <option value="Oncologie medicala">Oncologie medicală</option>
                                    <option value="Oncologie si hematologie pediatrica">Oncologie și hematologie pediatrică</option>
                                    <option value="Ortopedie pediatrica">Ortopedie pediatrică</option>
                                    <option value="Ortopedie si traumatologie">Ortopedie și traumatologie</option>
                                    <option value="Otorinolaringologie">Otorinolaringologie</option>
                                    <option value="Pediatrie">Pediatrie</option>
                                    <option value="Pneumologie">Pneumologie</option>
                                    <option value="Pneumologie pediatrica">Pneumologie pediatrică</option>
                                    <option value="Psihiatrie">Psihiatrie</option>
                                    <option value="Psihiatrie pediatrica">Psihiatrie pediatrică</option>
                                    <option value="Radiologie-imagistica medicala">Radiologie-imagistică medicală</option>
                                    <option value="Radioterapie">Radioterapie</option>
                                    <option value="Reumatologie">Reumatologie</option>
                                    <option value="Urologie">Urologie</option>
                                </select>
                            </div>
                        </div>
                    </div>
            `;
            container.appendChild(div);
        }

            function removeTrimitereGroup(button)
            {
                const group = button.closest('.investigation-group');
                if (group)
                {
                    group.remove();
                }
        }

        let investigationGroupCounter = 0;
        const MAX_INVESTIGATIONS = 15;

            function addInvestigatieGroup()
            {
            const container = document.getElementById('investigatiiContainer');
                if (!container)
                {
                console.error('Container not found');
                return;
            }

            const groupId = investigationGroupCounter++;
            
            const div = document.createElement('div');
            div.className = 'investigation-group';
            div.innerHTML = `
                <div class="investigation-header">
                    <input type="text" 
                            name="investigatie_cod_${groupId}" 
                           placeholder="Cod Bilet" 
                           style="flex: 1; padding: 0.5rem; background: #2A363F; color: white; border: 1px solid #2A363F; border-radius: 4px;">
                    <button type="button" 
                            class="remove-btn" 
                            onclick="removeInvestigatieGroup(this)">
                        Șterge Bilet
                    </button>
                </div>
                <div class="investigations-list" id="investigationList${groupId}">
                </div>
                <button type="button" 
                        class="add-investigation-btn" 
                        onclick="addInvestigatie(${groupId})"
                        id="addButton${groupId}">
                    + Adaugă Investigație
                </button>
                <div class="max-investigations-warning" id="warning${groupId}">
                    Numărul maxim de investigații permis pe un bilet este 15.
                </div>
            `;
            
            container.appendChild(div);
            addInvestigatie(groupId);
        }

            function updateInvestigationNumbers(groupId)
            {
            const container = document.getElementById(`investigationList${groupId}`);
            if (!container) return;

            const items = container.getElementsByClassName('investigation-item');
                for (let i = 0; i < items.length; i++)
                {
                const numberSpan = items[i].querySelector('.investigation-number');
                    if (numberSpan)
                    {
                    numberSpan.textContent = `${i + 1}.`;
                }
            }

            const addButton = document.getElementById(`addButton${groupId}`);
            const warning = document.getElementById(`warning${groupId}`);
                if (addButton && warning)
                {
                    if (items.length >= MAX_INVESTIGATIONS)
                    {
                    addButton.disabled = true;
                    warning.style.display = 'block';
                    }
                    else
                    {
                    addButton.disabled = false;
                    warning.style.display = 'none';
                }
            }
        }

            function addInvestigatie(groupId)
            {
            const container = document.getElementById(`investigationList${groupId}`);
                if (!container)
                {
                console.error('Investigation list container not found');
                return;
            }

                if (container.children.length >= MAX_INVESTIGATIONS)
                {
                return;
            }

            const div = document.createElement('div');
            div.className = 'investigation-item';
            div.innerHTML = `
                <span class="investigation-number">${container.children.length + 1}.</span>
                    <div class="investigation-selector">
                        <input type="hidden" name="investigatie_cod[${groupId}][]" class="selected-investigation-code">
                        <input type="hidden" name="investigatie_display[${groupId}][]" class="selected-investigation-display-value">
                <input type="text" 
                            class="selected-investigation-display" 
                            readonly 
                            placeholder="Selectează investigația" 
                            onclick="openInvestigationSearch(${groupId})">
                    </div>
            `;
            container.appendChild(div);
            updateInvestigationNumbers(groupId);
        }

            let currentInvestigationGroupId = null;

            function openInvestigationSearch(groupId)
            {
                currentInvestigationGroupId = groupId;
                document.getElementById('investigationSearchModal').style.display = 'block';
            }

            function closeInvestigationSearch()
            {
                document.getElementById('investigationSearchModal').style.display = 'none';
                currentInvestigationGroupId = null;
            }

            function selectInvestigation(id, code, investigation)
            {
                if (currentInvestigationGroupId === null) return;
                
                const container = document.querySelector(`#investigationList${currentInvestigationGroupId}`);
                const currentItem = container.querySelector('.investigation-item:last-child');
                const codeInput = currentItem.querySelector('.selected-investigation-code');
                const displayInput = currentItem.querySelector('.selected-investigation-display');
                const displayValueInput = currentItem.querySelector('.selected-investigation-display-value');
                
                codeInput.value = code;
                displayInput.value = `${code} - ${investigation}`;
                displayValueInput.value = id;
                closeInvestigationSearch();
            }

            function filterInvestigations()
            {
                const searchText = document.getElementById('investigationSearchInput').value.toLowerCase();
                const rows = document.getElementsByClassName('investigation-row');
                
                for (let row of rows)
                {
                    const code = row.getAttribute('data-code').toLowerCase();
                    const investigation = row.getAttribute('data-investigation').toLowerCase();
                    const category = row.closest('.category-content');
                    
                    if (code.includes(searchText) || investigation.includes(searchText))
                    {
                        row.style.display = '';
                        if (category)
                        {
                            category.style.display = 'block';
                            category.previousElementSibling.classList.remove('collapsed');
                        }
                    }
                    else
                    {
                        row.style.display = 'none';
                    }
                }
            }

            window.onclick = function(event)
            {
                const modal = document.getElementById('investigationSearchModal');
                if (event.target === modal)
                {
                    closeInvestigationSearch();
                }
            }

            function removeInvestigatieGroup(button)
            {
            const group = button.closest('.investigation-group');
                if (group)
                {
                group.remove();
            }
        }

            function removeInvestigatie(button, groupId)
            {
            const item = button.closest('.investigation-item');
            const group = button.closest('.investigations-list');
                if (item && group)
                {
                item.remove();
                    if (group.children.length === 0)
                    {
                    addInvestigatie(groupId);
                }
                updateInvestigationNumbers(groupId);
            }
        }

        let prescriptionGroupCounter = 0;
        const MAX_MEDICATIONS = 10;

            function addRetetaGroup()
            {
            const container = document.getElementById('reteteContainer');
                if (!container)
                {
                console.error('Container not found');
                return;
            }

            const groupId = prescriptionGroupCounter++;
            
            const div = document.createElement('div');
            div.className = 'prescription-group';
            div.innerHTML = `
                <div class="prescription-header">
                    <input type="text" 
                           name="reteta_cod[]" 
                           placeholder="Cod Rețetă" 
                           required 
                           style="flex: 1; padding: 0.5rem; background: #2A363F; color: white; border: 1px solid #2A363F; border-radius: 4px;">
                    <button type="button" 
                            class="remove-btn" 
                            onclick="removeRetetaGroup(this)">
                        Șterge Rețetă
                    </button>
                </div>
                <div class="medications-list" id="medicationList${groupId}">
                </div>
                <button type="button" 
                        class="add-investigation-btn" 
                        onclick="addMedicatie(${groupId})"
                        id="addMedicationButton${groupId}">
                    + Adaugă Medicament
                </button>
                <div class="max-medications-warning" id="medicationWarning${groupId}">
                    Numărul maxim de medicamente permis pe o rețetă este 10.
                </div>
            `;
            
            container.appendChild(div);
            addMedicatie(groupId);
        }

            function updateMedicationNumbers(groupId)
            {
            const container = document.getElementById(`medicationList${groupId}`);
                if (!container)
                    return;

            const items = container.getElementsByClassName('medication-item');
                for (let i = 0; i < items.length; i++)
                {
                const numberSpan = items[i].querySelector('.medication-number');
                    if (numberSpan)
                    {
                    numberSpan.textContent = `${i + 1}.`;
                }
            }

            const addButton = document.getElementById(`addMedicationButton${groupId}`);
            const warning = document.getElementById(`medicationWarning${groupId}`);
                if (addButton && warning)
                {
                    if (items.length >= MAX_MEDICATIONS)
                    {
                    addButton.disabled = true;
                    warning.style.display = 'block';
                    }
                    else
                    {
                    addButton.disabled = false;
                    warning.style.display = 'none';
                }
            }
        }

            function addMedicatie(groupId)
            {
            const container = document.getElementById(`medicationList${groupId}`);
                if (!container)
                {
                console.error('Medication list container not found');
                return;
            }

                if (container.children.length >= MAX_MEDICATIONS)
                {
                return;
            }

            const div = document.createElement('div');
            div.className = 'medication-item';
            div.innerHTML = `
                <span class="medication-number">${container.children.length + 1}.</span>
                <div class="medication-inputs">
                    <input type="text" 
                           name="medicament[${groupId}][]" 
                           placeholder="Denumire Medicament" 
                           required>
                    <input type="text" 
                           name="forma_farmaceutica[${groupId}][]" 
                           placeholder="Forma Farmaceutică" 
                           required>
                    <input type="text" 
                           name="cantitate[${groupId}][]" 
                           placeholder="Cantitate" 
                           required>
                    <input type="text" 
                           name="durata[${groupId}][]" 
                           placeholder="Durata Tratament" 
                           required>
                </div>
                <button type="button" 
                        class="remove-btn" 
                        onclick="removeMedicatie(this, ${groupId})"
                        style="align-self: center;">
                    ×
                </button>
            `;
            container.appendChild(div);
            updateMedicationNumbers(groupId);
        }

            function removeRetetaGroup(button)
            {
            const group = button.closest('.prescription-group');
                if (group)
                {
                group.remove();
            }
        }

            function removeMedicatie(button, groupId)
            {
            const item = button.closest('.medication-item');
            const group = button.closest('.medications-list');
                if (item && group)
                {
                item.remove();
                    if (group.children.length === 0)
                    {
                    addMedicatie(groupId);
                }
                updateMedicationNumbers(groupId);
            }
        }

            function viewConsultationDetails(consultationId)
            {
                window.location.href = 'detaliiConsultatie.php?id=' + consultationId;
            }

            function openPatientSearch()
            {
            document.getElementById('patientSearchModal').style.display = 'block';
        }

            function closePatientSearch()
            {
            document.getElementById('patientSearchModal').style.display = 'none';
        }

            function selectPatient(cnp, nume, prenume)
            {
            document.getElementById('selectedCNP').value = cnp;
            document.getElementById('selectedPatientDisplay').value = nume + ' ' + prenume;
            closePatientSearch();
        }

            function filterPatients()
            {
            const searchText = document.getElementById('patientSearchInput').value.toLowerCase();
            const rows = document.getElementsByClassName('patient-row');

                Array.from(rows).forEach(row =>
                {
                const nume = row.getAttribute('data-nume').toLowerCase();
                const prenume = row.getAttribute('data-prenume').toLowerCase();
                const cnp = row.getAttribute('data-cnp').toLowerCase();
                
                if (nume.includes(searchText) || 
                    prenume.includes(searchText) || 
                        cnp.includes(searchText))
                    {
                    row.style.display = '';
                    }
                    else
                    {
                    row.style.display = 'none';
                }
            });
        }

            function sortPatients()
            {
            const sortOrder = document.getElementById('sortOrder').value;
            const tbody = document.querySelector('#patientsTable tbody');
            const rows = Array.from(tbody.getElementsByClassName('patient-row'));

            rows.sort((a, b) => {
                let aValue = a.getAttribute('data-nume').toLowerCase();
                let bValue = b.getAttribute('data-nume').toLowerCase();

                    if (sortOrder === 'asc')
                    {
                    return aValue.localeCompare(bValue);
                    }
                    else
                    {
                    return bValue.localeCompare(aValue);
                }
            });

            rows.forEach(row => tbody.appendChild(row));
        }

            window.onclick = function(event)
            {
            const modal = document.getElementById('patientSearchModal');
            if (event.target === modal) {
                closePatientSearch();
            }
        }

            function formatDate(date)
            {
            return date.getFullYear() + '-' + 
                   String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                   String(date.getDate()).padStart(2, '0');
        }

            function formatTime(date)
            {
            return String(date.getHours()).padStart(2, '0') + ':' + 
                   String(date.getMinutes()).padStart(2, '0');
        }

            window.addEventListener('load', function()
            {
            const now = new Date();
            document.getElementById('data').value = formatDate(now);
            document.getElementById('ora').value = formatTime(now);
        });

            function openDiagnosticSearch()
            {
            document.getElementById('diagnosticSearchModal').style.display = 'block';
        }

            function closeDiagnosticSearch()
            {
            document.getElementById('diagnosticSearchModal').style.display = 'none';
        }

            function selectDiagnostic(code, diagnostic)
            {
            document.getElementById('selectedDiagnosticCode').value = code;
            document.getElementById('selectedDiagnosticDisplay').value = `${code} - ${diagnostic}`;
            closeDiagnosticSearch();
        }

            function toggleCategory(index)
            {
                const content = document.getElementById(`category-${index}`);
                const header = content.previousElementSibling;
                const icon = header.querySelector('.toggle-icon');
                
                if (content.style.display === 'none')
                {
                    content.style.display = 'block';
                    header.classList.remove('collapsed');
                }
                else
                {
                    content.style.display = 'none';
                    header.classList.add('collapsed');
                }
            }

            function filterCategory(categoryIndex)
            {
                const searchInput = document.getElementById(`categorySearch-${categoryIndex}`);
                const searchText = searchInput.value.toLowerCase();
                const categoryContent = document.getElementById(`category-${categoryIndex}`);
                const rows = categoryContent.getElementsByClassName('diagnostic-row');
                
                for (let row of rows)
                {
                    const code = row.getAttribute('data-code').toLowerCase();
                    const diagnostic = row.getAttribute('data-diagnostic').toLowerCase();
                    
                    if (code.includes(searchText) || diagnostic.includes(searchText))
                    {
                        row.style.display = '';
                    }
                    else
                    {
                        row.style.display = 'none';
                    }
                }
            }

            function filterDiagnostics()
            {
            const searchText = document.getElementById('diagnosticSearchInput').value.toLowerCase();
            const rows = document.getElementsByClassName('diagnostic-row');
                let foundAny = false;
                
                document.querySelectorAll('.category-search-input').forEach(input => {
                    input.value = '';
                });
                
                for (let row of rows)
                {
                const code = row.getAttribute('data-code').toLowerCase();
                const diagnostic = row.getAttribute('data-diagnostic').toLowerCase();
                    const category = row.closest('.category-content');
                
                    if (code.includes(searchText) || diagnostic.includes(searchText))
                    {
                    row.style.display = '';
                        if (category)
                        {
                            category.style.display = 'block';
                            category.previousElementSibling.classList.remove('collapsed');
                        }
                        foundAny = true;
                    }
                    else
                    {
                    row.style.display = 'none';
                }
                }
                
                if (searchText)
                {
                    document.querySelectorAll('.category-content').forEach(content => {
                        content.style.display = 'block';
                        content.previousElementSibling.classList.remove('collapsed');
                    });
                }
            }

            window.onclick = function(event)
            {
            const modal = document.getElementById('diagnosticSearchModal');
                if (event.target === modal)
                {
                closeDiagnosticSearch();
            }
        }

            document.addEventListener('DOMContentLoaded', function()
            {
                document.querySelectorAll('.category-search-input').forEach(input =>
                {
                    input.addEventListener('keypress', function(e)
                    {
                        if (e.key === 'Enter')
                        {
                            const categoryIndex = this.id.split('-')[1];
                            filterCategory(categoryIndex);
                        }
                    });
                });
            });

            function toggleInvestigationCategory(index)
            {
                const content = document.getElementById(`investigation-category-${index}`);
                const header = content.previousElementSibling;
                const icon = header.querySelector('.toggle-icon');
                
                if (content.style.display === 'none')
                {
                    content.style.display = 'block';
                    header.classList.remove('collapsed');
                }
                else
                {
                    content.style.display = 'none';
                    header.classList.add('collapsed');
                }
            }

            function filterInvestigationCategory(index)
            {
                const searchInput = document.getElementById(`investigationSearch-${index}`);
                const searchText = searchInput.value.toLowerCase();
                const categoryContent = document.getElementById(`investigation-category-${index}`);
                const rows = categoryContent.getElementsByClassName('investigation-row');
                
                for (let row of rows)
                {
                    const code = row.getAttribute('data-code').toLowerCase();
                    const investigation = row.getAttribute('data-investigation').toLowerCase();
                    
                    if (code.includes(searchText) || investigation.includes(searchText))
                    {
                        row.style.display = '';
                    }
                    else
                    {
                        row.style.display = 'none';
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', function()
            {
                const profileIcon = document.getElementById('profileIcon');
                const dropdownMenu = document.querySelector('.dropdown-menu');

                if (profileIcon && dropdownMenu)
                {
                    profileIcon.addEventListener('click', function(e)
                    {
                        e.stopPropagation();
                        dropdownMenu.classList.toggle('show');
                    });

                    document.addEventListener('click', function(e)
                    {
                        if (!profileIcon.contains(e.target) && !dropdownMenu.contains(e.target))
                        {
                            dropdownMenu.classList.remove('show');
                        }
                    });
                }
            });

            function deleteConsultation(consultationId)
            {
                if (confirm('Sigur doriți să ștergeți această consultație?'))
                {
                    fetch('delete_consultation.php',
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'consultation_id=' + consultationId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success)
                        {
                            const row = document.querySelector(`tr[data-consultation-id="${consultationId}"]`);
                            if (row)
                            {
                                row.remove();
                            }
                            alert('Consultația a fost ștearsă cu succes!');
                            location.reload();
                        }
                        else
                        {
                            alert('Eroare la ștergerea consultației: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('A apărut o eroare la ștergerea consultației.');
                    });
                }
            }

            function validateForm()
            {
                const prescriptionCodes = document.getElementsByName('reteta_cod[]');
                for (let code of prescriptionCodes)
                {
                    console.log('Cod reteta:', code.value);
                    if (code.value.trim() === '')
                    {
                        alert('Introdu un cod de reteta');
                        return false;
                    }
                }
                return true;
        }
    </script>
</body>
</html>
