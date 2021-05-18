<?php
/**
 * Encounter form save script.
 *
 * Copyright (C) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @link    http://www.open-emr.org
 */
require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/sql.inc");
require_once("$srcdir/encounter.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/formdata.inc.php");
use OpenEMR\Services\FacilityService;
$facilityService = new FacilityService();
// Sai custom code start . move all below code while migration.
require_once("$srcdir/billing.inc");

$conn = $GLOBALS['adodb']['db'];

// added for BUG 10577
$todays_date = date('Y-m-d H:i:s');
$device_type = "OE";

$date             = date('Y-m-d',strtotime(formData('form_date')));

$onset_date       = date('Y-m-d',strtotime(formData('form_onset_date')));
//Gangeya for user Stamp in notes on encounter screen

$user = sqlQuery("select username from users where username='".$_SESSION{"authUser"}."'");
$uname = current($user);
$userID = $_SESSION['authId'];
//modified by sangram bugzilla id 7932


if ((formData('form_onset_date'))== "")
{
$onset_date = '0000-00-00 00:00:00';
}

$sensitivity      = formData('form_sensitivity');
$pc_catid         = formData('pc_catid');
$facility_id      = formData('facility_code');
$billing_facility = formData('billing_facility');
// code added for integer value issue
if(empty($billing_facility)){
	$billing_facility = 0;
}


$reason           = formData('reason');
$mode             = formData('mode');
$referral_source  = formData('form_referral_source');
$claim_status_id  = formData('Encounter_Status'); 


$pos= formData('pos');
$drafted = formData('drafted');
$draft_id = formData('draft_id');
$batch_id = formData('batch_id');
$move_to_patient_id = formData('move_to_patient_id'); 
$form_method = trim(formData('form_method'));
$check_number = trim(formData('check_number'));
$ServicingProvider = trim(formData('ServicingProvider'));
$chk_num = explode("@",$check_number);
$session_id = $chk_num[0];
if($form_method=='check_payment'){
$form_source = $chk_num[1];
}
else
{
$form_source = "";
}




/*if($enc_status){
$statusresult = sqlQuery("select id,status FROM claim_status WHERE status = '$enc_status'");
$claim_status_id = $facilityresult['id'];
}*/
if($facility_id){
$facilityresult = sqlQuery("select name FROM facility WHERE id = $facility_id");
$facility = $facilityresult['name'];
$facility = htmlentities($facility, ENT_QUOTES);
}

if($_POST['pid']){
$pid=$_POST['pid'];
$_SESSION['pid']=$pid;
$_GET['set_pid']=$pid;
}
if ($GLOBALS['concurrent_layout'])
  $normalurl = "forms/newpatient/new.php?set_pid=$pid&autoloaded=1&calenc=";
else
  $normalurl = "$rootdir/patient_file/encounter/patient_encounter.php";

$nexturl = $normalurl;

if ($mode == 'move')

{

$pid=$move_to_patient_id;
$flag=0;
   $Total_price=1;
 $provider_id = $userauthorized ? $_SESSION['authUserID'] : 0;
  

  
   $prov_id = 0 + $_POST['RenderingProvider'];
   
  $main_provid = 0 + $_POST['RenderingProvider'];
  $main_refid  = 0 + $_POST['ReferringProvider'];
    $main_supid = 0 + $_POST['SupervisingProvider'];
  $main_pcp  = 0 + $_POST['PrimaryCareProvider'];
  $main_serPro  = 0 + $_POST['ServicingProvider'];
  
  if ($main_supid == $main_provid) $main_supid = 0;

foreach($_POST['bill'] as $value)
		{
			if($value['price']=='0')
			$Total_price = 	0;
			if($_POST['bill'][11]['price']==0)
			$Total_price = 	1;
		}
  /* for($i=0;$i<=count($_POST['bill']); $i++)
		{		
			if($_POST['bill'][$i]['price']=='0')
			$Total_price = 	0;
			if($_POST['bill'][11]['price']==0)
			$Total_price = 	1;
		}	*/
	if($Total_price==0)
	{
	print '<script type="text/javascript">';
print 'alert("This encounter is not accepted as Amount is $0.")';
print '</script>'; 
echo "<script>top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/new.php?enc=1&autoloaded=1&calenc=')</script>";
	}
	else {
 
    $chk_encounter = sqlQuery("Select count(*) as count,encounter from form_encounter where date = '$date' and pid = '$pid' and provider_id = '$prov_id'");
	$count = $chk_encounter['count'];
	$enc_id = $chk_encounter['encounter'];
	
	if($count>0)
	{
		$Get_code = sqlQuery("Select code from billing where encounter='$enc_id'");		
	    while($Arr_code = sqlFetchArray($Get_code)){
		$code_arr[] = $Arr_code['code'];
		}		
		
		$Total_price =0;
		foreach($_POST['bill'] as $value)
		{
			if (array_search($value['code'], $code_arr)) 
			$flag=1;	
			$Total_price = 	$Total_price + $value['price']	;
		}
		/*for($i=0;$i<=count($_POST['bill']); $i++)
		{			
			if (array_search($_POST['bill'][$i]['code'], $code_arr)) 
			$flag=1;	
			$Total_price = 	$Total_price + $_POST['bill'][$i]['price']	;
		}*/		
	} 
	if($Total_price==0)
	{
	print '<script type="text/javascript">';
	print 'alert("This encounter is not accepted as Amount is $0.")';
	print '</script>'; 
	echo "<script>top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/new.php?enc=1&autoloaded=1&calenc=')</script>";
	}
	if($flag==1)
	{
	print '<script type="text/javascript">';
print 'alert("The same encounter already created for the patient to which you are moving charge")';
print '</script>'; 
echo "<script>top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/new.php?enc=1&autoloaded=1&calenc=')</script>";
	}

}

if($Total_price!=0 && $flag!=1)
{
	$userID =$_SESSION['authId'];
$rs= sqlQuery("CALL move_encounter('$move_to_patient_id','$encounter','$userID',@result)");
//$rs= sqlQuery("CALL move_encounter('$move_to_patient_id','$encounter',@result)");
$rs = sqlQuery( "SELECT @result");
//$row = sqlFetchArray($rs);
// code change because sqlQuery itself return a associavtive single row
if($rs['@result']=='1')
{
echo '<script language="javascript">';
echo 'alert("Encounter Sucessfully moved without any changes")';
echo '</script>';
echo "<script>top.window.parent.left_nav.loadFrame2('nen1','RTop','patient_file/summary/demographics.php?set_pid=$pid')</script>";

}
else
{
echo '<script language="javascript">';
echo 'alert("Unable to move encounter, as payment already done")';
echo '</script>';
echo "<script>top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/new.php?enc=1&autoloaded=1&calenc=')</script>";
}

}
//echo "CALL move_encounter('$move_to_patient_id','$encounter',@result)";
//echo "CALL move_encounter('$move_to_patient_id','$encounter',@result);SELECT @result";

//echo '<pre>';

//echo '</pre>';
//echo $encounter;
//echo $move_to_patient_id;

}

