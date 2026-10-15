<?php
require_once __DIR__.'/cli-common.php';
require_once dirname(__DIR__).'/app/installer.php';
try{
 $installMutex=setup_mutex();
 if(file_exists(installation_marker()))throw new RuntimeException('This project installation is permanently locked.');
 cli_requirements();$options=getopt('',['demo','production','admin-name:','admin-phone:']);
 $demoInstall=array_key_exists('demo',$options);$production=array_key_exists('production',$options);
 if($demoInstall===$production)throw new RuntimeException('Choose exactly one: --demo OR --production. Production also needs --admin-name and --admin-phone.');
 $configuredDemo=filter_var($env['demo']??'false',FILTER_VALIDATE_BOOLEAN);
 if($configuredDemo!==$demoInstall)throw new RuntimeException('The .env demo flag must match your installation mode.');
 if($demoInstall&&($env['APP_ENV']??'local')==='production')throw new RuntimeException('Demo installation is forbidden with APP_ENV=production.');
 $owner=$production?cli_owner_details($options):null;
 foreach([app_storage_path(),$root.'/public/assets/uploads']as $dir){if(!is_dir($dir)&&!mkdir($dir,0700,true))throw new RuntimeException('Cannot create writable directories.');if(!is_writable($dir))throw new RuntimeException('Storage/uploads must be writable by the CLI and web account.');}
 $db=cli_db();$dbLock='setup-'.substr(hash('sha256',$env['DB_DATABASE']),0,48);$lockQuery=$db->prepare('SELECT GET_LOCK(?,0)');$lockQuery->execute([$dbLock]);if(!$lockQuery->fetchColumn())throw new RuntimeException('Another installer is using this database.');$count=(int)$db->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE()')->fetchColumn();
 if($count!==0)throw new RuntimeException('REFUSED: database is not empty. This installer never resets or upgrades an existing store.');
 $sql=file_get_contents($root.'/database/'.($demoInstall?'bonon-demo.sql':'schema.sql'));
 if($sql===false)throw new RuntimeException('Installation SQL is missing.');
 // DDL is not transactional in MySQL. A failed import must be investigated in this NEW DB.
 // No existing-table overwrite, automatic DROP, or automatic retry is performed.
 setup_private_write(installation_marker(),json_encode(['state'=>'installing','started_at'=>gmdate('c'),'source'=>'cli']));
 try{$db->exec($sql);}finally{$db->exec('SET FOREIGN_KEY_CHECKS=1');}
 if($production){
  $db->beginTransaction();$password=cli_create_owner($db,$owner);
  $q=$db->prepare('INSERT INTO settings(`key`,value) VALUES(?,?)');
  foreach(['store_name'=>'Lifestyle Store','demo_login_enabled'=>'0','shipping_fee'=>'80','free_shipping'=>'3000','store_email'=>'']as $k=>$v)$q->execute([$k,$v]);
  $db->commit();echo "Production database initialized without sample products/orders.\nAdministrator phone: ".$owner[1]."\nPassword (save privately now): $password\n";
 }else echo "Demo database initialized. Demo logins are PUBLIC: use fictional data only.\n";
 setup_private_write(installation_marker(),json_encode(['state'=>'installed','completed_at'=>gmdate('c'),'source'=>'cli']));
 echo "Next: php scripts/check.php; then follow docs/START-HERE-BN.md.\n";
}catch(Throwable $e){if(isset($db)&&$db->inTransaction())$db->rollBack();cli_fail($e);}finally{if(isset($db,$dbLock)){$q=$db->prepare('SELECT RELEASE_LOCK(?)');$q->execute([$dbLock]);}if(isset($installMutex)){flock($installMutex,LOCK_UN);fclose($installMutex);}}
