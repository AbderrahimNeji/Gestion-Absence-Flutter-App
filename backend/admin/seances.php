<?php
include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $sql = "SELECT s.id,
                   m.nom as matiere,
                   c.nom as classe,
                   CONCAT(u.nom, ' ', u.prenom) as enseignant,
                   s.date_seance,
                   s.heure_debut,
                   s.heure_fin
            FROM seances s
            JOIN matieres m ON s.matiere_id = m.id
            JOIN classes c ON s.classe_id = c.id
            JOIN enseignants e ON s.enseignant_id = e.id
            JOIN utilisateurs u ON e.utilisateur_id = u.id";

    $result = $conn->query($sql);

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode([
        "success" => 1,
        "data" => $data
    ]);

} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents("php://input"));

    if (!$data || !isset($data->enseignant_id) || !isset($data->classe_id) || !isset($data->matiere_id) || !isset($data->date_seance) || !isset($data->heure_debut) || !isset($data->heure_fin)) {
        echo json_encode(["success" => 0, "message" => "Données manquantes"]);
        exit();
    }

    $enseignant_id = (int)$data->enseignant_id;
    $classe_id = (int)$data->classe_id;
    $matiere_id = (int)$data->matiere_id;

    $stmt = $conn->prepare("INSERT INTO seances
            (enseignant_id, classe_id, matiere_id, date_seance, heure_debut, heure_fin)
            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisss", $enseignant_id, $classe_id, $matiere_id, $data->date_seance, $data->heure_debut, $data->heure_fin);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "message" => "Séance ajoutée avec succès"]);
    } else {
        echo json_encode(["success" => 0, "message" => "Erreur lors de l'ajout de la séance"]);
    }

} else {
    http_response_code(405);
    echo json_encode(["success" => 0, "message" => "Méthode non autorisée"]);
}
?>