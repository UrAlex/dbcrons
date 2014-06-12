<?
include_once("../main_functions/mailchimp.php");
include_once("../dblibraries/lib.customers.php");
include_once("../dblibraries/lib.venues.php");
include_once("../dbfunctions/cronlogs.php");
$insert = 0;
$update = 0;
$error = 0;
$cad = "";
$date = date("Y-m-d H:i:s");
$array = array("apikey" => $apikey);
$result = mailchimp_list($apikey, "lists/list", $array);
print_r($result);
for($i = 0; $i < count($result['data']); $i++)
{
	$id = $result['data'][$i]['id'];
	$webid = $result['data'][$i]['web_id'];
	$name = $result['data'][$i]['name'];
	$date_created = $result['data'][$i]['date_created'];
	$email_type_option = $result['data'][$i]['email_type_option'];
	$use_awesomebar = $result['data'][$i]['use_awesomebar'];
	$default_from_name = $result['data'][$i]['default_from_name'];
	$default_from_email = $result['data'][$i]['default_from_email'];
	$default_subject = $result['data'][$i]['default_subject'];
	$default_language = $result['data'][$i]['default_language'];
	$list_rating = $result['data'][$i]['list_rating'];
	$subscribe_url_short = $result['data'][$i]['subscribe_url_short'];
	$subscribe_url_long = $result['data'][$i]['subscribe_url_long'];
	$beamer_address = $result['data'][$i]['beamer_address'];
	$visibility = $result['data'][$i]['visibility'];
	$stats = $result['data'][$i]['stats'];
	$member_count = $stats['member_count'];
	$unsubscribe_count = $stats['unsubscribe_count'];
	$cleaned_count = $stats['cleaned_count'];
	$member_count_since_send = $stats['member_count_since_send'];
	$unsubscribe_count_since_send = $stats['unsubscribe_count_since_send'];
	$cleaned_count_since_send = $stats['cleaned_count_since_send'];
	$campaign_count = $stats['campaign_count'];
	$grouping_count = $stats['grouping_count'];
	$group_count = $stats['group_count'];
	$merge_var_count = $stats['merge_var_count'];
	$avg_sub_rate = $stats['avg_sub_rate'];
	$avg_unsub_rate = $stats['avg_unsub_rate'];
	$target_sub_rate = $stats['member_count'];
	$open_rate = $stats['open_rate'];
	$click_rate = $stats['click_rate'];
	$date_last_campaign = $stats['date_last_campaign'];
	$venues = Venues::get_venues("", $name);
	if($venues[0]['total'] != 0)
		$venueid = $venues[1]['VenueID']; 
	//$arraylist = array('apikey' => $apikey, 'id' => "43c6ad78c4");
	$response = mailchimp_members($apikey, $id, "2014-01-21");
	print_r($response);
	//echo count($response['data']);
	for($j=0; $j < count($response['data']); $j++)
	{
		$data = $response['data'][$j];
		$memberemail = $data['email'];
		$membertimestamp_signup = $data['timestamp'];
		$membermerge = $data['merges'];
		if(count($membermerge) != 0)
		{
			$mmemail = $membermerge['EMAIL'];
			$mmfname = $membermerge['FNAME'];
			$mmlname = $membermerge['LNAME'];
			$mmzipcode = $membermerge['ZIPCODE'];
		}
		$membergeo = $data['geo'];
		if(count($membergeo) != 0)
		{
			$membercc = $membergeo['cc'];
			$memberregion = $membergeo['region'];
		}
		//echo "Email: $memberemail, Firstname: $mmfname, Lastname: $mmlname, Zipcode: $mmzipcode, Country: $membercc, State: $memberregion, Createtstamp: $membertimestamp_signup\n";
			$customer = array("firstname" => $mmfname, "lastname" => $mmlname, "zipcode" => $mmzipcode, "email" => $mmemail, "city" => $membercc, "state" => $memberregion, "createtstamp" => $membertimestamp_signup);
			print_r($customer);
			echo "\n";
			//$addcust = Customers::add_customers($customer, "2", $venueid);
			/*
if($addcust['status'] == true)
			{
				if($addcust['action'] == "insert")
				{
					$insert++;
					$cad .= "Fistname: $mmfname\nLastname: $mmlname\nEmail: $mmemail\nAction: Insert\n\n";
				}
				else
				{
					$update++;
					$cad .= "Fistname: $mmfname\nLastname: $mmlname\nEmail: $mmemail\nAction: Update\n\n";
				}
			}
			else
			{
				$errormsg = $addcust['error'];
				$error++;
				$cad .= "Fistname: $mmfname\nLastname: $mmlname\nEmail: $mmemail\nError: $errormsg\n\n";
			}
*/
	}
}
/*
$total = $insert+$update+$error;
$logmsg = $date."\n\nTotal: $total\nInsert: $insert\nUpdate: $update\nError: $error\n\n".$cad;
$filelogname = "mailchimp";
makecronlog($filelogname);
*/
