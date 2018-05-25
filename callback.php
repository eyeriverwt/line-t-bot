<?php
//require_once dirname(__FILE__) . '/config.php';
//require_once dirname(__FILE__) . '/response_format_text.php';
//require_once dirname(__FILE__) . '/curl.php';
//require_once dirname(__FILE__) . '/register.php';


//APIから送信されてきたイベントオブジェクトを取得
$json_string = file_get_contents('php://input');
$json_obj = json_decode($json_string);

//イベントオブジェクトから必要な情報を抽出
$source = $json_obj->{"events"}[0]->{"source"};
$message = $json_obj->{"events"}[0]->{"message"};
$reply_token = $json_obj->{"events"}[0]->{"replyToken"};

// group_idの取得
$group_id =  $source->{"groupId"};

$space_ignored = str_replace(" ", "", $message->{"text"} );
$exploded = explode(",", $space_ignored);

$access_token = '4TTcwP8p15f7nzWgUziMkI0XzqUZBtZE4AoIoyrdiMYyGHF6hF4p658x5LdMuFOxdFqnAs/vvtBsRNqhco8xDzquLSsWdOADJ1Y9AE3yYaQIDmany4AqgwQpEYXukHqqZxkpQmGGBJ7kDoC049aS3AdB04t89/1O/w1cDnyilFU=';

$response_format_text = [
    "type" => "text",
    "text" => "おはよう！今日も１日頑張ってね〜！"
];


$post_data = [
    "replyToken" => $reply_token,
    "messages" => [$response_format_text]
];

curl($post_data, $access_token);

function curl($post_data, $access_token) {
    //curlを使用してメッセージを返信する
    $ch = curl_init("https://api.line.me/v2/bot/message/reply");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $access_token
    ));
    $result = curl_exec($ch);
    curl_close($ch);
}