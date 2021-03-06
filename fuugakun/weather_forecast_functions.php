<?php
function getWeatherInfo($lat, $lon) {
	$weather_info_url =$_ENV["OPENWEATHERMAPURL"] . $lat . '&lon=' . $lon . '&units=metric&lang=ja&appid=' . $_ENV["WEATHERTOKEN"];
	$get_weather_info = json_decode(file_get_contents($weather_info_url), true);
	$json_weather_info = json_encode($get_weather_info);
	$weather_info = json_decode($json_weather_info, true);
	return $weather_info['hourly'];
}

function createReplyMessage($hour, $temp, $weather) {
	switch ($hour) {
		case 0:
			return '今は' . $weather . '、温度は' . round($temp) . '度' . "\n";
		case 24:
			return '明日は' . $weather . '、温度は' . round($temp) . '度' . "\n";
		case 47:
			return '明後日は' . $weather . '、温度は' . round($temp) . '度！';
		default:
			return $hour . '時間後は' . $weather . '、温度は' . round($temp) . '度' . "\n";
	}
}

function weatherForecastReply($hourly) {
	$reply_message = "";
	$forecast_time = [0, 1, 3, 6, 9, 24, 47];
	foreach ($forecast_time as $i => $hour) {
		$temp = $hourly[$hour]['temp'];
		$weather = $hourly[$hour]['weather'][0]['description'];
		$reply_message .= createReplyMessage($hour, $temp, $weather);
	}
	return $reply_message;
}

//「雨」のワードが入っている場合、フラグを立てる。
function rainWordSearch ($reply_message){
	if (strpos($reply_message, '雨') !== false) {
		return true;
	}
	return false;
}

function checkNeedsUmbrella($client, $reply_token, $reply_message) {
	$client->replyMessage([
		'replyToken' => $reply_token,
		'messages' => array(
			array(
				'type' => 'template',
				'altText' => 'テスト',
				'template' => array(
					'type' => 'buttons',
					'text' => $reply_message,
					'actions' => array(
						array(
							'type' => 'datetimepicker',
							'label' => '外出るときに傘いる？',
							'data' => 'datetemp',
							'mode' => 'datetime',
						)
					)
				)
			)
		)
	]);
}
