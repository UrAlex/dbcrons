#!/usr/bin/php
<?php
	include_once("../dblibraries/lib.events.php");
	include_once("../dbfunctions/addimage.php");
	include_once("../dbfunctions/cronlogs.php");

	$venues = getrows("select fv.ForeignVenueID, fv.VenueID, fv.ApiKey, fv.VenueUrl, ve.Name from ZEN_ForeignVenues as fv join ZEN_Venues as ve on fv.VenueID=ve.VenueID where fv.ApiKey<>'' and fv.VenueUrl<>''");
	$numvenues = $venues[0]['total'];
	$logmsg = "";

	for($i=1; $i<=$numvenues; $i++){
		$foreignvenueid = $venues[$i]['ForeignVenueID'];
		$venueid = $venues[$i]['VenueID'];
		$venuename = $venues[$i]['Name'];
		$apikey = $venues[$i]['ApiKey'];
		$venueurl = $venues[$i]['VenueUrl'];
		$logmsg .= "----- $venuename -------\n\n";
		$params = array( 'key' => $apikey,'venue' => $venueurl);
		$client = new SoapClient('http://embed.laughstub.com/ElectroStubAPI/getProducts.cfc?wsdl');
		$result = $client->__soapCall('getProducts',$params);
		$obj = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
		$shows = get_object_vars($obj);
		$shows = $shows['products'];	
		$shows = get_object_vars($shows);
		$shows = $shows['product'];
		$numshows = count($shows);

		if($numshows >= 1){
			for($a=0; $a<$numshows; $a++){
				$show = get_object_vars($shows[$a]);
				$showid = $show['showTimingID'];		
				$showname = cleanup($show['showName']);
				$showdesc = cleanup($show['showDescription']);
				$showdate = cleanup($show['ResDate']);
				$showtime = cleanup($show['ResTime']);
				$showdatetime = $showdate." ".$showtime;
				$posters = $show['posters'];
				$posters = get_object_vars($posters);
				$poster1 = $posters['poster1'];
				$poster2 = $posters['poster2'];
				$poster3 = $posters['poster3'];
				$poster4 = $posters['poster4'];
				$assets = $show['assets'];
				$assets = get_object_vars($assets);
				$flyerimg = $assets['flyerImage'];
				$combinedInstagram = $assets['combinedInstagram'];
				$combinedFacebook = $assets['combinedFacebook'];
				$facebookImage = $assets['facebookImage'];
				$instagramImage = $assets['instagramImage'];
				$logmsg .= "Show ID: $showid\n";
				//$result_foreignshow = ForeignEvents::get_foreignevent($showid, "", $venueid);
				//if($result_foreignshow[0]['total']==0){				
					$result_event = Events::add_event($showname, $showdesc, $venueid, $showid, $showdatetime);
					$status = $result_event['status'];				
					if($status == 'true'){
						$action = $result_event['action'];
						$newshowid = $result_event['eventid'];
						$logmsg .= "Show $action\nShow id: $newshowid\n";

						if($poster1){
							$result_img = addimagesbyurl($poster1, $newshowid, 1);
							$statusimg = $result_img['status'];
							if($statusimg == "true"){
								$imgpath = $result_img['imgpath'];
								$logmsg .= "Main Flyer path: $imgpath\n";
							}else{
								$errorimg = $result_img['error'];
								$logmsg .= "Main Flyer path: $errorimg\n";
							}
						}

						if($poster2){
							$result_img = addimagesbyurl($poster2, $newshowid, 2);
							$statusimg = $result_img['status'];
							if($statusimg == "true"){
								$imgpath = $result_img['imgpath'];
								$logmsg .= "Flyer 2 path: $imgpath\n";
							}else{
								$errorimg = $result_img['error'];
								$logmsg .= "Flyer 2 path: $errorimg\n";
							}
						}

						if($poster3){
							$result_img = addimagesbyurl($poster3, $newshowid, 3);
							$statusimg = $result_img['status'];
							if($statusimg == "true"){
								$imgpath = $result_img['imgpath'];
								$logmsg .= "Flyer 3 path: $imgpath\n";
							}else{
								$errorimg = $result_img['error'];
								$logmsg .= "Flyer 3 path: $errorimg\n";
							}
						}

						if($poster4){
							$result_img = addimagesbyurl($poster4, $newshowid, 4);
							$statusimg = $result_img['status'];
							if($statusimg == "true"){
								$imgpath = $result_img['imgpath'];
								$logmsg .= "Flyer 4 path: $imgpath\n";
							}else{
								$errorimg = $result_img['error'];
								$logmsg .= "Flyer 4 path: $errorimg\n";
							}
						}

						if($flyerimg){
							$result_img = addimagesbyurl($flyerimg, $newshowid, 5);
							$statusimg = $result_img['status'];
							if($statusimg == "true"){
								$imgpath = $result_img['imgpath'];
								$logmsg .= "Asset Flyer Image path: $imgpath\n";
							}else{
								$errorimg = $result_img['error'];
								$logmsg .= "Asset Flyer Image path: $errorimg\n";
							}
						}

						if($combinedInstagram){
							$result_img = addimagesbyurl($combinedInstagram, $newshowid, 6);
							$statusimg = $result_img['status'];
							if($statusimg == "true"){
								$imgpath = $result_img['imgpath'];
								$logmsg .= "Combined Instagram path: $imgpath\n";
							}else{
								$errorimg = $result_img['error'];
								$logmsg .= "Combined Instagram path: $errorimg\n";
							}
						}

						if($combinedFacebook){
							$result_img = addimagesbyurl($combinedFacebook, $newshowid, 7);
							$statusimg = $result_img['status'];
							if($statusimg == "true"){
								$imgpath = $result_img['imgpath'];
								$logmsg .= "Combined Facebook path: $imgpath\n";
							}else{
								$errorimg = $result_img['error'];
								$logmsg .= "Combined Facebook path: $errorimg\n";
							}
						}

						if($facebookImage){
							$result_img = addimagesbyurl($facebookImage, $newshowid, 8);
							$statusimg = $result_img['status'];
							if($statusimg == "true"){
								$imgpath = $result_img['imgpath'];
								$logmsg .= "Facebook Image path: $imgpath\n";
							}else{
								$errorimg = $result_img['error'];
								$logmsg .= "Facebook Image path: $errorimg\n";
							}
						}

						if($instagramImage){
							$result_img = addimagesbyurl($instagramImage, $newshowid, 9);
							$statusimg = $result_img['status'];
							if($statusimg == "true"){
								$imgpath = $result_img['imgpath'];
								$logmsg .= "Instagram Image path: $imgpath\n";
							}else{
								$errorimg = $result_img['error'];
								$logmsg .= "Instagram Image path: $errorimg\n";
							}
						}	

						$logmsg .= "\n";										
					}else{
						$showerror = $result_event['error'];

						$logmsg .= "Error: $showerror\n\n";
					}
				//}else{
				//	$logmsg .= "Already Exits in the DB.\n\n";
				//}
			}
		}else{
			$logmsg .= "No shows to import\n\n";
		}		
	}

	$time = date("H", time());
	$filename = "events.$time";
	makecronlog($filename);

