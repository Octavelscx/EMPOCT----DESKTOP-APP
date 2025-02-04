<?php
function deconnexion() {
    session_start(); // Démarrer la session
    session_destroy(); // Détruire la session
    header("Location: index.php"); // Rediriger vers la page d'accueil
    exit();
}

// Appeler la fonction immédiatement
deconnexion();
