<?php
/**
 * Pagină pentru vizualizarea programărilor medicale
 * 
 * Această pagină oferă o interfață calendar pentru vizualizarea programărilor:
 * - Afișează un calendar lunar cu programările medicului
 * - Permite navigarea între luni și ani
 * - Marchează zilele cu programări
 * - Afișează detaliile programărilor pentru ziua selectată
 * - Include funcționalități de filtrare și navigare
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

    session_start();
    require_once 'db_connection.php';

    // Verifică dacă utilizatorul este autentificat
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // Obține ID-ul medicului din sesiune
    $doctorQuery = "SELECT id FROM medici WHERE utilizator_id = ?";
    $doctorStmt = mysqli_prepare($conn, $doctorQuery);
    mysqli_stmt_bind_param($doctorStmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($doctorStmt);
    $doctorResult = mysqli_stmt_get_result($doctorStmt);
    $doctor = mysqli_fetch_assoc($doctorResult);
    $doctorId = $doctor['id'];
    mysqli_stmt_close($doctorStmt);

    // Extrage luna și anul din parametrii GET sau folosește data curentă
    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
    $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

    // Array cu numele lunilor în română
    $romanianMonths = [
        1 => 'Ianuarie', 2 => 'Februarie', 3 => 'Martie', 4 => 'Aprilie',
        5 => 'Mai', 6 => 'Iunie', 7 => 'Iulie', 8 => 'August',
        9 => 'Septembrie', 10 => 'Octombrie', 11 => 'Noiembrie', 12 => 'Decembrie'
    ];

    // Calculează informațiile pentru calendar
    $firstDay = mktime(0, 0, 0, $month, 1, $year);
    $numberDays = date('t', $firstDay);
    $dateComponents = getdate($firstDay);
    $monthName = $romanianMonths[$month];
    $dayOfWeek = $dateComponents['wday'];
    $dayOfWeek = ($dayOfWeek == 0) ? 6 : $dayOfWeek - 1;

    // Calculează luna și anul anterior pentru navigare
    $prevMonth = $month - 1;
    $prevYear = $year;
    if ($prevMonth == 0) {
        $prevMonth = 12;
        $prevYear--;
    }

    // Calculează luna și anul următor pentru navigare
    $nextMonth = $month + 1;
    $nextYear = $year;
    if ($nextMonth == 13) {
        $nextMonth = 1;
        $nextYear++;
    }
?>

<!DOCTYPE html>
<html lang="ro">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vizualizare Programări - EasyMed</title>
        <link rel="stylesheet" href="style.css">
        <style>
            /* Container-ul principal pentru calendar */
            .calendar-container {
                max-width: 1000px;
                margin: 2rem auto;
                background: #13181d;
                padding: 2rem;
                border-radius: 10px;
                color: white;
            }

            /* Header-ul calendarului cu titlu și navigare */
            .calendar-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1.5rem;
            }

            .calendar-header h2 {
                color: #5cf9c8;
                margin: 0;
            }

            /* Butoanele de navigare pentru luni */
            .calendar-nav {
                display: flex;
                gap: 1rem;
            }

            .calendar-nav button {
                background: #2A363F;
                color: white;
                border: none;
                padding: 0.5rem 1rem;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .calendar-nav button:hover {
                background: #3A4A5F;
            }

            /* Grid-ul pentru zilele calendarului */
            .calendar-grid {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 0.5rem;
                margin-bottom: 2rem;
            }

            /* Stilizarea zilelor din calendar */
            .calendar-day {
                aspect-ratio: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #2A363F;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.3s;
                color: white;
                font-weight: 500;
            }

            .calendar-day:hover {
                background: #3A4A5F;
            }

            /* Zile goale (din luna anterioară/următoare) */
            .calendar-day.empty {
                background: transparent;
                cursor: default;
            }

            /* Zile din trecut */
            .calendar-day.past-day {
                background: #444 !important;
                color: #888 !important;
                cursor: not-allowed !important;
                pointer-events: none !important;
                opacity: 0.6;
            }

            /* Ziua selectată */
            .calendar-day.selected {
                background: #5cf9c8;
                color: black;
            }

            /* Zile cu programări */
            .calendar-day.has-appointments {
                background: #5cf9c8;
                color: black;
                position: relative;
            }

            /* Indicator pentru zilele cu programări */
            .calendar-day.has-appointments::after {
                content: '';
                position: absolute;
                bottom: 2px;
                left: 50%;
                transform: translateX(-50%);
                width: 4px;
                height: 4px;
                background: #ff4444;
                border-radius: 50%;
            }

            /* Secțiunea pentru afișarea programărilor */
            .appointments-section {
                display: none;
                background: #2A363F;
                padding: 1.5rem;
                border-radius: 8px;
                margin-top: 1rem;
            }

            .appointments-section.active {
                display: block;
            }

            /* Header-ul secțiunii de programări */
            .appointments-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1rem;
            }

            .appointments-header h3 {
                color: #5cf9c8;
                margin: 0;
            }

            /* Lista de programări */
            .appointments-list {
                display: grid;
                gap: 1rem;
            }

            /* Element individual pentru o programare */
            .appointment-item {
                background: #13181d;
                padding: 1rem;
                border-radius: 6px;
                border-left: 4px solid #5cf9c8;
            }

            /* Timpul programării */
            .appointment-time {
                font-size: 1.2rem;
                font-weight: bold;
                color: #5cf9c8;
                margin-bottom: 0.5rem;
            }

            .appointment-patient {
                font-size: 1.1rem;
                color: white;
                margin-bottom: 0.5rem;
            }

            .appointment-type {
                color: #888;
                font-size: 0.9rem;
            }

            .delete-btn {
                background: #dc3545;
                color: white;
                border: none;
                padding: 0.5rem 1rem;
                border-radius: 4px;
                cursor: pointer;
                font-size: 0.9rem;
                transition: background-color 0.3s;
                flex-shrink: 0;
            }

            .delete-btn:hover {
                background: #c82333;
            }

            .appointment-item {
                background: #13181d;
                padding: 1rem;
                border-radius: 6px;
                border-left: 4px solid #5cf9c8;
                position: relative;
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
            }

            .appointment-info {
                flex: 1;
            }

            .no-appointments {
                text-align: center;
                color: #888;
                padding: 2rem;
                font-style: italic;
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

            /* Dropdown styles */
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
                <div class="calendar-container">
                    <a href="dashboardMedici.php" class="back-btn">&larr; Înapoi la dashboard</a>
                    
                    <div class="calendar-header">
                        <h2><?php echo $monthName . ' ' . $year; ?></h2>
                        <div class="calendar-nav">
                            <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>">
                                <button>Luna precedentă</button>
                            </a>
                            <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>">
                                <button>Luna următoare</button>
                            </a>
                        </div>
                    </div>

                    <div class="calendar-grid">
                        <?php
                            $dayNames = ['Lu', 'Ma', 'Mie', 'Jo', 'Vi', 'Sa', 'Du'];
                            foreach ($dayNames as $day) {
                                echo "<div class='calendar-day' style='background: transparent; font-weight: bold;'>$day</div>";
                            }
                            
                            for ($i = 0; $i < $dayOfWeek; $i++) {
                                echo "<div class='calendar-day empty'></div>";
                            }

                            for ($day = 1; $day <= $numberDays; $day++) {
                                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                $today = date('Y-m-d');
                                $class = 'calendar-day';

                                if ($date < $today) {
                                    $class .= ' past-day';
                                }
                                
                                echo "<div class='$class' data-date='$date'>$day</div>";
                            }
                        ?>
                    </div>

                    <div class="appointments-section" id="appointmentsSection">
                        <div class="appointments-header">
                            <h3 id="selectedDateTitle">Programări pentru <span id="selectedDateSpan"></span></h3>
                        </div>
                        <div id="appointmentsList" class="appointments-list">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer>
            <div class="wrapper">
                <p>EasyMed © 2024</p>
            </div>
        </footer>

        <script>
            let selectedDate = null;
            let doctorId = <?php echo $doctorId; ?>;

            async function loadAppointments(date) {
                try {
                    const response = await fetch(`get_doctor_appointments.php?date=${date}&doctor_id=${doctorId}`);
                    const appointments = await response.json();
                    
                    const appointmentsList = document.getElementById('appointmentsList');
                    
                    if (appointments.length === 0) {
                        appointmentsList.innerHTML = '<div class="no-appointments">Nu există programări pentru această dată.</div>';
                        return;
                    }
                    
                    appointments.sort((a, b) => a.time_slot.localeCompare(b.time_slot));
                    
                    let appointmentsHTML = '';
                    appointments.forEach(appointment => {
                        let timeSlot = appointment.time_slot;
                        if (timeSlot && timeSlot.includes(':')) {
                            const timeParts = timeSlot.split(':');
                            if (timeParts.length >= 2) {
                                const hours = timeParts[0].padStart(2, '0');
                                const minutes = timeParts[1].padStart(2, '0');
                                timeSlot = `${hours}:${minutes}`;
                            }
                        }
                        const patientName = appointment.patient_name;
                        const consultationType = getConsultationTypeLabel(appointment.consultation_type);
                        
                        appointmentsHTML += `
                            <div class="appointment-item">
                                <div class="appointment-info">
                                    <div class="appointment-time">${timeSlot}</div>
                                    <div class="appointment-patient">${patientName}</div>
                                    <div class="appointment-type">${consultationType}</div>
                                </div>
                                <button class="delete-btn" onclick="deleteAppointment(${appointment.id}, '${patientName.replace(/'/g, "\\'")}', '${timeSlot}')">
                                    Șterge
                                </button>
                            </div>
                        `;
                    });
                    
                    appointmentsList.innerHTML = appointmentsHTML;
                    
                } catch (error) {
                    console.error('Error loading appointments:', error);
                    document.getElementById('appointmentsList').innerHTML = 
                        '<div class="no-appointments">Eroare la încărcarea programărilor.</div>';
                }
            }

            function getConsultationTypeLabel(type) {
                const types = {
                    'consult': 'Consult',
                    'vaccinare': 'Vaccinare',
                    'monitorizare': 'Monitorizare',
                    'examen_bilant': 'Examen bilanț',
                    'prescriere_reteta': 'Prescriere rețetă cronică'
                };
                return types[type] || type;
            }

            document.addEventListener('DOMContentLoaded', function() {
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

                const calendarDays = document.querySelectorAll('.calendar-day:not(.empty)');
                
                calendarDays.forEach(day => {
                    if (day.classList.contains('past-day')) return;

                    day.addEventListener('click', async function() {
                        selectedDate = this.getAttribute('data-date');
                        console.log('Selected date:', selectedDate);
                        
                        calendarDays.forEach(d => d.classList.remove('selected'));
                        this.classList.add('selected');

                        const appointmentsSection = document.getElementById('appointmentsSection');
                        appointmentsSection.classList.add('active');
                        
                        const selectedDateSpan = document.getElementById('selectedDateSpan');
                        const dateObj = new Date(selectedDate);
                        const formattedDate = dateObj.toLocaleDateString('ro-RO', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                        selectedDateSpan.textContent = formattedDate;
                        
                        await loadAppointments(selectedDate);
                    });
                });

                const today = new Date().toISOString().split('T')[0];
                const todayElement = document.querySelector(`[data-date="${today}"]`);
                if (todayElement && !todayElement.classList.contains('past-day')) {
                    todayElement.click();
                }
            });

            async function deleteAppointment(appointmentId, patientName, timeSlot) {
                if (!confirm(`Sigur doriți să ștergeți programarea pentru ${patientName} la ${timeSlot}?`)) {
                    return;
                }

                try {
                    console.log('Deleting appointment:', appointmentId);
                    
                    const response = await fetch('delete_appointment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            appointment_id: appointmentId
                        })
                    });

                    console.log('Response status:', response.status);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    console.log('Delete result:', result);

                    if (result.success) {
                        alert('Programarea a fost ștearsă cu succes!');
                        if (selectedDate) {
                            await loadAppointments(selectedDate);
                        }
                    } else {
                        alert('Eroare la ștergerea programării: ' + (result.message || 'Eroare necunoscută'));
                    }
                } catch (error) {
                    console.error('Error deleting appointment:', error);
                    alert('Eroare la ștergerea programării. Vă rugăm să încercați din nou. Detalii: ' + error.message);
                }
            }
        </script>
    </body>
</html>

<?php
    $conn->close();
?> 