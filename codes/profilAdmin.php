<?php
session_start();

// Connexion à la base de données
$host = 'localhost';
$dbname = 'empoct_app_medecin';
$username = 'root';
$password = '';

/*
// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: connexionAdmin.php');
    exit();
}*/

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
    $stmt = $pdo->prepare("SELECT nom, prenom FROM User WHERE id_user = :id_user");
    $stmt->bindParam(':id_user', $_SESSION['id_user'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $nom_utilisateur = htmlspecialchars($user['nom']);
        $prenom_utilisateur = htmlspecialchars($user['prenom']);
    }
}

// Récupération des professionnels de santé
$stmt = $pdo->query("SELECT id_user, nom, prenom FROM User WHERE statut = 0");
$professionnels = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <li class="nav-item"><a class="nav-link active" href="profilAdmin.php">Mon espace</a></li>
                        <li class="nav-item"><a class="nav-link" href="gererComptes.php">Gérer les comptes</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="d-flex align-items-center mb-4">
            <img src="icon_profil.jpg" alt="Avatar" class="avatar me-3">
            <h1 class="text-center">Bienvenu sur l'espace administrateur</h1>
        </div>
        
        <div class="row mt-4">

            <!-- Section Mon Compte -->
            <div class="col-md-6">
                <div class="card custom-card visites">
                    <div class="card-header bg-success text-white">Mon compte</div>
                    <div class="card-body">
                        <p>Nom : <?php echo $nom_utilisateur; ?></p>
                        <p>Prénom : <?php echo $prenom_utilisateur; ?></p>
                    </div>
                    <div class="card-body d-flex justify-content-evenly">
                            <a href="modif_infos.php" class="btn btn-info btn-custom">Modifier mes informations</a>
                    </div>
                </div>
            </div>

            <!-- Section Liste des professionnels de santé -->
                <div class="col-md-6">
                    <div class="card custom-card planning">
                        <div class="card-header bg-primary text-white">Liste des professionnels de santé</div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>N° compte</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($professionnels as $pro) : ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($pro['id_user']); ?></td>
                                            <td><?php echo htmlspecialchars($pro['nom']); ?></td>
                                            <td><?php echo htmlspecialchars($pro['prenom']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

        </div>

    </div>
</body>
</html>
