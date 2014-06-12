<?
$tmp = $_POST;
$tmp = json_encode($tmp);
$api = "bellagio: ".$tmp."\n";
$file2 = fopen("woobox.apicode.log", "a");
fwrite($file2, $api);
fclose($file2);
//include_once("cron.woobox.php");