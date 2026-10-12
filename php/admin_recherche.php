<?php
// php/admin_recherche.php — recherche d'utilisateurs (version sécurisée)
//
// Remédiation :
//  - SQLi : requête PRÉPARÉE paramétrée ; le terme de recherche ne peut plus
//    injecter de UNION ni altérer la requête. (La recherche exacte d'origine est
//    conservée ; un LIKE paramétré serait possible sans rouvrir la faille.)
//  - Plus d'affichage de la requête ni des erreurs MySQL (display_errors off,
//    pas d'écho de $conn->error) : on ne guide plus l'attaquant.
//  - Accès réservé à l'admin via la session serveur (require_admin), plus via le
//    cookie statique volable.
//  - Sorties échappées (e()) : les valeurs affichées ne peuvent pas porter de XSS.
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/auth.php';

require_admin();

$resultats = [];
$recherche = trim((string)($_GET['recherche'] ?? ''));

if ($recherche !== '') {
    $stmt = $pdo->prepare('SELECT identifiant, role FROM utilisateurs WHERE identifiant = ?');
    $stmt->execute([$recherche]);
    $resultats = $stmt->fetchAll();
}
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
        Identifiant : <input type="text" name="recherche" value="<?= e($recherche) ?>">
        <input type="submit" value="Chercher">
    </form>
    <br>

    <?php if ($recherche !== ''): ?>
        <?php if ($resultats): ?>
            <h3>Résultats :</h3>
            <ul>
                <?php foreach ($resultats as $row): ?>
                    <li>Identifiant : <b><?= e($row['identifiant']) ?></b> - Rôle : <b><?= e($row['role']) ?></b></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            Aucun utilisateur trouvé.
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
