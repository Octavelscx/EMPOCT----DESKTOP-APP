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

// Vérifier la présence du paramètre id_patient
if (isset($_GET['id_patient'])) {
    $id_patient = $_GET['id_patient'];

    // Récupérer les rapports du patient
    $stmt = $pdo->prepare("SELECT id_rapport, date, description FROM rapport WHERE id_patient = :id_patient");
    $stmt->bindParam(':id_patient', $id_patient, PDO::PARAM_INT);
    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retourner les résultats sous forme de JSON
    echo json_encode($reports);
} else {
    echo json_encode(["success" => false, "message" => "Paramètre id_patient manquant"]);
}
?>
