<?php
switch($action){
case 'recovery-options':reply(['available'=>comm_enabled('otp'),'ready'=>comm_ready(),'message'=>'Recovery uses an SMS code. Signup does not require verification.']);
case 'recovery-request':reply(comm_request_otp((string)($input['purpose']??'reset'),(string)($input['phone']??'')));
case 'recovery-complete':reply(comm_verify_otp($input));
case 'contact-preferences':
 $u=require_user();comm_require_schema();$p=query('SELECT email FROM customer_preferences WHERE user_id=?',[$u['id']])->fetch()?:['email'=>''];$contacts=query('SELECT id,channel,address,status,verified FROM communication_contacts WHERE user_id=?',[$u['id']])->fetchAll();reply(['email'=>$p['email']??'','contacts'=>$contacts,'sms_verification'=>comm_enabled('otp')]);
case 'contact-save':
 $u=require_user();comm_save_preferences($u,$input);reply(['ok'=>true]);
case 'subscribe':
 comm_require_schema();rate_limit('newsletter',5);$email=comm_email((string)($input['email']??''));if(!$email||empty($input['consent']))throw new RuntimeException('Enter an email and explicitly agree to receive marketing emails.');
 // Single opt-in collection only; email ownership is not claimed. Exports are labelled unverified.
 $old=query("SELECT id,status FROM communication_contacts WHERE channel='email' AND address=?",[$email])->fetch();
 if(!$old)query("INSERT INTO communication_contacts(channel,address,status,verified,consent_source,consent_at) VALUES('email',?,'active',0,'newsletter-checkbox',UTC_TIMESTAMP())",[$email]);
 // An anonymous request must never reverse an earlier unsubscribe.
 reply(['ok'=>true,'message'=>'Your preference has been received. Previously unsubscribed addresses are not automatically reactivated.']);
case 'communication-unsubscribe':
 comm_require_schema();rate_limit('unsubscribe',30);$id=(int)($input['id']??0);$token=(string)($input['token']??'');if($id>0&&hash_equals(comm_unsubscribe_token($id),$token))query("UPDATE communication_contacts SET status='unsubscribed',withdrawn_at=UTC_TIMESTAMP() WHERE id=?",[$id]);reply(['ok'=>true]);
case 'communications-admin':
 require_user(true);$ready=comm_ready();$cfg=comm_config();reply(['ready'=>$ready,'config'=>$cfg,'secret_set'=>comm_secret()!=='','health'=>json_decode(setting('private_sms_health','null'),true),'worker_seen'=>setting('private_communications_worker_seen',''),'can_send'=>!$demo&&!require_user()['demo'],
 'contacts'=>$ready?query('SELECT id,user_id,channel,address,status,verified,consent_source,consent_at FROM communication_contacts ORDER BY id DESC LIMIT 500')->fetchAll():[],
 'logs'=>$ready?query('SELECT * FROM communication_logs ORDER BY id DESC LIMIT 100')->fetchAll():[],
 'campaigns'=>$ready?query("SELECT c.id,c.title,c.state,c.created_at,COUNT(o.id) recipients,SUM(o.state='accepted') accepted,SUM(o.state='unknown') unknown_count,SUM(o.state='queued') queued FROM communication_campaigns c LEFT JOIN communication_outbox o ON o.campaign_id=c.id GROUP BY c.id ORDER BY c.id DESC LIMIT 50")->fetchAll():[],
 'queue'=>$ready?query('SELECT state,COUNT(*) total FROM communication_outbox GROUP BY state')->fetchAll():[],
 'otp_summary'=>$ready?query('SELECT purpose,state,COUNT(*) total FROM communication_otps WHERE created_at>=UTC_DATE() GROUP BY purpose,state')->fetchAll():[]]);
case 'communications-save':
 $u=require_user(true);if($u['demo'])throw new RuntimeException('Use a private non-demo administrator to configure SMS credentials.');comm_require_schema();$old=comm_config();$c=$old;
 $c['provider']='bulksmsbd';$c['sender_id']=trim((string)($input['sender_id']??''));if(!preg_match('/^[a-zA-Z0-9 _-]{0,30}$/',$c['sender_id']))throw new RuntimeException('Use your approved sender ID.');
 $c['brand']=mb_substr(trim((string)($input['brand']??'Lifestyle Store')),0,60);$c['ttl_seconds']=max(180,min(600,(int)($input['ttl_seconds']??300)));$c['daily_limit']=max(1,min(10000,(int)($input['daily_limit']??100)));
 $c['otp_enabled']=!empty($input['otp_enabled']);$c['marketing_enabled']=!empty($input['marketing_enabled']);
 $new=trim((string)($input['api_key']??''));if($new!==''&&(strlen($new)<8||strlen($new)>250))throw new RuntimeException('Check the SMS API key.');
 if(!empty($input['clear_key'])){set_setting('private_sms_key','');$c['otp_enabled']=false;$c['marketing_enabled']=false;}
 if($new!=='')set_setting('private_sms_key',secret_encrypt($new));
 $changed=$new!==''||$old['sender_id']!==$c['sender_id']||!empty($input['clear_key']);if($changed){$c['otp_enabled']=false;$c['marketing_enabled']=false;set_setting('private_sms_health','{}');}
 if(($c['otp_enabled']||$c['marketing_enabled'])&&!comm_healthy())throw new RuntimeException('Save credentials, send one explicit test SMS, then enable OTP or marketing.');
 set_setting('communications_config',json_encode($c,JSON_UNESCAPED_UNICODE));audit('communications-settings');reply(['ok'=>true,'test_required'=>$changed]);
case 'communications-test':
 $u=require_user(true);comm_require_schema();if($demo||$u['demo'])throw new RuntimeException('Live SMS tests require demo=false and a private non-demo administrator.');if(($input['confirm']??'')!=='SEND TEST SMS')throw new RuntimeException('Confirm the chargeable test SMS.');rate_limit('sms-test',3);
 $phone=phone_normalize((string)($input['phone']??''));if(!query("SELECT GET_LOCK('store-communications-worker',0)")->fetchColumn())throw new RuntimeException('The worker is busy. Try later.');
 try{$r=comm_sms_send($phone,'স্টোরের SMS সংযোগ পরীক্ষা। এই বার্তায় কোনো পাসওয়ার্ড বা যাচাইকরণ কোড নেই।','test');set_setting('private_sms_health',json_encode(['accepted'=>$r['accepted'],'fingerprint'=>comm_fingerprint(),'time'=>gmdate('c'),'code'=>$r['code']]));}finally{query("SELECT RELEASE_LOCK('store-communications-worker')");}
 reply(['ok'=>$r['accepted'],'state'=>$r['state'],'code'=>$r['code'],'message'=>'Accepted means gateway submission, not confirmed handset delivery.']);
case 'communications-process':
 $u=require_user(true);if($demo||$u['demo'])throw new RuntimeException('Real messaging requires demo=false and a private administrator.');if(($input['confirm']??'')!=='SEND QUEUED')throw new RuntimeException('Confirm sending queued messages.');reply(comm_process_queue(5));
case 'communications-export':
 require_user(true);comm_require_schema();$channel=($input['channel']??'email')==='sms'?'sms':'email';
 $rows=query("SELECT id,address,consent_source,consent_at,verified FROM communication_contacts WHERE channel=? AND status='active'".($channel==='sms'?' AND verified=1':'')." ORDER BY id",[$channel])->fetchAll();$base=rtrim((string)($env['APP_URL']??''),'/');
 foreach($rows as &$row){$row['unsubscribe_url']=$base.'/#unsubscribe?id='.$row['id'].'&token='.comm_unsubscribe_token((int)$row['id']);$row['verification']=$row['verified']?'verified':'unverified - verify with your email platform';unset($row['id']);}unset($row);audit('marketing-audience-export',['channel'=>$channel,'count'=>count($rows)]);reply(['rows'=>$rows,'channel'=>$channel]);
case 'communications-suppress':
 require_user(true);comm_require_schema();query("UPDATE communication_contacts SET status='unsubscribed',withdrawn_at=UTC_TIMESTAMP() WHERE id=?",[(int)($input['id']??0)]);audit('marketing-unsubscribe');reply(['ok'=>true]);
case 'communications-campaign':
 $u=require_user(true);comm_require_schema();if($demo||$u['demo']||!comm_enabled('marketing'))throw new RuntimeException('Activate and test the SMS gateway with a private administrator before a marketing campaign.');
 if(($input['confirm']??'')!=='QUEUE MARKETING')throw new RuntimeException('Confirm the consent-based marketing campaign.');
 $base=rtrim((string)($env['APP_URL']??''),'/');$host=parse_url($base,PHP_URL_HOST);if(parse_url($base,PHP_URL_SCHEME)!=='https'||!$host||in_array($host,['localhost','127.0.0.1'])||str_ends_with($host,'.test')||str_ends_with($host,'.example'))throw new RuntimeException('Set your real public HTTPS APP_URL for the mandatory unsubscribe link.');
 $title=trim((string)($input['title']??''));$message=trim((string)($input['message']??''));if(mb_strlen($title)<2||mb_strlen($title)>100||mb_strlen($message)<5||mb_strlen($message)>220)throw new RuntimeException('Use a title of 2–100 and message of 5–220 characters. The unsubscribe link is appended; multiple SMS segments may be billed.');
 $key=(string)($input['request_key']??'');if(!preg_match('/^[a-f0-9]{48}$/',$key))throw new RuntimeException('Refresh the campaign form.');
 $old=query('SELECT id FROM communication_campaigns WHERE request_key=?',[$key])->fetchColumn();if($old)reply(['ok'=>true,'id'=>$old]);
 $ids=array_values(array_unique(array_map('intval',$input['contacts']??[])));if(!$ids||count($ids)>100)throw new RuntimeException('Select 1–100 verified, opted-in SMS contacts.');
 $db->beginTransaction();$contacts=query("SELECT * FROM communication_contacts WHERE id IN (".implode(',',array_fill(0,count($ids),'?')).") AND channel='sms' AND status='active' AND verified=1 FOR UPDATE",$ids)->fetchAll();if(count($contacts)!==count($ids))throw new RuntimeException('Some contacts are no longer eligible. Refresh the audience.');
 query('INSERT INTO communication_campaigns(request_key,title,message,created_by) VALUES(?,?,?,?)',[$key,$title,$message,$u['id']]);$id=(int)$db->lastInsertId();
 foreach($contacts as $c){$body=$message.' Stop: '.$base.'/#unsubscribe?id='.$c['id'].'&token='.comm_unsubscribe_token((int)$c['id']);query("INSERT INTO communication_outbox(purpose,campaign_id,contact_id,recipient,payload) VALUES('marketing',?,?,?,?)",[$id,$c['id'],$c['address'],secret_encrypt($body)]);}
 $db->commit();audit('sms-campaign-queued',['campaign_id'=>$id,'recipients'=>count($contacts)]);reply(['ok'=>true,'id'=>$id,'queued'=>count($contacts)]);
case 'communications-cancel':
 require_user(true);comm_require_schema();$id=(int)($input['id']??0);query("UPDATE communication_campaigns SET state='cancelled' WHERE id=?",[$id]);query("UPDATE communication_outbox SET state='cancelled',payload=NULL,finished_at=UTC_TIMESTAMP() WHERE campaign_id=? AND state='queued'",[$id]);reply(['ok'=>true,'message'=>'Queued messages cancelled. An in-flight or already accepted send cannot be recalled.']);
case 'communications-cleanup':
 require_user(true);comm_require_schema();query("DELETE FROM communication_outbox WHERE otp_id IS NOT NULL AND created_at<UTC_TIMESTAMP()-INTERVAL 1 DAY");query('DELETE FROM communication_otps WHERE created_at<UTC_TIMESTAMP()-INTERVAL 7 DAY');query("DELETE FROM communication_outbox WHERE state!='queued' AND created_at<UTC_TIMESTAMP()-INTERVAL 30 DAY");query('DELETE FROM communication_logs WHERE created_at<UTC_TIMESTAMP()-INTERVAL 90 DAY');reply(['ok'=>true]);
}