if ($mode == 'new' || $mode == 'draft' || $mode == 'review' || $mode == 'clarification')
{
  $provider_id = $userauthorized ? $_SESSION['authUserID'] : 0;
  
  if($mode == 'new')
  $encounter = $conn->GenID("sequences");
  else
  $encounter =1;
  
   $prov_id = 0 + $_POST['RenderingProvider'];
   
  $main_provid = 0 + $_POST['RenderingProvider'];
  $main_refid  = 0 + $_POST['ReferringProvider'];
   $main_supid = 0 + $_POST['SupervisingProvider'];
  $main_pcp  = 0 + $_POST['PrimaryCareProvider'];
  $main_serPro  = 0 + $_POST['ServicingProvider'];
$main_icd_code_type = $_POST['icd_code_type'];


  
  if ($main_supid == $main_provid) $main_supid = 0;
   $Total_price=1;
   foreach($_POST['bill'] as $value)
		{
		if($value['price']=='0')
			$Total_price = 	0;
			if($_POST['bill'][11]['price']==0)
			$Total_price = 	1;
		}
		
   /*for($i=0;$i<=count($_POST['bill']); $i++)
		{		
			if($_POST['bill'][$i]['price']=='0')
			$Total_price = 	0;
			if($_POST['bill'][11]['price']==0)
			$Total_price = 	1;
		}	*/
	if($Total_price==0)
	{
	print '<script type="text/javascript">';
print 'alert("This encounter is not accepted as Amount is $0.")';
print '</script>'; 
echo "<script>top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/new.php?enc=1&autoloaded=1&calenc=')</script>";
	}
	else {
 
    $chk_encounter = sqlQuery("Select count(*) as count,encounter from form_encounter where date = '$date' and pid = '$pid' and provider_id = '$prov_id'");
	$count = $chk_encounter['count'];
	$enc_id = $chk_encounter['encounter'];
	
	if($count>0)
	{
		$Get_code = sqlStatement("Select code from billing where encounter='$enc_id'");	
		
			

	    while($Arr_code = sqlFetchArray($Get_code)){
		$code_arr[] = $Arr_code['code'];
		}		
		$flag=0;
		$Total_price =0;		
		
		
		foreach($_POST['bill'] as $value)
		{
			if($value['code'])
			{
				if(array_search($value['code'],$code_arr))
				$flag=1;
			}
			$Total_price = 	$Total_price + $value['price'];
			//Updated by Gangeya for BUG ID : 9555
			if($_POST['bill'][11]['price']==0)
			$Total_price = 	1;
		}
		
		/*for($i=0;$i<=count($_POST['bill']); $i++)
		{	
		
				
			if (($_POST['bill'][$i]['code']!='')){
				if (array_search($_POST['bill'][$i]['code'], $code_arr))
					$flag=1;	
				}	
			
			$Total_price = 	$Total_price + $_POST['bill'][$i]['price'];
			//Updated by Gangeya for BUG ID : 9555
			if($_POST['bill'][11]['price']==0)
			$Total_price = 	1;
		}*/		
	}
	
	if($Total_price==0)
	{
	print '<script type="text/javascript">';
	print 'alert("This encounter is not accepted as Amount is $0.")';
	print '</script>'; 
	echo "<script>top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/new.php?enc=1&autoloaded=1&calenc=')</script>";
	}
	if($flag==1)

	{
	print '<script type="text/javascript">';
print 'alert("The same encounter already created for this patient.")';
print '</script>'; 
echo "<script>top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/new.php?enc=1&autoloaded=1&calenc=')</script>";
	}
	else {
	if($mode == 'new') {
	
	 // code added for getting last inserted id of encounter by pawan 25-01-2017
	$lastid = sqlInsert("INSERT INTO form_encounter SET " .
      "date = '$date', " .
      "onset_date = '$onset_date', " .
      "reason = CONCAT('[',DATE_FORMAT(NOW(),'%m/%d/%Y %h:%m:%s'),']:[','$uname',']:','$reason'), " .
      "facility = '$facility', " .
      "pc_catid = '$pc_catid', " .
      "facility_id = '$facility_id', " .
      "billing_facility = '$billing_facility', " .
      "sensitivity = '$sensitivity', " .
      "referral_source = '$referral_source', " .
      "pid = '$pid', " .
      "encounter = '$encounter', " .
      "provider_id = '$provider_id',".
      "servicing_provider_id = '$ServicingProvider',".
  	  "supervisor_id = '$main_supid',".
	  "batch_id = '$batch_id',".
      "pos_id = '$pos',".
	  "claim_status_id = '$claim_status_id', ".
	  "device_type = '$device_type', ".
	  "created_date = '$todays_date', " .
	  "created_by = '$userID', " .	  
	  "modified_date = '$todays_date', " .
	  "modified_by = ''");	
	  
	  // code added for getting last inserted id of encounter by pawan 25-01-2017
	 /* $result = mysqli_query("SELECT LAST_INSERT_ID() "); 
	  $row =  mysqli_fetch_row($result);
	  $lastid = $row[0];*/
	  
	  
	  //$lastid = mysql_insert_id($lastRes);
	  
	
	/* $lastid=sqlInsert("INSERT INTO form_encounter SET " .
      "date = '$date', " .
      "onset_date = '$onset_date', " .
      "reason = CONCAT('[',DATE_FORMAT(NOW(),'%m/%d/%Y %h:%m:%s'),']:[','$uname',']:','$reason'), " .
      "facility = '$facility', " .
      "pc_catid = '$pc_catid', " .
      "facility_id = '$facility_id', " .
      "billing_facility = '$billing_facility', " .
      "sensitivity = '$sensitivity', " .
      "referral_source = '$referral_source', " .
      "pid = '$pid', " .
      "encounter = '$encounter', " .
      "provider_id = '$provider_id',".
  	  "supervisor_id = '$main_supid',".
	  "batch_id = '$batch_id',".
      "pos_id = '$pos',".
	  "claim_status_id = '$claim_status_id', ".
	  "device_type = '$device_type', ".
	  "created_date = '$todays_date', " .
	  "created_by = '$userID', " .	  
	  "modified_date = '$todays_date', " .
	  "modified_by = '' ");*/
	  
	
 addForm($encounter, "New Patient Encounter",
   $lastid, // code added for BUG 10577
    "newpatient", $pid, $userauthorized, $date);
	
	 $EncounterID= sqlQuery("select max(encounter) as ID from form_encounter" );
	 	 
	
	$modifier_user=$_SESSION{"authUser"};
	$encID = $EncounterID['ID']; 
$sql="INSERT INTO `encounter_status`(`Encounter`,`Status`,`Status_Date`,`modifier`)VALUES(
'$encID','$enc_status',NOW(),'$modifier_user')";
sqlInsert( $sql ); 


 $qry = sqlInsert("INSERT INTO form_encounter_draft SET " .
      "date = '$date', " .
      "onset_date = '$onset_date', " .
	  "device_type = '$device_type', " .
      "created_date = '$todays_date', " .
      "reason = CONCAT('[',DATE_FORMAT(NOW(),'%m/%d/%Y %h:%m:%s'),']:[','$uname',']:','$reason'), " .
      "facility = '$facility', " .
      "pc_catid = '$pc_catid', " .
      "facility_id = '$facility_id', " .
      "billing_facility = '$billing_facility', " .
      "sensitivity = '$sensitivity', " .
      "referral_source = '$referral_source', " .
      "pid = '$pid', " .
      "encounter = '$encounter', " .
      "provider_id = '$main_provid',".
      "servicing_provider_id = '$ServicingProvider',".
	  "supervisor_id = '$main_supid',".
	  "referrer_id = '$main_refid',".
	  "pcp_id = '$main_pcp',".
      "pos_id = '$pos',".
	  "draft = '0',".
	  "review = '0',".
	  "clarification = '0',".
	  "batch_id = '$batch_id',".
	  "final_status='save',".
	  "claim_status_id = '$claim_status_id' "); // code added for BUG 10521;	
	  
	  // code added for patient statement batch printing
	   
		
		// query for primary payer statement limit
		$inspid =$pid;
		$insu_query = "SELECT provider,ic.name,ic.statement_limit FROM insurance_data as isd
						INNER join insurance_companies as ic 
						ON isd.provider=ic.id
						where  isd.pid=$inspid and isd.type='primary' order by isd.date desc LIMIT 1";
		$insu_res = sqlStatement($insu_query);
		$insu_row = sqlFetchArray($insu_res);
		$payer_limit = $insu_row['statement_limit'];
		
		// query for getting patient last statement date and statement count
		$pat_query = "SELECT last_stmt_date,statement_count from patient_data where pid=$pid ";
		$pat_res = sqlStatement($pat_query);
		$pat_row = sqlFetchArray($pat_res);
		$last_statement_date = $pat_row['last_stmt_date'];
		$pat_statement_count = $pat_row['statement_count'];
		
		/* Resetting patient statement count to 0 
	  	if encounter created date is greater than last statement date and patient statement count is greater or equal to payer statement limit. */ 
		
		if(  (strtotime($last_statement_date) < strtotime($todays_date)) &&  ($pat_statement_count >= $payer_limit) ){
		
			 sqlStatement("UPDATE patient_data SET statement_count=0 WHERE pid = '$pid'");
		
		
		}
		
		
		
		
}
else if($mode == 'draft' || $mode == 'review' || $mode == 'clarification'){
	


$draft=0;$review=0;$clarification=0;

if($mode == 'draft')
$draft = 1;
if($mode == 'review')
$review = 1;
if($mode == 'clarification')
$clarification = 1;



if($_POST['draft_id']==''){
	$chk_encounter = sqlQuery("Select count(*) as count,id from form_encounter_draft where date = '$date' and pid = '$pid' and provider_id = '$prov_id' ");
	$count_draft = $chk_encounter['count'];
	$fe_draft_id = $chk_encounter['id'];
	
	
	if($count_draft>0)
	{	
		$Get_code = sqlQuery("Select code from billing_draft where draft_id='$fe_draft_id' and activity=1");		
	    while($Arr_code_draft = sqlFetchArray($Get_code)){
		$code_arr_draft[] = $Arr_code_draft['code'];
		}		
		$flag_draft=0;
		$Total_price =0;	
		foreach($_POST['bill'] as $value)
		{			
			//array_search($value['code'], $code_arr_draft);
			if (array_search($value['code'], $code_arr_draft)) 
			$flag_draft=1;	
			$Total_price = 	$Total_price + $value['price']	;			
		}	
		/*for($i=0;$i<=count($_POST['bill']); $i++)
		{		
		    array_search($_POST['bill'][$i]['code'], $code_arr_draft);
			if (array_search($_POST['bill'][$i]['code'], $code_arr_draft)) 
			$flag_draft=1;	
			$Total_price = 	$Total_price + $_POST['bill'][$i]['price']	;
		}	*/
		
	} 
	

	if($flag_draft==1)
	{
	print '<script type="text/javascript">';
print 'alert("The same encounter already drafted for this patient.")';
print '</script>'; 
echo "<script>top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/new.php?enc=1&autoloaded=1&calenc=')</script>";
	}
	else {
	
		
    $draft_id = sqlInsert("INSERT INTO form_encounter_draft SET " .
      "date = '$date', " .
      "onset_date = '$onset_date', " .
	   "device_type = '$device_type', " .
      "created_date = '$todays_date', " .
	"reason = CONCAT('[',DATE_FORMAT(NOW(),'%m/%d/%Y %h:%m:%s'),']:[','$uname',']:','$reason'), " .
      "facility = '$facility', " .
      "pc_catid = '$pc_catid', " .
      "facility_id = '$facility_id', " .
      "billing_facility = '$billing_facility', " .
      "sensitivity = '$sensitivity', " .
      "referral_source = '$referral_source', " .
      "pid = '$pid', " .
      "encounter = '1', " .
      "provider_id = '$main_provid',".
      "servicing_provider_id = '$ServicingProvider',".
	  "supervisor_id = '$main_supid',".
	  "referrer_id = '$main_refid',".
	  "pcp_id = '$main_pcp',".
      "pos_id = '$pos',".
	  "draft = '$draft',".
	  "review = '$review',".
	  "clarification = '$clarification',".
	  "batch_id = '$batch_id',".
	  "final_status='$mode',".
	  "claim_status_id = '$claim_status_id' ");
	  
	 // $result = mysql_query("SELECT LAST_INSERT_ID() "); 
	 // $row =  mysql_fetch_row($result);
	 // $draft_id = $row[0];
	  }
	}
}

//	
	////////////////////////////////////////////////////
	
  
  $default_warehouse = $_POST['default_warehouse'];
  
  //Added By Gangeya : BUG ID 8366
  $countFlag = 1;
  
  $bill = $_POST['bill'];
  $iter=array();
  
 $copay_update = FALSE;
  $update_session_id = '';  
$ct0 = '';//takes the code type of the first fee type code type entry from the fee sheet, against which the copay is posted
  $cod0 = '';//takes the code of the first fee type code type entry from the fee sheet, against which the copay is posted
  $mod0 = '';//takes the modifier of the first fee type code type entry from the fee sheet, against which the copay is posted

  $billed=0;
  foreach ($bill as $key => $iter ) {
    //$iter = $bill["$lino"];
    $code_type = $iter['code_type'];
    $code      = $iter['code'];
    $del       = $iter['del'];
	$billingid  = $iter['id'];
// code added for cpt eaa amount
	$cpt_eaa =  $iter['cpt_eaa'];
	if(empty($cpt_eaa)){
		$cpt_eaa= '0.00';
	}
  
  

 if ($iter['id'] && !$iter['code'] && ($mode == 'draft' || $mode == 'review' || $mode == 'clarification')) {		
        deleteBillingDraft($iter['id']);			
      }

	  
	  if($iter['id'] )
	  {
	  	$billing_code_type= sqlQuery("select code_type from billing_draft where id=$billingid" );

		 if($billing_code_type['code_type'] != $code_type)
		 {
		 deleteBillingDraft($iter['id']);
		 $iter['id'] = NULL;
		 }
 
	  }
	
	
	
	
	 
    // Get some information about this service code.
    $codesrow = sqlQuery("SELECT id,code,code_text FROM codes WHERE " .
      "code_type = '" . $code_types[$code_type]['id'] .
      "' AND code = '$code' LIMIT 1");

    // Skip disabled (billed) line items.
    if ($iter['billed']) continue;

    $id        = $iter['id'];	
    $modifier1  = trim($iter['mod1']);
	$modifier2  = trim($iter['mod2']);
	$modifier3  = trim($iter['mod3']);
	$modifier4  = trim($iter['mod4']);
	$modifier='';
	
	if($modifier1)
	$modifier .= $modifier1;
	if($modifier2)
	$modifier .= ":".$modifier2;
	if($modifier3)
	$modifier .= ":".$modifier3;
	if($modifier4)
	$modifier .= ":".$modifier4;
	
	//$modifier=$modifier1.":".$modifier2.":".$modifier3.":".$modifier4;
	
	$modifier = str_replace("::",":",$modifier);
	 if($modifier[0] == ":")
	 $modifier = substr($modifier, 1);
	 if(substr($modifier, -1, 1)==":")
	 $modifier = substr($modifier, 0, -1);
	 
	 if( !($cod0) ){
     	 //mod0 is added by sangram for bug 9327
	 $mod0 = '';
    if($bill['105']['mod1'])
	$mod0 .= $bill['105']['mod1'];
	if($bill['105']['mod2'])
	$mod0 .= ":".$bill['105']['mod2'];
	if($bill['105']['mod3'])
	$mod0 .= ":".$bill['105']['mod3'];
	if($bill['105']['mod4'])
	$mod0 .= ":".$bill['105']['mod4'];
	
$mod0 = str_replace("::",":",$mod0);
	 if($mod0[0] == ":")
	 $mod0 = substr($mod0, 1);
	 if(substr($mod0, -1, 1)==":")
	 $mod0 = substr($mod0, 0, -1);
	  
	  
	  $cod0 = $bill['105']['code']; 
	  
	 
    }

	
    $units     = max(1, intval(trim($iter['units'])));
    $fee       = sprintf('%01.2f',(0 + trim($iter['price'])));
	
	
    if ($code_type == 'COPAY' && $mode == 'new') {
      if($id == '' && $fee>0){
        //adding new copay from fee sheet into  ar_activity table
        if($fee < 0){
          $fee = $fee * -1;
        }
		$memo = "Copay : $".$fee;		
		/*if($form_method != 'check_payment'){
       $session_id = idSqlStatement("INSERT INTO ar_session(payer_id,user_id,pay_total,payment_type,description,".
          "patient_id,payment_method,reference,adjustment_code,post_to_date,check_date,deposit_date) VALUES('0',?,?,'patient','COPAY',?,?,?,'patient_payment',now(),now(),now())",
          array($_SESSION['authId'],$fee,$pid,$form_method,$form_source));
		  $billed=1;
		  }	*/	
		   $res_session  = sqlQuery("SELECT * from ar_session where patient_id=? and payment_method=? and reference=? ",array($pid,$form_method,$form_source));	
		 	$billingid = $res_session['session_id'];
		// code change by Mahesh Kunta. added modified time for insert record.   
        SqlStatement("INSERT INTO ar_activity (pid,encounter,code,modifier,payer_type,post_time,post_user,session_id,".
          "pay_amount,account_code,memo,modified_time,created_time) VALUES (?,?,?,?,0,now(),?,?,?,'PCP',?,now(),now())",
          array($pid,$encounter,$cod0,$mod0,$_SESSION['authId'],$session_id,$fee,$memo));		
		  // code change end
		   $billed=1;		  
      }else{
        //editing copay saved to ar_session and ar_activity
        if($fee < 0){
          $fee = $fee * -1;
        }
        $session_id = $id;
        $res_amount = sqlQuery("SELECT pay_amount FROM ar_activity WHERE pid=? AND encounter=? AND session_id=?",
          array($pid,$encounter,$session_id));
        if($fee != $res_amount['pay_amount']){	
		 
         /* sqlStatement("UPDATE ar_session SET user_id=?,pay_total=?,payment_method=?,reference=?,modified_time=now(),post_to_date=now(),check_date=now(),deposit_date=now() WHERE session_id=?",
            array($_SESSION['authId'],$fee,$form_method,$form_source,$session_id));*/
          sqlStatement("UPDATE ar_activity SET code=?, modifier=?, post_user=?, post_time=now(),".
            "pay_amount=?, modified_time=now(), memo=? WHERE pid=? AND encounter=? AND account_code='PCP' AND session_id=?",
            array($cod0,$mod0,$_SESSION['authId'],$fee,$memo,$pid,$encounter,$session_id));
        }
      }
      if(!$cod0){
        $copay_update = TRUE;
        $update_session_id = $session_id;
      }
      continue;
    }	
	if ($code_type == 'COPAY' && $mode != 'new') {	
	$code = $fee;
	$ndc_info=$form_source;	
	if($code && $ndc_info)
	addBillingDraft(1, $code_type, strtoupper($code), $code_text, $pid, $auth,
        $main_provid, strtoupper($modifier), $units, $fee, $ndc_info, strtoupper($justify),0,$from_dos,$to_dos,$tos,$draft_id);
			
	}
	
	$justify='';
    $justify   = trim($iter['justify']);	
	if($justify){
	$splitter=':';
    $returned = ''; 
	$justify_new = array();
	
    for($i=0; $i<strlen($justify); $i++) {
         $returned .= $justify{$i};
         if($i != (strlen($justify) - 1))
             $returned .= $splitter;
    } 
	$pointer = explode(':',$returned);
	$pointer = array_unique($pointer);
	
	 for($i=0;$i<count($pointer);$i++)
	{
		$aa = $pointer[$i];
		$justify_new[$i] =$_POST['bill'][$aa]['code'];
	}
	$justify = implode(':',$justify_new); 
	}
	
	
 
    // $auth      = $iter['auth'] ? "1" : "0";
    $auth      = "1";
    //$provid    = 0 + $iter['provid'];	
 	$provid    = $main_provid;
 
    $ndc_info = '';
    if ($iter['ndcnum']) {
    $ndc_info = 'N4' . trim($iter['ndcnum']) . '   ' . $iter['ndcuom'] .
      trim($iter['ndcqty']);  	  
    }
	
	if($iter['from_dos']=='')
	$from_dos = $date;
	else
	$from_dos             = date('Y-m-d',strtotime($iter['from_dos']));
	
	if($iter['to_dos']=='')
	$to_dos = $date;
	else
	$to_dos             = date('Y-m-d',strtotime($iter['to_dos']));	
	
	$tos = $iter['tos'];	
	
	$enc_num = sqlQuery("select encounter from billing where id='$id'");			
    // If the item is already in the database...
	 if($mode == 'new'){
  
	if($drafted) {
	sqlQuery("UPDATE form_encounter_draft set draft=0,review=0,clarification=0,final_status='save' where pid=$pid and id = '$draft_id'");
	sqlQuery("UPDATE billing_draft set encounter=$encounter where draft_id=$draft_id");
	}
      $code_text = addslashes($codesrow['code_text']);	    
	  //if($date=='')
		$billing_date=date('Y-m-d H:i:s');
    
		if($code_type=='ICD9'  || $code_type=='ICD10') 
		addBilling($encounter, $code_type, strtoupper($code), $code_text, $pid, $auth, $main_provid, strtoupper($modifier), $units, $fee, $ndc_info, strtoupper($justify),$billed,'0000-00-00 00:00:00','0000-00-00 00:00:00','',1,$billing_date,$cpt_eaa);  
		else
		 addBilling($encounter, $code_type, strtoupper($code), $code_text, $pid, $auth,
        $main_provid, strtoupper($modifier), $units, $fee, $ndc_info, strtoupper($justify),$billed,$from_dos,$to_dos,$tos,1,$billing_date,$cpt_eaa);
		
   }
   //}
   
   else if($mode == 'draft' || $mode == 'review' || $mode == 'clarification' ) {
 
   if ($id) {
   
         
   
   //Added By Gangeya : BUG ID 8366
   if($countFlag == 1)
   {
   		$reasonFull = ' [' . date("m/d/Y h:m:s", time()) .']:[' . $uname . ']:' . $reason;
   }
   else
   {
   		$reasonFull = ' ';
   }
   
        // authorizeBilling($id, $auth);
		$draft_id = $_POST['draft_id'];	
		$datepart = acl_check('encounters', 'date_a') ? "date = '$date', " : "";		
		sqlQuery("UPDATE form_encounter_draft SET " .
    $datepart .
    "onset_date = '$onset_date', " .
    "reason = CONCAT(reason,'$reasonFull'), " .
    "facility = '$facility', " .
    "pc_catid = '$pc_catid', " .
    "facility_id = '$facility_id', " .
    "billing_facility = '$billing_facility', " .
    "sensitivity = '$sensitivity', " .
    "referral_source = '$referral_source', " .
	"batch_id = '$batch_id',".
	"provider_id = '$main_provid',".
	"supervisor_id = '$main_supid',".
	"servicing_provider_id = '$main_serPro',".
	"referrer_id = '$main_refid',".
	  "pcp_id = '$main_pcp',".
	"billing_note = '$billing_note',".
	"draft = '$draft',".
	"review = '$review',".
	"clarification = '$clarification',".
	"final_status = '$mode',".
	"pos_id = '$pos', " .
	"claim_status_id = '$claim_status_id' ".
    "WHERE id = '$draft_id'");
		  
        sqlQuery("UPDATE billing_draft SET code = '$code', " .
          "units = '$units', fee = '$fee', modifier = '$modifier', " .
          "authorized = $auth, provider_id = '$main_provid', " .
          "justify = '$justify',from_dos='$from_dos',to_dos='$to_dos',tos='$tos', ".
		  "draft_id='$draft_id' WHERE " .
          "id = '$id' AND billed = 0 AND activity = 1");
		  
		  $countFlag = $countFlag+1;
      
    }
    // Otherwise it's a new item...
    else if (! $del) { 
      $code_text = addslashes($codesrow['code_text']);	        
		if($code_type=='ICD9'  || $code_type=='ICD10') 
		addBillingDraft(1, $code_type, strtoupper($code), $code_text, $pid, $auth, $main_provid, strtoupper($modifier), $units, $fee, $ndc_info, strtoupper($justify),0,'0000-00-00 00:00:00','0000-00-00 00:00:00','',$draft_id);  
		else if($code_type=='CPT4')
		 addBillingDraft(1, $code_type, strtoupper($code), $code_text, $pid, $auth,
        $main_provid, strtoupper($modifier), $units, $fee, $ndc_info, strtoupper($justify),0,$from_dos,$to_dos,$tos,$draft_id);
		
   }
   } 
 // }
  } // end for

  if($mode == 'draft' || $mode == 'review' || $mode == 'clarification' ) {  /*
  $sql = sqlStatement("select code from billing where encounter='$enc_num[encounter]' and code_type!='COPAY'  ");
   while ($rows = sqlFetchArray($sql)) {
   $code1= $rows['code'];
   $flag=0;
	   for($cnt=1;$cnt<=10;$cnt++)
	   {
	   	  if($bill[$cnt]['code']==$code1)
		  $flag=1;	
	   }
	   if($flag==0)
	   sqlQuery("UPDATE billing SET activity=0 where encounter='$enc_num[encounter]' and code='$code1' ");
   }
  */}
  if($mode == 'new'){
  
   //if modifier is not inserted during loop update the record using the first
  //non-empty modifier and code
  // code changes by Mahesh, added modified time.
  if($copay_update == TRUE && $update_session_id != '' && $mod0 != ''){
    sqlStatement("UPDATE ar_activity SET code=?, modified_time=now(),modifier=?".
      " WHERE pid=? AND encounter=? AND account_code='PCP' AND session_id=?",
      array($cod0,$mod0,$pid,$encounter,$update_session_id));
  }
  // Code changes end. 
  
  // Doing similarly to the above but for products.
  $prod = $_POST['prod'];
  for ($lino = 1; $prod["$lino"]['drug_id']; ++$lino) {
    $iter = $prod["$lino"];

    if (!empty($iter['billed'])) continue;

    $drug_id   = $iter['drug_id'];
    $sale_id   = $iter['sale_id']; // present only if already saved
    $units     = max(1, intval(trim($iter['units'])));
    $fee       = sprintf('%01.2f',(0 + trim($iter['price'])));
    $del       = $iter['del'];

    // If the item is already in the database...
    if ($sale_id) {
      if ($del) {
        // Zero out this sale and reverse its inventory update.  We bring in
        // drug_sales twice so that the original quantity can be referenced
        // unambiguously.
        sqlStatement("UPDATE drug_sales AS dsr, drug_sales AS ds, " .
          "drug_inventory AS di " .
          "SET di.on_hand = di.on_hand + dsr.quantity, " .
          "ds.quantity = 0, ds.fee = 0 WHERE " .
          "dsr.sale_id = '$sale_id' AND ds.sale_id = dsr.sale_id AND " .
          "di.inventory_id = ds.inventory_id");
        // And delete the sale for good measure.
        sqlStatement("DELETE FROM drug_sales WHERE sale_id = '$sale_id'");
      }
      else {
        // Modify the sale and adjust inventory accordingly.
        $query = "UPDATE drug_sales AS dsr, drug_sales AS ds, " .
          "drug_inventory AS di " .
          "SET di.on_hand = di.on_hand + dsr.quantity - $units, " .
          "ds.quantity = '$units', ds.fee = '$fee', " .
          "ds.sale_date = '$visit_date' WHERE " .
          "dsr.sale_id = '$sale_id' AND ds.sale_id = dsr.sale_id AND " .
          "di.inventory_id = ds.inventory_id";
        sqlStatement($query);
      }
    }

    // Otherwise it's a new item...
    else if (! $del) {
      $sale_id = sellDrug($drug_id, $units, $fee, $pid, $encounter, 0,
        $visit_date, '', $default_warehouse);
      if (!$sale_id) die("Insufficient inventory for product ID \"$drug_id\".");
    }
  } // end for 

  // Set the main/default service provider in the new-encounter form.
  /*******************************************************************
  sqlStatement("UPDATE forms, users SET forms.user = users.username WHERE " .
    "forms.pid = '$pid' AND forms.encounter = '$encounter' AND " .
    "forms.formdir = 'newpatient' AND users.id = '$provid'");
  *******************************************************************/
   sqlStatement("UPDATE form_encounter SET provider_id = '$main_provid', " .
    "supervisor_id = '$main_supid', referrer_id = '$main_refid', ".
	  "pcp_id = '$main_pcp', servicing_provider_id = '$main_serPro', modified_date = '$todays_date', modified_by = '$userID' WHERE " .
    "pid = '$pid' AND encounter = '$encounter'");

  // More IPPF stuff.
  if (!empty($_POST['contrastart'])) {
    $contrastart = $_POST['contrastart'];
    sqlStatement("UPDATE patient_data SET contrastart = '" .
      $contrastart . "' WHERE pid = '$pid'");
  }
 

	////////////////////////////////////////////////////
	
 
	print '<script type="text/javascript">';
print 'alert("The Encounter saves Successfully \n Claim No. is '. $EncounterID['ID'].'")';
print '</script>'; 
}
	else if($mode == 'draft') {
	print '<script type="text/javascript" language="javascript">';
	print 'alert("The Encounter is in Draft mode for more verification.");';
	print '</script><script type="text/javascript" language="javascript">';	
	echo "top.window.parent.left_nav.loadFrame2('nen1','RBot','forms/newpatient/new.php?autoloaded=1&calenc=')"; 
	print '</script>';
	}
	else if($mode == 'review'){
	print '<script type="text/javascript">';
	print 'alert("The Encounter is kept in Review mode.")';
	print '</script><script type="text/javascript" language="javascript">';	
	echo "top.window.parent.left_nav.loadFrame2('nen1','RBot','forms/newpatient/new.php?autoloaded=1&calenc=')"; 
	print '</script>';
	}
	else if($mode == 'clarification'){
	print '<script type="text/javascript">';
	print 'alert("The Encounter is kept in Clarification mode.")';
	print '</script><script type="text/javascript" language="javascript">';	
	echo "top.window.parent.left_nav.loadFrame2('nen1','RBot','forms/newpatient/new.php?autoloaded=1&calenc=')"; 
	print '</script>';
	}
   
}
}
}
else if ($mode == 'update')
{


 
$id = $_POST["id"];
 $prov_id = 0 + $_POST['RenderingProvider'];
 
$Total_price=1;
foreach($_POST['bill'] as $value)
		{
			if($value['price']=='0')
			$Total_price = 	0;
			if($_POST['bill'][11]['price']==0)
			$Total_price = 	1;
		}
   /*for($i=0;$i<=count($_POST['bill']); $i++)
		{		
			if($_POST['bill'][$i]['price']=='0')
			$Total_price = 	0;
			if($_POST['bill'][11]['price']==0)
			$Total_price = 	1;
			
		}	*/
	if($Total_price==0)
	{
	print '<script type="text/javascript">';
print 'alert("This encounter is not accepted as Amount is $0.")';
print '</script>'; 

$path = $rootdir."/patient_file/encounter/view_form.php?formname=newpatient&id=".$id;  
echo("<script>location.href = '$path'</script>");
	}
	else {
 $count='';
    $chk_encounter = sqlQuery("Select count(*) as count,encounter from form_encounter where date = '$date' and pid = '$pid' and provider_id = '$prov_id' and id != '$id' ");
	$count = $chk_encounter['count'];
	$enc_id = $chk_encounter['encounter'];
			
	if($count>0)
	{
		$Get_code = sqlQuery("Select code from billing where encounter='$enc_id' and code_type!='COPAY' ");		
	    while($Arr_code = sqlFetchArray($Get_code)){
		$code_arr[] = $Arr_code['code'];
		}		
		$flag=0;
		$Total_price =0;
		foreach($_POST['bill'] as $value)
		{
			if (array_search($value['code'], $code_arr)) 
			$flag=1;	
			$Total_price = 	$Total_price + $value['price']	;		
				
			//Updated by Gangeya for BUG ID : 9555
			if($_POST['bill'][11]['price']==0)
			$Total_price = 	1;
		}
		
		/*for($i=0;$i<=count($_POST['bill']); $i++)
		{			
			if (array_search($_POST['bill'][$i]['code'], $code_arr)) 
			$flag=1;	
			$Total_price = 	$Total_price + $_POST['bill'][$i]['price']	;		
				
			//Updated by Gangeya for BUG ID : 9555
			if($_POST['bill'][11]['price']==0)
			$Total_price = 	1;
		}	*/	
	} 
	
		
	if($Total_price==0)
	{
	print '<script type="text/javascript">';
	print 'alert("This encounter is not accepted as Amount is $0.")';
	print '</script>'; 
	print '</script>'; 
$path = $rootdir."/patient_file/encounter/view_form.php?formname=newpatient&id=".$id;  
echo("<script>location.href = '$path'</script>");
	}
	if($flag==1)
	{
	print '<script type="text/javascript">';
print 'alert("The same encounter already created for this patient.")';
print '</script>'; 
print '</script>'; 
$path = $rootdir."/patient_file/encounter/view_form.php?formname=newpatient&id=".$id;  
echo("<script>location.href = '$path'</script>");
	}
  
  $result = sqlQuery("SELECT reason,encounter, sensitivity FROM form_encounter WHERE id = '$id'");
  if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
   die("You are not authorized to see this encounter.");
  }
  $encounter = $result['encounter'];
  // See view.php to allow or disallow updates of the encounter date.
  $datepart = acl_check('encounters', 'date_a') ? "date = '$date', " : "";
  
	// code added for reason issue and creates userstamp
  $reason2 =  "[".date("m/d/Y h:m:s")."]: [".$uname."]: ".$reason;
  
  if(empty($result['reason'])){
	  
	  sqlStatement("UPDATE form_encounter SET " .
    $datepart .
    "onset_date = '$onset_date', " .
    "reason = '$reason2', " .
    "facility = '$facility', " .
    "pc_catid = '$pc_catid', " .
    "facility_id = '$facility_id', " .
    "billing_facility = '$billing_facility', " .
    "sensitivity = '$sensitivity', " .
    "referral_source = '$referral_source', " .
	"batch_id = '$batch_id',".
	"pos_id = '$pos', " .
	"claim_status_id = '$claim_status_id',  ".
	"modified_date = '$todays_date', " .
	"modified_by = '$userID' ".	  
    "WHERE id = '$id'");
  }
  else{
	  
	  sqlStatement("UPDATE form_encounter SET " .
    $datepart .
    "onset_date = '$onset_date', " .
    "reason = CONCAT(reason,' ','$reason2'), " .
    "facility = '$facility', " .
    "pc_catid = '$pc_catid', " .
    "facility_id = '$facility_id', " .
    "billing_facility = '$billing_facility', " .
    "sensitivity = '$sensitivity', " .
    "referral_source = '$referral_source', " .
	"batch_id = '$batch_id',".
	"pos_id = '$pos', " .
	"claim_status_id = '$claim_status_id',  ".
	"modified_date = '$todays_date', " .
	"modified_by = '$userID' ".	  
    "WHERE id = '$id'");
  }
  

  
	
	
	$EncounterID= sqlQuery("select max(encounter) as ID from form_encounter" );
	/*$modifier_user=$_SESSION{"authUser"};
	$encID = $EncounterID['ID']; 
$sql="INSERT INTO `encounter_status`(`Encounter`,`Status`,`Status_Date`,`modifier`)VALUES(
'$encID','$enc_status',NOW(),'$modifier_user')";
sqlInsert( $sql );*/

	////////////////////////////////////////////////////
	
	$modifier_user=$_SESSION{"authUser"};
	$encID = $EncounterID['ID']; 
$sql="INSERT INTO `encounter_status`(`Encounter`,`Status`,`Status_Date`,`modifier`)VALUES(
'$encID','$enc_status',NOW(),'$modifier_user')";
sqlInsert( $sql );
	
  $main_provid = 0 + $_POST['RenderingProvider'];
   $main_refid = 0 + $_POST['ReferringProvider'];
      $main_supid = 0 + $_POST['SupervisingProvider'];
  $main_pcp  = 0 + $_POST['PrimaryCareProvider'];
  $main_serPro  = 0 + $_POST['ServicingProvider'];
  $main_icd_code_type = $_POST['icd_code_type'];
  if ($main_supid == $main_provid) $main_supid = 0;
  $default_warehouse = $_POST['default_warehouse'];

  $bill = $_POST['bill'];
