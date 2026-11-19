<?php /* Toont de lijst in $gigs, zoals de "upcoming dates" op de EPK */ ?>
<?php if (!$gigs): ?>
  <p class="label"><?= t('no_gigs') ?></p>
<?php else: ?>
  <ol class="gigs">
<?php foreach ($gigs as $gig):
    $parts = array_filter([shortTime($gig['start_time']), $gig['event_name'], $gig['venue'] . ($gig['city'] !== '' ? ', ' . $gig['city'] : '')], fn($p) => $p !== '');
?>
    <li class="gig">
      <time class="gig__when" datetime="<?= e($gig['gig_date']) ?>">
        <span class="gig__day"><?= e(dayName($gig['gig_date'], $lang)) ?></span>
        <span class="gig__date"><?= e(shortDate($gig['gig_date'])) ?></span>
      </time>
      <span class="gig__what">
        <?= implode(' <span class="sep">//</span> ', array_map('e', $parts)) ?>
<?php if ($gig['info'] !== ''): ?>
        <small><?= e($gig['info']) ?></small>
<?php endif; ?>
      </span>
    </li>
<?php endforeach; ?>
  </ol>
<?php endif; ?>
