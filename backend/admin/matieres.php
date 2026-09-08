<?php
include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $sql = "SELECT * FROM matieres";
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

    if (!$data || !isset($data->nom)) {
        echo json_encode(["success" => 0, "message" => "Nom de la matière requis"]);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO matieres (nom) VALUES (?)");
    $stmt->bind_param("s", $data->nom);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "message" => "Matière ajoutée avec succès"]);
    } else {
        echo json_encode(["success" => 0, "message" => "Erreur lors de l'ajout de la matière"]);
    }

} else {
    http_response_code(405);
    echo json_encode(["success" => 0, "message" => "Méthode non autorisée"]);
}
?>
