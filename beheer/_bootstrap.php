<?php
declare(strict_types=1);

// Gedeelde basis voor elke beheerpagina.
require __DIR__ . '/../config.php';
ini_set('display_errors', DEBUG ? '1' : '0');

require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/helpers.php';
require __DIR__ . '/../src/lang.php';
require __DIR__ . '/_layout.php';

// Extra beveiliging: niet in een frame laden, geen bestandstypes raden.
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

// Sessie = "ingelogd blijven". Cookie alleen voor /beheer/, niet leesbaar voor JavaScript.
session_name('hrf_beheer');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => rtrim(BASE_URL, '/') . '/beheer/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

const SESSION_IDLE = 7200; // na 2 uur niets doen: opnieuw inloggen

function adminUrl(string $file = '', array $query = []): string
{
    $url = rtrim(BASE_URL, '/') . '/beheer/' . $file;
    return $query ? $url . '?' . http_build_query($query) : $url;
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

// ----- Bescherming tegen vervalste formulieren (CSRF) -----
function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

function checkCsrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Ongeldig formulier. Ga terug, vernieuw de pagina en probeer opnieuw.');
    }
}

// ----- Inloggen -----
function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    if (time() - ($_SESSION['last_seen'] ?? 0) > SESSION_IDLE) {
        logoutUser();
        return null;
    }
    $_SESSION['last_seen'] = time();
    return ['id' => (int) $_SESSION['user_id'], 'username' => $_SESSION['username']];
}

function requireLogin(): array
{
    $user = currentUser();
    if ($user === null) {
        redirect(adminUrl('login.php'));
    }
    return $user;
}

function loginUser(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']   = (int) $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['last_seen'] = time();
}

function logoutUser(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}

// ----- Korte meldingen na opslaan ("Opgeslagen") -----
function flash(string $message): void
{
    $_SESSION['flash'] = $message;
}

function takeFlash(): ?string
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

// Van een titel een adres maken: "À propos" -> "a-propos"
function slugify(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    $text = strtr($text, [
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n', 'ß' => 'ss', 'œ' => 'oe', 'æ' => 'ae',
    ]);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

// Tekstveld uit een formulier: ingekort en zonder spaties rond.
function postText(string $key, int $max): string
{
    return mb_substr(trim((string) ($_POST[$key] ?? '')), 0, $max);
}

// Geldige datum (JJJJ-MM-DD) of null.
function validDate(string $value): ?string
{
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return ($date && $date->format('Y-m-d') === $value) ? $value : null;
}

// Geldig uur (UU:MM) of null.
function validTime(string $value): ?string
{
    return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) ? $value . ':00' : null;
}
