<?php
// php/auth.php — gestion de session et contrôle d'accès (version sécurisée)
//
// Remédiation du "vol de session par cookie" :
//  - On n'utilise plus un cookie de session deviné/statique
//    ("session_id=admin_secret_token_999") que n'importe qui peut rejouer.
//  - On s'appuie sur les sessions natives PHP. Le cookie de session (PHPSESSID)
//    est opaque, aléatoire, marqué HttpOnly (inaccessible au JavaScript, donc non
//    volable par XSS), SameSite=Strict (non envoyé par un site tiers) et Secure
//    en HTTPS. Le rôle réel est stocké côté serveur dans $_SESSION, jamais dans
//    le cookie.
//  - L'identifiant de session est régénéré à la connexion (anti-fixation).

function secure_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443)
    );

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,       // le cookie n'est pas lisible via document.cookie
        'secure'   => $secure,    // envoyé uniquement en HTTPS quand disponible
        'samesite' => 'Strict',   // pas d'envoi en contexte cross-site
    ]);

    session_name('EHSESSID');
    session_start();
}

/** Vrai si un utilisateur est connecté. */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/** Vrai si l'utilisateur connecté est administrateur. */
function is_admin(): bool
{
    return is_logged_in() && (($_SESSION['role'] ?? '') === 'admin');
}

/**
 * Barrière pour une page réservée à l'admin.
 * Le rôle vient de $_SESSION (serveur), impossible à falsifier via un cookie.
 */
function require_admin(): void
{
    secure_session_start();
    if (!is_admin()) {
        http_response_code(403);
        die('<h1>Accès refusé.</h1><p>Vous devez être administrateur pour voir cette page.</p>');
    }
}

/** Échappement HTML court pour l'affichage. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
