<?php
include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents("php://input"));

    // Vérifier que les données sont présentes
    if (!$data || !isset($data->email) || !isset($data->password)) {
        http_response_code(400);
        echo json_encode(["success" => 0, "message" => "Email et mot de passe requis"]);
        exit();
    }

    $email = trim($data->email);
    // Pas de trim : le mot de passe est haché tel quel à la création du compte
    $password = $data->password;

    $stmt = $conn->prepare("SELECT id, nom, prenom, email, role, password FROM utilisateurs WHERE email = ? LIMIT 1");
    if (!$stmt) {
        echo json_encode(["success" => 0, "message" => "Erreur lors de la préparation de la requête"]);
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($userId, $nom, $prenom, $userEmail, $role, $storedPassword);
    $found = $stmt->fetch();
    $stmt->close();

    // Comptes créés avant le hachage : mot de passe en clair, re-haché à la connexion.
    // hash_equals() lève une ValueError en PHP 8 si les longueurs diffèrent.
    $legacy = $found
        && !password_get_info($storedPassword)['algo']
        && strlen($storedPassword) === strlen($password)
        && hash_equals($storedPassword, $password);

    if ($found && ($legacy || password_verify($password, $storedPassword))) {

        if ($legacy || password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $up = $conn->prepare("UPDATE utilisateurs SET password = ? WHERE id = ?");
            if ($up) {
                $up->bind_param("si", $hash, $userId);
                $up->execute();
                $up->close();
            }
        }

        // Préparer la réponse avec les infos de base
        $response = [
            "id" => (int) $userId,
            "nom" => $nom,
            "prenom" => $prenom,
            "email" => $userEmail,
            "role" => $role
        ];

        // Ajouter l'ID spécifique au rôle (enseignant_id ou etudiant_id)
        if ($role == 'enseignant') {
            $res = $conn->prepare("SELECT id FROM enseignants WHERE utilisateur_id = ? LIMIT 1");
            if ($res) {
                $res->bind_param("i", $userId);
                $res->execute();
                $res->bind_result($enseignantId);
                if ($res->fetch()) {
                    $response['enseignant_id'] = (int) $enseignantId;
                }
                $res->close();
            }
        } elseif ($role == 'etudiant') {
            $res = $conn->prepare("SELECT id, classe_id FROM etudiants WHERE utilisateur_id = ? LIMIT 1");
            if ($res) {
                $res->bind_param("i", $userId);
                $res->execute();
                $res->bind_result($etudiantId, $classeId);
                if ($res->fetch()) {
                    $response['etudiant_id'] = (int) $etudiantId;
                    $response['classe_id'] = (int) $classeId;
                }
                $res->close();
            }
        }

        echo json_encode([
            "success" => 1,
            "data" => $response
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "success" => 0,
            "message" => "Email ou mot de passe incorrect"
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        "success" => 0,
        "message" => "Méthode non autorisée"
    ]);
}
?>