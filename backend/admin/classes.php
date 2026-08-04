<?php
include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $sql = "SELECT * FROM classes";
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
        echo json_encode(["success" => 0, "message" => "Données manquantes"]);
        exit();
    }

    $niveau = isset($data->niveau) ? $data->niveau : '';

    $stmt = $conn->prepare("INSERT INTO classes (nom, niveau) VALUES (?, ?)");
    $stmt->bind_param("ss", $data->nom, $niveau);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "message" => "Classe ajoutée avec succès"]);
    } else {
        echo json_encode(["success" => 0, "message" => "Erreur lors de l'ajout de la classe"]);
    }

} else {
    echo json_encode(["success" => 0, "message" => "Méthode non autorisée"]);
}
?>