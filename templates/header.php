<?php
$cssFile = __DIR__ . '/../css/main.css';
$cssUrl  = asset('css/main.css') . (is_file($cssFile) ? '?v=' . filemtime($cssFile) : '');
$isHome  = $page['page_key'] === 'home';
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($isHome ? 'Homme R Fatigué' : $page['title'] . ' — Homme R Fatigué') ?></title>
  <meta name="description" content="<?= t('tagline') ?>">
  <meta name="theme-color" content="#f1efe9">
<?php foreach ($alternates as $code => $altSlug): ?>
  <link rel="alternate" hreflang="<?= e($code) ?>" href="<?= e(url($code, $altSlug)) ?>">
<?php endforeach; ?>
  <link rel="preload" href="<?= e(asset('fonts/chakra-petch-latin-700-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="<?= e(asset('fonts/ibm-plex-mono-latin-400-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="<?= e($cssUrl) ?>">
</head>
<body class="page-<?= e($page['page_key']) ?>">
<a class="skip-link" href="#inhoud"><?= t('skip_link') ?></a>

<header class="site-header">
  <div class="site-header__inner wrap">
    <a href="<?= e(url($lang)) ?>" class="wordmark"<?= $isHome ? ' aria-current="page"' : '' ?>>Homme R Fatigué</a>

    <nav class="main-nav" aria-label="Menu">
      <ul>
<?php foreach ($menu as $item): if ($item['page_key'] === 'home') continue; ?>
        <li><a href="<?= e(url($lang, $item['slug'])) ?>"<?= $item['page_key'] === $page['page_key'] ? ' aria-current="page"' : '' ?>><?= e($item['menu_label']) ?></a></li>
<?php endforeach; ?>
      </ul>
    </nav>

    <ul class="lang-switch">
<?php foreach ($languages as $code => $language): ?>
      <li><a href="<?= e(url($code, $alternates[$code] ?? '')) ?>" hreflang="<?= e($code) ?>" lang="<?= e($code) ?>" title="<?= e($language['name']) ?>"<?= $code === $lang ? ' aria-current="true"' : '' ?>><?= e(strtoupper($code)) ?></a></li>
<?php endforeach; ?>
    </ul>
  </div>
</header>

<main id="inhoud">
