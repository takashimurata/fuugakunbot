<?php
require './vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
try {
  $dbh = new PDO('mysql:host=localhost;dbname=' . $_ENV["DB"] . ';DB;charset=utf8mb4', $_ENV["DBNAME"], $_ENV["DBPASS"]);
} catch (PDOException $e) {
  echo 'データベースにアクセスできません。' . $e->getmessage();
  exit;
}
$sql = 'SELECT * FROM `users` WHERE `id` = 1';
$stmt = $dbh->prepare($sql);
$stmt->execute();
$test = $stmt->fetchAll(PDO::FETCH_ASSOC);
var_dump($test);
?>
