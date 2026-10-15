<?php
/** Shared installation helpers: deliberately unavailable over HTTP. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once dirname(__DIR__).'/app/config.php';
function cli_requirements():void{
 if(PHP_VERSION_ID<80200)throw new RuntimeException('PHP 8.2 or newer is required.');
 foreach(['pdo_mysql','mbstring','curl','sodium','gd','exif'] as $x)if(!extension_loaded($x))throw new RuntimeException("Enable the PHP $x extension.");
 $gd=gd_info();foreach(['JPEG Support','PNG Support','WebP Support']as $x)if(empty($gd[$x]))throw new RuntimeException("GD needs $x.");
}
function cli_db():PDO{
 global $env;
 foreach(['DB_DATABASE','DB_USERNAME']as $k)if(empty($env[$k]))throw new RuntimeException("Configure $k in .env first.");
 foreach(['DB_HOST','DB_DATABASE','DB_PORT']as $k)if(str_contains((string)($env[$k]??''),';'))throw new RuntimeException('Invalid database configuration.');
 $db=new PDO('mysql:host='.($env['DB_HOST']??'127.0.0.1').';port='.($env['DB_PORT']??3306).';dbname='.$env['DB_DATABASE'].';charset=utf8mb4',$env['DB_USERNAME'],$env['DB_PASSWORD']??'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
 $db->exec("SET time_zone='+00:00'");return $db;
}
function cli_owner_details(array $options):array{
 $name=trim((string)($options['admin-name']??''));$phone=trim((string)($options['admin-phone']??''));
 if(mb_strlen($name)<2||mb_strlen($name)>100||!preg_match('/^01[3-9][0-9]{8}$/',$phone))throw new RuntimeException('Supply --admin-name="Your name" --admin-phone=01XXXXXXXXX (11 English digits).');
 return [$name,$phone];
}
function cli_create_owner(PDO $db,array $details):string{
 [$name,$phone]=$details;
 $password='Store-'.bin2hex(random_bytes(12));
 $q=$db->prepare('INSERT INTO users(name,email,password,role,phone,address,demo) VALUES(?,NULL,?,\'admin\',?,\'Store administrator\',0)');
 $q->execute([$name,password_hash($password,PASSWORD_DEFAULT),$phone]);
 return $password;
}
function cli_fail(Throwable $e):never{
 // Do not echo DSNs, credentials, SQL data or user details from database exceptions.
 $msg=$e instanceof PDOException?'Database operation failed (SQLSTATE '.$e->getCode().'). Check database access/schema; existing records were not intentionally removed.':$e->getMessage();
 fwrite(STDERR,"ERROR: $msg\n");exit(1);
}
