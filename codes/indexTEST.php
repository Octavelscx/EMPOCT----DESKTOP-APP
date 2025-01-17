<?php include ("connexion_BDD.php") ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empoct</title>
    <link rel="stylesheet" href="style_css/indexTEST.css">
</head>
<body>
    <div class="container">
        <!-- Section gauche avec image -->
        <div class="left-section">
            <img src="doctors.jpg" alt="Médecins en discussion" class="rounded-image">
            <div class="logo">EMPOCT</div>
        </div>
        <!-- Section droite avec boutons -->
        <div class="right-section">
            <button onclick="window.location.href='solution.php'">Découvrez notre solution</button>
            <button onclick="window.location.href='connexionMedecin.php'">Médecin ? Connectez-vous</button>
            <button onclick="window.location.href='downloadApp.php'">Patient ? Télécharger notre app</button>
        </div>
    </div>
</body>
</html>
