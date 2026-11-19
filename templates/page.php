<?php require __DIR__ . '/_page-hero.php'; ?>

<?php if (trim($page['body'] ?? '') !== ''): ?>
<section class="wrap">
  <div class="text text--page"><?= paragraphs($page['body']) ?></div>
</section>
<?php endif; ?>

<?php if ($page['page_key'] !== '404') require __DIR__ . '/_cta.php'; ?>
