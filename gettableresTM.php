#!/usr/bin/php
<?php	
	include_once("../dblibraries/lib.tableres.php");
	include_once("../dblibraries/lib.customers.php");
	include_once("../dbfunctions/cronlogs.php");

	$today = time();
	$startdate = strtotime("-1 day", $today);
	$startdate = date("Y-m-d H:i:s", $startdate);
	$enddate = date("Y-m-d H:i:s", $today);
	
	$logmsg = "Table Reservations from $startdate to $enddate\n\n";
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
			$client = new SoapClient('http://embed.laughstub.com/ElectroStubAPI/getShowCustomers.cfc?wsdl');
		    $result = $client->__soapCall('getShowCustomers',$params);
		    $obj = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);			   
		    $tickets = get_object_vars($obj);		    
			$tickets = $tickets['customers'];			
		    $tickets = get_object_vars($tickets);
		    $tickets = $tickets['customer'];		    
		    $numtickets = count($tickets);

		    $logmsg .= "External EventID: $for_showid, EventID: $showid\nEvent Name: $showname\n";
		    $logtickets = "Tickets:\n\n";
		    $insert = 0;
		    $update = 0;
		    $tables = 0;
		    if($numtickets > 0){
			    for($a=0; $a<count($tickets); $a++){
			    	$ticket = @get_object_vars($tickets[$a]);		    	
			    	$ticketid = $ticket['@attributes']['id'];
			    	$tcheckedin = $ticket['checkedIn'];
			    	$cfirstname = $ticket['firstName'];
			    	$clastname = $ticket['lastName'];
			    	$cphone = $ticket['phone'];
			    	$czip = $ticket['zipcode'];
			    	$cemail = $ticket['email'];
			    	$cgender = $ticket['gender'];
			    	$cbirthday = $ticket['birthday'];
			    	$cover21 = over21($cbirthday);
			    	$timetstamp = date("Y-m-d H:i:s", strtotime($ticket['purchseTime']));
			    	$trevenue = $ticket['revenue'];
			    	$trevenue = str_replace("$", "", $trevenue);
			    	$trevenue = str_replace(",", "", $trevenue);
			    	$tquantity = $ticket['quantity'];
			    	$ttax = $ticket['tax'];
			    	$ttax = str_replace("$", "", $ttax);
			    	$ttax = str_replace(",", "", $ttax);
			    	$ttotal = $ticket['total'];
			    	$ttotal = str_replace("$", "", $ttotal);
			    	$ttotal = str_replace(",", "", $ttotal);
			    	$tdiscount = $ticket['socialShareDiscount'];
			    	$tdiscount = str_replace("$", "", $tdiscount);
			    	$tdiscount = str_replace(",", "", $tdiscount);
			   		$tservicefee = $ticket['serviceFee'];
			   		$tservicefee = str_replace("$", "", $tservicefee);
			   		$tservicefee = str_replace(",", "", $tservicefee);
			   		$tickettype = get_object_vars($ticket['ticketTypes']);
			   		$tickettype = $tickettype['ticketType'];
			   		$tickettype = strtolower($tickettype);
			   		if(strpos($tickettype, "table") !== false){
			   			$tables ++;
			   			$customer = array(
				    		"firstname" => $cfirstname,
				    		"lastname" => $clastname,
				    		"email" => $cemail,
				    		"cellphone" => $cphone,
				    		"dob" => $cbirthday,
				    		"zipcode" => $czip,
				    		"over21" => $cover21,
				    		"gender" => $cgender
				    	);			    

				    	$result_cust = Customers::add_customers($customer, 58, $venueid);		    
				    	$cid = $result_cust['custid'];		    	
				    	$action = $result_cust['action'];
				    	if($action == "insert"){
				    		$insert++;
				    	}else if($action == "update"){
				    		$update++;
				    	}
				    	
				    	$tableinfo = array(
				    		"checkedin" => $tcheckedin,
				    		"spent" => $ttotal,
				    		"timetstamp" => $timetstamp		    		
				    	);
				    				    	
				    	$result_table = TableRes::add_tableres($cid, $showid, $tableinfo);
				    	$newtableresid = $result_table['tableresid'];
				    	$logtableres .= "TicketID: $ticketid\nTable Reservation ID: $newtableresid\nTable Reservation Info:\n"; 
				    	$logtableres .= "   firstname : $cfirstname\n   lastname : $clastname\n   email : $cemail\n   cellphone : $cphone\n   dob : $cbirthday\n   zipcode : $czip\n   over21 : $cover21\n   gender : $cgender\n   checkedin : $tcheckedin\n   spent : $ttotal\n   timetstamp : $timetstamp\n\n";			    	
				    }
				}
				
			}	
			$logmsg .= "Total Table Reservations: $tables\n";
			$logmsg .= "Customers Inserted: $insert\nCustomers Updated: $update\n\n";
			if($tables > 0)
				$logmsg .= "$logtableres\n";

			$logmsg .= "-----------------\n";
		}
	}

	$filelogname = "tableresTM";
	makecronlog($filelogname);	



