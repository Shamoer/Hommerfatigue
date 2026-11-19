<?php
require __DIR__ . '/_bootstrap.php';
require __DIR__ . '/_media.php';
$user = requireLogin();
$pdo  = db();

$id     = (int) ($_GET['id'] ?? 0);
$errors = [];
$mix    = ['title' => '', 'subtitle' => '', 'embed_url' => '', 'link_url' => '', 'cover' => '', 'published_at' => date('Y-m-d'), 'active' => 1];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT title, subtitle, embed_url, link_url, cover, published_at, active FROM mixes WHERE id = ?');
    $stmt->execute([$id]);
    $mix = $stmt->fetch();
    if (!$mix) {
        flash('Die sessie bestaat niet.');
        redirect(adminUrl('sessies.php'));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    if (($_POST['action'] ?? '') === 'delete' && $id > 0) {
        $pdo->prepare('DELETE FROM mixes WHERE id = ?')->execute([$id]);
        deleteCover($mix['cover']);
        flash('Sessie verwijderd.');
        redirect(adminUrl('sessies.php'));
    }

    $oldCover = $mix['cover'];
    $mix['title']        = postText('title', 200);
    $mix['subtitle']     = postText('subtitle', 200);
    $mix['published_at'] = postText('published_at', 10);
    $mix['active']       = isset($_POST['active']) ? 1 : 0;

    [$embed, $embedError] = normalizeEmbed((string) ($_POST['embed_url'] ?? ''));
    $mix['embed_url'] = $embedError ? postText('embed_url', 500) : $embed;
    $link = validLink(postText('link_url', 500));
    $mix['link_url'] = $link ?? postText('link_url', 500);

    if ($mix['title'] === '')                       { $errors[] = 'Vul een titel in.'; }
    if (validDate($mix['published_at']) === null)   { $errors[] = 'Kies een geldige datum.'; }
    if ($embedError)                                { $errors[] = $embedError; }
    if ($link === null)                             { $errors[] = 'De link moet met http:// of https:// beginnen.'; }
    if (strlen($mix['embed_url']) > 500)            { $errors[] = 'De speler-link is te lang.'; }

    // Artwork pas verwerken als de rest klopt, anders blijven er losse bestanden achter.
    $newCover = '';
    if (!$errors) {
        [$newCover, $coverError] = saveCover($_FILES['cover'] ?? []);
        if ($coverError) { $errors[] = $coverError; }
    }

    if (!$errors) {
        if ($newCover !== '') {
            $mix['cover'] = $newCover;
        } elseif (isset($_POST['remove_cover'])) {
            $mix['cover'] = '';
        }

        $values = [$mix['title'], $mix['subtitle'], $mix['embed_url'], $mix['link_url'], $mix['cover'], $mix['published_at'], $mix['active']];
        if ($id > 0) {
            $pdo->prepare('UPDATE mixes SET title = ?, subtitle = ?, embed_url = ?, link_url = ?, cover = ?, published_at = ?, active = ? WHERE id = ?')
                ->execute([...$values, $id]);
        } else {
            $pdo->prepare('INSERT INTO mixes (title, subtitle, embed_url, link_url, cover, published_at, active) VALUES (?, ?, ?, ?, ?, ?, ?)')
                ->execute($values);
        }
        if ($oldCover !== '' && $oldCover !== $mix['cover']) {
            deleteCover($oldCover);
        }
        flash('Sessie opgeslagen.');
        redirect(adminUrl('sessies.php'));
    }
    $mix['cover'] = $oldCover;
}

adminHeader($id ? 'Sessie bewerken' : 'Nieuwe sessie', $user);
?>
<p><a href="<?= e(adminUrl('sessies.php')) ?>">← Alle sessies</a></p>
<h1><?= $id ? 'Sessie bewerken' : 'Nieuwe sessie' ?></h1>

<?php foreach ($errors as $error): ?>
  <p class="error"><?= e($error) ?></p>
<?php endforeach; ?>

<form method="post" class="form" enctype="multipart/form-data">
  <?= csrfField() ?>
  <label>Titel
    <input name="title" value="<?= e($mix['title']) ?>" maxlength="200" required>
  </label>
  <div class="row">
    <label>Ondertitel <small>(bv. Live session, Mix, Brugge)</small>
      <input name="subtitle" value="<?= e($mix['subtitle']) ?>" maxlength="200">
    </label>
    <label>Datum
      <input type="date" name="published_at" value="<?= e($mix['published_at']) ?>" required>
    </label>
  </div>

  <label>Speler <small>(link van Mixcloud of SoundCloud, of de volledige insluitcode)</small>
    <textarea name="embed_url" rows="3" class="short" autocapitalize="none" spellcheck="false"><?= e($mix['embed_url']) ?></textarea>
  </label>
  <label>Extra link <small>(optioneel, voor de knop "Beluisteren")</small>
    <input type="url" name="link_url" value="<?= e($mix['link_url']) ?>" maxlength="500" autocapitalize="none" placeholder="https://">
  </label>

  <fieldset>
    <legend>Artwork <small>(JPG, PNG of WebP, max. 8 MB — wordt vierkant bijgesneden)</small></legend>
<?php if ($mix['cover'] !== ''): ?>
    <img class="preview" src="<?= e(asset($mix['cover'])) ?>" alt="Huidig artwork" width="160" height="160">
    <label class="check"><input type="checkbox" name="remove_cover" value="1"> Artwork verwijderen</label>
<?php endif; ?>
    <input type="file" name="cover" accept="image/jpeg,image/png,image/webp">
  </fieldset>

  <label class="check"><input type="checkbox" name="active" value="1"<?= $mix['active'] ? ' checked' : '' ?>> Tonen op de site</label>

  <div class="actions">
    <button type="submit">Opslaan</button>
  </div>
</form>

<?php if ($id > 0): ?>
<form method="post" class="danger" onsubmit="return confirm('Deze sessie verwijderen?');">
  <?= csrfField() ?>
  <input type="hidden" name="action" value="delete">
  <button class="link" type="submit">Sessie verwijderen</button>
</form>
<?php endif; ?>
<?php adminFooter();
