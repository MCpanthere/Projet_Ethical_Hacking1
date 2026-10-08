<?php
// html/login.php
require_once __DIR__ . '/../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $password = $_POST['mot_de_passe'];

    // Requête de vérification simple
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE identifiant = ? AND mot_de_passe = ?");
    $stmt->execute([$nom, $password]);
    $user = $stmt->fetch();

    if ($user) {
        echo "<h1>Bienvenue " . htmlspecialchars($user['identifiant']) . " !</h1>";
        if ($user['role'] === 'admin') {
            echo "<a href='../php/admin_messages.php'>Accéder au panneau d'administration</a>";
        }
    } else {
        echo "<p style='color:red;'>Identifiant ou mot de passe incorrect.</p>";
        echo "<a href='connexion.html'>Réessayer</a>";
    }
}
?>