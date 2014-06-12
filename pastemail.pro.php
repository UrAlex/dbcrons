#!/usr/bin/php
<?php
include_once("../dblibraries/lib.venues.php");
include_once("../dbfunctions/cronlogs.php");
include_once("../dblibraries/lib.customers.php");
$insert=0;
$update=0;
$error=0;
$cad="Customers Info: \n\n";
$sourceid = "12";
$enddate = time();
$stardate = strtotime("-2 days", $enddate);
$enddate = date("m/d/Y", $enddate);	
$stardate = date("m/d/Y", $stardate);
echo "<br>";
echo "$stardate - $enddate \n";
$venues = Venues::get_venues();
$rows = $venues[0]['total'];
for($i=1;$i<=$rows;$i++)
{	
	$venueid = $venues[$i]['VenueID'];
	$venuename = $venues[$i]['Name'];
//	echo "\n".strtoupper($venuename)."\n";
	$query = getrows("select ForeignVenueID from ZEN_ForeignVenues where VenueID='$venueid'");
	$newvenueid = $query[1]['ForeignVenueID'];
	//echo "Venueid: $venueid, Venue Name: $venuename, Foreign Venueid: $newvenueid<br>";		

	$content = file_get_contents("http://www.electrostub.com/urvenue/emails.cfm?urvenueid=$newvenueid&startDate=$stardate&enddate=$enddate");

	$obj = @simplexml_load_string($content, "SimpleXMLElement"); 
	$emails = @get_object_vars($obj); 
	$emails = @get_object_vars($emails["emails"]); 
	$emails = $emails['emailRecord'];
	
	for($j=0; $j<count($emails);$j++)
	{
		$emailinfo=@get_object_vars($emails[$j]);
		$firstname = $emailinfo["firstName"];
		$lastname = $emailinfo["lastName"];
		$email = $emailinfo["email"];
		$datecreated = $emailinfo["dateCreated"];
		$datecreated = explode(".", $datecreated);
		$datecreated = $datecreated[0];
		
		$customer = array("firstname" => "$firstname", "lastname" => "$lastname", "email" => "$email", "createtstamp" => $datecreated);
		
		$addcust = Customers::add_customers($customer, $sourceid, $venueid);			
		if($addcust['status'] == true)
		{
			if($addcust['action'] == "insert")
			{
				$insert++;
				$cad .= "Email: $email\nFirstname: $firstname\nLastname: $lastname\nAction: Insert\n\n";
			}
			else
			{
				$update++;
				$cad .= "Email: $email\nFirstname: $firstname\nLastname: $lastname\nAction: Update\n\n";
			}
		}
		else
		{
			$error++;
			$errormsg = $addcust['error'];
			$cad .= "Email: $email\nFirstname: $firstname\nLastname: $lastname\nError: $errormsg\n\n";
		}
	}
	$total = $update + $insert + $error;
	$logmsg3 .= "Venue: $venuename\nTotal: $total\nInsert: $insert\nUpdate: $update\nError: $error\n\n".$cad;
	$total = 0;
	$insert = 0;
	$update = 0;
	$error =0;
	$cad = "";
}
$header = "From $stardate to $enddate\n\n";

$logmsg .= $header.$logmsg3;
echo $logmsg;	
$filelogname = "newslettersignup";
makecronlog($filelogname);

	
	