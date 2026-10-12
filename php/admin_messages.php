<?php
// php/admin_messages.php — back-office : liste des messages (version sécurisée)
//
// Remédiation :
//  - XSS STOCKÉ : chaque champ issu de la base est échappé avec e() /
//    htmlspecialchars() à l'affichage. Un payload <script> envoyé via le
//    formulaire de contact s'affiche désormais comme du texte et ne s'exécute pas.
//  - Accès : réservé à l'admin via la session serveur (require_admin). Plus de
//    cookie statique "admin_secret_token_999" posé puis vérifié.
//  - Connexion BDD : via le PDO centralisé.
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/auth.php';

require_admin();

$stmt = $pdo->query('SELECT nom, sujet, message, date_envoi FROM messages ORDER BY date_envoi DESC');
$messages = $stmt->fetchAll();
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

    <?php if ($messages): ?>
        <?php foreach ($messages as $row): ?>
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <b>De :</b> <?= e($row['nom']) ?><br>
                <b>Sujet :</b> <?= e($row['sujet']) ?><br><br>
                <b>Message :</b><br><?= nl2br(e($row['message'])) ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        Aucun message.
    <?php endif; ?>
</body>
</html>
