<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=empoct_app_medecin;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Hacher les mots de passe existants
    $stmt = $pdo->query("SELECT id_user, mdp FROM User");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $hashedPassword = password_hash($row['mdp'], PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE User SET mdp = :hashedPassword WHERE id_user = :id_user");
        $updateStmt->execute([
            ':hashedPassword' => $hashedPassword,
            ':id_user' => $row['id_user']
        ]);
    }

    echo "Mots de passe mis à jour avec succès !";
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
