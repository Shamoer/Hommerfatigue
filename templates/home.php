<?php
$gigs     = loadGigs($pdo, $lang, true, 4);
$sessions = loadMixes($pdo, 3);
$rider    = loadRider($pdo);
$email    = setting('BOOKING_EMAIL');
?>
<section class="epk-hero wrap">
  <div class="epk-hero__text">
    <p class="label">Electronic press kit //</p>
    <h1 class="display">Homme R<br>Fatigué</h1>
    <p class="label epk-hero__tagline"><?= t('tagline') ?></p>
  </div>
  <figure class="portrait">
    <span class="shape shape--ring" aria-hidden="true"></span>
    <span class="shape shape--stripes" aria-hidden="true"></span>
    <span class="shape shape--grain" aria-hidden="true"></span>
    <img src="<?= e(asset('img/portret.jpg')) ?>" alt="Homme R Fatigué" width="512" height="512">
  </figure>
</section>

<div class="epk-grid wrap">
  <div class="epk-col">
    <section class="epk-block">
      <h2 class="heading"><?= t('bio') ?></h2>
      <div class="text"><?= paragraphs($page['body']) ?></div>
    </section>

<?php if ($rider): ?>
    <section class="epk-block">
      <h2 class="heading"><?= t('rider') ?></h2>
<?php require __DIR__ . '/_rider.php'; ?>
    </section>
<?php endif; ?>

    <section class="epk-block">
      <h2 class="heading"><?= t('latest_sessions') ?></h2>
<?php require __DIR__ . '/_sessions.php'; ?>
      <p class="more label"><a href="<?= e(pageUrl($menu, $lang, 'mixes')) ?>"><?= t('all_sessions') ?> →</a></p>
    </section>
  </div>

  <div class="epk-col">
    <section class="epk-block">
      <h2 class="heading"><?= t('booking_info') ?></h2>
<?php if ($email !== ''): ?>
      <p class="label"><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></p>
<?php endif; ?>
      <a class="button" href="<?= e(pageUrl($menu, $lang, 'booking')) ?>"><?= t('book_button') ?> →</a>
    </section>

    <section class="epk-block">
      <h2 class="heading"><?= t('upcoming') ?></h2>
<?php require __DIR__ . '/_gigs.php'; ?>
<?php require __DIR__ . '/_map.php'; ?>
      <p class="more label"><a href="<?= e(pageUrl($menu, $lang, 'agenda')) ?>"><?= t('full_agenda') ?> →</a></p>
    </section>
  </div>
</div>
