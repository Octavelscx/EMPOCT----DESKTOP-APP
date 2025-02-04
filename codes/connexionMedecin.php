<?php
// Activer l'affichage des erreurs pour le debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrage de la session
session_start();

// Mise en tampon pour éviter les problèmes de "headers already sent"
ob_start();

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

// Initialisation du message d'erreur
$message = '';

// Vérification si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $id_connexion = trim($_POST['id_connexion']);
    $mdp = trim($_POST['mdp']);

    // Requête pour récupérer l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM user WHERE id_connexion = :id_connexion AND statut = 0");
    $stmt->bindParam(':id_connexion', $id_connexion, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification de l'utilisateur et du mot de passe
    if ($user && password_verify($mdp, $user['mdp'])) {
        // Stockage des informations utilisateur dans la session
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];

        // Redirection vers la page profil
        header('Location: profil.php');
        exit();
    } else {
        $message = "Les identifiants saisis sont incorrects. Vous ne pouvez pas accéder à l'espace dédié aux professionnels de santé.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Médecin</title>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            display: flex;
            align-items: center;
            gap: 40px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .image-container {
            position: relative;
        }
        .logo {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #3b82f6;
            color: white;
            padding: 10px;
            font-weight: bold;
            border-radius: 5px;
        }
        .image-container img {
            width: 300px;
            height: auto;
            border-radius: 10px;
        }
        .form-container {
            border: 1px solid #c3c3c3;
            padding: 20px;
            border-radius: 10px;
            background-color: #fdfdfd;
            text-align: center;
        }
        .form-container h2 {
            margin-bottom: 10px;
        }
        .form-container p {
            font-style: italic;
            color: #555;
        }
        .form-container label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .form-container button {
            background: #3b82f6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .form-container button:hover {
            background: #2563eb;
        }
        .form-container a {
            color: #3b82f6;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="image-container">
            <div class="logo">EMPOCT</div>
            <img src="bruno-rodrigues-BCUUAsPECK4-unsplash.jpg" alt="Médecin qui sourit">
        </div>
        <div class="form-container">
            <p><i>Bienvenue sur notre interface médecin.<br>Veuillez vous connecter pour accéder à votre espace.</i></p>
            <form action="connexionMedecin.php" method="POST">
                <label for="id_connexion">Identifiant</label>
                <input type="text" id="id_connexion" name="id_connexion" required>
                
                <label for="mdp">Mot de passe</label>
                <input type="password" id="mdp" name="mdp" required>
                
                <button type="submit">Se connecter</button>
            </form>
            <p>Pas de compte ? <a href="#">Envoyez un mail.</a></p>
            <?php if (!empty($message)) : ?>
                <p style="color: red;"> <?php echo htmlspecialchars($message); ?> </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
