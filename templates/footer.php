</main>

<footer class="site-footer">
  <div class="site-footer__inner wrap">
    <a class="monogram" href="<?= e(url($lang)) ?>" aria-label="Homme R Fatigué">HR</a>
    <p class="label">Homme R Fatigué — <?= t('footer_location') ?></p>
    <ul class="site-footer__links label">
      <li><a href="<?= e(pageUrl($menu, $lang, 'booking')) ?>">Booking</a></li>
<?php if (setting('INSTAGRAM_URL') !== ''): ?>
      <li><a href="<?= e(setting('INSTAGRAM_URL')) ?>" target="_blank" rel="noopener">Instagram ↗</a></li>
<?php endif; ?>
<?php if (setting('BOOKING_EMAIL') !== ''): ?>
      <li><a href="mailto:<?= e(setting('BOOKING_EMAIL')) ?>"><?= e(setting('BOOKING_EMAIL')) ?></a></li>
<?php endif; ?>
    </ul>
    <p class="label">&copy; <?= date('Y') ?></p>
  </div>
</footer>
</body>
</html>
