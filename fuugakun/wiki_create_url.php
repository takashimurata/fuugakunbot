<?php
function wikiArticleSearch($search_word) {
	$split_word = explode(" ", $search_word, 2);
	if (!empty($split_word[1])){

		//初めの文字がwikiだった場合、リプライメッセージを上書き
		if ($split_word[0] === 'Wiki' || $split_word[0] === 'wiki') {
			$search_word = $split_word[1];
			$reply_message .= $search_word . 'をwikiで検索したよ〜' . "\n";
			$reply_message .= 'https://ja.wikipedia.org/wiki/' . $search_word;
		}
		return $reply_message;
	} else {
		return $split_word[0];
	}
}
