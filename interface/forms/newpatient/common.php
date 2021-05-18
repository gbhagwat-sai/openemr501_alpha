<?php
/**
 * Common script for the encounter form (new and view) scripts.
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
 * @link    http://www.open-emr.org
 */

require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/lists.inc");
// Sai custom code start
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");


 $patientname = xl('Click here and select Patient');
 // Sai custom code end


use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

if ($GLOBALS['enable_group_therapy']) {
    require_once("$srcdir/group.inc");
}

$months = array("01","02","03","04","05","06","07","08","09","10","11","12");
$days = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14",
  "15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31");
$thisyear = date("Y");
$years = array($thisyear-1, $thisyear, $thisyear+1, $thisyear+2);


// Sai custom code start
if($_REQUEST['new_enc_type']==1)
$new_enc_mode=1;
else
$new_enc_mode='';

$resultDiagnosis = sqlQuery("select * from diagnosis where encounter=$encounter");

$cpt_arr_rows = 105;
//$pid = empty($_REQUEST['from_finder']) ? 0 : $_REQUEST['set_pid'];

if($new_enc_mode==1){
$draft_id=$_REQUEST['draft_id'];

$result = sqlQuery("SELECT * FROM form_encounter_draft WHERE id = '$draft_id'");
  $pid = $result['pid'];
  $def_facility = $result['facility_id'];
  $facility_name = $result['facility'];
  $pos_id = $result['pos_id'];
  $batch_id = $result['batch_id'];
  $dos  = $result['date'];
  $reason = $result['reason'];
  $claim_status_id = $result['claim_status_id'];


  $billresult =getDraftBillingByEncounter($pid, $draft_id, "*");
  $encounter_provid = 0 + $result['provider_id'];
  $encounter_supid  = 0 + $result['supervisor_id'];
  $encounter_refid  = 0 + $result['referrer_id'];
  $encounter_pcp  = 0 + $result['pcp_id'];



}
// Sai custom code end
if ($viewmode) {
    $id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : '';
    $result = sqlQuery("SELECT * FROM form_encounter WHERE id = ?", array($id));
    $encounter = $result['encounter'];
  // Sai custom code start
  $pid = $result['pid'];
  $def_facility = $result['facility_id'];
  $def_bfacility = $result['billing_facility'];
  $pos_id = $result['pos_id'];
  $batch_id = $result['batch_id'];
  $dos  = $result['date'];
  $reason = $result['reason'];
  
//10525: Copay Validation
  $rows = sqlStatement("select a.session_id,a.reference,a.payment_method,b.pay_amount,b.sequence_no,b.encounter from ar_session as a left join ar_activity as b on a.session_id=b.session_id where b.encounter='$encounter' and b.pid= '$pid' order by b.sequence_no desc ");
  $numRows =  sqlNumRows($rows); 
  if($numRows<=0)
  $rows = sqlStatement("select a.session_id,a.reference,a.payment_method,0 as pay_amount from ar_session as a where a.patient_id= '$pid' order by a.session_id desc ");
  
  $result = sqlFetchArray($rows);
  $form_source = $result['reference'];
  $form_method = $result['payment_method'];
  $session_id = $result['session_id'];
  $copay_paid =  $result['pay_amount'];
  
  $billresult = getBillingByEncounter($pid, $encounter, "*");
//echo '<pre>';
//print_r($billresult);
  $tmp = sqlQuery("SELECT provider_id, supervisor_id,referrer_id,pcp_id, servicing_provider_id FROM form_encounter " .
  "WHERE pid = '$pid' AND encounter = '$encounter' " .
  "ORDER BY id DESC LIMIT 1");
$encounter_provid = 0 + $tmp['provider_id'];
$encounter_supid  = 0 + $tmp['supervisor_id'];
 $encounter_refid  = 0 + $tmp['referrer_id'];
  $encounter_pcp  = 0 + $tmp['pcp_id'];
  $encounter_serviceid  = 0 + $tmp['servicing_provider_id'];
    if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
        echo "<body>\n<html>\n";
        echo "<p>" . xlt('You are not authorized to see this encounter.') . "</p>\n";
        echo "</body>\n</html>\n";
        exit();
    }
}
// Sai custom code start
else
{

 if($_GET['set_pid'])
    $pid=$_GET['set_pid'];
 elseif($_SESSION['pid'])
    $pid = $_SESSION['pid'];
	elseif($_GET['pid'])
	$pid=$_GET['pid']; 
}
// Sai custom code end
// Sort comparison for sensitivities by their order attribute.
function sensitivity_compare($a, $b){
return ($a[2] < $b[2]) ? -1 : 1;
}
// Sai custom code start
// Build a drop-down list of providers.  This includes users who
// have the word "provider" anywhere in their "additional info"
// field, so that we can define providers (for billing purposes)
// who do not appear in the calendar.
//
//function genProviderSelect($selname, $toptext, $default=0, $disabled=false, $disabled=false, $role='', $isServicingPro, $mode) {
function genProviderSelect($selname, $toptext, $default=0,  $disabled1=false, $disabled=false, $role, $isServicingPro, $mode) {
	//Added by Gangeya to include inactive Providers in Edit mode.
  	//JIRA ID: PAYEHR - 48
	if($mode == ''){
		if($isServicingPro == 0){
			$query = "SELECT id, lname, fname FROM users WHERE " .
				"( authorized = 1 OR info LIKE '%provider%' ) AND username != '' " .
				"AND active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
				"AND newcrop_user_role = '$role' ".
				"ORDER BY lname, fname";
		}
		else{
			$query = "SELECT id, lname, fname FROM users WHERE " .
				"( authorized = 1 OR info LIKE '%provider%' ) AND username != '' " .
				"AND active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .			
				"AND isServicingProvider = '$isServicingPro' ".
				"ORDER BY lname, fname";
		}
	}
	else{
		if($isServicingPro == 0)
			$query = "SELECT id, lname, fname FROM users WHERE " .
				"( authorized = 1 OR info LIKE '%provider%' ) AND username != '' " .
				"AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
				"AND newcrop_user_role = '$role' ".
				"ORDER BY lname, fname";
		else
			$query = "SELECT id, lname, fname FROM users WHERE " .
				"( authorized = 1 OR info LIKE '%provider%' ) AND username != '' " .
				"AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
				"AND isServicingProvider = '$isServicingPro' ".
				"ORDER BY lname, fname";
	}
	
  $res = sqlStatement($query);
  // Modified by Sonali. 8845: Application should display default facility and provider when there is only one value in dropdown selection.
  $numProviders =  sqlNumRows($res); 
  echo "   <select name='$selname'";
  if ($disabled) echo " disabled";
  echo ">\n";
  
   // Modified by Sonali.  8845: Application should display default facility and provider when there is only one value in dropdown selection.
  if($numProviders==1)
  {
    $row = sqlFetchArray($res);
    $provid = $row['id'];
    echo "    <option value='$provid' 'selected'>". $row['lname'] . ", " . $row['fname'] . "\n";
  }
  else
  {
    echo "    <option value=''>$toptext\n";
    while ($row = sqlFetchArray($res)) {
      $provid = $row['id'];
      echo "    <option value='$provid'";
      if ($provid == $default) echo " selected";
      echo ">" . $row['lname'] . ", " . $row['fname'] . "\n";
    }
  }
  echo "   </select>\n";
}


//Modified By Sonali Dhumal
//Issue:Duplicate diagnosis code should be identified and  restricted

  
  $res = sqlStatement("SELECT id ,name FROM facility where id ='$facility_cd'");
	$row = sqlFetchArray($res);
	$FacilityCode=$row['id'];
	$FacilityName=$row['name'];
	$primary_insID = 0;
	
	$ins_res = sqlStatement("select ind.provider, ind.type,inc.name from insurance_companies inc,insurance_data as ind where ind.provider=inc.id and ind.pid='$pid' order by date");
	while($ins_row = sqlFetchArray($ins_res))	
	{
		if($ins_row['type'] == "primary"){
			$primary_insurance = $ins_row['name'];
			$primary_insID = $ins_row['provider'];
		}
		else
			$primary_insID = 0;
		
		if($ins_row['type'] == "secondary")
			$secondary_insurance = $ins_row['name'];
	}
	//echo "????????????".$pid;
	//Updated By Gangeya for BUG ID 11240
	$demo_res = sqlStatement("select Concat(street,postal_code,state) as address,DOB,ss,fname,mname,lname,pstatus,renderingproviderID,referringproviderID,supervisingPID,servicing_provider_i,PCP,facility_id,pos_id,IP_DP,stop_stmt from patient_data where pid='$pid'");
	while($demo_row = sqlFetchArray($demo_res)){

		$demo_address = $demo_row['address'];
		$demo_DOB = $demo_row['DOB'];
		$pstatus = $demo_row['pstatus'];
		$patient_name = $demo_row['fname']." ".$demo_row['mname']." ".$demo_row['lname'];
		$encounter_provid_def = $demo_row['renderingproviderID'];
		$encounter_supid_def = $demo_row['referringproviderID'];
		$encounter_refid_def = $demo_row['supervisingPID'];
		$encounter_serviceid_def = $demo_row['servicing_provider_i'];
		$encounter_pcp_def = $demo_row['PCP'];
		$def_facility_def = $demo_row['facility_id'];	
		$def_pos_id = $demo_row['pos_id'];	
		// modified by Sonali: 10412 IP/DP issue
		$ip_dp = $demo_row['IP_DP'];
		//Updated By Gangeya for BUG ID 11240
		$stop_stmt = $demo_row['stop_stmt'];
		
					
	}
		$stop_stmtArray  = explode("|",$stop_stmt);
		$stop_stmt = $stop_stmtArray['0'];
	
	// code for getting last encounter facility BUG 10173
	$facility_res = sqlStatement("select facility_id from form_encounter where pid='$pid' order by id desc");
	$facility_row = sqlFetchArray($facility_res);
	$prev_enc_facility = $facility_row['facility_id'];
	
	// code for getting user facility
	$user_res = sqlStatement("select facility_id from users where username = '" . $_SESSION['authUser'] . "'");
	$user_row = sqlFetchArray($user_res);
	$user_facility = $user_row['facility_id'];	
	
// Sai custom code end	
?>
<!DOCTYPE html>
<html>
<head>

<title><?php echo xlt('Patient Encounter'); ?></title>
    <?php Header::setupHeader(['jquery-ui', 'datetime-picker']); ?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.css" media="screen" />
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.4.3.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/overlib_mini.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
<!-- Sai custom code start -->
<script type="text/javascript">

//Gangeya : To add Shortcut functionality
document.onkeydown = function(evt) 
{
    masterkeypress(evt);
}

document.onkeydown = function(evt) 
{
	evt = evt || window.event; // because of Internet Explorer quirks...
	k = evt.which || evt.charCode || evt.keyCode; // because of browser differences...	
	if (k == 80 && evt.altKey && evt.ctrlKey && !evt.shiftKey) 
	{
		 patientSelect();
	}
	if (k == 112 && evt.altKey && evt.ctrlKey && !evt.shiftKey) 
	{
		 patientSelect();
	}
}			
	


</script>
<!-- Sai custom code end -->
<!-- pop up calendar -->
<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>

<!-- Sai custom code start -->
<style>
#ajax_div_facility {
	position: absolute;
	z-index:10;
	background-color: #FBFDD0;
	border: 1px solid #ccc;
	padding: 10px;
}
#addnewrow{
    font-family: verdana;
    font-size: 12px;
    border: 1px solid #EAEAEA;
    padding: 2px;
}

</style>
<!-- Sai custom code end -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?> <!-- Sai custom code  -->
<?php include_once("{$GLOBALS['srcdir']}/ajax/facility_ajax_jav.inc.php"); ?>
<script language="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
// Sai custom code start 
 var diags = new Array();

// When a justify selection is made, apply it to the current list for
// this procedure and then rebuild its selection list.
//
function newDiagJS1(codetype,code){
 if (codetype == 'ICD9') {
    diags.push(code);
} 
}
function total_charge_calc(){
total_charge = document.getElementById('total_charge_id').value
}

