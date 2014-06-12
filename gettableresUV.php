#!/usr/bin/php
<?php	
	include_once("../dblibraries/lib.tableres.php");
	include_once("../dblibraries/lib.customers.php");
	include_once("../dbfunctions/cronlogs.php");

	$content = file_get_contents("http://ursbe.com/promoters/dumptableresuv.pc8");
	eval($content);
	$venues = $xc8['venues'];
	$numvenues = count($venues);

	$startdate = date("Y-m-d H:i:s", time());
	$enddate = strtotime("-1 day", time());
	$enddate = date("Y-m-d H:i:s", $enddate);

	$logmsg = "Table Reservations from $startdate to $enddate\n\n";
	
	for($i=1; $i<=$numvenues; $i++){
		$for_venueid = cleanup($venues[$i]['venueid']);
		$venueid = getvalue("select VenueID from ZEN_ForeignVenues where ForeignVenueID='$for_venueid'");
		$venuename = cleanup($venues[$i]['venuename']);		
		$reservations = $venues[$i]['reservations'];
		$numreservations = count($reservations);

		$logmsg .= "Venue: $venuename\nTotal Reservations: $numreservations\n";
		$logtable = "";
		$insert = 0;
		$update = 0;
		$error = 0;
		for($a=0; $a<$numreservations; $a++){
			$showname = cleanup($reservations[$a]['show']);				
			$showid = getvalue("select EventID from ZEN_Events where Name='$showname'");
			if($showid){
				$spent = cleanup($reservations[$a]['spent']);
				$spent = $spent/100;
				$email = cleanup($reservations[$a]['email']);
				$firstname = cleanup($reservations[$a]['firstname']);
				$lastname = cleanup($reservations[$a]['lastname']);
				$dob = cleanup($reservations[$a]['dob']);
				$over21 = over21($dob);
				$address = cleanup($reservations[$a]['address']);
				$city = cleanup($reservations[$a]['city']);
				$state = cleanup($reservations[$a]['state']);
				$country = cleanup($reservations[$a]['country']);
				$zipcode = cleanup($reservations[$a]['zipcode']);
				$gender = cleanup($reservations[$a]['gender']);
				$timetstamp = cleanup($reservations[$a]['timetstamp']);				
				$timetstamp = date("Y-m-d H:i:s", $timetstamp);
				
				$customer_info = array(
					"firstname" => $firstname,
					"lastname" => $lastname,
					"cellphone" => $cellphone,
					"email" => $email,
					"dob" => $dob,
					"address" => $address,
					"city" => $city,
					"state" => $state,
					"country" => $country,
					"zipcode" => $zipcode,
					"gender" => $gender,
					"over21" => $over21,
					"createtstamp" => $timetstamp
				);

				$result_cust = Customers::add_customers($customer_info, 59, $venueid);		    
				
		    	$cid = $result_cust['custid'];		    	
		    	$action = $result_cust['action'];
		    	if($action == "insert"){
		    		$insert++;
		    	}else{
		    		$update++;
		    	}

				$tableinfo = array(
		    		"spent" => $spent,
		    		"timetstamp" => $timetstamp		    		
		    	);
		    		
		    	$result_table = TableRes::add_tableres($cid, $showid, $tableinfo);
		    	$newtableresid = $result_table['tableresid'];
		    	

				$logtable .= "\nShowid: $showid\nShowname: $showname\nTable Res ID: $newtableresid\n";
				$logtable .= "CustID: $cid\nfirstname: $firstname\nlastname: $lastname\ncellphone: $cellphone\nemail: $email\ndob: $dob\naddress: $address\ncity: $city\nstate: $state\ncountry: $country\nzipcode: $zipcode\ngender: $gender\nover21: $over21\ncreatetstamp: $timetstamp\n";
		    }else{
		    	$error++;
		    }
		}

		if($numreservations > 0){
		 	$logmsg .= "Reservations error (doesn't match with any event): $error\nCustomers Inserted: $insert\nCustomers Updated: $update\n";
		 	$logmsg .= "\nTable Reservation Info:\n";
		 	$logmsg .= $logtable;
		 }

		 $logmsg .= "\n--------------\n\n";	
	}
	
	$filename = "tableresUV";
	makecronlog($filename);

