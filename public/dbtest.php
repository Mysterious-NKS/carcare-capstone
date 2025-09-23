<?php
require_once dirname(__DIR__).'/app/config/app.php';
ini_set('display_errors','1'); error_reporting(E_ALL); // dev only

try {
  $pdo = DB::pdo();
  echo "<p>DB connected ✔</p>";
  $pdo->query("SELECT 1")->fetch();
  echo "<p>Simple query ran ✔</p>";
} catch (Throwable $e) {
  echo "<p style='color:red'>DB error: ".htmlspecialchars($e->getMessage())."</p>";
}