// Sai custom code end 
 // Process click on issue title.
 function newissue() {
  dlgopen('../../patient_file/summary/add_edit_issue.php', '_blank', 700, 535, '', '', {
      buttons: [
          {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
      ]
  });
  return false;
 }
//Sai custom code start  move all below code while migration

//Modified by Sonali Dhumal 
//Issue: Need to add,  modify and delete the Diagnosis code in the same screen
 // callback from add_edit_issue.php:
/* function refreshIssue(issue, title) {
 //alert(document.forms[0]['issues[]'].value+'=='+issue+'=='+title);
 dropDownListRef = document.getElementById('issues');
  var itemIndex = dropDownListRef.selectedIndex;
  var s = document.forms[0]['issues[]']; 
  if(document.forms[0]['issues[]'].value != issue)
  s.options[s.options.length] = new Option(title, issue, true, true);
  else
  {
  s.options[s.options.length-1] = null;
   dropDownListRef.remove(itemIndex); 
  s.options[s.options.length] = new Option(title, issue, true, true);
  }
   //location.reload();
 }
  function refreshFacility(facility,fac_id) {
  dropDownListRef = document.getElementById('facility_id');
  var itemIndex = dropDownListRef.selectedIndex;
  var s = document.forms[0]['facility_id']; 
  flag=0;
  for (i = dropDownListRef.length - 1; i>0; i--) {   
	if(dropDownListRef.options[i].value==fac_id){
	   //dropDownListRef.remove(fac_id); 
	   //$("#facility_id option[value='" + dropDownListRef.value + "']").remove();  
	   var oOption = dropDownListRef.options[i]; //Parkhead
		dropDownListRef.removeChild(oOption);
	   s.options[s.options.length] = new Option(facility, true, true);
	   flag=1;
	   break;
	}	 
  }
  if(flag==0){
  s.options[s.options.length] = new Option(facility, true, true);  
  }
 }*/  
 function total_charges_calc(lino){
var str = "bill[" + lino +"][units]";
var str1 = "bill[" + lino +"][price]";
var str2 = "bill[" + lino +"][total_charges]";
var units_str = document.getElementById(str).value;
var price_str = document.getElementById(str1).value;
if(price_str == ''){
		alert("Charges cannot be blank.");
		
		return false;
	}
	if(units_str == ''){
		alert("Units cannot be blank.");
		return false;
	}
	if(document.getElementById(str2).value == ''){
		alert("Total charges cannot be blank.");
		return false;
	}	
	 if(price_str != '' && units_str!=''){
		total = (price_str) * (units_str);		
		document.getElementById(str2).value=total;
	}
	
 }
 function cancelEncounter(){ 
  
  top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/new.php?autoloaded=1&calenc=');  
 }
 
 function patientSelect(){
 //dlgopen('../../main/finder/dynamic_finder.php?from_enc=1', '_blank', 800, 600);
 document.location.href = "../../main/finder/dynamic_finder.php?from_enc=1"; 
  return false;
 }
 
function addnewrow(e,pid,code_type){
var f = document.forms[0];   
	if(e.keyCode == 13)
	{
	var cpt_arr_rows = document.getElementById('cpt_arr_rows').value;
	var cpt_arr_rows1 = parseInt(cpt_arr_rows) ;
	ajaxaddnewdiv(cpt_arr_rows,pid,code_type);
	document.getElementById('cpt_arr_rows').value = parseInt(cpt_arr_rows) + 1;	
	}
}

function process(e){
 var code = (e.keyCode ? e.keyCode : e.which); 
            var parent = $("#classes tr:last-child").closest('tr');
            var tr = parent.clone();
            if($("#classes tr:last-child td:last-child input").val() !== '')
            {
                $("#classes").append(tr);
            }
            else if($("#classes tr:last-child td").eq(0).find("input").val() === '' && $("#classes tr:last-child td").eq(1).find("input").val() === '' && $("#classes tr:last-child td").eq(2).find("input").val() === '')
            {
                $("#classes tr:last-child").remove();
            }
        }

 function saveClicked(status) {
  var f = document.forms[0];   
  var flag= 0;
  var flag1= 0;
  document.getElementById('mode').value=status;

   if(status=='draft' || status=='update' || status=='new'){
	if(document.getElementById('self_pay').value != 'self_pay'){
		if(document.getElementById('primary_ins').value=='')
		{	
			if(document.getElementById('ip_dp').value!="IP|DP" && document.getElementById('ip_dp').value!="IP" )
			{
				alert("Patient does not have primary insurance. Please check IP on demegraphics.");
				return false;
			}
			else if(document.getElementById('Encounter_Status').value!="2" && document.getElementById('ip_dp').value=="IP")
			{
				alert("Please select claim status as 'Unbilled Insurance Pending' ");
				return false;
			}
			else if((document.getElementById('Encounter_Status').value!="1" && document.getElementById('Encounter_Status').value!="2") && document.getElementById('ip_dp').value=="IP|DP")
			{
				alert("Please select claim status as 'Unbilled Insurance Pending' or 'Unbilled Demo Pending' ");
				return false;
			}
		}
	}
	
	if(document.getElementById('patient_name').value=='')
	{
		alert("Please select Patient");
		return false;
	}
	if(document.getElementById('primary_ins').value!='')
	{
	if(document.getElementById('ip_dp').value!="IP|DP" && document.getElementById('ip_dp').value!="DP")
	{
		if(document.getElementById('demo_DOB').value=='')
		{
			alert("Patient's DOB is missing. Please check DP on demographics.");
			return false;
		}
		/*if(document.getElementById('demo_ss').value=='')
		{
			alert("Patient's SSN is missing");
			return false;
		}*/
		if(document.getElementById('demo_address').value=='')
		{
			alert("Patient's address is missing. Please check DP on demographics.");
			return false;
		}
	 }
	 else if(document.getElementById('Encounter_Status').value!="1")
		{
			alert("Please select claim status as 'Unbilled Demo Pending' ");
			return false;
		}
	}
	if(document.getElementById('facility_code').value=='0'){
	alert("Please enter facility.");
	document.getElementById('facility_code').focus();
	return false;
	}		
	if(document.getElementById('form_date').value==''){
	alert("Please enter date of service.");
	document.getElementById('form_date').focus();
	return false;
	}
	
	
	if(document.getElementsByName("RenderingProvider")[0].value==''){
	alert("Please select Rendering Provider.");
	document.getElementsByName("RenderingProvider")[0].focus();
	return false;
	}
	if(document.getElementById('batch_id').value==''){
	alert("Please enter batch.");
	document.getElementById('batch_id').focus();
	return false;
	}
	var reason = document.getElementById('reason').value;
	reason = reason.trim();
	if( reason == ''){
	alert("Please enter notes.");
	document.getElementById('reason').focus();
	return false;
	}
	
	for(var j=1;j<4;j++){
	code_str1 = "bill[" + j +"][code]";
	if(document.getElementById(code_str1).value!='')
		flag1=1;	
	}
	
	var cpt_tble_arr = document.getElementById('cpt_arr_rows').value;	
	for(var i=105;i<cpt_tble_arr;i++){
	str="bill[" + i +"][total_charges]";
	code_str = "bill[" + i +"][code]";
	justify_str = "bill[" + i +"][justify]";
	//frm_date = "bill[" + i +"][from_dos]";
	//to_date = "bill[" + i +"][to_dos]";		
	if(document.getElementById(str).value == "" && document.getElementById(code_str).value != ""){
	alert("Please enter charges. Charges cannot null");
		document.getElementById(str).focus();
		return false;
	}
	else if(document.getElementById(str).value!="" && document.getElementById(str).value==0)
		{
		alert("Please enter charges. Charges cannot be 0");
		document.getElementById(str).focus();
		return false;
		}
		if(document.getElementById(code_str).value!=''){
		flag=1;	
			/*if(document.getElementById(frm_date).value==''){
			alert("Please enter from date");
			document.getElementById(frm_date).focus();
			return false;
			}*/
			/*if(document.getElementById(to_date).value==''){
			alert("Please enter to date");
			document.getElementById(to_date).focus();
			return false;
			}*/
			if(document.getElementById(justify_str).value=='')
			{
				alert("Please enter DX linking");
				document.getElementById(justify_str).focus();
				return false;
			}
		}
		if(document.getElementById(justify_str).value!='')
		{
		just_value=document.getElementById(justify_str).value;
			for (var js = 0; js < just_value.length && just_value.charAt(js)!=0 ; js++) {
    			//alert(just_value.charAt(js));
				dx_str1= "bill[" + just_value.charAt(js) +"][code]";


				if(document.getElementById(dx_str1).value==''){
				alert("Please enter correct diagnosis pointer");
				return false;
				}
				
			}
		}		
	}
	if(flag1==0)
{
	alert("Please enter atleast one ICD9 code");
	document.getElementById('bill[1][code]').focus();
	return false;
}
if(flag==0)
{
	alert("Please enter atleast one CPT code");
	document.getElementById('bill[5][code]').focus();
	return false;
}
	

  }
if(status=='review'){
	if(document.getElementById('batch_id').value==''){
		alert("Please enter batch");
		document.getElementById('batch_id').focus();
		return false;
	}
	if(document.getElementById('primary_ins').value=='' || document.getElementById('demo_DOB').value==''  || document.getElementById('demo_address').value=='')
	{	
		if(document.getElementById('self_pay').value != 'self_pay'){		
			if(document.getElementById('batch_id').value != 'DP' && document.getElementById('batch_id').value != 'IP' && document.getElementById('batch_id').value != 'Waiting for Enrollment' ){
				alert("Please enter correct batch as 'DP' or 'IP' or 'Waiting for Enrollment' ");
				document.getElementById('batch_id').focus();
				return false;
			}
		}
	}
}
if(status=='clarification'){
	if(document.getElementById('batch_id').value==''){
		alert("Please enter batch");
		document.getElementById('batch_id').focus();
		return false;
	}
	if(document.getElementById('batch_id').value != "DX Clarification" && document.getElementById('batch_id').value != "CPT Clarification" && document.getElementById('batch_id').value != "Location Clarification" && document.getElementById('batch_id').value != "Provider Clarification" && document.getElementById('batch_id').value != "DOS Clarification" && document.getElementById('batch_id').value != "DOB Clarification" ){
		alert("Please enter correct batch as 'DX Clarification' or 'CPT<sup>&reg;</sup> Clarification' or 'Location Clarification' or 'Provider Clarification' or 'DOS Clarification' or 'DOB Clarification' ");
		document.getElementById('batch_id').focus();
		return false;
	}
}
 if (status=='move')
 {
 
 if(document.getElementById('move_to_patient').value=='Click here and select Patient')
 {
 alert('Please select Patient first');
return false;
 }
 
 
 }
 if(document.getElementById('bill[11][price]').value!='' && (document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value=='check_payment') && (document.getElementById('check_number').value==''))
 {
 alert("Please fill Check Number");
 document.getElementById('check_number').focus();
 return false;
 }

  top.restoreSession();
  f.submit();
 }



$(document).ready(function(){ 
 // enable_big_modals();
  $("input[name=class_name\\[\\]]:last-child").keyup(function addNewRow(e){
        var parent = $(this).closest('tr');
        var tr = parent.clone();
        if(e.keyCode != 8){
            $("#classes tr:last-child").remove();
        }else{
            $("#classes").append(tr);
        }
    });


  // code for ICD type
  var default_facility =  document.getElementById('facility_id').value;
  var patient_facility =  document.getElementById('facility_id_pat').value;
  var selected_facility =  document.getElementById('facility_code').value;
  var icd_type =  document.getElementById('icd_code_type').value;
  

  
  if(icd_type == 9){
    display_icd_box(9); // code added for BUG 10524
  }
  else{
  
    display_icd_box(0);
  }
  
  
  if(patient_facility != ""){
  
    if(selected_facility != 0){
      if(selected_facility != patient_facility){
          //alert("Demo Facility is different from last encounter");
         if (confirm("Demo Facility is different from last encounter") == true) {
          document.getElementById('facility_code').value = selected_facility;
         }
         else {
          document.getElementById('facility_code').value = patient_facility;
         }
      }
    }
    else{
      document.getElementById('facility_code').value = patient_facility;
    
    }
  }
  else{
  
      alert("Please set the default facility for patient");
      //document.getElementById('facility_code').value = 0;
  }
  
  
  
});
function bill_loc(){
var pid=<?php echo $pid;?>;
var dte=document.getElementById('form_date').value;
var facility=document.forms[0].facility_id.value;
ajax_bill_loc(pid,dte,facility);
}

function display_icd_box(value)
{
//alert(value);

/*document.getElementById('bill[11][code]').style.visibility='hidden';
var ab= document.getElementById('bill[11][code]').value;
alert (ab);
alert (document.getElementById('bill[12][code]').value);*/

// code change for BUG 10524 more than 4dx
/*document.getElementById('bill[1][code]').value='';
document.getElementById('bill[2][code]').value='';
document.getElementById('bill[3][code]').value='';
document.getElementById('bill[4][code]').value='';
document.getElementById('bill[5][code]').value='';
document.getElementById('bill[6][code]').value='';
document.getElementById('bill[7][code]').value='';
document.getElementById('bill[8][code]').value='';
document.getElementById('bill[9][code]').value='';
document.getElementById('bill[X][code]').value='';
document.getElementById('bill[Y][code]').value='';
document.getElementById('bill[Z][code]').value='';*/

if(value==9)
{
/*document.getElementById('dx5').style.visibility='hidden';
document.getElementById('dx6').style.visibility='hidden';
document.getElementById('dx7').style.visibility='hidden';
document.getElementById('dx8').style.visibility='hidden';
document.getElementById('dx9').style.visibility='hidden';
document.getElementById('dx10').style.visibility='hidden';
document.getElementById('dx11').style.visibility='hidden';
document.getElementById('dx12').style.visibility='hidden';
document.getElementById('bill[5][code]').style.visibility='hidden';
document.getElementById('bill[6][code]').style.visibility='hidden';
document.getElementById('bill[7][code]').style.visibility='hidden';
document.getElementById('bill[8][code]').style.visibility='hidden';
document.getElementById('bill[9][code]').style.visibility='hidden';
document.getElementById('bill[X][code]').style.visibility='hidden';
document.getElementById('bill[Y][code]').style.visibility='hidden';
document.getElementById('bill[Z][code]').style.visibility='hidden';*/

// code change for BUG 10524 more than 4 DX for ICD 9
document.getElementById('dx5').style.visibility='visible';
document.getElementById('dx6').style.visibility='visible';
document.getElementById('dx7').style.visibility='visible';
document.getElementById('dx8').style.visibility='visible';
document.getElementById('dx9').style.visibility='visible';
document.getElementById('dx10').style.visibility='visible';
document.getElementById('dx11').style.visibility='visible';
document.getElementById('dx12').style.visibility='visible';
document.getElementById('bill[5][code]').style.visibility='visible';
document.getElementById('bill[6][code]').style.visibility='visible';
document.getElementById('bill[7][code]').style.visibility='visible';
document.getElementById('bill[8][code]').style.visibility='visible';
document.getElementById('bill[9][code]').style.visibility='visible';
document.getElementById('bill[X][code]').style.visibility='visible';
document.getElementById('bill[Y][code]').style.visibility='visible';
document.getElementById('bill[Z][code]').style.visibility='visible';
document.getElementById('bill[1][code_type]').value='ICD9';
document.getElementById('bill[2][code_type]').value='ICD9';
document.getElementById('bill[3][code_type]').value='ICD9';
document.getElementById('bill[4][code_type]').value='ICD9';
document.getElementById('bill[5][code_type]').value='ICD9';
document.getElementById('bill[6][code_type]').value='ICD9';
document.getElementById('bill[7][code_type]').value='ICD9';
document.getElementById('bill[8][code_type]').value='ICD9';
document.getElementById('bill[9][code_type]').value='ICD9';
document.getElementById('bill[X][code_type]').value='ICD9';
document.getElementById('bill[Y][code_type]').value='ICD9';
document.getElementById('bill[Z][code_type]').value='ICD9';


}
else
{
document.getElementById('dx5').style.visibility='visible';
document.getElementById('dx6').style.visibility='visible';
document.getElementById('dx7').style.visibility='visible';
document.getElementById('dx8').style.visibility='visible';
document.getElementById('dx9').style.visibility='visible';
document.getElementById('dx10').style.visibility='visible';
document.getElementById('dx11').style.visibility='visible';
document.getElementById('dx12').style.visibility='visible';
document.getElementById('bill[5][code]').style.visibility='visible';
document.getElementById('bill[6][code]').style.visibility='visible';
document.getElementById('bill[7][code]').style.visibility='visible';
document.getElementById('bill[8][code]').style.visibility='visible';
document.getElementById('bill[9][code]').style.visibility='visible';
document.getElementById('bill[X][code]').style.visibility='visible';
document.getElementById('bill[Y][code]').style.visibility='visible';
document.getElementById('bill[Z][code]').style.visibility='visible';
document.getElementById('bill[1][code_type]').value='ICD10';
document.getElementById('bill[2][code_type]').value='ICD10';
document.getElementById('bill[3][code_type]').value='ICD10';
document.getElementById('bill[4][code_type]').value='ICD10';
document.getElementById('bill[5][code_type]').value='ICD10';
document.getElementById('bill[6][code_type]').value='ICD10';
document.getElementById('bill[7][code_type]').value='ICD10';
document.getElementById('bill[8][code_type]').value='ICD10';
document.getElementById('bill[9][code_type]').value='ICD10';
document.getElementById('bill[X][code_type]').value='ICD10';
document.getElementById('bill[Y][code_type]').value='ICD10';
document.getElementById('bill[Z][code_type]').value='ICD10';
}

}
function policykeyup(e,evt) {


 evt = (evt) ? evt : window.event;
 var charCode = (evt.which) ? evt.which : evt.keyCode;

if(charCode == 37)
        evt.cancelBubble = true;
    else{
	    if(charCode >= 97 || charCode <=122)
		 var v = e.value.toUpperCase(); 
		
 for (var i = 0; i < v.length; ++i) {
  var c = v.charAt(i);
  if (c >= '0' && c <= '9') continue;
  if (c >= 'A' && c <= 'Z') continue;
  if (c == '*') continue;
  if (c == '-') continue;
  if (c == '_') continue;
  if (c == '(') continue;
  if (c == ')') continue;
  if (c == '#') continue;
  if (c == ' ') continue;
  v = v.substring(0, i) + v.substring(i + i);
  --i;
 }
 
 e.value = v;
 }
 return;
}
// Modified by Sonali. 8845: Application should display default facility and provider when there is only one value in dropdown selection.
function checkmodifier(obj,cnt,num){
	modifier = obj.value;
	if(modifier.match(/\s/g)){

	alert('Sorry, you are not allowed to enter any spaces');
	//modifiere=modifier.replace(/\s/g,'');
	str1= 'bill['+ cnt +'][mod'+ num +']';	
			document.getElementById(str1).value='';			
			document.getElementById(str1).focus();
			return false;
	}else{
		len = modifier.length;	
		if(len<2 && len>0){
			alert("Modifier should not be single digit.");
			str1= 'bill['+ cnt +'][mod'+ num +']';	
			document.getElementById(str1).value='';			
			document.getElementById(str1).focus();
			return false;
	}
	}
}

function cpt_code_popup(obj,pat_id,code_type){
var sel_facility= document.getElementById('facility_code').value;
var primary_insurance = <?php echo $primary_insID;?>;
var selctedFeeSchedule = document.getElementById('feeschedule').value;

code = obj.value;

lineno = obj.id.charAt(5);



if(code_type==0)
{
flag=0;
	modr_value="0";
	if (!isNaN(lineno))
	{
	for(i=1;i<=9;i++){
	
	if(i==lineno)
	continue;
	
	str = 'bill['+ i +'][code]';
	str1= 'bill['+ lineno +'][code]';
		if((document.getElementById(str).value.toUpperCase() == document.getElementById(str1).value.toUpperCase() ) && document.getElementById(str1).value.trim().length!=0)
		flag=1;
		}
	}
	if (lineno=='X')
	{
	for(i=1;i<=9;i++){
	
	if(i==lineno)
	continue;
	
	str = 'bill['+ i +'][code]';
	str1= 'bill['+ lineno +'][code]';
		if((document.getElementById(str).value.toUpperCase() == document.getElementById(str1).value.toUpperCase()) && document.getElementById(str1).value.trim().length!=0)
		flag=1;
		}
	}
	if (lineno=='Y')
	{
	for(i=1;i<=9;i++){
	
	if(i==lineno)
	continue;
	
	str = 'bill['+ i +'][code]';
	str1= 'bill[Y][code]';
		if((document.getElementById(str).value.toUpperCase() == document.getElementById(str1).value.toUpperCase()) && document.getElementById(str1).value.trim().length!=0)
		flag=1;
		}

		if((document.getElementById('bill[Y][code]').value.toUpperCase() == document.getElementById('bill[X][code]').value.toUpperCase()) && document.getElementById('bill[X][code]').value.trim().length!=0)
		{
		flag=1;
		str1='bill[X][code]' ;
		}
	}
	if (lineno=='Z')
	{
	for(i=1;i<=9;i++){
	
	if(i==lineno)
	continue;
	
	str = 'bill['+ i +'][code]';
	str1= 'bill[Z][code]';
	
	
	
		if((document.getElementById(str).value.toUpperCase() == document.getElementById(str1).value.toUpperCase()) && document.getElementById(str1).value.trim().length!=0)
		flag=1;
		}

		if((document.getElementById('bill[Z][code]').value.toUpperCase() == document.getElementById('bill[X][code]').value.toUpperCase()) && document.getElementById('bill[X][code]').value.trim().length!=0)
		{
		str1='bill[X][code]';
		flag=1;
		}
		if((document.getElementById('bill[Z][code]').value.toUpperCase() == document.getElementById('bill[Y][code]').value.toUpperCase()) && document.getElementById('bill[Y][code]').value.trim().length!=0)
		{
		str1='bill[X][code]';
		flag=1;
		}
	}
	
	if(flag==1)
	{
		alert("Duplicate ICD not allowed");
		document.getElementById(str1).value='';
		document.getElementById(str1).focus();
	}
}
else if(code_type==1)
{
lineno =obj.id.substring(5,8);

flag=0;
modr1= 'bill['+ lineno +'][mod1]';
modr_value=document.getElementById(modr1).value;
	for(i=105;i<lineno;i++){
	str = 'bill['+ i +'][code]';
	str1= 'bill['+ lineno +'][code]';
	modr = 'bill['+ i +'][mod1]';
	modr1= 'bill['+ lineno +'][mod1]';
		if(document.getElementById(str).value == document.getElementById(str1).value)
		{
		if(document.getElementById(modr).value == document.getElementById(modr1).value)
		{
		//flag=1;
		}
		}
	}
	if(flag==1)
	{
		alert("Duplicate CPT4 not allowed");
		document.getElementById(str1).value='';
		document.getElementById(str1).focus();
	}


//cd_type=document.getElementById('bill['+lineno+'][code_type]').value;
}
ajax_cpt_code_popup(code,lineno,pat_id,document.getElementById('bill['+lineno+'][code_type]').value,modr_value, selctedFeeSchedule);



}

function set_billing_facility(){
	var sel_facility= document.getElementById('facility_code').value;
	alert(sel_facility);
}

// modified by sonali for bug 9551
function mod_cpt_chk(obj,pat_id,code_type){
	
var sel_facility= document.getElementById('facility_code').value;
var primary_insurance = <?php echo $primary_insID;?>;
var selctedFeeSchedule = document.getElementById('feeschedule').value;

if(code_type==1)
{
lineno =obj.id.substring(5,8);

flag=0;
	for(i=105;i<lineno;i++){
	str = 'bill['+ i +'][code]';
	str1= 'bill['+ lineno +'][code]';
	modr = 'bill['+ i +'][mod1]';
	modr1= 'bill['+ lineno +'][mod1]';
	code= document.getElementById(str1).value;
		if(document.getElementById(str).value == document.getElementById(str1).value)
		{
		if(document.getElementById(modr).value == document.getElementById(modr1).value)
		flag=1;
		}
	}
	if(flag==1)
	{
		alert("Duplicate CPT4 with same modifier are not allowed");
		document.getElementById(modr1).value='';
		document.getElementById(str1).value='';
		document.getElementById(str1).focus();
		return false;
	}


ajax_cpt_code_popup(code,lineno,pat_id,document.getElementById('bill['+lineno+'][code_type]').value,document.getElementById(modr1).value, selctedFeeSchedule);
}
}
 // Modified by Sonali Dhumal on 15/1/2013 
//Issue : Need an option to change, insert and delete the facility from the lookup
 // Process click on Add/Edit link.
 function add_edit_facility() {
  dlgopen('../../usergroup/facilities.php?link=facility_add', '_blank', 800, 800);
  return false;
 }
 
  function icd9_codes(code_type) { 
  dlgopen('../../forms/newpatient/codes_popup.php?code_type='+code_type, '_blank', 600, 600);
   
  return false;
 }
 
 /*function removeIssueOptionSelected()
{//Remove the selected options from the drop down.
  OptionRemoved='no';
  var elSel = document.getElementById('issues');
  var i;
  for (i = elSel.length - 1; i>=0; i--) {
    if (elSel.options[i].selected) {
      elSel.remove(i);
      OptionRemoved='yes';
    }
  }
  if(OptionRemoved=='no')
   {
       alert("<?php echo htmlspecialchars( xl('Select Issues to Remove'), ENT_QUOTES) ?>")
   }
}*/
 // Process click on Delete link.
 function deleteme() {
  var f = document.forms[0];
  var issue = document.getElementById("issues");
  len = issue.length;
  selected_value = '1';

  for (i = 0; i < len; i++) {
            if (issue[i].selected == true) {
               // issue[i].selected = true;
			    var issulist = issulist.concat(issue[i].value);				
            }			
        }
  //alert(issuelist);
  //dlgopen('../../patient_file/deleter.php?issue='+issue, '_blank', 500, 450);
  return false;
 }
 
function editClicked() {
 var f = document.forms[0];
 var issue = document.getElementById("issues").value;   		
 if (f['issues[]'].selectedIndex < 0) 
  alert('No Issue selected. To edit issue select one issue.'); 
  else
    dlgopen('../../patient_file/summary/add_edit_issue.php?mode=edit&issue='+issue, '_blank', 800, 800); 

	 return true;  
 }
 function handleDiv(obj)
 {
  if(obj.value.length == 0)
{
document.getElementById('ajax_div_facility').style.visibility = 'hidden';
	}
	else
	{
document.getElementById('ajax_div_facility').style.visibility = 'visible'; 
//    document.getElementById('ajax_div_facility').innerHTML;
   }
 } 
 
  // This is for callback by the find-patient popup.
 function setpatient(pid, lname, fname, dob) {
  var f = document.forms[0];
  f.move_to_patient.value = lname + ', ' + fname;
  f.move_to_patient_id.value = pid;
  dobstyle = (dob == '' || dob.substr(5, 10) == '00-00') ? '' : 'none';
  document.getElementById('dob_row').style.display = dobstyle;
 }

 // This invokes the find-patient popup.
 function sel_patient() {
  dlgopen('<?php echo $rootdir ?>/forms/newpatient/find_patient_popup.php', '_blank', 500, 500);
   
 }

 
function validatejustify(just_pointer,lineno){
str = "bill[" + lineno +"][justify]";
justify = document.getElementById(str).value;
flag=0;
var msg=''; var msg1=''; var msg2='';
var arr = [];
var arr1 = [];
var newarr = [];


if(document.getElementById('icd_code_type').value==0 || document.getElementById('icd_code_type').value==9)
{

	if(justify.length >4){
		msg = "Please enter only 4 digits\n";
		flag=1;
	}
	for(var i=0; i<justify.length; i++)
	{

	 	c = justify.charAt(i);		
		
		arr.push(c);
		arr1.push(c);
		if(c==0){
			msg = "0 is not allowed\n";
			flag=1;
		}
	
		if(isNaN(c))
		{
			if(c!='X' && c!='Y' &&  c!='Z')
			{
				msg1 = "Please enter digits between 1 to 9 for icd 1to 9 \n use X for Dx10 \n use Y for Dx11 \n use Z for Dx12";
				flag=1;	
			}		
		}		
	}

}
else
{

if(justify.length >=5){
msg = "Please enter only 4 digits\n";
flag=1;
}
for(var i=0; i<justify.length; i++)
	{
	 	c = justify.charAt(i);		
		arr.push(c);
		arr1.push(c);			
		if(c<=0 || c>=5 )
		{
			msg1 = "Please enter digits between 1 to 4\n";
			flag=1;			
		}		
	}
}	
	newarr = sort_and_unique( arr );	
		if(arr1.length != newarr.length)
		{
			msg2 = "Please remove duplicate pointers\n";
			flag=1;
		}
	
	if(flag==1)
		{
			alert(msg+msg1+msg2);
			document.getElementById(str).value=''; 
			document.getElementById(str).focus();
			return false;
		}
}

function validateAmount(amount){
if(isNaN(amount)){
alert("Please enter correct amount.\nCharacters not allowed.");
document.getElementById('bill[11][price]').value='';
document.getElementById('bill[11][price]').focus();
return false;
}
else{
var balance = document.getElementById("check_balance").innerHTML;
var balance_amt = parseInt(balance);
// Bug 10525 Copay issue
var form_method = document.getElementById('form_method').value;
var check_number = document.getElementById('check_number').value;
if(form_method==0)
{
 alert("Please select Payment Method");
 document.getElementById('bill[11][price]').value='';
 document.getElementById('form_method').focus();
}
else if(check_number==0){
alert("Please select Check/Reference Number.");
document.getElementById('bill[11][price]').value='';
 document.getElementById('check_number').focus();
}
else if(balance_amt=="" || balance_amt==0 || isNaN(balance_amt))
{
 alert("Please enter copay amount first ");
 document.getElementById('form_method').value="0";
   document.getElementById('bill[11][price]').value='';
document.getElementById('bill[11][price]').focus();
return false;
}
else if(amount>balance_amt)
  {
   alert("Copay amount should not be greater than "+balance_amt);
   document.getElementById('bill[11][price]').value='';
document.getElementById('bill[11][price]').focus();
return false;
  }
}
}

function sort_and_unique(my_array ) {
    my_array.sort();
    for ( var i = 1; i < my_array.length; i++ ) {
        if ( my_array[i] === my_array[ i - 1 ] ) {
                    my_array.splice( i--, 1 );
        }
    }	
    return my_array;
};

/*function replacedate(obj){
	str=obj.value;
	//str=str.replace(/[^\d]/gi,'');
	var cpt_tble_arr = document.getElementById('cpt_arr_rows').value;
	for(var i=105;i<=cpt_tble_arr;i++){
	strfrmdate = 'bill[' + i + '][from_dos]';
	strtodate = 'bill[' + i + '][to_dos]';
		document.getElementById(strfrmdate).value=str;
		document.getElementById(strtodate).value=str;
	}	
	return;
}*/
function replacetodate(cnt,obj){
	str=obj.value;	
	strtodate = 'bill[' + cnt + '][to_dos]';
	document.getElementById(strtodate).value=str;	
	return;
}
function CheckVisible()
 {//Displays and hides the check number text box.
   if(document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value=='check_payment' ||
   	  document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value=='bank_draft'  )
   {
	document.getElementById('check_number').disabled=false;
   }
   else
   {
   document.getElementById('check_number').value='';
	document.getElementById('check_number').disabled=true;
   }
 }
 function CopayChange(mode)
 {
 if(mode==1){
 	alert("You are editing the copay. Are you sure about it");
	return false;
	}
 }
 function calculate_check_balance(check_number){ 
 var result = check_number.split('@');
 chk_num=result[1];
 session_id = result[0];
 ajax_check_balance(chk_num,session_id);
 }
 
 function AddPaymentvalues(payment_type,pid)
{
	ajax_add_payment(payment_type,pid);
}


// code added for validation of default facility BUG 10173	
function default_facility(){
	
	var sel_facility= document.getElementById('facility_code').value
	var def_facility= document.getElementById('facility_id').value
	
	
	
	if(sel_facility != def_facility){
	
		 if (confirm("Please confirm the selected facility is belongs to the patient") == true) {
     		  document.getElementById('facility_code').value = sel_facility;
   		 } else {
       		if(def_facility == 0)
				document.getElementById('facility_code').value =0;
			else
				document.getElementById('facility_code').value = def_facility;
				
   		 }
	}
	

}	 


//Added by Gangeya BUG ID 10922
function isDisable(){
	var dOS = document.getElementById('form_date').value;

	var dateOne = new Date(dOS); //Year, Month, Date
	var dateTwo = new Date(2015, 9, 1); //Year, Month, Date

	if (dateOne >= dateTwo) {
		//alert("Date One is greather then Date Two.");	
		document.getElementById('icd_code_type').options[1].selected = true;
		document.getElementById('icd_code_type').options[0].disabled = true;
		display_icd_box(0);
	}else {
		//alert("Date Two is greather then Date One.");
		document.getElementById('icd_code_type').options[0].selected = true;
		document.getElementById('icd_code_type').options[1].disabled = true;
		
		display_icd_box(9);
	}
}
</script>
</head>

<?php if ($viewmode) { ?>
<body class="body_top">
<?php } else { ?>
<body class="body_top" onload="javascript:document.new_encounter.facility_code.focus();">

<?php } ?>

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form method='post' action="<?php echo $rootdir ?>/forms/newpatient/save.php" id="new_encounter" name='new_encounter' <?php if (!$GLOBALS['concurrent_layout']) echo "target='Main'"; ?> >
<br>
<div style = 'float:left'>



<?php if ($viewmode) { $mode='update'; ?>
<?php /*?><input type=hidden name='mode' id='mode' value='update'><?php */?>
<input type=hidden name='id' value='<?php echo $_GET["id"] ?>'>
<span class=title><?php xl('Patient Encounter Form','e'); ?>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
$dres = sqlStatement("select hospice from form_encounter where encounter = '$encounter'");
  $drow = sqlFetchArray($dres);
   $hsp= $drow['hospice'];
   if($hsp == 'NA' ||$hsp == ''){
		echo " <font color='red' size='3pt'>hospice is not applicable to this encounter</font>";
	}
	else if($hsp == '0')
	{
		echo " <font color='red' size='3pt'>This encounter is not relevant to hospice Care.</font>";
	}	
	else if($hsp == '1')
	{
		echo " <font color='red' size='3pt'>This encounter is relevant to hospice Care.</font>";
	}
   
?></span>





<?php } else { $mode='new'; $encounter=''; global $Code_arr; ?>
<?php /*?><input type='hidden' name='mode' id='mode' value='new'><?php */?><span class='title'><?php xl('New Encounter Form','e'); ?></span>

<?php } 
global $justinit1;
$justinit1 = " var f = document.forms[0];\n ";
?>
</div>
</span></div>
<div style="float:left;padding-left:10px;"><span class="text title_bar_top">
<input type='button'  onClick="javascript:patientSelect();" value='<?php xl('Select Patient ...','e');?>' style="width:150px" /> &nbsp;&nbsp;<input type="text" name="patient_name" id="patient_name" value="<?php echo $patient_name;?>" readonly>
</span></div>
<div style="float:right"><span class="text title_bar_top">
Primary Insurance &nbsp;:&nbsp;</span><span><?php echo $primary_insurance?></span>
<br><span class="text title_bar_top">Secondary Insurance &nbsp;:&nbsp;</span><span><?php echo $secondary_insurance?>
</span></div>
<input type="hidden" name="self_pay" id="self_pay" value="<?php echo $stop_stmt?>" />
<input type="hidden" name="primary_ins" id="primary_ins" value="<?php echo $primary_insurance?>" />
<input type="hidden" name="pid" id="pid" value="<?php echo $pid?>" />
<input type="hidden" name="secondary_ins" id="secondary_ins" value="<?php echo $secondary_insurance?>" />

<input type="hidden" name="demo_DOB" id="demo_DOB" value="<?php echo $demo_DOB?>" />
<input type="hidden" name="demo_address" id="demo_address" value="<?php echo $demo_address?>" />
<input type="hidden" name="ip_dp" id="ip_dp" value="<?php echo $ip_dp?>" />
<?php /*?><input type="hidden" name="demo_ss" id="demo_ss" value="<?php echo $demo_ss?>" />
<?php */?>
<br> <br>
<!-- start -->
<?php
// Added By Gangeya to deactivate encounter screen for Inactive patients



function isPatientActive($pid) {
  $row = sqlQuery("SELECT count(*) AS count FROM patient_data WHERE " .
    "pid = '$pid' and pstatus = 'NO' ");


  $count = $row['count'];
 
  return $count ? true : false;
}

$isActive = isPatientActive($pid);
$isBilled = isEncounterBilled($pid, $encounter);

$disabled = '';
$disabled1 = '';

if($isActive)
{
	echo "<p><font color='red'>Patient status is In-Active</font></p>\n";
	$disabled = 'disabled';
	$disabled1 = 'disabled';
}

if ($isBilled) {
  echo "<p><font color='green'>This encounter has been billed. If you " .
    "need to change it, it must be re-opened.</font></p>\n";
	
  $disabled = 'disabled';
  $disabled1 = 'disabled';
}

//else { // the encounter is not yet billed
?>
<table width='100%' border="0" >
<tr>
<td width="25%" class='bold'><?php xl('Facility','e'); ?></td>
<?php
 $sensitivities = acl_get_sensitivities();
 if ($sensitivities && count($sensitivities)) {
  usort($sensitivities, "sensitivity_compare");
?>
     <?php } ?>
     <td class='bold' width="25%"><?php xl('Claim Status','e'); ?></td><td class='bold' nowrap><?php xl('Date of Service','e'); ?></td>

<td class='bold'><?php xl('Billing Facility','e');?> 
</td>

</tr>

<?php  $visitCategory = sqlQuery("SELECT count(encounter) as count from form_encounter where pid = '$pid'");
 $pccat_count = $visitCategory['count'];
 if(!$viewmode){
	 if($pccat_count==0)
	 $cat_id = 10;
	 else 
	 $cat_id =9;
 }
 else{
 if($pccat_count==1)
	 $cat_id = 10;
	 else 
	 $cat_id =9;
 }
 echo "<input type='hidden' name='pc_catid' id='pc_catid' value='$cat_id' />";
 
?>
<tr><?php /*?><td class='text'  width="15%">
      <select name='pc_catid' id='pc_catid' disabled>
	<!--<option value='_blank'>-- Select One --</option>-->
<?php
 $cres = sqlStatement("SELECT pc_catid, pc_catname " .
  "FROM openemr_postcalendar_categories where pc_catid=$cat_id ORDER BY pc_catname");
 while ($crow = sqlFetchArray($cres)) {
  $catid = $crow['pc_catid'];
  if ($catid < 9 && $catid != 5) continue;
  echo "       <option value='$catid'";
  if ($viewmode && $crow['pc_catid'] == $result['pc_catid']) echo " selected";
  echo ">" . xl_appt_category($crow['pc_catname']) . "</option>\n";
 }
?>
      </select>
      <input type="hidden" id="pc_catid" name="pc_catid" value="<?php echo $cat_id ?>">
     </td><?php */?>
     <td class='text'  width="35%">

      <!--  Modified By Sonali Dhumal --> 
      <?php

if ($viewmode) {
$dres = sqlStatement("select form_encounter.device_type,facility.id,facility.name FROM facility, form_encounter where facility.id=form_encounter.facility_id and form_encounter.encounter='$encounter'");
  $drow = sqlFetchArray($dres);
   $def_facility = $drow['id'];
  $facility_name = $drow['name'];
  $device_type = $drow['device_type'];
 // echo $device_type;
  }
  
?>
   <?php /*?><input type="hidden" id="hidden_ajax_close_value" value="<?php echo htmlspecialchars($def_facility);?>" /><input name='facility_code'  id='facility_code' class="text"  style="width:150px"   onKeyDown="PreventIt(event)"  value="<?php echo $facility_name;?>"  autocomplete="off"   />
   
   <div id='ajax_div_insurance_section'>
		  <div id='ajax_div_insurance_error'>
		  </div>
		  <div id="ajax_div_facility" style="display:none;"></div>
		  </div>
		 </div><?php */?>  
         
         <select name="facility_code" id="facility_code" <?php if ($isBilled || $isActive) echo " disabled"; ?> onChange="default_facility();">
        
         <?php 
		 if($def_facility ==''){
		 	//if($def_facility_def == '')
		 		//$def_facility= $user_facility;
			//else
				$def_facility= $def_facility_def;
				//$def_facility= 0;
		}
			
		 // Added by Gangeya : Bug ID 8302
		 if($viewmode)
		 {
		 	$dres = sqlStatement("select id,name, state, defaultBillingFacility FROM facility where service_location=1 order by name asc");
		 }
		 else
		 {
		 	$dres = sqlStatement("select id,name, state, defaultBillingFacility FROM facility where service_location=1 and active = 1 order by name asc");
		 }
		 // Modified by Sonali. 8845: Application should display default facility and provider when there is only one value in dropdown selection.
		 $numFacility =  sqlNumRows($dres); 
		 if($numFacility==1)
		 {
		 	$drow = sqlFetchArray($dres);
		 	echo "<option value='$drow[id]' 'selected'>$drow[name], $drow[state]</option>";
		 }
		 else
		 {		 
		 ?>
              <option value="0">- Select Facility - </option>
             <?php
        		while($drow = sqlFetchArray($dres)){
				
           			$selected='';
             		if($viewmode || $new_enc_mode==1 || $def_facility!='')
              		{
					
						if($prev_enc_facility == ''){
							if( $def_facility == $drow[id] )
								$selected ="selected"; 
						}
						else{
						 
							if( $prev_enc_facility == $drow[id] )
								$selected ="selected"; 
						}
              		}	
					// code added for previous encounter facility BUG 10173
					if($def_facility ==''){
					
						if($prev_enc_facility !=''){
						
							if( $prev_enc_facility == $drow[id])
								$selected ="selected"; 
						}
						
					}
							
             		echo "<option value='$drow[id]' $selected>$drow[name], $drow[state]</option>"; 
              	}	
         } 
		 ?>
         </select>
                  
              <input type="hidden" id="facility_id" name="facility_id" value="<?php echo $def_facility;?>" >
              <input type="hidden" id="facility_id_pat" name="facility_id_pat" value="<?php echo $def_facility_def;?>" >
              <!--<input type="hidden" id="facility_id" name="facility_id" value="<?php echo $def_facility;?>" >-->
              
          
              

      </select> 

     </td>
     <?php 
	 $qsql = sqlStatement("SELECT id, name FROM facility WHERE billing_location = 1  AND id = (SELECT defaultBillingFacility FROM facility WHERE id = '$def_facility')");
	 while ($facrow = sqlFetchArray($qsql)) {
				$billing_facility = $facrow['id'] ;
				}
				if($viewmode) $billing_facility = $result['billing_facility'];
				echo "<input type='hidden' name='billing_facility' id='billing_facility' value='$billing_facility' />";
	 
	 ?>

        <?php  if ($sensitivities && count($sensitivities)) { 
		 foreach ($sensitivities as $value) {
		 $sensitivity = $value[1];
		 }
		 if ($viewmode && $result['sensitivity'])
		 $sensitivity = $result['sensitivity'];
		 echo "<input type='hidden' name='form_sensitivity' id='form_sensitivity' value='$sensitivity' />";
		 }
		?>
      
     <td class="text">
  <?php
  if ($encounter)
  {
      $St=CheckEncounterStatus($encounter); 
  }
	//Updated By Gangeya for BUG ID 11240
	if($stop_stmt == 'self_pay'){
		$qsql = sqlStatement("SELECT id, status,iphone_status FROM claim_status where id in (1, 10, 11, 12, 15, 16, 17)");
	}
	else{
		$qsql = sqlStatement("SELECT id, status,iphone_status FROM claim_status");
	}
	
	$selected1='';	
?>
<select name="Encounter_Status" id="Encounter_Status" <?php if ($isBilled ||  $isActive) echo " disabled"; ?>			 > 
<?php
	while ($statusrow = sqlFetchArray($qsql)) {
		$claim_status = $statusrow['status'];
		$claim_status_id = $statusrow['id'];
		
		// code added by pawan on 19-01-2017
		if(isset($device_type) && $device_type =='OE') {
			if($statusrow['iphone_status']== "true" || $statusrow['id'] == 20){
				$disabled1 = "disabled";
			}
			else{
				 $disabled1 = "";
			}
		}
		
		//Updated By Gangeya for BUG ID 11240
		if(!$viewmode) {
			if($stop_stmt == 'self_pay'){
				if($claim_status=="Ready to send patient")
					$selected1 = "selected";
				else
					$selected1="";
			}
			else{
				if($claim_status=="Ready to send primary")
					$selected1 = "selected";
				else
					$selected1="";
			}
		}
		else { 
			if($St['Status'] == $claim_status)
				$selected1 = "selected";
			else
				$selected1="";
		}		
		echo "<option value='$claim_status_id' $selected1 $disabled1>$claim_status</option>";
	} 
	
	
?>


<?php /*?><option value="Unbilled Demo Pending" <?php if($St['Status']=='Unbilled Demo Pending') echo "selected='selected'; ";?>  >Unbilled Demo Pending</option>
    <option value="Unbilled Insurance Pending" <?php if($St['Status']=='Unbilled Insurance Pending') echo "selected='selected'; ";?> >Unbilled Insurance Pending</option>
  <option value="Unbilled rejected" <?php if($St['Status']=='Unbilled rejected') echo "selected='selected'; ";?>>Unbilled rejected</option>
  <option value="Ready to send primary" <?php if(!$viewmode) echo "selected";?> <?php if($St['Status']=='Ready to send primary') echo "selected='selected'; ";?> >Ready to send primary</option>
  <option value="Billed to primary" <?php if($St['Status']=='Billed to primary') echo ' selected="selected"'; ?> >Billed to primary</option>
  <option value="Ready to send secondary" <?php if($St['Status']=='Ready to send secondary') echo "selected='selected'; ";?> >Ready to send secondary</option>
  <option value="Billed to secondary" <?php if($St['Status']=='Billed to secondary') echo "selected='selected'; ";?> >Billed to secondary </option>
  <option value="Ready to send tertiary" <?php if($St['Status']=='Ready to send tertiary') echo "selected='selected'; ";?> >Ready to send tertiary</option>
  <option value="Billed to tertiary" <?php if($St['Status']=='Billed to tertiary') echo "selected='selected'; ";?> >Billed to tertiary</option>
  <option value="Ready to send patient" <?php if($St['Status']=='Ready to send patient') echo "selected='selected'; ";?> >Ready to send patient</option>
  <option value="Billed to patient" <?php if($St['Status']=='Billed to patient') echo "selected='selected'; ";?> > Billed to patient</option>
  <option value="Claim settled" <?php if($St['Status']=='Claim settled') echo "selected='selected'; ";?>>Claim settled</option><?php */?>
  
</select>


</td>
<td class='text' nowrap>
<?php /*?><?php echo date("m/d/Y"); ?> this is added by sangram for getting server date
<?php */?> 

<?php
date_default_timezone_set('America/New_York');
$view_dos=date('m/d/Y');
if($dos){
$newDate = date("m/d/Y", strtotime($dos));
$view_dos=$newDate;
$DOS_year = date('Y', strtotime($dos));
}
?>

     <input type='text' size='10' name='form_date' id='form_date' <?php echo $disabled ?>
       value='<?php echo $view_dos; ?>'
       title='<?php xl('mm/dd/yyyy Date of service','e'); ?>'
       onkeyup='datekeyup(this)' onBlur="dateblur(this,mypcc,'<?php echo date("m/d/Y"); ?>')"  <?php if ($isBilled ||  $isActive) echo " disabled"; ?> />
        <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
        id='img_form_date' border='0' alt='[?]' style='cursor:pointer;cursor:hand'
        title='<?php xl('Click here to choose a date','e'); ?>'>
        &nbsp;&nbsp;
        
		<?php 
		//added By Gangeya : PQRS BUG
		if($viewmode || $new_enc_type==1)
		{
			if($DOS_year <= 2016)
			{
		?>
        <a href="../../forms/newpatient/pqrs_popup.php" class='iframe small_modal' onclick='top.restoreSession()' style="font-weight:bold; background-color:#33CCFF; padding:3px">
		<?php echo htmlspecialchars(xl('PQRS'),ENT_NOQUOTES); ?></a>
			<?php } 
				if($DOS_year >= 2017)
			{
		
		?>
		<!-- Added By kiran	dynamic PQRS as per the client account-->
        <a href="../../forms/newpatient/pqrs_popup.php" class='iframe small_modal' onclick='top.restoreSession()' style="font-weight:bold; background-color:#33CCFF; padding:3px">
		<?php echo htmlspecialchars(xl('PQRS '.$DOS_year),ENT_NOQUOTES); ?></a>
        <?php 
		}
		}
		?>
     </td>
	 
	 <td>
		 <select name="billing_facility" id="billing_facility" <?php if ($isBilled ||  $isActive) echo " disabled"; ?>>
	<?php
		
		$prow = sqlStatement("select id, name, state from facility where billing_location = 1");
		
		$fresult = array();
					
		for ($iter = 0; $frow = sqlFetchArray($prow); $iter++)
			$fresult[$iter] = $frow;
			
		foreach($fresult as $iter) {     
			$billingFacility = $iter['name'];
			$billingFacilityState = $iter['state'];
			$selected='';

			if($def_bfacility == '' || !isset($def_bfacility)){
				if( $iter['id'] == $billing_facility)
					$selected ="selected"; 
					echo "<option value=".$iter['id']." $selected>$billingFacility, $billingFacilityState</option>";

			}
			else{
				if( $iter['id'] == $def_bfacility)
					$selected ="selected";
					echo "<option value=".$iter['id']." $selected>$billingFacility, $billingFacilityState</option>";

			}
		}
		
	?>
	</select>

	 </td>    
     </tr>
     <input type="hidden" name="form_onset_date" id="form_onset_date" value="" />
<?php /*?><tr height="15px"><td colspan="4"></td></tr>
<tr>  
 <td<?php if (!$GLOBALS['gbl_visit_referral_source']) echo " style='visibility:hidden;'"; ?> class='bold' width="25%">
     <?php xl('Referral Source','e'); ?></td>  
     <td ></td> 
</tr>
<tr> 

     <td<?php if (!$GLOBALS['gbl_visit_referral_source']) echo " style='visibility:hidden;'"; ?> class='text'>
<?php
  echo generate_select_list('form_referral_source', 'refsource', $viewmode ? $result['referral_source'] : '', '');
?>
     </td>
     <td></td>

</tr><?php */?>

<tr><td class='bold'><?php xl('Rendering Provider','e'); ?></td><td class='bold'><?php xl('Referring provider','e'); ?></td><td class='bold'><?php xl('Supervising provider','e'); ?></td><td class='bold'><?php xl('Servicing provider','e'); ?></td>
<?php /*?><td class='bold'><?php xl('Batch#','e'); ?></td><td class='bold'><?php xl('Price level','e'); ?></td><?php */?></tr><tr>
<?php 
if($encounter_provid=='')
$encounter_provid = $encounter_provid_def;
if($encounter_supid=='')
$encounter_supid = $encounter_supid_def;
if($encounter_refid=='')
$encounter_refid = $encounter_refid_def;
if($encounter_serviceid=='')
$encounter_serviceid = $encounter_serviceid_def;

//Updated by Gangeya to include inactive Providers in Edit mode.
//JIRA ID: PAYEHR - 48
echo "<td valign='top'>";
$isServicingPro = 0;
genProviderSelect('RenderingProvider', '-- Please Select --', $encounter_provid, $isBilled,$isActive,'erxrenderingProvider', $isServicingPro, $viewmode);
echo "  </td>"; 
 
//Updated by Gangeya to include inactive Providers in Edit mode.
//JIRA ID: PAYEHR - 48
echo "<td valign='top'>";
$isServicingPro = 0;
genProviderSelect('ReferringProvider', '-- Please Select --', $encounter_refid, $isBilled,$isActive,'erxreferringProvider', $isServicingPro, $viewmode);
  echo "  </td>"; 

  //Updated by Gangeya to include inactive Providers in Edit mode.
//JIRA ID: PAYEHR - 48
echo "<td valign='top'>";
$isServicingPro = 0;
genProviderSelect('SupervisingProvider', '-- Please Select --', $encounter_supid, $isBilled,$isActive,'erxsupervisingProvider', $isServicingPro, $viewmode);
  echo "  </td>"; 

  //Updated by Gangeya to include inactive Providers in Edit mode.
//JIRA ID: PAYEHR - 48
echo "<td valign='top'>";
$isServicingPro = 1;
genProviderSelect('ServicingProvider', '-- Please Select --', $encounter_serviceid, $isBilled,$isActive,'erxrenderingProvider', $isServicingPro, $viewmode);
  echo "  </td>"; 
  
/*  echo "<td >";
if (!$GLOBALS['ippf_specific']) {  
  genProviderSelect('SupervisorID', '-- N/A --', $encounter_supid, $isBilled);
}
// If applicable, ask for the contraceptive services start date.
$trow = sqlQuery("SELECT count(*) AS count FROM layout_options WHERE " .
  "form_id = 'DEM' AND field_id = 'contrastart' AND uor > 0");
if ($trow['count'] && $contraception && !$isBilled) {
  $date1 = substr($visit_row['date'], 0, 10);
  // If admission or surgical, then force contrastart.
  if ($contraception > 1 ||
    strpos(strtolower($visit_row['pc_catname']), 'admission') !== false)
  {
    echo "   <input type='hidden' name='contrastart' value='$date1' />\n";
  }
  else {
    // echo "<!-- contraception = $contraception -->\n"; // debugging
    $trow = sqlQuery("SELECT contrastart " .
      "FROM patient_data WHERE " .
      "pid = '$pid' LIMIT 1");
    if (empty($trow['contrastart']) || substr($trow['contrastart'], 0, 4) == '0000') {
      $date0 = date('Y-m-d', strtotime($date1) - (60 * 60 * 24));
      echo "   <select name='contrastart'>\n";
      echo "    <option value='$date1'>" . xl('This visit begins new contraceptive use') . "</option>\n";
      echo "    <option value='$date0'>" . xl('Contraceptive services previously started') . "</option>\n";
      echo "    <option value=''>" . xl('None of the above') . "</option>\n";
      echo "   </select>\n";
      echo "&nbsp; &nbsp; &nbsp;";
    }
  }
}

// If there is a choice of warehouses, allow override of user default.
if ($prod_lino > 0) { // if any products are in this form
  $trow = sqlQuery("SELECT count(*) AS count FROM list_options WHERE list_id = 'warehouse'");
  if ($trow['count'] > 1) {
    $trow = sqlQuery("SELECT default_warehouse FROM users WHERE username = '" .
      $_SESSION['authUser'] . "'");
    echo "   <span class='billcell'><b>" . xl('Warehouse') . ":</b></span>\n";
    echo generate_select_list('default_warehouse', 'warehouse',
      $trow['default_warehouse'], '');
    echo "&nbsp; &nbsp; &nbsp;\n";
  }
}
echo "</td>";*/
//echo "<input type='hidden' name='SupervisorID' id='SupervisorID' value='' />";
?>
<?php /*?><td>
<?php 

$plres = sqlStatement("SELECT option_id, title FROM list_options " .
  "WHERE list_id = 'pricelevel' ORDER BY seq");
if (true) {
  $trow = sqlQuery("SELECT pricelevel FROM patient_data WHERE " .
    "pid = '$pid' LIMIT 1");
  $pricelevel = $trow['pricelevel'];
   echo "   <select name='pricelevel'";
  if ($isBilled ||  $isActive) echo " disabled";
  echo ">\n";
  while ($plrow = sqlFetchArray($plres)) {
    $key = $plrow['option_id'];
    $val = $plrow['title'];
    echo "    <option value='$key'";
    if ($key == $pricelevel) echo ' selected';
    echo ">$val</option>\n";
  }
  echo "   </select>\n"; 
  echo "<input type='hidden' name='pricelevel' id='pricelevel' value='$pricelevel' >";
}


?>
</td><?php */?>
	

<?php /*?> <td>
  <input type="text" name="batch_id" id="batch_id" value="<?php echo $batch_id?>" <?php if ($isBilled) echo " disabled"; ?>>
 </td><?php */?>
</tr>
<?php
$copaychange=0;

if ($viewmode || $new_enc_mode==1) { 
$icd9_first =array();
$cpt4_first =array();
$copay_first =array();
$copaychange=1;


for($i=0;$i<count($billresult);$i++){
if($billresult[$i]['code_type']=="COPAY"){
$copay_value=$billresult[$i]['code'];
$copay_first=$billresult[$i]['id'];
$copay_amt +=$billresult[$i]['fee'];
}
if($pid && $encounter){
$patcopay = getPatientCopay($pid, $encounter);
//$patsession = getPatientSession($pid,$encounter);
$patcopay = $copay_paid;
}
if($draft_id && $billresult[$i]['code_type']=='COPAY')
{
 
$patcopay = $billresult[$i]['fee'];
$form_source= $billresult[$i]['ndc_info'];
}
if($patcopay==0)
$patcopay='';


if(($billresult[$i]['code_type']=="ICD9")||($billresult[$i]['code_type']=="ICD10"))
{
$icd9_first['code'][]=$billresult[$i]['code'];
$icd9_first['id'][]=$billresult[$i]['id'];


}
if($billresult[$i]['code_type']=="CPT4")
{

$cpt4_first['id'][]=$billresult[$i]['id'];
$cpt4_first['code'][]=$billresult[$i]['code'];
$cpt4_first['tos'][]=$billresult[$i]['tos'];

if($billresult[$i]['from_dos']=='0000-00-00' || $billresult[$i]['from_dos']=='')
$cpt4_first['from_dos'][]=$view_dos;
else
$cpt4_first['from_dos'][] = date("m/d/Y", strtotime($billresult[$i]['from_dos']));

if($billresult[$i]['to_dos']=='0000-00-00' || $billresult[$i]['to_dos']=='')
$cpt4_first['to_dos'][]=$view_dos;
else
$cpt4_first['to_dos'][] = date("m/d/Y", strtotime($billresult[$i]['to_dos']));

// code added for cpt eaa amount
if($billresult[$i]['eaa_amount']=='0.00'){
	$cpt4_first['eaa_amount'][]='';

}else
{
	$cpt4_first['eaa_amount'][]=$billresult[$i]['eaa_amount'];

}

if($billresult[$i]['fee']=='0.00'){
$cpt4_first['units'][]='';
$cpt4_first['fee'][]='';
}else
{
$cpt4_first['units'][]=$billresult[$i]['units'];
$cpt4_first['fee'][]=$billresult[$i]['fee'];
}
$cpt4_first['modifier'][]=$billresult[$i]['modifier'];
$cpt4_first['justify'][]=$billresult[$i]['justify'];
}
}
}
?>
<tr>
<td class='bold'><?php xl('Primary Care Provider','e'); ?></td>
<td class='bold'><?php xl('Place of service','e'); ?></td>
<td class='bold'><?php xl('Batch#','e'); ?> </td>
</tr>
<tr>
<?php   
if($encounter_pcp=='')
$encounter_pcp = $encounter_pcp_def;

//Updated by Gangeya to include inactive Providers in Edit mode.
//JIRA ID: PAYEHR - 48
echo "<td valign='top'>";
genProviderSelect('PrimaryCareProvider', '-- Please Select --', $encounter_pcp, $isBilled,$isActive,'erxprimarycareProvider', $isServicingPro, $viewmode);
  echo "  </td>"; ?>
<td>
<select name="pos" id="pos" <?php if ($isBilled ||  $isActive) echo " disabled"; ?>>
<?php
	$prow = sqlStatement("SELECT id,pos_id,pos_name FROM pos_list order by pos_name");
	
	$fresult = array();
	//Added By Gangeya : 02/22/2017
	//update some condition for default POS if there is no default POS saved against encounter.
	
	if(!$viewmode){
		if($def_pos_id == '0' || $def_pos_id == '') 
			$pos_id = '24';	
		else
			$pos_id = $def_pos_id;
	}
	else if($viewmode && $pos_id == '0'){
		$pos_id = '24';
	}
		
	for ($iter = 0; $frow = sqlFetchArray($prow); $iter++)
    	$fresult[$iter] = $frow;
		
	foreach($fresult as $iter) {     
		$pos_name = $iter['pos_id']." - ".$iter['pos_name'];
		$selected='';

		if($iter['id']==$pos_id) $selected = "selected";
			echo "<option value=".$iter['id']." $selected>$pos_name</option>";
	}
	
?>
</select>

</td> 
<td>

<input type="text" name="batch_id" id="batch_id" value="<?php echo $batch_id?>" <?php if ($isBilled ||  $isActive) echo " disabled"; ?>></td>
</tr>
<tr>
<td class='bold'><?php xl('Fee Schedules','e');?> 

<select name="feeschedule" id="feeschedule" <?php if ($isBilled ||  $isActive) echo " disabled"; ?>>
<?php
	if($primary_insID == '' || !isset($primary_insID))
		$primary_insID = 0;
	
	$qry = sqlStatement("select fm.feeScheduleID from feeschedule fs join feeschedulemapping fm on fm.feeScheduleID = fs.feeScheduleID where fs.status = 1 and fm.payorID = $primary_insID");
	$row1=sqlFetchArray($qry);
	if(!empty($row1['feeScheduleID']) || isset($row1['feeScheduleID']))
		$selctedFeeSchedule = $row1['feeScheduleID'];
	else
		$selctedFeeSchedule = 1;
	
	$prow = sqlStatement("select feeScheduleID, feeScheduleName from feeschedule where status = 1 order by feeScheduleName");
	
	$fresult = array();
	
	//if(!$viewmode){
	//	if($def_pos_id == '0' || $def_pos_id == '') 
	//		$pos_id = '24';	
	//	else
	//		$pos_id = $def_pos_id;
	//}
	//else if($viewmode && $pos_id == '0'){
	//	$pos_id = '24';
	//}
		
	for ($iter = 0; $frow = sqlFetchArray($prow); $iter++)
    	$fresult[$iter] = $frow;
		
	foreach($fresult as $iter) {     
		$feeScheduleName = $iter['feeScheduleName'];
		$selected='';

		if($iter['feeScheduleID']==$selctedFeeSchedule) 
			$selected = "selected";
			echo "<option value=".$iter['feeScheduleID']." $selected>$feeScheduleName</option>";
	}
	
?>
</select>

</td>
</tr>

<tr>
<td><table border="0" ><th class='bold'>Payment Method</th><th class="bold">Check/Ref Number</th><th class='bold'><?php xl('Copay','e'); ?></th>
<tr>
 <td>
<select name="form_method" id="form_method"  class="text" onChange='CopayChange(<?php echo $copaychange; ?>);AddPaymentvalues(this.value,<?php echo $pid; ?>);' <?php echo $disabled; ?> >
<option value=0>-Select Payment method-</option>
  <?php
  $query1112 = "SELECT * FROM list_options where list_id=?  ORDER BY seq, title ";
  $bres1112 = sqlStatement($query1112,array('payment_method'));
  while ($brow1112 = sqlFetchArray($bres1112)) 
   {
  	if($brow1112['option_id']=='electronic' || $brow1112['option_id']=='bank_draft')
	 continue;
	 if($brow1112['option_id'] == $form_method)
	 $selected = "selected";
	 else
	 $selected ='';
	echo "<option value='".htmlspecialchars($brow1112['option_id'], ENT_QUOTES)."' $selected>".htmlspecialchars(xl_list_label($brow1112['title']), ENT_QUOTES)."</option>";
   }
  ?>
  </select>
</td><td>
 <?php if($form_method != 'check_payment') $disabled1 = 'disabled = "" '; 
 else $disabled1 = '$form_method';  
 ?>
 
<?php /*?><select name="check_number" id="check_number"  class="text" onChange='CopayChange(<?php echo $copaychange; ?>);calculate_check_balance(this.value)' <?php echo $disabled1; ?> <?php echo $disabled; ?>  ><?php */?>
<select name="check_number" id="check_number"  class="text" onChange='CopayChange(<?php echo $copaychange; ?>);calculate_check_balance(this.value)' >

<?php
 
$qry_check_number = "select session_id,reference,check_date,pay_total from ar_session where patient_id=? and pay_total>0 and payment_type='patient' 
and payment_method=? ";
 $chk_num_res = sqlStatement($qry_check_number,array($pid,$form_method));
echo " <option value=0>-Select -</option>";
while ($chk_num_row = sqlFetchArray($chk_num_res)) 
   {
   
    if($chk_num_row['reference'] == $form_source)
	 $selected = "selected";
	 else
	 $selected =''; 
	 if($form_method=="check_payment"){
	 $chk_num = htmlspecialchars($chk_num_row['session_id'], ENT_QUOTES)."@".htmlspecialchars($chk_num_row['reference'], ENT_QUOTES);
	 echo "<option value='".$chk_num."' $selected>".htmlspecialchars(xl_list_label($chk_num_row['reference']."[".$chk_num_row['check_date']."]"), ENT_QUOTES)."</option>";  
	 }
	 else{
	  $chk_num = htmlspecialchars($chk_num_row['session_id'], ENT_QUOTES)."@".htmlspecialchars($chk_num_row['pay_total'], ENT_QUOTES);
	  echo "<option value='".$chk_num."' $selected>".htmlspecialchars(xl_list_label($chk_num_row['pay_total']."[".$chk_num_row['check_date']."]"), ENT_QUOTES)."</option>";  
	  }
	  }
/*$qry_check_number = "select session_id,reference,check_date from ar_session where patient_id=? and pay_total>0 and payment_type='patient' 
and payment_method='check_payment' ";
 $chk_num_res = sqlStatement($qry_check_number,array($pid));
  while ($chk_num_row = sqlFetchArray($chk_num_res)) 
   {
   
   if($chk_num_row['reference'] == $form_source)
	 $selected = "selected";
	 else
	 $selected ='';
	 $chk_num = htmlspecialchars($chk_num_row['session_id'], ENT_QUOTES)."@".htmlspecialchars($chk_num_row['reference'], ENT_QUOTES);
   echo "<option value='".$chk_num."' $selected>".htmlspecialchars(xl_list_label($chk_num_row['reference']."[".$chk_num_row['check_date']."]"), ENT_QUOTES)."</option>";  
   }*/
?>
</select>
</td>

<?php 

echo "<input type='hidden' id='bill[11][code_type]' name='bill[11][code_type]' value='COPAY' /><input type='hidden' name='bill[11][id]' id='bill[11][id]' value='$session_id' />";

echo "<td class='bold'><input type='text' name='bill[11][price]' id='bill[11][price]' value='$patcopay' size='4' maxlength='4' onchange='CopayChange($copaychange),validateAmount(this.value)' $disabled ><input type='hidden' name='bill[11][code]' id='bill[11][code]' value='$patcopay' size='8' ></td><td><b><div id='check_balance'></div></b></td>";

 ?>
</tr>
</table>
</td>
</tr>
<tr>
<td class='bold'><?php xl('Diagnosis Code ','e'); ?> 

<?php /*?>Temporary commented below control to avoid icd10 entry
<?php if ($isBilled ||  $isActive) echo " disabled"; ?> 
<?php */?>

<?php

// code added for ICD-10 Code activation by pawan
$qry_icd10 = "select * from globals where gl_name='enable_icd10' ";
$icd10_res = sqlStatement($qry_icd10);
$icd10_row = sqlFetchArray($icd10_res);

$icd10_active = $icd10_row['gl_value'];
//echo "pawan==".$icd10_active;
if(empty($icd10_active))
{
?>
<select name="icd_code_type" id="icd_code_type" onChange="display_icd_box(this.value)"  >
<?php
}else{
//Added onFocus function by Gangeya for BUG ID 10922
?>
<select name="icd_code_type" id="icd_code_type" onChange="display_icd_box(this.value)"  onFocus="isDisable()">
<?php
}
?>

<?php


if($encounter || $draft_id)
{
	if($encounter)
	{
		$dres = sqlStatement("select count(*) as cnt from billing where encounter=$encounter and pid=$pid and activity=1 and code_type='ICD10'");
  		$drow = sqlFetchArray($dres);
   		$code_type_check = $drow['cnt'];
  	}
   	if($draft_id)
	{
		$dres = sqlStatement("select count(*) as cnt from billing_draft where draft_id=$draft_id and pid=$pid and activity=1 and code_type='ICD10'");
  		$drow = sqlFetchArray($dres);
	}

	// code change for BUG 10524 more than 4Dx
   	if($drow['cnt']==0)
   	{
		$selected = "selected";
   		$icd_code_type='ICD9';
		$visibility_status='hidden';
		
		if($mode=="update"){
			echo "<option value='9'>ICD9</option>";
			
			echo "<option value='0' $selected>ICD10</option>";
		}
	}
	else{
	
		$selected = "selected";
	  	$visibility_status='visible';
	  	$icd_code_type ='ICD10';
		
		if($mode=="update"){
			
			echo "<option value='0' $selected>ICD10</option>";
		}
		else{
			echo "<option value='9'>ICD9</option>";
			echo "<option value='0' $selected>ICD10</option>";
		}
		
	}

}
else
{
 	$visibility_status='hidden';
  	$icd_code_type = 'ICD9';
 	
?>
	<option value='9'>ICD9</option>";
	<option value='0' selected >ICD10</option>";
<?php 
} 
?>
</select>


&nbsp;&nbsp; &nbsp;&nbsp;
</td>
<td class='bold'>
<a onClick="javascript:icd9_codes('ICD9')" href="#"> ICD9 List </a>
&nbsp;&nbsp;
<?php if($icd10_active == 1){ ?>
<a onClick="javascript:icd9_codes('ICD10')" href="#"> ICD10 List </a>
&nbsp;&nbsp;
<?php } ?>

<a onClick="javascript:icd9_codes('CPT4')" href="#"> CPT<sup>&reg;</sup> List </a>

</td>
</tr>
<tr>
<td colspan="3">

<table  border="0">
	<tr>
    	<th class='bold'><?php xl('DX1 - (1)','e'); ?></th>
        <th class="bold"><?php xl('DX2 - (2)','e'); ?></th>
        <th class='bold'><?php xl('DX3 - (3)','e'); ?></th>
        <th class='bold'><?php xl('DX4 - (4)','e'); ?></th>
        <th class='bold' id="dx5" style="visibility:<?php echo $visibility_status ?>"><?php xl('DX5 - (5)','e'); ?></th>
        <th class='bold' id="dx6" style="visibility: <?php echo $visibility_status ?>"><?php xl('DX6 - (6)','e'); ?></th>
        <th class='bold' id="dx7" style="visibility:<?php echo $visibility_status ?>"><?php xl('DX7 - (7)','e'); ?></th>
        <th class='bold' id="dx8" style="visibility:<?php echo $visibility_status ?>"><?php xl('DX8 - (8)','e'); ?></th>
        <th class='bold' id="dx9" style="visibility:<?php echo $visibility_status ?>"><?php xl('DX9 - (9)','e'); ?></th>
        <th class='bold' id="dx10" style="visibility:<?php echo $visibility_status ?>"><?php xl('DX10 - (X)','e'); ?></th>
        <th class='bold' id="dx11" style="visibility:<?php echo $visibility_status ?>"><?php xl('DX11 - (Y)','e'); ?></th>
        <th class='bold' id="dx12" style="visibility:<?php echo $visibility_status ?>"><?php xl('DX12 - (Z)','e'); ?></th> 
    </tr>

<?php  

for($cnt=1; $cnt<=12;$cnt++){ 
	$billid='';
	$icnt = $cnt-1;
	$billid=$icd9_first['id'][$icnt];
	$icdcode=$icd9_first['code'][$icnt];




	if($cnt==10){
		$dx='X';
	}
	elseif($cnt==11){
		$dx='Y';
	}elseif($cnt==12){
		$dx='Z';
	}
	else{
		$dx=$cnt;
	}

	$code_type='0';


	if ($cnt >=5)
	{

		echo "<td>
		<input type='hidden' name='bill[$dx][id]' id='bill[$dx][id]' value='$billid' />
		<input type='text' name='bill[$dx][code]' id='bill[$dx][code]' value='$icdcode' onChange='cpt_code_popup(this,$pid,$code_type)' onfocusout='cpt_code_popup(this,$pid,$code_type)' size='8' style='visibility:$visibility_status;'  $disabled  style='text-transform: uppercase' >
		<input type='hidden' id='bill[$dx][code_type]' name='bill[$dx][code_type]' value=$icd_code_type /></td>";
	}
	else
	{
		echo "<td> 
		<input type='hidden' name='bill[$cnt][id]' id='bill[$cnt][id]' value='$billid' />
		<input type='text' name='bill[$cnt][code]' id='bill[$cnt][code]' value='$icdcode' onChange='cpt_code_popup(this,$pid,$code_type)' onfocusout='cpt_code_popup(this,$pid,$code_type)' size='8' $disabled style='text-transform: uppercase'  >
		<input type='hidden' id='bill[$cnt][code_type]' name='bill[$cnt][code_type]' value=$icd_code_type /></td>";
}
}
echo "</tr>";
?>
</table>

</td>
</tr>

<tr>
<table border="1" width="100%" style="background-color:#3399CC" id="cptinfo">
<tr class="bold">
<!--<th>From</th><th>To</th>--><th>CPT<sup>&reg;</sup></th><th>Mod1</th><th>Mod2</th><th>Mod3</th><th>Mod4</th><th>DX Pointer</th><th>TOS</th><th>Units</th><th>Units Charge</th><th>Total Charge</th><th>CPT<sup>&reg;</sup> EAA </th>
</tr>
<tbody id="bale_data">
<?php  
//print_r($cpt4_first);
//for($cnt=105; $cnt<=110;$cnt++){
if(count($cpt4_first['code'])>=1)
$cpt_arr_rows = 105 + count($cpt4_first['code']);
else
$cpt_arr_rows = $cpt_arr_rows + 1;

echo "<input type='hidden' id='cpt_arr_rows' value='$cpt_arr_rows'>";

for($cnt=105; $cnt < $cpt_arr_rows;$cnt++){
//for($cnt=105; $cnt<=110;$cnt++){
$ccnt = $cnt-105;
$cpt_code=''; $units='';$fee='';$total_charge='';$tos='';$justify='';$from_dos='';$to_dos='';$billid='';$key='';$jarr='';
$mod1='';$mod2='';$mod3='';$mod4='';
$just_arr = array();
$justify_arr = array();

if ($viewmode || $new_enc_mode==1) {
$total_charge='';
$billid=$cpt4_first['id'][$ccnt];
if($cpt4_first['from_dos'][$ccnt])
$from_dos=$cpt4_first['from_dos'][$ccnt];
else
$from_dos= $view_dos;

if($cpt4_first['to_dos'][$ccnt])
$to_dos=$cpt4_first['to_dos'][$ccnt];
else
$to_dos= $view_dos;

$cpt_code=$cpt4_first['code'][$ccnt];
$units=$cpt4_first['units'][$ccnt];
$fee=$cpt4_first['fee'][$ccnt];
// code added for CPT EAA
$cpt_eaa=$cpt4_first['eaa_amount'][$ccnt];

if( $units !='' || $fee!='')
$total_charge = $units * $fee;
$tos=$cpt4_first['tos'][$ccnt];
$modifier_arr=explode(":",$cpt4_first['modifier'][$ccnt]);
$mod1=$modifier_arr[0]; $mod2=$modifier_arr[1];$mod3=$modifier_arr[2];$mod4=$modifier_arr[3];

if(count($cpt4_first['justify'][$ccnt])>0)
$justify_arr=explode(":",$cpt4_first['justify'][$ccnt]);
if($justify_arr[0]){
	for($jarr=0;$jarr<count($justify_arr);$jarr++){
	$key = array_search($justify_arr[$jarr], $icd9_first['code']); 
	
	$just_arr[] = $key+1;
	}
}
foreach($just_arr as &$sValue)
{
     if ( $sValue=='10') $sValue='X' ;
	 if ( $sValue=='11') $sValue='Y' ;
	 if ( $sValue=='12') $sValue='Z' ;
}
$justify_arr[$cnt] = implode('',$just_arr);

}
$from_dos = $view_dos;
$to_dos = $view_dos;
echo "<input type='hidden' name='bill[$cnt][from_dos]' id='bill[$cnt][from_dos]' value='$from_dos' >";
	    echo "<input type='hidden' name='bill[$cnt][to_dos]' id='bill[$cnt][to_dos]'
       value='$to_dos' >";
	   
echocptline($cnt,$billid,$cpt_code,$pid,$disabled,$mod1,$mod2,$mod3,$mod4,$total_charge,$justify_arr,$tos,$units,$fee,$cpt_eaa);
 }
 
 
  ?>
</tbody>
</table>
</tr>
<tr>
<td>

<table>
<tr>
<?php
$res = sqlQuery("select * from users where username='".$_SESSION{"authUser"}."'");
?>
<td class='bold' width="50%">
<?php xl('Notes','e'); ?> 
<?php /*?><?php echo date("m/d/y", time()); ?> | <?php echo htmlspecialchars($res{"fname"}.' '.$res{"lname"},ENT_NOQUOTES); ?><?php */?>
<br />
<span style="font-weight:500">
<?php if($viewmode || $new_enc_mode==1){ echo $reason; }   ?>
</span>
</td>
</tr>
<tr><td>

<textarea name="reason" id="reason" class="width=100%" <?php echo $disabled; ?> >
<?php /*?><?php if($viewmode || $new_enc_type==1){ echo $result['reason']; }   ?><?php */?>
</textarea>
</td>
<td>



</td>
<td valign="top" align="left" width="160px">
<?php 
if($viewmode || $new_enc_mode==1)
{

 
?>

<label style="font-weight:bold; font-size:14px" >Diagnosis Description:</label>
</td>
<td  valign="top" align="left" ><?php echo $resultDiagnosis['Dx1'];?> <span >, </span><br /> 
<?php echo $resultDiagnosis['Dx2']; ?> <span >, </span><br />
<?php echo $resultDiagnosis['Dx3']; ?> <span >, </span><br />
<?php  echo $resultDiagnosis['Dx4'];  } ?>
</td>
</tr> </table>

</td>
</tr>
<tr><td colspan="2" align="center">
<?php 
if($viewmode)
{

 
?>
<input type='button' onClick="javascript:saveClicked('move');" value='<?php xl('Move to..','e');?>' style="width:150px"  <?php if ($isBilled ||  $isActive) echo " disabled"; ?> /> 
   <input type='text' size='10' id="move_to_patient"  name='move_to_patient' style='width:25%;cursor:pointer;cursor:hand' value='<?php echo htmlspecialchars($patientname, ENT_QUOTES); ?>' onclick='sel_patient()' title='<?php xl('Click here select patient','e'); ?>' readonly <?php if ($isBilled ||  $isActive) echo " disabled"; ?>  />
   <input type='hidden' name='move_to_patient_id' value='<?php echo $patientid ?>'  />
<?php } ?>
</td></tr>
<tr><td colspan="2" align="center">
<br>
<input type="hidden" name="mode" id="mode" value='' />
  <div style = 'float:left;'>
<?php /*?>     <a href="javascript:saveClicked();" class="css_button link_submit"><span><?php xl('Save','e'); ?></span></a>
<?php */?>  

 <input type='button'  onClick="javascript:saveClicked('draft');" value='<?php xl('Save as Draft','e');?>' style="width:150px; " <?php  if($viewmode==1) echo disabled ?>  />
  <input type='button'  onClick="javascript:saveClicked('review');" value='<?php xl('Save for Review','e');?>' style="width:150px;" <?php if($viewmode==1) echo disabled ?> />
  <input type='button' onClick="javascript:saveClicked('<?php echo $mode; ?>');" value='<?php xl('Approve','e');?>' style="width:150px" <?php echo $disabled ?> />
   <input type='button'  onClick="javascript:saveClicked('clarification');" value='<?php xl('Save for Clarification','e');?>' style="width:150px;" <?php if($viewmode==1) echo disabled ?> />
      <?php //if ($viewmode || !isset($_GET["autoloaded"]) || $_GET["autoloaded"] != "1") { ?>  
  
   <input type='button' onClick="javascript:cancelEncounter();" value='<?php xl('Cancel','e');?>' style="width:150px" />
   
  <?php //} // end not autoloading ?>
   <?php /*?> </div><?php */?>
 </div>
</td>

 </tr>

 <?php /*?> <tr > <td colspan="4"><input type="hidden" name="reason" id="reason" value="" /></td>
  </tr><?php */?>
  <tr>
  <td colspan="4" align="center">
  </td>
  </tr>
  </table>
  <br>
<div class="footer">
  <p align="center">
  CPT<sup>&reg;</sup> 2017 American Medical Association. All rights reserved.<br>
  CPT<sup>&reg;</sup> is a registered trademark of the American Medical Association
  </p>
</div>
<style>
.footer {
    position: relative;
   width: 50%;
    left: 0;
    bottom: 0;
   top:10;
    background-color: none;
    color: black;
    text-align: center;
}
</style>
<?php //} ?>
<!-- END    ------------- -->

<input type='hidden' name='ajax_mode' id='ajax_mode' value='' />
<input type="hidden" name="facility_cd" id="facility_cd" value="<?php echo htmlspecialchars($facility_cd);?>" />

<input type="hidden" name="drafted" id="drafted" value="<?php if($new_enc_mode==1) echo 1; else echo 0; ?>" />
<input type="hidden" name="draft_id" id="draft_id" value="<?php echo $draft_id?>" />


<?php if ($_GET['set_pid']) { 
$pid=$_GET['set_pid'];
$result = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
?>
<script language='JavaScript'>
 top.window.parent.left_nav.setPatient(<?php echo "'" . htmlspecialchars(($result['fname']) . " " . ($result['lname']),ENT_QUOTES) .
   "'," . htmlspecialchars($pid,ENT_QUOTES) . ",'" . htmlspecialchars(($result['pubpid']),ENT_QUOTES) .
   "','', ' " . htmlspecialchars(xl('DOB') . ": " . oeFormatShortDate($result['DOB_YMD']) . " " . xl('Age') . ": " . getPatientAge($result['DOB_YMD']), ENT_QUOTES) . "','" .addslashes($result['Alert_note']) ."'"; ?>);
EncounterDateArray=new Array;
CalendarCategoryArray=new Array;
EncounterIdArray=new Array;
Count=0;
 <?php
 //Encounter details are stored to javacript as array.
$result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe ".
	" left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? order by fe.date desc", array($pid));
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
 parent.left_nav.setRadio(window.name, 'dem');
</script>
<?php } ?>

</form>

</body>

<script language="javascript">


/* required for popup calendar */
Calendar.setup({inputField:"form_date", ifFormat:"%m/%d/%Y", button:"img_form_date"});
Calendar.setup({inputField:"form_onset_date", ifFormat:"%m/%d/%Y", button:"img_form_onset_date"});
<?php
if (!$viewmode) {
  $erow = sqlQuery("SELECT count(*) AS count " .
    "FROM form_encounter AS fe, forms AS f WHERE " .
    "fe.pid = '$pid' AND fe.date = '" . date('Y-m-d 00:00:00') . "' AND " .
    "f.formdir = 'newpatient' AND f.form_id = fe.id AND f.deleted = 0");
  if ($erow['count'] > 0 && $_GET['enc']!=1) {
 //   echo "alert('" . xl('Warning: A visit was already created for this patient today!') . "');\n";
  }
}
?>
</script>
<script language='JavaScript'>
<?php echo $justinit1; ?>
</script>

</html>
