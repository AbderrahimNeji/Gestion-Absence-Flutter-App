<?php
// === CORS Headers (needed for Flutter to communicate with the API) ===
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// === Database connection ===
// Valeurs par défaut pour XAMPP en local, surchargeables par variables d'environnement
$host = getenv("DB_HOST") ?: "localhost";
$db_name = getenv("DB_NAME") ?: "gest_absence";
$username = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASS") ?: "";

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    echo json_encode(["success" => 0, "message" => "Erreur de connexion à la base de données"]);
    exit();
}

$conn->set_charset("utf8mb4");
?>