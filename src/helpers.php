<?php
// Tekst veilig tonen (beschermt tegen ingevoegde HTML/JavaScript).
function e(?string $text): string
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

// Vaste interfacetekst in de huidige taal, bv. t('latest_mix').
function t(string $key): string
{
    return e($GLOBALS['strings'][$key] ?? $key);
}

// Adres van een pagina: url('fr', 'a-propos') -> /hommerfatigue/fr/a-propos
function url(string $lang, string $slug = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . rawurlencode($lang) . '/' . ($slug !== '' ? rawurlencode($slug) : '');
}

// Adres van een bestand zoals css of afbeeldingen.
function asset(string $path): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

// Adres van een pagina op basis van haar vaste sleutel, bv. 'booking'.
function pageUrl(array $menu, string $lang, string $key): string
{
    foreach ($menu as $item) {
        if ($item['page_key'] === $key) {
            return url($lang, $item['slug']);
        }
    }
    return url($lang);
}

// Tekst uit de CMS omzetten naar HTML.
// Lege regel = nieuwe alinea, regel die begint met "## " = tussentitel.
function paragraphs(?string $text): string
{
    $html = '';
    foreach (preg_split('/\R\s*\R/', trim($text ?? '')) as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        if (str_starts_with($block, '## ')) {
            $html .= '<h2>' . e(substr($block, 3)) . "</h2>\n";
        } else {
            $html .= '<p>' . nl2br(e($block)) . "</p>\n";
        }
    }
    return $html;
}

function formatDate(string $date): string
{
    return date('d.m.Y', strtotime($date));
}

// Korte datum zoals op de EPK: "16.10" (jaartal erbij als het niet dit jaar is).
function shortDate(string $date): string
{
    $ts = strtotime($date);
    return date('Y', $ts) === date('Y') ? date('d.m', $ts) : date('d.m.y', $ts);
}

// Afgekorte weekdag in de taal van de pagina: "ZA", "SAT", "SAM".
function dayName(string $date, string $lang): string
{
    $days = [
        'nl' => ['ZO', 'MA', 'DI', 'WO', 'DO', 'VR', 'ZA'],
        'en' => ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'],
        'fr' => ['DIM', 'LUN', 'MAR', 'MER', 'JEU', 'VEN', 'SAM'],
    ];
    return ($days[$lang] ?? $days['en'])[(int) date('w', strtotime($date))];
}

// Uur zoals "20.00", of leeg als er geen uur is ingevuld.
function shortTime(?string $time): string
{
    return $time ? str_replace(':', '.', substr($time, 0, 5)) : '';
}

// Een instelling uit config.php, of een standaardwaarde als ze (nog) niet bestaat.
function setting(string $name, string $default = ''): string
{
    return defined($name) ? (string) constant($name) : $default;
}
