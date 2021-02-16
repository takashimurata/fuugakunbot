<?php
//DB接続
require_once('./db_connection.php');

//.envの呼び出し
require './vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$search_time = date("Y-m-d H:i", strtotime("+10 minute"));
$sql = 'SELECT `line_accesstoken` FROM `users` WHERE :search_time >= `departure_time`';
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':search_time', $search_time);
$stmt->execute();
$fetch_line_accesstokens = $stmt->fetchall(PDO::FETCH_ASSOC);
$line_accesstokens = array_column($fetch_line_accesstokens, 'line_accesstoken');

//リプライ
$reply_message = [
	'to' => $line_accesstokens,
	'messages' => [
		[
			'type' => 'text',
			'text' => '傘忘れたらあかんでー！！！'
		]
	]
];

//TODO::リファクタ予定
//リマインドした場合、departure_timeをNULLへ更新
if (!empty($line_accesstokens)) {
	$update_null_conditional_branches = '';
	foreach ($line_accesstokens as $line_accesstoken) {
		$update_null_conditional_branches .= 'WHEN `line_accesstoken` = "' . $line_accesstoken .  '" THEN NULL ';
	}
	$update_string = 'UPDATE `users` SET `departure_time` = CASE ' . $update_null_conditional_branches;
	$update_to_null_sql = $update_string . ' ELSE `departure_time` END';
	$stmt = $dbh->query($update_to_null_sql);
}

//Curl処理
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
