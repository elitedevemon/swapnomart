<?php
require_once __DIR__.'/cli-common.php';
try{
 cli_requirements();echo "PASS PHP extensions and image formats\n";
 if(!is_file($envFile))throw new RuntimeException('Configuration file is missing. Copy the appropriate .env template.');
 foreach([app_storage_path(),$root.'/public/assets/uploads']as $dir)if(!is_dir($dir)||!is_writable($dir))throw new RuntimeException('Storage/uploads are missing or not writable.');
 echo "PASS CLI storage/uploads access (also check the web PHP user)\n";
 $db=cli_db();$version=$db->query('SELECT VERSION()')->fetchColumn();echo 'Database version: '.$version."\n";
 preg_match_all('/CREATE TABLE (?:IF NOT EXISTS )?`?([a-z_]+)`?/',file_get_contents($root.'/database/schema.sql'),$m);
 $tables=$db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);$missing=array_diff($m[1],$tables);
 if($missing)throw new RuntimeException('Missing tables: '.implode(', ',$missing));
 echo "PASS all ".count($m[1])." application tables present\n";
 $production=($env['APP_ENV']??'local')==='production';
 if($production){
  if(filter_var($env['demo']??'false',FILTER_VALIDATE_BOOLEAN))throw new RuntimeException('Production must have demo=false.');
  if(!filter_var($env['APP_REQUIRE_HTTPS']??'false',FILTER_VALIDATE_BOOLEAN)||parse_url($env['APP_URL']??'',PHP_URL_SCHEME)!=='https')throw new RuntimeException('Production needs HTTPS APP_URL and APP_REQUIRE_HTTPS=true.');
  if(empty($env['DB_PASSWORD'])||str_contains($env['DB_PASSWORD'],'CHANGE_ME'))throw new RuntimeException('Set a private non-empty production database password.');
  if(parse_url($env['APP_URL']??'',PHP_URL_HOST)==='your-domain.example')throw new RuntimeException('Replace the sample domain.');
  if(!$db->query("SELECT COUNT(*) FROM users WHERE role='admin' AND demo=0")->fetchColumn())throw new RuntimeException('Create a real non-demo admin.');
  $samples=(int)$db->query('SELECT (SELECT COUNT(*) FROM products WHERE is_demo=1)+(SELECT COUNT(*) FROM orders WHERE is_demo=1)+(SELECT COUNT(*) FROM users WHERE demo=1)')->fetchColumn();
  if($samples)throw new RuntimeException('Demo records remain. For launch, use a separate clean production database; never delete real customer orders to satisfy this check.');
  echo "PASS production configuration, real owner, no demo records\n";
 }else echo "NOTICE local/testing environment; not a production approval\n";
 echo "Checks complete. This does NOT test Apache access rules, TLS, browser flows or live courier credentials; complete the go-live checklist.\n";
}catch(Throwable $e){cli_fail($e);}
