<?php
if($action==='courier-webhook'){
 if($method!=='POST')reply(['error'=>'POST required.'],405);
 $p=$_GET['provider']??'';if(!in_array($p,['pathao','steadfast']))reply(['error'=>'Unknown provider.'],404);
 $c=courier_config($p);$s=courier_secrets($p);if(!$c['webhooks_enabled']||$c['mode']==='simulation'||empty($s['webhook_secret']))reply(['error'=>'Webhook is disabled.'],403);
 $given=$p==='pathao'?($_SERVER['HTTP_X_PATHAO_SIGNATURE']??''):preg_replace('/^Bearer\s+/i','',$_SERVER['HTTP_AUTHORIZATION']??'');if(!hash_equals($s['webhook_secret'],$given))reply(['error'=>'Invalid webhook signature.'],401);
 if($p==='pathao'&&!empty($s['webhook_response_value']))header($c['webhook_response_header'].': '.$s['webhook_response_value']);
 if(($input['event']??'')==='webhook_integration'&&$p==='pathao')reply(['accepted'=>true],202);
 $clean=[];foreach(['consignment_id','merchant_order_id','invoice','order_status','order_status_slug','status','delivery_status','event','updated_at','timestamp']as $k)if(isset($input[$k])&&is_scalar($input[$k]))$clean[$k]=mb_substr((string)$input[$k],0,150);
 if(empty($clean['consignment_id']))reply(['error'=>'Missing consignment ID.'],422);$clean['_fingerprint']=courier_fingerprint($p);ksort($clean);$body=json_encode($clean,JSON_UNESCAPED_UNICODE);query('INSERT IGNORE INTO courier_webhooks(provider,event_hash,payload) VALUES(?,?,?)',[$p,hash('sha256',$p.$body),$body]);reply(['accepted'=>true],$p==='pathao'?202:200);
}
