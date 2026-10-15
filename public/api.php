<?php
require_once dirname(__DIR__).'/app/installation.php';
installation_gate();
require_once dirname(__DIR__).'/app/errors.php';
require dirname(__DIR__).'/app/bootstrap.php';
header('Cache-Control: no-store');
$action=$_GET['action']??'catalog';
$method=$_SERVER['REQUEST_METHOD'];
$rawInput=file_get_contents('php://input',false,null,0,$action==='courier-webhook'?65537:2097152);if($action==='courier-webhook'&&strlen($rawInput)>65536)reply(['error'=>'Webhook payload too large.'],413);$input=json_decode($rawInput,true)??$_POST;
$write=['recovery-request','recovery-complete','contact-save','communication-unsubscribe','communications-save','communications-test','communications-process','communications-export','communications-suppress','communications-campaign','communications-cancel','communications-cleanup','credentials-save','demo-login','login','register','logout','checkout','profile','product-save','product-delete','order-status','coupon-save','coupon-delete','settings-save','subscribe','quick-order','password-change','demo-products-remove','demo-login-disable','demo-login-enable','studio-branding','studio-design','studio-text-save','studio-slide-save','studio-slide-delete','studio-carousel-save','media-upload','studio-realtime-save','pusher-test','pusher-auth','notifications-read','showcase-save','errors-report','errors-clear','return-create','return-policy-save','return-update','return-cancel','return-evidence-upload','return-evidence-delete'];
if(in_array($action,$write,true)){
 if($method!=='POST')reply(['error'=>'POST required.'],405);
 if(!hash_equals($_SESSION['csrf'],$_SERVER['HTTP_X_CSRF_TOKEN']??($action==='credentials-save'?($_POST['csrf']??''):'')))reply(['error'=>'Your session expired. Refresh and try again.'],419);
}
try {
require dirname(__DIR__).'/app/communications-api.php';
require dirname(__DIR__).'/app/return-api.php';
require dirname(__DIR__).'/app/error-api.php';
require dirname(__DIR__).'/app/courier-webhook.php';
require dirname(__DIR__).'/app/courier-api.php';
require dirname(__DIR__).'/app/studio-api.php';
switch($action){
case 'qa-environment':if(PHP_SAPI==='cli-server'&&in_array($env['DB_DATABASE']??'',['bonon_courier_test','bonon_test'],true))reply(['isolated'=>true]);reply(['error'=>'Not found.'],404);
case 'catalog':
 $products=query("SELECT p.*,COALESCE(s.units,0) sales_units,COALESCE(s.recent,0) recent_units FROM products p LEFT JOIN (SELECT oi.product_id,SUM(oi.quantity) units,SUM(CASE WHEN o.created_at>=NOW()-INTERVAL 30 DAY AND o.created_at<=NOW() THEN oi.quantity ELSE 0 END) recent FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.status='Delivered'".($demo?'':' AND o.is_demo=0')." GROUP BY oi.product_id) s ON s.product_id=p.id WHERE p.active=1 ORDER BY p.id")->fetchAll();foreach($products as &$p){$p['thumbnail']=product_thumbnail($p['image']);$p['colors']=json_decode($p['colors'],true);$p['sizes']=json_decode($p['sizes'],true);$p['gallery']=json_decode($p['gallery']??'[]',true)?:[$p['image']];}unset($p);
 $settings=query("SELECT key,value FROM settings WHERE key IN ('store_name','store_email','announcement','shipping_fee','free_shipping','announcement_bn','demo_products_removed')")->fetchAll(PDO::FETCH_KEY_PAIR);
 reply(['products'=>$products,'settings'=>$settings,'user'=>current_user(),'demo'=>$demo,'demo_login'=>$demoLogin,'studio'=>studio_public(),'delivery'=>delivery_rules(),'showcase'=>showcase_config(),'return_policy'=>return_policy()]);
case 'demo-login':
 if(!$demoLogin)reply(['error'=>'Demo login is disabled.'],403);
 $role=($input['role']??'user')==='admin'?'admin':'user';$id=query('SELECT id FROM users WHERE demo=1 AND role=? ORDER BY id LIMIT 1',[$role])->fetchColumn();
 sign_in_user((int)$id);reply(['user'=>current_user()]);
case 'login':
 rate_limit('login',30);$identifier=trim($input['identifier']??$input['email']??'');
 if(str_contains($identifier,'@')){$u=query('SELECT * FROM users WHERE email=?',[strtolower($identifier)])->fetch();}else{$phone=phone_normalize($identifier);$u=query('SELECT * FROM users WHERE phone=?',[$phone])->fetch();}
 if(!$u||$u['demo']||!password_verify($input['password']??'',$u['password']))reply(['error'=>'Phone number or password is incorrect.'],422);
 sign_in_user((int)$u['id']);reply(['user'=>current_user()]);
case 'register':
 rate_limit('register',20);[$name,$phone,$address]=customer_details($input);
 if(query('SELECT id FROM users WHERE phone=?',[$phone])->fetchColumn())reply(['error'=>'This phone number already has an account. Please sign in.','login_required'=>true],409);
 $pass=generated_password();query('INSERT INTO users(name,phone,address,password) VALUES(?,?,?,?)',[$name,$phone,$address,password_hash($pass,PASSWORD_DEFAULT)]);$id=$db->lastInsertId();sign_in_user((int)$id);reply(['user'=>current_user(),'generated_password'=>$pass]);
case 'credentials-save':
 $u=require_user();rate_limit('credentials-save',10);$hash=query('SELECT password FROM users WHERE id=?',[$u['id']])->fetchColumn();
 if(phone_normalize((string)($input['identifier']??''))!==$u['phone']||!password_verify((string)($input['password']??''),$hash))reply(['error'=>'Login details could not be confirmed. Sign in again.'],422);
 header('Cache-Control: no-store');header('Location: /#account',true,303);exit;
case 'password-change':
 $u=require_user();rate_limit('password',20);$hash=query('SELECT password FROM users WHERE id=?',[$u['id']])->fetchColumn();
 if(!password_verify($input['current_password']??'',$hash))reply(['error'=>'Your current password is incorrect.'],422);
 $pw=$input['new_password']??'';if(strlen($pw)<10||strlen($pw)>72)reply(['error'=>'Use a password with 10 to 72 bytes.'],422);
 query('UPDATE users SET password=? WHERE id=?',[password_hash($pw,PASSWORD_DEFAULT),$u['id']]);sign_in_user((int)$u['id']);reply(['ok'=>true]);
case 'logout':unset($_SESSION['uid'],$_SESSION['auth_fingerprint']);session_regenerate_id(true);reply(['ok'=>true]);
case 'profile':
 $u=require_user();[$n,$phone,$address]=customer_details($input);
 if($phone!==$u['phone'])reply(['error'=>'Your login phone cannot be changed here. Contact the store.'],422);
 query('UPDATE users SET name=?,address=? WHERE id=?',[$n,$address,$u['id']]);reply(['user'=>current_user()]);
case 'my-orders':$u=require_user();$orders=array_map('order_load',query('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC',[$u['id']])->fetchAll());reply(['orders'=>$orders]);
case 'tracking':
 $u=require_user();$o=query('SELECT * FROM orders WHERE id=? AND user_id=?',[(int)($_GET['id']??0),$u['id']])->fetch();if(!$o)reply(['error'=>'Order not found.'],404);reply(['order'=>order_load($o)]);
case 'coupon':$c=query('SELECT * FROM coupons WHERE code=? AND active=1',[strtoupper(trim($_GET['code']??''))])->fetch();if(!$c)reply(['error'=>'That code is not available. Try WELCOME10 in demo mode.'],422);reply(['coupon'=>$c]);
case 'quick-order':
case 'checkout':
 rate_limit('checkout',30);$contactEmail=comm_email((string)($input['contact_email']??''));[$name,$phone,$address]=customer_details($input);$u=current_user();$pass=null;
 $key=$input['request_key']??'';if(!preg_match('/^[a-f0-9-]{20,64}$/',$key))reply(['error'=>'Please refresh and try again.'],422);
 $items=$input['items']??[];if(!$items||count($items)>100)reply(['error'=>'Your bag is empty or contains too many items.'],422);
 $db->beginTransaction();
 if(!$u){
  $existing=query('SELECT * FROM users WHERE phone=? FOR UPDATE',[$phone])->fetch();
  if($existing){
   if($existing['demo']||!password_verify($input['password']??'',$existing['password'])){$db->rollBack();reply(['error'=>'This phone number already has an account. Enter your password to place this order.','login_required'=>true],409);}
   $u=$existing;
  }else{$pass=generated_password();query('INSERT INTO users(name,phone,address,password) VALUES(?,?,?,?)',[$name,$phone,$address,password_hash($pass,PASSWORD_DEFAULT)]);$u=['id'=>(int)$db->lastInsertId(),'email'=>null];}
 }
 $prior=query('SELECT id,total,user_id FROM orders WHERE request_key=? FOR UPDATE',[$key])->fetch();
 if($prior){if($prior['user_id']!=$u['id'])throw new RuntimeException('Please refresh and try again.');sign_in_user((int)$u['id']);$responseUser=current_user();$db->commit();reply(['order_id'=>$prior['id'],'total'=>$prior['total'],'user'=>$responseUser]);}
 $sub=0;$lines=[];$totals=[];
 usort($items,fn($a,$b)=>(int)($a['id']??0)<=>(int)($b['id']??0));
 foreach($items as $it){
  $p=query('SELECT * FROM products WHERE id=? AND active=1 FOR UPDATE',[(int)($it['id']??0)])->fetch();$qty=filter_var($it['qty']??0,FILTER_VALIDATE_INT);
  if(!$p||!$qty||$qty<1||$qty>20)throw new RuntimeException('One of your items is unavailable.');
  $totals[$p['id']]=($totals[$p['id']]??0)+$qty;if($totals[$p['id']]>$p['stock'])throw new RuntimeException('Requested quantity is not available.');
  $size=$it['size']??'';if(!in_array($size,json_decode($p['sizes'],true),true))throw new RuntimeException('Please choose a valid size.');
  $sub+=$qty*$p['price'];$lines[]=[$p,$qty,$size];
 }
 $coupon=query('SELECT * FROM coupons WHERE code=? AND active=1',[strtoupper(trim($input['coupon']??''))])->fetch();if(!empty($input['coupon'])&&!$coupon)throw new RuntimeException('The discount code is no longer available.');$discount=$coupon?(int)round($sub*$coupon['percent']/100):0;
 $region=$input['shipping_region']??'outside';$deliveryMethod=$input['delivery_method']??'standard';$shipping=delivery_cost($sub,$deliveryMethod,$region);$total=$sub-$discount+$shipping;
 query('INSERT INTO orders(user_id,name,email,phone,address,subtotal,discount,shipping,total,status,tracking_token,request_key,is_demo) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)',[$u['id'],$name,$contactEmail?:($u['email']??null),$phone,$address,$sub,$discount,$shipping,$total,'Pending',bin2hex(random_bytes(24)),$key,$demo?1:0]);$oid=(int)$db->lastInsertId();query('UPDATE orders SET shipping_region=?,delivery_method=?,return_policy_snapshot=? WHERE id=?',[$region,$deliveryMethod,json_encode(return_policy(),JSON_UNESCAPED_UNICODE),$oid]);
 foreach($lines as [$p,$qty,$size]){query('INSERT INTO order_items(order_id,product_id,name,image,price,quantity,size) VALUES(?,?,?,?,?,?,?)',[$oid,$p['id'],$p['name'],$p['image'],$p['price'],$qty,$size]);query('UPDATE products SET stock=stock-? WHERE id=?',[$qty,$p['id']]);}
 query('INSERT INTO order_events(order_id,status,note) VALUES(?,?,?)',[$oid,'Pending',$demo?'Demo order received':'Order received']);
 sign_in_user((int)$u['id']);$responseUser=current_user();$db->commit();try{emit_event('new-order','admin',['order_id'=>$oid,'total'=>$total]);emit_event('order-status','user',['order_id'=>$oid,'status'=>'Pending'],(int)$u['id']);site_changed('inventory');}catch(Throwable $notifyError){record_error('Order confirmed, but a realtime notification failed.',['source'=>'checkout','action'=>'post-confirmation-notify']);}
 try{if(array_key_exists('contact_email',$input)&&comm_ready())comm_save_preferences($responseUser,$input,'checkout-checkbox');}catch(Throwable $contactError){record_error('Order confirmed, but contact preferences could not be saved.',['source'=>'checkout','action'=>'post-confirmation-contact']);}
 reply(['order_id'=>$oid,'total'=>$total,'user'=>$responseUser,'generated_password'=>$pass]);
case 'admin':
 require_user(true);$orders=query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();$orders=array_map('order_load',$orders);
 $contactColumn=comm_ready()?"COALESCE((SELECT cp.email FROM customer_preferences cp WHERE cp.user_id=u.id),u.email)":"u.email";
 $customers=query("SELECT u.id,u.name,$contactColumn email,u.phone,u.address,u.created_at,count(o.id) as orders,COALESCE(SUM(CASE WHEN o.status!='Cancelled' THEN o.total ELSE 0 END),0) as spent FROM users u LEFT JOIN orders o ON u.id=o.user_id WHERE u.role='user' GROUP BY u.id ORDER BY spent DESC")->fetchAll();
 reply(['orders'=>$orders,'customers'=>$customers,'coupons'=>query('SELECT * FROM coupons')->fetchAll(),'settings'=>query("SELECT key,value FROM settings WHERE key!='seeded' AND key NOT LIKE 'private_%'")->fetchAll(PDO::FETCH_KEY_PAIR),'subscribers'=>(int)query(comm_ready()?"SELECT COUNT(*) FROM communication_contacts WHERE channel='email' AND status='active'":'SELECT COUNT(*) FROM subscribers')->fetchColumn(),'demo_login'=>$demoLogin,'demo_product_count'=>(int)query('SELECT COUNT(*) FROM products WHERE is_demo=1')->fetchColumn(),'database'=>'MySQL '.$db->getAttribute(PDO::ATTR_SERVER_VERSION)]);
case 'showcase-save':
 require_user(true);$cfg=showcase_defaults();$cfg['count']=(int)($input['count']??4);if(!in_array($cfg['count'],[4,8]))throw new RuntimeException('Choose four or eight products per shelf.');foreach($cfg['sections']as $k=>$v)$cfg['sections'][$k]=!empty($input['sections'][$k]);set_setting('showcase_settings',json_encode($cfg));audit('showcase-settings');site_changed('showcase');reply(['ok'=>true]);
case 'product-save':
 require_user(true);$id=(int)($input['id']??0);$previous=$id?query('SELECT id,slug FROM products WHERE id=?',[$id])->fetch():null;if($id&&!$previous)reply(['error'=>'This product no longer exists. Reload the catalog.'],404);
 $name=trim((string)($input['name']??''));$cat=$input['category']??'';$price=filter_var($input['price']??null,FILTER_VALIDATE_INT);$stock=filter_var($input['stock']??null,FILTER_VALIDATE_INT);$errors=[];
 if(mb_strlen($name)<2||mb_strlen($name)>160)$errors['name']='Enter a product name with 2–160 characters.';
 if(!in_array($cat,['Women','Men','Kids','Home & Living','Accessories'],true))$errors['category']='Choose a valid product category.';
 if($price===false||$price<1)$errors['price']='Enter a price of at least ৳1, using a whole number.';
 if($stock===false||$stock<0)$errors['stock']='Stock must be a whole number, zero or higher.';
 $nameBn=trim((string)($input['name_bn']??''))?:$name;$description=mb_substr(trim((string)($input['description']??'')),0,3000);$descriptionBn=trim((string)($input['description_bn']??''))?:$description;if(mb_strlen($nameBn)>240)$errors['name_bn']='Keep the Bengali name within 240 characters.';if(mb_strlen($descriptionBn)>3000)$errors['description_bn']='Keep the Bengali description within 3,000 characters.';
 $provided=trim((string)($input['slug']??''));$slug=$provided?:($previous['slug']??trim(strtolower(preg_replace('/[^a-zA-Z0-9]+/','-',$name)),'-'));if(!$provided&&strlen($slug)<3)$slug='product-'.bin2hex(random_bytes(5));$slug=strtolower($slug);if(strlen($slug)<3||strlen($slug)>210||!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/',$slug))$errors['slug']='Use a URL slug with 3–210 English letters, numbers and hyphens, or leave it blank for an automatic URL.';
 if(!isset($errors['slug'])&&query('SELECT id FROM products WHERE slug=? AND id!=?',[$slug,$id])->fetchColumn()){if(!$provided)$slug.='-'.bin2hex(random_bytes(3));else $errors['slug']='This product URL already exists. Choose another slug or leave it blank to generate one.';}
 $gallery=$input['gallery']??[];if(is_string($gallery))$gallery=json_decode($gallery,true);$image=trim((string)($input['image']??''));if(!is_array($gallery)){$errors['gallery']='The photo list is invalid. Select the product photos again.';$gallery=[];}$gallery=array_values(array_unique(array_filter(array_merge($image?[$image]:[],$gallery),fn($v)=>is_string($v)&&$v!=='')));if(!$gallery)$errors['gallery']='Add at least one product photo: upload an image or choose one from the media library.';if(count($gallery)>12)$errors['gallery']='Choose no more than 12 product photos.';
 if($errors)reply(['error'=>'Product could not be saved. '.reset($errors),'field_errors'=>$errors],422);
 $gallery=array_map('normalize_product_image',$gallery);$image=$gallery[0];$old=max(0,(int)($input['old_price']??0));$sizes=array_values(array_unique(array_filter(array_map('trim',explode(',',(string)($input['sizes']??'Standard'))))));if(!$sizes)$sizes=['Standard'];if(count($sizes)>30)throw new RuntimeException('Use no more than 30 sizes.');foreach($sizes as $size)if(mb_strlen($size)>40)throw new RuntimeException('Keep each size label within 40 characters.');
 $vals=[$name,$cat,mb_substr((string)($input['subcategory']??'Essentials'),0,100),$price,$old,$stock,$image,$description,mb_substr((string)($input['tag']??''),0,30),json_encode(['#c7bba6','#737d64','#ad6550']),json_encode($sizes),$nameBn,$descriptionBn,$slug];
 $db->beginTransaction();if($id){$vals[]=$id;query('UPDATE products SET name=?,category=?,subcategory=?,price=?,old_price=?,stock=?,image=?,description=?,tag=?,colors=?,sizes=?,name_bn=?,description_bn=?,slug=? WHERE id=?',$vals);}else{query('INSERT INTO products(name,category,subcategory,price,old_price,stock,image,description,tag,colors,sizes,name_bn,description_bn,slug,is_demo) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,0)',$vals);$id=(int)$db->lastInsertId();}query('UPDATE products SET gallery=?,featured=? WHERE id=?',[json_encode($gallery),!empty($input['featured'])?1:0,$id]);$db->commit();audit('product-saved',['product_id'=>$id]);site_changed('product');reply(['ok'=>true,'id'=>$id,'slug'=>$slug]);
case 'product-delete':require_user(true);query('UPDATE products SET active=0 WHERE id=?',[(int)($input['id']??0)]);site_changed('product');reply(['ok'=>true]);
case 'order-status':
 require_user(true);$status=$input['status']??'';if(!in_array($status,['Pending','Processing','Shipped','Delivered','Cancelled','Returned','Partially delivered'],true))reply(['error'=>'Invalid status.'],422);
 $db->beginTransaction();$order=query('SELECT * FROM orders WHERE id=? FOR UPDATE',[(int)($input['id']??0)])->fetch();if(!$order)throw new RuntimeException('Order not found.');if(query("SELECT id FROM shipments WHERE order_id=? AND mode IN ('live','sandbox') AND state IN ('queued','booking','booked','unknown')",[$order['id']])->fetchColumn())throw new RuntimeException('This order has a courier shipment. Use the Delivery hub to sync, reconcile or request a return.');if($order['status']==='Cancelled'&&$status!=='Cancelled')throw new RuntimeException('Cancelled orders cannot be reopened.');
 if($status==='Cancelled'&&$order['status']!=='Cancelled')foreach(query('SELECT * FROM order_items WHERE order_id=?',[$order['id']])->fetchAll() as $line)query('UPDATE products SET stock=stock+? WHERE id=?',[$line['quantity'],$line['product_id']]);
 if($status==='Delivered'&&$order['status']!=='Delivered')query('UPDATE orders SET delivered_at=COALESCE(delivered_at,UTC_TIMESTAMP()) WHERE id=?',[$order['id']]);query('UPDATE orders SET status=?,courier=?,tracking_number=? WHERE id=?',[$status,mb_substr($input['courier']??'',0,100),mb_substr($input['tracking_number']??'',0,100),$order['id']]);if($status!==$order['status'])query('INSERT INTO order_events(order_id,status,note) VALUES(?,?,?)',[$order['id'],$status,'Updated by store']);audit('order-status',['order_id'=>$order['id'],'status'=>$status]);$db->commit();emit_event('order-status','user',['order_id'=>(int)$order['id'],'status'=>$status],(int)$order['user_id']);emit_event('order-updated','admin',['order_id'=>(int)$order['id'],'status'=>$status]);site_changed('inventory');reply(['ok'=>true]);
case 'coupon-save':require_user(true);$code=strtoupper(trim($input['code']??''));$percent=(int)($input['percent']??0);if(!preg_match('/^[A-Z0-9_-]{3,24}$/',$code)||$percent<1||$percent>70)reply(['error'=>'Use a 3–24 character code and a discount from 1% to 70%.'],422);query('INSERT INTO coupons(code,percent,active) VALUES(?,?,1) ON DUPLICATE KEY UPDATE percent=VALUES(percent),active=1',[$code,$percent]);reply(['ok'=>true]);
case 'coupon-delete':require_user(true);query('DELETE FROM coupons WHERE id=?',[(int)($input['id']??0)]);reply(['ok'=>true]);
case 'settings-save':require_user(true);foreach(['store_name','store_email','announcement','announcement_bn','shipping_fee','free_shipping'] as $k){$v=trim((string)($input[$k]??''));if(in_array($k,['shipping_fee','free_shipping'])&&(!ctype_digit($v)||strlen($v)>7))reply(['error'=>'Shipping values must be non-negative whole numbers.'],422);if($k==='store_email'&&!filter_var($v,FILTER_VALIDATE_EMAIL))reply(['error'=>'Please enter a valid support email.'],422);if(strlen($v)>500)reply(['error'=>'Setting value is too long.'],422);query('INSERT INTO settings(key,value) VALUES(?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)',[$k,$v]);}reply(['ok'=>true]);
case 'demo-products-remove':
 require_user(true);$db->beginTransaction();$count=(int)query('SELECT COUNT(*) FROM products WHERE is_demo=1')->fetchColumn();
 query('DELETE FROM products WHERE is_demo=1');set_setting('demo_products_removed','1');audit('demo-products-removed',['count'=>$count]);$db->commit();reply(['ok'=>true,'removed'=>$count]);
case 'demo-login-disable':
 $u=require_user(true);$pass=null;$db->beginTransaction();
 if($u['demo']){
  [$name,$phone,$address]=customer_details($input);
  if(query('SELECT id FROM users WHERE phone=? AND id!=?',[$phone,$u['id']])->fetchColumn())throw new RuntimeException('This phone number already has an account. Use a different number for the owner.');
  $pass=generated_password();query('UPDATE users SET name=?,phone=?,address=?,email=NULL,password=?,demo=0 WHERE id=?',[$name,$phone,$address,password_hash($pass,PASSWORD_DEFAULT),$u['id']]);
 }
 set_setting('demo_login_enabled','0');audit('demo-login-disabled');$db->commit();sign_in_user((int)$u['id']);reply(['ok'=>true,'user'=>current_user(),'generated_password'=>$pass]);
case 'demo-login-enable':
 $u=require_user(true);if(!$demo)reply(['error'=>'Set demo=true in .env before enabling demo access.'],403);
 if(!query("SELECT id FROM users WHERE demo=1 AND role='admin'")->fetchColumn()){
  query('INSERT INTO users(name,phone,address,password,role,demo) VALUES(?,?,?,?,?,1)',['Demo Administrator','01999999998','Demo studio, Dhaka',password_hash(generated_password(),PASSWORD_DEFAULT),'admin']);
 }
 set_setting('demo_login_enabled','1');audit('demo-login-enabled');reply(['ok'=>true]);
default:reply(['error'=>'Not found.'],404);
}
 }catch(Throwable $e){if($db->inTransaction())$db->rollBack();$GLOBALS['error_exception_context']=exception_context($e)+['hint'=>exception_safe_message($e)];if($e instanceof RuntimeException&&!($e instanceof PDOException))reply(['error'=>$e->getMessage()],422);if($e instanceof PDOException&&$e->getCode()==='23000')reply(['error'=>'An existing record already uses this product URL or account identifier.'],409);reply(['error'=>'The operation failed. Open error details for the technical cause.'],500);}
