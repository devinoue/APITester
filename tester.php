<?php

/**
 * APIテスターfor MindSphere
 * @author Masaharu Inoue <inoue@ferix.jp>
 */

// 設定
ini_set('display_errors', "On");
ini_set("short_open_tag", "On");


// 変数セット
$header1 = $_POST['header1'] ?? "";
$header2 = $_POST['header2'] ?? "";
$header3 = $_POST['header3'] ?? "";
$payload_type = $_POST['payload_type'] ?? "JSON";
$param = $_POST['param'] ?? "";
$method = $_POST['method'] ?? "GET";
$url = $_POST['url'] ?? "";

$self =basename(__FILE__);
$information=[];

if (isset($_POST['url'])) {

    // クッキーの設定(必要)
    $cookie="";
    foreach($_COOKIE as $k => $v){
        $cookie .= "$k=$v; ";
    }


    // cURLセッションを初期化
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url); // 取得するURLを指定


    $header=[];
    if (trim($header1) != "") $header[]=trim($header1);
    if (trim($header2) != "") $header[]=trim($header2);
    if (trim($header3) != "") $header[]=trim($header3);

    // Cookieとxsrf-token(必要)
    $header[] = "Cookie: $cookie";
    if (isset($_COOKIE['XSRF-TOKEN'])) {
		$header[] = "x-xsrf-token: {$_COOKIE['XSRF-TOKEN']}";
	}

    // cURLのその他設定
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 実行結果を文字列で返す
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header); // リクエストにヘッダーを含める
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); // methodの指定



    //リダイレクト関係
    //Locationをたどる
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
    //最大何回リダイレクトをたどるか
    curl_setopt($ch,CURLOPT_MAXREDIRS,30);
    //リダイレクトの際にヘッダのRefererを自動的に追加させる
    curl_setopt($ch,CURLOPT_AUTOREFERER,true);

    //分岐
    if ($payload_type == "JSON"){
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsoned);
    } else { // クエリ
        $tmp = json_decode($param,true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tmp));

    }

    // URLの情報を取得
    $response =  curl_exec($ch);
    $information = curl_getinfo($ch);

    // セッションを終了
    curl_close($ch);
}



?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>APIテスター for Mind Sphere</title>
     <!-- Compiled and minified CSS -->
     <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

<style>

</style>
</head>
<body>
<div class="container">
<a href="$self">更新</a>
<h1>APIテスター for MindSphere</h1>
<?php
if (isset($information["http_code"])) {

    print<<<HTML
    <b>Response header:</b> <br>
    Content_type : {$information["content_type"]} <br>
    Http_code : {$information["http_code"]} <br>

    <b>Response body: </b><br>
    <textarea  cols="100" rows="8">$response</textarea>
    <hr>
HTML;

}

?>
    <form action="$self" method="POST">
Req header1:<input type=text name="header1" size="50" id="header1" value="<?=$header1?>">
<input type="button" value="クリア" onclick="document.getElementById('header1').value='';" /><br>
Req header2:<input type=text name="header2" size="50" id="header2" value="<?=$header2?>">
<input type="button" value="クリア" onclick="document.getElementById('header2').value='';" /><br>
Req header3:<input type=text name="header3" size="50" id="header3" value="<?=$header3?>">
<input type="button" value="クリア" onclick="document.getElementById('header3').value='';" /><br>

    <input type="hidden" name="_csrf" value="<?=$_COOKIE['XSRF-TOKEN'] ?? "" ?>">


        形式 : <select name="method">
            <option value="POST" <?php if($method == "POST") echo "selected" ?>>POST</option>
            <option value="GET" <?php if($method == "GET") echo "selected" ?>>GET</option>
            <option value="PUT"<?php if($method == "PUT") echo "selected" ?>>PUT</option>
            <option value="DELETE"<?php if($method == "DELETE") echo "selected" ?>>DELETE</option>
        </select>
        URL : <input type="text" name="url" value="<?=$url?>" size="60" id="url">
        <input type="button" value="クリア" onclick="document.getElementById('url').value='';" />
        <br>
        ペイロードタイプ: 
        <input type="radio" name="payload_type" value="query" <?php if($payload_type == "query") echo "checked" ?>>クエリ 
        <input type="radio" name="payload_type" value="JSON"<?php if($payload_type == "JSON") echo "checked" ?>>JSON <br>
        パラメータ(json形式) : <br><textarea cols="100" rows="8" name="param"><?=$param?></textarea><br>
        <input type="submit" value="send">
    </form>
現在時刻(ISO8601) : 
<?php
	// 現在時刻の所得し表示
	$dt = new DateTime();
	$dt->setTimeZone(new DateTimeZone('UTC'));
	print $dt->format('Y-m-d\TH:i:s.\0\0\0\Z'); 
 ?>

</div>

</body>
</html>




