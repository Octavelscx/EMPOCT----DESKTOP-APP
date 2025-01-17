<?php
// Démarrer une session (uniquement si nécessaire)
session_start();

try {
    // Connexion à la base de données avec PDO
    $bdd = new PDO(
        'mysql:host=localhost;dbname=empoct_app_medecin;charset=utf8',
        'root', // Nom d'utilisateur de la base de données
        '',  // Mot de passe de la base de données
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Activer les exceptions PDO
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Mode de récupération par défaut
            PDO::ATTR_EMULATE_PREPARES => false // Désactiver les requêtes préparées émulées
        ]
    );
} catch (Exception $e) {
    // En cas d'erreur, afficher un message et arrêter le script
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}
