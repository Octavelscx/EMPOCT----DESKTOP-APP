<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interface Médecin</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="left-section">
            <div class="logo">
                <span>EMPOCT</span>
            </div>
            <img src="stethoscope.jpg" alt="Stethoscope">
        </div>
        <div class="right-section">
            <h2>Bienvenue sur notre interface médecin.</h2>
            <p>Veuillez vous connecter pour accéder à votre espace.</p>
            <form>
                <label for="identifiant">Identifiant</label>
                <input type="text" id="identifiant" name="identifiant">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password">
                <button type="submit">Se connecter</button>
            </form>
        </div>
    </div>
</body>
</html>
