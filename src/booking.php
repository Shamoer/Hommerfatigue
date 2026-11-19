<?php
// Het boekingsformulier verwerken: controleren, bewaren en per mail doorsturen.

const BOOKING_MAX_PER_HOUR = 3;   // per IP-adres
const BOOKING_MIN_SECONDS  = 4;   // sneller ingevuld = waarschijnlijk een bot

function emptyBooking(): array
{
    return ['name' => '', 'email' => '', 'organisation' => '', 'event_date' => '', 'venue' => '', 'message' => ''];
}

// Geeft [gegevens, foutsleutel]. Foutsleutel null = gelukt.
function handleBooking(PDO $pdo, string $lang): array
{
    $clean = fn(string $key, int $max) => mb_substr(trim(str_replace(["\r", "\0"], '', (string) ($_POST[$key] ?? ''))), 0, $max);

    $data = [
        'name'         => $clean('name', 100),
        'email'        => $clean('email', 190),
        'organisation' => $clean('organisation', 150),
        'event_date'   => $clean('event_date', 10),
        'venue'        => $clean('venue', 150),
        'message'      => mb_substr(trim(str_replace("\r\n", "\n", (string) ($_POST['message'] ?? ''))), 0, 5000),
    ];

    // Lokvogel: dit veld is onzichtbaar voor mensen, bots vullen het in. Doe alsof het gelukt is.
    if (trim((string) ($_POST['website'] ?? '')) !== '') {
        return [$data, null];
    }

    $started = (int) ($_POST['started'] ?? 0);
    if ($started > 0 && time() - $started < BOOKING_MIN_SECONDS) {
        return [$data, 'f_err_slow'];
    }

    $nameOneLine = str_replace("\n", ' ', $data['name']);
    if ($nameOneLine === '' || $data['message'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return [$data, 'f_err_required'];
    }

    $date = DateTime::createFromFormat('!Y-m-d', $data['event_date']);
    $eventDate = ($date && $date->format('Y-m-d') === $data['event_date']) ? $data['event_date'] : null;

    $ip   = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM booking_requests WHERE ip = ? AND created_at > (NOW() - INTERVAL 1 HOUR)');
    $stmt->execute([$ip]);
    if ((int) $stmt->fetchColumn() >= BOOKING_MAX_PER_HOUR) {
        return [$data, 'f_err_limit'];
    }

    $pdo->prepare(
        'INSERT INTO booking_requests (name, email, organisation, event_date, venue, message, lang_code, ip)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([$nameOneLine, $data['email'], $data['organisation'], $eventDate, $data['venue'], $data['message'], $lang, $ip]);

    sendBookingMail($data, $nameOneLine, $eventDate, $lang);
    return [$data, null];
}

function sendBookingMail(array $data, string $name, ?string $eventDate, string $lang): void
{
    $to = setting('BOOKING_EMAIL');
    if ($to === '' || DEBUG) {   // lokaal geen mail versturen
        return;
    }
    $body = "Nieuwe boekingsaanvraag via de site\n\n"
          . "Naam: {$name}\n"
          . "E-mail: {$data['email']}\n"
          . "Organisatie: {$data['organisation']}\n"
          . 'Datum: ' . ($eventDate ? formatDate($eventDate) : '-') . "\n"
          . "Locatie: {$data['venue']}\n"
          . 'Taal: ' . strtoupper($lang) . "\n\n"
          . $data['message'] . "\n";

    $headers = [
        'From'         => 'Homme R Fatigué <' . setting('MAIL_FROM', $to) . '>',
        'Reply-To'     => $data['email'],   // al gecontroleerd met FILTER_VALIDATE_EMAIL
        'Content-Type' => 'text/plain; charset=UTF-8',
    ];
    @mail($to, '=?UTF-8?B?' . base64_encode('Boekingsaanvraag: ' . $name) . '?=', $body, $headers);
}
