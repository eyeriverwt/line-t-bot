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

//CSVデータを読み込む
$csv_filepath = 'list.csv';
try {
    $data["list"] = array_csv($csv_filepath);
} catch (Exception $e) {
    echo "error：", $e->getMessage(), "\n";
}

//search
$flg = 0;
foreach ((array)$data['list'] as $key => $value) {
    if ($value[1] == $message->{"text"}) {
        $bottext = $value[0] ."の技で" .$value[2] ."です。";
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
        'text' => 'ちょっとわかんないです3...'
    ];
}
$post_data = [
    "replyToken" => $reply_token,
    "messages" => [$response_format_text]
];
$response_format_text2 = [
    'type' => 'text',
    'text' => '2行目のテキストです。'
];
$post_data2 = [
    "replyToken" => $reply_token,
    "messages" => [$response_format_text2]
];
curl($post_data, $access_token);
curl($post_data, $access_token);



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
