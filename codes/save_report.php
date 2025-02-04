<?php
// Connexion à la base de données
$host = "localhost";
$dbname = "empoct_app_medecin";
$username = "root";
$password = "";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur de connexion : " . $e->getMessage()]);
    exit();
}

// Récupération des données envoyées
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id_patient'], $data['date'], $data['description'])) {
    $id_patient = $data['id_patient'];
    $date = $data['date'];
    $description = $data['description'];

    // Préparer et exécuter l’insertion
    $stmt = $pdo->prepare("INSERT INTO rapport (id_patient, date, description) VALUES (:id_patient, :date, :description)");
    $stmt->bindParam(':id_patient', $id_patient, PDO::PARAM_INT);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
}