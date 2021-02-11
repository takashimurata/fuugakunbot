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
      $params = array(':line_accesstoken' => $line_accesstoken);
      $stmt->execute($params);
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