//print_r($_POST['bill']); die;

  $copay_update = FALSE;
  $update_session_id = '';
  $ct0 = '';//takes the code type of the first fee type code type entry from the fee sheet, against which the copay is posted
  $cod0 = '';//takes the code of the first fee type code type entry from the fee sheet, against which the copay is posted
  $mod0 = '';//takes the modifier of the first fee type code type entry from the fee sheet, against which the copay is posted

 
  foreach ($bill as $key => $iter ) {
   // $iter = $bill["$lino"];
   
   
    $code_type = $iter['code_type'];
    $code      = $iter['code'];
    $del       = $iter['del'];
	$billingid  = $iter['id'];
	// code added for CPT EAA amount
	$cpt_eaa =  $iter['cpt_eaa'];
	if(empty($cpt_eaa)){
		$cpt_eaa= '0.00';
	}
  
 if ($iter['id'] && !$iter['code']) {		
        deleteBilling($iter['id']);			
      }
	  
	  if($iter['id'] )
	  {
	  	$billing_code_type= sqlQuery("select code_type from billing where id=$billingid" );

		 if($billing_code_type['code_type'] != $code_type)
		 {
		 deleteBilling($iter['id']);
		 $iter['id'] = NULL;
		 }
 
	  }
	  
/*	  if($main_icd_code_type==0 &&  $code_type=='ICD9')
	  {
 deleteBilling($iter['id']);
 	
	  }
  if($main_icd_code_type==9  &&  $code_type=='ICD10')
	  {
 deleteBilling($iter['id']);	
	  }*/
	

    // Get some information about this service code.
    $codesrow = sqlQuery("SELECT code_text FROM codes WHERE " .
      "code_type = '" . $code_types[$code_type]['id'] .
      "' AND code = '$code' LIMIT 1");

    // Skip disabled (billed) line items.
    if ($iter['billed']) continue;
	

    $id        = $iter['id'];
		
	$modifier1  = trim($iter['mod1']);
	$modifier2  = trim($iter['mod2']);
	$modifier3  = trim($iter['mod3']);
	$modifier4  = trim($iter['mod4']);
	$modifier='';
	
	if($modifier1)
	$modifier .= $modifier1;
	if($modifier2)
	$modifier .= ":".$modifier2;
	if($modifier3)
	$modifier .= ":".$modifier3;
	if($modifier4)
	$modifier .= ":".$modifier4;
	
	//$modifier=$modifier1.":".$modifier2.":".$modifier3.":".$modifier4;
	
		
	$modifier = str_replace("::",":",$modifier);
	if($modifier[0] == ":")
	 $modifier = substr($modifier, 1);
	 if(substr($modifier, -1, 1)==":")
	 $modifier = substr($modifier, 0, -1);
	 
	 if( !($cod0) ){
	 
	 //mod0 is added by sangram for bug 9327
       $mod0 = '';
    if($bill['105']['mod1'])
	$mod0 .= $bill['105']['mod1'];
	if($bill['105']['mod2'])
	$mod0 .= ":".$bill['105']['mod2'];
	if($bill['105']['mod3'])
	$mod0 .= ":".$bill['105']['mod3'];
	if($bill['105']['mod4'])
	$mod0 .= ":".$bill['105']['mod4'];
	
$mod0 = str_replace("::",":",$mod0);
	 if($mod0[0] == ":")
	 $mod0 = substr($mod0, 1);
	 if(substr($mod0, -1, 1)==":")
	 $mod0 = substr($mod0, 0, -1);
	  
      $cod0 = $bill['105']['code'];     
    }
	
    $units     = max(1, intval(trim($iter['units'])));
    $fee       = sprintf('%01.2f',(0 + trim($iter['price'])));
	$billed=0;
	
	
    if ($code_type == 'COPAY') {
      
        //editing copay saved to ar_session and ar_activity
        if($fee < 0){
          $fee = $fee * -1;
        }    		
        $res_amount = sqlQuery("SELECT pay_amount FROM ar_activity WHERE pid=? AND encounter=? ",
          array($pid,$encounter));
		  $memo = "Copay : $".$fee;		
		  if($session_id)			  
		  $billingid = $session_id;
        if($fee != $res_amount['pay_amount'] && $res_amount['pay_amount']){			
         
          /*sqlStatement("UPDATE ar_activity SET code=?, modifier=?, post_user=?, post_time=now(),".
            "pay_amount=?, modified_time=now(),memo=?  WHERE pid=? AND encounter=? AND account_code='PCP' AND session_id=?",
          array($cod0,$mod0,$_SESSION['authId'],$fee,$memo,$pid,$encounter,$billingid));*/
		 /* if($form_method != 'check_payment'){
       $session_id = idSqlStatement("INSERT INTO ar_session(payer_id,user_id,pay_total,payment_type,description,".
          "patient_id,payment_method,reference,adjustment_code,post_to_date,check_date,deposit_date) VALUES('0',?,?,'patient','COPAY',?,?,?,'patient_payment',now(),now(),now())",
          array($_SESSION['authId'],$fee,$pid,$form_method,$form_source));
		  $billingid = $session_id;
		  }	*/
		 
		 /* $res_session  = sqlQuery("SELECT * from ar_session where patient_id=? and payment_method=? ",array($pid,$form_method));	
		 	$billingid = $res_session['session_id'];
		  
		  SqlStatement("INSERT INTO ar_activity (pid,encounter,code,modifier,payer_type,post_time,post_user,session_id,".
          "pay_amount,account_code,memo) VALUES (?,?,?,?,0,now(),?,?,?,'PCP',?)",
          array($pid,$encounter,$cod0,$mod0,$_SESSION['authId'],$billingid,$fee,$memo));*/
		//  SqlQuery("Update ar_activity set pay_amount='$fee', post_time=now() where pid='$pid' and encounter='$encounter' and account_code='PCP' and payer_type=0  ");
		   sqlStatement("UPDATE ar_activity SET code=?, modifier=?, post_user=?, post_time=now(),".
            "pay_amount=?, modified_time=now(), memo=? WHERE pid=? AND encounter=? AND account_code='PCP' AND session_id=?",
            array($cod0,$mod0,$_SESSION['authId'],$fee,$memo,$pid,$encounter,$session_id));
		  $billed=1;
        }
		else if(!$res_amount['pay_amount'] && $fee>0)
		{
			// code change by Mahesh Kunta. added modified date for creating record in ar actvity. 
			SqlStatement("INSERT INTO ar_activity (pid,encounter,code,modifier,payer_type,post_time,post_user,session_id,".
          "pay_amount,account_code,memo,modified_time,created_time) VALUES (?,?,?,?,0,now(),?,?,?,'PCP',?,now(),now())",
          array($pid,$encounter,$cod0,$mod0,$_SESSION['authId'],$billingid,$fee,$memo));
		  $billed=1;
			// Code changes end.
		}
   
      if(!$cod0){
        $copay_update = TRUE;
        $update_session_id = $billingid;
      }
      continue;
    }
	
    $justify='';	
	$justify   = trim($iter['justify']);	
	if($justify){	
	$splitter=':';
    $returned = ''; 
	$justify_new = array();	

    for($i=0; $i<strlen($justify); $i++) {
         $returned .= $justify{$i};
         if($i != (strlen($justify) - 1))
             $returned .= $splitter;
    } 
	$pointer = explode(':',$returned);
	$pointer = array_unique($pointer);
	
	 for($i=0;$i<count($pointer);$i++)
	{
		$aa = $pointer[$i];
		$justify_new[$i] =$_POST['bill'][$aa]['code'];
	}
	$justify = implode(':',$justify_new); 
	 }
    // $auth      = $iter['auth'] ? "1" : "0";
    $auth      = "1";
    //$provid    = 0 + $iter['provid'];

    $ndc_info = '';
    if ($iter['ndcnum']) {
    $ndc_info = 'N4' . trim($iter['ndcnum']) . '   ' . $iter['ndcuom'] .
      trim($iter['ndcqty']);
    }
	
	if($iter['from_dos']=='')
	$from_dos = $date;
	else
	$from_dos             = date('Y-m-d',strtotime($iter['from_dos']));
	
	if($iter['to_dos']=='')
	$to_dos = $date;
	else
	$to_dos = date('Y-m-d',strtotime($iter['to_dos']));	
	
	$tos = $iter['tos'];
	

	
	$enc_num = sqlQuery("select encounter from billing where id='$id'");
	
	// echo "<br>>>".$id.">>>>>".$code;
 // If the item is already in the database...
 

 
    if ($id && $code) {		
      if ($del) {
        deleteBilling($id);			
      }
      else {   
      //below added by sangram for bug 8919
	  
	      $row_to_update = sqlQuery("select code,encounter from billing where id='$id'");
	$code_to_update = $row_to_update['code'];
	$encounter_to_update = $row_to_update['encounter'];
	  
	      //below added by sangram for bug 8919
	  // code change by Mahesh. added modified date. 
	    sqlQuery("UPDATE ar_activity SET code = '$code' ,modified_time=now() " .
          "WHERE code ='$code_to_update' AND encounter ='$encounter_to_update'");
		   $billed =1;
	  // Code changes end.
	     sqlQuery("UPDATE billing SET code = '$code', " .
          "units = '$units', fee = '$fee', modifier = '$modifier', " .
          "authorized = $auth, provider_id = '$provid', " .
          "ndc_info = '$ndc_info', justify = '$justify', from_dos='$from_dos', to_dos='$to_dos', tos='$tos', ".
		  "modified_by = '$userID', modified_date = NOW(),eaa_amount='$cpt_eaa' WHERE " .
          "id = '$id' AND activity = 1");
		  
		
		  
      }
    }

    // Otherwise it's a new item...
   else if (! $del) { 
   
      $code_text = addslashes($codesrow['code_text']);	 
	  //echo "<br>$code_type ***********<br>";
	  if($code=='')
	  $activity=0;
	  else
	  $activity=1;	
	  
	  //if($date=='')
	$billing_date=date('Y-m-d H:i:s');
	  
		if($code_type=='ICD9' || $code_type=='ICD10') 
		addBilling($encounter, $code_type, strtoupper($code), $code_text, $pid, $auth, $main_provid, strtoupper($modifier), $units, $fee, $ndc_info, strtoupper($justify),$billed,'0000-00-00 00:00:00','0000-00-00 00:00:00','',$activity,$billing_date,$cpt_eaa);  
		else  if($code)
		 addBilling($encounter, $code_type, strtoupper($code), $code_text, $pid, $auth,
        $main_provid, strtoupper($modifier), $units, $fee, $ndc_info, strtoupper($justify),$billed,$from_dos,$to_dos,$tos,$activity,$billing_date,$cpt_eaa);
		else if($code=='' && ($units>0 || $fee>0) )
			deleteBilling($id);			
		
    } 
  } // end for 
  
    //if modifier is not inserted during loop update the record using the first
  //non-empty modifier and code
  if($copay_update == TRUE && $update_session_id != '' && $mod0 != ''){
	  // Code changes by Mahesh kunta. added modified date
    sqlStatement("UPDATE ar_activity SET code=?, modified_time=now(), modifier=?".
      " WHERE pid=? AND encounter=? AND account_code='PCP' AND session_id=?",
      array($cod0,$mod0,$pid,$encounter,$billingid));
	  // Code changes end. 
  }
  
  //die;
