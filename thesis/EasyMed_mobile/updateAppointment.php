<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get POST data
        $appointmentId = $_POST['appointmentId'] ?? null;
        $pacientId = $_POST['pacient_id'] ?? null;
        $medicId = $_POST['medic_id'] ?? null;
        $dataProgramare = $_POST['data_programare'] ?? null;
        $oraProgramare = $_POST['ora_programare'] ?? null;
        $status = $_POST['status'] ?? 'programat';
        $motivConsultatie = $_POST['motiv_consultatie'] ?? null;

        // Validate required fields
        if (!$appointmentId || !$pacientId || !$medicId || !$dataProgramare || !$oraProgramare || !$motivConsultatie) {
            echo json_encode([
                'success' => false,
                'error' => 'Toate câmpurile sunt obligatorii'
            ]);
            exit;
        }

        // Update appointment in database
        $sql = "UPDATE programari SET 
                pacient_id = ?, 
                medic_id = ?, 
                data_programare = ?, 
                ora_programare = ?, 
                status = ?, 
                motiv_consultatie = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissssi", 
            $pacientId, 
            $medicId, 
            $dataProgramare, 
            $oraProgramare, 
            $status, 
            $motivConsultatie, 
            $appointmentId
        );

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Programarea a fost actualizată cu succes'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Nu s-a găsit programarea sau nu au fost făcute modificări'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Eroare la actualizarea programării: ' . $stmt->error
            ]);
        }

        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Eroare server: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Metoda HTTP nu este permisă'
    ]);
}
?> 
