<?
include_once("../dblibraries/lib.woobox.php");
$venue = $_GET['venue'];
$response = Woobox::get_wooboxcode($venue);
if($response[0]['total'] == 0)
{
	$code = $_POST['realtimeapi_code'];
	if($code)
	{
		$result = Woobox::add_wooboxcode($venue, $code);
		if($result['status'] == true)
		{
			echo "The code was added correctly";
		}
		else
		{
			$errormsg = $result['error'];
			echo "Error: $errormsg";
		}
	}
	else
		echo "Invalid Woobox Code";	
}
else
{
	$code = $response[1]['Code'];
	include_once("cron.woobox.php");
}