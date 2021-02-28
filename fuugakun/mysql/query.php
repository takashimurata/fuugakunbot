<?php
require_once('./mysql/connect.php');

//line_accesstokenを保存
function saveLineAccesstoken($line_accesstoken, $dbh) {
	$query_string = 'INSERT INTO `users` (line_accesstoken) VALUES (:line_accesstoken)';
	$stmt = $dbh->prepare($query_string);
	$stmt->bindValue(':line_accesstoken', $line_accesstoken);
	$stmt->execute();
}

//ユーザーを削除
function deleteAccount($line_accesstoken, $dbh) {
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
function isValidLocation($line_accesstoken, $dbh) {
	$query_string = 'SELECT latitude, longitude FROM users WHERE line_accesstoken = :line_accesstoken';
	$stmt = $dbh->prepare($query_string);
	$stmt->bindValue(':line_accesstoken', $line_accesstoken);
	$stmt->execute();
	$fetch_position = $stmt->fetch(PDO::FETCH_ASSOC);
	$latitude = $fetch_position['latitude'];
	$longitude = $fetch_position['longitude'];
	return array($latitude, $longitude);
}

//位置情報をDBへ保存
function saveLocation($dbh, $event) {
	$query_string = 'UPDATE users SET latitude = :latitude, longitude = :longitude WHERE line_accesstoken = :line_accesstoken';
	$stmt = $dbh->prepare($query_string);
	$line_accesstoken= $event['source']['userId'];
	$latitude = $event['message']['latitude'];  //緯度
	$longitude = $event['message']['longitude'];//経度
	$stmt->bindValue(':line_accesstoken', $line_accesstoken);
	$stmt->bindValue(':latitude', $latitude);
	$stmt->bindValue(':longitude', $longitude);
	$stmt->execute();
}
