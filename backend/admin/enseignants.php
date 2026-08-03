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

    $nom = $conn->real_escape_string($data->nom);
    $prenom = $conn->real_escape_string($data->prenom);
    $email = $conn->real_escape_string($data->email);
    $password = password_hash($data->password, PASSWORD_DEFAULT);
    $specialite = isset($data->specialite) ? $conn->real_escape_string($data->specialite) : '';

    $sql1 = "INSERT INTO utilisateurs (nom,prenom,email,password,role)
             VALUES ('$nom','$prenom','$email','$password','enseignant')";

    if ($conn->query($sql1)) {

        $user_id = $conn->insert_id;

        $sql2 = "INSERT INTO enseignants (utilisateur_id,specialite)
                 VALUES ($user_id,'$specialite')";

        $conn->query($sql2);

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
    $nom = $conn->real_escape_string($data->nom);
    $prenom = $conn->real_escape_string($data->prenom);
    $email = $conn->real_escape_string($data->email);
    $specialite = isset($data->specialite) ? $conn->real_escape_string($data->specialite) : '';

    // récupérer utilisateur_id
    $res = $conn->query("SELECT utilisateur_id FROM enseignants WHERE id=$id");
    if ($res->num_rows == 0) {
        echo json_encode(["success" => 0, "message" => "Enseignant non trouvé"]);
        exit();
    }
    $row = $res->fetch_assoc();
    $user_id = $row['utilisateur_id'];

    // update utilisateurs
    $conn->query("UPDATE utilisateurs 
                  SET nom='$nom', prenom='$prenom', email='$email'
                  WHERE id=$user_id");

    // update spécialité
    $conn->query("UPDATE enseignants 
                  SET specialite='$specialite'
                  WHERE id=$id");

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