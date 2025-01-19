<?php 
function est_connecte (): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($_SESSION['connecte']);
}

function forcer_utilisateur_connecte () : void {
    if (!est_connecte()) {
        header('Location: index.php');
        exit();
    }
}

function deconnexion(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_destroy();
    header("Location: ../index.php");
    exit();
}
