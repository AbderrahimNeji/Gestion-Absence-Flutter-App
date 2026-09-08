<?php
include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    if (!isset($_GET['id'])) {
        echo json_encode(["success" => 0, "message" => "ID de l'étudiant requis"]);
        exit();
    }

    $id = (int)$_GET['id'];

    $sql = "SELECT u.nom, u.prenom, u.email, c.nom as classe
            FROM utilisateurs u
            JOIN etudiants e ON u.id = e.utilisateur_id
            JOIN classes c ON e.classe_id = c.id
            WHERE u.id = $id";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        echo json_encode([
            "success" => 1,
            "data" => $data
        ]);
    } else {
        echo json_encode(["success" => 0, "message" => "Étudiant non trouvé"]);
    }

} else {
    http_response_code(405);
    echo json_encode(["success" => 0, "message" => "Méthode non autorisée"]);
}
?>