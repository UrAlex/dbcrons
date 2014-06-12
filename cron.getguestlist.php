#!/usr/bin/php
<?php
	include_once("../dblibraries/lib.guestlist.php");
	include_once("../dblibraries/lib.customers.php");
	include_once("../dbfunctions/cronlogs.php");
	
	$today = time();
	$startdate = strtotime("-1 day", $today);
	$startdate = date("Y-m-d H:i:s", $startdate);
	$enddate = date("Y-m-d H:i:s", $today);
	
	$logmsg = "Guest List from $startdate to $enddate\n\n";	
	$query = "select * from ZEN_Events where `Date/Time`>='$startdate' and `Date/Time`<='$enddate'";
	$shows = getrows($query);
	$numshows = $shows[0]['total'];
	for($i=1; $i<=$numshows; $i++){
		$showid = $shows[$i]['EventID'];
		$showname = $shows[$i]['Name'];
		$datetime = $shows[$i]['Date/Time'];		
		$venueid = $shows[$i]['VenueID'];

		$for_showid = getvalue("select ForeignEventID from ZEN_ForeignEvents where EventID='$showid'");
		$forvenues = getrows("select * from ZEN_ForeignVenues where VenueID='$venueid'");
		$for_venueid = $forvenues[1]['ForeignVenueID'];
		$apikey = $forvenues[1]['ApiKey'];
		$venueurl = $forvenues[1]['VenueUrl'];

		if($apikey && $venueurl){
			
			$pshowid = new SoapVar((double)$for_showid, XSD_DOUBLE);				
			$params = array( 'key' => $apikey,'venue' => $venueurl, 'showTimingID' => $pshowid);
			$client = new SoapClient('http://embed.laughstub.com/ElectroStubAPI/getShowGuestList.cfc?wsdl');
		 	$result = $client->__soapCall('getShowGuestList',$params);
		 	$obj = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
		 	$guestlists = get_object_vars($obj);		    
			$guestlists = $guestlists['customers'];		
			if($guestlists){
			    $guestlists = get_object_vars($guestlists);
			    $guestlists = $guestlists['customer'];
			    $numguest = count($guestlists);
	
			    $logmsg .= "External EventID: $for_showid, EventID: $showid\nEvent Name: $showname\nTotal Guest List: $numguest\n";
			    $insert = 0;
			    $update = 0;
	
			    if($numguest > 0){
				    for($a=0; $a<$numguest; $a++){
				    	$guest = @get_object_vars($guestlists[$a]);		    	
				    	$guestid = $guest['@attributes']['id'];
				    	$name = get_object_vars($guest['name']);
				    	$firstname = $name['firstName'];
				    	$lastname = $name['lastName'];
				    	$cellphone = $guest['phone'];
				    	$zipcode = $guest['zipcode'];
				    	$email = $guest['email'];
				    	$quantity = $guest['quantity'];
				    	$confirmedQuantity = $guest['confirmedQuantity'];
				    	$totalamount = $guest['totalAmount'];
				    	$totalamount = str_replace("$", "", $totalamount);
	
				    	$customer = array(
				    		"firstname" => $firstname,
				    		"lastname" => $lastname,
				    		"email" => $email,
				    		"cellphone" => $cellphone,
				    		"zipcode" => $zipcode,
				    		"over21" => '0'		    		
				    	);			    	
				    	
				    	$result_cust = Customers::add_customers($customer, 56, $venueid);		    
					   	$cid = $result_cust['custid'];		    	
					    $action = $result_cust['action'];
					    if($action == "insert"){
				    		$insert++;
				    	}else if($action == "update"){
				    		$update++;
				    	}
					    
				    	$result_guest = GuestList::add_guestlist($cid, $showid);
				    	$newguestid = $result_guest['guestid'];
					    
					    $logguest .= "External Guest ListID: $guestid\nNew GuestID: $newguestid\nGuest List Info:\n"; 
					    $logguest .= "   firstname : $firstname\n   lastname : $lastname\n   email : $email\n   cellphone : $cellphone\n   zipcode : $zipcode\n\n";			    	
				    }
				}
			}
			$logmsg .= "Customers Inserted: $insert\nCustomers Updated: $update\n";
			if($numguest > 0)
				$logmsg .= "$logguest\n";

			$logmsg .= "-----------------\n"; 	 
		}	    
	}

    $filename = "guestlist";
    makecronlog($filename, $logmsg);