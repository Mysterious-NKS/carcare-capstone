<?php
class DB {
  public static function pdo(){
    static $pdo;
    if ($pdo) return $pdo;

    $dsn  = "mysql:host=localhost;port=3306;dbname=carcare_db;charset=utf8mb4";
    $user = "root";
    $pass = ""; 

    $opt = [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    return $pdo = new PDO($dsn, $user, $pass, $opt);
  }
}
