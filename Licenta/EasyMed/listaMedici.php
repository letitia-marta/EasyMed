<?php
    include('db_connection.php');
    session_start();

    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT rol FROM utilizatori WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $role = $user['rol'];
    }

    if (!isset($_SESSION['user_id'])) {
        header("Location: pacientiLogin.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM pacienti WHERE utilizator_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
    $pacient_id = $patient['id'];
    $stmt->close();

    $specialtyQuery = "SELECT DISTINCT specializare FROM medici ORDER BY specializare";
    $specialtyResult = $conn->query($specialtyQuery);
    $specialties = [];
    while ($row = $specialtyResult->fetch_assoc()) {
        $specialties[] = $row['specializare'];
    }

    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'nume';
    $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $specialty = isset($_GET['specialty']) ? $conn->real_escape_string($_GET['specialty']) : '';
    $showOnlyMyDoctors = isset($_GET['my_doctors']) ? true : false;

    $nextOrderNume = ($sort === 'nume' && $order === 'ASC') ? 'DESC' : 'ASC';
    $nextOrderSpecializare = ($sort === 'specializare' && $order === 'ASC') ? 'DESC' : 'ASC';

    $sql = "SELECT DISTINCT m.id, m.nume, m.prenume, m.specializare 
            FROM medici m";
    
    if ($showOnlyMyDoctors) {
        $sql .= " INNER JOIN doctor_pacient dp ON m.id = dp.doctor_id 
                  WHERE dp.pacient_id = $pacient_id";
    } else {
        $sql .= " WHERE 1=1";
    }
    
    if ($search) {
        $sql .= " AND (m.nume LIKE '%$search%' OR m.prenume LIKE '%$search%' OR m.specializare LIKE '%$search%')";
    }
    
    if ($specialty) {
        $sql .= " AND m.specializare = '$specialty'";
    }
    
    $sql .= " ORDER BY $sort $order";
    
    $result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EasyMed pentru pacienți</title>
        <link rel="stylesheet" href="style.css">
        <style>
            a, button, input, select, .button, .sort-button, .dropdown-item {
                transition: none !important;
            }

            .card {
                background: rgba(42, 54, 63, 0.9);
                border: 2px solid white;
                border-radius: 10px;
                padding: 2rem;
                min-width: 250px;
                transition: none !important;
            }

            .card:hover {
                background: rgba(42, 54, 63, 0.9) !important;
                color: whitesmoke !important;
                transform: none !important;
                box-shadow: none !important;
            }

            .card .section-divider {
                color: rgba(255, 255, 255, 0.7) !important;
            }

            .card:hover .section-divider {
                color: rgba(255, 255, 255, 0.7) !important;
            }

            .navigation a:hover,
            .navigation a:focus,
            .navigation a:active {
                text-decoration: none !important;
                color: inherit !important;
                background: none !important;
            }

            .menu-opened li a:hover,
            .menu-opened li a:focus,
            .menu-opened li a:active {
                text-decoration: none !important;
                color: inherit !important;
                background: none !important;
            }

            .profile-dropdown img:hover,
            .profile-dropdown img:focus,
            .profile-dropdown img:active {
                opacity: 1 !important;
                transform: none !important;
            }

            .dropdown-item:hover,
            .dropdown-item:focus,
            .dropdown-item:active {
                background: #13181d !important;
                color: white !important;
                text-decoration: none !important;
            }

            .button {
                background: #5cf9c8 !important;
                color: black !important;
                padding: 0.5rem 1rem;
                border-radius: 4px;
                text-decoration: none;
                cursor: pointer;
            }

            .button:hover,
            .button:focus,
            .button:active {
                background: #5cf9c8 !important;
                color: black !important;
                transform: none !important;
                box-shadow: none !important;
            }

            .sort-button:hover,
            .sort-button:focus,
            .sort-button:active {
                background: #13181d !important;
                color: white !important;
                transform: none !important;
                box-shadow: none !important;
            }

            .sort-button.active:hover,
            .sort-button.active:focus,
            .sort-button.active:active {
                background: #5cf9c8 !important;
                color: black !important;
            }

            .filters {
                background: #2A363F;
                padding: 1rem;
                border-radius: 8px;
                margin-bottom: 1rem;
            }

            .search-box {
                display: flex;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .search-box input, .search-box select {
                padding: 0.5rem;
                border: none;
                border-radius: 4px;
                background: #13181d;
                color: white;
            }

            .sort-options {
                display: flex;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .sort-button {
                padding: 0.5rem 1rem;
                border: none;
                border-radius: 4px;
                background: #13181d;
                color: white;
                cursor: pointer;
            }

            .sort-button:hover {
                background: #13181d;
                color: white;
            }

            .sort-button.active {
                background: #5cf9c8;
                color: black;
            }

            .doctor-list {
                list-style: none;
                padding: 0;
            }

            .doctor-item {
                background: #13181d;
                margin-bottom: 0.5rem;
                padding: 1rem;
                border-radius: 4px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .doctor-info {
                flex-grow: 1;
            }

            .button {
                background: #5cf9c8;
                color: black;
                padding: 0.5rem 1rem;
                border-radius: 4px;
                text-decoration: none;
                cursor: pointer;
            }

            .button:hover {
                background: #5cf9c8;
                color: black;
            }

            .no-results {
                text-align: center;
                padding: 2rem;
                color: white;
            }

            .search-box label {
                white-space: nowrap;
                user-select: none;
            }

            .search-box input[type="checkbox"] {
                width: 16px;
                height: 16px;
                cursor: pointer;
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
            }

            .dropdown-item:hover {
                background: #13181d;
                color: white;
            }

            .dropdown-item:first-child {
                border-radius: 8px 8px 0 0;
            }

            .dropdown-item:last-child {
                border-radius: 0 0 8px 8px;
            }
            
            .back-btn {
                background: #000000 !important;
                color: white !important;
                padding: 0.5rem 1rem;
                border-radius: 4px;
                text-decoration: none;
                display: inline-block;
                border: none;
                cursor: pointer;
                font-weight: bold;
            }
            
            .back-btn:hover,
            .back-btn:focus,
            .back-btn:active {
                background: #000000 !important;
                color: white !important;
                text-decoration: none !important;
                transform: none !important;
                box-shadow: none !important;
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

        <section class="content">
            <div class="cards wrapper">
                <div class="card">
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <a href="dashboardPacienti.php" class="back-btn">← Înapoi la dashboard</a>
                    </div>
                    <h2 class="card-title">Medici</h2>
                    <span class="section-divider">──────────────────────────────────────────────────────────── ⋆⋅☆⋅⋆ ───────────────────────────────────────────────────────────</span>
                    
                    <div class="filters">
                        <form id="filterForm" method="GET" action="">
                            <div class="search-box">
                                <input type="text" 
                                    name="search" 
                                    placeholder="Caută după nume sau specializare..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                                <select name="specialty">
                                    <option value="">Toate specializările</option>
                                    <?php foreach ($specialties as $spec): ?>
                                        <option value="<?php echo htmlspecialchars($spec); ?>"
                                            <?php echo $specialty === $spec ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($spec); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label>
                                    <input type="checkbox" name="my_doctors" <?php echo $showOnlyMyDoctors ? 'checked' : ''; ?> onchange="this.form.submit()">
                                    Arată doar medicii mei
                                </label>
                            </div>
                            <div class="sort-options">
                                <button type="button" class="sort-button <?php echo $sort === 'nume' ? 'active' : ''; ?>" 
                                    onclick="sortBy('nume')">
                                    Nume
                                    <?php if ($sort === 'nume'): ?>
                                        <?php echo $order === 'ASC' ? '↑' : '↓'; ?>
                                    <?php endif; ?>
                                    <input type="hidden" name="order" value="<?php echo $nextOrderNume; ?>">
                                </button>
                                <button type="button" class="sort-button <?php echo $sort === 'specializare' ? 'active' : ''; ?>" 
                                    onclick="sortBy('specializare')">
                                    Specializare
                                    <?php if ($sort === 'specializare'): ?>
                                        <?php echo $order === 'ASC' ? '↑' : '↓'; ?>
                                    <?php endif; ?>
                                    <input type="hidden" name="order" value="<?php echo $nextOrderSpecializare; ?>">
                                </button>
                            </div>
                        </form>
                    </div>

                    <?php if ($result && $result->num_rows > 0): ?>
                        <ul class="doctor-list">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <li class="doctor-item">
                                    <div class="doctor-info">
                                        <strong><?php echo htmlspecialchars($row['nume'] . ' ' . $row['prenume']); ?></strong>
                                        <br>
                                        <?php echo htmlspecialchars($row['specializare']); ?>
                                    </div>
                                    <button onclick="selectDoctor(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nume'] . ' ' . $row['prenume'] . ' - ' . $row['specializare']); ?>')" 
                                            class="button" id="doctorBtn_<?php echo $row['id']; ?>">Programează-te</button>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="no-results">Nu au fost găsiți medici care să corespundă criteriilor de căutare.</p>
                    <?php endif; ?>
                </div>         
            </div>            
        </section>

        <footer>
            <div class="wrapper">
                <p>EasyMed © 2024</p>
            </div>
        </footer>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelector('select[name="specialty"]').addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });

                document.getElementById('filterForm').addEventListener('submit', function() {
                    document.body.style.cursor = 'wait';
                });

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
            });

            function selectDoctor(doctorId, doctorName) {
                window.location.href = `programarePacienti.php?doctor=${doctorId}`;
            }

            function sortBy(field) {
                const form = document.getElementById('filterForm');
                const currentOrder = form.querySelector('input[name="order"]').value;
                const nextOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
                
                form.querySelector('input[name="order"]').value = nextOrder;
                
                const sortInput = document.createElement('input');
                sortInput.type = 'hidden';
                sortInput.name = 'sort';
                sortInput.value = field;
                form.appendChild(sortInput);
                
                form.submit();
            }
        </script>
    </body>
</html>

<?php
    $conn->close();
?>
