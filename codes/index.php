<?php
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: connexionMedecin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMPOCT</title>
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
        .buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .buttons a {
            text-decoration: none;
            color: #3b82f6;
            border: 2px solid #3b82f6;
            padding: 10px 20px;
            border-radius: 20px;
            text-align: center;
            transition: 0.3s;
        }
        .buttons a:hover {
            background: #3b82f6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="image-container">
            <div class="logo">EMPOCT</div>
            <img src="image.png" alt="Médecins en discussion">
        </div>
        <div class="buttons">
            <a href="#">Découvrez notre solution</a>
            <a href="#">Médecin ? Connectez-vous</a>
            <a href="#">Patient ? Télécharger notre app</a>
        </div>
    </div>
</body>
</html>

