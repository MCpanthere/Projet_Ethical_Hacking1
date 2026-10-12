<?php
// php/contact.php — réception d'un message de contact (version sécurisée)
//
// Remédiation (SQLi en écriture + stockage du payload XSS) :
//  - Requête préparée : nom / sujet / message ne peuvent plus modifier le SQL.
//  - Les données sont stockées telles quelles (brutes) ; l'échappement se fait à
//    l'AFFICHAGE (admin_messages.php), ce qui neutralise le XSS stocké sans
//    altérer le contenu légitime.
//  - Les erreurs SQL ne sont plus renvoyées au visiteur.
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim((string)($_POST['nom'] ?? ''));
    $sujet   = trim((string)($_POST['sujet'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if ($nom === '' || $sujet === '' || $message === '') {
        http_response_code(400);
        echo '<h2>Tous les champs sont obligatoires.</h2>';
        echo "<a href='../html/contact.html'>Retour au formulaire</a>";
        return;
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO messages (nom, sujet, message) VALUES (?, ?, ?)'
        );
        $stmt->execute([$nom, $sujet, $message]);

        echo "<h2 style='color:green;'>Message envoyé avec succès au support !</h2>";
        echo "<a href='../html/index.html'>Retourner à l'accueil</a>";
    } catch (PDOException $ex) {
        error_log('Erreur insertion message : ' . $ex->getMessage());
        http_response_code(500);
        echo "<h2>Une erreur est survenue, merci de réessayer plus tard.</h2>";
    }
}