/*  $sql = sqlStatement("select code from billing where encounter='$enc_num[encounter]' and code_type!='COPAY' ");
   while ($rows = sqlFetchArray($sql)) {
   $code1= $rows['code'];
   $flag=0;
	   for($cnt=1;$cnt<=10;$cnt++)
	   {
	   	  if($bill[$cnt]['code']==$code1)
		  $flag=1;	
	   }
	   if($flag==0)
	   sqlQuery("UPDATE billing SET activity=0 where encounter='$enc_num[encounter]' and code='$code1' ");
   }*/
  

  // Doing similarly to the above but for products.
  $prod = $_POST['prod'];
  for ($lino = 1; $prod["$lino"]['drug_id']; ++$lino) {
    $iter = $prod["$lino"];

    if (!empty($iter['billed'])) continue;

    $drug_id   = $iter['drug_id'];
    $sale_id   = $iter['sale_id']; // present only if already saved
    $units     = max(1, intval(trim($iter['units'])));
    $fee       = sprintf('%01.2f',(0 + trim($iter['price'])));
    $del       = $iter['del'];

    // If the item is already in the database...
    if ($sale_id) {
      if ($del) {
        // Zero out this sale and reverse its inventory update.  We bring in
        // drug_sales twice so that the original quantity can be referenced
        // unambiguously.
        sqlStatement("UPDATE drug_sales AS dsr, drug_sales AS ds, " .
          "drug_inventory AS di " .
          "SET di.on_hand = di.on_hand + dsr.quantity, " .
          "ds.quantity = 0, ds.fee = 0 WHERE " .
          "dsr.sale_id = '$sale_id' AND ds.sale_id = dsr.sale_id AND " .
          "di.inventory_id = ds.inventory_id");
        // And delete the sale for good measure.
        sqlStatement("DELETE FROM drug_sales WHERE sale_id = '$sale_id'");
      }
      else {
        // Modify the sale and adjust inventory accordingly.
        $query = "UPDATE drug_sales AS dsr, drug_sales AS ds, " .
          "drug_inventory AS di " .
          "SET di.on_hand = di.on_hand + dsr.quantity - $units, " .
          "ds.quantity = '$units', ds.fee = '$fee', " .
          "ds.sale_date = '$visit_date' WHERE " .
          "dsr.sale_id = '$sale_id' AND ds.sale_id = dsr.sale_id AND " .
          "di.inventory_id = ds.inventory_id";
        sqlStatement($query);
      }
    }

    // Otherwise it's a new item...
    else if (! $del) {
      $sale_id = sellDrug($drug_id, $units, $fee, $pid, $encounter, 0,
        $visit_date, '', $default_warehouse);
      if (!$sale_id) die("Insufficient inventory for product ID \"$drug_id\".");
    }
  } // end for

  // Set the main/default service provider in the new-encounter form.
  /*******************************************************************
  sqlStatement("UPDATE forms, users SET forms.user = users.username WHERE " .
    "forms.pid = '$pid' AND forms.encounter = '$encounter' AND " .
    "forms.formdir = 'newpatient' AND users.id = '$provid'");
  *******************************************************************/
  sqlStatement("UPDATE form_encounter SET provider_id = '$main_provid', " .
    "supervisor_id = '$main_supid' , referrer_id = '$main_refid', ".
	  "pcp_id = '$main_pcp', servicing_provider_id = '$main_serPro', modified_date = '$todays_date', modified_by = '$userID' WHERE " .
    "pid = '$pid' AND encounter = '$encounter'");

  // More IPPF stuff.
  if (!empty($_POST['contrastart'])) {
    $contrastart = $_POST['contrastart'];
    sqlStatement("UPDATE patient_data SET contrastart = '" .
      $contrastart . "' WHERE pid = '$pid'");
  }

