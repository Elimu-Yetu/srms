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
$selectedIntake = postInt('intake') ?: current_intake();
$page_title = $isEdit ? 'Edit student file' : 'Register a student';
$page_sub   = $isEdit ? e($rec['student_no']) . ' · ' . e($rec['first_name'] . ' ' . $rec['last_name'])
                      : 'The next number will be <span class="mono">' . e(next_student_no($selectedIntake)) . '</span>';

$depts   = rows('SELECT id, name FROM departments WHERE status = ? ORDER BY name', ['active']);
$courses = rows('SELECT c.id, c.code, c.name, c.department_id, d.name AS dept_name, c.capacity,
                        (SELECT COUNT(*) FROM enrolments e WHERE e.course_id = c.id AND e.status = \'active\') AS taken
                 FROM courses c
                 LEFT JOIN departments d ON d.id = c.department_id
                 WHERE c.status = ? ORDER BY d.name, c.name', ['active']);
$courseGroups = [];
foreach ($courses as $c) {
    $deptName = $c['dept_name'] ?: 'Unassigned';
    $courseGroups[$deptName][] = $c;
}

$errors = [];
$f = $rec ?: [
    'first_name' => '', 'middle_name' => '', 'last_name' => '', 'gender' => '', 'dob' => '',
    'phone' => '', 'email' => '', 'national_id' => '', 'address' => '', 'education_level' => '',
    'guardian_name' => '', 'guardian_phone' => '', 'guardian_relation' => '',
    'department_id' => null, 'status' => 'pending', 'notes' => '', 'photo' => null,
];
$selectedCourseIds = [];

if (is_post()) {
    csrf_check();
    foreach (array_keys($f) as $k) {
        if ($k !== 'photo') $f[$k] = post($k);
    }
    $selectedCourseIds = array_values(array_unique(array_filter(array_map('intval', (array) post('course_ids')), static fn($id) => $id > 0)));

    $f['email'] = strtolower(trim($f['email']));

    if ($f['first_name'] === '') $errors[] = 'First name is required.';
    if ($f['last_name'] === '')  $errors[] = 'Last name is required.';
    if ($f['email'] !== '' && !filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'That email address is not valid.';
    if ($f['dob'] !== '' && strtotime($f['dob']) > time()) $errors[] = 'Date of birth cannot be in the future.';

    // Duplicate detection on national ID, phone and email
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
    if ($f['email'] !== '') {
        $dupe = row('SELECT id, student_no, first_name, last_name FROM students WHERE email = ? AND id <> ?',
                    [$f['email'], $id]);
        if ($dupe) $errors[] = 'Email address already belongs to ' . e($dupe['first_name'] . ' ' . $dupe['last_name'])
                             . ' (' . e($dupe['student_no']) . ').';
    }

    $photoErr = null;
    $newPhoto = save_photo('photo', $photoErr);
    if ($photoErr) $errors[] = $photoErr;

    if (!$errors && $selectedCourseIds) {
        foreach ($selectedCourseIds as $courseId) {
            $course = row('SELECT * FROM courses WHERE id = ? AND status = ?', [$courseId, 'active']);
            if (!$course) {
                $errors[] = 'One or more selected courses are no longer available.';
                continue;
            }
            $taken = (int) val('SELECT COUNT(*) FROM enrolments WHERE course_id = ? AND status = ?', [$courseId, 'active'], 0);
            if ($taken >= (int) $course['capacity']) {
                $errors[] = 'Course ' . e($course['code']) . ' is full. Choose a different course.';
                continue;
            }
            foreach (array_diff($selectedCourseIds, [$courseId]) as $otherCourseId) {
                $clash = course_timetable_clash((int) $courseId, (int) $otherCourseId);
                if ($clash) {
                    $day = day_name((int) $clash['day_of_week']);
                    $errors[] = 'Course ' . e($course['code']) . ' clashes with ' . e($clash['second_code']) . ' on ' . e($day)
                        . ' (' . e($clash['first_start']) . '–' . e($clash['first_end']) . ').';
                    break;
                }
            }
        }
    }

    if (!$errors) {
        $data = [
            'first_name' => $f['first_name'], 'middle_name' => $f['middle_name'], 'last_name' => $f['last_name'],
            'gender' => $f['gender'], 'dob' => $f['dob'] ?: null, 'phone' => $f['phone'], 'email' => $f['email'],
            'national_id' => $f['national_id'], 'address' => $f['address'], 'education_level' => $f['education_level'],
            'guardian_name' => $f['guardian_name'], 'guardian_phone' => $f['guardian_phone'],
            'guardian_relation' => $f['guardian_relation'], 'department_id' => !empty($f['department_id']) ? (int) $f['department_id'] : null,
            'status' => $f['status'], 'notes' => $f['notes'],
        ];
        if ($newPhoto) $data['photo'] = $newPhoto;

        if ($isEdit) {
            if ($newPhoto && !empty($rec['photo']) && $rec['photo'] !== $newPhoto) {
                delete_student_photo($rec['photo']);
            }
            update('students', $data, 'id = :wid', ['wid' => $id]);
            audit('update', 'students', $id, $rec['student_no']);
            flash('ok', 'Student file updated.');
            redirect('students.view', ['id' => $id]);
        } else {
            $intake = postInt('intake') ?: current_intake();
            $data['student_no']    = next_student_no($intake);
            $data['registered_by'] = user_id();
            $data['registered_at'] = now();
            $newId = insert('students', $data);
            audit('create', 'students', $newId, $data['student_no']);

            foreach ($selectedCourseIds as $courseId) {
                $c = row('SELECT * FROM courses WHERE id = ?', [$courseId]);
                if (!$c) { continue; }
                insert('enrolments', [
                    'student_id' => $newId, 'course_id' => $courseId,
                    'enrolled_on' => date('Y-m-d'), 'status' => 'active', 'created_at' => now(),
                ]);
                audit('enrol', 'enrolments', $newId, $c['code']);
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
    <div class="stack">
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
            <label for="email">Email <?= badge('Optional', 'neutral') ?></label>
            <input id="email" name="email" type="email" value="<?= e($f['email']) ?>" placeholder="Optional">
            <div class="hint">Optional — used for portal login.</div>
          </div>
          <div class="field">
            <label for="national_id">National ID / NIDA <?= badge('Optional', 'neutral') ?></label>
            <input id="national_id" name="national_id" value="<?= e($f['national_id']) ?>" placeholder="Optional">
            <div class="hint">Used for duplicate detection (optional).</div>
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
          <textarea id="notes" name="notes" rows="3" placeholder="Anything the office should know — referral, special needs, background."><?= e($f['notes']) ?></textarea>
        </div>

        <div class="section-head"><span></span><h3>Photo</h3></div>
        <div class="field" style="margin-bottom:0">
          <?php if (!empty($f['photo'])): ?>
            <img class="photo-preview-large" src="<?= e(photo_url($f['photo'])) ?>" alt="">
          <?php endif; ?>
          <label for="photo">Passport photo</label>
          <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp">
          <div class="hint">JPG, PNG or WEBP, up to 3 MB. Used on the ID card.</div>
        </div>
        </div>
      </div>

    </div>

    <div class="stack">
      <div class="panel">
        <div class="panel__head"><h2>Placement</h2></div>
        <div class="panel__body">
          <?php if (!$isEdit): ?>
            <div class="field">
              <label for="intake">Intake</label>
              <select id="intake" name="intake">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                  <option value="<?= $i ?>" <?= $selectedIntake === $i ? 'selected' : '' ?>><?= e(intake_label($i)) ?></option>
                <?php endfor; ?>
              </select>
              <div class="hint">Determines the intake code in the student number.</div>
            </div>
          <?php endif; ?>

          <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
              <?php foreach (['pending', 'active', 'completed', 'deferred', 'withdrawn', 'suspended'] as $st): ?>
                <option value="<?= $st ?>" <?= $f['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="hint">New applications start as <strong>pending</strong> until documents are confirmed.</div>
          </div>
        </div>
        <div class="panel__foot" style="border-top:0; justify-content:flex-start; margin-top:0; padding-top:12px">
          <button class="btn btn--primary" type="submit">
            <?= icon('check', 16) ?> <?= $isEdit ? 'Save changes' : 'Register student' ?>
          </button>
          <a class="btn btn--ghost" href="<?= e($isEdit ? url('students.view', ['id' => $id]) : url('students.index')) ?>">Cancel</a>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
(function(){
  const startBtn = document.getElementById('cam-start');
  const captureBtn = document.getElementById('cam-capture');
  const retakeBtn = document.getElementById('cam-retake');
  const stopBtn = document.getElementById('cam-stop');
  const video = document.getElementById('cam-video');
  const canvas = document.getElementById('cam-canvas');
  const preview = document.getElementById('cam-preview');
  const photoData = document.getElementById('photo_data');
  const fileInput = document.getElementById('photo');
  const largePreview = document.querySelector('.photo-preview-large');
  let largeLiveVideo = null;
  let stream = null;
  const camArea = document.getElementById('cam-area');

  function show(el, ok){ el.style.display = ok ? '' : 'none'; }

  startBtn.addEventListener('click', async function(){
    try {
      // Prefer the rear camera on phones
      const facing = window.matchMedia('(max-width: 600px)').matches ? 'environment' : 'user';
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: facing }, audio: false });
      video.srcObject = stream;
      video.play();
      show(video, true); show(captureBtn, true); show(stopBtn, true); show(startBtn, false);
      // On narrow screens, request fullscreen for a native camera-like feel
      if (window.matchMedia('(max-width: 600px)').matches) {
        try {
          if (camArea.requestFullscreen) await camArea.requestFullscreen();
          else if (video.requestFullscreen) await video.requestFullscreen();
          video.classList.add('cam-fullscreen');
        } catch (fsErr) {
          video.classList.add('cam-fullscreen');
        }
      }
      // Set passport pixel dimensions for capture area
      const passport = { w: 360, h: 480 };
      // apply dimensions to video, canvas and preview for an accurate passport-size capture
      canvas.width = passport.w; canvas.height = passport.h;
      video.width = passport.w; video.height = passport.h;
      video.style.width = passport.w + 'px'; video.style.height = passport.h + 'px';
      preview.style.width = passport.w + 'px'; preview.style.height = passport.h + 'px';
      // add helper class for styling
      video.classList.add('passport-size');
      preview.classList.add('passport-size');
      // show live video in the large photo area on wider screens
      if (!window.matchMedia('(max-width: 600px)').matches) {
        largeLiveVideo = document.createElement('video');
        largeLiveVideo.autoplay = true; largeLiveVideo.playsInline = true; largeLiveVideo.muted = true;
        largeLiveVideo.className = 'photo-preview-large';
        try { largeLiveVideo.srcObject = stream; } catch (e) { /* older browsers */ }
        if (largePreview && largePreview.parentNode) {
          largePreview.parentNode.insertBefore(largeLiveVideo, largePreview);
          largePreview.style.display = 'none';
        } else {
          // find the photo panel body to insert into when no existing image
          const panelBody = fileInput.closest('.panel__body');
          if (panelBody) {
            panelBody.insertBefore(largeLiveVideo, panelBody.firstChild);
            // create a hidden img placeholder to hold captured image later
            largePreview = document.createElement('img');
            largePreview.className = 'photo-preview-large';
            largePreview.style.display = 'none';
            panelBody.insertBefore(largePreview, largeLiveVideo.nextSibling);
          }
        }
      }
    } catch (err) {
      console.error('getUserMedia error', err);
      alert('Could not access the camera. Check permissions.\n' + (err && err.message ? err.message : ''));
    }
  });

  captureBtn.addEventListener('click', function(){
    const w = canvas.width || 360; const h = canvas.height || 480;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, w, h);
    // Prefer WEBP when supported for smaller uploads, fallback to JPEG
    let dataUrl;
    try {
      dataUrl = canvas.toDataURL('image/webp', 0.92);
      if (!dataUrl || dataUrl.indexOf('data:image/webp') !== 0) throw new Error('no webp');
    } catch (err) {
      dataUrl = canvas.toDataURL('image/jpeg', 0.92);
    }
    photoData.value = dataUrl;
    preview.src = dataUrl; show(preview, true);
    // update the large photo area with the captured image immediately
    if (largeLiveVideo) { try { largeLiveVideo.remove(); } catch (e) {} largeLiveVideo = null; }
    if (largePreview) { largePreview.src = dataUrl; largePreview.style.display = 'block'; }
    show(video, false); show(captureBtn, false); show(retakeBtn, true);
    // clear file input so server uses captured photo
    try { fileInput.value = ''; } catch (e) {}
    // Exit fullscreen if active
    try { if (document.fullscreenElement) document.exitFullscreen(); } catch (e) {}
    video.classList.remove('cam-fullscreen');
    // remove passport-size class after capture so preview can scale responsively
    video.classList.remove('passport-size');
    preview.classList.add('passport-size');
    // ensure the large preview shows the captured image
    if (largePreview) { largePreview.src = dataUrl; largePreview.style.display = 'block'; }
  });

  retakeBtn.addEventListener('click', function(){
    photoData.value = '';
    preview.src = '';
    show(preview, false);
    show(video, true); show(captureBtn, true); show(retakeBtn, false);
    if (largeLiveVideo) { try { largeLiveVideo.remove(); } catch (e) {} largeLiveVideo = null; }
    if (largePreview) largePreview.style.display = 'none';
  });

  stopBtn.addEventListener('click', function(){
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
    show(video, false); show(captureBtn, false); show(stopBtn, false); show(startBtn, true);
    try { if (document.fullscreenElement) document.exitFullscreen(); } catch (e) {}
    video.classList.remove('cam-fullscreen');
    if (largeLiveVideo) { try { largeLiveVideo.remove(); } catch (e) {} largeLiveVideo = null; }
    if (largePreview) largePreview.style.display = largePreview.src ? 'block' : 'none';
  });

  fileInput.addEventListener('change', function(){
    if (fileInput.files && fileInput.files.length) {
      // clear any captured data
      photoData.value = '';
      const f = fileInput.files[0];
      // show a preview in the browser before upload
      try {
        const url = URL.createObjectURL(f);
        preview.src = url;
        show(preview, true);
        show(video, false);
        show(captureBtn, false);
        show(retakeBtn, false);
        // also show the selected image in the large photo area
        if (largeLiveVideo) { try { largeLiveVideo.remove(); } catch (e) {} largeLiveVideo = null; }
        if (largePreview) { largePreview.src = url; largePreview.style.display = 'block'; }
        preview.onload = () => { try { URL.revokeObjectURL(url); } catch (e) {} };
      } catch (e) {
        // fallback: clear preview
        preview.src = '';
        show(preview, false);
        show(video, false);
        show(captureBtn, false);
        show(retakeBtn, false);
        if (largePreview) largePreview.style.display = 'none';
      }
    } else {
      preview.src = '';
      show(preview, false);
      if (largePreview) largePreview.style.display = 'none';
    }
  });
  // Stop camera if form is submitted or page unloads
  const theForm = document.querySelector('form');
  if (theForm) {
    theForm.addEventListener('submit', function(){ if (stream) { stream.getTracks().forEach(t => t.stop()); } });
  }
  window.addEventListener('beforeunload', function(){ if (stream) { stream.getTracks().forEach(t => t.stop()); } });
})();
</script>
