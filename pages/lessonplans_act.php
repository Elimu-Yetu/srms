<?php
/** POST-only handler for lesson-plan actions (delete). */
if (!is_post()) { redirect('lessonplans.index'); }
csrf_check();

$id = postInt('id');
if (!$id) { flash('error', 'No lesson plan specified.'); redirect('lessonplans.index'); }

$p = row('SELECT * FROM lesson_plans WHERE id = ?', [$id]);
if (!$p) { flash('error', 'That lesson plan was not found.'); redirect('lessonplans.index'); }

// Facilitators may delete their own plans; reviewers (admin/manager) may delete any plan.
if (is_role('facilitator')) {
    if ((int) $p['facilitator_id'] !== user_id()) { deny('You may only delete your own plans.'); }
} else {
    if (!can('lessonplans.review')) { deny(); }
}

q('DELETE FROM lesson_plans WHERE id = ?', [$id]);
audit('delete', 'lesson_plans', $id, $p['topic'] ?? $id);
flash('ok', 'Lesson plan deleted.');
redirect('lessonplans.index');
