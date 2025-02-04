<?php
session_start();

// Connexion à la base de données
$host = 'localhost';
$dbname = 'empoct_app_medecin';
$username = 'root';
$password = '';


// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit();
}

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
    $stmt = $pdo->prepare("SELECT nom, prenom FROM user WHERE id_user = :id_user");
    $stmt->bindParam(':id_user', $_SESSION['id_user'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $nom_utilisateur = htmlspecialchars($user['nom']);
        $prenom_utilisateur = htmlspecialchars($user['prenom']);
    }
}

// Récupération des ID des médecins (statut = 0)
$stmt = $pdo->prepare("SELECT id_user, nom, prenom FROM user WHERE statut = 0");
$stmt->execute();
$medecins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion de la création d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $id_connexion = htmlspecialchars($_POST['id_connexion']);
    $statut = 0; // Médecin
    $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);

    // Vérification de l'unicité de l'identifiant de connexion
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE id_connexion = :id_connexion");
    $stmt->bindParam(':id_connexion', $id_connexion, PDO::PARAM_STR);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $errorMessage = "Cet identifiant est déjà pris. Veuillez en saisir un autre.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO user (id_connexion, nom, prenom, mdp, statut) VALUES (:id_connexion, :nom, :prenom, :mdp, :statut)");
            $stmt->bindParam(':id_connexion', $id_connexion, PDO::PARAM_STR);
            $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(':mdp', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':statut', $statut, PDO::PARAM_INT);
            $stmt->execute();

            $successMessage = "Utilisateur créé avec succès. Mot de passe : " . $password;
        } catch (PDOException $e) {
            $errorMessage = "Erreur lors de la création de l'utilisateur : " . $e->getMessage();
        }
    }
}

// Gestion de la modification d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modify_user'])) {
    $id_user = intval($_POST['id_user']);
    $champ_a_modifier = $_POST['champ'];
    $nouvelle_valeur = htmlspecialchars($_POST['nouvelle_valeur']);

    $champs_valides = ['nom', 'prenom', 'id_connexion', 'mdp'];
    if (in_array($champ_a_modifier, $champs_valides)) {
        try {
            if ($champ_a_modifier === 'mdp') {
                $nouvelle_valeur = password_hash($nouvelle_valeur, PASSWORD_DEFAULT);
            }
            $stmt = $pdo->prepare("UPDATE user SET $champ_a_modifier = :nouvelle_valeur WHERE id_user = :id_user");
            $stmt->bindParam(':nouvelle_valeur', $nouvelle_valeur, PDO::PARAM_STR);
            $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
            $stmt->execute();

            $successMessage = "Compte modifié avec succès.";
        } catch (PDOException $e) {
            $errorMessage = "Erreur lors de la modification : " . $e->getMessage();
        }
    } else {
        $errorMessage = "Champ de modification invalide.";
    }
}

// Suppression d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $id_user = intval($_POST['id_user']);
    try {
        $stmt = $pdo->prepare("DELETE FROM user WHERE id_user = :id_user");
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->execute();
        $successMessage = "Compte supprimé avec succès.";
    } catch (PDOException $e) {
        $errorMessage = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Administrateur</title>
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
                <a class="navbar-brand" href="#">Mon Tableau de Bord</a>
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link" href="gererComptes.php">Gérer les comptes</a></li>
                        <li class="nav-item">
                            <a class="nav-link" href="deconnexion.php" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">Déconnexion</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="d-flex align-items-center mb-4">
            <img src="icon_profil.jpg" alt="Avatar" class="avatar me-3">
            <h1 class="text-center">Bonjour <?php echo $nom_utilisateur . ' ' . $prenom_utilisateur; ?></h1>
        </div>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger"> <?php echo $errorMessage; ?> </div>
        <?php elseif (isset($successMessage)): ?>
            <div class="alert alert-success"> <?php echo $successMessage; ?> </div>
        <?php endif; ?>

        <!-- Formulaire de création d'un utilisateur -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">Créer un utilisateur</div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom :</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom :</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" required>
                    </div>
                    <div class="mb-3">
                        <label for="id_connexion" class="form-label">ID de connexion :</label>
                        <input type="text" class="form-control" id="id_connexion" name="id_connexion" required>
                    </div>
                    <button type="submit" name="create_user" class="btn btn-success">Créer</button>
                </form>
            </div>
        </div>

        <!-- Formulaire de modification d'un utilisateur -->
        <div class="card">
            <div class="card-header bg-warning text-dark">Modifier un compte</div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="id_user" class="form-label">N° compte :</label>
                        <select class="form-control" id="id_user" name="id_user" required>
                            <option value="">Sélectionner un compte</option>
                            <?php foreach ($medecins as $medecin): ?>
                                <option value="<?php echo $medecin['id_user']; ?>">
                                    <?php echo $medecin['id_user'] . " - " . $medecin['nom'] . " " . $medecin['prenom']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="champ" class="form-label">Élément à modifier :</label>
                        <select class="form-control" id="champ" name="champ" required>
                            <option value="nom">Nom</option>
                            <option value="prenom">Prénom</option>
                            <option value="id_connexion">ID de connexion</option>
                            <option value="mdp">Mot de passe</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="nouvelle_valeur" class="form-label">Nouvelle valeur :</label>
                        <input type="text" class="form-control" id="nouvelle_valeur" name="nouvelle_valeur" required>
                    </div>

                    <button type="submit" name="modify_user" class="btn btn-warning">Modifier</button>
                </form>
            </div>
        </div>
<br>
        <!-- Formulaire de suppression d'un utilisateur -->
        <div class="card">
            <div class="card-header bg-danger text-white">Supprimer un compte utilisateur</div>
            <div class="card-body">
                <?php if (isset($errorMessage)): ?>
                    <div class="alert alert-danger"> <?php echo $errorMessage; ?> </div>
                <?php elseif (isset($successMessage)): ?>
                    <div class="alert alert-success"> <?php echo $successMessage; ?> </div>
                <?php endif; ?>
                <form id="deleteForm" method="POST" action="">
                    <div class="mb-3">
                        <label for="id_user" class="form-label">Titulaire du compte :</label>
                        <select class="form-control" id="id_user" name="id_user" onchange="updateUserInfo()" required>
                            <option value="">Sélectionner un compte</option>
                            <?php foreach ($medecins as $medecin): ?>
                                <option value="<?php echo $medecin['id_user']; ?>">
                                    <?php echo $medecin['id_user'] . " - " . $medecin['nom'] . " " . $medecin['prenom']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                
                    <button type="submit" name="delete_user" class="btn btn-danger" onclick="confirmDeletion(event)">Supprimer</button>
                </form>
            </div>
        </div>

    </div>
</body>
</html>
