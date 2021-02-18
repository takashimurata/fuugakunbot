<?php
//.envの呼び出し
$vendor_path = __DIR__ . '/vendor/autoload.php';
require $vendor_path;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
  $dbh = new PDO('mysql:host=localhost;dbname=' . $_ENV["DB"] . ';DB;charset=utf8mb4', $_ENV["DBNAME"], $_ENV["DBPASS"]);
} catch (PDOException $e) {
  echo 'データベースにアクセスできません。' . $e->getmessage();
  exit;
}
?>
