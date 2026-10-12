<?php
// VÉRIFICATION DE SESSION : L'attaquant doit posséder ce cookie volé pour accéder à cette page
ini_set('display_errors', 1);
error_reporting(E_ALL);
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
        
        // CORRECTION (SQLi) : requête préparée, l'entrée est liée comme paramètre (?)
        // et transmise séparément du SQL : elle ne peut plus modifier la requête.
        $stmt = $conn->prepare("SELECT identifiant, role FROM utilisateurs WHERE identifiant = ?");
        $stmt->bind_param("s", $recherche);
        $stmt->execute();
        $result = $stmt->get_result();

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