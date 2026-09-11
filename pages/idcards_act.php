<?php
/** POST-only handler to unissue (remove) an ID card record. */
if (!is_post()) { redirect('idcards.index'); }
csrf_check();

if (!can('idcards.manage')) { deny('You do not have permission to manage ID cards.'); }

$id = postInt('id_card_id');
$card = row('SELECT * FROM id_cards WHERE id = ?', [$id]);
if (!$card) { flash('error', 'That ID card was not found.'); redirect('idcards.index'); }

q('DELETE FROM id_cards WHERE id = ?', [$id]);
audit('unissue', 'id_cards', $id, $card['card_no'] ?? $id);
flash('ok', 'ID card ' . e($card['card_no'] ?? $id) . ' unissued.');
redirect('idcards.index');
