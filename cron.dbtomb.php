#!/usr/bin/php
<?php
	include_once("../dblibraries/lib.customers.php");
	include_once("../main_functions/mobilestorm.php");
	include_once("../dbfunctions/cronlogs.php");
	$date = date("Y-m-d");
	$firstdate = date("Y-m-d", strtotime("-1 day", strtotime($date)));
	$fd = $firstdate." 00:00:00";
	$date = $firstdate." 23:59:59";
	$insert = 0;
	$update = 0;
	$error = 0;
	$cad = "";
	$query = "select distinct Email from ZEN_Customers where Email<>'' and CreatedTIMESTAMP >= '$fd' and CreatedTIMESTAMP <= '$date' order by CreatedTIMESTAMP asc";
	$rows = getrows($query);
	$numrows = $rows[0]['total'];
	for($i=1; $i<=$numrows; $i++)
	{
		$email = $rows[$i]['Email'];
		if($email){
			$customerinfo['email'] = $email;
			
			$response = inserttomblist("14442", $i, $customerinfo, $apiaccesskey);
			echo $response;
			/*
if($response['status'] == true)
			{
				if($response['action'] == "update")
				{
					$update++;
					$cad .= "Email: $email\nAction: Update\n\n";
				}
				else
				{
					$insert++;
					$cad .= "Email: $email\nAction: Insert\n\n";		
				}
			}
			else
			{
				$error++;
				$msgerror = $response['error'];
				$cad .= "Email: $email\nError: $msgerror\n\n";
			}
*/
			//print_r($customerinfo);	
			//echo $email."\n";
		}
	}
	
	$total = $update + $insert + $error;
	$header = "$fd to $date\n\nTotal: $total\nInsert: $insert\nUpdate: $update\nError: $error\n\n";
	$logmsg =$header.$cad;
		
	echo $logmsg;
	$filelogname = "dbtomb";
	//makecronlog($filelogname);
