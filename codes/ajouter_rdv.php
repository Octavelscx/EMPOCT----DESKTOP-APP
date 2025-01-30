<?php
// Connexion Ã  la base de donnÃ©es
$host = 'localhost';
$dbname = 'empoct_app_medecin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// VÃ©rifier si les champs sont bien remplis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['id_patient']) && !empty($_POST['date_rdv']) && !empty($_POST['heure'])) {
        
        // RÃ©cupÃ©rer les valeurs du formulaire
        $id_patient = $_POST['id_patient'];
        $date_rdv = $_POST['date_rdv'];
        $heure = $_POST['heure'];
        $description = !empty($_POST['description']) ? $_POST['description'] : null;

        // InsÃ©rer dans la base de donnÃ©es
        $query = $pdo->prepare("INSERT INTO rendezvous (id_patient, date_rdv, heure, description) VALUES (?, ?, ?, ?)");
        $query->execute([$id_patient, $date_rdv, $heure, $description]);

        // Redirection avec un message de succÃ¨s
        header("Location: profil.php?success=rdv_added");
        exit();
    } else {
        // Redirection avec un message d'erreur
        header("Location: profil.php?error=missing_fields");
        exit();
    }
}
?>
