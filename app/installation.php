<?php
/** First-run gate. Independent of the database; a database outage never reopens setup. */
require_once __DIR__.'/config.php';
function installation_marker():string{return __DIR__.'/.installation-state';}
function installation_configured():bool{
 global $envFile;
 if(file_exists($envFile))return true;
 foreach(['DB_HOST','DB_DATABASE','DB_USERNAME','DB_PASSWORD','DB_PASSWORD_B64']as $key)if(getenv($key)!==false)return true;
 return false;
}
function installation_state():string{
 if(file_exists(installation_marker())){
  $state=json_decode((string)file_get_contents(installation_marker()),true);
  return ($state['state']??'')==='installed'?'installed':'blocked';
 }
 return installation_configured()?'existing':'fresh';
}
function installation_gate():void{
 if(PHP_SAPI==='cli')return;
 $state=installation_state();
 if($state==='fresh'){header('Cache-Control: no-store');header('Location: /install.php',true,303);exit;}
 if($state==='blocked'||($state==='installed'&&!installation_configured())){
  http_response_code(503);header('Cache-Control: no-store');header('Content-Type: text/plain; charset=utf-8');
  exit('Installation is locked. Restore the configuration or ask the hosting owner to inspect the interrupted installation. Reinstallation is disabled.');
 }
}
