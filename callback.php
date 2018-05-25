<?php
require_once dirname(__FILE__) . '/config.php';
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
//$group_id =  $source->{"groupId"};

$space_ignored = str_replace(" ", "", $message->{"text"} );
$exploded = explode(",", $space_ignored);




// 送られてきたメッセージの中身からレスポンスのタイプを選択
if ($message->{"text"} == '確認') {
    // 確認ダイアログタイプ
    $response_format_text = [
        'type' => 'template',
        'altText' => '確認ダイアログ',
        'template' => [
            'type' => 'confirm',
            'text' => '元気ですかー？',
            'actions' => [
                [
                    'type' => 'message',
                    'label' => '元気です',
                    'text' => '元気です'
                ],
                [
                    'type' => 'message',
                    'label' => 'まあまあです',
                    'text' => 'まあまあです'
                ],
            ]
        ]
    ];
} else {
    // それ以外は送られてきたテキストをオウム返し
    $response_format_text = [
        'type' => 'text',
        'text' => $message->{"text"}
    ];
}



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