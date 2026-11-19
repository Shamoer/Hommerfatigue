<?php require __DIR__ . '/_page-hero.php'; ?>

<section class="wrap">
<?php if (trim($page['body'] ?? '') !== ''): ?>
  <div class="text text--page"><?= paragraphs($page['body']) ?></div>
<?php endif; ?>
<?php $sessions = loadMixes($pdo); $withEmbed = true; require __DIR__ . '/_sessions.php'; ?>
</section>

<?php require __DIR__ . '/_cta.php'; ?>
