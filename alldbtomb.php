#!/usr/bin/php
<?php
	include_once("../dblibraries/lib.customers.php");
	include_once("../main_functions/mobilestorm.php");
	include_once("../dbfunctions/cronlogs.php");
	$date = date("Y-m-d");
	$insert = 0;
	$update = 0;
	$error = 0;
	$cad = "";
	$limit = file_get_contents("alldbcont.txt");
	$query2 = "select count(*) from ZEN_Customer2Source";
	$rows2 = getrows($query2);
	$numrows2 = $rows2[1][0];
	if($limit <= $numrows2)
	{
		echo $query = "select distinct Email from ZEN_Customers where Email<>'' order by CreatedTIMESTAMP asc limit $limit,1000";
		echo "\n";
		$rows = getrows($query);
		$numrows = $rows[0]['total'];
	
		for($i=1; $i<=$numrows; $i++)
		{
			$custid = $rows[$i]['CustID'];
			/*
$sourceid = $rows[$i]['SourceID'];
			$venueid = $rows[$i]['VenueID'];
			$affiliateid = $rows[$i]['AffiliateID'];
			$csvid = $rows[$i]['CSVID'];
			$created = $rows[$i]['CreatedTIMESTAMP'];	
			$cutomer = getrows("select * from ZEN_Customers where CustID='$custid'");
*/
			$firstname = $rows[$i]['First Name'];
			$middlename = $rows[$i]['Middle Name'];
			$lastname = $rows[$i]['Last Name'];
			$cellphone = $rows[$i]['Cellphone'];
			$email = $rows[$i]['Email'];
			$dob = $rows[$i]['DOB'];
			$address = $rows[$i]['Address'];
			$city = $rows[$i]['City'];
			$state = $rows[$i]['State'];
			$country = $rows[$i]['Country'];
			$zipcode = $rows[$i]['Zipcode'];
			$gender = $rows[$i]['Gender'];		
			$over21 = $rows[$i]['Over21'];		
			$dob = str_replace("-", "/", $dob);
			$dobarr = explode("/", $dob);
			$year = $dobarr[0];
			$month = $dobarr[1];
			$day = $dobarr[2];		
			if($gender == "M")
				$gender = "male";
			else if($gender == "F")	
				$gender = "female";
			else if($gender == "u")
				$gender = "";
			
		/*
	if($email)
			{
				$sourcename = getvalue("select Name from ZEN_Sources where SourceID='$sourceid'");
				$vertical = "NIGHTLIFE";
				$venuename = getvalue("select Name from ZEN_Venues where VenueID='$venueid'");
				$fullname = "$firstname $lastname";
				$createby = $venuesource = $stdfirstname = $stdlastname = $homephone = $workphone = $otherphone = $fulladdress = $addr2 = $eopt = $mopt = $topt = $copt = $popt = $evalid = $mvalid = $tvalid = $cvalid = $friend = $prefered = $lasttrans = $moddate = $unsdate = $unsreason = $sourcedate = $sourcemoddate = $sourcemoduser = "";
				
				if($affiliateid)
				{
					$foundersarr = getvalue("select AffiliateID from ZEN_Customer2Source where custid='$custid' limit 1");
					$founder = getvalue("select Name from ZEN_Affiliates where AffiliateID='$affiliateid'");				
				}else
				{
					$founder = "";
				}	
			
			}
*/
			echo $email."\n";
			$customer['email'] = $email;
			$response = inserttomblist("14442", $i, $customer, $apiaccesskey);
			if($response['status'] == true)
			{
				if($response['action'] == "update")
				{
					$custevent = $response['events'];
					$update++;
					$cad .= "Email: $email\nAction: Update\n\n";
				}
				else
				{
					$custevent = $response['events'];
					$insert++;
					$cad .= "Email: $email\nAction: Insert\n\n";		
				}
			}
			else
			{
				$custevent = $response['events'];
				$error++;
				$msgerror = $response['error'];
				$cad .= "Email: $email\nError: $msgerror\n\n";
			}
			unset($customer);
		}
		$total = $update + $insert + $error;
		$header = "$date\n\nTotal: $total\nInsert: $insert\nUpdate: $update\nError: $error\n\n";
		$logmsg =$header.$cad;
		
		echo $logmsg;
		$filelogname = "alldbtomb.$limit";
		makecronlog($filelogname, $logmsg);
		$cont2= $limit + 1000;
		$file2 = fopen("alldbcont.txt", "w");
		fwrite($file2, $cont2);
		fclose($file2);
	}