print '<script type="text/javascript">';
print 'alert("The Claim No. '. $encounter.' is updated successfully. ")';
print '</script>'; 
	////////////////////////////////////////////////////
	}
}
else {
  die("Unknown mode '$mode'");
}

setencounter($encounter);

// Update the list of issues associated with this encounter.
sqlStatement("DELETE FROM issue_encounter WHERE " .
  "pid = '$pid' AND encounter = '$encounter'");
if (is_array($_POST['issues'])) {
  foreach ($_POST['issues'] as $issue) {
    $query = "INSERT INTO issue_encounter ( " .
      "pid, list_id, encounter " .
      ") VALUES ( " .
      "'$pid', '$issue', '$encounter'" .
    ")";
    sqlStatement($query);
  }
}

// Custom for Chelsea FC.
//
if ($mode == 'new' && $GLOBALS['default_new_encounter_form'] == 'football_injury_audit') {
  // If there are any "football injury" issues (medical problems without
  // "illness" in the title) linked to this encounter, but no encounter linked
  // to such an issue has the injury form in it, then present that form.

  $lres = sqlStatement("SELECT list_id " .
    "FROM issue_encounter, lists WHERE " .
    "issue_encounter.pid = '$pid' AND " .
    "issue_encounter.encounter = '$encounter' AND " .
    "lists.id = issue_encounter.list_id AND " .
    "lists.type = 'medical_problem' AND " .
    "lists.title NOT LIKE '%Illness%'");

  if (mysql_num_rows($lres)) {
    $nexturl = "patient_file/encounter/load_form.php?formname=" .
      $GLOBALS['default_new_encounter_form'];
    while ($lrow = sqlFetchArray($lres)) {
      $frow = sqlQuery("SELECT count(*) AS count " .
         "FROM issue_encounter, forms WHERE " .
         "issue_encounter.list_id = '" . $lrow['list_id'] . "' AND " .
         "forms.pid = issue_encounter.pid AND " .
         "forms.encounter = issue_encounter.encounter AND " .
         "forms.formdir = '" . $GLOBALS['default_new_encounter_form'] . "'");
      if ($frow['count']) $nexturl = $normalurl;
    }
  }
}

