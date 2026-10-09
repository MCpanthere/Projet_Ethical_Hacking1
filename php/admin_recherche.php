<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// VÉRIFICATION DE SESSION : L'attaquant doit posséder ce cookie volé pour accéder à cette page
if(!isset($_COOKIE['session_id']) || $_COOKIE['session_id'] !== 'admin_secret_token_999') {
    die("<h1>Accès refusé.</h1><p>Vous devez être administrateur pour voir cette page.</p>");
}

$conn = new mysqli("localhost", "app_user", "password123", "projet_eh");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Back-office : Recherche</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Recherche d'utilisateurs (Accès Restreint Admin)</h1>
    <form method="GET" action="">
        Identifiant : <input type="text" name="recherche">
        <input type="submit" value="Chercher">
    </form>
    <br>
    
    <?php
    if (isset($_GET['recherche']) && !empty($_GET['recherche'])) {
        $recherche = $_GET['recherche'];
        
        // VULNÉRABILITÉ SQLi : Concaténation directe de l'entrée utilisateur
        $sql = "SELECT identifiant, role FROM utilisateurs WHERE identifiant = '$recherche'";
        
        // Ligne décommentée pour t'aider à visualiser la requête lors de tes tests d'injection
        // echo "<p><i>Requête exécutée : " . $sql . "</i></p>";

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            echo "<h3>Résultats :</h3><ul>";
            while($row = $result->fetch_assoc()) {
                // L'affichage direct de 2 colonnes permet facilement l'injection UNION
                echo "<li>Identifiant : <b>" . $row['identifiant'] . "</b> - Rôle : <b>" . $row['role'] . "</b></li>";
            }
            echo "</ul>";
        } else {
            // L'erreur MySQL est affichée pour faciliter l'exploitation manuelle
            if ($conn->error) {
                echo "<p style='color:red;'>Erreur SQL : " . $conn->error . "</p>";
            } else {
                echo "Aucun utilisateur trouvé.";
            }
        }
    }
    ?>
</body>
</html>