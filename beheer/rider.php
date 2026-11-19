<?php
require __DIR__ . '/_bootstrap.php';
$user = requireLogin();
$pdo  = db();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $rows = [];
    foreach ((array) ($_POST['item'] ?? []) as $i => $item) {
        $item = mb_substr(trim((string) $item), 0, 150);
        if ($item === '' || isset($_POST['delete'][$i])) {
            continue;   // lege rij of aangevinkt om te verwijderen
        }
        $qty = (int) ($_POST['quantity'][$i] ?? 1);
        if ($qty < 1 || $qty > 99) {
            $errors[] = 'Het aantal bij "' . $item . '" moet tussen 1 en 99 liggen.';
        }
        $rows[] = [max(1, min(99, $qty)), $item];
    }

    if (!$errors) {
        // De volledige lijst opnieuw opslaan, in de volgorde van het formulier.
        $pdo->beginTransaction();
        $pdo->exec('DELETE FROM rider_items');
        $insert = $pdo->prepare('INSERT INTO rider_items (quantity, item, sort_order) VALUES (?, ?, ?)');
        foreach ($rows as $order => [$qty, $item]) {
            $insert->execute([$qty, $item, $order + 1]);
        }
        $pdo->commit();
        flash('Rider opgeslagen.');
        redirect(adminUrl('rider.php'));
    }
}

$items = $pdo->query('SELECT quantity, item FROM rider_items ORDER BY sort_order, id')->fetchAll();
$items[] = ['quantity' => 1, 'item' => ''];   // lege rij om iets toe te voegen
$items[] = ['quantity' => 1, 'item' => ''];

adminHeader('Rider', $user);
?>
<h1>Technische rider</h1>
<p class="hint">Eén lijn per toestel. Vul een lege lijn in om iets toe te voegen, vink "weg" aan om te verwijderen,
  en verschuif lijnen met ↑ ↓. Vergeet daarna niet op te slaan.</p>

<?php foreach ($errors as $error): ?>
  <p class="error"><?= e($error) ?></p>
<?php endforeach; ?>

<form method="post" class="form">
  <?= csrfField() ?>
  <div class="rider-rows">
<?php foreach ($items as $i => $row): ?>
    <div class="rider-row">
      <input type="number" name="quantity[<?= $i ?>]" value="<?= (int) $row['quantity'] ?>" min="1" max="99" aria-label="Aantal" inputmode="numeric">
      <input name="item[<?= $i ?>]" value="<?= e($row['item']) ?>" maxlength="150" aria-label="Toestel" placeholder="<?= $row['item'] === '' ? 'Nieuw toestel…' : '' ?>">
<span class="controls">
<?php if ($row['item'] !== ''): ?>
        <label class="check"><input type="checkbox" name="delete[<?= $i ?>]" value="1"> weg</label>
<?php endif; ?>
        <button type="button" class="link" data-move="-1" aria-label="Omhoog">↑</button>
        <button type="button" class="link" data-move="1" aria-label="Omlaag">↓</button>
      </span>
    </div>
<?php endforeach; ?>
  </div>
  <div class="actions">
    <button type="submit">Opslaan</button>
  </div>
</form>

<script>
// Lijnen verschuiven: de volgorde op het scherm is de volgorde die opgeslagen wordt.
document.querySelectorAll('[data-move]').forEach(function (button) {
  button.addEventListener('click', function () {
    var row = button.closest('.rider-row');
    var other = button.dataset.move === '-1' ? row.previousElementSibling : row.nextElementSibling;
    if (!other) return;
    if (button.dataset.move === '-1') row.parentNode.insertBefore(row, other);
    else row.parentNode.insertBefore(other, row);
    button.focus();
  });
});
</script>
<?php adminFooter();
