<?php include ("code/EnTete.php") ?>

<?php
session_start();

// Connexion à la base de données
$host = 'localhost';
$dbname = 'empoct_app_medecin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Vérification des identifiants
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_connexion = $_POST['id_connexion'];
    $mdp = $_POST['mdp'];
    $message = '';

    // Requête pour vérifier l'utilisateur avec le statut de professionnel de santé
    $stmt = $pdo->prepare("SELECT * FROM User WHERE id_connexion = :id_connexion AND statut = 0");
    $stmt->bindParam(':id_connexion', $id_connexion, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($mdp, $user['mdp'])) {
        // Connexion réussie
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];
        header('Location: profil.php');
        exit();
    } else {
        // Identifiants incorrects
        $message = "Les identifiants saisis sont incorrects. Vous ne pouvez accéder à l'espace dédié aux professionnels de santé.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Médecin</title>
</head>
<body>
    <h2>Connexion Médecin</h2>
    <form action="connexionMedecin.php" method="POST">
        <label for="id_connexion">Identifiant :</label>
        <input type="text" id="id_connexion" name="id_connexion" required>
        <br><br>
        <label for="mdp">Mot de passe :</label>
        <input type="password" id="mdp" name="mdp" required>
        <br><br>
        <button type="submit">Se connecter</button>
    </form>
    <?php if (!empty($message)) : ?>
        <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
</body>
</html>
<?php include("code/PiedDePage.php") ?>


