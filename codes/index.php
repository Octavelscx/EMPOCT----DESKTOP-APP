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

// Récupération des patients
$query = $pdo->query("SELECT * FROM Patients");
$patients = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Médecin</title>
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
        .planning {
            background-color: #E7F3FF;
        }
        .visites {
            background-color: #E8F5E9;
        }
        .compte {
            background-color: #E3F2FD;
        }
        .card-header {
            font-weight: bold;
        }
        .btn-custom {
            border-radius: 20px;
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
                        <li class="nav-item"><a class="nav-link active" href="#mon-espace">Mon espace</a></li>
                        <li class="nav-item"><a class="nav-link" href="#mes-patients">Mes patients</a></li>
                        <li class="nav-item"><a class="nav-link" href="#configuration">Configuration</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Section d'accueil -->
        <div class="d-flex align-items-center mb-4">
            <img src="https://via.placeholder.com/80" alt="Avatar" class="avatar me-3">
            <h1 class="text-center">Bonjour Dr [Nom]</h1>
        </div>

        <!-- Section Planning -->
        <div class="row">
            <div class="col-md-6">
                <div class="card custom-card planning">
                    <div class="card-header bg-primary text-white">Mon Planning</div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Lundi</th>
                                    <th>Mardi</th>
                                    <th>Mercredi</th>
                                    <th>Jeudi</th>
                                    <th>Vendredi</th>
                                    <th>Samedi</th>
                                    <th>Dimanche</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-danger">Mme Jean - 10h</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-danger">Mme Jane - 14h30</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-between mt-2">
                            <a href="prise_rdv.php" class="btn btn-primary btn-custom">Ajouter RDV</a>
                            <a href="#" class="btn btn-warning btn-custom">Mettre à jour RDV</a>
                            <a href="#" class="btn btn-danger btn-custom">Supprimer RDV</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dernières visites -->
            <div class="col-md-6">
                <div class="card custom-card visites">
                    <div class="card-header bg-success text-white">Dernières visites</div>
                    <div class="card-body">
                        <?php foreach ($patients as $patient): ?>
                            <div class="d-flex justify-content-between align-items-center p-2 mb-2" style="background-color: #B0D8F8; border-radius: 10px;">
                                <span><?= htmlspecialchars($patient['nom_patient'] . ' ' . $patient['prenom_patient']) ?></span>
                                <span><?= htmlspecialchars($patient['date_debut']) ?></span>
                                <span>10h</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Mon Compte -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card custom-card compte">
                    <div class="card-header bg-info text-white">Mon Compte</div>
                    <div class="card-body d-flex justify-content-evenly">
                        <a href="#" class="btn btn-info btn-custom">Modifier mes informations</a>
                        <a href="#" class="btn btn-danger btn-custom">Supprimer mon compte</a>
                        <a href="#" class="btn btn-secondary btn-custom">Prise en main</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
