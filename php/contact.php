<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// php/contact.php
$conn = new mysqli("localhost", "app_user", "password123", "projet_eh");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $sujet = $_POST['sujet'];
    $message = $_POST['message'];;

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
