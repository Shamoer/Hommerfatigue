<?php
// Hulpfuncties voor sessies: speler-links en artwork.

const COVER_DIR      = 'img/sessies';   // t.o.v. de hoofdmap van de site
const COVER_SIZE     = 800;             // artwork wordt een vierkant van 800 × 800
const COVER_MAX_MB   = 8;

// Maakt van een Mixcloud- of SoundCloud-link (of de volledige <iframe>-code) een speler-adres.
// Geeft [adres, foutmelding]. Leeg veld = geen speler, geen fout.
function normalizeEmbed(string $input): array
{
    $input = trim($input);
    if ($input === '') {
        return ['', null];
    }
    if (preg_match('/src=["\']([^"\']+)["\']/i', $input, $m)) {   // volledige iframe-code geplakt
        $input = html_entity_decode($m[1]);
    }
    $parts = parse_url($input);
    $host  = strtolower($parts['host'] ?? '');
    $path  = $parts['path'] ?? '';

    if (($parts['scheme'] ?? '') !== 'https') {
        return ['', 'De speler-link moet met https:// beginnen.'];
    }

    // Al een speler-adres? Dan zo laten.
    if ($host === 'www.mixcloud.com' && str_starts_with($path, '/widget/iframe/')) {
        return [$input, null];
    }
    if ($host === 'w.soundcloud.com' && str_starts_with($path, '/player/')) {
        return [$input, null];
    }

    // Gewone pagina-link omzetten naar de speler.
    if (in_array($host, ['mixcloud.com', 'www.mixcloud.com', 'm.mixcloud.com'], true) && substr_count(trim($path, '/'), '/') === 1) {
        return ['https://www.mixcloud.com/widget/iframe/?hide_cover=1&light=1&feed=' . rawurlencode('/' . trim($path, '/') . '/'), null];
    }
    if (in_array($host, ['soundcloud.com', 'www.soundcloud.com', 'm.soundcloud.com'], true) && trim($path, '/') !== '') {
        $track = 'https://soundcloud.com/' . trim($path, '/');
        return ['https://w.soundcloud.com/player/?url=' . rawurlencode($track) . '&color=%23111111&auto_play=false&visual=false&show_comments=false', null];
    }

    return ['', 'Die speler-link wordt niet herkend. Plak een link van Mixcloud of SoundCloud.'];
}

// Gewone link (bv. "beluisteren op Bandcamp"): moet http(s) zijn.
function validLink(string $input): ?string
{
    $input = trim($input);
    if ($input === '') {
        return '';
    }
    return (filter_var($input, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $input)) ? $input : null;
}

// Geüpload artwork controleren, vierkant bijsnijden, verkleinen en als JPG bewaren.
// Het beeld wordt volledig opnieuw getekend, zodat er nooit verborgen code in het bestand achterblijft.
// Geeft [pad, foutmelding]. Geen bestand gekozen = ['', null].
function saveCover(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['', null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return ['', 'Het uploaden is mislukt. Is het bestand misschien groter dan ' . COVER_MAX_MB . ' MB?'];
    }
    if ($file['size'] > COVER_MAX_MB * 1024 * 1024) {
        return ['', 'Het artwork mag maximaal ' . COVER_MAX_MB . ' MB zijn.'];
    }

    $info = @getimagesize($file['tmp_name']);
    $type = $info[2] ?? 0;
    $source = match ($type) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($file['tmp_name']),
        IMAGETYPE_PNG  => @imagecreatefrompng($file['tmp_name']),
        IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file['tmp_name']) : false,
        default        => false,
    };
    if (!$source) {
        return ['', 'Kies een afbeelding in JPG, PNG of WebP.'];
    }

    // Foto van een gsm die gedraaid is opgeslagen? Rechtzetten.
    if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $orientation = (int) (@exif_read_data($file['tmp_name'])['Orientation'] ?? 1);
        $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;
        if ($angle) {
            $source = imagerotate($source, $angle, 0);
        }
    }

    $w = imagesx($source);
    $h = imagesy($source);
    $side = min($w, $h);
    $size = min(COVER_SIZE, $side);

    $target = imagecreatetruecolor($size, $size);
    imagefill($target, 0, 0, imagecolorallocate($target, 17, 17, 17));   // transparante PNG: zwarte achtergrond
    imagecopyresampled($target, $source, 0, 0, intdiv($w - $side, 2), intdiv($h - $side, 2), $size, $size, $side, $side);

    $dir = dirname(__DIR__) . '/' . COVER_DIR;
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return ['', 'De map ' . COVER_DIR . ' kon niet aangemaakt worden.'];
    }
    $name = bin2hex(random_bytes(8)) . '.jpg';
    if (!imagejpeg($target, $dir . '/' . $name, 85)) {
        return ['', 'Het artwork kon niet bewaard worden. Controleer de schrijfrechten van ' . COVER_DIR . '.'];
    }
    return [COVER_DIR . '/' . $name, null];
}

// Oud artwork van de server verwijderen (alleen bestanden die de CMS zelf maakte).
function deleteCover(string $path): void
{
    if (preg_match('#^' . preg_quote(COVER_DIR, '#') . '/[a-f0-9]{16}\.jpg$#', $path)) {
        @unlink(dirname(__DIR__) . '/' . $path);
    }
}
