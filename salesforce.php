#!/usr/bin/php
<?
include_once("../main_functions/salesforce.php");
include_once("../dblibraries/lib.venues.php");
include_once("../dblibraries/lib.customers.php");
include_once("../dblibraries/lib.extradata.php");
include_once("../dbfunctions/cronlogs.php");
include_once("../dblibraries/lib.extradata.php");
$insert = 0;
$update = 0;
$errorcont = 0;
$logmsg2 = "";
$userName = "sbedev@sbe.com";
$password = "S@m8000!@#$%21!###";
$mySforceConnection = login($userName, $password, "");
$cad="Customers Info: \n\n";
$venues = Venues::get_venues();
$date = date("Y-m-d");
$yesterday = date("Y-m-d", strtotime("-1 day", strtotime($date)));
//echo $yesterday."T01:00:00";
for($k=0; $k < 2; $k++)
{
	if($k==0)
	{
		$sourcename = "Wifi Splash Page - Opt in";
		$sourceid= "30";
	}
	else
	{
		$sourcename = "sbe.com - Opt in";
		$sourceid = "12";
	}
	for($i=1; $i <= $venues[0]['total']; $i++)
	{
		$venue = $venues[$i]['Name'];
		$venueid = $venues[$i]['VenueID'];
	
		if($venue == "Hyde AAA")
			$venue = "Hyde - AAarena";
		if($venue == "Hyde Bellagio")
			$venue = "Hyde - Bellagio";
		if($venue == "Hyde Beach")	
			$venue = "Hyde - South Beach";
		if($venue == "Hyde Staples")	
			$venue = "Hyde - Staples Center";
		if($venue == "Hyde Sunset")	
			$venue = "Hyde - Sunset";
		if($venue == "BLOK")	
			$venue = "Blok";
		if($venue == "Greystone Manor")
			$venue = "Greystone";
		if($venue == "Sayers Club")	
			$venue = "The Sayers Club";
		$query = "SELECT FirstName, LastName, Email, PostalCode, CreatedDate, Id, City, State, Street, Country, MobilePhone, MACAddress__c from Lead WHERE LeadSource = '$sourcename' AND Venue_Source__c IN ('$venue') AND DAY_ONLY(CreatedDate) =".$yesterday."";
		$response = Squery($query, $mySforceConnection, "");
		echo $venue."\n\n";
		//print_r($response);
		for($j = 0; $j<count($response['Info']); $j++)
		{
			$firstname = $response['Info'][$j]['FirstName'];
			$lastname = $response['Info'][$j]['LastName'];
			$email = $response['Info'][$j]['Email'];
			$zip = $response['Info'][$j]['PostalCode'];
			$createdate = $response['Info'][$j]['CreatedDate'];
			echo $firstname."\n";
			echo $createdate."\n";
			$city = $response['Info'][$j]['City'];
			$state = $response['Info'][$j]['State'];
			$street = $response['Info'][$j]['Street'];
			$country = $response['Info'][$j]['Country'];
			$phone = $response['Info'][$j]['MobilePhone'];
			$macaddress = $response['Info'][$j]['MACAddress__c'];
			$createdate = str_replace("T", " ", $createdate);
			$createdate = str_replace(".000Z", "", $createdate);
					echo $createdate."\n";
			$customer = array("firstname" => $firstname, "lastname" => $lastname, "email" => $email, "cellphone" => $phone, "address" => $street, "city" => $city, "state" => $state, "country" => $country, "zipcode" => $zip, "createtstamp" => $createdate, "over21" => 0);
			
			$addcust = Customers::add_customers($customer, $sourceid, $venueid);
			//$addcust = array("status" => true, "action", "update");
			echo $addcust['status'];
			echo $addcust['action']."\n";
			if($addcust['status'] == true)
			{
				$action = $addcust['action'];
				$custid = $addcust['custid'];
				$cad .= "Action: $action \n";
				$cad .= "Cust ID: $custid \n"; 
				if($action == "insert")
					$insert++;
				else
					$update++;	
				if($macaddress)
					$addextradata = CustExtraData::add_extradata($custid, "3", $macaddress);	
				
			}
			else
			{
				$error = $addcust['error'];
				$cad .= "Action: Error\n";
				$cad .= "Error: $error \n";
				$errorcont++;
			}
			$cad .= "First Name: $firstname \n";
			$cad .= "Last Name: $lastname \n";
			$cad .= "Email: $email \n";
			$cad .= "Create: $createdate\n";
			$cad .= "\n";
			
		}
		$total = $update + $insert + $errorcont;
		$logmsg2 .= "Venue: ".$venue."\nTotal: $total\nInsert: $insert\nUpdate: $update\nError: $errorcont\n\n".$cad;
		$total = 0;
		$insert = 0;
		$update = 0;
		$errorcont =0;
		$cad = "";
	}
}
$header = "From $yesterday to $date\n\n";

$logmsg .= $header.$logmsg2;	
$filelogname = "salesforce";
makecronlog($filelogname, $logmsg);
