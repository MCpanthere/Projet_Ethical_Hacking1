<?php
session_start();
// On simule que l'admin vient de se connecter
// C'est ce cookie précis que l'attaquant cherchera à voler avec le XSS
setcookie("session_id", "admin_secret_token_999", time() + 3600, "/");

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
            // VULNÉRABILITÉ XSS STOCKÉ : Affichage direct du contenu sans htmlspecialchars()
            echo "<div style='border: 1px solid red; padding: 10px; margin-bottom: 10px; background-color: #ffe6e6;'>";
            echo "<b>De :</b> " . $row['nom'] . "<br>";
            echo "<b>Sujet :</b> " . $row['sujet'] . "<br><br>";
            echo "<b>Message :</b> <br>" . $row['message']; // C'est ici que le payload JS de l'attaquant s'exécutera
            echo "</div>";
        }
    } else {
        echo "Aucun message.";
    }
    ?>
</body>
</html>