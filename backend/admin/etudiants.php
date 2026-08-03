<?php
include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    // Support filtre par classe (pour l'appel de l'enseignant)
    $where = "";
    if (isset($_GET['classe_id'])) {
        $classe_id = (int)$_GET['classe_id'];
        $where = " WHERE e.classe_id = $classe_id";
    }

    $sql = "SELECT e.id, u.nom, u.prenom, u.email, c.nom as classe, e.classe_id, e.utilisateur_id
            FROM etudiants e
            JOIN utilisateurs u ON e.utilisateur_id = u.id
            JOIN classes c ON e.classe_id = c.id" . $where;

    $result = $conn->query($sql);

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(["success" => 1, "data" => $data]);

} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents("php://input"));

    if (!$data || !isset($data->nom) || !isset($data->prenom) || !isset($data->email) || !isset($data->password) || !isset($data->classe_id)) {
        echo json_encode(["success" => 0, "message" => "Données manquantes"]);
        exit();
    }

    $nom = $conn->real_escape_string($data->nom);
    $prenom = $conn->real_escape_string($data->prenom);
    $email = $conn->real_escape_string($data->email);
    $password = password_hash($data->password, PASSWORD_DEFAULT);
    $classe_id = (int)$data->classe_id;

    // ajouter utilisateur
    $sql1 = "INSERT INTO utilisateurs (nom,prenom,email,password,role)
             VALUES ('$nom','$prenom','$email','$password','etudiant')";

    if ($conn->query($sql1)) {

        $user_id = $conn->insert_id;

        // ajouter etudiant
        $sql2 = "INSERT INTO etudiants (utilisateur_id,classe_id)
                 VALUES ($user_id,$classe_id)";

        $conn->query($sql2);

        echo json_encode(["success" => 1, "message" => "Étudiant ajouté avec succès"]);
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
    $classe_id = (int)$data->classe_id;

    // récupérer utilisateur_id
    $res = $conn->query("SELECT utilisateur_id FROM etudiants WHERE id=$id");
    if ($res->num_rows == 0) {
        echo json_encode(["success" => 0, "message" => "Étudiant non trouvé"]);
        exit();
    }
    $row = $res->fetch_assoc();
    $user_id = $row['utilisateur_id'];

    // update utilisateurs
    $conn->query("UPDATE utilisateurs 
                  SET nom='$nom', prenom='$prenom', email='$email'
                  WHERE id=$user_id");

    // update classe
    $conn->query("UPDATE etudiants 
                  SET classe_id=$classe_id
                  WHERE id=$id");

    echo json_encode(["success" => 1, "message" => "Étudiant modifié avec succès"]);

} elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id == 0) {
        echo json_encode(["success" => 0, "message" => "ID manquant"]);
        exit();
    }

    // récupérer utilisateur_id
    $res = $conn->query("SELECT utilisateur_id FROM etudiants WHERE id=$id");
    if ($res->num_rows == 0) {
        echo json_encode(["success" => 0, "message" => "Étudiant non trouvé"]);
        exit();
    }
    $row = $res->fetch_assoc();
    $user_id = $row['utilisateur_id'];

    // supprimer utilisateur (CASCADE supprimera l'étudiant)
    $conn->query("DELETE FROM utilisateurs WHERE id=$user_id");

    echo json_encode(["success" => 1, "message" => "Étudiant supprimé avec succès"]);

} else {
    echo json_encode(["success" => 0, "message" => "Méthode non autorisée"]);
}
?>