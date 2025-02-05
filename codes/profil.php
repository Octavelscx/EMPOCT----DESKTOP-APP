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
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
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


// Récupération des patients
$query = $pdo->query("SELECT * FROM Patients");
$patients = $query->fetchAll(PDO::FETCH_ASSOC);

// Récupération du mois et de l'année à afficher (par défaut : mois en cours)
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Nombre de jours dans le mois
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Récupération des rendez-vous pour le mois en cours
$query = $pdo->prepare("
    SELECT r.date_rdv, r.heure, r.description, p.nom_patient, p.prenom_patient
    FROM rendezvous r
    JOIN patients p ON r.id_patient = p.id_patient
    WHERE MONTH(r.date_rdv) = :month AND YEAR(r.date_rdv) = :year
");

$query->execute(['month' => $month, 'year' => $year]);
$appointments = $query->fetchAll(PDO::FETCH_ASSOC);

// Organisation des rendez-vous par jour
$rdvs_by_day = [];
foreach ($appointments as $appointment) {
    $day = date('j', strtotime($appointment['date_rdv']));
    $rdvs_by_day[$day][] = $appointment;
}

// Récupération des 5 prochains rendez-vous à venir
$query = $pdo->prepare("
    SELECT r.date_rdv, r.heure, r.description, p.nom_patient, p.prenom_patient
    FROM rendezvous r
    JOIN patients p ON r.id_patient = p.id_patient
    WHERE r.date_rdv >= CURDATE()
    ORDER BY r.date_rdv ASC, r.heure ASC
    LIMIT 5
");
$query->execute();
$next_appointments = $query->fetchAll(PDO::FETCH_ASSOC);

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
            border-radius: 70%;
        }
    </style>
</head>
<body>

    <?php if (isset($_GET['success']) && $_GET['success'] == "rdv_added"): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Rendez-vous ajouté avec succès !
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] == "missing_fields"): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Tous les champs doivent être remplis !
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    

    <?php if (isset($_GET['success']) && $_GET['success'] == "rdv_deleted"): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Le rendez-vous a été supprimé avec succès !
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] == "no_rdv_selected"): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        Veuillez sélectionner un rendez-vous à supprimer.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>



    <div class="container mt-4">
        <!-- Barre de navigation -->
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" class="nav-link active" href="profil.php">Mon Tableau de Bord</a>
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link" href="gestion_mesures.php">Mes patients</a></li>
                        <li class="nav-item"><a class="nav-link" href="configuration.php">Configuration</a></li>
                        <li class="nav-item">
                            <a class="nav-link" href="deconnexion.php" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">Déconnexion</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Section d'accueil -->
        <div class="d-flex align-items-center mb-4">
            <img src="https://cdn-icons-png.flaticon.com/512/3541/3541871.png" alt="Avatar" class="avatar me-3">
            <h1 class="text-center">Bonjour Dr. <?= $nom_utilisateur ? $nom_utilisateur : "Non défini" ?> </h1>
        </div>

        <!-- Section Planning -->
        <div class="row">
            <div class="col-md-12">
                <div class="card custom-card planning">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <a href="?month=<?= $month == 1 ? 12 : $month - 1 ?>&year=<?= $month == 1 ? $year - 1 : $year ?>" class="btn btn-light">← Mois précédent</a>
                        <h3><?= date('F Y', strtotime("$year-$month-01")) ?></h3>
                        <a href="?month=<?= $month == 12 ? 1 : $month + 1 ?>&year=<?= $month == 12 ? $year + 1 : $year ?>" class="btn btn-light">Mois suivant →</a>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr>
                                    <th>Lun</th>
                                    <th>Mar</th>
                                    <th>Mer</th>
                                    <th>Jeu</th>
                                    <th>Ven</th>
                                    <th>Sam</th>
                                    <th>Dim</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    // Premier jour du mois
                                    $first_day_of_month = date('N', strtotime("$year-$month-01"));

                                    // Compteur pour les jours
                                    $day_counter = 1;

                                    // Affichage des semaines
                                    for ($week = 0; $week < 6; $week++) {
                                        echo "<tr>";
                                        for ($day = 1; $day <= 7; $day++) {
                                            if (($week === 0 && $day < $first_day_of_month) || $day_counter > $days_in_month) {
                                                // Case vide
                                                echo "<td></td>";
                                            } else {
                                                // Affichage du jour et des rendez-vous
                                                echo "<td>";
                                                echo "<strong>$day_counter</strong>";

                                                // Vérification des rendez-vous pour ce jour
                                                if (isset($rdvs_by_day[$day_counter])) {
                                                    foreach ($rdvs_by_day[$day_counter] as $rdv) {
                                                        echo "<div class='mt-1 p-1 bg-success text-white rounded'>
                                                            " . htmlspecialchars($rdv['nom_patient']) . " " . htmlspecialchars($rdv['prenom_patient']) . " à " . htmlspecialchars($rdv['heure']) . "
                                                        </div>";
                                                    }                                                    
                                                }

                                                echo "</td>";
                                                $day_counter++;
                                            }
                                        }
                                        echo "</tr>";
                                        // Arrête si tous les jours du mois ont été affichés
                                        if ($day_counter > $days_in_month) {
                                            break;
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>

                        <!-- Boutons pour gérer les rendez-vous -->
                        
                        <div class="d-flex justify-content-center mt-3">
                            <button class="btn btn-primary btn-custom mx-5 px-4" data-bs-toggle="modal" data-bs-target="#addRdvModal">Ajouter RDV</button>
                            <button class="btn btn-danger btn-custom mx-5 px-4" data-bs-toggle="modal" data-bs-target="#deleteRdvModal">Supprimer RDV</button>
                        </div>



                    </div>
                </div>
            </div>
        

            <!-- Section Derniers Rendez-vous -->
            <div class="col-md-6">
                <div class="card custom-card visites">
                    <div class="card-header bg-success text-white">Prochains rendez-vous</div>
                    <div class="card-body">
                        <?php if (!empty($next_appointments)): ?>
                            <?php foreach ($next_appointments as $rdv): ?>
                                <div class="d-flex justify-content-between align-items-center p-2 mb-2" style="background-color: #B0D8F8; border-radius: 10px;">
                                    <span><?= htmlspecialchars($rdv['nom_patient'] . ' ' . $rdv['prenom_patient']) ?></span>
                                    <span><?= htmlspecialchars($rdv['date_rdv']) ?></span>
                                    <span><?= htmlspecialchars($rdv['heure']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Aucun rendez-vous à venir.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>


         <!-- Section Mon Compte -->
         <div class="row mt-4">
            <div class="col-md-12">
                <div class="card custom-card compte">
                    <div class="card-header bg-info text-white">Mon Compte</div>
                    <div class="card-body">
                        <p>Nom : <?= $nom_utilisateur ? $nom_utilisateur : "Non défini" ?></p>
                        <p>Prénom : <?= $prenom_utilisateur ? $prenom_utilisateur : "Non défini" ?></p>
                    </div>
                    <div class="card-body d-flex justify-content-evenly">
                        <a href="modif_infos.php" class="btn btn-info btn-custom">Modifier mes informations</a>
                        <a href="#" class="btn btn-secondary btn-custom">Prise en main</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    
    <!-- Modal pour Ajouter un RDV -->
    <div class="modal fade" id="addRdvModal" tabindex="-1" aria-labelledby="addRdvModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRdvModalLabel">Ajouter un Rendez-vous</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addRdvForm" action="ajouter_rdv.php" method="POST">
                        <div class="mb-3">
                            <label for="id_patient" class="form-label">Patient</label>
                            <select class="form-select" name="id_patient" id="id_patient" required>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= $patient['id_patient'] ?>"><?= htmlspecialchars($patient['nom_patient'] . ' ' . $patient['prenom_patient']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="date_rdv" class="form-label">Date</label>
                            <input type="date" class="form-control" name="date_rdv" id="date_rdv" required>
                        </div>
                        <div class="mb-3">
                            <label for="heure" class="form-label">Heure</label>
                            <input type="time" class="form-control" name="heure" id="heure" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="description"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    <!-- Modal pour Supprimer un RDV -->
    <div class="modal fade" id="deleteRdvModal" tabindex="-1" aria-labelledby="deleteRdvModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteRdvModalLabel">Supprimer un Rendez-vous</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="deleteRdvForm" action="supprimer_rdv.php" method="POST">
                        <div class="mb-3">
                            <label for="delete_id_rdv" class="form-label">Sélectionnez le RDV à supprimer</label>
                            <select class="form-select" name="id_rdv" id="delete_id_rdv" required>
                                <option value="" disabled selected>Choisissez un rendez-vous</option>
                                <?php
                                // Récupération des rendez-vous pour affichage dans la liste déroulante
                                $query = $pdo->query("SELECT r.id_rdv, r.date_rdv, r.heure, p.nom_patient, p.prenom_patient FROM rendezvous r JOIN patients p ON r.id_patient = p.id_patient ORDER BY r.date_rdv ASC");
                                $rendezvous = $query->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($rendezvous as $rdv) {
                                    echo "<option value='{$rdv['id_rdv']}'>"
                                        . htmlspecialchars($rdv['nom_patient'] . " " . $rdv['prenom_patient'] . " - " . $rdv['date_rdv'] . " à " . $rdv['heure']) .
                                        "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        if(window.location.search.includes("success=rdv_deleted") || window.location.search.includes("error=no_rdv_selected")) {
            // Supprime le paramètre de l'URL après 3 secondes
            setTimeout(function() {
                window.history.replaceState(null, "", window.location.pathname);
            }, 3000);
        }
    });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if(window.location.search.includes("success=rdv_deleted") || window.location.search.includes("error=no_rdv_selected") || window.location.search.includes("success=rdv_added")) {
                // Supprime le paramètre de l'URL après 3 secondes
                setTimeout(function() {
                    window.history.replaceState(null, "", window.location.pathname);
                }, 3000);
            }
        });
    </script>

</body>
</html>
