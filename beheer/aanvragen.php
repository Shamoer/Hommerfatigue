<?php
require __DIR__ . '/_bootstrap.php';
$user = requireLogin();
$pdo  = db();

// Aanvraag als behandeld markeren (of terugzetten).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $pdo->prepare('UPDATE booking_requests SET handled = 1 - handled WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
    redirect(adminUrl('aanvragen.php'));
}

$requests = $pdo->query('SELECT * FROM booking_requests ORDER BY handled ASC, created_at DESC LIMIT 200')->fetchAll();

adminHeader('Boekingsaanvragen', $user);
?>
<p><a href="<?= e(adminUrl()) ?>">← Overzicht</a></p>
<h1>Boekingsaanvragen</h1>

<?php if (!$requests): ?>
  <p class="hint">Nog geen aanvragen.</p>
<?php endif; ?>

<ul class="cards">
<?php foreach ($requests as $r): ?>
  <li class="card<?= $r['handled'] ? ' missing' : '' ?>">
    <strong><?= e($r['name']) ?></strong>
    <?php if ($r['organisation'] !== ''): ?> — <?= e($r['organisation']) ?><?php endif; ?>
    <div class="hint">
      <a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a>
      · ontvangen <?= e(date('d.m.Y H:i', strtotime($r['created_at']))) ?>
      · <?= e(strtoupper($r['lang_code'])) ?>
      <?php if ($r['event_date']): ?>· event <?= e(formatDate($r['event_date'])) ?><?php endif; ?>
      <?php if ($r['venue'] !== ''): ?>· <?= e($r['venue']) ?><?php endif; ?>
    </div>
    <p><?= nl2br(e($r['message'])) ?></p>
    <form method="post">
      <?= csrfField() ?>
      <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
      <button class="link" type="submit"><?= $r['handled'] ? 'Terug op open zetten' : 'Markeer als behandeld' ?></button>
    </form>
  </li>
<?php endforeach; ?>
</ul>
<?php adminFooter();
