<?php
require_once __DIR__.'/cli-common.php';
try{$db=cli_db();$db->exec(file_get_contents($root.'/database/communications.sql'));echo "Communications tables ready. Existing accounts/orders preserved; no legacy marketing consent inferred. Existing sessions must sign in again after this update.\n";}catch(Throwable $e){cli_fail($e);}
