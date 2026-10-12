<?php
session_start();
// On simule que l'admin vient de se connecter
// C'est ce cookie précis que l'attaquant cherchera à voler avec le XSS
// CORRECTION (vol de session) : cookie HttpOnly -> illisible par JavaScript (document.cookie),
// donc impossible à exfiltrer via un XSS ; SameSite=Strict -> non envoyé depuis un site tiers.
setcookie("session_id", "admin_secret_token_999", [
    "expires"  => time() + 3600,
    "path"     => "/",
    "httponly" => true,
    "samesite" => "Strict",
]);

$conn = new mysqli("localhost", "app_user", "password123", "projet_eh");
$result = $conn->query("SELECT * FROM messages ORDER BY date_envoi DESC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Back-office : Messages</title>
</head>
<body>
    <h1>Boîte de réception Administrateur</h1>
    <a href="admin_recherche.php">Aller à la recherche d'utilisateurs</a><br><br>
    
    <?php
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // CORRECTION (XSS stocké) : chaque champ est échappé avec htmlspecialchars() avant affichage,
            // le contenu est affiché comme du texte et n'est plus interprété par le navigateur.
            echo "<div style='border: 1px solid red; padding: 10px; margin-bottom: 10px; background-color: #ffe6e6;'>";
            echo "<b>De :</b> " . htmlspecialchars($row['nom'], ENT_QUOTES, 'UTF-8') . "<br>";
            echo "<b>Sujet :</b> " . htmlspecialchars($row['sujet'], ENT_QUOTES, 'UTF-8') . "<br><br>";
            echo "<b>Message :</b> <br>" . htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8');
            echo "</div>";
        }
    } else {
        echo "Aucun message.";
    }
    ?>
</body>
</html>