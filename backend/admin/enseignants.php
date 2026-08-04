<?php
include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $sql = "SELECT e.id, u.nom, u.prenom, u.email, e.specialite, e.utilisateur_id
            FROM enseignants e
            JOIN utilisateurs u ON e.utilisateur_id = u.id";

    $result = $conn->query($sql);

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(["success" => 1, "data" => $data]);

} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents("php://input"));

    if (!$data || !isset($data->nom) || !isset($data->prenom) || !isset($data->email) || !isset($data->password)) {
        echo json_encode(["success" => 0, "message" => "Données manquantes"]);
        exit();
    }

    $password = password_hash($data->password, PASSWORD_DEFAULT);
    $specialite = isset($data->specialite) ? $data->specialite : '';

    $stmt = $conn->prepare("INSERT INTO utilisateurs (nom,prenom,email,password,role) VALUES (?,?,?,?,'enseignant')");
    $stmt->bind_param("ssss", $data->nom, $data->prenom, $data->email, $password);

    if ($stmt->execute()) {

        $user_id = $conn->insert_id;

        $stmt2 = $conn->prepare("INSERT INTO enseignants (utilisateur_id,specialite) VALUES (?,?)");
        $stmt2->bind_param("is", $user_id, $specialite);
        $stmt2->execute();

        echo json_encode(["success" => 1, "message" => "Enseignant ajouté avec succès"]);
    } else {
        echo json_encode(["success" => 0, "message" => "Erreur lors de l'ajout (email peut-être déjà utilisé)"]);
    }

} elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {

    $data = json_decode(file_get_contents("php://input"));

    if (!$data || !isset($data->id)) {
        echo json_encode(["success" => 0, "message" => "ID manquant"]);
        exit();
    }

    $id = (int)$data->id;
    $specialite = isset($data->specialite) ? $data->specialite : '';

    // récupérer utilisateur_id
    $res = $conn->query("SELECT utilisateur_id FROM enseignants WHERE id=$id");
    if ($res->num_rows == 0) {
        echo json_encode(["success" => 0, "message" => "Enseignant non trouvé"]);
        exit();
    }
    $row = $res->fetch_assoc();
    $user_id = $row['utilisateur_id'];

    // update utilisateurs
    $stmt = $conn->prepare("UPDATE utilisateurs SET nom=?, prenom=?, email=? WHERE id=?");
    $stmt->bind_param("sssi", $data->nom, $data->prenom, $data->email, $user_id);
    $stmt->execute();

    // update spécialité
    $stmt = $conn->prepare("UPDATE enseignants SET specialite=? WHERE id=?");
    $stmt->bind_param("si", $specialite, $id);
    $stmt->execute();

    echo json_encode(["success" => 1, "message" => "Enseignant modifié avec succès"]);

} elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id == 0) {
        echo json_encode(["success" => 0, "message" => "ID manquant"]);
        exit();
    }

    // récupérer utilisateur_id
    $res = $conn->query("SELECT utilisateur_id FROM enseignants WHERE id=$id");
    if ($res->num_rows == 0) {
        echo json_encode(["success" => 0, "message" => "Enseignant non trouvé"]);
        exit();
    }
    $row = $res->fetch_assoc();
    $user_id = $row['utilisateur_id'];

    // supprimer utilisateur (CASCADE supprimera l'enseignant)
    $conn->query("DELETE FROM utilisateurs WHERE id=$user_id");

    echo json_encode(["success" => 1, "message" => "Enseignant supprimé avec succès"]);

} else {
    echo json_encode(["success" => 0, "message" => "Méthode non autorisée"]);
}
?>