<?php
/**
 * One day, four numbers. Kept deliberately blunt: big inputs, live totals,
 * one record per date (saving the same date again updates it).
 */
require_once BASE_PATH . '/views/icons.php';

$st      = settings();
$teaLbl  = $st['kitchen_tea_label']  ?? 'Morning tea (cups)';
$mealLbl = $st['kitchen_meal_label'] ?? 'Afternoon meal (plates)';

$date = getStr('date') ?: date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');

$errors = [];

if (is_post()) {
    csrf_check();
    $date = post('service_date');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors[] = 'Choose the day being recorded.';
    } elseif (strtotime($date) > time()) {
        $errors[] = 'You cannot record a day that has not happened yet.';
    }

    $nums = [];
    foreach (['tea_teachers', 'tea_students', 'meals_teachers', 'meals_students'] as $k) {
        $n = postInt($k);
        if ($n < 0)     $errors[] = 'Counts cannot be negative.';
        if ($n > 2000)  $errors[] = 'That count looks too high — please check it.';
        $nums[$k] = max(0, min(2000, $n));
    }
    $notes = mb_substr(trim(post('notes')), 0, 240);

    if (!$errors) {
        $existing = row('SELECT id FROM kitchen_records WHERE service_date = ?', [$date]);
        $data = $nums + ['notes' => $notes, 'recorded_by' => user_id(), 'updated_at' => now()];
        if ($existing) {
            update('kitchen_records', $data, 'id = :wid', ['wid' => $existing['id']]);
            audit('update', 'kitchen_records', $existing['id'], $date);
            flash('ok', 'Updated the kitchen record for ' . e(d($date)) . '.');
        } else {
            $data['service_date'] = $date;
            $data['created_at']   = now();
            $id = insert('kitchen_records', $data);
            audit('create', 'kitchen_records', $id, $date);
            flash('ok', 'Saved the kitchen record for ' . e(d($date)) . '.');
        }
        redirect('kitchen.index');
    }
}

$rec = row('SELECT k.*, u.name AS recorded_by_name FROM kitchen_records k
            LEFT JOIN users u ON u.id = k.recorded_by WHERE k.service_date = ?', [$date]);
$f = $rec ?: ['tea_teachers' => 0, 'tea_students' => 0, 'meals_teachers' => 0, 'meals_students' => 0, 'notes' => ''];
if (is_post()) $f = array_merge($f, $_POST);

$page_title = $rec ? 'Update ' . d($date) : 'Record ' . d($date);
$page_sub   = 'Morning tea and afternoon meals, counted separately for teachers and for students.';
?>
<?php foreach ($errors as $er): ?>
  <div class="alert alert--error"><?= icon('alert', 17) ?><div><?= $er ?></div></div>
<?php endforeach; ?>

<?php if ($rec): ?>
  <div class="alert alert--info">
    <?= icon('clock', 17) ?>
    <div><?= e(d($date, 'l d F Y')) ?> is already recorded by <strong><?= e($rec['recorded_by_name'] ?? 'someone') ?></strong>.
      Saving again replaces the numbers, and the change is logged.</div>
  </div>
<?php endif; ?>

<div class="grid grid--sidebar">
  <div class="panel">
    <form method="post" id="kitchen-form" class="panel__body">
      <?= csrf_field() ?>

      <div class="field" style="max-width:260px">
        <label for="service_date">Day <span class="req">*</span></label>
        <input id="service_date" name="service_date" type="date" value="<?= e($date) ?>" max="<?= date('Y-m-d') ?>" required>
        <div class="hint">One record per day. Pick an earlier date to fill in a missed day.</div>
      </div>

      <div class="section-head"><span></span><h3><?= e($teaLbl) ?> — morning</h3></div>
      <div class="formgrid">
        <div class="field">
          <label for="tea_teachers">Teachers</label>
          <input id="tea_teachers" name="tea_teachers" type="number" min="0" max="2000" class="bignum"
                 value="<?= (int) $f['tea_teachers'] ?>" inputmode="numeric">
        </div>
        <div class="field">
          <label for="tea_students">Students</label>
          <input id="tea_students" name="tea_students" type="number" min="0" max="2000" class="bignum"
                 value="<?= (int) $f['tea_students'] ?>" inputmode="numeric">
        </div>
      </div>

      <div class="section-head"><span></span><h3><?= e($mealLbl) ?> — afternoon</h3></div>
      <div class="formgrid">
        <div class="field">
          <label for="meals_teachers">Teachers</label>
          <input id="meals_teachers" name="meals_teachers" type="number" min="0" max="2000" class="bignum"
                 value="<?= (int) $f['meals_teachers'] ?>" inputmode="numeric">
        </div>
        <div class="field">
          <label for="meals_students">Students</label>
          <input id="meals_students" name="meals_students" type="number" min="0" max="2000" class="bignum"
                 value="<?= (int) $f['meals_students'] ?>" inputmode="numeric">
        </div>
      </div>

      <div class="field" style="margin-top:6px">
        <label for="notes">Note for the day</label>
        <input id="notes" name="notes" maxlength="240" value="<?= e($f['notes'] ?? '') ?>"
               placeholder="e.g. Extra guests at the ICT graduation, or gas ran out at 2pm">
      </div>

      <div class="btnrow">
        <button class="btn btn--primary"><?= icon('check', 16) ?> <?= $rec ? 'Update the day' : 'Save the day' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('kitchen.index')) ?>">Cancel</a>
      </div>
    </form>
  </div>

  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>Totals</h2></div>
      <div class="panel__body" id="kitchen-live">
        <p class="tiny muted">Totals update as you type.</p>
      </div>
    </div>
    <div class="panel">
      <div class="panel__head"><h2>How to count</h2></div>
      <div class="panel__body tiny" style="color:var(--ink-soft);line-height:1.7">
        Count <strong>servings, not people</strong> — if a teacher takes two cups, that is two cups.
        Enter teachers and students separately so management can see who the kitchen is feeding.
        Record the day before you close; if you forget, come back and pick the date.
      </div>
    </div>
  </div>
</div>
