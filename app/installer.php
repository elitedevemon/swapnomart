<?php
/** Installer helpers; never load application bootstrap or expose existing credentials. */
require_once __DIR__.'/installation.php';
function setup_mutex(){
 $fp=fopen(__DIR__.'/.setup-mutex','c');
 if(!$fp||!flock($fp,LOCK_EX|LOCK_NB))throw new RuntimeException('Another setup request is running. Please wait.');
 chmod(__DIR__.'/.setup-mutex',0600);return $fp;
}
function setup_private_write(string $path,string $body):void{
 $temp=$path.'.'.bin2hex(random_bytes(8)).'.tmp';$fp=fopen($temp,'x');
 if(!$fp)throw new RuntimeException('Cannot write private setup files. Check directory permissions.');
 try{chmod($temp,0600);if(fwrite($fp,$body)!==strlen($body)||!fflush($fp))throw new RuntimeException('Private file write failed.');if(function_exists('fsync'))fsync($fp);}finally{fclose($fp);}
 if(!rename($temp,$path)){unlink($temp);throw new RuntimeException('Cannot publish private configuration.');}
}
function setup_key():string{
 $mutex=setup_mutex();try{
  $file=__DIR__.'/.setup-key.php';
  if(!is_file($file))setup_private_write($file,"<?php http_response_code(404); exit; /*\nPRIVATE SETUP KEY: ".bin2hex(random_bytes(32))."\n*/\n");
  $contents=(string)file_get_contents($file);preg_match('/PRIVATE SETUP KEY: ([a-f0-9]{64})/',$contents,$matches);$key=$matches[1]??'';if(!preg_match('/^[a-f0-9]{64}$/',$key))throw new RuntimeException('Private setup key is damaged. Ask the hosting owner to inspect it.');return $key;
 }finally{flock($mutex,LOCK_UN);fclose($mutex);}
}
function setup_requirements():void{
 if(PHP_VERSION_ID<80200)throw new RuntimeException('PHP 8.2 or newer is required.');
 foreach(['pdo_mysql','mbstring','curl','sodium','gd','exif']as $extension)if(!extension_loaded($extension))throw new RuntimeException('Enable PHP extension: '.$extension);
 $gd=gd_info();foreach(['JPEG Support','PNG Support','WebP Support']as $type)if(empty($gd[$type]))throw new RuntimeException('Enable GD '.$type.'.');
 global $root,$envFile;
 foreach([__DIR__,dirname($envFile)]as $dir)if(!is_dir($dir)||!is_writable($dir))throw new RuntimeException('The app/configuration directory must be writable during setup. Do not use permissions 777.');
 foreach([app_storage_path(),$root.'/public/assets/uploads']as $dir){if(!is_dir($dir)&&!mkdir($dir,0755,true))throw new RuntimeException('Cannot create storage/uploads.');if(!is_writable($dir))throw new RuntimeException('Storage and uploads must be writable.');}
}
function setup_db_details(array $input):array{
 $c=[];foreach(['DB_HOST','DB_PORT','DB_DATABASE','DB_USERNAME']as $k)$c[$k]=trim((string)($input[$k]??''));$c['DB_PASSWORD']=(string)($input['DB_PASSWORD']??'');
 if(!preg_match('/^[a-zA-Z0-9.:-]{1,253}$/',$c['DB_HOST'])||!ctype_digit($c['DB_PORT'])||(int)$c['DB_PORT']<1||(int)$c['DB_PORT']>65535)throw new RuntimeException('Enter a valid MySQL hostname and port.');
 if(!preg_match('/^[a-zA-Z0-9_-]{1,64}$/',$c['DB_DATABASE'])||!preg_match('/^[a-zA-Z0-9_.@$-]{1,80}$/',$c['DB_USERNAME']))throw new RuntimeException('Enter a valid database name and database username.');
 if(strlen($c['DB_PASSWORD'])>1024||str_contains($c['DB_PASSWORD'],"\0"))throw new RuntimeException('Invalid database password.');return $c;
}
function setup_db(array $c):PDO{
 try{$db=new PDO('mysql:host='.$c['DB_HOST'].';port='.$c['DB_PORT'].';dbname='.$c['DB_DATABASE'].';charset=utf8mb4',$c['DB_USERNAME'],$c['DB_PASSWORD'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false,PDO::ATTR_TIMEOUT=>5]);$db->exec("SET time_zone='+00:00'");return $db;}
 catch(PDOException $e){throw new RuntimeException('Cannot connect to MySQL. Check database name, user, password, host, port and granted permissions. Create the empty database in Laragon/cPanel first.');}
}
function setup_empty(PDO $db):void{
 if((int)$db->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE()')->fetchColumn()!==0)throw new RuntimeException('REFUSED: this database is not empty. Existing data will not be overwritten. Use an empty database, or restore the existing store configuration.');
}
function setup_origin(array $input):array{
 $url=rtrim(trim((string)($input['APP_URL']??'')),'/');$p=parse_url($url);
 if(!$p||!filter_var($url,FILTER_VALIDATE_URL)||!in_array($p['scheme']??'',['http','https'],true)||isset($p['user'])||isset($p['pass'])||isset($p['query'])||isset($p['fragment'])||!empty($p['path'])||preg_match('/[\x00-\x20"\\\\]/',$url))throw new RuntimeException('Use the domain-root URL, for example https://shop.example.com (no subfolder).');
 $local=in_array(strtolower($p['host']),['localhost','127.0.0.1','[::1]'],true)||str_ends_with(strtolower($p['host']),'.test');
 if($p['scheme']!=='https'&&!$local)throw new RuntimeException('Public installations require HTTPS. HTTP is permitted only for localhost or a Laragon .test domain.');
 return [$url,$local];
}
function setup_logo(?array $file):?array{
 if(!$file||($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)return null;
 if(($file['error']??1)!==UPLOAD_ERR_OK||!is_uploaded_file($file['tmp_name']))throw new RuntimeException('Logo upload failed. Please choose the image again.');
 if($file['size']>2*1024*1024)throw new RuntimeException('Logo must be 2 MB or smaller.');
 $size=@getimagesize($file['tmp_name']);if(!$size||!in_array($size[2],[IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_WEBP],true)||$size[0]>4000||$size[1]>4000||$size[0]*$size[1]>8000000)throw new RuntimeException('Use a JPEG, PNG or WebP logo, up to 4000 pixels per side and 8 megapixels. SVG is not accepted.');
 return ['tmp'=>$file['tmp_name'],'width'=>$size[0],'height'=>$size[1]];
}
function setup_save_logo(?array $logo):string{
 if(!$logo)return '/assets/round-mark.png';
 $src=@imagecreatefromstring(file_get_contents($logo['tmp']));if(!$src)throw new RuntimeException('Cannot decode the logo.');
 $ratio=min(1,512/$logo['width'],512/$logo['height']);$w=max(1,(int)round($logo['width']*$ratio));$h=max(1,(int)round($logo['height']*$ratio));$dst=imagecreatetruecolor($w,$h);imagealphablending($dst,false);imagesavealpha($dst,true);imagefill($dst,0,0,imagecolorallocatealpha($dst,0,0,0,127));
 imagecopyresampled($dst,$src,0,0,0,0,$w,$h,$logo['width'],$logo['height']);$path='/assets/uploads/setup-logo-'.bin2hex(random_bytes(12)).'.webp';global $root;
 try{if(!imagewebp($dst,$root.'/public'.$path,88))throw new RuntimeException('Cannot save the optimized logo.');chmod($root.'/public'.$path,0644);}finally{imagedestroy($dst);imagedestroy($src);}return $path;
}
function setup_run(array $input,?array $upload):void{
 global $root,$envFile;
 $mutex=setup_mutex();$db=null;$dbLock=null;$started=false;
 try{
  if(installation_state()!=='fresh')throw new RuntimeException('Installation is locked.');
  setup_requirements();$c=setup_db_details($input);[$url,$local]=setup_origin($input);
  $demo=($input['mode']??'clean')==='demo';if($demo&&!$local)throw new RuntimeException('Public demo installs are disabled. Use a local .test/localhost installation for fictional demo data.');
  if(!$local&&$c['DB_PASSWORD']==='')throw new RuntimeException('Use a non-empty private database password on public hosting.');
  $name=trim((string)($input['admin_name']??''));$phone=strtr(trim((string)($input['admin_phone']??'')),['০'=>'0','১'=>'1','২'=>'2','৩'=>'3','৪'=>'4','৫'=>'5','৬'=>'6','৭'=>'7','৮'=>'8','৯'=>'9']);$password=(string)($input['admin_password']??'');$shop=trim((string)($input['shop_name']??''));
  if(mb_strlen($name)<2||mb_strlen($name)>100||!preg_match('/^01[3-9][0-9]{8}$/',$phone))throw new RuntimeException('Enter the administrator name and a valid 11-digit Bangladesh phone number.');
  if(str_contains($password,"\0")||strlen($password)<12||strlen($password)>72||!hash_equals($password,(string)($input['admin_confirm']??'')))throw new RuntimeException('Use matching administrator passwords of 12–72 bytes.');
  if(mb_strlen($shop)<2||mb_strlen($shop)>100)throw new RuntimeException('Shop name must contain 2–100 characters.');
  if($demo&&preg_match('/^019000000(?:0[1-9]|1[0-4])$/',$phone))throw new RuntimeException('Choose your own phone number, not a bundled demo account number.');
  $logo=setup_logo($upload);$db=setup_db($c);$dbLock='setup-'.substr(hash('sha256',$c['DB_DATABASE']),0,48);
  $q=$db->prepare('SELECT GET_LOCK(?,0)');$q->execute([$dbLock]);if(!$q->fetchColumn())throw new RuntimeException('This database is being installed by another request.');setup_empty($db);
  // Seal before any schema mutation. MySQL DDL cannot be rolled back safely.
  setup_private_write(installation_marker(),json_encode(['state'=>'installing','started_at'=>gmdate('c')]));$started=true;
  $sql=file_get_contents($root.'/database/'.($demo?'bonon-demo.sql':'schema.sql'));if($sql===false)throw new RuntimeException('Installation schema is missing.');
  try{$db->exec($sql);}finally{$db->exec('SET FOREIGN_KEY_CHECKS=1');}
  $logoPath=setup_save_logo($logo);$db->beginTransaction();
  $q=$db->prepare("INSERT INTO users(name,phone,password,role,address,demo) VALUES(?,?,?,'admin','Store administrator',0)");$q->execute([$name,$phone,password_hash($password,PASSWORD_DEFAULT)]);
  $branding=['name'=>$shop,'title_en'=>$shop,'title_bn'=>$shop,'logo_light'=>$logoPath,'logo_dark'=>$logoPath];
  $q=$db->prepare('INSERT INTO settings(`key`,value) VALUES(?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)');
  foreach(['store_name'=>$shop,'store_email'=>'','demo_login_enabled'=>$demo?'1':'0','shipping_fee'=>'80','free_shipping'=>'3000','installation_complete'=>gmdate('c'),'studio_branding'=>json_encode($branding,JSON_UNESCAPED_UNICODE)]as $k=>$v)$q->execute([$k,$v]);$db->commit();
  // RAW INI does not unescape quotes/backslashes. Encode only the arbitrary password value.
  $config=['APP_ENV'=>$local?'local':'production','APP_URL'=>$url,'APP_REQUIRE_HTTPS'=>$local?'false':'true','TRUST_PROXY_HEADERS'=>'false','PUSHER_ENABLED'=>'false','demo'=>$demo?'true':'false','DB_HOST'=>$c['DB_HOST'],'DB_PORT'=>$c['DB_PORT'],'DB_DATABASE'=>$c['DB_DATABASE'],'DB_USERNAME'=>$c['DB_USERNAME'],'DB_PASSWORD_B64'=>base64_encode($c['DB_PASSWORD'])];
  $body="; Created by the one-time installer. Private configuration; base64 is NOT encryption.\n";foreach($config as $k=>$v)$body.=$k.'="'.$v.'"'."\n";
  if(file_exists($envFile))throw new RuntimeException('Configuration appeared during setup; refusing to overwrite it.');setup_private_write($envFile,$body);
  setup_private_write(installation_marker(),json_encode(['state'=>'installed','completed_at'=>gmdate('c'),'version'=>1]));
  // Keep the setup key private; locked routes never use it again. No web reset switch exists.
 }catch(Throwable $e){
  if($db&&$db->inTransaction())$db->rollBack();
  if($started&&function_exists('record_error'))record_error('One-time installation interrupted. Inspect private configuration, schema permissions and partial tables.',exception_context($e)+['source'=>'installer','action'=>'setup']);
  if($started)throw new RuntimeException('Installation stopped and remains locked to protect partial data. The hosting owner must inspect the new database/configuration; there is no automatic retry, reset or deletion.');
  throw $e;
 }finally{
  if($db&&$dbLock){try{$q=$db->prepare('SELECT RELEASE_LOCK(?)');$q->execute([$dbLock]);}catch(Throwable $ignored){}}
  flock($mutex,LOCK_UN);fclose($mutex);
 }
}
