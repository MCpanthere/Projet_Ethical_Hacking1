# Version sécurisée — remédiation XSS, SQLi et vol de session

Cette branche `version-securisee` corrige les vulnérabilités de la version
d'origine **sans casser les fonctionnalités** (connexion, formulaire de contact,
back-office, recherche d'utilisateurs).

Toutes les corrections sont **côté serveur** (comme l'exige le sujet) : une
protection uniquement côté client serait contournable.

## 1. XSS stocké (formulaire de contact → back-office)

- **Cause racine :** `php/admin_messages.php` réaffichait `nom`, `sujet` et
  `message` issus de la base **sans échappement** (`echo $row['message']`). Un
  message contenant `<script>…</script>` était donc exécuté dans le navigateur de
  l'administrateur qui consultait la boîte de réception.
- **Remédiation :** échappement systématique à l'affichage avec
  `htmlspecialchars()` (`ENT_QUOTES | ENT_SUBSTITUTE`, UTF-8), via le helper
  `e()` de `php/auth.php`. Le message est stocké brut mais rendu comme du texte.

## 2. Injection SQL (recherche d'utilisateurs + insertion de message)

- **Cause racine :** concaténation directe de l'entrée utilisateur dans la
  requête — `WHERE identifiant = '$recherche'` dans `php/admin_recherche.php`, et
  `INSERT … VALUES ('$nom', '$sujet', '$message')` dans `php/contact.php`.
  Permettait les attaques `UNION SELECT` (extraction de `mot_de_passe`,
  `num_securite_sociale`).
- **Remédiation :** **requêtes préparées** paramétrées (PDO, `ATTR_EMULATE_PREPARES = false`)
  partout. L'entrée est transmise comme donnée, jamais comme SQL.
- **En complément :** plus aucun affichage de la requête ni des erreurs MySQL au
  visiteur (`display_errors` off, erreurs journalisées côté serveur).

## 3. Vol de session par cookie

- **Cause racine :** l'accès admin reposait sur un cookie **statique et devinable**
  (`session_id = "admin_secret_token_999"`) posé par `admin_messages.php` et
  vérifié par `admin_recherche.php`. Volable par le XSS (cookie lisible en JS) et
  rejouable tel quel.
- **Remédiation (`php/auth.php`) :** vraie session PHP native. Le cookie
  (`EHSESSID`) est aléatoire, **HttpOnly** (inaccessible au JavaScript, donc non
  volable par XSS), **SameSite=Strict** et **Secure** en HTTPS. Le rôle réel est
  stocké **côté serveur** dans `$_SESSION`, jamais dans le cookie. L'identifiant
  de session est **régénéré à la connexion** (anti-fixation). Les pages admin
  sont protégées par `require_admin()`.

## 4. Durcissements complémentaires

- **Mots de passe hachés** (bcrypt via `password_hash` / `password_verify`) au
  lieu du stockage en clair ; le login ne révèle pas quel champ est erroné.
- **Config BDD centralisée** dans `db.php` (PDO unique, identifiants surclassables
  par variables d'environnement), au lieu de `new mysqli(...)` répété avec le mot
  de passe en clair dans chaque page.

## Preuve avant/après

`tests/poc_remediation.php` rejoue les charges d'attaque sur les motifs d'origine
et sur les motifs corrigés (sortie figée dans `tests/poc_remediation.out.txt`) :
la charge SQLi `UNION` renvoie 2 lignes sensibles sur le motif vulnérable et 0 sur
la requête préparée ; le payload `<script>` s'affiche actif en sortie brute et
échappé (inerte) en sortie corrigée.
