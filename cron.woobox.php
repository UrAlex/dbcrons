<?
/*
$tmp = json_encode($tmp);

$file = fopen("woobox2.log", "w");
	fwrite($file, $tmp);
fclose($file);
*/
include_once("../dblibraries/lib.customers.php");
include_once("../dblibraries/lib.venues.php");
include_once("../dbfunctions/cronlogs.php");
header('realtimeapi_code: '.$code);
$insert = 0;
$update = 0;
$error = 0;
$tmp = $_POST;
if(!is_array($tmp))
{
	$file = json_decode($tmp, true);
	$entries = $file['entries'];
}
else
{
	$entries = $tmp['entries'];
}

$cad = "";
//$file = file_get_contents("woobox.log");
if(!is_array($entries))
	$entries = json_decode($entries, true);
if(count($entries) > 0)
{	
	for($i=0; $i < count($entries); $i++)
	{
		$id = $entries[$i]['id'];
		$email = $entries[$i]['email'];
		$createdate = $entries[$i]['createdate'];
		$last = $entries[$i]['fbdata_last'];
		$first = $entries[$i]['fbdata_first'];
		$name = $entries[$i]['facebook_name'];
		$fid = $entries[$i]['facebook_id'];	
		$liked_page = $entries[$i]['from_liked_pageid'];	
		$ipaddress = $entries[$i]['ipaddress'];
		$femail = $entries[$i]['fbdata_email'];
		$dob = $entries[$i]['fbdata_birthday'];
		$location = $entries[$i]['fbdata_location'];
		$location = explode(", ", $location);
		if(count($location) < 3)
		{
			$city = $location[0];
			$country = $location[1];
		}
		else
		{
			$city = $location[0];
			$state = $location[1];
			$country = $location[2];
		}
		$cad .= "ID: $id, Email: $email, Date: $createdate, Lastname: $last, Firstname: $first, Name: $name, FacebookID: $fid, Liked Page: $liked_page, Ip address: $ipaddress, Facebook email: $femail, DOB: $dob, City: $city, Country: $country <br>";
			
		$customer = array("firstname" => $first, "lastname" => $last, "email" => $email, "city" => $city, "country"=> $country, "createtstamp" => $createdate, "state" => $state);
		$addcust = Customers::add_customers($customer, "41", $venue);
			if($addcust['status'] == true)
			{
				if($addcust['action'] == "insert")
				{
					$insert++;
					$cad .= "Fistname: $first\nLastname: $last\nEmail: $email\nAction: Insert\n\n";
				}
				else
				{
					$update++;
					$cad .= "Fistname: $first\nLastname: $last\nEmail: $email\nAction: Update\n\n";
				}
			}
			else
			{
				$errormsg = $addcust['error'];
				$error++;
				$cad .= "Fistname: $first\nLastname: $last\nEmail: $email\nError: $errormsg\n\n";
			}

	}
}
else
{
	$cad = "No information Yet";
}

/*
$total = $insert+$update+$error;
$logmsg = $date."\n\nTotal: $total\nInsert: $insert\nUpdate: $update\nError: $error\n\n".$cad;
$filelogname = "woobox";
makecronlog($filelogname);
*/

$file2 = fopen("woobox2.log", "w");
fwrite($file2, $cad);
fclose($file2);

//print_r($entries);