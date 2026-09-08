<?php
include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents("php://input"));

    if (!$data || !isset($data->seance_id) || !isset($data->absences)) {
        echo json_encode(["success" => 0, "message" => "Données manquantes (seance_id et absences requis)"]);
        exit();
    }

    $seance_id = (int)$data->seance_id;
    $absences = $data->absences;

    $errors = 0;

    $stmt = $conn->prepare("INSERT INTO absences (seance_id, etudiant_id, statut)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE statut = VALUES(statut)");

    foreach ($absences as $a) {

        $etudiant_id = (int)$a->etudiant_id;
        $statut = isset($a->statut) ? $a->statut : '';

        // Seules les valeurs de l'ENUM sont acceptées
        if (!in_array($statut, ['present', 'absent'], true)) {
            $errors++;
            continue;
        }

        $stmt->bind_param("iis", $seance_id, $etudiant_id, $statut);
        if (!$stmt->execute()) {
            $errors++;
        }
    }

    if ($errors == 0) {
        echo json_encode(["success" => 1, "message" => "Appel enregistré avec succès"]);
    } else {
        echo json_encode(["success" => 0, "message" => "Certaines absences n'ont pas pu être enregistrées"]);
    }

} else {
    http_response_code(405);
    echo json_encode(["success" => 0, "message" => "Méthode non autorisée"]);
}
?>