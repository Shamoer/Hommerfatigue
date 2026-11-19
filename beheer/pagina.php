<?php
require __DIR__ . '/_bootstrap.php';
$user = requireLogin();
$pdo  = db();

$languages = loadLanguages($pdo);
$pageId    = (int) ($_GET['id'] ?? 0);
$lang      = (string) ($_GET['lang'] ?? '');

$stmt = $pdo->prepare('SELECT id, page_key FROM pages WHERE id = ?');
$stmt->execute([$pageId]);
$page = $stmt->fetch();

if (!$page || !isset($languages[$lang])) {
    flash('Die pagina of taal bestaat niet.');
    redirect(adminUrl());
}

$isHome = $page['page_key'] === 'home';

// Huidige vertaling ophalen (kan nog leeg zijn).
$stmt = $pdo->prepare('SELECT slug, menu_label, title, body FROM page_translations WHERE page_id = ? AND lang_code = ?');
$stmt->execute([$pageId, $lang]);
$data   = $stmt->fetch() ?: ['slug' => '', 'menu_label' => '', 'title' => '', 'body' => ''];
$exists = $stmt->rowCount() > 0;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $data = [
        'title'      => trim((string) ($_POST['title'] ?? '')),
        'menu_label' => trim((string) ($_POST['menu_label'] ?? '')),
        'slug'       => $isHome ? '' : slugify((string) ($_POST['slug'] ?? '')),
        'body'       => str_replace("\r\n", "\n", (string) ($_POST['body'] ?? '')),
    ];

    if ($data['title'] === '')      { $errors[] = 'Vul een titel in.'; }
    if ($data['menu_label'] === '') { $data['menu_label'] = $data['title']; }
    if (!$isHome && $data['slug'] === '') {
        $data['slug'] = slugify($data['title']);
    }
    if (!$isHome && $data['slug'] === '') {
        $errors[] = 'Het adres mag niet leeg zijn.';
    }
    if (mb_strlen($data['title']) > 200 || mb_strlen($data['menu_label']) > 100 || strlen($data['slug']) > 100) {
        $errors[] = 'Titel, menunaam of adres is te lang.';
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO page_translations (page_id, lang_code, slug, menu_label, title, body)
                 VALUES (:page_id, :lang, :slug, :menu_label, :title, :body)
                 ON DUPLICATE KEY UPDATE slug = VALUES(slug), menu_label = VALUES(menu_label),
                                         title = VALUES(title), body = VALUES(body)'
            );
            $stmt->execute([
                'page_id'    => $pageId,
                'lang'       => $lang,
                'slug'       => $data['slug'],
                'menu_label' => $data['menu_label'],
                'title'      => $data['title'],
                'body'       => $data['body'],
            ]);
            flash('Opgeslagen.');
            redirect(adminUrl('pagina.php', ['id' => $pageId, 'lang' => $lang]));
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $errors[] = 'Het adres "' . $data['slug'] . '" wordt in deze taal al door een andere pagina gebruikt.';
            } else {
                throw $ex;
            }
        }
    }
}

adminHeader($data['title'] ?: 'Pagina', $user);
?>
<p><a href="<?= e(adminUrl()) ?>">← Alle pagina's</a></p>
<h1><?= e($data['title'] ?: $page['page_key']) ?></h1>

<nav class="tabs">
<?php foreach ($languages as $code => $language): ?>
  <a href="<?= e(adminUrl('pagina.php', ['id' => $pageId, 'lang' => $code])) ?>"<?= $code === $lang ? ' class="active"' : '' ?>>
    <?= e($language['name']) ?>
  </a>
<?php endforeach; ?>
</nav>

<?php if (!$exists && !$errors): ?>
  <p class="hint">Deze pagina is nog niet vertaald in het <?= e($languages[$lang]['name']) ?>. Vul de velden in en sla op.</p>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
  <p class="error"><?= e($error) ?></p>
<?php endforeach; ?>

<form method="post" class="form">
  <?= csrfField() ?>

  <label>Titel
    <input name="title" value="<?= e($data['title']) ?>" maxlength="200" required>
  </label>

  <label>Naam in het menu
    <input name="menu_label" value="<?= e($data['menu_label']) ?>" maxlength="100">
    <small>Leeg laten = zelfde als de titel.</small>
  </label>

<?php if (!$isHome): ?>
  <label>Adres
    <span class="prefix"><?= e(url($lang)) ?><input name="slug" value="<?= e($data['slug']) ?>" maxlength="100" autocapitalize="none"></span>
    <small>Alleen kleine letters, cijfers en streepjes. Leeg laten = gemaakt uit de titel.</small>
  </label>
<?php endif; ?>

  <label>Tekst
    <textarea name="body" rows="14"><?= e($data['body']) ?></textarea>
    <small>Lege regel = nieuwe alinea. Begin een regel met <code>## </code> voor een tussentitel.</small>
  </label>

  <div class="actions">
    <button type="submit">Opslaan</button>
<?php if ($exists): ?>
    <a href="<?= e(url($lang, $data['slug'])) ?>" target="_blank" rel="noopener">Bekijk pagina ↗</a>
<?php endif; ?>
  </div>
</form>
<?php adminFooter();