$result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe ".
	" left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = '$pid' order by fe.date desc");
?>
<html>
<body>
<script language='JavaScript'>
<?php if ($GLOBALS['concurrent_layout'])
 {//Encounter details are stored to javacript as array.
?>
	EncounterDateArray=new Array;
	CalendarCategoryArray=new Array;
	EncounterIdArray=new Array;
	Count=0;
	 <?php
			   if(sqlNumRows($result4)>0)
				while($rowresult4 = sqlFetchArray($result4))
				 {
	?>
					EncounterIdArray[Count]='<?php echo htmlspecialchars($rowresult4['encounter'], ENT_QUOTES); ?>';
					EncounterDateArray[Count]='<?php echo htmlspecialchars(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date']))), ENT_QUOTES); ?>';
					CalendarCategoryArray[Count]='<?php echo htmlspecialchars( xl_appt_category($rowresult4['pc_catname']), ENT_QUOTES); ?>';
					Count++;
	 <?php
				 }
	 ?>
	 top.window.parent.left_nav.setPatientEncounter(EncounterIdArray,EncounterDateArray,CalendarCategoryArray);
<?php } ?>
 top.restoreSession();
<?php if ($GLOBALS['concurrent_layout'] || $_GET['set_pid']) { ?>
<?php if ($mode == 'new') { ?>
 parent.left_nav.setEncounter(<?php echo "'" . oeFormatShortDate($date) . "', $encounter, window.name"; ?>);
 //parent.left_nav.setRadio(window.name, 'enc');
<?php } // end if new encounter ?>
 parent.left_nav.loadFrame('enc2', window.name, '<?php echo $nexturl; ?>');
<?php } else { // end if concurrent layout ?>
 window.location="<?php echo $nexturl; ?>";
<?php } // end not concurrent layout ?>
</script>

</body>
</html>
