<?php

//メッセージを取得し、値を変更。
//この時に、Qiitaは検索ワードをとswitchに入れる単語は分ける。
$message = 'trend';
require './vendor/autoload.php';

switch ($message) {
  case 'trend';
    $html = file_get_contents('https://qiita.com');
    $phpobj = phpQuery::newDocument($html);
    $links = $phpobj["h2 > a"];
    foreach ($links as $link) {
      echo pq($link)->text() . "<br>";
      echo pq($link)->attr("href") . "<br>";
    }
    break;
  case 'qiita';
    $search_word = 'オブジェクト指向';
    $html = file_get_contents('https://qiita.com/search?sort=&q=' . $search_word);
    $phpobj = phpQuery::newDocument($html);
    $links = $phpobj["h1 > a"];
    foreach ($links as $link) {
      echo pq($link)->text() . "<br>";
      echo 'https://qiita.com' . pq($link)->attr("href") . "<br>";
    }
    break;
  case 'Wiki';
    //wiki、検索ワードでそのワードのwikiのページを出す。
    break;
  case 'weather';
    //天気
    break;
  case 'default';
    //弾かれたワードは全てchat処理へ
    break;
}
