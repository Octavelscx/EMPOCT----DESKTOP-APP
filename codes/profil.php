<?php
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: connexionMedecin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Médecin</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1CmrxMRARb6aLqgBO7uuGH8tzK3OClMZ1TLlMy0fYZQHcxZ5qI5QOeJd8OS6Fm1k" crossorigin="anonymous">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Espace Médecin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-md-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Patients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Paramètres</a>
                    </li>
                </ul>
                <a href="logout.php" class="btn btn-outline-danger">Se déconnecter</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container" style="margin-top: 80px;">
        <div class="bg-light p-5 rounded">
            <h1 class="display-4">Bienvenue, <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?> !</h1>
            <p class="lead">Vous êtes connecté à l'espace professionnel de santé.</p>
            <hr class="my-4">
            <p>Explorez les fonctionnalités disponibles ou gérez vos informations.</p>
            <a class="btn btn-primary btn-lg" href="#" role="button">Commencer</a>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-tQ7AOsczhFGx1ZcmtRWIOV2z9WmrPLc5IPmFZlgEJG8w9LR7WwPYdKOMBOpr5Wx5" crossorigin="anonymous"></script>
</body>
</html>

