<?php

//require 14行目の$eventで必ずundefinded errorが出るため、エラー制御
ini_set('display_errors', 0);

//.envの呼び出し
$vendor_path = __DIR__ . '/vendor/autoload.php';
require $vendor_path;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//送信時の設定
$post_array = [
	"utterance" => $event['message']['text'],
	"username" => "飼い主",
	"agentState" => [
		"agentName" => "ふうが",
		"tone" => "normal",
		"age" => "14歳"
	]
];

$post_array = json_encode($post_array);
$url = 'https://www.chaplus.jp/v1/chat?apikey=' . $_ENV["CHATBOTTOKEN"] ;

//curl処理
$ch = curl_init($url);
$options = [
	CURLOPT_CUSTOMREQUEST => 'POST',
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_BINARYTRANSFER => true,
	CURLOPT_POSTFIELDS => $post_array,
];
curl_setopt_array($ch, $options);
$reply_chunk = curl_exec($ch);
curl_close($ch);

//リプライメッセージ
$json_reply_chunk = json_decode($reply_chunk, true);
$reply_message = $json_reply_chunk['bestResponse']['utterance'];
