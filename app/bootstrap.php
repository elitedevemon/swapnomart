<?php
declare(strict_types=1);
require_once __DIR__."/errors.php";
$demo=filter_var($env['demo']??'false',FILTER_VALIDATE_BOOLEAN);
$isSecure=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')||(filter_var($env['TRUST_PROXY_HEADERS']??'false',FILTER_VALIDATE_BOOLEAN)&&($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https');
if(!is_dir(app_storage_path())&&!mkdir(app_storage_path(),0700,true))throw new RuntimeException('Private storage is not writable.');
if(PHP_SAPI!=='cli'&&filter_var($env['APP_REQUIRE_HTTPS']??'false',FILTER_VALIDATE_BOOLEAN)&&!$isSecure){http_response_code(426);header('Content-Type: text/plain; charset=utf-8');exit('HTTPS is required. Open the HTTPS store URL.');}
if(($env['APP_ENV']??'local')==='production'&&$demo)throw new RuntimeException('Disable demo mode before using the production environment.');
if(empty($env['DB_DATABASE'])||!isset($env['DB_USERNAME']))throw new RuntimeException('Store is not configured. Copy the environment template and follow README.md.');
ini_set('session.use_strict_mode','1');ini_set('session.use_only_cookies','1');
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>$isSecure]);
session_start();$_SESSION['csrf']??=bin2hex(random_bytes(24));
$db=new PDO('mysql:host='.($env['DB_HOST']??'127.0.0.1').';port='.($env['DB_PORT']??3306).';dbname='.($env['DB_DATABASE']??'bonon').';charset=utf8mb4',$env['DB_USERNAME']??'bonon',$env['DB_PASSWORD']??'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
$db->exec("SET time_zone='+00:00'");
function query(string $sql,array $args=[]):PDOStatement{global $db;$sql=preg_replace('/(?<!`)\bkey\b(?!`)/','`key`',$sql);$s=$db->prepare($sql);$s->execute($args);return $s;}
function setting(string $k,string $default=''):string{$v=query('SELECT value FROM settings WHERE `key`=?',[$k])->fetchColumn();return $v===false?$default:(string)$v;}
function set_setting(string $k,string $v):void{query('INSERT INTO settings(`key`,value) VALUES(?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)',[$k,$v]);}
$demoLogin=$demo&&setting('demo_login_enabled','1')==='1';
function sign_in_user(int $id):void{
 $hash=query('SELECT password FROM users WHERE id=?',[$id])->fetchColumn();if(!$hash)throw new RuntimeException('Account unavailable.');
 // Keep the old anonymous session record to avoid late AJAX requests issuing a stale session cookie.
 session_regenerate_id(false);$_SESSION['uid']=$id;$_SESSION['auth_fingerprint']=hash('sha256',$hash);
}
function current_user():?array{
 global $demoLogin;if(empty($_SESSION['uid']))return null;
 $u=query('SELECT id,name,email,role,phone,address,demo,password FROM users WHERE id=?',[$_SESSION['uid']])->fetch();
 if(!$u||(!$demoLogin&&$u['demo'])||!hash_equals(hash('sha256',$u['password']),$_SESSION['auth_fingerprint']??'')){unset($_SESSION['uid'],$_SESSION['auth_fingerprint']);return null;}
 unset($u['password']);return $u;
}

function reply($data,int $code=200):never{if($code>=400&&is_array($data)&&($_GET['action']??'')!=='errors-report'){$data['error']??=$data['message']??'The request failed.';$data['error_id']??=record_error((string)$data['error'],['status'=>$code,'severity'=>$code>=500?'error':'warning','fields'=>array_keys($data['field_errors']??[])]+($GLOBALS['error_exception_context']??[]));}http_response_code($code);header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);exit;}
function require_user(bool $admin=false):array{$u=current_user();if(!$u)reply(['error'=>'Please sign in to continue.'],401);if($admin&&$u['role']!=='admin')reply(['error'=>'Administrator access required.'],403);return $u;}
function phone_normalize(string $p):string{$p=strtr($p,['০'=>'0','১'=>'1','২'=>'2','৩'=>'3','৪'=>'4','৫'=>'5','৬'=>'6','৭'=>'7','৮'=>'8','৯'=>'9']);$p=preg_replace('/[^0-9]/','',$p);if(str_starts_with($p,'00880'))$p=substr($p,4);elseif(str_starts_with($p,'880'))$p=substr($p,2);if(!preg_match('/^01[3-9][0-9]{8}$/',$p))throw new RuntimeException('Enter a valid Bangladesh mobile number.');return $p;}
function customer_details(array $d):array{$n=trim($d['name']??'');$a=trim($d['address']??'');$p=phone_normalize($d['phone']??'');if(mb_strlen($n)<2||mb_strlen($n)>100||mb_strlen($a)<8||mb_strlen($a)>500)throw new RuntimeException('Please enter your full name and complete shipping address.');return [$n,$p,$a];}
function generated_password():string{return 'Bn-'.substr(strtr(base64_encode(random_bytes(12)),'+/','xy'),0,14);}
function rate_limit(string $action,int $limit=20):void{$key=$action.':'.hash('sha256',$_SERVER['REMOTE_ADDR']??'local');$now=time();query('INSERT INTO rate_limits(bucket,attempts,window_start) VALUES(?,1,?) ON DUPLICATE KEY UPDATE attempts=IF(window_start<? ,1,attempts+1),window_start=IF(window_start<?,VALUES(window_start),window_start)',[$key,$now,$now-300,$now-300]);$r=query('SELECT attempts FROM rate_limits WHERE bucket=?',[$key])->fetchColumn();if($r>$limit)reply(['error'=>'Too many attempts. Please wait five minutes.'],429);}
function audit(string $action,array $details=[]):void{query('INSERT INTO audit_log(user_id,action,details) VALUES(?,?,?)',[(int)($_SESSION['uid']??0),$action,json_encode($details)]);}
function order_load(array $o):array{$o['shipment']=query('SELECT id,provider,mode,state,consignment_id,tracking_code,provider_status FROM shipments WHERE order_id=?',[$o['id']])->fetch()?:null;$o['items']=query('SELECT * FROM order_items WHERE order_id=?',[$o['id']])->fetchAll();$o['events']=query('SELECT status,note,created_at FROM order_events WHERE order_id=? ORDER BY id',[$o['id']])->fetchAll();$o['return_info']=return_order_meta($o);return $o;}

require_once __DIR__."/studio.php";

require_once __DIR__."/couriers.php";

require_once __DIR__."/shopping.php";

require_once __DIR__."/returns.php";

require_once __DIR__."/communications.php";
