<?php
// CLI only; preview by default. Removes only unattached photos older than 7 days.
if(PHP_SAPI!=='cli')exit(1);
require dirname(__DIR__).'/app/bootstrap.php';
$apply=in_array('--apply',$argv,true);$count=0;
$rows=query('SELECT id FROM return_evidence WHERE return_id IS NULL AND created_at < UTC_TIMESTAMP() - INTERVAL 7 DAY LIMIT 1000')->fetchAll();
foreach($rows as $row){if(!$apply){$count++;continue;}$db->beginTransaction();try{$e=query('SELECT * FROM return_evidence WHERE id=? AND return_id IS NULL AND created_at < UTC_TIMESTAMP() - INTERVAL 7 DAY FOR UPDATE',[$row['id']])->fetch();if($e){query('DELETE FROM return_evidence WHERE id=?',[$e['id']]);}$db->commit();if($e){$path=app_storage_path('return-evidence/').$e['file_name'];if(is_file($path))unlink($path);$count++;}}catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}}
echo ($apply?'Removed ':'Would remove ').$count." stale, unattached evidence photo(s). Attached case evidence is never removed.\n";
