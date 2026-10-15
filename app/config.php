<?php
/** Environment configuration. Default storage/config stay private to the project. */
$root=dirname(__DIR__);
$envFile=getenv('APP_ENV_FILE')?:$root.'/.env';
$env=is_file($envFile)?parse_ini_file($envFile,false,INI_SCANNER_RAW):[];
if(!is_array($env))throw new RuntimeException('The environment configuration is not valid INI.');
foreach(['DB_HOST','DB_PORT','DB_DATABASE','DB_USERNAME','DB_PASSWORD','DB_PASSWORD_B64','APP_URL','APP_ENV','APP_STORAGE','APP_REQUIRE_HTTPS','TRUST_PROXY_HEADERS','PUSHER_APP_ID','PUSHER_APP_KEY','PUSHER_APP_SECRET','PUSHER_APP_CLUSTER','PUSHER_ENABLED','demo'] as $key){if(getenv($key)!==false)$env[$key]=getenv($key);}
if(!array_key_exists('DB_PASSWORD',$env)&&array_key_exists('DB_PASSWORD_B64',$env)){
 $decoded=base64_decode((string)$env['DB_PASSWORD_B64'],true);if($decoded===false)throw new RuntimeException('Invalid encoded database configuration.');$env['DB_PASSWORD']=$decoded;
}
$storageDir=$env['APP_STORAGE']??$root.'/storage';
if(!preg_match('~^(?:[A-Za-z]:[\\\\/]|/)~',$storageDir))$storageDir=$root.'/'.$storageDir;
$storageDir=rtrim($storageDir,'/\\');
function app_storage_path(string $suffix=''):string{return $GLOBALS['storageDir'].($suffix!==''?'/'.ltrim($suffix,'/\\'):'');}
