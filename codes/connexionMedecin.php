<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"> <!-- Encodage des caractères -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Fait varier la taille de la page en fonction de l'appareil utilisé (ordinateur, smartphone, ...) -->
    <title> Connexion </title>
    <link rel="stylesheet" href="style_css/connexion.css"> <!-- Lien vers le fichier CSS -->
</head>

<body>
<div class="container">
        <!-- Partie avec image et bords arrondis -->
        <div class="left-section">
            <img src="image.png" alt="Photo" class="rounded-image">
        </div>
        <!-- Partie avec le formulaire -->
        <div class="right-section">
            <h2>Bienvenue sur notre interface médecin</h2>
            <p>Veuillez vous connecter pour accéder à votre espace</p>
            <form>
                <label for="identifiant">Identifiant</label>
                <input type="text" id="identifiant" name="identifiant" required>
                
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
                
                <button type="submit">Se connecter</button>
            </form>
            <p>Pas de compte ? <a href="#">Inscrivez-vous</a></p>
        </div>
    </div>
</body>
</html>
