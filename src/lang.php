<?php
// Alle actieve talen, in de volgorde van het menu.
function loadLanguages(PDO $pdo): array
{
    $languages = [];
    foreach ($pdo->query('SELECT code, name, is_default FROM languages WHERE active = 1 ORDER BY sort_order') as $row) {
        $languages[$row['code']] = $row;
    }
    return $languages;
}

function defaultLanguage(array $languages): string
{
    foreach ($languages as $code => $language) {
        if ($language['is_default']) {
            return $code;
        }
    }
    return array_key_first($languages);
}

// Welke taal staat ingesteld in de browser van de bezoeker?
function browserLanguage(array $languages, string $fallback): string
{
    foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '') as $part) {
        $code = strtolower(substr(trim($part), 0, 2));
        if (isset($languages[$code])) {
            return $code;
        }
    }
    return $fallback;
}

// Interfaceteksten; ontbreekt een vertaling, dan valt hij terug op de standaardtaal.
function loadStrings(PDO $pdo, string $lang, string $default): array
{
    $stmt = $pdo->prepare('SELECT string_key, lang_code, value FROM ui_strings WHERE lang_code IN (?, ?)');
    $stmt->execute([$lang, $default]);
    $strings = [];
    foreach ($stmt as $row) {
        if ($row['lang_code'] === $lang || !isset($strings[$row['string_key']])) {
            $strings[$row['string_key']] = $row['value'];
        }
    }
    return $strings;
}
