<?php
// html/login.php — authentification (version sécurisée)
//
// Remédiation :
//  - Mots de passe vérifiés avec password_verify() contre un hachage bcrypt
//    stocké en base (plus de comparaison en clair).
//  - On ne révèle pas si c'est l'identifiant ou le mot de passe qui est faux.
//  - En cas de succès, on ouvre une vraie session serveur et on régénère l'ID
//    (anti-fixation) ; le rôle est stocké côté serveur dans $_SESSION.
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../php/auth.php';

secure_session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim((string)($_POST['nom'] ?? ''));
    $password = (string)($_POST['mot_de_passe'] ?? '');

    // Requête préparée : l'identifiant ne peut pas altérer la requête.
    $stmt = $pdo->prepare('SELECT id, identifiant, mot_de_passe, role FROM utilisateurs WHERE identifiant = ?');
    $stmt->execute([$nom]);
    $user = $stmt->fetch();

    // Diagnostic : un mot de passe encore stocké en clair ne peut jamais être
    // validé par password_verify(). On le signale dans le journal serveur
    // (jamais au visiteur) pour indiquer qu'il faut lancer la migration.
    if ($user && password_get_info($user['mot_de_passe'])['algo'] === null) {
        error_log("login : mot de passe non haché pour '" . $user['identifiant']
            . "' — exécuter sql/migration_hash_mots_de_passe.sql");
    }

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        // Anti-fixation : nouvel identifiant de session à l'élévation de privilège.
        session_regenerate_id(true);
        $_SESSION['user_id']     = $user['id'];
        $_SESSION['identifiant'] = $user['identifiant'];
        $_SESSION['role']        = $user['role'];

        echo '<h1>Bienvenue ' . e($user['identifiant']) . ' !</h1>';
        if (is_admin()) {
            echo "<a href='../php/admin_messages.php'>Accéder au panneau d'administration</a>";
        }
    } else {
        echo "<p style='color:red;'>Identifiant ou mot de passe incorrect.</p>";
        echo "<a href='connexion.html'>Réessayer</a>";
    }
}
