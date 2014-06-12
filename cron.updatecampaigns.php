<?php
include_once("../dblibraries/lib.mobilestorm.php");
include_once("../dbfunctions/cronlogs.php");

$campinsert=0;
$campupdate=0;
$camperror=0;
$unsinsert=0;
$unsupdate=0;
$unserror=0;
$openinsert=0;
$openupdate=0;
$openerror=0;
$bounceinsert=0;
$bounceupdate=0;
$bounceerror=0;
$clickupdate=0;
$clickinsert=0;
$clickerror=0;
$globalerror=0;
$tci = 0;
$tcu = 0;
$tce = 0;
$tui = 0;
$tuu = 0;
$tue = 0;
$tbi = 0;
$tbu = 0;
$tbe = 0;
$toi = 0;
$tou = 0;
$toe = 0;
$tcli = 0;
$tclu = 0;
$tcle = 0;
$cad = "";

$campaigns = Blasts::get_blasts("email");
$ccampupdate = false;
$totalcampaings = $campaigns[0]['total'];
for($i=1; $i<= $totalcampaings; $i++)
{
	$messageid = $campaigns[$i]['aliencampaingid'];
	echo $messageid;
	$system = $campaigns[$i]['system'];
	$campaignname = $campaigns[$i]['campaignname'];
	$lasttimeupdate = $campaigns[$i]['lasttimeupdate'];
	$timesupdate = $campaigns[$i]['timesupdate'];
	if($system == "vegas")
	{
		$venueid = "9";
		$venue = "Hyde Bellagio";
	}	
	else if($system == "Create Nightclub")	
	{
		$venueid = "13";	
		$venue = "Create Nightclub";
	}	
	else if($system == "abbey")	
	{
		$venueid = "16";
		$venue = "Abbey";
	}
	else if($system == "sayers")
	{
		$venueid = "11";	
		$venue = "Sayers";
	}
	else if($system == "staples")	
	{
		$venueid = "2";
		$venue = "Hyde Staples";
	}	
	else if($system == "emerson")	
	{
		$venueid = "18";
		$venue = "Emerson Theatre";
	}	
	else if($system == "greystone")
	{	
		$venueid = "19";
		$venue = "Greystone Manor";
	}	
	else if($system == "miami")	
	{
		$venueid = "6";
		$venue = "Hyde Beach";
	}	
	else if($system == "hydeaaa")
	{
		$venueid = "4";	
		$venue = "Hyde AAA";
	}	
	else if($system == "miaminye")	
	{
		$venueid = "6";
		$venue = "Hyde Beach";
	}	
	else if($system == "vegasnye")	
	{
		$venueid = "9";
		$venue = "Hyde Bellagio";
	}	
	else
		$venueid = "0";	
	$day = date("H", time());
	if($venueid != "0")
	{
		if($timesupdate == 1 && (int)$day >= 8 && (int)$day <= 23)
		{
			$ccampupdate = true;
		}	
		else if($timesupdate >= 2 && (int)$day > 0 && (int)$day < 8 && $timesupdate < 12)
		{
			$ccampupdate = true;
		}	
		else if($timesupdate >= 12)
		{
			$ccampupdate = false;
		}
		if($ccampupdate == true)
		{
			$wsdl = "http://services.stun1.com/reportingAPI/?wsdl";
		
			try
			{	
				
				$xml = '<?xml version="1.0"?>
				<REPORTINGAPIREQUEST>
					<GETCAMPAIGNREPORT>
				    	<CLIENTID>9047</CLIENTID>
				        <ACCESSKEY>Fr3EhGPJ3S1GVa3</ACCESSKEY>
				    	<MESSAGEID>'.$messageid.'</MESSAGEID>
				    	<DATERANGE></DATERANGE>
				    	<DETAIL>YES</DETAIL>
				    </GETCAMPAIGNREPORT>
				</REPORTINGAPIREQUEST>';
				$soap = new SoapClient($wsdl);
				$result = @$soap->getcampaignreport($xml);
				$statustry = true;
			} 
			catch (Exception $e) 
			{
				try
				{
					$xml = '<?xml version="1.0"?>
					<REPORTINGAPIREQUEST>
						<GETCAMPAIGNREPORT>
				    		<CLIENTID>9047</CLIENTID>
				    		<ACCESSKEY>Fr3EhGPJ3S1GVa3</ACCESSKEY>
				    		<MESSAGEID>'.$messageid.'</MESSAGEID>
				    		<DATERANGE>4</DATERANGE>
				    		<DETAIL>YES</DETAIL>
				    	</GETCAMPAIGNREPORT>
				    </REPORTINGAPIREQUEST>';
				    $soap = new SoapClient($wsdl);
				    $result = @$soap->getcampaignreport($xml);
				    $statustry = true;
				} 
				catch(Exception $e)
				{
					try
					{
						$xml = '<?xml version="1.0"?>
						<REPORTINGAPIREQUEST>
							<GETCAMPAIGNREPORT>
				    			<CLIENTID>9047</CLIENTID>
				    			<ACCESSKEY>Fr3EhGPJ3S1GVa3</ACCESSKEY>
				    			<MESSAGEID>'.$messageid.'</MESSAGEID>
				    			<DATERANGE>3</DATERANGE>
				    			<DETAIL>YES</DETAIL>
				    		</GETCAMPAIGNREPORT>
				    	</REPORTINGAPIREQUEST>';
				    	$soap = new SoapClient($wsdl);
				    	$result = @$soap->getcampaignreport($xml);
				    	$statustry = true;
					} 
					catch(Exception $e)
					{
						try
						{
							$xml = '<?xml version="1.0"?>
							<REPORTINGAPIREQUEST>
								<GETCAMPAIGNREPORT>
						    		<CLIENTID>9047</CLIENTID>
						    		<ACCESSKEY>Fr3EhGPJ3S1GVa3</ACCESSKEY>
						    		<MESSAGEID>'.$messageid.'</MESSAGEID>
						    		<DATERANGE>2</DATERANGE>
						    		<DETAIL>YES</DETAIL>
						    	</GETCAMPAIGNREPORT>
						    </REPORTINGAPIREQUEST>';
						    $soap = new SoapClient($wsdl);
						    $result = @$soap->getcampaignreport($xml);
						    $statustry = true;
					    } 
					    catch(Exception $e)
					    {
						    try
						    {
								$xml = '<?xml version="1.0"?>
								<REPORTINGAPIREQUEST>
									<GETCAMPAIGNREPORT>
							    		<CLIENTID>9047</CLIENTID>
							    		<ACCESSKEY>Fr3EhGPJ3S1GVa3</ACCESSKEY>
							    		<MESSAGEID>'.$messageid.'</MESSAGEID>
							    		<DATERANGE>1</DATERANGE>
							    		<DETAIL>YES</DETAIL>
							    	</GETCAMPAIGNREPORT>
							    </REPORTINGAPIREQUEST>';
							    $soap = new SoapClient($wsdl);
							    $result = @$soap->getcampaignreport($xml);
							    $statustry = true;
						    } 
						    catch(Exception $e)
						    {
						    	$campaigninfo = array("campaignname" => $campaignname, "campaingstatus" => "Error");
						    	$cresult = Campaigns::add_campaign($campaigninfo, $messageid);
						    	print_r($cresult);
						    }
					    }
					}
				}
			}
			echo $xml."\n";
			
			$result = str_replace("&rsquo", "", $result);
			$result=preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $result);
			$result .="";
			
			$xml = simplexml_load_string($result);
		    if (is_object($xml))
			{
				$response = $xml -> RESPONSE -> RESPONSECODE;
				if($response=='False')
				{ 
						$error = $xml ->RESPONSE->RESPONSEERRORMESSAGE;
						echo "ERROR: $error\n\n";
						$campaigninfo = array("campaignname" => $campaignname, "campaingstatus" => "Error");
						$cresult = Campaigns::add_campaign($campaigninfo, $messageid);
						print_r($cresult);
						echo "<br>";
				}
				else 
				{
					$cname = cleanup($xml->CAMPAIGNREPORT->CAMPAIGNNAME);
					$lname = cleanup($xml->CAMPAIGNREPORT->LISTNAME);
					$campaignstatus = cleanup($xml->CAMPAIGNREPORT->CAMPAIGNSTATUS);
					$sentstamp = cleanup($xml->CAMPAIGNREPORT->DATETIMESENT);
					$totalsent = cleanup($xml->CAMPAIGNREPORT->TOTALSENT);
					$delivered = cleanup($xml->CAMPAIGNREPORT->DELIVERED);
					$falied = cleanup($xml->CAMPAIGNREPORT->FAILED);
					$unsubscribers = cleanup($xml->CAMPAIGNREPORT->UNSUBSCRIBERS->UNSUBSCRIBECOUNT);
					$clickcount = cleanup($xml->CAMPAIGNREPORT->CLICKS->CLICKCOUNTS->CLICKSCOUNT);
					$uniqueclick = cleanup($xml->CAMPAIGNREPORT->CLICKS->CLICKCOUNTS->UNIQUECLICKSCOUNT);
					$clickthrupercentage = cleanup($xml->CAMPAIGNREPORT->CLICKS->CLICKCOUNTS->CLICKTHRUPERCENTAGE);
					$bounce = $xml->CAMPAIGNREPORT->BOUNCES->BOUNCECOUNTS;
					$complaincounts = cleanup($xml->CAMPAIGNREPORT->COMPLAINTS->COMPLAINTSCOUNTS);
					$totalbounce = cleanup($bounce->TOTALBOUNCECOUNT);
					$hardbounce = cleanup($bounce->HARDBOUNCECOUNT);
					$softbounce = cleanup($bounce->SOFTBOUNCECOUNT);
					$spambounce = cleanup($bounce->SPAMBOUNCECOUNT);
					$unkownbounce = cleanup($bounce->UNKNOWNBOUNCECOUNT);
					$totalcomplaint = cleanup($complaincounts->TOTALCOMPLAINTSCOUNT);
					
					$sentstamp=strtotime("$sentstamp");
					$now=time();
					
					$campaigninfo = array("campaignname" => $cname, "campaingstatus" => $campaignstatus, "timestamp" => $sentstamp, "sent" => $totalsent, "delivered" => $delivered, "failed" => $falied, "listname" => $lname, "unsubscribers" => $unsubscribers, "clickcount" => $clickcount, "uniqueclick" => $uniqueclick, "clickthrupercentage" => $clickthrupercentage, "totalbouncecount" => $totalbounce, "hardbouncecount" => $hardbounce, "softbouncecount" => $softbounce, "spamboucecount" => $spambounce, "unknownboucecount" => $unkownbounce, "totalcomplaintscount" => $totalcomplaint, "createtstamp" => $now, "venueid" => $venueid);
					$cresult = Campaigns::add_campaign($campaigninfo, $messageid);
					print_r($cresult);
					$campstatus = $cresult['status'];
					if($campstatus == true)
					{
						$campaction = $cresult['action'];
						if($campaction == "insert")
						{
							$campinsert++;
							$tci++;
						}
						else
						{
							$campupdate++;	
							$tcu++;
						}
					}
					else
					{
						$camperror++;			
						$tce++;
					}
					foreach ($xml->CAMPAIGNREPORT->UNSUBSCRIBERS->UNSUBSCRIBERSDETAILS->UNSUBSCRIBERSDETAIL as $unsubscribers) 
					{
				        	$unsubsmail = cleanup($unsubscribers->EMAILADDRESS);
				        	$firstname = cleanup($unsubscribers->FIRSTNAME);
				        	$lastname = cleanup($unsubscribers->LASTNAME);
				        	$unsubscribeddatetime = cleanup($unsubscribers->UNSUBSCRIBEDDATETIME);
				        	$unsubscribeddate=strtotime("$unsubscribeddatetime");
				        	$ipaddress = cleanup($unsubscribers->IPADDRESS);
				        	$unsubscribersinfo = array("email" => $unsubsmail, "firstname" => $firstname, "lastname" => $lastname, "datetime" => $unsubscribeddate, "ipaddress" => $ipaddress, "createtstamp"=>$now, "venueid" => $venueid);
				        	
				        	$uresult = Unsubscribers::add_unsubscriber($unsubscribersinfo, $messageid);
				        	print_r($uresult);
				        	$ustatus = $uresult['status'];
				        	if($ustatus == true)
				        	{
					        	$uaction = $uresult['action'];
					        	if($uaction == "insert")
					        	{
					        		$unsinsert++;
					        		$tui++;
					        	}		
					        	else
					        	{
					        		$unsupdate++;	
					        		$tuu++;
					        	}	
				        	}
				        	else
				        	{
				        		$unserror++;
				        		$tue++;
				        	}	
				    }
				    
				    foreach ($xml->CAMPAIGNREPORT->OPENS->OPENADDRESSES->OPENADDRESS as $openemails) 
			        {
			        	$opmail = cleanup($openemails->EMAILADDRESS);
			        	$opfirstname = cleanup($openemails->FIRSTNAME);
			        	$oplastname = cleanup($openemails->LASTNAME);
			        	$numberofopens = cleanup($openemails->NUMBEROFOPENS);
			        	$lastopendatetime = cleanup($openemails->LASTOPENDATETIME);
			        	$lastopendatetime=strtotime("$lastopendatetime");
			        	$ipaddress = cleanup($openemails->IPADDRESS);
			        	$openinfo = array("email" => $opmail, "firstname" => $opfirstname, "lastname" => $oplastname, "nofopens" => $numberofopens, "lastopen" => $lastopendatetime, "ipaddress" => $ipaddress, "createtstamp" => $now, "venueid" => $venueid);
			        	$oresult = Openaddress::add_openaddress($openinfo, $messageid);
			        	print_r($oresult);
			        	$oestatus = $oresult['status'];
			        	if($oestatus == true)
			        	{
				        	$oaction = $oresult['action'];
				        	if($oaction == "insert")
				        	{
				        		$openinsert++;
				        		$toi++;
				        	}	
				        	else
				        	{
				        		$openupdate++;	
				        		$tou++;
				        	}	
			        	}
			        	else
			        	{
			        		$openerror++;
			        		$toe++;
			        	}	
			        }
			        
			        foreach ($xml->CAMPAIGNREPORT->BOUNCES->BOUNCEDADDRESSES->BOUNCEADDRESS as $BOUNCEADDRESS) 
			        {
			        	$bouncemail = cleanup($BOUNCEADDRESS->EMAILADDRESS);
			        	$bouncereason = cleanup($BOUNCEADDRESS->REASON);
			        	$firstname = cleanup($BOUNCEADDRESS->FIRSTNAME);
			        	$lastname = cleanup($BOUNCEADDRESS->LASTNAME);
			        	$bounceinfo = array("email" => $bouncemail, "firstname" => $firstname, "lastname" => $lastname, "reason" => $bouncereason, "createtstamp" => $createtstamp, "venueid" => $venueid);
			        	$bresult = Bounceaddress::add_bounceaddress($bounceinfo, $messageid);
			        	print_r($bresult);
			        	$bstatus = $bresult['status'];
			        	if($bstatus == true)
			        	{
				        	$baction = $bresult['action'];
				        	if($baction == "insert")
				        	{
				        		$bounceinsert++;
				        		$tbi++;
				        	}	
				        	else
				        	{
				        		$bounceupdate++;	
				        		$tbu++;
				        	}
			        	}	
			        	else
			        	{
			        		$bounceerror++;
			        		$tbe++;
			        	}	
			        }
			        
			        foreach ($xml->CAMPAIGNREPORT->CLICKS->CLICKDETAILS->CLICKDETAIL as $clickdetail) 
			        {
			        	$clickemail = cleanup($clickdetail->EMAILADDRESS);
			        	$firstname = cleanup($clickdetail->FIRSTNAME);
			        	$lastname = cleanup($clickdetail->LASTNAME);
			        	$numberclicks = cleanup($clickdetail->NUMBEROFCLICKS);
			        	$urlclicked = cleanup($clickdetail->URLCLICKED);
			        	$clickeddatetime = cleanup($clickdetail->CLICKEDDATETIME);
			        	$clickeddatetime=strtotime("$clickeddatetime");
			        	$clickinfo = array("email" => $clickemail, "firstname" => $firstname, "lastname" => $lastname, "numberofclick" => $numberclicks, "urlclicked" => $urlclicked, "clickdatetime" => $clickeddatetime, "createtstamp" => $now, "venueid" => $venueid);
			        	$clickresult = Clickdetails::add_clickdetails($clickinfo, $messageid);
			        	print_r($clickresult);
			        	$clickstatus = $clickresult['status'];
			        	if($clickstatus)
			        	{
				        	$clickaction = $clickresult['action'];
				        	if($clickaction == "insert")
				        	{
				        		$clickinsert++;
				        		$tcli++;
				        	}	
				        	else
				        	{
				        		$clickupdate++;	
				        		$tclu++;
				        	}	
			        	}
			        	else
			        	{
			        		$clickerror++;
			        		$tcle++;
			        	}	
			        }
				}
			}
		}
		else
		{
			$globalerror++;
		}
	}
	else
	{
		$globalerror++;
	}
	if($globalerror == 0)
	{
		$total = $unsinsert + $unsupdate + $unserror + $openinsert + $openupdate + $openerror + $bounceinsert + $bounceupdate + $bounceerror + $clickinsert + $clickupdate + $clickerror;
		$totalinsert = $unsinsert + $bounceinsert + $clickinsert + $openinsert;
		$totalupdate = $unsupdate + $bounceupdate + $clickupdate + $openupdate;
		$totalerror = $unserror + $bounceerror + $clickerror + $openerror;
		$totalbaddress = $bounceinsert + $bounceupdate + $bounceerror;
		$totalopen = $openinsert + $openupdate + $openerror;
		$totalclick = $clickinsert + $clickupdate + $clickerror;
		$totaluns = $unsinsert + $unsupdate + $unserror;
		$cad .= "Campaign $cname:\n\n";	
		$cad .= "Total: $total\n";
		$cad .= "Insert: $totalinsert\n";
		$cad .= "Update: $totalupdate\n";
		$cad .= "Error: $totalerror\n\n";
		$cad .= "Bounce Address: \n\n";
		$cad .= "Total: $totalbaddress\n";
		$cad .= "Insert: $bounceinsert\n";
		$cad .= "Update: $bounceupdate\n";
		$cad .= "Error: $bounceerror\n\n";
		$cad .= "Open Address:\n\n";
		$cad .= "Total: $totalopen\n";
		$cad .= "Insert: $openinsert\n";
		$cad .= "Update: $openupdate\n";
		$cad .= "Error: $openerror\n\n";
		$cad .= "Click Details:\n\n";
		$cad .= "Total: $totalclick\n";
		$cad .= "Insert: $clickinsert\n";
		$cad .= "Update: $clickupdate\n";
		$cad .= "Error: $clickerror\n\n";
		$cad .= "Unsubscribers:\n\n";
		$cad .= "Total: $totaluns\n";
		$cad .= "Insert: $unsinsert\n";
		$cad .= "Update: $unsupdate\n";
		$cad .= "Error: $unserror\n\n";
	}
	$total = 0;
	$unsinsert = 0;
	$unsupdate = 0;
	$unserror = 0;
	$openinsert = 0; 
	$openupdate = 0;
	$openerror = 0;
	$bounceinsert = 0;
	$bounceupdate = 0;
	$bounceerror = 0;
	$clickinsert = 0;
	$clickupdate = 0;
	$clickerror = 0;
	$totalinsert = 0;
	$totalupdate = 0;
	$totalerror = 0;
	$totalbaddress = 0;
	$totalopen = 0;
	$totalclick = 0;
	$totaluns = 0;
	$globalerror = 0;
	$dateaction = date("Y-m-d H:i:s", time());
	$newtimeupdate = $timesupdate + 1;
	if($ccampupdate)
	{
		 $updatequery = "update NEWSLETTERblasts set lasttimeupdate='$dateaction', timesupdate='$newtimeupdate' where aliencampaingid='$messageid'";
		 update($updatequery);
	}
	$ccampupdate = false;	
}
$ttotal = $tbe + $tbi + $tbu + $toe + $toi + $tou + $tcle + $tcli + $tclu + $tue + $tui + $tuu;
$ttc = $tce + $tci + $tcu;
$ttb = $tbe + $tbi + $tbu;
$tto = $toe + $toi + $tou;
$ttcl = $tcle + $tcli + $tclu;
$ttu = $tue + $tui + $tuu;
$date1 = date("Y-m-d H:i:s", time());
$header = "$date1\n\n";
$header .= "Total: $ttotal\n";
$header .= "Total Campaigns: $ttc\n";
$header .= "Campaigns with error: $globalerror\n";
$header .= "Total Campaigns Insert: $tci\n";
$header .= "Total Campaigns Update: $tcu\n";
$header .= "Total Campaigns Error: $tce\n";
$header .= "Total Bounce Address: $ttb\n";
$header .= "Total Bounce Address Insert: $tbi\n";
$header .= "Total Bounce Address Update: $tbu\n";
$header .= "Total Bounce Address Error: $tbe\n";
$header .= "Total Open Address: $tto\n";
$header .= "Total Open Address Insert: $toi\n";
$header .= "Total Open Address Update: $tou\n";
$header .= "Total Open Address Error: $toe\n";
$header .= "Total Click Details: $ttcl\n";
$header .= "Total Click Details Insert: $tcli\n";
$header .= "Total Click Details Update: $tclu\n";
$header .= "Total Click Details Error: $tcle\n";
$header .= "Total Unsubscribers: $ttu\n";
$header .= "Total Unsubscribers Insert: $tui\n";
$header .= "Total Unsubscribers Update: $tuu\n";
$header .= "Total Unsubscribers Error: $tue\n\n";

$day = date("H", time());
if($day >= 8 && $day <= 23)
	$cont = 1;
else if($day >= 0 && $day < 8)	
	$cont = 2;
$logmsg =$header.$cad;
echo $logmsg;
$filelogname = "campaignsupdate.$cont";
makecronlog($filelogname, $logmsg);

