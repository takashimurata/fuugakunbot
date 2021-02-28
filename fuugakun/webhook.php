<?php
/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

//.envの呼び出し
require './vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once('./LINEBotTiny.php');
require_once('./mysql/query.php');
require_once('./fetch_qiita_article.php');
require_once('./wiki_create_url.php');
require_once('./weather_forecast_functions.php');
require_once('./reply_function.php');

$client = new LINEBotTiny($_ENV["ACCESSTOKEN"], $_ENV["CHANNELSECRET"]);
foreach ($client->parseEvents() as $event) {
	switch ($event['type']) {

		//ユーザー登録
		case 'follow':
			saveLineAccesstoken($event['source']['userId'], $dbh);
			break;

		//ユーザーを削除
		case 'unfollow':
			deleteAccount($event['source']['userId'], $dbh);
			break;

		//外に出る時間(departure_time)を保存し、リプライ
		case 'postback':
			$reply_message = date('m月d日 H時i分', strtotime($event['postback']['params']['datetime'])) . 'やな！任しとき！';
			saveDepartureTime($event['source']['userId'], $event['postback']['params']['datetime'], $dbh);
			reply($event['replyToken'], $reply_message, $client);
			break;

		//メッセージが来た場合
		case 'message':
			switch ($event['message']['type']) {
				case 'text':
					//初期化
					$reply_message = '';

					//Qiitaの記事を文字検索
					if (strpos($event['message']['text'], 'Qiita') !== false || strpos($event['message']['text'], 'qiita') !== false) {

						//qiitaの記事をスクレイピング
						$reply_message = qiitaArticleSearch($event['message']['text']);

					//Qiitaのトレンド
					} elseif (strpos($event['message']['text'], 'トレンド') !== false) {
						$reply_message = qiitaTrendSearch();

					//Wiki記事を検索
					} elseif (strpos($event['message']['text'], 'Wiki') !== false || strpos($event['message']['text'], 'wiki') !== false) {
						$reply_message = wikiArticleSearch($event['message']['text']);

					//天気予報
					} elseif (strpos($event['message']['text'], '天気予報') !== false) {
						list($latitude, $longitude) = isValidLocation($event['source']['userId'], $dbh);

						if ($latitude === null) {
							$reply_message = 'どこの天気予報したらいいんや！下の＋から位置情報を送って！';

						//天気予報を表示
						} else {
							$hourly = getWeatherInfo($latitude, $longitude);
							$reply_message = weatherForecastReply($hourly);
							$rain_flag = rainCheck($reply_message);
							if ($rain_flag === true) {
								checkNeedsUmbrella($client, $event['replyToken'], $reply_message);
							}
						}
					} else {

						//メッセージで弾かれたものは全てチャットへ
						require_once('./reply_chat.php');
					}

					//リプライ
					reply($event['replyToken'], $reply_message, $client);
					break;

				//位置情報をDBへ保存
				case 'location' && 'message':
					$reply_message = '位置情報オッケー！';
					saveLocation($dbh, $event);
					reply($event['replyToken'], $reply_message, $client);
					break;
				default:
					error_log('Unsupported message type: ' . $event['message']['type']);
					break;
			}
		default:
			error_log('Unsupported message type: ' . $event['message']['type']);
			break;
	}
};
?>
