<?php
/**
 * Script pentru obținerea sloturilor de timp disponibile pentru programări
 * 
 * Acest script returnează sloturile de timp disponibile pentru un medic
 * într-o dată specifică:
 * - Validează parametrii de intrare (medic_id, date)
 * - Generează sloturi de timp între 08:00 și 16:00 (la 30 min)
 * - Verifică programările existente pentru a marca sloturile ocupate
 * - Marchează sloturile din trecut ca fiind indisponibile
 * - Returnează lista cu statusul fiecărui slot
 * - Include informații de debug pentru troubleshooting
 * 
 * @package EasyMed
 * @author EasyMed Team
 * @version 1.0
 */

require_once 'db_connection.php';

// Verifică prezența parametrilor obligatorii
if (!isset($_GET['medic_id']) || !isset($_GET['date'])) {
    http_response_code(400);
    echo json_encode([]);
    exit();
}

// Extrage și validează parametrii
$medicId = isset($_GET['medic_id']) ? intval($_GET['medic_id']) : 0;
$date = $_GET['date'];

// Normalizează formatul datei (YYYY-MM-DD)
$dateParts = explode('-', $date);
if (count($dateParts) === 3) {
    if (strlen($dateParts[0]) === 4) {
        $date = $date;
    } else {
        $date = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
    }
}

// Verifică existența tabelei programări
$tableExists = $conn->query("SHOW TABLES LIKE 'programari'")->num_rows > 0;

// Obține toate programările pentru medicul specific (pentru debug)
$allAppointments = [];
$checkQuery = "SELECT * FROM programari WHERE medic_id = $medicId";
$checkResult = $conn->query($checkQuery);
if ($checkResult) {
    while ($row = $checkResult->fetch_assoc()) {
        $allAppointments[] = $row;
    }
}

// Obține programările exacte pentru data specificată (pentru debug)
$exactQuery = "SELECT * FROM programari WHERE medic_id = $medicId AND data_programare = '$date'";
$exactResult = $conn->query($exactQuery);
$exactMatch = [];
if ($exactResult) {
    while ($row = $exactResult->fetch_assoc()) {
        $exactMatch[] = $row;
    }
}

// Generează sloturile de timp disponibile (08:00 - 16:00, la 30 min)
$timeSlots = [];
$startTime = strtotime('08:00');
$endTime = strtotime('16:00');
$interval = 30 * 60; // 30 minute în secunde

for ($time = $startTime; $time <= $endTime; $time += $interval) {
    $timeSlots[] = [
        'time' => date('H:i', $time),
        'available' => true,
        'isPast' => false
    ];
}

// Obține programările existente pentru data și medicul specificat
$query = "SELECT * FROM programari WHERE medic_id = $medicId AND data_programare = '$date' AND status != 'anulat'";
$result = $conn->query($query);

$bookedTimes = [];
$rawTimes = [];
$debugRows = [];

// Procesează programările existente
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $debugRows[] = $row;
        $rawTimes[] = $row['ora_programare'];
        $bookedTimes[] = date('H:i', strtotime($row['ora_programare']));
    }
}

// Adaugă informații de debug la primul slot
if (!empty($timeSlots)) {
    $timeSlots[0]['_debug'] = [
        'raw_times_from_db' => $rawTimes,
        'query' => $query,
        'params' => [
            'medic_id' => $medicId,
            'date' => $date,
            'original_date' => $_GET['date']
        ],
        'table_exists' => $tableExists,
        'all_appointments' => $allAppointments,
        'exact_match' => $exactMatch,
        'num_rows' => $result ? $result->num_rows : 0,
        'error' => $conn->error,
        'errno' => $conn->errno,
        'debug_rows' => $debugRows,
        'booked_times' => $bookedTimes
    ];
}

// Marchează sloturile ocupate ca fiind indisponibile
foreach ($timeSlots as &$slot) {
    if (in_array($slot['time'], $bookedTimes)) {
        $slot['available'] = false;
    }
}

// Verifică dacă data este astăzi pentru a marca sloturile din trecut
$today = date('Y-m-d');
$now = new DateTime();
$isToday = ($date === $today);

// Setează timezone-ul pentru România
date_default_timezone_set('Europe/Bucharest');

// Marchează sloturile din trecut ca fiind indisponibile
if ($isToday) {
    foreach ($timeSlots as &$slot) {
        $slotTime = new DateTime($date . ' ' . $slot['time']);
        $slot['_debug_times'] = [
            'slotTime' => $slotTime->format('Y-m-d H:i:s'),
            'now' => $now->format('Y-m-d H:i:s')
        ];
        if ($slotTime <= $now) {
            $slot['available'] = false;
            $slot['isPast'] = true;
        }
    }
}

// Returnează rezultatul în format JSON
header('Content-Type: application/json');
echo json_encode($timeSlots);
exit();
?> 