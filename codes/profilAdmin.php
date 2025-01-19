<?php
session_start();

// Vérifie si l'utilisateur est connecté et qu'il est administrateur
if (!isset($_SESSION['id_user']) || !isset($_SESSION['statut']) || $_SESSION['statut'] != 1) {
    header('Location: connexionAdmin.php');
    exit();
}

include("connexion_BDD.php");

// Gestion des actions (ajouter ou supprimer un médecin)
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        // Ajouter un médecin
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $id_connexion = $_POST['id_connexion'];
        $mdp = password_hash($_POST['mdp'], PASSWORD_DEFAULT); // Hacher le mot de passe

        $stmt = $pdo->prepare("INSERT INTO User (nom, prenom, id_connexion, mdp, statut) VALUES (:nom, :prenom, :id_connexion, :mdp, 0)");
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':id_connexion' => $id_connexion,
            ':mdp' => $mdp
        ]);
        $message = "Le compte médecin a été créé avec succès.";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        // Supprimer un médecin
        $id_user = $_POST['id_user'];

        $stmt = $pdo->prepare("DELETE FROM User WHERE id_user = :id_user AND statut = 0");
        $stmt->execute([':id_user' => $id_user]);
        $message = "Le compte médecin a été supprimé avec succès.";
    }
}

// Récupérer la liste des médecins
$stmt = $pdo->prepare("SELECT * FROM User WHERE statut = 0");
$stmt->execute();
$medecins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Administrateur</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1CmrxMRARb6aLqgBO7uuGH8tzK3OClMZ1TLlMy0fYZQHcxZ5qI5QOeJd8OS6Fm1k" crossorigin="anonymous">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Espace Administrateur</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-md-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Accueil</a>
                    </li>
                </ul>
                <a href="deconnexion.php" class="btn btn-outline-danger">Se déconnecter</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container" style="margin-top: 80px;">
        <div class="bg-light p-5 rounded">
            <h1 class="display-4">Espace Administrateur</h1>
            <p class="lead">Bienvenue, <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?>.</p>
            <hr class="my-4">

            <!-- Message -->
            <?php if (!empty($message)) : ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Liste des médecins -->
            <h2>Liste des Médecins</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Identifiant</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medecins as $medecin) : ?>
                        <tr>
                            <td><?php echo $medecin['id_user']; ?></td>
                            <td><?php echo htmlspecialchars($medecin['nom']); ?></td>
                            <td><?php echo htmlspecialchars($medecin['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($medecin['id_connexion']); ?></td>
                            <td>
                                <form action="profilAdmin.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="id_user" value="<?php echo $medecin['id_user']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Formulaire d'ajout de médecin -->
            <h2>Créer un Compte Médecin</h2>
            <form action="profilAdmin.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" required>
                </div>
                <div class="mb-3">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" required>
                </div>
                <div class="mb-3">
                    <label for="id_connexion" class="form-label">Identifiant</label>
                    <input type="text" class="form-control" id="id_connexion" name="id_connexion" required>
                </div>
                <div class="mb-3">
                    <label for="mdp" class="form-label">Mot de Passe</label>
                    <input type="password" class="form-control" id="mdp" name="mdp" required>
                </div>
                <button type="submit" class="btn btn-primary">Créer</button>
            </form>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-tQ7AOsczhFGx1ZcmtRWIOV2z9WmrPLc5IPmFZlgEJG8w9LR7WwPYdKOMBOpr5Wx5" crossorigin="anonymous"></script>
</body>
</html>
