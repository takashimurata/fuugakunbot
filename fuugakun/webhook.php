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

require_once('./LINEBotTiny.php');
require_once('./db_connection.php');

//.envの呼び出し
require './vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$client = new LINEBotTiny($_ENV["ACCESSTOKEN"], $_ENV["CHANNELSECRET"]);
foreach ($client->parseEvents() as $event) {
  switch ($event['type']) {
    case 'follow':

      //line_accesstokenを取得
      $sql = 'INSERT INTO `users` (line_accesstoken) VALUES (:line_accesstoken)';
      $stmt = $dbh->prepare($sql);
      $line_accesstoken= $event['source']['userId'];
      $stmt->bindValue(':line_accesstoken', $line_accesstoken);
      $stmt->execute();
      break;

    case 'unfollow':

      //ユーザーを削除
      $sql = 'DELETE FROM users WHERE line_accesstoken = :line_accesstoken';
      $stmt = $dbh->prepare($sql);
      $line_accesstoken= $event['source']['userId'];
      $stmt->bindValue(':line_accesstoken', $line_accesstoken);
      $stmt->execute();
      break;

    case 'message':
      switch ($event['message']['type']) {
        case 'text':
          //文字分割
          //初期化
          $reply_message = '';

          //Qiitaの文字が含まれているか。
          //FIXME::qiita,wikiを同時に入れると帰ってこない
          //FIXME::!== flase 消すと通らない？
          if (strpos($event['message']['text'], 'Qiita') !== false || strpos($event['message']['text'], 'qiita') !== false) {

            //messageを2つに分ける。
            $split_word = explode(" ", $event['message']['text'], 2);

            //Qiitaのみ入れた場合のエラー制御
            if (!empty($split_word[1])){

              //初めの文字がQiitaだった場合、検索ワード定義とflagをtrueへ
              if ($split_word[0] === 'Qiita' || $split_word[0] === 'qiita') {
                $search_word = $split_word[1];
                $html = file_get_contents('https://qiita.com/search?sort=&q=' . $search_word);
                $phpobj = phpQuery::newDocument($html);
                $links = $phpobj["h1 > a"];

                //配列を結合
                foreach ($links as $link) {
                  $reply_message .= pq($link)->text() . "\n";
                  $reply_message .= 'https://qiita.com' . pq($link)->attr("href") . "\n";
                }
              }
            }

          //Qiitaのトレンド
          } elseif (strpos($event['message']['text'], 'トレンド') !== false) {
            $html = file_get_contents('https://qiita.com');
            $phpobj = phpQuery::newDocument($html);
            $links = $phpobj["h2 > a"];
            foreach ($links as $link) {
              $reply_message .= pq($link)->text() . "\n";
              $reply_message .= pq($link)->attr("href") . "\n";
            }

          //Wikiの文字が含まれているか
          } elseif (strpos($event['message']['text'], 'Wiki') !== false || strpos($event['message']['text'], 'wiki') !== false) {

            //messageを2つに分ける。
            $split_word = explode(" ", $event['message']['text'], 2);

            //wikiのみ入れた場合のエラー制御
            if (!empty($split_word[1])){

              //初めの文字がQiitaだった場合、検索ワード定義とflagをtrueへ
              if ($split_word[0] === 'Wiki' || $split_word[0] === 'wiki') {
                $search_word = $split_word[1];
                $reply_message .= $search_word . 'をwikiで検索したよ〜' . "\n";
                $reply_message .= 'https://ja.wikipedia.org/wiki/' . $search_word;
              }
            }
          } elseif (strpos($event['message']['text'], '天気予報') !== false) {

            //位置情報の有無の確認
            $line_accesstoken= $event['source']['userId'];
            $sql = 'SELECT latitude, longitude FROM users WHERE line_accesstoken = :line_accesstoken';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':line_accesstoken', $line_accesstoken);
            $stmt->execute();
            $stmt_fetch = $stmt->fetch(PDO::FETCH_ASSOC);
            $lat = $stmt_fetch['latitude'];
            $lon = $stmt_fetch['longitude'];

            //位置情報登録なし
            if ($lat === null) {
              $reply_message = 'どこの天気予報したらいいんや！下の＋から位置情報を送って！';

            //登録あり
            } else {

              //TODO::変数名CHECK
              $weather_info_url = 'https://api.openweathermap.org/data/2.5/onecall?lat=' . $lat . '&lon=' . $lon . '&units=metric&lang=ja&appid=' . $_ENV["WEATHERTOKEN"];
              $get_weather_info = json_decode(file_get_contents($weather_info_url), true);
              $json_weather_info = json_encode($get_weather_info);
              $weather_info = json_decode($json_weather_info, true);
              $hourly = $weather_info['hourly'];

              //0,1,3,6,9,24時間後の天気予報を表示
              $forecast_time = [0, 1, 3, 6, 9, 24];
              foreach ($forecast_time as $i => $hour) {
                $temp = $hourly[$hour]['temp'];
                $weather = $hourly[$hour]['weather'][0]['description'];

                //TODO::天気のアイコンを入れたい
                //TODO::いつ外に出る？と聞く->出発時間前にリプライを送る。->departure_timeへ保存
                if ($hour === 0) {
                  $reply_message .= '今の天気は' . $weather . '、温度は' . round($temp) . '度やで！' . "\n";
                } elseif ($hour === 24) {
                  $reply_message .= $hour . '時間後は' . $weather . '、温度は' . round($temp) . '度やで！';
                } else {
                  $reply_message .= $hour . '時間後は' . $weather . '、温度は' . round($temp) . '度やで！' . "\n";
                }
              }
            }
            //TODO::スケジュール管理機能
            //TODO::チャットボット
          } else {
            $reply_message = '開発中！！！！';
          }

          //リプライ
          $client->replyMessage([
              'replyToken' => $event['replyToken'],
              'messages' => [
                [
                  'type' => 'text',
                  'text' => $reply_message
                ]
              ]
          ]);
          break;

        case 'location' && 'message':

          //位置情報をDBへ保存
          $sql = 'UPDATE users SET latitude = :lat, longitude = :lon WHERE line_accesstoken = :line_accesstoken';
          $stmt = $dbh->prepare($sql);
          $line_accesstoken= $event['source']['userId'];
          $lat = $event['message']['latitude'];  //緯度
          $lon = $event['message']['longitude'];//経度
          $stmt->bindValue(':line_accesstoken', $line_accesstoken);
          $stmt->bindValue(':lat', $lat);
          $stmt->bindValue(':lon', $lon);
          $stmt->execute();
          $client->replyMessage([
              'replyToken' => $event['replyToken'],
              'messages' => [
                [
                  'type' => 'text',
                  'text' => '位置情報登録オッケー！'
                ]
              ]
          ]);
          break;
        default:
          error_log('Unsupported message type: ' . $event['message']['type']);
          break;
      }
  }
};
?>
