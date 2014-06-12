#!/usr/bin/php
<?php
include_once("../dblibraries/lib.customers.php");
include_once("../dbfunctions/cronlogs.php");

$xml = simplexml_load_file("http://ursbe.com/promoters/xmlfiles/xmlbackupcustallemails.xml");
$results = get_object_vars($xml);
$emails = $results['info'];
$date = date("m-d-Y");
$time = time();
$updatecont = 0;
$insertcont = 0;
$errorcont = 0;
$logmsg="Customers Info: \n\n";
echo "date $date - $time \n";
for($i=0; $i<count($emails);$i++){
	$info = get_object_vars($emails[$i]);
	$email = cleanup($info['email']);
	$email = strtolower($email);
	$ownerid = cleanup($info['ownerid']);
	$sourceid = cleanup($info['sourceid']);
	$createtstamp = cleanup($info['createtstamp']);
	$sourcevenueid = cleanup($info['sourcevenueid']);
	$createtstamp = date("Y-m-d H:i:s", $createtstamp);
	$eid = cleanup($info['eid']);
	echo $email." ".$createtstamp."<br>";
	
	$customer = array(
		"email" => $email,
		"over21" => 0,
		"createtstamp" => $createtstamp
	);
	
	if($sourceid == 2603614101)
		$sourceid = 12;
	else if ($sourceid == 303593112)
		$sourceid = 2;
		
	$query = "select VenueID from ZEN_ForeignVenues where ForeignVenueID='$sourcevenueid'";
	$venueid = getvalue($query);
	
	$result_cust = Customers::add_customers($customer, $sourceid, $venueid, "", "");		
	$status = $result_cust['status'];
	if($status == true)
	{
		$action = $result_cust['action'];
		$custid = $result_cust['custid'];
		$logmsg .= "Action: $action\n";
		$logmsg .= "Cust ID: $custid\n";
		if($action=="insert")	
			$insertcont++;
		else
			$updatecont++;	
	}
	else
	{
		$errorcont++;
		$error = $result_cust['error'];
		$logmsg .= "Action: Error\n";
		$logmsg .= "Error: $error\n";
	}
	$logmsg .= "Email: $email\n\n";
	print_r($result_cust);
}
$total = $updatecont+$insertcont+$errorcont;
$header = "Total Records: $total\nInsert: $insertcont\nUpdate: $updatecont\nError: $errorcont\n\n";
$logmsg = $header.$logmsg;

$filelogname = "allemails";
makecronlog($filelogname, $logmsg);


	
