<?php require __DIR__ . '/_page-hero.php'; ?>

<div class="epk-grid wrap">
  <div class="epk-col">
<?php if (trim($page['body'] ?? '') !== ''): ?>
    <div class="text epk-block"><?= paragraphs($page['body']) ?></div>
<?php endif; ?>
    <section class="epk-block">
      <h2 class="heading"><?= t('upcoming') ?></h2>
<?php $gigs = loadGigs($pdo, $lang, true); require __DIR__ . '/_gigs.php'; ?>
    </section>

<?php $gigs = loadGigs($pdo, $lang, false, 20); if ($gigs): ?>
    <section class="epk-block epk-block--muted">
      <h2 class="heading"><?= t('past_gigs') ?></h2>
<?php require __DIR__ . '/_gigs.php'; ?>
    </section>
<?php endif; ?>
  </div>

  <div class="epk-col">
<?php require __DIR__ . '/_map.php'; ?>
  </div>
</div>

<?php require __DIR__ . '/_cta.php'; ?>
