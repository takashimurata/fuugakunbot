<?php
//DB接続
require_once('./db_connection.php');

//.envの呼び出し
require './vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$search_time = date("Y-m-d K:i", strtotime("-10 minute"));
$sql = 'SELECT `line_accesstoken` FROM `users` WHERE :search_time <= `departure_time`';
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':search_time', $search_time);
$stmt->execute();
$fetch_line_accesstoken = $stmt->fetchall(PDO::FETCH_ASSOC);
$line_accesstoken = array_column($fetch_line_accesstoken, 'line_accesstoken');
//TODO::リマインド後、departure_timeを削除。複数ユーザーを想定。SQLは最小限に。
//TODO::cron処理を5分ごとに流す。

/*
   var_dump($line_accesstoken);
 */

$reply_message = [
  'to' => $line_accesstoken,
  'messages' => [
    [
      'type' => 'text',
      'text' => '傘忘れたらあかんでー！！！'
    ]
  ]
];

$reply_message = json_encode($reply_message);
$ch = curl_init('https://api.line.me/v2/bot/message/multicast');
$options = [
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_BINARYTRANSFER => true,
  CURLOPT_HEADER => true,
  CURLOPT_POSTFIELDS => $reply_message,
];

curl_setopt_array($ch, $options);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  'Content-Type: application/json; charser=UTF-8',
  'Authorization: Bearer ' . $_ENV["ACCESSTOKEN"]
));
curl_exec($ch);

