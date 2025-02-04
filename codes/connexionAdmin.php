<?php
session_start();

// Empêche la mise en cache des pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_connexion = $_POST['id_connexion'];
    $mdp = $_POST['mdp'];
    
    $stmt = $pdo->prepare("SELECT * FROM User WHERE id_connexion = :id_connexion AND statut = 1");
    $stmt->bindParam(':id_connexion', $id_connexion, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($mdp, $user['mdp'])) {
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];
        header('Location: profilAdmin.php');
        exit();
    } else {
        $message = "Les identifiants saisis sont incorrects. Vous n'avez pas les privilèges administrateur.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administrateur</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="image-container">
            <div class="logo">EMPOCT</div>
            <img src="jefferson-santos-9SoCnyQmkzI-unsplash.jpg" alt="personne sur son ordinateur">
        </div>
        <div class="form-container">
            <p><i>Bienvenue sur l'interface administrateur.<br>Veuillez vous connecter pour gérer votre espace.</i></p>
            <form action="connexionAdmin.php" method="POST">
                <label for="id_connexion">Identifiant</label>
                <input type="text" id="id_connexion" name="id_connexion" required>
                
                <label for="mdp">Mot de passe</label>
                <input type="password" id="mdp" name="mdp" required>
                
                <button type="submit">Se connecter</button>
            </form>
            <?php if (!empty($message)) : ?>
                <p style="color: red;"> <?php echo htmlspecialchars($message); ?> </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
