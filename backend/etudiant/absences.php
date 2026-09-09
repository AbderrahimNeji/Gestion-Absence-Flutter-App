<?php
include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["success" => 0, "message" => "ID de l'étudiant requis"]);
        exit();
    }

    $id = (int)$_GET['id'];

    $sql = "SELECT m.nom as matiere,
                   s.date_seance,
                   s.heure_debut,
                   s.heure_fin,
                   a.statut
            FROM absences a
            JOIN etudiants e ON a.etudiant_id = e.id
            JOIN seances s ON a.seance_id = s.id
            JOIN matieres m ON s.matiere_id = m.id
            WHERE e.utilisateur_id = $id
            ORDER BY s.date_seance DESC, s.heure_debut DESC";

    $result = $conn->query($sql);

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode([
        "success" => 1,
        "data" => $data
    ]);

} else {
    http_response_code(405);
    echo json_encode(["success" => 0, "message" => "Méthode non autorisée"]);
}
?>