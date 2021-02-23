<?php

function qiitaArticleSearch($search_word) {
	//messageを2つに分ける。
	$split_word = explode(" ", $search_word, 2);

	//Qiitaのみ入れた場合のエラー制御
	if (!empty($split_word[1])){

		//初めの文字がQiitaだった場合
		if ($split_word[0] === 'Qiita' || $split_word[0] === 'qiita') {
			$html = file_get_contents('https://qiita.com/search?sort=&q=' . $split_word[1]);
			$phpobj = phpQuery::newDocument($html);
			$links = $phpobj["h1 > a"];

			//配列を結合
			foreach ($links as $link) {
				$reply_message .= pq($link)->text() . "\n";
				$reply_message .= 'https://qiita.com' . pq($link)->attr("href") . "\n";
			}
		}
	}
	return $reply_message;
}

function qiitaTrendSearch () {
	$html = file_get_contents('https://qiita.com');
	$phpobj = phpQuery::newDocument($html);
	$links = $phpobj["h2 > a"];
	foreach ($links as $link) {
		$reply_message .= pq($link)->text() . "\n";
		$reply_message .= pq($link)->attr("href") . "\n";
	}
	return $reply_message;
}
