<?php
require_once __DIR__.'/cli-common.php';
try{
 cli_requirements();$options=getopt('',['admin-name:','admin-phone:']);$details=cli_owner_details($options);$db=cli_db();
 $q=$db->prepare('SELECT COUNT(*) FROM users WHERE phone=?');$q->execute([$details[1]]);
 if($q->fetchColumn())throw new RuntimeException('Phone already exists. Refusing to promote or overwrite an existing account.');
 $password=cli_create_owner($db,$details);
 echo "New non-demo administrator created.\nPhone: ".$details[1]."\nPassword (save privately now): $password\n";
}catch(Throwable $e){cli_fail($e);}
