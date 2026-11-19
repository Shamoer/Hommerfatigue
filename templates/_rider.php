<?php /* Technische rider uit $rider */ ?>
<?php if ($rider): ?>
  <ul class="rider">
<?php foreach ($rider as $row): ?>
    <li><span class="rider__qty"><?= (int) $row['quantity'] ?>×</span> <?= e($row['item']) ?></li>
<?php endforeach; ?>
  </ul>
<?php endif; ?>
