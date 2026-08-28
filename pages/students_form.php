<?php
/** Register a new student, or edit an existing file. Replaces the paper form. */
require_once BASE_PATH . '/views/icons.php';

$id  = getInt('id');
$rec = null;
if ($id) {
    $rec = row('SELECT * FROM students WHERE id = ?', [$id]);
    if (!$rec) { flash('error', 'That student file was not found.'); redirect('students.index'); }
    if (!is_admin() && (int) $rec['department_id'] !== my_department()) deny('That student is in another department.');
}
$isEdit = (bool) $rec;
$page_title = $isEdit ? 'Edit student file' : 'Register a student';
$page_sub   = $isEdit ? e($rec['student_no']) . ' · ' . e($rec['first_name'] . ' ' . $rec['last_name'])
                      : 'The next number will be <span class="mono">' . e(next_student_no()) . '</span>';

$depts   = rows('SELECT id, name FROM departments WHERE status = ? ORDER BY name', ['active']);
$courses = rows('SELECT c.id, c.code, c.name, c.department_id, c.capacity,
                        (SELECT COUNT(*) FROM enrolments e WHERE e.course_id = c.id AND e.status = \'active\') AS taken
                 FROM courses c WHERE c.status = ? ORDER BY c.name', ['active']);

$errors = [];
$f = $rec ?: [
    'first_name' => '', 'middle_name' => '', 'last_name' => '', 'gender' => '', 'dob' => '',
    'phone' => '', 'email' => '', 'national_id' => '', 'address' => '', 'education_level' => '',
    'guardian_name' => '', 'guardian_phone' => '', 'guardian_relation' => '',
    'department_id' => (is_admin() ? '' : my_department()), 'status' => 'pending', 'notes' => '', 'photo' => null,
];

if (is_post()) {
    csrf_check();
    foreach (array_keys($f) as $k) {
        if ($k !== 'photo') $f[$k] = post($k);
    }

    if ($f['first_name'] === '') $errors[] = 'First name is required.';
    if ($f['last_name'] === '')  $errors[] = 'Last name is required.';
    if (!$f['department_id'])     $errors[] = 'Choose a department.';
    if ($f['email'] !== '' && !filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'That email address is not valid.';
    if ($f['dob'] !== '' && strtotime($f['dob']) > time()) $errors[] = 'Date of birth cannot be in the future.';

    // Duplicate detection on national ID and phone, as required by the proposal
    if ($f['national_id'] !== '') {
        $dupe = row('SELECT id, student_no, first_name, last_name FROM students WHERE national_id = ? AND id <> ?',
                    [$f['national_id'], $id]);
        if ($dupe) $errors[] = 'National ID already belongs to ' . e($dupe['first_name'] . ' ' . $dupe['last_name'])
                             . ' (' . e($dupe['student_no']) . ').';
    }
    if ($f['phone'] !== '') {
        $dupe = row('SELECT id, student_no, first_name, last_name FROM students WHERE phone = ? AND id <> ?',
                    [$f['phone'], $id]);
        if ($dupe) $errors[] = 'Phone number already on file for ' . e($dupe['first_name'] . ' ' . $dupe['last_name'])
                             . ' (' . e($dupe['student_no']) . ').';
    }

    $photoErr = null;
    $newPhoto = save_photo('photo', $photoErr);
    if ($photoErr) $errors[] = $photoErr;

    if (!$errors) {
        $data = [
            'first_name' => $f['first_name'], 'middle_name' => $f['middle_name'], 'last_name' => $f['last_name'],
            'gender' => $f['gender'], 'dob' => $f['dob'] ?: null, 'phone' => $f['phone'], 'email' => $f['email'],
            'national_id' => $f['national_id'], 'address' => $f['address'], 'education_level' => $f['education_level'],
            'guardian_name' => $f['guardian_name'], 'guardian_phone' => $f['guardian_phone'],
            'guardian_relation' => $f['guardian_relation'], 'department_id' => (int) $f['department_id'],
            'status' => $f['status'], 'notes' => $f['notes'],
        ];
        if ($newPhoto) $data['photo'] = $newPhoto;

        if ($isEdit) {
            update('students', $data, 'id = :wid', ['wid' => $id]);
            audit('update', 'students', $id, $rec['student_no']);
            flash('ok', 'Student file updated.');
            redirect('students.view', ['id' => $id]);
        } else {
            $data['student_no']    = next_student_no();
            $data['registered_by'] = user_id();
            $data['registered_at'] = now();
            $newId = insert('students', $data);
            audit('create', 'students', $newId, $data['student_no']);

            $courseId = postInt('course_id');
            if ($courseId) {
                $c = row('SELECT * FROM courses WHERE id = ?', [$courseId]);
                if ($c) {
                    insert('enrolments', [
                        'student_id' => $newId, 'course_id' => $courseId,
                        'enrolled_on' => date('Y-m-d'), 'status' => 'active', 'created_at' => now(),
                    ]);
                    audit('enrol', 'enrolments', $newId, $c['code']);
                }
            }
            flash('ok', 'Registered <strong>' . e($data['first_name'] . ' ' . $data['last_name'])
                . '</strong> as <span class="mono">' . e($data['student_no']) . '</span>.');
            redirect('students.view', ['id' => $newId]);
        }
    }
}
?>

<?php foreach ($errors as $er): ?>
  <div class="alert alert--error"><?= icon('alert', 17) ?><div><?= $er ?></div></div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="grid grid--sidebar">
    <div class="panel">
      <div class="panel__body">

        <div class="section-head"><span></span><h3>Student details</h3></div>
        <div class="formgrid formgrid--3">
          <div class="field">
            <label for="first_name">First name <span class="req">*</span></label>
            <input id="first_name" name="first_name" value="<?= e($f['first_name']) ?>" required>
          </div>
          <div class="field">
            <label for="middle_name">Middle name</label>
            <input id="middle_name" name="middle_name" value="<?= e($f['middle_name']) ?>">
          </div>
          <div class="field">
            <label for="last_name">Last name <span class="req">*</span></label>
            <input id="last_name" name="last_name" value="<?= e($f['last_name']) ?>" required>
          </div>
          <div class="field">
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
              <option value="">—</option>
              <?php foreach (['Female', 'Male'] as $g): ?>
                <option <?= $f['gender'] === $g ? 'selected' : '' ?>><?= $g ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="dob">Date of birth</label>
            <input id="dob" name="dob" type="date" value="<?= e($f['dob']) ?>" max="<?= date('Y-m-d') ?>">
          </div>
          <div class="field">
            <label for="education_level">Education level</label>
            <input id="education_level" name="education_level" value="<?= e($f['education_level']) ?>" placeholder="Standard Seven, Form Four…">
          </div>
        </div>

        <div class="section-head"><span></span><h3>Contact</h3></div>
        <div class="formgrid formgrid--3">
          <div class="field">
            <label for="phone">Phone</label>
            <input id="phone" name="phone" value="<?= e($f['phone']) ?>" placeholder="+255 7…">
            <div class="hint">Checked against existing files.</div>
          </div>
          <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= e($f['email']) ?>">
          </div>
          <div class="field">
            <label for="national_id">National ID / NIDA</label>
            <input id="national_id" name="national_id" value="<?= e($f['national_id']) ?>">
            <div class="hint">Used for duplicate detection.</div>
          </div>
          <div class="field span2">
            <label for="address">Address / ward</label>
            <input id="address" name="address" value="<?= e($f['address']) ?>">
          </div>
        </div>

        <div class="section-head"><span></span><h3>Parent, guardian or emergency contact</h3></div>
        <div class="formgrid formgrid--3">
          <div class="field">
            <label for="guardian_name">Full name</label>
            <input id="guardian_name" name="guardian_name" value="<?= e($f['guardian_name']) ?>">
          </div>
          <div class="field">
            <label for="guardian_phone">Phone</label>
            <input id="guardian_phone" name="guardian_phone" value="<?= e($f['guardian_phone']) ?>">
          </div>
          <div class="field">
            <label for="guardian_relation">Relationship</label>
            <input id="guardian_relation" name="guardian_relation" value="<?= e($f['guardian_relation']) ?>" placeholder="Mother, Father, Guardian…">
          </div>
        </div>

        <div class="field">
          <label for="notes">Office notes</label>
          <textarea id="notes" name="notes" rows="3" placeholder="Anything the office should know — fee arrangement, referral, special needs."><?= e($f['notes']) ?></textarea>
        </div>
      </div>
    </div>

    <div class="stack">
      <div class="panel">
        <div class="panel__head"><h2>Placement</h2></div>
        <div class="panel__body">
          <div class="field">
            <label for="department_id">Department <span class="req">*</span></label>
            <select id="department_id" name="department_id" required <?= is_admin() ? '' : 'disabled' ?>>
              <option value="">Choose…</option>
              <?php foreach ($depts as $d): ?>
                <option value="<?= (int) $d['id'] ?>" <?= (int) $f['department_id'] === (int) $d['id'] ? 'selected' : '' ?>>
                  <?= e($d['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (!is_admin()): ?>
              <input type="hidden" name="department_id" value="<?= (int) my_department() ?>">
              <div class="hint">You register into your own department.</div>
            <?php endif; ?>
          </div>

          <?php if (!$isEdit): ?>
            <div class="field">
              <label for="course_id">Enrol into a course now</label>
              <select id="course_id" name="course_id">
                <option value="">Later</option>
                <?php foreach ($courses as $c):
                  $left = (int) $c['capacity'] - (int) $c['taken']; ?>
                  <option value="<?= (int) $c['id'] ?>" <?= $left <= 0 ? 'disabled' : '' ?>>
                    <?= e($c['code'] . ' — ' . $c['name']) ?><?= $left <= 0 ? ' (full)' : ' (' . $left . ' places)' ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="hint">You can enrol into more courses from the student file.</div>
            </div>
          <?php endif; ?>

          <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
              <?php foreach (['pending', 'active', 'completed', 'deferred', 'withdrawn', 'suspended'] as $st): ?>
                <option value="<?= $st ?>" <?= $f['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="hint">New applications start as <strong>pending</strong> until fees or documents are confirmed.</div>
          </div>
        </div>
      </div>

      <div class="panel">
        <div class="panel__head"><h2>Photo</h2></div>
        <div class="panel__body">
          <?php if (!empty($f['photo'])): ?>
            <img src="<?= e(photo_url($f['photo'])) ?>" alt="" style="width:100%;max-width:150px;border-radius:12px;border:1px solid var(--glass-edge);margin-bottom:10px">
          <?php endif; ?>
          <div class="field" style="margin:0">
            <label for="photo">Passport photo</label>
            <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp">
            <div class="hint">JPG, PNG or WEBP, up to 3 MB. Used on the ID card.</div>
          </div>
        </div>
      </div>

      <div class="panel">
        <div class="panel__foot" style="border-top:0">
          <button class="btn btn--primary" type="submit">
            <?= icon('check', 16) ?> <?= $isEdit ? 'Save changes' : 'Register student' ?>
          </button>
          <a class="btn btn--ghost" href="<?= e($isEdit ? url('students.view', ['id' => $id]) : url('students.index')) ?>">Cancel</a>
        </div>
      </div>
    </div>
  </div>
</form>
