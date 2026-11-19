<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
ini_set('display_errors', DEBUG ? '1' : '0');

require __DIR__ . '/src/db.php';
require __DIR__ . '/src/helpers.php';
require __DIR__ . '/src/lang.php';
require __DIR__ . '/src/content.php';
require __DIR__ . '/src/booking.php';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

$pdo         = db();
$languages   = loadLanguages($pdo);
$defaultLang = defaultLanguage($languages);

// 1. Welk adres vraagt de bezoeker? Bv. /hommerfatigue/fr/a-propos -> ['fr', 'a-propos']
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$base = rtrim(BASE_URL, '/');
if ($base !== '' && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
}
$parts = array_values(array_filter(explode('/', $path), fn($p) => $p !== ''));

// 2. Geen taal in het adres? Stuur door naar de taal van de browser.
if (count($parts) === 0) {
    header('Location: ' . url(browserLanguage($languages, $defaultLang)), true, 302);
    exit;
}

// 3. Taal en pagina bepalen.
$validLang = isset($languages[$parts[0]]);
$lang      = $validLang ? $parts[0] : $defaultLang;
$slug      = rawurldecode($parts[1] ?? '');
$strings   = loadStrings($pdo, $lang, $defaultLang);
$menu      = loadMenu($pdo, $lang);
$page      = ($validLang && count($parts) <= 2) ? findPage($pdo, $lang, $slug) : null;

// 4. Niet gevonden? Dan een 404-pagina in de juiste taal.
if ($page === null) {
    http_response_code(404);
    $page = [
        'id'       => 0,
        'page_key' => '404',
        'slug'     => '',
        'title'    => $strings['not_found_title'] ?? '404',
        'body'     => $strings['not_found_text'] ?? '',
    ];
    $alternates = [];
} else {
    $alternates = loadAlternates($pdo, (int) $page['id']);
}

// 5. Boekingsformulier verstuurd? Verwerken en daarna doorsturen,
//    zodat vernieuwen de aanvraag niet opnieuw verstuurt.
$booking      = emptyBooking();
$bookingError = null;
$bookingSent  = isset($_GET['sent']);
if ($page['page_key'] === 'booking' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    [$booking, $bookingError] = handleBooking($pdo, $lang);
    if ($bookingError === null) {
        header('Location: ' . url($lang, $page['slug']) . '?sent=1#aanvraag', true, 303);
        exit;
    }
}

// 6. Sjabloon kiezen: een eigen sjabloon als het bestaat, anders het standaardsjabloon.
$template = __DIR__ . '/templates/' . $page['page_key'] . '.php';
if (!preg_match('/^[a-z0-9_-]+$/', $page['page_key']) || !is_file($template)) {
    $template = __DIR__ . '/templates/page.php';
}

require __DIR__ . '/templates/header.php';
require $template;
require __DIR__ . '/templates/footer.php';
