<?php
require __DIR__ . '/_bootstrap.php';
$user = requireLogin();
$pdo  = db();

$gigs = $pdo->query(
    'SELECT id, gig_date, start_time, event_name, venue, city, gig_date >= CURDATE() AS upcoming
       FROM gigs ORDER BY gig_date >= CURDATE() DESC,
                CASE WHEN gig_date >= CURDATE() THEN gig_date END ASC,
                gig_date DESC'
)->fetchAll();

adminHeader('Gigs', $user);
?>
<div class="titlebar">
  <h1>Gigs</h1>
  <a class="button" href="<?= e(adminUrl('gig.php')) ?>">+ Nieuwe gig</a>
</div>

<?php if (!$gigs): ?>
  <p class="hint">Nog geen gigs.</p>
<?php endif; ?>

<?php $shownPast = false; ?>
<ul class="cards">
<?php foreach ($gigs as $gig): ?>
<?php if (!$gig['upcoming'] && !$shownPast): $shownPast = true; ?>
</ul>
<h2 class="subhead">Voorbij</h2>
<ul class="cards">
<?php endif; ?>
  <li>
    <a class="card card--link card--stack<?= $gig['upcoming'] ? '' : ' card--past' ?>" href="<?= e(adminUrl('gig.php', ['id' => $gig['id']])) ?>">
      <span class="date"><?= e(formatDate($gig['gig_date'])) ?><?= $gig['start_time'] ? ' · ' . e(substr($gig['start_time'], 0, 5)) : '' ?></span>
      <strong><?= e($gig['event_name'] !== '' ? $gig['event_name'] . ' — ' : '') ?><?= e($gig['venue']) ?></strong>
      <span class="hint"><?= e($gig['city']) ?></span>
    </a>
  </li>
<?php endforeach; ?>
</ul>
<?php adminFooter();
