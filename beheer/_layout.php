<?php
function adminHeader(string $title, ?array $user): void
{
    $flash = takeFlash();
    ?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($title) ?> — Beheer</title>
  <link rel="stylesheet" href="<?= e(adminUrl('admin.css')) ?>">
</head>
<body>
<header class="bar">
  <a class="brand" href="<?= e(adminUrl()) ?>">HRF / beheer</a>
<?php if ($user): ?>
  <nav class="adminnav" aria-label="Beheer">
<?php
    $sections = [
        ''              => ["Pagina's",  ['index.php', 'pagina.php']],
        'gigs.php'      => ['Gigs',      ['gigs.php', 'gig.php']],
        'sessies.php'   => ['Sessies',   ['sessies.php', 'sessie.php']],
        'rider.php'     => ['Rider',     ['rider.php']],
        'aanvragen.php' => ['Aanvragen', ['aanvragen.php']],
    ];
    $script = basename($_SERVER['SCRIPT_NAME']);
    foreach ($sections as $file => [$label, $files]): ?>
    <a href="<?= e(adminUrl($file)) ?>"<?= in_array($script, $files, true) ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
<?php endforeach; ?>
  </nav>
  <form method="post" action="<?= e(adminUrl('logout.php')) ?>">
    <?= csrfField() ?>
    <button class="link" type="submit">Uitloggen</button>
  </form>
<?php endif; ?>
</header>
<main class="wrap">
<?php if ($flash): ?>
  <p class="flash"><?= e($flash) ?></p>
<?php endif; ?>
    <?php
}

function adminFooter(): void
{
    ?>
</main>
</body>
</html>
    <?php
}
