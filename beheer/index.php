<?php
require __DIR__ . '/_bootstrap.php';
$user = requireLogin();
$pdo  = db();

$languages = loadLanguages($pdo);
$default   = defaultLanguage($languages);

// Alle pagina's, met per taal of er al een vertaling is.
$rows = $pdo->query(
    'SELECT p.id, p.page_key, pt.lang_code, pt.title, pt.updated_at
       FROM pages p
       LEFT JOIN page_translations pt ON pt.page_id = p.id
      ORDER BY p.sort_order, p.id'
)->fetchAll();

$pages = [];
foreach ($rows as $row) {
    $id = $row['id'];
    $pages[$id] ??= ['id' => $id, 'page_key' => $row['page_key'], 'titles' => []];
    if ($row['lang_code'] !== null) {
        $pages[$id]['titles'][$row['lang_code']] = $row['title'];
    }
}

adminHeader('Overzicht', $user);
?>
<?php $open = (int) $pdo->query('SELECT COUNT(*) FROM booking_requests WHERE handled = 0')->fetchColumn(); ?>
<p><a href="<?= e(adminUrl('aanvragen.php')) ?>">Boekingsaanvragen<?= $open ? ' (' . $open . ' nieuw)' : '' ?> →</a></p>

<h1>Pagina's</h1>
<p class="hint">Kies een pagina en een taal om de tekst aan te passen.
  <a href="<?= e(url($default)) ?>" target="_blank" rel="noopener">Bekijk de site ↗</a></p>

<ul class="cards">
<?php foreach ($pages as $page): ?>
  <li class="card">
    <strong><?= e($page['titles'][$default] ?? $page['page_key']) ?></strong>
    <div class="langs">
<?php foreach ($languages as $code => $language): ?>
      <a class="pill<?= isset($page['titles'][$code]) ? '' : ' missing' ?>"
         href="<?= e(adminUrl('pagina.php', ['id' => $page['id'], 'lang' => $code])) ?>"
         title="<?= e($language['name']) ?><?= isset($page['titles'][$code]) ? '' : ' (nog niet vertaald)' ?>">
        <?= e(strtoupper($code)) ?>
      </a>
<?php endforeach; ?>
    </div>
  </li>
<?php endforeach; ?>
</ul>
<?php adminFooter();
