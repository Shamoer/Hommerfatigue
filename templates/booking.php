<?php
$rider = loadRider($pdo);
$email = setting('BOOKING_EMAIL');
$err   = fn(string $field) => $bookingError === 'f_err_required' && in_array($field, ['name', 'email', 'message'], true);
?>
<?php require __DIR__ . '/_page-hero.php'; ?>

<div class="epk-grid epk-grid--booking wrap">
  <section class="epk-col epk-block" id="aanvraag">
    <h2 class="heading"><?= t('form_title') ?></h2>
<?php if (trim($page['body'] ?? '') !== ''): ?>
    <div class="text"><?= paragraphs($page['body']) ?></div>
<?php endif; ?>

<?php if ($bookingSent): ?>
    <p class="notice notice--ok" role="status"><?= t('f_sent') ?></p>
<?php else: ?>
<?php if ($bookingError): ?>
    <p class="notice notice--error" role="alert"><?= t($bookingError) ?></p>
<?php endif; ?>
    <form class="form" method="post" action="<?= e(url($lang, $page['slug'])) ?>#aanvraag">
      <input type="hidden" name="started" value="<?= time() ?>">
      <div class="form__trap" aria-hidden="true">
        <label>Website <input name="website" tabindex="-1" autocomplete="off"></label>
      </div>

      <label class="field">
        <span class="label"><?= t('f_name') ?> *</span>
        <input name="name" value="<?= e($booking['name']) ?>" maxlength="100" autocomplete="name" required<?= $err('name') ? ' aria-invalid="true"' : '' ?>>
      </label>
      <label class="field">
        <span class="label"><?= t('f_email') ?> *</span>
        <input type="email" name="email" value="<?= e($booking['email']) ?>" maxlength="190" autocomplete="email" required<?= $err('email') ? ' aria-invalid="true"' : '' ?>>
      </label>
      <label class="field">
        <span class="label"><?= t('f_organisation') ?> <em>(<?= t('f_optional') ?>)</em></span>
        <input name="organisation" value="<?= e($booking['organisation']) ?>" maxlength="150" autocomplete="organization">
      </label>
      <div class="form__row">
        <label class="field">
          <span class="label"><?= t('f_date') ?> <em>(<?= t('f_optional') ?>)</em></span>
          <input type="date" name="event_date" value="<?= e($booking['event_date']) ?>" min="<?= date('Y-m-d') ?>">
        </label>
        <label class="field">
          <span class="label"><?= t('f_venue') ?> <em>(<?= t('f_optional') ?>)</em></span>
          <input name="venue" value="<?= e($booking['venue']) ?>" maxlength="150">
        </label>
      </div>
      <label class="field">
        <span class="label"><?= t('f_message') ?> *</span>
        <textarea name="message" rows="6" maxlength="5000" required<?= $err('message') ? ' aria-invalid="true"' : '' ?>><?= e($booking['message']) ?></textarea>
      </label>
      <button class="button" type="submit"><?= t('f_send') ?> →</button>
    </form>
<?php endif; ?>
  </section>

  <aside class="epk-col">
    <section class="epk-block">
      <h2 class="heading"><?= t('booking_info') ?></h2>
<?php if ($email !== ''): ?>
      <p class="label"><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></p>
<?php endif; ?>
    </section>
<?php if ($rider): ?>
    <section class="epk-block">
      <h2 class="heading"><?= t('rider') ?></h2>
<?php require __DIR__ . '/_rider.php'; ?>
    </section>
<?php endif; ?>
  </aside>
</div>
