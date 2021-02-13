<?php

//メッセージを取得し、値を変更。
//この時に、Qiitaは検索ワードをとswitchに入れる単語は分ける。
$reply_message = '雨でっせ';
              $rain_flag = false;
if (strpos($reply_message, '雨') !== false) {
  $rain_flag = true;
}
var_dump($rain_flag);
