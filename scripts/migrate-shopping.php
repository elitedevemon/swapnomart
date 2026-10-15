<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}

require dirname(__DIR__).'/app/bootstrap.php';
if(!query("SHOW COLUMNS FROM products LIKE 'featured'")->fetch())$db->exec('ALTER TABLE products ADD COLUMN featured TINYINT NOT NULL DEFAULT 0');
if(!setting('shopping_installed')){query('UPDATE products SET featured=1 WHERE is_demo=1 AND id IN (1,2,4,8,12,18,20,24)');set_setting('shopping_installed','1');}
echo "Shopping showcase schema ready.\n";
