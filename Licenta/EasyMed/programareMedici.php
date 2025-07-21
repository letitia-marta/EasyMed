<?php
    session_start();
    require_once 'db_connection.php';

    $doctorQuery = "SELECT id FROM medici WHERE utilizator_id = ?";
    $doctorStmt = mysqli_prepare($conn, $doctorQuery);
    mysqli_stmt_bind_param($doctorStmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($doctorStmt);
    $doctorResult = mysqli_stmt_get_result($doctorStmt);
    $doctor = mysqli_fetch_assoc($doctorResult);
    $doctorId = $doctor['id'];
    mysqli_stmt_close($doctorStmt);

    error_log("Logged in doctor ID: " . $doctorId);

    $selectedDoctorId = isset($_GET['doctor']) ? (int)$_GET['doctor'] : null;
    error_log("Selected doctor ID: " . $selectedDoctorId);

    $doctorQuery = "SELECT id, nume, prenume, specializare FROM medici";
    $doctorResult = mysqli_query($conn, $doctorQuery);
    $doctors = [];

    if ($doctorResult && mysqli_num_rows($doctorResult) > 0)
    {
        while ($row = mysqli_fetch_assoc($doctorResult))
        {
            $doctors[] = $row;
        }
    }
    error_log("Available doctors: " . print_r($doctors, true));

    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
    $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

    $romanianMonths = [
        1 => 'Ianuarie', 2 => 'Februarie', 3 => 'Martie', 4 => 'Aprilie',
        5 => 'Mai', 6 => 'Iunie', 7 => 'Iulie', 8 => 'August',
        9 => 'Septembrie', 10 => 'Octombrie', 11 => 'Noiembrie', 12 => 'Decembrie'
    ];

    $firstDay = mktime(0, 0, 0, $month, 1, $year);
    $numberDays = date('t', $firstDay);
    $dateComponents = getdate($firstDay);
    $monthName = $romanianMonths[$month];
    $dayOfWeek = $dateComponents['wday'];
    $dayOfWeek = ($dayOfWeek == 0) ? 6 : $dayOfWeek - 1;

    $prevMonth = $month - 1;
    $prevYear = $year;
    if ($prevMonth == 0)
    {
        $prevMonth = 12;
        $prevYear--;
    }

    $nextMonth = $month + 1;
    $nextYear = $year;
    if ($nextMonth == 13)
    {
        $nextMonth = 1;
        $nextYear++;
    }

    if (!$conn) {
        die("Database connection failed");
    }

    if (isset($_GET['action']) && $_GET['action'] === 'search_patients') {
        $search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $patients_per_page = 10;
        $offset = ($page - 1) * $patients_per_page;
        
        if (!empty($search_query)) {
            $patientsQuery = "SELECT id, nume, prenume FROM pacienti 
                             WHERE nume LIKE ? OR prenume LIKE ? OR CONCAT(nume, ' ', prenume) LIKE ?
                             ORDER BY nume, prenume 
                             LIMIT ? OFFSET ?";
            $search_param = "%$search_query%";
            $stmt = mysqli_prepare($conn, $patientsQuery);
            mysqli_stmt_bind_param($stmt, "sssii", $search_param, $search_param, $search_param, $patients_per_page, $offset);
        } else {
            $patientsQuery = "SELECT id, nume, prenume FROM pacienti 
                             ORDER BY nume, prenume 
                             LIMIT ? OFFSET ?";
            $stmt = mysqli_prepare($conn, $patientsQuery);
            mysqli_stmt_bind_param($stmt, "ii", $patients_per_page, $offset);
        }
        
        mysqli_stmt_execute($stmt);
        $patientsResult = mysqli_stmt_get_result($stmt);
        
        $patients = [];
        if (mysqli_num_rows($patientsResult) > 0) {
            while ($row = mysqli_fetch_assoc($patientsResult)) {
                $row['id'] = (string)$row['id'];
                $patients[] = $row;
            }
        }
        
        if (!empty($search_query)) {
            $countQuery = "SELECT COUNT(*) as total FROM pacienti 
                          WHERE nume LIKE ? OR prenume LIKE ? OR CONCAT(nume, ' ', prenume) LIKE ?";
            $countStmt = mysqli_prepare($conn, $countQuery);
            mysqli_stmt_bind_param($countStmt, "sss", $search_param, $search_param, $search_param);
        } else {
            $countQuery = "SELECT COUNT(*) as total FROM pacienti";
            $countStmt = mysqli_prepare($conn, $countQuery);
        }
        
        mysqli_stmt_execute($countStmt);
        $countResult = mysqli_stmt_get_result($countStmt);
        $totalPatients = mysqli_fetch_assoc($countResult)['total'];
        $totalPages = ceil($totalPatients / $patients_per_page);
        
        header('Content-Type: application/json');
        echo json_encode([
            'patients' => $patients,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_patients' => $totalPatients,
                'patients_per_page' => $patients_per_page
            ]
        ]);
        exit();
    }

    $patientsQuery = "SELECT id, nume, prenume FROM pacienti ORDER BY nume, prenume LIMIT 10";
    $patientsResult = mysqli_query($conn, $patientsQuery);
    
    if (!$patientsResult) {
        die("Query failed: " . mysqli_error($conn));
    }

    $patients = [];
    if (mysqli_num_rows($patientsResult) > 0) {
        while ($row = mysqli_fetch_assoc($patientsResult)) {
            $row['id'] = (string)$row['id'];
            $patients[] = $row;
        }
    } else {
        error_log("No patients found in database");
    }

    error_log("Number of patients found: " . count($patients));
    error_log("Patients data: " . print_r($patients, true));

    $tableQuery = "DESCRIBE pacienti";
    $tableResult = mysqli_query($conn, $tableQuery);
    if ($tableResult) {
        $tableStructure = [];
        while ($row = mysqli_fetch_assoc($tableResult)) {
            $tableStructure[] = $row;
        }
        error_log("Pacienti table structure: " . print_r($tableStructure, true));
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Programare Medici</title>
        <link rel="stylesheet" href="style.css">
        <style>
            .time-slot {
                padding: 10px 20px;
                margin: 5px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #fff;
                cursor: pointer;
                transition: all 0.3s ease;
                color: white;
            }

            .time-slot:hover {
                background-color: #f0f0f0;
            }

            .time-slot.selected {
                background-color: #4CAF50;
                color: white;
                border-color: #4CAF50;
            }

            .time-slot.past {
                background-color: #444;
                color: #888;
                cursor: not-allowed;
                border-color: #444;
                opacity: 0.6;
            }

            .time-slot.past:hover {
                background-color: #444;
                transform: none;
            }

            .time-slot.occupied {
                background-color: #ff4444;
                color: white;
                cursor: not-allowed;
                border-color: #ff4444;
                opacity: 0.7;
            }

            .time-slot.occupied:hover {
                background-color: #ff4444;
                transform: none;
            }

            .time-slot:disabled {
                opacity: 0.7;
                cursor: not-allowed;
            }

            .past-day {
                color:rgb(0, 0, 0);
                cursor: not-allowed;
            }

            #doctorSelect {
                background: #13181d;
                color: white;
            }

            .content {
                background: #2A363F;
            }

            body {
                background-color: #2A363F;
                margin: 0;
                padding: 0;
            }

            .calendar-container {
                max-width: 800px;
                margin: 2rem auto;
                background:rgb(24, 31, 36);
                padding: 2rem;
                border-radius: 10px;
            }

            .calendar-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1rem;
            }

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
            }

            .calendar-grid {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 0.5rem;
            }

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
            }

            .calendar-day:hover {
                background: #3A4A5F;
            }

            .calendar-day.empty {
                background: transparent;
                cursor: default;
            }

            .calendar-day.past-day {
                background: #444 !important;
                color: #888 !important;
                cursor: not-allowed !important;
                pointer-events: none !important;
                opacity: 0.6;
            }

            .calendar-day.selected {
                background: #5cf9c8;
                color: black;
            }

            .time-slots {
                display: none;
                margin-top: 2rem;
                background: #2A363F;
                padding: 1rem;
                border-radius: 10px;
            }

            .time-slots.active {
                display: block;
            }

            .time-slot {
                display: inline-block;
                padding: 0.5rem 1rem;
                margin: 0.5rem;
                background: #13181d;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.3s;
                color: white;
            }

            .time-slot:hover {
                background: #3A4A5F;
            }

            .time-slot.selected {
                background: #5cf9c8;
                color: black;
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

            .date-picker input[type="date"] {
                padding: 0.5rem;
                border: 1px solid #2A363F;
                border-radius: 4px;
                background: #2A363F;
                color: white;
            }

            .date-picker input[type="date"]::-webkit-calendar-picker-indicator {
                filter: invert(1);
            }

            .hour-button.past {
                background-color: #1a1a1a;
                color: #666;
                cursor: not-allowed;
                opacity: 0.5;
            }

            .hour-button.past:hover {
                background-color: #1a1a1a;
                transform: none;
            }

            .hour-button.past.selected {
                background-color: #1a1a1a;
                color: #666;
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
                <div class="calendar-container" style="margin-bottom: 1rem;">
                    <a href="dashboardMedici.php" class="back-btn">&larr; Înapoi la dashboard</a>
                    <div style="display: flex; justify-content: center;">
                    <button id="selectPatientBtn" style="padding: 1rem 2rem; font-size: 1rem; background-color: #13181d; color: white; border: none; border-radius: 8px; cursor: pointer;">
                        <span id="selectedPatientName">Selectează pacientul</span>
                    </button>
                    </div>
                </div>

                <div class="calendar-container">
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
                            foreach ($dayNames as $day)
                            {
                                echo "<div class='calendar-day' style='background: transparent;'>$day</div>";
                            }
                            for ($i = 0; $i < $dayOfWeek; $i++)
                            {
                                echo "<div class='calendar-day empty'></div>";
                            }

                            for ($day = 1; $day <= $numberDays; $day++)
                            {
                                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                $today = date('Y-m-d');
                                $class = 'calendar-day';

                                if ($date < $today)
                                {
                                    $class .= ' past-day';
                                }
                                echo "<div class='$class' data-date='$date'>$day</div>";
                            }
                        ?>
                    </div>
                    <div class="time-slots" id="timeSlots" style="display: none;">
                        <h3 style="color: white;">Selectează tipul consultației</h3>
                        <div style="margin-bottom: 1rem;">
                            <select id="consultationType" style="width: 100%; padding: 0.5rem; background: #13181d; color: white; border: none; border-radius: 4px;">
                                <option value="">Selectează motivul consultației</option>
                                <option value="consult">Consult</option>
                                <option value="vaccinare">Vaccinare</option>
                                <option value="monitorizare">Monitorizare</option>
                                <option value="examen_bilant">Examen bilanț</option>
                                <option value="prescriere_reteta">Prescriere rețetă cronică</option>
                            </select>
                        </div>
                        <h3 style="color: white;">Selectează ora</h3>
                        <div id="availableSlots"></div>
                    </div>

                    <div id="patientModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                        background-color: rgba(0, 0, 0, 0.6); z-index: 9999; align-items: center; justify-content: center;">
                        <div style="background-color: #2A363F; padding: 2rem; border-radius: 10px; width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto;">
                            <h2 style="color: white; margin-top: 0;">Caută pacient</h2>
                            
                            <div style="margin-bottom: 1rem;">
                                <input type="text" id="searchPatient" placeholder="Caută după nume..." 
                                       style="width: 100%; padding: 0.8rem; border: 1px solid #5cf9c8; border-radius: 4px; background: #13181d; color: white; font-size: 1rem;">
                            </div>
                            
                            <div id="searchResultsInfo" style="color: #888; font-size: 0.9rem; margin-bottom: 1rem; display: none;"></div>
                            
                            <ul id="patientList" style="list-style: none; padding: 0; max-height: 300px; overflow-y: auto; margin-bottom: 1rem;"></ul>
                            
                            <div id="paginationContainer" style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin: 1rem 0;"></div>
                            
                            <div style="margin-top: 1rem; color: white;" id="selectedPatientPreview"></div>
                            <div style="margin-top: 1rem; display: flex; justify-content: space-between;">
                                <button id="closeModalBtn" style="padding: 0.5rem 1rem; background: #444; color: white; border: none; border-radius: 4px;">Închide</button>
                                <button id="confirmPatientBtn" style="padding: 0.5rem 1rem; background: #5cf9c8; color: black; border: none; border-radius: 4px;">OK</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <script>
            const patients = <?php echo json_encode($patients); ?>;
            let selectedPatientId = null;
            let selectedDate = null;
            let selectedTime = null;
            let selectedDoctorId = <?php echo $doctorId; ?>;
            let selectedConsultationType = null;

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

                console.log('Patients loaded:', patients);
                const calendarDays = document.querySelectorAll('.calendar-day:not(.empty)');
                const timeSlots = document.getElementById('timeSlots');
                const availableSlots = document.getElementById('availableSlots');
                const consultationType = document.getElementById('consultationType');

                if (consultationType) {
                    consultationType.addEventListener('change', function() {
                        selectedConsultationType = this.value;
                        console.log('Consultation type selected:', selectedConsultationType);
                    });
                }

                const saveButton = document.createElement('button');
                saveButton.id = 'saveAppointment';
                saveButton.textContent = 'Salvează programarea';
                saveButton.style.display = 'none';
                saveButton.style.marginTop = '1rem';
                saveButton.style.padding = '0.5rem 1rem';
                saveButton.style.background = '#5cf9c8';
                saveButton.style.color = 'black';
                saveButton.style.border = 'none';
                saveButton.style.borderRadius = '4px';
                saveButton.style.cursor = 'pointer';
                timeSlots.appendChild(saveButton);

                saveButton.addEventListener('click', function() {
                    console.log('Save button clicked');
                    console.log('Current state:', { selectedPatientId, selectedDate, selectedTime });
                    
                    if (selectedPatientId && selectedDate && selectedTime && selectedDoctorId && selectedConsultationType) {
                        const formData = new FormData();
                        formData.append('patient_id', selectedPatientId);
                        formData.append('date', selectedDate);
                        formData.append('time', selectedTime);
                        formData.append('doctor_id', selectedDoctorId);
                        formData.append('consultation_type', selectedConsultationType);

                        console.log('Sending data to server:', {
                            patient_id: selectedPatientId,
                            date: selectedDate,
                            time: selectedTime,
                            doctor_id: selectedDoctorId,
                            consultation_type: selectedConsultationType
                        });

                        fetch('save_appointment.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            console.log('Server response status:', response.status);
                            return response.json();
                        })
                        .then(data => {
                            console.log('Server response data:', data);
                            if (data.success) {
                                alert('Programarea a fost salvată cu succes!');
                                selectedDate = null;
                                selectedTime = null;
                                selectedPatientId = null;
                                document.getElementById('selectedPatientName').textContent = 'Selectează pacientul';
                                timeSlots.classList.remove('active');
                                timeSlots.style.display = 'none';
                                saveButton.style.display = 'none';
                                
                                document.querySelectorAll('.calendar-day').forEach(day => {
                                    day.classList.remove('selected');
                                });
                                
                                document.querySelectorAll('.time-slot').forEach(slot => {
                                    slot.classList.remove('selected');
                                });
                                
                                calendarDays.forEach(day => {
                                    if (!day.classList.contains('past-day')) {
                                        day.style.pointerEvents = 'none';
                                    }
                                });
                            } else {
                                alert(data.message || 'Eroare la salvarea programării.');
                            }
                        })
                        .catch(error => {
                            console.error('Error saving appointment:', error);
                            alert('Eroare la salvarea programării. Vă rugăm să încercați din nou.');
                        });
                    } else {
                        console.log('Missing data:', { selectedPatientId, selectedDate, selectedTime });
                        alert('Vă rugăm să selectați un pacient, o dată și o oră.');
                    }
                });

                const selectBtn = document.getElementById('selectPatientBtn');
                const modal = document.getElementById('patientModal');
                const patientList = document.getElementById('patientList');
                const searchInput = document.getElementById('searchPatient');
                const selectedNameDisplay = document.getElementById('selectedPatientName');

                if (selectBtn) {
                    selectBtn.addEventListener('click', () => {
                        if (modal) {
                            modal.style.display = 'flex';
                            currentSearchQuery = '';
                            currentPage = 1;
                            if (searchInput) {
                                searchInput.value = '';
                            }
                            loadPatients('', 1);
                        }
                    });
                }

                const closeModalBtn = document.getElementById('closeModalBtn');
                const confirmPatientBtn = document.getElementById('confirmPatientBtn');

                if (closeModalBtn) {
                    closeModalBtn.addEventListener('click', () => {
                        if (modal) {
                            modal.style.display = 'none';
                        }
                    });
                }

                if (confirmPatientBtn) {
                    confirmPatientBtn.addEventListener('click', confirmPatientSelection);
                }

                calendarDays.forEach(day => {
                    if (day.classList.contains('past-day')) return;

                    day.addEventListener('click', async function() {
                        console.log('Day clicked, current patient ID:', selectedPatientId);
                        
                        if (!selectedPatientId) {
                            console.log('Please select a patient first');
                            return;
                        }
                        
                        selectedDate = this.getAttribute('data-date');
                        console.log('Selected date:', selectedDate);
                        
                        calendarDays.forEach(d => d.classList.remove('selected'));
                        this.classList.add('selected');

                        timeSlots.style.display = 'block';
                        timeSlots.classList.add('active');
                        availableSlots.innerHTML = '';
                        saveButton.style.display = 'none';

                        try {
                            const response = await fetch(`get_appointments.php?date=${selectedDate}&doctor_id=${selectedDoctorId}`);
                            const existingAppointments = await response.json();
                            console.log('Existing appointments:', existingAppointments);

                        const now = new Date();
                        const todayStr = now.toISOString().slice(0, 10);

                            for (let hour = 8; hour <= 16; hour++) {
                                for (let minute of ['00', '30']) {
                                    const time = `${hour.toString().padStart(2, '0')}:${minute}`;
                                    const slotDateTime = new Date(`${selectedDate}T${time}:00`);

                                let isPast = false;
                                    if (selectedDate < todayStr) {
                                    isPast = true;
                                    } else if (selectedDate === todayStr && slotDateTime <= now) {
                                    isPast = true;
                                }

                                    const isOccupied = existingAppointments.some(apt => {
                                        console.log('Comparing:', apt.time_slot, 'with', time);
                                        return apt.time_slot === time;
                                    });
                                    console.log(`Time ${time} is ${isOccupied ? 'occupied' : 'available'}`);

                                    const isAvailable = !isPast && !isOccupied;

                                    const timeSlot = document.createElement('button');
                                    timeSlot.type = 'button';
                                    timeSlot.className = `time-slot ${isPast ? 'past' : (isOccupied ? 'occupied' : 'available')}`;
                                    timeSlot.textContent = time;
                                    timeSlot.disabled = !isAvailable;
                                    timeSlot.title = isPast ? 'Interval orar trecut' : (isOccupied ? 'Acest interval orar este ocupat' : '');

                                    if (isAvailable) {
                                        timeSlot.addEventListener('click', function() {
                                        document.querySelectorAll('.time-slot').forEach(slot => {
                                            slot.classList.remove('selected');
                                        });
                                        this.classList.add('selected');
                                            selectedTime = time;
                                            console.log('Time selected:', selectedTime);
                                        saveButton.style.display = 'block';
                                    });
                                }

                                availableSlots.appendChild(timeSlot);
                            }
                        }
                        } catch (error) {
                            console.error('Error fetching appointments:', error);
                            alert('Eroare la încărcarea programărilor existente.');
                        }
                });
            });

                let currentSearchQuery = '';
                let currentPage = 1;
                let totalPages = 1;
                let totalPatients = 0;

                async function loadPatients(searchQuery = '', page = 1) {
                    try {
                        const params = new URLSearchParams({
                            action: 'search_patients',
                            search: searchQuery,
                            page: page
                        });

                        const response = await fetch(`programareMedici.php?${params}`);
                        const data = await response.json();

                        if (data.patients && data.pagination) {
                            renderPatients(data.patients);
                            renderPagination(data.pagination);
                            updateSearchResultsInfo(data.pagination);
                        }
                    } catch (error) {
                        console.error('Error loading patients:', error);
                    }
                }

                function renderPatients(patients) {
                    if (patientList) {
                        patientList.innerHTML = '';
                        
                        if (patients.length === 0) {
                            const li = document.createElement('li');
                            li.textContent = 'Nu s-au găsit pacienți.';
                            li.style.padding = '1rem';
                            li.style.color = '#888';
                            li.style.textAlign = 'center';
                            patientList.appendChild(li);
                            return;
                        }

                        patients.forEach(p => {
                            const li = document.createElement('li');
                            li.textContent = `${p.nume} ${p.prenume}`;
                            li.style.padding = '0.8rem';
                            li.style.color = 'white';
                            li.style.cursor = 'pointer';
                            li.style.borderBottom = '1px solid #444';
                            li.style.transition = 'background-color 0.3s';
                            
                            li.addEventListener('click', () => {
                                selectedPatientId = p.id;
                                console.log('Patient selected:', selectedPatientId);
                                
                                const preview = document.getElementById('selectedPatientPreview');
                                if (preview) {
                                    preview.textContent = `Selectat: ${p.nume} ${p.prenume}`;
                                }
                                
                                document.querySelectorAll('#patientList li').forEach(item => {
                                    item.style.background = 'transparent';
                                });
                                li.style.background = '#3A4A5F';
                            });
                            
                            patientList.appendChild(li);
                        });
                    }
                }

                function renderPagination(pagination) {
                    const container = document.getElementById('paginationContainer');
                    if (!container) return;

                    container.innerHTML = '';
                    
                    if (pagination.total_pages <= 1) {
                        container.style.display = 'none';
                        return;
                    }

                    container.style.display = 'flex';

                    if (pagination.current_page > 1) {
                        const prevBtn = document.createElement('button');
                        prevBtn.textContent = '← Anterior';
                        prevBtn.style.cssText = 'padding: 0.5rem 1rem; background: #2A363F; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem;';
                        prevBtn.addEventListener('click', () => {
                            currentPage = pagination.current_page - 1;
                            loadPatients(currentSearchQuery, currentPage);
                        });
                        container.appendChild(prevBtn);
                    } else {
                        const prevBtn = document.createElement('span');
                        prevBtn.textContent = '← Anterior';
                        prevBtn.style.cssText = 'padding: 0.5rem 1rem; background: #1a2128; color: #666; border: none; border-radius: 4px; font-size: 0.9rem; cursor: not-allowed;';
                        container.appendChild(prevBtn);
                    }

                    const startPage = Math.max(1, pagination.current_page - 2);
                    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);

                    if (startPage > 1) {
                        const firstBtn = document.createElement('button');
                        firstBtn.textContent = '1';
                        firstBtn.style.cssText = 'padding: 0.5rem 1rem; background: #2A363F; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem;';
                        firstBtn.addEventListener('click', () => {
                            currentPage = 1;
                            loadPatients(currentSearchQuery, currentPage);
                        });
                        container.appendChild(firstBtn);

                        if (startPage > 2) {
                            const dots = document.createElement('span');
                            dots.textContent = '...';
                            dots.style.cssText = 'color: #888; padding: 0.5rem; font-size: 0.9rem;';
                            container.appendChild(dots);
                        }
                    }

                    for (let i = startPage; i <= endPage; i++) {
                        if (i === pagination.current_page) {
                            const currentBtn = document.createElement('span');
                            currentBtn.textContent = i;
                            currentBtn.style.cssText = 'padding: 0.5rem 1rem; background: #5cf9c8; color: black; border: none; border-radius: 4px; font-size: 0.9rem;';
                            container.appendChild(currentBtn);
                        } else {
                            const pageBtn = document.createElement('button');
                            pageBtn.textContent = i;
                            pageBtn.style.cssText = 'padding: 0.5rem 1rem; background: #2A363F; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem;';
                            pageBtn.addEventListener('click', () => {
                                currentPage = i;
                                loadPatients(currentSearchQuery, currentPage);
                            });
                            container.appendChild(pageBtn);
                        }
                    }

                    if (endPage < pagination.total_pages) {
                        if (endPage < pagination.total_pages - 1) {
                            const dots = document.createElement('span');
                            dots.textContent = '...';
                            dots.style.cssText = 'color: #888; padding: 0.5rem; font-size: 0.9rem;';
                            container.appendChild(dots);
                        }

                        const lastBtn = document.createElement('button');
                        lastBtn.textContent = pagination.total_pages;
                        lastBtn.style.cssText = 'padding: 0.5rem 1rem; background: #2A363F; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem;';
                        lastBtn.addEventListener('click', () => {
                            currentPage = pagination.total_pages;
                            loadPatients(currentSearchQuery, currentPage);
                        });
                        container.appendChild(lastBtn);
                    }

                    if (pagination.current_page < pagination.total_pages) {
                        const nextBtn = document.createElement('button');
                        nextBtn.textContent = 'Următor →';
                        nextBtn.style.cssText = 'padding: 0.5rem 1rem; background: #2A363F; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem;';
                        nextBtn.addEventListener('click', () => {
                            currentPage = pagination.current_page + 1;
                            loadPatients(currentSearchQuery, currentPage);
                        });
                        container.appendChild(nextBtn);
                    } else {
                        const nextBtn = document.createElement('span');
                        nextBtn.textContent = 'Următor →';
                        nextBtn.style.cssText = 'padding: 0.5rem 1rem; background: #1a2128; color: #666; border: none; border-radius: 4px; font-size: 0.9rem; cursor: not-allowed;';
                        container.appendChild(nextBtn);
                    }
                }

                function updateSearchResultsInfo(pagination) {
                    const infoDiv = document.getElementById('searchResultsInfo');
                    if (!infoDiv) return;

                    if (currentSearchQuery) {
                        infoDiv.textContent = `Rezultate pentru: "${currentSearchQuery}" (${pagination.total_patients} pacienți găsiți)`;
                        infoDiv.style.display = 'block';
                    } else {
                        infoDiv.style.display = 'none';
                    }
                }

                if (searchInput) {
                    let searchTimeout;
                    searchInput.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        currentSearchQuery = this.value.trim();
                        currentPage = 1;
                        
                        searchTimeout = setTimeout(() => {
                            loadPatients(currentSearchQuery, currentPage);
                        }, 300);
                    });
                }

                function confirmPatientSelection() {
                    console.log('Confirming selection for patient ID:', selectedPatientId);

                    if (selectedPatientId !== null) {
                    const selectedPatient = patients.find(p => p.id === selectedPatientId);
                        console.log('Found patient:', selectedPatient);
                        
                        if (selectedPatient && selectedNameDisplay) {
                            selectedNameDisplay.textContent = `${selectedPatient.nume} ${selectedPatient.prenume}`;
                            console.log('Updated display name to:', selectedNameDisplay.textContent);
                            
                            if (modal) {
                                modal.style.display = 'none';
                            }
                            
                            const preview = document.getElementById('selectedPatientPreview');
                            if (preview) {
                                preview.textContent = '';
                            }
                            
                            if (searchInput) {
                                searchInput.value = '';
                            }

                            calendarDays.forEach(day => {
                                if (!day.classList.contains('past-day')) {
                                    day.style.pointerEvents = 'auto';
                        }
                    });
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