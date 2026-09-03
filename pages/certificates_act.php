<?php
/** POST-only handler to unissue (revoke) certificates. */
if (!is_post()) { redirect('certificates.index'); }
csrf_check();

if (!can('certificates.manage')) { deny('You do not have permission to manage certificates.'); }

$id = postInt('certificate_id');
$c = row('SELECT * FROM certificates WHERE id = ?', [$id]);
if (!$c) { flash('error', 'That certificate was not found.'); redirect('certificates.index'); }

// Delete the certificate record. Keep any audit trail.
q('DELETE FROM certificates WHERE id = ?', [$id]);
audit('unissue', 'certificates', $id, $c['serial_no'] ?? $id);
flash('ok', 'Certificate ' . e($c['serial_no'] ?? $id) . ' unissued.');
redirect('certificates.index');
