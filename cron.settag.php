#!/urs/bin/php
<?php
	include_once("../dblibraries/lib.settag.php");
	include_once("../main_plugins/nusoap/nusoap.php");
	
	$query = "select * from ZEN_Settag where Status='pending' order by CreatedTIMESTAMP asc limit 0,1";
	$rows = getrows($query);
	$numrows = $rows[0]['total'];
	for($i=1; $i<=$numrows; $i++){
		$settagid = $rows[$i]['SettagID'];
		
		$query1 = "select * from ZEN_SettagQueue where SettagID='$settagid' and Status='pending' order by CreatedTIMESTAMP asc ";
		$rows1 = getrows($query1);
		$numrows1 = $rows1[0]['total'];
		
		
		$queueid = $rows1[1]['SettagQueueID'];
		
		$xml = file_get_contents("../sys_settag/promoqueue/settag.$queueid.log");
		
		$client=new nusoap_client('http://services.stun1.com/messagingAPI/?wsdl', true);
		$params=array('messageinput'=>$xml);	
		$result=$client->call('CreateMessage',$params);
		$res_camp = simplexml_load_string($result);
		$code = $res_camp->RESPONSE->RESPONSECODE;
		$errormsg = $res_camp->RESPONSE->RESPONSEERRORMESSAGE;
		$messageid = $res_camp->RESPONSE->MESSAGEID;
		$now = date("Y-m-d H:i:s", time());
		
		$query = "update ZEN_SettagQueue set OptTIMESTAMP='$now'";
		if($code == true){
			$query .= ", Status='complete', MessageID='$messageid'";
		}else{
			$query .= ", Status='Error: $errormsg'";
		}
		$query .= " where SettagQueueID='$queueid'";
		
		update($query);
		
		if($numrows1==1){
			update("update ZEN_Settag set Status='complete', OptTIMESTAMP='$now' where SettagID='$settagid'");	
		}
	}


