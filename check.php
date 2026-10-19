<?php define('SETUP',1);require 'config.php';
function ok($t,$b,$h=''){echo '<p>'.($b?'&#10004; ':'&#10008; ').e($t).(!$b&&$h?': '.e($h):'').'</p>';}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Setup check</title><link rel="stylesheet" href="assets/style.css"></head><body><main class="post"><h1>Setup check</h1>
<?php
ok('PHP '.PHP_VERSION,PHP_VERSION_ID>=70000,'PHP 7.0 or newer is needed');
ok('PDO MySQL extension',extension_loaded('pdo_mysql'),'enable extension=pdo_mysql in php.ini');
ok('uploads folder is writable',is_writable(__DIR__.'/uploads'),'give the uploads folder write permission');
try{
 $s=new PDO('mysql:host='.DB['host'].';port='.DB['port'].';charset=utf8mb4',DB['user'],DB['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
 ok('MySQL connection ('.DB['host'].':'.DB['port'].')',true);
 if($_SERVER['REQUEST_METHOD']==='POST'){check();$s->exec(file_get_contents(__DIR__.'/database.sql'));migrate(new PDO('mysql:host='.DB['host'].';port='.DB['port'].';dbname='.DB['name'].';charset=utf8mb4',DB['user'],DB['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]));}
 $db=(bool)$s->query('SHOW DATABASES LIKE '.$s->quote(DB['name']))->fetch();
 $tb=false;if($db){$n=$s->query('SHOW TABLES FROM `'.DB['name'].'`')->fetchAll(PDO::FETCH_COLUMN);$tb=!array_diff(['users','entries','settings','skills','messages','files'],$n);}
 ok('Database "'.DB['name'].'" with tables',$tb,'click the button below');
 if(!$tb)echo '<form method="post">'.field().'<button class="btn">Create or update database tables</button></form>';
 else echo '<p class="note">All good. <a href="index.php">Open the website</a> or <a href="admin.php">open the admin</a>. Then delete check.php.</p>';
}catch(PDOException $x){ok('MySQL connection',false,$x->getMessage().' (start MySQL in XAMPP, or fix host, port, user and pass in config.php)');}
?></main></body></html>
