<?php
class DB{ public static function pdo(){ static $pdo; if($pdo) return $pdo;
$dsn="mysql:host=127.0.0.1;dbname=carcare_db;charset=utf8mb4"; $u="root"; $p="";
$opt=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
return $pdo=new PDO($dsn,$u,$p,$opt); } }
