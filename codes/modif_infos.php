<?php
session_start();

// Empêche la mise en cache des pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit();
}

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

// Vérification de l'utilisateur connecté
$nom_utilisateur = "";
$prenom_utilisateur = "";
if (isset($_SESSION['id_user'])) {
    $stmt = $pdo->prepare("SELECT nom, prenom, statut FROM User WHERE id_user = :id_user");
    $stmt->bindParam(':id_user', $_SESSION['id_user'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $nom_utilisateur = htmlspecialchars($user['nom']);
        $prenom_utilisateur = htmlspecialchars($user['prenom']);
    }
}

// Déterminer la page de redirection en fonction du statut de l'utilisateur
$profilPage = ($user['statut'] == 1) ? "profilAdmin.php" : "profil.php";

    if ($user) {
        $statut = $user['statut'];
        $profilPage = ($statut == 1) ? "profilAdmin.php" : "profil.php";
    } else {
        $profilPage = "index.php"; // Redirection par défaut
    }


// Gestion de la modification des informations de l'utilisateur connecté
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modify_user'])) {
    $champ_a_modifier = $_POST['champ'];
    $nouvelle_valeur = htmlspecialchars($_POST['nouvelle_valeur']);

    $champs_valides = ['nom', 'prenom', 'mdp'];
    if (in_array($champ_a_modifier, $champs_valides)) {
        try {
            if ($champ_a_modifier === 'mdp') {
                $nouvelle_valeur = password_hash($nouvelle_valeur, PASSWORD_DEFAULT);
            }
            $stmt = $pdo->prepare("UPDATE user SET $champ_a_modifier = :nouvelle_valeur WHERE id_user = :id_user");
            $stmt->bindParam(':nouvelle_valeur', $nouvelle_valeur, PDO::PARAM_STR);
            $stmt->bindParam(':id_user', $_SESSION['id_user'], PDO::PARAM_INT);
            $stmt->execute();

            $successMessage = "Vos informations ont été mises à jour avec succès.";
        } catch (PDOException $e) {
            $errorMessage = "Erreur lors de la modification : " . $e->getMessage();
        }
    } else {
        $errorMessage = "Champ de modification invalide.";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau 'mon espace' Medecin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #F5F5F5;
        }
        .navbar {
            background-color: #0073E6;
        }
        .navbar a {
            color: white;
            font-weight: bold;
        }
        .navbar a:hover {
            text-decoration: underline;
        }
        .custom-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }
        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Barre de navigation -->
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="<?= $profilPage ?>">Mon Tableau de Bord</a>
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <li class="nav-item">
                                <a class="nav-link" href="deconnexion.php" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">Déconnexion</a>
                            </li>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="d-flex align-items-center mb-4">
            <img src="icon_profil.jpg" alt="Avatar" class="avatar me-3">
            <h1 class="text-center">Bonjour <?php echo $nom_utilisateur . ' ' . $prenom_utilisateur; ?></h1>
        </div>
        
        <div class="row mt-4">

                <!-- Formulaire de modification des infos -->
                <div class="card">
            <div class="card-header bg-warning text-dark">Modifier mes informations</div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="champ" class="form-label">Élément à modifier :</label>
                        <select class="form-control" id="champ" name="champ" required>
                            <option value="nom">Nom</option>
                            <option value="prenom">Prénom</option>
                            <option value="mdp">Mot de passe</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nouvelle_valeur" class="form-label">Nouvelle valeur :</label>
                        <input type="text" class="form-control" id="nouvelle_valeur" name="nouvelle_valeur" required>
                    </div>
                    <button type="submit" name="modify_user" class="btn btn-warning">Modifier</button>
                </form>
                <?php if (isset($successMessage)) echo "<p class='text-success mt-2'>$successMessage</p>"; ?>
                <?php if (isset($errorMessage)) echo "<p class='text-danger mt-2'>$errorMessage</p>"; ?>
            </div>
        </div>

            
        </div>

    </div>
</body>
</html>
