<?php
require __DIR__ . '/_bootstrap.php';
$user = requireLogin();
$pdo  = db();

$sessions = $pdo->query('SELECT id, title, subtitle, cover, published_at, active, embed_url FROM mixes ORDER BY published_at DESC, id DESC')->fetchAll();

adminHeader('Sessies', $user);
?>
<div class="titlebar">
  <h1>Sessies</h1>
  <a class="button" href="<?= e(adminUrl('sessie.php')) ?>">+ Nieuwe sessie</a>
</div>

<?php if (!$sessions): ?>
  <p class="hint">Nog geen sessies.</p>
<?php endif; ?>

<ul class="cards">
<?php foreach ($sessions as $s): ?>
  <li>
    <a class="card card--link card--media<?= $s['active'] ? '' : ' card--past' ?>" href="<?= e(adminUrl('sessie.php', ['id' => $s['id']])) ?>">
<?php if ($s['cover'] !== ''): ?>
      <img class="thumb" src="<?= e(asset($s['cover'])) ?>" alt="" width="56" height="56">
<?php else: ?>
      <span class="thumb thumb--empty">HR</span>
<?php endif; ?>
      <span>
        <strong><?= e($s['title']) ?></strong><br>
        <span class="hint"><?= e(formatDate($s['published_at'])) ?><?= $s['subtitle'] !== '' ? ' · ' . e($s['subtitle']) : '' ?><?= $s['active'] ? '' : ' · verborgen' ?><?= $s['embed_url'] === '' ? ' · geen speler' : '' ?></span>
      </span>
    </a>
  </li>
<?php endforeach; ?>
</ul>
<?php adminFooter();
