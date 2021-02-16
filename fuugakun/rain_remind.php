<?php
//DB接続
require_once('./db_connection.php');

//.envの呼び出し
require './vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$search_time = date("Y-m-d H:i", strtotime("+10 minute"));
$query_string = 'SELECT `line_accesstoken` FROM `users` WHERE :search_time >= `departure_time`';
$stmt = $dbh->prepare($query_string);
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
/**
 *傘リマインドした場合、departure_timeをNULLへ更新(以下のようなquery文を作成)
 *```sql
 *UPDATE
 *    `user`
 *SET
 *    `departure_time` = CASE
 *        WHEN `line_accesstoken` IN ("ACCESSTOKEN[1]") THEN NULL
 *        WHEN `line_accesstoken` IN ("ACCESSTOKEN[2]") THEN NULL
 *    END
 *    WHERE `line_accesstoken` IN ("ACCESSTOKEN[1]", "ACCESSTOKEN[2]")
 *```
 */
if (!empty($line_accesstokens)) {
	$when_phrase_string = '';
	foreach ($line_accesstokens as $line_accesstoken) {
		$when_phrase_string .= 'WHEN `line_accesstoken` IN ("' . $line_accesstoken . '") THEN NULL ';
	}
	$delimited_line_accesstokens = implode('","', $line_accesstokens);
	$query_string = 'UPDATE `users` SET `departure_time` = CASE ' . $when_phrase_string . ' END WHERE `line_accesstoken` IN ("' . $delimited_line_accesstokens . '")';
	$stmt = $dbh->query($query_string);
}

//Curl処理
$reply_message = json_encode($reply_message);
$ch = curl_init($_ENV["LINEAPI_MULTICAST_URL"]);
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
