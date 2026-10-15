<?php
// Development server only. Production uses Apache/PHP and the supplied .htaccess.
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'/');
if(preg_match('~(?:^|/)\.|^/(?:app|database|storage|scripts|docs|tests|resources)(?:/|$)|^/(?:working-flow\.md|README\.md|package(?:-lock)?\.json|tailwind\.config\.js)$~i',$path)){http_response_code(403);exit('Forbidden');}
if($path==='/sw.js'){header('Content-Type: application/javascript');readfile(__DIR__.'/sw.js');return;}
if(str_starts_with($path,'/assets/')){
 if(preg_match('~\.(?:css|js|woff2?|ttf|otf|png|jpe?g|webp|gif|svg|ico)$~i',$path)&&is_file(__DIR__.$path))return false;
 http_response_code(404);header('Content-Type: text/plain');echo 'Asset not found.';return;
}
if($path==='/install.php'){require __DIR__.'/install.php';return;}
if($path==='/api'||$path==='/api.php'){require __DIR__.'/api.php';return;}
require __DIR__.'/index.php';
