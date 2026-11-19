<?php
require __DIR__ . '/_bootstrap.php';
$pdo = db();

if (currentUser()) {
    redirect(adminUrl());
}
if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0) {
    redirect(adminUrl('setup.php'));
}

const MAX_ATTEMPTS   = 5;   // mislukte pogingen...
const WINDOW_MINUTES = 15;  // ...binnen dit aantal minuten

$ip       = $_SERVER['REMOTE_ADDR'] ?? '';
$error    = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $username = trim((string) ($_POST['username'] ?? ''));

    // Oude pogingen opruimen
    $pdo->exec('DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)');

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > (NOW() - INTERVAL ' . WINDOW_MINUTES . ' MINUTE)');
    $stmt->execute([$ip]);

    if ((int) $stmt->fetchColumn() >= MAX_ATTEMPTS) {
        $error = 'Te veel mislukte pogingen. Probeer het over ' . WINDOW_MINUTES . ' minuten opnieuw.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify((string) ($_POST['password'] ?? ''), $user['password_hash'])) {
            $pdo->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
            $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                    ->execute([password_hash((string) $_POST['password'], PASSWORD_DEFAULT), $user['id']]);
            }
            loginUser($user);
            redirect(adminUrl());
        }

        $pdo->prepare('INSERT INTO login_attempts (ip, attempted_at) VALUES (?, NOW())')->execute([$ip]);
        $error = 'Gebruikersnaam of wachtwoord klopt niet.';
    }
}

adminHeader('Inloggen', null);
?>
<h1>Inloggen</h1>

<?php if ($error): ?>
  <p class="error"><?= e($error) ?></p>
<?php endif; ?>

<form method="post" class="form">
  <?= csrfField() ?>
  <label>Gebruikersnaam
    <input name="username" value="<?= e($username) ?>" autocomplete="username" autocapitalize="none" required>
  </label>
  <label>Wachtwoord
    <input type="password" name="password" autocomplete="current-password" required>
  </label>
  <button type="submit">Inloggen</button>
</form>
<?php adminFooter();
