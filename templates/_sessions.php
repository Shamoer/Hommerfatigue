<?php /* Toont $sessions als zwarte tegels. Met $withEmbed = true ook de speler. */ ?>
<?php $withEmbed = $withEmbed ?? false; ?>
<?php if (!$sessions): ?>
  <p class="label"><?= t('embed_soon') ?></p>
<?php else: ?>
  <ul class="sessions<?= $withEmbed ? ' sessions--full' : '' ?>">
<?php foreach ($sessions as $mix): ?>
    <li class="session">
      <div class="session__cover" aria-hidden="true">
<?php if ($mix['cover'] !== ''): ?>
        <img src="<?= e(asset($mix['cover'])) ?>" alt="" loading="lazy" width="160" height="160">
<?php else: ?>
        <span>Homme R<br>Fatigué</span>
<?php endif; ?>
      </div>
      <div class="session__text">
        <strong class="session__title"><?= e($mix['title']) ?></strong>
        <span class="label"><?= e(implode(' // ', array_filter([$mix['subtitle'], formatDate($mix['published_at'])]))) ?></span>
<?php if ($mix['link_url'] !== ''): ?>
        <a class="session__link label" href="<?= e($mix['link_url']) ?>" target="_blank" rel="noopener"><?= t('listen') ?> ↗</a>
<?php endif; ?>
      </div>
<?php if ($withEmbed): ?>
      <div class="session__player">
<?php if ($mix['embed_url'] !== ''): ?>
        <iframe src="<?= e($mix['embed_url']) ?>" title="<?= e($mix['title']) ?>" loading="lazy" allow="autoplay"></iframe>
<?php else: ?>
        <p class="label"><?= t('embed_soon') ?></p>
<?php endif; ?>
      </div>
<?php endif; ?>
    </li>
<?php endforeach; ?>
  </ul>
<?php endif; ?>
