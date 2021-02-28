<?php
function reply($reply_token, $reply_message, $client){
	$client->replyMessage([
		'replyToken' => $reply_token,
		'messages' => [
			[
				'type' => 'text',
				'text' => $reply_message
			]
		]
	]);
}
