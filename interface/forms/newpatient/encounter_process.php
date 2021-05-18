<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
require_once("$srcdir/billing.inc");

//include_once("../../billing/$srcdir/patient.inc");
//include_once("../../billing/$srcdir/billrep.inc");
//include_once("../../billing/$srcdir/billing.inc");
//include_once("../../billing/$srcdir/gen_x12_837.inc.php");
//include_once("../../billing/$srcdir/gen_hcfa_1500.inc.php");
//include_once(dirname(__FILE__) . "/../../library/classes/WSClaim.class.php");
//require_once("../../billing/$srcdir/classes/class.ezpdf.php");

/*$EXPORT_INC = "$webserver_root/custom/BillingExport.php";
if (file_exists($EXPORT_INC)) {
  include_once($EXPORT_INC);
  $BILLING_EXPORT = true;
}*/

// added for BUG 10521
$todays_date = date('Y-m-d H:i:s');
$device_type = "OE";

function approveEncounters($encounters){
$conn = $GLOBALS['adodb']['db'];
foreach ($encounters as $draft_id){
	$res= sqlStatement("Select * from form_encounter_draft where id=$draft_id");
	for($iter=0; $row=sqlFetchArray($res); $iter++)
        {
           $date=$row['date'];
		   $pid=$row['pid'];
		   $provider_id=$row['provider_id'];
		   $flag_check=0;
		   $facility_id=$row['facility_id'];
		   $batch_id=$row['batch_id'];
		   $reason=$row['reason'];
		   
		   if($facility_id=='' || $provider_id=='' || $batch_id=='' || $reason=='' || $date=='' )
		   $flag_check=1;
		  
		   
		   $res_patient = sqlStatement("select concat(street,postal_code) as addr,dob,fname from patient_data where pid= $pid");
		    $res_pd=sqlFetchArray($res_patient);		   
		   if($res_pd['addr']=="" || $res_pd['dob']=="" || $res_pd['fname']=="")
		   $flag_check=1;
		   
		   
			$res_ins = sqlStatement("select count(provider) as prov_count from insurance_data where pid=$pid");
			$row_ins=sqlFetchArray($res_ins);
						
				if($row_ins['prov_count']<3)
				$flag_check=1;
				
			
		   if($flag_check==0){			      
		   $chk_encounter_qry=sqlStatement("Select count(*) as count,encounter from form_encounter where date='$date' and pid=$pid and provider_id=$provider_id ");
		   $chk_encounter=sqlFetchArray($chk_encounter_qry);
		   $count = $chk_encounter['count'];
		   $enc_id = $chk_encounter['encounter'];
		   
		   
		   
		   $bill_qry1 = sqlStatement("select count(DISTINCT(code_type)) as code_count from billing_draft where draft_id = $draft_id group by draft_id");
		   $biil_res1 = sqlFetchArray($bill_qry1);
		   $bill_count = $biil_res1['code_count']; 
		   if($bill_count < 3)
		    $flag_check =1; 
		   
		  
		  $flag=0;
		  $Total_price =0;
		   if($count>0 && $enc_id!='' && $flag_check==0)
	        {			
				$Get_draft_code = sqlStatement("Select code,fee from billing_draft where draft_id='$draft_id'");	
				while($Arr_draft_code = sqlFetchArray($Get_draft_code)){
				$code_draft_arr[] = $Arr_draft_code['code'];
				$code_fee_arr[] = $Arr_draft_code['fee'];
				}	
									
				$Get_code = sqlStatement("Select code from billing where encounter='$enc_id'");		
				while($Arr_code = sqlFetchArray($Get_code)){
				$code_arr[] = $Arr_code['code'];
				}		
				
				for($i=0;$i<=count($code_fee_arr); $i++)
				{			
					if (array_search($code_fee_arr, $code_arr)) 
					$flag=1;	
					$Total_price = 	$Total_price + $code_fee_arr[$i];
				}				
				if($flag==1)
				{
					print '<script type="text/javascript">';
	print 'alert("The same encounter already created for this patient.")';
	print '</script><script type="text/javascript" language="javascript">';	
	echo "top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/encounter_summary.php')"; 
	print '</script>';
				}
				
				if($Total_price==0)
				{
					print '<script type="text/javascript">';
	print 'alert("This encounter is not accepted as Amount is $0.")';
	print '</script><script type="text/javascript" language="javascript">';	
	echo "top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/encounter_summary.php')"; 
	print '</script>';
				}
			}		
			//die;
			//echo "***".$flag."***".$Total_price."***".$count."***".$flag_check;
				if($flag==0  && $count==0 && $flag_check==0)	   {
				$encounter = $conn->GenID("sequences");
				$qry = sqlStatement("INSERT INTO form_encounter SET " .
					  "date = '$row[date]', " .
					  "onset_date = '$row[onset_date]', " .
					   "device_type = '$device_type', " . // added for BUG ID 10521
					  "created_date = '$todays_date', " . // added for BUG ID 10521
					  "reason =  '$row[reason]'," .
					  "facility = '$row[facility]', " .
					  "pc_catid = '$row[pc_catid]', " .
					  "facility_id = '$row[facility_id]', " .
					  "billing_facility = '$row[billing_facility]', " .
					  "sensitivity = '$row[sensitivity]', " .
					  "referral_source = '$row[referral_source]', " .
					  "pid = '$row[pid]', " .
					  "encounter = '$encounter', " .
					  "provider_id = '$row[provider_id]',".
					  "supervisor_id = '$row[supervisor_id]',".
					  "batch_id = '$row[batch_id]',".
					  "pos_id = '$row[pos]',".
					  "claim_status_id = '$row[claim_status_id]' ");
					  
					  $modifier_user=$_SESSION{"authUser"};
					  $qry2=sqlStatement("Select max(id) as form_id from form_encounter");
					  $res2 = sqlFetchArray($qry2);
					  $form_id = $res2['form_id'];  
					 
		
					$qry3=  sqlStatement("INSERT INTO forms SET ".
					  "date = '$row[date]', ".
					  "encounter = '$encounter', " .
					  "form_name = 'New Patient Encounter', " .
					  "form_id = '$form_id', " .
					  "pid = '$row[pid]', " .
					  "user = '$modifier_user', " .
					  "groupname = 'Default', " .
					  "authorized = '0', " .
					  "deleted = '0', " .
					  "formdir = 'newpatient' " 					  
					  );
					  
					   
					  $EncounterStatusqry= sqlStatement("select status from claim_status where id=$row[claim_status_id]" );
					   $EncounterStatus = sqlFetchArray($EncounterStatusqry);
					  $enc_status=$EncounterStatus['status'];
	
$sql="INSERT INTO `encounter_status`(`Encounter`,`Status`,`Status_Date`,`modifier`)VALUES(
'$encounter','$enc_status','$row[date]','$modifier_user')";
sqlStatement( $sql );

//code_type,code_text, pid, authorized, activity,provider_id,modifier, units, ndc_info, justify,from_dos,to_dos,tos
//echo "Select * from billing_draft where draft_id='$draft_id'"; die;
 $Get_draft_code = sqlStatement("Select * from billing_draft where draft_id='$draft_id'");	
while($billing_draft = sqlFetchArray($Get_draft_code)){
addBilling($encounter, $billing_draft['code_type'], $billing_draft['code'], $billing_draft['code_text'], $billing_draft['pid'], $billing_draft['auth'],$billing_draft['provider_id'], $billing_draft['modifier'], $billing_draft['units'], $billing_draft['fee'], $billing_draft['ndc_info'], $billing_draft['justify'],0,$billing_draft['from_dos'],$billing_draft['to_dos'],$billing_draft['tos'],$billing_draft['activity'],$row['date']);
		}
		
			
		sqlStatement("UPDATE form_encounter_draft set draft=0,review=0,clarification=0,final_status='save',encounter=$encounter where pid=$pid and id = '$draft_id'");
	sqlStatement("UPDATE billing_draft set encounter=$encounter where draft_id=$draft_id");
	
	print '<script type="text/javascript">';
	print 'alert("The selected encounter/s approved.")';
	print '</script><script type="text/javascript" language="javascript">';	
	echo "top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/encounter_summary.php')"; 
	print '</script>';					
				
    	   	 }
			}
			if($flag_check==1)
			{
			print '<script type="text/javascript">';
	print 'alert("The selected encounters not valid for approval.")';
	print '</script><script type="text/javascript" language="javascript">';	
	echo "top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/encounter_summary.php')"; 
	print '</script>';
				
			}
	
}
}
}

