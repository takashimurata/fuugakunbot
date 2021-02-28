<?php
try {
  $dbh = new PDO('mysql:host=localhost;dbname=' . $_ENV["DB"] . ';DB;charset=utf8mb4', $_ENV["DBNAME"], $_ENV["DBPASS"]);
} catch (PDOException $e) {
  echo 'データベースにアクセスできません。' . $e->getmessage();
  exit;
}
?>
