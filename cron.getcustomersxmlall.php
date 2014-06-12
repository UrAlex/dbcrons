#!/usr/bin/php
<?php
//include_once("../library/database.php");
//include_once("../zc8_functions/sqltools.php");
$showerrors = true;
include_once("../dblibraries/lib.customers.php");
include_once("../dblibraries/lib.extradata.php");
include_once("../dbfunctions/cronlogs.php");

$insertcont = 0;
$updatecont = 0;
$errorcont=0;
$logmsg="Customers Info: \n\n";
$xml = simplexml_load_file("http://ursbe.com/promoters/xmlfiles/xmlbackupcustall.xml");
$results = get_object_vars($xml);
$customer = $results['info'];
$date = date("d-m-Y");
$time = time();
echo ("date: $date - $time \n");
echo count($customer)."\n";
for($i=0; $i<count($customer);$i++){	
	$info = get_object_vars($customer[$i]);
	$firstname = cleanup($info['firstname']);
	$lastname = cleanup($info['lastname']);
	$email = cleanup($info['email']);
	$email = strtolower($email);
	$cell = cleanup($info['cell']);
	$firstres = cleanup($info['firstres']);
	$lastres = cleanup($info['lastres']);
	$numres = cleanup($info['numres']);
	$resids = cleanup($info['resids']);
	$ownerids = cleanup($info['ownerids']);
	$refby = cleanup($info['refby']);
	$venueids = cleanup($info['venueids']);
	$sourceid = cleanup($info['sourceid']);
	$bday = cleanup($info['bday']);
	$dob = cleanup($info['dob']);
	if(!$dob && $bday){
		if(strlen($bday)==3){
			$month = "0".$bday[0];
			$day = $bday[1].$bday[2];
		}else if(strlen($bday)==4){
			$month = $bday[0].$bday[1];
			$day = $bday[2].$bday[3];
		}
		
		$dob = "1990-$month-$day";
	}else if($dob){
		$dobarray = explode("-", $dob);
		$dobyear =$dobarray[0]; 
		$dobmonth = $dobarray[1];
		$dobday = $dobarray[2];
		if($dobmonth[0]=='0'){
			$bday = $dobmonth[1];
		}else{
			$bday = $dobmonth;
		}		
	}
	
	$address = cleanup($info['address']);
	$city = cleanup($info['city']);
	$country = cleanup($info['country']);
	$createtstamp = cleanup($info['createtstamp']);
	$province = cleanup($info['province']);
	$state = cleanup($info['state']);
	$zip = cleanup($info['zip']);
	$mainowner = cleanup($info['mainowner']);
	$gender = cleanup($info['gender']);
	$vip = cleanup($info['vip']);
	$comment = cleanup($info['comment']);	
	$facebookid = cleanup($info['facebookid']);
	$nickname = cleanup($info['nickname']);
	$sourcevenueid = cleanup($info['sourcevenueid']);
	$cid = cleanup($info['cid']);
	$totalspent = cleanup($info['totalspent']);	
	$over21 = over21($dob);	
	$createtstamp = date("Y-m-d H:i:s", $createtstamp);
	
	$customer_info = array(
		"firstname" => "$firstname", 
		"lastname" => "$lastname",
		"middlename" => "",
		"cellphone" => "$cell",
		"email" => "$email",
		"dob" => "$dob",
		"address" => "$address",
		"city" => "$city",
		"state" => "$state",
		"country" => "$country",
		"gender" => "$gender",
		"zipcode" => "$zip",
		"over21" => "$over21",
		"createtstamp" => "$createtstamp"
	);
	
	
	$sourcevenueid = getvalue("select VenueID from ZEN_ForeignVenues where ForeignVenueID='$sourcevenueid'");
	
	$sourcename = getvalue("select sourcename from Sources where sid='$sourceid'");
	
	$sourceid2 = getvalue("select SourceID from ZEN_Sources where Name='$sourcename'");
	
	
	$resultcust = Customers::add_customers($customer_info, $sourceid2, $sourcevenueid, '', $affiliateid);
	print_r($resultcust);

	$custid = $resultcust['custid'];
	$status = $resultcust['status'];
	if($status == true)
	{
		$action = $resultcust['action'];
		$logmsg .= "Action: $action \n";
		$logmsg .= "Cust ID: $custid \n"; 
		if($action == "insert")
			$insertcont++;
		else
			$updatecont++;
	}
	else
	{
		$errorcont++;
		$error = $resultcust['error'];
		$logmsg .= "Action: Error\n";
		$logmsg .= "Error: $error \n";
	}
	$logmsg .= "First Name: $firstname \n";
	$logmsg .= "Last Name: $lastname \n";
	$logmsg .= "Email: $email \n";
	$logmsg .= "\n";
	print_r($resultcust);
	echo"\n";
	if($facebookid)
	{
		$resultextra = CustExtraData::add_extradata($custid, 1, $facebookid, $createtstamp);			
		print_r($resultextra);
		echo "\n";
	}
	
	if($refby)
	{
		$query = "select * from ZEN_Customer2Refby where SourceID='$sourceid2' and VenueID='$sourcevenueid' and CustID='$custid' and Refby='$refby'";
		$rows = getrows($query);
		if($rows[0]['total']==0){
			insert("insert into ZEN_Customer2Refby set SourceID='$sourceid2', VenueID='$sourcevenueid', CustID='$custid', Refby='$refby'");
		}
		echo "\n";
	}
	
}
$total = $updatecont+$insertcont+$errorcont;
$header = "Total Records: $total\nInsert: $insertcont\nUpdate: $updatecont\nError: $errorcont\n\n";
$logmsg = $header.$logmsg;

$filelogname = "allcustomers";
makecronlog($filelogname, $logmsg);

	
