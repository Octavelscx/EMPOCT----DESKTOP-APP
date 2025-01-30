<?php
// Connexion à la base de données
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

// Vérifier si un ID de rendez-vous a été sélectionné
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['id_rdv'])) {
        
        // Récupérer l'ID du RDV à supprimer
        $id_rdv = $_POST['id_rdv'];

        // Supprimer le RDV de la base de données
        $query = $pdo->prepare("DELETE FROM rendezvous WHERE id_rdv = ?");
        $query->execute([$id_rdv]);

        // Redirection avec un message de succès
        header("Location: profil.php?success=rdv_deleted");
        exit();
    } else {
        // Redirection avec un message d'erreur
        header("Location: profil.php?error=no_rdv_selected");
        exit();
    }
}
?>
