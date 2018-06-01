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
$group_id =  $source->{"groupId"};

$space_ignored = str_replace(" ", "", $message->{"text"} );
$exploded = explode(",", $space_ignored);

//CSVデータを読み込む
$csv_filepath = 'list.csv';
try {
    $data["list"] = array_csv($csv_filepath);
} catch (Exception $e) {
    echo "error：", $e->getMessage(), "\n";
}
$cmd_flg = 0;
$charactor = "test1";
$input_text_format = "test2";
$flg = 0;
$bottext = "";

// strpos で含まれている文字列の検出
if ((strpos($message->{"text"},'#')) !== false) {
    $input_text = explode("#", $message->{"text"});
    $charactor = $input_text[0];
    $input_text_format = $input_text[1];
    //$input_text_format = mb_convert_kana($input_text_format, 'a'); // errになる。。
    /*
    $input_text_format = mb_convert_kana($input_text[1], 'a');
    $input_text_format = str_replace('、', '', $input_text_format);
    $input_text_format = str_replace(' ', '', $input_text_format);
    */
    //search
    foreach ((array)$data['list'] as $key => $value) {
        if (((strpos($charactor,$value[0])) !== false) & ((strpos($input_text_format,$value[1])) !== false)) {
            $bottext .= "【" .$value[0] ."】";
            $bottext .= "技名：" .$value[1] ."（" .$value[2] ."）";
            $bottext .= "コマンド：".$value[3] ."";
            $bottext .= "判定：".$value[4] ."\n";
            $bottext .= "ダメージ：".$value[4] ."";
            $bottext .= "発生：".$value[4] ."\n";
            $bottext .= "ガード：".$value[4] ."";
            $bottext .= "";
            $bottext .= "";
            $bottext .= "です。";
            $flg = 1;
            break;
        }
    }

    // 送られてきたメッセージからレスポンスのタイプを選択
    if ($flg == 1) {
        $response_format_text = [
            'type' => 'text',
            'text' => $bottext
        ];
    } elseif($message->{"text"} == 'スタンプ'){
        $response_format_text = [
            'type' => 'sticker',
            'packageId' => 2,
            'stickerId' => 1
        ];
    }else{
        // それ以外は送られてきたテキストをオウム返し
        $response_format_text = [
            'type' => 'text',
            //'text' => $message->{"text"}
            'text' => 'ちょっとわかんないです＞＜...'
        ];
    }
}else{
    $response_format_text = [
        [
            "type" => "text",
            "text" => "ちょっとわかんないです..."
        ]
    ];

}
/*
// strpos で含まれている文字列の検出
if ((strpos($message->{"text"},'#')) !== false) {
    $input_text = explode("#", $message->{"text"});
    $charactor = $input_text[0];
    $input_text_format = mb_convert_kana($input_text[1], 'as');
    $input_text_format = str_replace(',', '', $input_text_format);
    $input_text_format = str_replace('、', '', $input_text_format);
    $input_text_format = str_replace(' ', '', $input_text_format);

    //search
    foreach ((array)$data['list'] as $key => $value) {
        if ((strpos($charactor,$value[0])) !== false) {
            $bottext .= "【" .$value[0] ."】\n";
            $bottext .= "技名：".$value[1] ."（" .$value[2]."）\n";
            $bottext .= "コマンド：".$value[3] ."\n";
            $bottext .= "判定：".$value[4] ."\n";
            $bottext .= "ダメージ：".$value[4] ."\n";
            $bottext .= "発生：".$value[4] ."\n";
            $bottext .= "ガード：".$value[4] ."\n";
            $bottext .= "";
            $bottext .= "";
            $bottext .= "です。";
            $flg = 1;
            break;
        }
    }

    // 送られてきたメッセージからレスポンスのタイプを選択
    if ($flg == 1) {
        $response_format_text = [
            'type' => 'text',
            'text' => $bottext
        ];
    } elseif($message->{"text"} == 'スタンプ'){
        $response_format_text = [
            'type' => 'sticker',
            'packageId' => 2,
            'stickerId' => 1
        ];
    }else{
        // それ以外は送られてきたテキストをオウム返し
        $response_format_text = [
            'type' => 'text',
            //'text' => $message->{"text"}
            'text' => 'ちょっとわかんないです＞＜...'
        ];
    }
}else{
    $response_format_text = [
        [
            "type" => "text",
            "text" => "ちょっとわかんないです..."
        ]
    ];

}
*/


$post_data = [
    "replyToken" => $reply_token,
    "messages" => $response_format_text
];

curl($post_data, $access_token);






/*
$messages_format_text2 = [
    [
        "type" => "text",
        "text" => "誕生日おめでとう！"
    ],
    [
        "type" => "sticker",
        "packageId" => "1",
        "stickerId" => "410"
    ],
    [
        "type" => "sticker",
        "packageId" => "4",
        "stickerId" => "307"
    ]
];
$messages_format_text ="";
$i =0;
foreach ($messages_format_text2 as $key => $value){
    $messages_format_text[$i][$key] = $value;
    $i++;
}

*/








//@return array $csv_data csv配列
function array_csv($csv_filepath) {
    //TODO エラー処理
    setlocale(LC_ALL, 'ja_JP.UTF-8');
    $handle = file_get_contents($csv_filepath);
    //$handle = mb_convert_encoding($handle, 'UTF-8', 'sjis-win');
    $temp = tmpfile();
    $csv_data  = array();
    fwrite($temp, $handle);
    rewind($temp);
    while (($handle = fgetcsv($temp, 0, ",")) !== false) {
        $tmp_value = array();
        foreach ($handle as $key1 => $value1) {
            $tmp_value[] = trim ($value1);
        }
        $csv_data[] = $tmp_value;
        //$tempstr[] = implode(",",$handle);//配列を区切り文字で文字列化
    }
    $message = ERR_CSV_EMPTY .$csv_filepath;
    if(empty($csv_data)) {
        throw new Exception($message);
    }
    return $csv_data;
}


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

// プッシュメッセージの送信
function push_message($post_data) {
    global $access_token;

    $ch = curl_init("https://api.line.me/v2/bot/message/push");
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

