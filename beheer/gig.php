<?php
require __DIR__ . '/_bootstrap.php';
$user = requireLogin();
$pdo  = db();

$languages = loadLanguages($pdo);
$id        = (int) ($_GET['id'] ?? 0);
$errors    = [];

$gig  = ['gig_date' => '', 'start_time' => '', 'event_name' => '', 'venue' => '', 'city' => 'Brugge'];
$info = array_fill_keys(array_keys($languages), '');

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT gig_date, start_time, event_name, venue, city FROM gigs WHERE id = ?');
    $stmt->execute([$id]);
    $gig = $stmt->fetch();
    if (!$gig) {
        flash('Die gig bestaat niet.');
        redirect(adminUrl('gigs.php'));
    }
    $gig['start_time'] = $gig['start_time'] ? substr($gig['start_time'], 0, 5) : '';
    $stmt = $pdo->prepare('SELECT lang_code, info FROM gig_translations WHERE gig_id = ?');
    $stmt->execute([$id]);
    $info = array_merge($info, $stmt->fetchAll(PDO::FETCH_KEY_PAIR));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    if (($_POST['action'] ?? '') === 'delete' && $id > 0) {
        $pdo->prepare('DELETE FROM gigs WHERE id = ?')->execute([$id]);   // vertalingen gaan mee (CASCADE)
        flash('Gig verwijderd.');
        redirect(adminUrl('gigs.php'));
    }

    $gig = [
        'gig_date'   => postText('gig_date', 10),
        'start_time' => postText('start_time', 5),
        'event_name' => postText('event_name', 150),
        'venue'      => postText('venue', 150),
        'city'       => postText('city', 100),
    ];
    foreach ($languages as $code => $language) {
        $info[$code] = postText('info_' . $code, 255);
    }

    if (validDate($gig['gig_date']) === null) { $errors[] = 'Kies een geldige datum.'; }
    if ($gig['start_time'] !== '' && validTime($gig['start_time']) === null) { $errors[] = 'Het uur klopt niet (bv. 22:00).'; }
    if ($gig['venue'] === '') { $errors[] = 'Vul de zaak of locatie in.'; }

    if (!$errors) {
        $values = [$gig['gig_date'], validTime($gig['start_time']), $gig['event_name'], $gig['venue'], $gig['city']];
        $pdo->beginTransaction();
        if ($id > 0) {
            $pdo->prepare('UPDATE gigs SET gig_date = ?, start_time = ?, event_name = ?, venue = ?, city = ? WHERE id = ?')
                ->execute([...$values, $id]);
        } else {
            $pdo->prepare('INSERT INTO gigs (gig_date, start_time, event_name, venue, city) VALUES (?, ?, ?, ?, ?)')
                ->execute($values);
            $id = (int) $pdo->lastInsertId();
        }
        $save = $pdo->prepare(
            'INSERT INTO gig_translations (gig_id, lang_code, info) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE info = VALUES(info)'
        );
        foreach ($info as $code => $text) {
            $save->execute([$id, $code, $text]);
        }
        $pdo->commit();
        flash('Gig opgeslagen.');
        redirect(adminUrl('gigs.php'));
    }
}

adminHeader($id ? 'Gig bewerken' : 'Nieuwe gig', $user);
?>
<p><a href="<?= e(adminUrl('gigs.php')) ?>">← Alle gigs</a></p>
<h1><?= $id ? 'Gig bewerken' : 'Nieuwe gig' ?></h1>

<?php foreach ($errors as $error): ?>
  <p class="error"><?= e($error) ?></p>
<?php endforeach; ?>

<form method="post" class="form">
  <?= csrfField() ?>
  <div class="row">
    <label>Datum
      <input type="date" name="gig_date" value="<?= e($gig['gig_date']) ?>" required>
    </label>
    <label>Uur <small>(optioneel)</small>
      <input type="time" name="start_time" value="<?= e($gig['start_time']) ?>">
    </label>
  </div>
  <label>Naam van het event <small>(optioneel, bv. Resonance)</small>
    <input name="event_name" value="<?= e($gig['event_name']) ?>" maxlength="150">
  </label>
  <div class="row">
    <label>Zaak / locatie
      <input name="venue" value="<?= e($gig['venue']) ?>" maxlength="150" required>
    </label>
    <label>Stad
      <input name="city" value="<?= e($gig['city']) ?>" maxlength="100">
    </label>
  </div>

  <fieldset>
    <legend>Extra info per taal <small>(optioneel, bv. "Set van 6 uur")</small></legend>
<?php foreach ($languages as $code => $language): ?>
    <label><?= e($language['name']) ?>
      <input name="info_<?= e($code) ?>" value="<?= e($info[$code] ?? '') ?>" maxlength="255" lang="<?= e($code) ?>">
    </label>
<?php endforeach; ?>
  </fieldset>

  <div class="actions">
    <button type="submit">Opslaan</button>
  </div>
</form>

<?php if ($id > 0): ?>
<form method="post" class="danger" onsubmit="return confirm('Deze gig verwijderen?');">
  <?= csrfField() ?>
  <input type="hidden" name="action" value="delete">
  <button class="link" type="submit">Gig verwijderen</button>
</form>
<?php endif; ?>
<?php adminFooter();
