#!/usr/bin/php
<?php
include_once("../dblibraries/lib.customers.php");
include_once("../dbfunctions/cronlogs.php");
$insert = 0;
$update = 0;
$error = 0;
$cad = "";
$custevent = "";
$today = date("Y-m-d");
$today = date("Y-m-d", strtotime("-1 day", strtotime($today)));
$yesterday = date("Y-m-d", strtotime("-1 day", strtotime($today)));
$cont =file_get_contents("cont2.txt");
if($cont == 1)
{
	$pastweek = date("Y-m-d H:i:s", strtotime("$yesterday 16:00:00"));	
	$thisweek = date("Y-m-d H:i:s",strtotime("$today 09:00:00"));
}
else if($cont==2)
{
	$pastweek = date("Y-m-d H:i:s",strtotime("$today 09:00:00"));	
	$thisweek = date("Y-m-d H:i:s",strtotime("$today 12:00:00"));
}
else if($cont == 3)
{
	$pastweek = date("Y-m-d H:i:s",strtotime("$today 12:00:00"));	
	$thisweek = date("Y-m-d H:i:s",strtotime("$today 16:00:00"));		
}
echo $pastweek."\n".$thisweek;

$wsdl = "https://services.stun1.com/datainxml/?wsdl";
$query = "select distinct cs.CustID, cu.`First Name`, cu.`Last Name`, cu.Gender, cu.Email from ZEN_Customers as cu join ZEN_Customer2Source as cs on cu.CustID=cs.CustID where cs.VenueID='13' and cs.CreatedTIMESTAMP >= '$pastweek' and cs.CreatedTIMESTAMP <= '$thisweek'";
$custs = getrows($query);
for($i = 1; $i <= $custs[0]['total']; $i++)
{
	$firstname = $custs[$i]['First Name'];
	$lastname = $custs[$i]['Last Name'];
	$custid = $custs[$i]['CustID'];
	$gender = $custs[$i]['Gender'];
	if($gender == "M")
		$gender = "male";
	else if($gender == "F")	
		$gender = "female";
	else if($gender == "u")
		$gender = "";
	$email = $custs[$i]['Email'];
	$firstname = htmlentities($firstname);
	$lastname = htmlentities($lastname);
	$email = htmlentities($email);

	$qevents = "select t.EventID, ev.Name from ZEN_Tickets as t join ZEN_Events as ev on t.Eventid=ev.Eventid where t.Custid='$custid'";
	$events = getrows($qevents);
	for($j = 1; $j <= $events[0]['total']; $j++)
	{
		$eventname = $events[$j]['Name'];
		$custevent .= "$eventname "; 	
	}
	$qtreservations = "select t.EventID, ev.Name from ZEN_TableReservations as t join ZEN_Events as ev on t.Eventid=ev.Eventid where t.Custid='$custid'";
	$tablereservations = getrows($qtreservations);
	for($j = 1; $j <= $tablereservations[0]['total']; $j++)
	{
		$eventname = $tablereservations[$j]['Name'];
		$custevent .= "$eventname ";
	}
	try
	{
		$xml ='<?xml version="1.0" encoding="iso-8859-1"?>
		<SUBSCRIBERINFO>
			<SUBSCRIBER RequestId="'.$i.'"> 
				<FORMID>14116</FORMID>
				<ACCESSKEY>Fr3EhGPJ3S1GVa3</ACCESSKEY>
				<ACTION>upsert</ACTION>
				<RESPONSEADDRESS>nothing@urvenue.com</RESPONSEADDRESS> 
				<NEWEMAIL>'.$email.'</NEWEMAIL>
				<EMAIL>'.$email.'</EMAIL>
				<EMAILPREFERENCE>html</EMAILPREFERENCE>
				<FIRSTNAME>'.$firstname.'</FIRSTNAME>
				<LASTNAME>'.$lastname.'</LASTNAME>
				<GENDER>'.$gender.'</GENDER>
				<MISC1>'.$custevent.'</MISC1>
			</SUBSCRIBER>
		</SUBSCRIBERINFO>';
		$soap = new SoapClient($wsdl);
		$result = @$soap->gatewayXML($xml);
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}
	print_r($xml);
	echo"\n";
	
	print_r($result);

	$xml = @simplexml_load_string($result);
	$responsecode = $xml->RESPONSE->RESPONSECODE;
	$responsemessage = $xml->RESPONSE->RESPONSEMESSAGE;
	if($responsecode == 1)
	{
		$update++;
		$cad .= "Email: $email\nFirstname: $firstname\nLastname: $lastname\nGender: $gender\nEvents: $custevent\nAction: Update\n\n";
	}	
	else if($responsecode == 2)
	{
		$error++;
		$cad .= "Email: $email\nFirstname: $firstname\nLastname: $lastname\nGender: $gender\nEvents: $custevent\nError: $responsemessage\n\n";
	}	
	else	
	{
		$insert++;	
		$cad .= "Email: $email\nFirstname: $firstname\nLastname: $lastname\nGender: $gender\nEvents: $custevent\nAction: Insert\n\n";		
	}	
	echo "\n";
	$custevent = "";
}
$total = $update + $insert + $error;
$header = "$pastweek to $thisweek\n\nTotal: $total\nInsert: $insert\nUpdate: $update\nError: $error\n\n";
$logmsg =$header.$cad;
$filelogname = "createtomobilestorm";
makecronlog($filelogname, $logmsg);
$file = fopen("cont2.txt", "w+");
if($cont >= 3)
	$cont = 1;
else
	$cont = $cont + 1;
fwrite($file, "$cont");
fclose($file); 