function draftEncounters($encounters,$mode){
//foreach ($encounters )


$draft=0;$review=0;$clarification=0;

if($mode == 'Draft')
$draft = 1;
if($mode == 'Review')
$review = 1;
if($mode == 'Clarification')
$clarification = 1;
//$date             = date('Y-m-d');

foreach ($encounters as $draft_id){
		  
    sqlQuery("UPDATE form_encounter_draft SET " .         
	  "review = '$review',".
	  "clarification = '$clarification',".	  
	  "final_status='$mode' ".
	  "WHERE id=$draft_id"
	 );	  
	
	 /*sqlQuery("UPDATE billing_draft SET date = '$date' " .
          " WHERE " .
          "draft_id = '$draft_id'"); */

}

print '<script type="text/javascript">';
	print 'alert("The selected encounter/s is/are moved in to '.$mode .'  .")';
	print '</script><script type="text/javascript" language="javascript">';	
	echo "top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/encounter_summary.php')"; 
	print '</script>';


	}

?>
<html>
<head>
<?php if (function_exists(html_header_show)) html_header_show(); ?>

<link rel="stylesheet" href="<?echo $css_header;?>" type="text/css">

</head>
<body class="body_top">
<form name="updateclaims" action="encounter_summary.php" style="display:inline">
<br><p><h3><?php xl('Results:','e'); ?></h3><a href="encounter_summary.php">back</a><ul>
<?php
$_POST['bn_review'];
if($_POST['bn_approve']=="Approve")
{
	approveEncounters($_POST['claims']);
}
if($_POST['bn_review']=="Review")
{
	draftEncounters($_POST['claims'],"Review");
}
if($_POST['bn_clarification']=="Clarification")
{
	draftEncounters($_POST['claims'],"Clarification");
}
/*foreach(){
}*/
?>
</ul></p>
</form>
</body>
</html>
