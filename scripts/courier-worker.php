<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}

require dirname(__DIR__).'/app/bootstrap.php';
session_write_close();
if(PHP_SAPI!=='cli')exit(1);
if(!query("SELECT GET_LOCK('bonon_courier_worker_".preg_replace('/[^a-zA-Z0-9_]/','',$env['DB_DATABASE']??'bonon')."',0)")->fetchColumn()){echo "Worker already running.\n";exit;}
$once=in_array('--once',$argv,true);echo "Storefront courier worker ready. Simulation never contacts a courier.\n";
do{
 try{
 set_setting('private_courier_worker_seen',date(DATE_ATOM));
 query("UPDATE shipments SET state='unknown',last_error='Worker interrupted during booking. Reconcile in the merchant portal; do not retry.' WHERE state='booking' AND updated_at<NOW()-INTERVAL 2 MINUTE");
 query("UPDATE courier_actions SET state='unknown',last_error='Action interrupted. Check the courier portal; never resend blindly.' WHERE state='processing' AND updated_at<NOW()-INTERVAL 2 MINUTE");
 foreach(query("SELECT * FROM shipments WHERE state='queued' ORDER BY id LIMIT 5")->fetchAll()as $s)process_shipment($s);
 foreach(query("SELECT * FROM courier_webhooks WHERE state='queued' ORDER BY id LIMIT 30")->fetchAll()as $e){$d=json_decode($e['payload'],true);$s=query("SELECT * FROM shipments WHERE provider=? AND mode IN ('live','sandbox') AND (consignment_id=? OR reference=?) LIMIT 1",[$e['provider'],$d['consignment_id'],$d['merchant_order_id']??$d['invoice']??''])->fetch();if($s&&$s['account_fingerprint']===$d['_fingerprint']){if(!$s['consignment_id']&&in_array($s['state'],['unknown','booking','queued'])){query("UPDATE shipments SET consignment_id=?,tracking_code=?,state='booked' WHERE id=?",[$d['consignment_id'],$d['consignment_id'],$s['id']]);query('UPDATE orders SET courier=?,tracking_number=? WHERE id=?',[COURIER_NAMES[$s['provider']],$d['consignment_id'],$s['order_id']]);}if(!$s['consignment_id']||$s['consignment_id']===$d['consignment_id'])update_shipment_status($s,$d['order_status_slug']??$d['delivery_status']??$d['status']??$d['order_status']??$d['event']??'Updated',$d['timestamp']??$d['updated_at']??null);}query('UPDATE courier_webhooks SET state=? WHERE id=?',[$s?'processed':'unmatched',$e['id']]);}
 foreach(query("SELECT * FROM courier_actions WHERE state='queued' ORDER BY id LIMIT 3")->fetchAll()as $a){$d=json_decode($a['payload'],true);try{courier_ready('steadfast',true);if(($d['_fingerprint']??'')!==courier_fingerprint('steadfast'))throw new RuntimeException('The courier account changed. Check before making this request.');unset($d['_fingerprint']);query("UPDATE courier_actions SET state='processing' WHERE id=?",[$a['id']]);$r=carrier_request('steadfast','POST',$a['kind']==='return'?'/create_return_request':'/create_pickup_request',$d);$id=$r['data']['data']['id']??$r['data']['id']??'';$state=$r['ok']&&$id?'accepted':($r['ambiguous']||$r['ok']?'unknown':'rejected');query('UPDATE courier_actions SET state=?,remote_id=?,last_error=? WHERE id=?',[$state,(string)$id,$state==='accepted'?'':$r['message'],$a['id']]);courier_log('steadfast',$a['kind'],$r['status'],$state,$r['message'],$a['shipment_id']?(int)$a['shipment_id']:null);}catch(Throwable $ex){query("UPDATE courier_actions SET state=IF(state='processing','unknown','rejected'),last_error=? WHERE id=?",[mb_substr($ex->getMessage(),0,650),$a['id']]);}}
 $mins=(int)delivery_rules()['poll_minutes'];foreach(query("SELECT s.* FROM shipments s JOIN orders o ON o.id=s.order_id WHERE s.state='booked' AND s.mode IN ('live','sandbox') AND o.status NOT IN ('Delivered','Cancelled','Returned') AND (s.last_sync_at IS NULL OR s.last_sync_at<NOW()-INTERVAL $mins MINUTE) ORDER BY s.last_sync_at LIMIT 3")->fetchAll()as $s){try{sync_shipment($s);}catch(Throwable $ex){query('UPDATE shipments SET last_sync_at=NOW(),last_error=? WHERE id=?',[mb_substr($ex->getMessage(),0,650),$s['id']]);}}
 query('DELETE FROM courier_webhooks WHERE created_at<NOW()-INTERVAL 30 DAY');query('DELETE FROM courier_logs WHERE created_at<NOW()-INTERVAL 90 DAY');
 }catch(Throwable $e){error_log('Courier worker: '.$e->getMessage());}
 if(!$once)sleep(4);
}while(!$once);
