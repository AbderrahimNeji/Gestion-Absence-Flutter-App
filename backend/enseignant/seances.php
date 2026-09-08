<?php
include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["success" => 0, "message" => "ID de l'enseignant requis"]);
        exit();
    }

    $user_id = (int)$_GET['id'];

    // On accepte l'utilisateur_id (retourné par login) et on fait un JOIN
    // pour trouver les séances de cet enseignant
    $sql = "SELECT s.id,
                   m.nom as matiere,
                   c.nom as classe,
                   s.classe_id,
                   s.date_seance,
                   s.heure_debut,
                   s.heure_fin
            FROM seances s
            JOIN matieres m ON s.matiere_id = m.id
            JOIN classes c ON s.classe_id = c.id
            JOIN enseignants e ON s.enseignant_id = e.id
            WHERE e.utilisateur_id = $user_id";

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