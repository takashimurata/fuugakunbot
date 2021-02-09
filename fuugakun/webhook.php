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

//.envの呼び出し
require './vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$client = new LINEBotTiny($_ENV["ACCESSTOKEN"], $_ENV["CHANNELSECRET"]);
foreach ($client->parseEvents() as $event) {
  switch ($event['type']) {
    case 'follow':

      //DB接続
      require_once('./db_connection.php');

      //line_accesstokenを取得
      $sql = 'INSERT INTO `users` (line_accesstoken) VALUES (:line_accesstoken)';
      $stmt = $dbh->prepare($sql);
      $line_accesstoken= $event['source']['userId'];
      $params = array(':line_accesstoken' => $line_accesstoken);
      $stmt->execute($params);
      break;

    case 'message':
      $message = $event['message'];
      switch ($message['type']) {
        case 'text':
          $client->replyMessage([
              'replyToken' => $event['replyToken'],
              'messages' => [
              [
              'type' => 'text',
              'text' => $message['text']
              ]
              ]
          ]);
          break;
        default:
          error_log('Unsupported message type: ' . $message['type']);
          break;
      }
      break;
    default:
      error_log('Unsupported event type: ' . $event['type']);
      break;
  }
};
?>
