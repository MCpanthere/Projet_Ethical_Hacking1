<?php
// db.php — connexion PDO centralisée (version sécurisée)
//
// Remédiation :
//  - Une seule connexion PDO pour toute l'application (plus de mysqli dispersés
//    avec les identifiants en clair répétés dans chaque page).
//  - Les erreurs de base de données ne sont JAMAIS affichées au visiteur : elles
//    sont journalisées côté serveur et une page neutre est renvoyée. Cela évite
//    de divulguer la structure SQL et facilite l'exploitation manuelle.
//  - En production, les identifiants doivent venir de variables d'environnement.

$host    = getenv('DB_HOST') ?: 'localhost';
$dbname  = getenv('DB_NAME') ?: 'projet_eh';
$username = getenv('DB_USER') ?: 'app_user';
$password = getenv('DB_PASS') ?: 'password123';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Empêche l'émulation des requêtes préparées : les paramètres sont
            // réellement envoyés séparément de la requête au serveur MySQL.
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // On journalise le détail, on n'expose rien au visiteur.
    error_log('Erreur de connexion BDD : ' . $e->getMessage());
    http_response_code(500);
    die('Service momentanément indisponible.');
}
