<?php
// Connexion Ã  la base de donnÃ©es
$host = 'localhost';
$dbname = 'empoct_app_medecin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // RÃ©cupÃ©rer les patients
    $stmt = $pdo->query("SELECT id_patient, nom_patient, prenom_patient, date_debut FROM patients");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($patients); // Convertir en JSON
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
