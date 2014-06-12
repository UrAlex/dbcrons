<?
$tmp = $_POST;
$tmp = json_encode($tmp);
$api = "create: ".$tmp."\n";
$file2 = fopen("woobox.apicode.log", "a");
fwrite($file2, $api);
fclose($file2);
header('realtimeapi_code: '.$_POST['realtimeapi_code']);
//include_once("cron.woobox.php");