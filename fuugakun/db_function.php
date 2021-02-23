<?php
require_once('./db_connection.php');
//line_accesstokenを取得
function insertLineAccesstoken($line_accesstoken, $dbh) {
	$query_string = 'INSERT INTO `users` (line_accesstoken) VALUES (:line_accesstoken)';
	$stmt = $dbh->prepare($query_string);
	$stmt->bindValue(':line_accesstoken', $line_accesstoken);
	$stmt->execute();
}

//ユーザーを削除
function accountDelete($line_accesstoken, $dbh) {
	$query_string = 'DELETE FROM users WHERE line_accesstoken = :line_accesstoken';
	$stmt = $dbh->prepare($query_string);
	$stmt->bindValue(':line_accesstoken', $line_accesstoken);
	$stmt->execute();
}

//外に出る時間(departure_time)を保存
function saveDepartureTime($line_accesstoken, $departure_time, $dbh) {
	$query_string = "UPDATE users SET departure_time = :departure_time WHERE line_accesstoken = :line_accesstoken";
	$stmt = $dbh->prepare($query_string);
	$stmt->bindValue(':line_accesstoken', $line_accesstoken);
	$stmt->bindValue(':departure_time', $departure_time);
	$stmt->execute();
}

//位置情報の有無の確認
function locationCheck($line_accesstoken, $dbh) {
	$query_string = 'SELECT latitude, longitude FROM users WHERE line_accesstoken = :line_accesstoken';
	$stmt = $dbh->prepare($query_string);
	$stmt->bindValue(':line_accesstoken', $line_accesstoken);
	$stmt->execute();
	$fetch_position = $stmt->fetch(PDO::FETCH_ASSOC);
	$lat = $fetch_position['latitude'];
	$lon = $fetch_position['longitude'];
	return array($lat, $lon);
}

//位置情報をDBへ保存
function saveLocation($dbh, $event) {
	$query_string = 'UPDATE users SET latitude = :lat, longitude = :lon WHERE line_accesstoken = :line_accesstoken';
	$stmt = $dbh->prepare($query_string);
	$line_accesstoken= $event['source']['userId'];
	$lat = $event['message']['latitude'];  //緯度
	$lon = $event['message']['longitude'];//経度
	$stmt->bindValue(':line_accesstoken', $line_accesstoken);
	$stmt->bindValue(':lat', $lat);
	$stmt->bindValue(':lon', $lon);
	$stmt->execute();
}










