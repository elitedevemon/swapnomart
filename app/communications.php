<?php
/** Password recovery and consent-based messaging. OTPs never authorize marketing. */
function comm_ready():bool{static $ready;return $ready??=(int)query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name IN ('customer_preferences','communication_contacts','communication_otps','communication_campaigns','communication_outbox','communication_logs')")->fetchColumn()===6;}
function comm_require_schema():void{if(!comm_ready())throw new RuntimeException('Run php scripts/migrate-communications.php before enabling recovery or messaging.');}
function comm_config():array{return array_replace(['provider'=>'bulksmsbd','sender_id'=>'','otp_enabled'=>false,'marketing_enabled'=>false,'ttl_seconds'=>300,'daily_limit'=>100,'brand'=>'Lifestyle Store'],json_decode(setting('communications_config','{}'),true)?:[]);}
function comm_secret():string{return secret_decrypt(setting('private_sms_key',''));}
function comm_fingerprint():string{$c=comm_config();return hash('sha256',$c['provider'].'|'.$c['sender_id'].'|'.comm_secret());}
function comm_healthy():bool{$h=json_decode(setting('private_sms_health','{}'),true)?:[];return !empty($h['accepted'])&&($h['fingerprint']??'')===comm_fingerprint();}
function comm_enabled(string $purpose):bool{global $demo;if($demo)return false;$c=comm_config();return comm_ready()&&$c['provider']==='bulksmsbd'&&$c['sender_id']!==''&&comm_secret()!==''&&comm_healthy()&&!empty($c[$purpose==='marketing'?'marketing_enabled':'otp_enabled']);}
function comm_mask(string $phone):string{return substr($phone,0,3).'*****'.substr($phone,-3);}
function comm_hmac(string $value):string{return hash_hmac('sha256',$value,secret_key());}
function comm_binding():string{$_SESSION['recovery_binding']??=bin2hex(random_bytes(24));return comm_hmac($_SESSION['recovery_binding']);}
function comm_email(string $email):string{$email=strtolower(trim($email));if($email!==''&&(strlen($email)>190||!filter_var($email,FILTER_VALIDATE_EMAIL)))throw new RuntimeException('Enter a valid optional email address.');return $email;}
function comm_contact(int $uid,string $channel,string $address,bool $optin,string $source):void{
 if($address==='')return;
 if(!$optin){query("UPDATE communication_contacts SET status='unsubscribed',withdrawn_at=UTC_TIMESTAMP() WHERE channel=? AND address=? AND user_id=?",[$channel,$address,$uid]);return;}
 $old=query('SELECT * FROM communication_contacts WHERE channel=? AND address=?',[$channel,$address])->fetch();
 // Never transfer another customer's consent merely because an address was typed.
 if($old&&$old['user_id']!==null&&(int)$old['user_id']!==$uid)return;
 $state=$channel==='sms'?'pending':'active';
 if($old&&$old['status']==='active'&&($channel!=='sms'||$old['verified'])){if($old['user_id']===null)query('UPDATE communication_contacts SET user_id=? WHERE id=? AND user_id IS NULL',[$uid,$old['id']]);return;}
 query("INSERT INTO communication_contacts(user_id,channel,address,status,verified,consent_source,consent_at) VALUES(?,?,?,?,0,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE user_id=VALUES(user_id),status=VALUES(status),verified=0,consent_source=VALUES(consent_source),consent_at=UTC_TIMESTAMP(),withdrawn_at=NULL",[$uid,$channel,$address,$state,$source]);
}
function comm_save_preferences(array $u,array $input,string $source='account'):void{
 comm_require_schema();$email=comm_email((string)($input['contact_email']??''));$checkout=$source==='checkout-checkbox';
 $previous=query('SELECT email FROM customer_preferences WHERE user_id=?',[$u['id']])->fetchColumn();
 if(!$checkout&&$previous&&$previous!==$email)comm_contact((int)$u['id'],'email',$previous,false,$source);
 if(!$checkout||$email!=='')query('INSERT INTO customer_preferences(user_id,email) VALUES(?,?) ON DUPLICATE KEY UPDATE email=VALUES(email)',[$u['id'],$email?:null]);
 // Unchecked checkout boxes mean no new opt-in, not a silent withdrawal of existing preferences.
 if(!$checkout||!empty($input['email_marketing']))comm_contact((int)$u['id'],'email',$email,!empty($input['email_marketing']),$source);
 if(!$checkout||!empty($input['sms_marketing']))comm_contact((int)$u['id'],'sms',$u['phone'],!empty($input['sms_marketing']),$source);
}

function comm_sms_send(string $phone,string $message,string $purpose,?int $outbox=null):array{
 $c=comm_config();$key=comm_secret();if($key===''||$c['sender_id']==='')throw new RuntimeException('Configure the SMS API key and approved sender ID first.');
 $daily=(int)query('SELECT COUNT(*) FROM communication_logs WHERE created_at>=UTC_DATE()')->fetchColumn();if($daily>=(int)$c['daily_limit'])throw new RuntimeException('The daily SMS attempt limit has been reached.');
 $payload=['api_key'=>$key,'senderid'=>$c['sender_id'],'number'=>'88'.phone_normalize($phone),'message'=>$message,'type'=>'text'];
 // Injection is only available in CLI fixtures; never through HTTP or stored settings.
 if(PHP_SAPI==='cli'&&isset($GLOBALS['COMM_SMS_TEST_TRANSPORT']))$r=($GLOBALS['COMM_SMS_TEST_TRANSPORT'])($payload);
 else{
  $ch=curl_init('https://bulksmsbd.net/api/smsapi');curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($payload),CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>15,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded']]);
  $body=curl_exec($ch);$http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$error=curl_errno($ch);curl_close($ch);$json=json_decode((string)$body,true);
  $code=is_array($json)?($json['response_code']??$json['code']??null):(is_numeric(trim((string)$body))?trim((string)$body):null);
  $r=['http'=>$http,'error'=>$error,'code'=>$code];
 }
 $code=preg_match('/^\d{1,6}$/',(string)($r['code']??''))?(string)$r['code']:'';
 $accepted=empty($r['error'])&&($r['http']??0)>=200&&($r['http']??0)<300&&$code==='202';
 $knownRejection=empty($r['error'])&&($r['http']??0)<500&&in_array($code,['1002','1003','1006','1007','1011','1012','1013','1014','1015','1016','1017','1018','1019','1020','1021'],true);
 $outcome=$accepted?'accepted':($knownRejection?'rejected':'unknown');
 query('INSERT INTO communication_logs(outbox_id,purpose,recipient_mask,outcome,provider_code) VALUES(?,?,?,?,?)',[$outbox,$purpose,comm_mask($phone),$outcome,$code?:null]);
 return ['state'=>$outcome,'code'=>$code,'accepted'=>$accepted];
}
function comm_request_otp(string $purpose,string $phone):array{
 comm_require_schema();if(!comm_enabled('otp'))throw new RuntimeException('SMS recovery is not available yet. Contact the store; no password has been changed.');
 $phone=phone_normalize($phone);$u=null;if($purpose==='sms-optin'){$u=require_user();$phone=$u['phone'];}elseif($purpose!=='reset')throw new RuntimeException('Invalid verification purpose.');
 rate_limit('otp-request',5);$lock='otp-'.substr(hash('sha256',$phone),0,45);if(!query('SELECT GET_LOCK(?,2)',[$lock])->fetchColumn())throw new RuntimeException('Please wait before trying again.');
 try{
  $counts=query('SELECT COUNT(*) total,SUM(created_at>=UTC_TIMESTAMP()-INTERVAL 1 HOUR) hourly,SUM(created_at>=UTC_TIMESTAMP()-INTERVAL 60 SECOND) recent FROM communication_otps WHERE phone=? AND created_at>=UTC_TIMESTAMP()-INTERVAL 1 DAY',[$phone])->fetch();
  if($counts['recent']||$counts['hourly']>=3||$counts['total']>=5)throw new RuntimeException('Please wait before requesting another code. Limit: 3 per hour, 5 per day.');
  if($purpose==='reset')$u=query("SELECT id,phone,password FROM users WHERE phone=? AND role='user' AND demo=0",[$phone])->fetch()?:null;
  else $u=query('SELECT id,phone,password FROM users WHERE id=?',[$u['id']])->fetch();
  $id=bin2hex(random_bytes(24));$code=(string)random_int(100000,999999);$ttl=(int)comm_config()['ttl_seconds'];
  global $db;$db->beginTransaction();
  query("UPDATE communication_otps SET state='superseded' WHERE phone=? AND purpose=? AND state IN ('queued','sent')",[$phone,$purpose]);
  query('INSERT INTO communication_otps(id,purpose,user_id,phone,code_hash,binding_hash,password_marker,expires_at) VALUES(?,?,?,?,?,?,?,?)',[$id,$purpose,$u['id']??null,$phone,comm_hmac($id.'|'.$purpose.'|'.$code),comm_binding(),$u?hash('sha256',$u['password']):'',gmdate('Y-m-d H:i:s',time()+$ttl)]);
  if($u){$brand=preg_replace('/[^\p{L}\p{N} ._-]/u','',comm_config()['brand']);$text=$brand.($purpose==='reset'?' পাসওয়ার্ড পরিবর্তনের কোড: ':' প্রচারমূলক SMS সম্মতির কোড: ').$code.'। মেয়াদ '.(int)($ttl/60).' মিনিট। কাউকে কোডটি দেবেন না।';
   query('INSERT INTO communication_outbox(purpose,otp_id,recipient,payload) VALUES(?,?,?,?)',[$purpose,$id,$phone,secret_encrypt($text)]);}
  $db->commit();return ['ok'=>true,'challenge'=>$id,'expires_in'=>$ttl,'resend_after'=>60,'message'=>'If this request is eligible, a code will be sent. Check the phone and keep this browser open.'];
 }catch(Throwable $e){global $db;if($db->inTransaction())$db->rollBack();throw $e;}finally{query('SELECT RELEASE_LOCK(?)',[$lock]);}
}
function comm_verify_otp(array $input):array{
 comm_require_schema();rate_limit('otp-verify',20);$id=(string)($input['challenge']??'');$code=(string)($input['code']??'');$purpose=(string)($input['purpose']??'reset');
 if(!preg_match('/^[a-f0-9]{48}$/',$id)||!preg_match('/^[0-9]{6}$/',$code)||!in_array($purpose,['reset','sms-optin'],true))throw new RuntimeException('The code is invalid or expired.');
 if(!comm_enabled('otp'))throw new RuntimeException('SMS recovery is currently disabled.');
 $password=(string)($input['new_password']??'');if($purpose==='reset'&&(strlen($password)<10||strlen($password)>72))throw new RuntimeException('Use a new password with 10–72 bytes. A long, unique passphrase is recommended.');
 $owner=$purpose==='sms-optin'?require_user():null;
 global $db;$db->beginTransaction();try{
  $r=query('SELECT * FROM communication_otps WHERE id=? FOR UPDATE',[$id])->fetch();
  if(!$r||$r['purpose']!==$purpose||!hash_equals($r['binding_hash'],comm_binding())||$r['state']!=='sent'||$r['attempts']>=5||strtotime($r['expires_at'].' UTC')<time())throw new RuntimeException('The code is invalid or expired. Request a new code when available.');
  if(!hash_equals($r['code_hash'],comm_hmac($id.'|'.$purpose.'|'.$code))){query("UPDATE communication_otps SET attempts=attempts+1,state=IF(attempts>=5,'locked',state) WHERE id=?",[$id]);$db->commit();throw new RuntimeException('The code is invalid or expired.');}
  $u=query('SELECT id,role,phone,password,demo FROM users WHERE id=? FOR UPDATE',[$r['user_id']])->fetch();
  if(!$u||$u['phone']!==$r['phone']||!hash_equals($r['password_marker'],hash('sha256',$u['password']))||($purpose==='reset'&&($u['role']!=='user'||$u['demo']))||($owner&&(int)$owner['id']!==(int)$u['id']))throw new RuntimeException('The code is invalid or expired.');
  query("UPDATE communication_otps SET state='used',used_at=UTC_TIMESTAMP(),code_hash='' WHERE id=?",[$id]);
  if($purpose==='reset'){
   query('UPDATE users SET password=? WHERE id=?',[password_hash($password,PASSWORD_DEFAULT),$u['id']]);
   query("UPDATE communication_otps SET state='superseded' WHERE user_id=? AND state IN ('queued','sent')",[$u['id']]);
  }else{
   // Verification is purpose-specific and also records an explicit SMS marketing consent.
   query("INSERT INTO communication_contacts(user_id,channel,address,status,verified,consent_source,consent_at) VALUES(?,'sms',?,'active',1,'sms-otp',UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE user_id=VALUES(user_id),status='active',verified=1,consent_source='sms-otp',consent_at=UTC_TIMESTAMP(),withdrawn_at=NULL",[$u['id'],$u['phone']]);
  }
  $db->commit();try{audit($purpose==='reset'?'customer-password-reset':'sms-consent-verified',['user_id'=>(int)$u['id']]);}catch(Throwable $auditError){record_error('Verified account action completed, but its audit write failed.',['source'=>'communications','action'=>'post-verification-audit']);}
  if($purpose==='reset'){unset($_SESSION['uid'],$_SESSION['auth_fingerprint'],$_SESSION['recovery_binding']);session_regenerate_id(true);$_SESSION['csrf']=bin2hex(random_bytes(24));}
  return ['ok'=>true,'csrf'=>$_SESSION['csrf']];
 }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
}
function comm_unsubscribe_token(int $id):string{return comm_hmac('unsubscribe|'.$id);}
function comm_process_queue(int $limit=10):array{
 comm_require_schema();if(!query("SELECT GET_LOCK('store-communications-worker',0)")->fetchColumn())return ['processed'=>0,'busy'=>true];
 global $db;$done=0;
 try{
  // Never retry a send whose outcome is unknown after a worker crash.
  query("UPDATE communication_outbox SET state='unknown',payload=NULL,finished_at=UTC_TIMESTAMP() WHERE state='processing' AND claimed_at<UTC_TIMESTAMP()-INTERVAL 5 MINUTE");
  query("UPDATE communication_otps SET state='expired',code_hash='' WHERE expires_at<UTC_TIMESTAMP() AND state IN ('queued','sent')");
  query("UPDATE communication_outbox o LEFT JOIN communication_otps t ON t.id=o.otp_id SET o.state='cancelled',o.payload=NULL,o.finished_at=UTC_TIMESTAMP() WHERE o.state='queued' AND o.otp_id IS NOT NULL AND (t.id IS NULL OR t.state!='queued' OR t.expires_at<UTC_TIMESTAMP())");
  for($i=0;$i<max(1,min(20,$limit));$i++){
   $allowOtp=comm_enabled('otp');$allowMarketing=comm_enabled('marketing');if(!$allowOtp&&!$allowMarketing)break;
   $eligible=$allowOtp&&$allowMarketing?'1=1':($allowMarketing?"purpose='marketing'":"purpose!='marketing'");
   $db->beginTransaction();$job=query("SELECT * FROM communication_outbox WHERE state='queued' AND $eligible ORDER BY (purpose='marketing'),id LIMIT 1 FOR UPDATE")->fetch();
   if(!$job){$db->commit();break;}
   if(!comm_enabled($job['purpose']==='marketing'?'marketing':'otp')){$db->commit();break;}
   if((int)query('SELECT COUNT(*) FROM communication_logs WHERE created_at>=UTC_DATE()')->fetchColumn()>=(int)comm_config()['daily_limit']){$db->commit();break;}
   $allowed=true;
   if($job['otp_id']){$otp=query('SELECT * FROM communication_otps WHERE id=?',[$job['otp_id']])->fetch();$allowed=$otp&&$otp['state']==='queued'&&strtotime($otp['expires_at'].' UTC')>=time();}
   if($job['contact_id']){$c=query('SELECT * FROM communication_contacts WHERE id=?',[$job['contact_id']])->fetch();$campaign=query('SELECT state FROM communication_campaigns WHERE id=?',[$job['campaign_id']])->fetchColumn();$allowed=$c&&$c['status']==='active'&&$c['verified']&&$campaign==='queued';}
   if(!$allowed){query("UPDATE communication_outbox SET state='cancelled',payload=NULL,finished_at=UTC_TIMESTAMP() WHERE id=?",[$job['id']]);$db->commit();continue;}
   query("UPDATE communication_outbox SET state='processing',claimed_at=UTC_TIMESTAMP() WHERE id=?",[$job['id']]);$db->commit();
   try{$message=secret_decrypt($job['payload']??'');if($message==='')throw new RuntimeException('Message payload unavailable.');$r=comm_sms_send($job['recipient'],$message,$job['purpose'],(int)$job['id']);}
   catch(Throwable $e){$r=['state'=>'unknown','code'=>'','accepted'=>false];record_error('SMS send not confirmed. Inspect gateway balance/configuration and provider records before any new send.',['source'=>'sms','action'=>'send']);}
   query('UPDATE communication_outbox SET state=?,provider_code=?,payload=NULL,finished_at=UTC_TIMESTAMP() WHERE id=?',[$r['state'],$r['code']?:null,$job['id']]);
   if($job['otp_id'])query("UPDATE communication_otps SET state=? WHERE id=? AND state='queued'",[$r['accepted']?'sent':'failed',$job['otp_id']]);
   $done++;
  }
  set_setting('private_communications_worker_seen',gmdate('c'));return ['processed'=>$done];
 }finally{if($db->inTransaction())$db->rollBack();query("SELECT RELEASE_LOCK('store-communications-worker')");}
}
