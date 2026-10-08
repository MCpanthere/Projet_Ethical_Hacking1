<?php
// php/contact.php
$conn = new mysqli("localhost", "app_user", "password123", "projet_eh");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $sujet = $_POST['sujet'];
    $message = $_POST['message'];

    // Insertion vulnérable en base
    $sql = "INSERT INTO messages (nom, sujet, message) VALUES ('$nom', '$sujet', '$message')";
    
    if ($conn->query($sql) === TRUE) {
        // Succès : on propose de revenir à l'accueil
        echo "<h2 style='color:green;'>Message envoyé avec succès au support !</h2>";
        echo "<a href='../html/index.html'>Retourner à l'accueil</a>";
    } else {
        echo "Erreur : " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nous contacter</title>

</head>
<body>
    <h1>Formulaire de contact (Public)</h1>
    <form method="POST" action="">
        Nom: <br><input type="text" name="nom"><br><br>
        Sujet: <br><input type="text" name="sujet"><br><br>
        Message:<br> <textarea name="message" rows="5" cols="40"></textarea><br><br>
        <input type="submit" value="Envoyer au support">
    </form>
</body>
</html>