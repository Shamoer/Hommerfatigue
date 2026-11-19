<?php
// Eenmalig: de eerste beheerder aanmaken. Werkt niet meer zodra er een gebruiker bestaat.
require __DIR__ . '/_bootstrap.php';
$pdo = db();

if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0) {
    redirect(adminUrl('login.php'));
}

$errors   = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $repeat   = (string) ($_POST['password_repeat'] ?? '');

    if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
        $errors[] = 'Gebruikersnaam: 3 tot 50 tekens, alleen letters, cijfers, punt, streepje of underscore.';
    }
    if (mb_strlen($password) < 12) {
        $errors[] = 'Wachtwoord: minstens 12 tekens.';
    }
    if ($password !== $repeat) {
        $errors[] = 'De twee wachtwoorden zijn niet gelijk.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        loginUser(['id' => (int) $pdo->lastInsertId(), 'username' => $username]);
        flash('Account aangemaakt. Welkom!');
        redirect(adminUrl());
    }
}

adminHeader('Eerste beheerder', null);
?>
<h1>Eerste beheerder aanmaken</h1>
<p class="hint">Dit kan maar één keer. Kies een sterk wachtwoord van minstens 12 tekens.</p>

<?php foreach ($errors as $error): ?>
  <p class="error"><?= e($error) ?></p>
<?php endforeach; ?>

<form method="post" class="form">
  <?= csrfField() ?>
  <label>Gebruikersnaam
    <input name="username" value="<?= e($username) ?>" autocomplete="username" required>
  </label>
  <label>Wachtwoord
    <input type="password" name="password" autocomplete="new-password" minlength="12" required>
  </label>
  <label>Wachtwoord herhalen
    <input type="password" name="password_repeat" autocomplete="new-password" minlength="12" required>
  </label>
  <button type="submit">Aanmaken</button>
</form>
<?php adminFooter();
