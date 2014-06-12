<?php
#!/usr/bin/php
//include the libraries
include_once("../dblibraries/lib.venues.php");
include_once("../dblibraries/lib.customers.php");
include_once("../dbfunctions/cronlogs.php");
//define the access key that mobilestorm give us
$apiaccesskey = "Fr3EhGPJ3S1GVa3";
//put the varibales for the log
$insert = 0;
$update = 0;
$error = 0;
//get the info of the cont2 to know what time we need put
$cont =file_get_contents("cont2.txt");
if($cont == 1)
{
	$date = date("Y-m-d", strtotime("-1 day", time()));
	$date .= " 08:00:00";
	$date2 = date("Y-m-d", strtotime("-1 day", strtotime($date)));
	$date2 .= " 18:00:00";
}
else if($cont==2)
{
	$date = date("Y-m-d", strtotime("-1 day", time()));
	$date .=" 12:00:00";
	$date2 = date("Y-m-d",strtotime($date));
	$date2 .= " 08:00:00";
}
else if($cont == 3)
{
	$date = date("Y-m-d", strtotime("-1 day", time()));
	$date .=" 18:00:00";
	$date2 = date("Y-m-d",strtotime($date));
	$date2 .= " 12:00:00";	
}
echo $date2." to ".$date;
//put the wsdl that we send the info
$wsdl = "http://services.stun1.com/datainxml/?wsdl";
//get the form id
$query = getrows("select f.VenueID, l.formid from MBlists as l join ZEN_MB_Venue2List as f on l.id=f.ListID");
//travel the result
for($i = 1; $i <= $query[0]['total']; $i++)
{
	//get the variables that we need
	$venueid = $query[$i]['VenueID'];
	$venue = Venues::get_venues($venueid);
	$venuename = $venue[1]['Name'];
	$formid = $query[$i]['formid'];
	$cad2 .= "Venue: $venuename with the FormID: $formid\n";
	//making another query to get the customers of the each venue
	$cust = getrows("select distinct c2s.CustID, c.`First Name`, c.Email, c.`Last Name`, c.Gender from ZEN_Customers as c join ZEN_Customer2Source as c2s on c.CustID=c2s.CustID where c2s.VenueID='$venueid' and c2s.CreatedTIMESTAMP >='$date2' and c2s.CreatedTIMESTAMP <= '$date' and c.Email<>''");
	//checking if is more than 0
	if($cust[0]['total'] != 0)
	{
		//start to define the structure of the log
		$cad .= "Customer Info: \n\n"; 
		//start structure the xml that we need send
		$xml = '<?xml version="1.0" encoding="iso-8859-1"?>
				<SUBSCRIBERINFO>';
		//traveling the number of cust
		for($j = 1; $j <= $cust[0]['total']; $j++)
		{	
			//get the info that we need
			$firstname = $cust[$j]['First Name'];
			$lastname = $cust[$j]['Last Name'];
			$email = $cust[$j]['Email'];
			$gender = $cust[$j]['Gender'];
			$firstname = htmlentities($firstname);
			$lastname = htmlentities($lastname);
			$email = htmlentities($email);
			$gender = htmlentities($gender);
			//echo $firstname." $lastname $email $gender<br>";
			//construct the xml
			$xml .='
				<SUBSCRIBER RequestId="'.$j.'"> 
					<FORMID>'.$formid.'</FORMID>
					<ACCESSKEY>'.$apiaccesskey.'</ACCESSKEY>
					<ACTION>upsert</ACTION>
					<RESPONSEADDRESS>nothing@urvenue.com</RESPONSEADDRESS> 
					<NEWEMAIL>'.$email.'</NEWEMAIL>
					<EMAIL>'.$email.'</EMAIL>
					<EMAILPREFERENCE>html</EMAILPREFERENCE>
					<FIRSTNAME>'.$firstname.'</FIRSTNAME>
					<LASTNAME>'.$lastname.'</LASTNAME>
					<GENDER>'.$gender.'</GENDER>	
					<MISC1></MISC1>
				</SUBSCRIBER>';
			//cheking the number of i for know if we need to send or no
			if((($i%200)==0) || ($i==$cust[0]['total']))
			{
				//end of xml
				$xml .= '
					</SUBSCRIBERINFO>';
				//call the functio soap
				$soap = new SoapClient($wsdl);
				//send the xml and get the response
				$result = $soap->gatewayXML($xml);
				$result = str_replace("&rsquo", "", $result);
				$result=preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $result);
				//tranform the xml into array
				$xml2 = @simplexml_load_string($result);
				//print_r($xml2);
				//traveling th result
				foreach($xml2->RESPONSE as $response)
				{
					//get the code and the message of the respones
					$responsecode = $response->RESPONSECODE;
					$responsemessage = $response->RESPONSEMESSAGE;
					$emailinfo = $response->SUBSCRIBERINFO->EMAIL;
					//cheking the code
					if($responsecode == 2)
					{
						//increase the error
						$error++;
						//put the info in the cad
						$cad.="Email: $emailinfo\nAction: Error\nError: $responsemessage\n\n";
					}
					else
					{
						if($responsemessage == "Subscriber with New Email Address already registered with us")
						{
							//increase the update
							$update++;
							//put the info in the cad
							$cad.="Email: $emailinfo\nAction: Update\n\n";
						}	
						else
						{
							//increase the insert
							$insert++;
							//put the info in the cad
							$cad.="Email: $emailinfo\nAction: Insert\n\n";	
						}	
					}
				}
				//delete the xml
				$xml = '';
			}	
		}
		/*
$xml .= '
		</SUBSCRIBERINFO>';
		//echo $xml."\n";
		
		$soap = new SoapClient($wsdl);
		$result = $soap->gatewayXML($xml);
		$result = str_replace("&rsquo", "", $result);
		$result=preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $result);
		//print_r($result);
		//echo "\n\n";
		$xml2 = @simplexml_load_string($result);
		//print_r($xml2);
		foreach($xml2->RESPONSE as $response)
		{
			$responsecode = $response->RESPONSECODE;
			$responsemessage = $response->RESPONSEMESSAGE;
			$emailinfo = $response->SUBSCRIBERINFO->EMAIL;
			if($responsecode == 2)
			{
				$error++;
				$cad.="Email: $emailinfo\nAction: Error\nError: $responsemessage\n\n";
			}
			else
			{
				if($responsemessage == "Subscriber with New Email Address already registered with us")
				{
					$update++;
					$cad.="Email: $emailinfo\nAction: Update\n\n";
				}	
				else
				{
					$insert++;
					$cad.="Email: $emailinfo\nAction: Insert\n\n";	
				}	
			}
		}
*/
	}	
	//getting the total
	$total = $insert + $update+$error;
	//structure of the log
	$logmsg2 .= $cad2."Total: $total\nInsert: $insert\nUpdate: $update\nError: $error\n\n".$cad;
	//put empty and in 0 the variables
	$cad2 = "";
	$total = 0;
	$insert = 0;
	$update = 0;
	$error = 0;
	$xml = '';
	$cad = "";
}
//put a header
$header = "From $date2 to $date\n\n";
//finish to strcuture the log
$logmsg = $header.$logmsg2;
echo $logmsg;
/*
$filelogname = "allforms";
makecronlog($filelogname, $logmsg);
*/
