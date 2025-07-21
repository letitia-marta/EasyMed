<?php
/**
 * API Endpoint pentru detalii consultație medicală
 *
 * Acest endpoint returnează detalii complete despre o consultație:
 * - Primește consultation_id prin GET
 * - Returnează date despre consultație, medic, pacient, diagnostic, bilete de trimitere, investigații și rețete
 * - Folosește interogări multiple cu prepared statements pentru siguranță
 * - Returnează răspuns JSON structurat cu toate datele relevante
 * - Gestionează erorile de validare și de bază de date
 *
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

// Setează headerele pentru răspuns JSON și CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestionare pentru cereri OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include conexiunea la baza de date
include('../db_connection.php');

try {
    // Extrage consultation_id din GET
    $consultationId = isset($_GET['consultation_id']) ? (int)$_GET['consultation_id'] : 0;
    
    // Validează consultation_id
    if ($consultationId <= 0) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Missing or invalid consultation_id parameter'
        ]);
        exit;
    }

    // Interoghează detaliile consultației, medicului, pacientului și diagnosticului
    $sqlConsultatie = "SELECT c.*, m.nume as nume_doctor, m.prenume as prenume_doctor, m.specializare, m.cod_parafa,
                      p.nume as nume_pacient, p.prenume as prenume_pacient, p.CNP as CNP_pacient,
                      cb.cod_999 as cod_diagnostic, cb.denumire_boala as nume_diagnostic
                      FROM consultatii c
                      INNER JOIN medici m ON c.id_medic = m.id
                      INNER JOIN pacienti p ON c.CNPPacient = p.CNP
                      LEFT JOIN coduri_boli cb ON c.Diagnostic = cb.cod_999
                      WHERE c.ID = ?";

    $stmtConsultatie = $conn->prepare($sqlConsultatie);
    $stmtConsultatie->bind_param("i", $consultationId);
    $stmtConsultatie->execute();
    $resultConsultatie = $stmtConsultatie->get_result();

    if ($resultConsultatie->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'error' => 'Consultation not found'
        ]);
        exit;
    }

    $consultatie = $resultConsultatie->fetch_assoc();

    // Obține biletele de trimitere asociate consultației
    $sqlBileteTrimitere = "SELECT * FROM bilete_trimitere WHERE Consultatie = ?";
    $stmtBileteTrimitere = $conn->prepare($sqlBileteTrimitere);
    $stmtBileteTrimitere->bind_param("i", $consultationId);
    $stmtBileteTrimitere->execute();
    $resultBileteTrimitere = $stmtBileteTrimitere->get_result();
    $bileteTrimitere = $resultBileteTrimitere->fetch_all(MYSQLI_ASSOC);

    // Obține biletele de investigații asociate consultației
    $sqlBileteInvestigatii = "SELECT bi.CodBilet, GROUP_CONCAT(CONCAT(ca.cod, ' - ', ca.denumire) ORDER BY ca.denumire SEPARATOR '||') as nume_investigatii
                             FROM bilete_investigatii bi
                             LEFT JOIN investigatii i ON bi.CodBilet = i.CodBilet
                             LEFT JOIN coduri_analize ca ON i.investigatie = ca.id
                             WHERE bi.Consultatie = ?
                             GROUP BY bi.CodBilet";
    $stmtBileteInvestigatii = $conn->prepare($sqlBileteInvestigatii);
    $stmtBileteInvestigatii->bind_param("i", $consultationId);
    $stmtBileteInvestigatii->execute();
    $resultBileteInvestigatii = $stmtBileteInvestigatii->get_result();
    $bileteInvestigatii = $resultBileteInvestigatii->fetch_all(MYSQLI_ASSOC);

    // Obține rețetele asociate consultației
    $sqlRetete = "SELECT rm.*, m.Medicamente, m.FormaFarmaceutica, m.Cantitate, m.Durata
                  FROM retete_medicale rm
                  LEFT JOIN medicamente m ON rm.Cod = m.CodReteta
                  WHERE rm.Consultatie = ?";
    $stmtRetete = $conn->prepare($sqlRetete);
    $stmtRetete->bind_param("i", $consultationId);
    $stmtRetete->execute();
    $resultRetete = $stmtRetete->get_result();
    $retete = $resultRetete->fetch_all(MYSQLI_ASSOC);

    // Formează răspunsul cu toate datele relevante
    $response = [
        'consultation' => [
            'id' => (int)$consultatie['ID'],
            'date' => $consultatie['Data'],
            'time' => $consultatie['Ora'],
            'symptoms' => $consultatie['Simptome'],
            'diagnosis_code' => $consultatie['cod_diagnostic'],
            'diagnosis_name' => $consultatie['nume_diagnostic'],
            'patient' => [
                'name' => $consultatie['nume_pacient'] . ' ' . $consultatie['prenume_pacient'],
                'cnp' => $consultatie['CNP_pacient']
            ],
            'doctor' => [
                'name' => $consultatie['nume_doctor'] . ' ' . $consultatie['prenume_doctor'],
                'specialty' => $consultatie['specializare'],
                'stamp_code' => $consultatie['cod_parafa']
            ]
        ],
        'referral_tickets' => $bileteTrimitere,
        'investigation_tickets' => $bileteInvestigatii,
        'prescriptions' => $retete
    ];
    
    // Returnează răspunsul în format JSON
    echo json_encode($response);

} catch (Exception $e) {
    // Gestionare excepții
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    // Închide toate statement-urile și conexiunea
    if (isset($stmtConsultatie)) {
        $stmtConsultatie->close();
    }
    if (isset($stmtBileteTrimitere)) {
        $stmtBileteTrimitere->close();
    }
    if (isset($stmtBileteInvestigatii)) {
        $stmtBileteInvestigatii->close();
    }
    if (isset($stmtRetete)) {
        $stmtRetete->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?> 