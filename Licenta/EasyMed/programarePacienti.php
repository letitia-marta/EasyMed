<?php
    session_start();
    require_once 'db_connection.php';

    if (!isset($_SESSION['user_id']))
    {
        header("Location: pacientiLogin.php");
        exit();
    }

    $sql = "SELECT id FROM pacienti WHERE utilizator_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
    $pacient_id = $patient['id'];
    $stmt->close();

    error_log("Logged in patient ID: " . $pacient_id);

    $selectedDoctorId = isset($_GET['doctor']) ? (int)$_GET['doctor'] : null;
    error_log("Selected doctor ID: " . $selectedDoctorId);

    $selectedDoctor = null;
    if ($selectedDoctorId) {
        $stmt = $conn->prepare("SELECT nume, prenume, specializare FROM medici WHERE id = ?");
        $stmt->bind_param("i", $selectedDoctorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $selectedDoctor = $result->fetch_assoc();
        $stmt->close();
    }

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
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Programare Pacienti</title>
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
                color: black;
            }

            .time-slot:hover {
                background-color: #f0f0f0;
            }

            .time-slot.selected {
                background-color: #5cf9c8;
                color: black;
                border-color: #5cf9c8;
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
                color: black;
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
                color: rgb(0, 0, 0);
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
                background: rgb(24, 31, 36);
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
            <div class="wrapper">
                <div class="calendar-container" style="margin-bottom: 1rem;">
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <a href="dashboardPacienti.php" class="back-btn">← Înapoi la dashboard</a>
                    </div>
                    <div style="display: flex; justify-content: center;">
                        <button id="selectDoctorBtn" style="padding: 1rem 2rem; font-size: 1rem; background-color: #13181d; color: white; border: none; border-radius: 8px; cursor: pointer;">
                            <span id="selectedDoctorName">
                                <?php 
                                    if ($selectedDoctor) {
                                        echo "Dr. " . htmlspecialchars($selectedDoctor['nume'] . ' ' . $selectedDoctor['prenume'] . ' - ' . $selectedDoctor['specializare']);
                                    } else {
                                        echo "Selectează medicul";
                                    }
                                ?>
                            </span>
                        </button>
                    </div>
                </div>

                <div id="doctorModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                    background-color: rgba(0, 0, 0, 0.6); z-index: 9999; align-items: center; justify-content: center;">
                    <div style="background-color: #2A363F; padding: 2rem; border-radius: 10px; width: 90%; max-width: 500px;">
                        <h2 style="color: white;">Caută medic</h2>
                        
                        <div id="specialtyFilters" style="margin-bottom: 1rem; display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            <button class="specialty-filter active" data-specialty="all" 
                                style="padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; background: #5cf9c8; color: black;">
                                Toate
                            </button>
                            <?php
                                $specialties = [
                                    'Medic de familie', 'Cardiologie', 'Dermatologie', 'Pediatrie',
                                    'Ortopedie', 'Neurologie', 'Ginecologie', 'Psihiatrie',
                                    'Oftalmologie', 'Endocrinologie'
                                ];
                                foreach ($specialties as $specialty) {
                                    echo '<button class="specialty-filter" data-specialty="' . htmlspecialchars($specialty) . '" 
                                        style="padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; background: #13181d; color: white;">' 
                                        . htmlspecialchars($specialty) . '</button>';
                                }
                            ?>
                        </div>

                        <input type="text" id="searchDoctor" placeholder="Caută după nume..." style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; background: #13181d; color: white; border: none; border-radius: 4px;">
                        <ul id="doctorList" style="list-style: none; padding: 0; max-height: 300px; overflow-y: auto;"></ul>
                        <div style="margin-top: 1rem; color: white;" id="selectedDoctorPreview"></div>
                        <div style="margin-top: 1rem; display: flex; justify-content: space-between;">
                            <button id="closeModalBtn" style="padding: 0.5rem 1rem; background: #444; color: white; border: none; border-radius: 4px;">Închide</button>
                            <button id="confirmDoctorBtn" style="padding: 0.5rem 1rem; background: #5cf9c8; color: black; border: none; border-radius: 4px;">OK</button>
                        </div>
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
                </div>
            </div>
        </div>

        <script>
            const doctors = <?php echo json_encode($doctors); ?>;
            let selectedDate = null;
            let selectedTime = null;
            let selectedDoctorId = <?php echo $selectedDoctorId ? $selectedDoctorId : 'null'; ?>;
            let selectedConsultationType = null;
            const patientId = <?php echo $pacient_id; ?>;

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

                const selectBtn = document.getElementById('selectDoctorBtn');
                const modal = document.getElementById('doctorModal');
                const doctorList = document.getElementById('doctorList');
                const searchInput = document.getElementById('searchDoctor');
                const selectedNameDisplay = document.getElementById('selectedDoctorName');
                const specialtyFilters = document.querySelectorAll('.specialty-filter');
                let currentSpecialty = 'all';

                if (selectBtn) {
                    selectBtn.addEventListener('click', () => {
                        if (modal) {
                        modal.style.display = 'flex';
                        renderDoctors('');
                        }
                    });
                }

                const closeModalBtn = document.getElementById('closeModalBtn');
                const confirmDoctorBtn = document.getElementById('confirmDoctorBtn');

                if (closeModalBtn) {
                    closeModalBtn.addEventListener('click', () => {
                        if (modal) {
                        modal.style.display = 'none';
                        }
                    });
                }

                if (confirmDoctorBtn) {
                    confirmDoctorBtn.addEventListener('click', () => {
                        if (selectedDoctorId !== null) {
                            const selectedDoctor = doctors.find(d => d.id === selectedDoctorId);
                            if (selectedDoctor && selectedNameDisplay) {
                                selectedNameDisplay.textContent = `Dr. ${selectedDoctor.nume} ${selectedDoctor.prenume} - ${selectedDoctor.specializare}`;
                            modal.style.display = 'none';
                            }
                        }
                    });
                }

                specialtyFilters.forEach(filter => {
                    filter.addEventListener('click', () => {
                        specialtyFilters.forEach(f => {
                            f.style.background = '#13181d';
                            f.style.color = 'white';
                        });
                        filter.style.background = '#5cf9c8';
                        filter.style.color = 'black';
                        currentSpecialty = filter.dataset.specialty;
                        renderDoctors(searchInput.value);
                    });
                });

                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                    renderDoctors(this.value);
                });
                }

                function renderDoctors(query) {
                    const filtered = doctors.filter(d => {
                        const matchesSearch = (d.nume + ' ' + d.prenume + ' ' + d.specializare)
                            .toLowerCase()
                            .includes(query.toLowerCase());
                        const matchesSpecialty = currentSpecialty === 'all' || d.specializare === currentSpecialty;
                        return matchesSearch && matchesSpecialty;
                    });

                    if (doctorList) {
                    doctorList.innerHTML = '';
                        filtered.forEach(d => {
                        const li = document.createElement('li');
                            li.textContent = `Dr. ${d.nume} ${d.prenume} - ${d.specializare}`;
                        li.style.padding = '0.5rem';
                        li.style.color = 'white';
                        li.style.cursor = 'pointer';
                            li.style.borderBottom = '1px solid #444';
                            li.addEventListener('click', () => {
                                selectedDoctorId = d.id;
                                console.log('Doctor selected:', selectedDoctorId);
                                const preview = document.getElementById('selectedDoctorPreview');
                                if (preview) {
                                    preview.textContent = `Selectat: Dr. ${d.nume} ${d.prenume} - ${d.specializare}`;
                                }
                                document.querySelectorAll('#doctorList li').forEach(item => {
                                item.style.background = 'transparent';
                            });
                            li.style.background = '#3A4A5F';
                            });
                            doctorList.appendChild(li);
                        });
                    }
                }

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
                    console.log('Current state:', { patientId, selectedDate, selectedTime, selectedDoctorId, selectedConsultationType });
                    
                    if (patientId && selectedDate && selectedTime && selectedDoctorId && selectedConsultationType) {
                        const formData = new FormData();
                        formData.append('patient_id', patientId);
                        formData.append('date', selectedDate);
                        formData.append('time', selectedTime);
                        formData.append('doctor_id', selectedDoctorId);
                        formData.append('consultation_type', selectedConsultationType);

                        console.log('Sending data to server:', {
                            patient_id: patientId,
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
                                selectedDoctorId = null;
                                selectedConsultationType = null;
                                timeSlots.classList.remove('active');
                                timeSlots.style.display = 'none';
                                saveButton.style.display = 'none';
                                
                                document.querySelectorAll('.calendar-day').forEach(day => {
                                    day.classList.remove('selected');
                                });
                                
                                document.querySelectorAll('.time-slot').forEach(slot => {
                                    slot.classList.remove('selected');
                                });
                                
                                if (selectedNameDisplay) {
                                    selectedNameDisplay.textContent = 'Selectează medicul';
                                }
                                
                                if (consultationType) {
                                    consultationType.value = '';
                                }
                            } else {
                                alert(data.message || 'Eroare la salvarea programării.');
                    }
                        })
                        .catch(error => {
                            console.error('Error saving appointment:', error);
                            alert('Eroare la salvarea programării. Vă rugăm să încercați din nou.');
                        });
                    } else {
                        console.log('Missing data:', { patientId, selectedDate, selectedTime, selectedDoctorId, selectedConsultationType });
                        alert('Vă rugăm să selectați un medic, o dată, o oră și tipul consultației.');
                    }
                });

                calendarDays.forEach(day => {
                    if (day.classList.contains('past-day')) return;

                    day.addEventListener('click', async function() {
                        if (!selectedDoctorId) {
                            alert('Vă rugăm să selectați mai întâi un medic.');
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
            });
        </script>

        <footer>
            <div class="wrapper">
                <p>EasyMed © 2024</p>
            </div>
        </footer>
    </body>
</html>