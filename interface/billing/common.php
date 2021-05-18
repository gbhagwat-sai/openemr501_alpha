<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("$srcdir/options.inc.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");

print_r($_POST);

 $patientname = xl('Click here and select Patient');
$months = array("01","02","03","04","05","06","07","08","09","10","11","12");
$days = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14",
  "15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31");
$thisyear = date("Y");
$years = array($thisyear-1, $thisyear, $thisyear+1, $thisyear+2);

if($_REQUEST['new_enc_type']==1)
$new_enc_mode=1;
else
$new_enc_mode='';

$resultDiagnosis = sqlQuery("select * from diagnosis where encounter=$encounter");


//$pid = empty($_REQUEST['from_finder']) ? 0 : $_REQUEST['set_pid'];

if($new_enc_mode==1){
$draft_id=$_REQUEST['draft_id'];

$result = sqlQuery("SELECT * FROM form_encounter_draft WHERE id = '$draft_id'");
  $pid = $result['pid'];
  $def_facility = $result['facility_id'];
  $facility_name = $result['facility'];
  $pos_id = $result['pos_id'];
  $batch_id = $result['batch_id'];
  $claim_status_id = $result['claim_status_id'];
  $billresult =getDraftBillingByEncounter($pid, $draft_id, "*");
  $encounter_provid = 0 + $result['provider_id'];
  $encounter_supid  = 0 + $result['supervisor_id'];
}

if ($viewmode) {
  $id = $_REQUEST['id'];
  $result = sqlQuery("SELECT * FROM form_encounter WHERE id = '$id'");
  $encounter = $result['encounter'];
  $pid = $result['pid'];
  $def_facility = $result['facility_id'];
  $pos_id = $result['pos_id'];
  $batch_id = $result['batch_id'];
  $billresult = getBillingByEncounter($pid, $encounter, "*");
  
  $tmp = sqlQuery("SELECT provider_id, supervisor_id FROM form_encounter " .
  "WHERE pid = '$pid' AND encounter = '$encounter' " .
  "ORDER BY id DESC LIMIT 1");
$encounter_provid = 0 + $tmp['provider_id'];
$encounter_supid  = 0 + $tmp['supervisor_id'];

  
  if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
    echo "<body>\n<html>\n";
    echo "<p>" . xl('You are not authorized to see this encounter.') . "</p>\n";
    echo "</body>\n</html>\n";
    exit();
  }
}
else
{

 if($_GET['set_pid'])
    $pid=$_GET['set_pid'];
 elseif($_SESSION['pid'])
    $pid = $_SESSION['pid'];
	elseif($_GET['pid'])
	$pid=$_GET['pid']; 
}
// Sort comparison for sensitivities by their order attribute.
function sensitivity_compare($a, $b) {
  return ($a[2] < $b[2]) ? -1 : 1;
}

// Build a drop-down list of providers.  This includes users who
// have the word "provider" anywhere in their "additional info"
// field, so that we can define providers (for billing purposes)
// who do not appear in the calendar.
//
function genProviderSelect($selname, $toptext, $default=0, $disabled=false, $disabled=false) {
  $query = "SELECT id, lname, fname FROM users WHERE " .
    "( authorized = 1 OR info LIKE '%provider%' ) AND username != '' " .
    "AND active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
    "ORDER BY lname, fname";
  $res = sqlStatement($query);
  echo "   <select name='$selname'";
  if ($disabled) echo " disabled";
  echo ">\n";
  echo "    <option value=''>$toptext\n";
  while ($row = sqlFetchArray($res)) {
    $provid = $row['id'];
    echo "    <option value='$provid'";
    if ($provid == $default) echo " selected";
    echo ">" . $row['lname'] . ", " . $row['fname'] . "\n";
  }
  echo "   </select>\n";
}


//Modified By Sonali Dhumal
//Issue:Duplicate diagnosis code should be identified and  restricted
// get issues
/*$ires = sqlStatement("SELECT id, type, title, begdate,diagnosis FROM lists WHERE " .
  "pid = $pid AND enddate IS NULL " .
  "  group by title,diagnosis,id ORDER BY type, begdate");*/
  
  $res = sqlStatement("SELECT id ,name FROM facility where id ='$facility_cd'");
	$row = sqlFetchArray($res);
	$FacilityCode=$row['id'];
	$FacilityName=$row['name'];
	
	$ins_res = sqlStatement("select ind.type,inc.name from insurance_companies inc,insurance_data as ind where ind.provider=inc.id and ind.pid='$pid' order by ind.id");
	while($ins_row = sqlFetchArray($ins_res))	
	{
		if($ins_row['type'] == "primary")
			$primary_insurance = $ins_row['name'];
		if($ins_row['type'] == "secondary")
			$secondary_insurance = $ins_row['name'];
	}
	//echo "????????????".$pid;
	$demo_res = sqlStatement("select Concat(street,postal_code,state) as address,DOB,ss,fname,mname,lname,pstatus from patient_data where pid='$pid'");
	while($demo_row = sqlFetchArray($demo_res)){
		$demo_address = $demo_row['address'];
		$demo_DOB = $demo_row['DOB'];
		$pstatus = $demo_row['pstatus'];
		$patient_name = $demo_row['fname']." ".$demo_row['mname']." ".$demo_row['lname'];
	}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>


<?php html_header_show();?>
<title><?php xl('Patient Encounter','e'); ?></title>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.css" media="screen" />
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.4.3.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/overlib_mini.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
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
<!-- pop up calendar -->
<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
<style>
#ajax_div_facility {
	position: absolute;
	z-index:10;
	background-color: #FBFDD0;
	border: 1px solid #ccc;
	padding: 10px;
}
</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>
<?php include_once("{$GLOBALS['srcdir']}/ajax/facility_ajax_jav.inc.php"); ?>
<script language="JavaScript" type="text/javascript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
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



 // Process click on issue title.
 function newissue() {
  dlgopen('../../patient_file/summary/add_edit_issue.php', '_blank', 800, 600);
  return false;
 }

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

 function saveClicked(status) {
  var f = document.forms[0];   
  var flag= 0;
  var flag1= 0;
document.getElementById('mode').value=status;
   if(status=='draft' || status=='update' || status=='new'){
	if(document.getElementById('primary_ins').value=='')
	{
		alert("Patient does not have primary insurance");
		return false;
	}
	if(document.getElementById('patient_name').value=='')
	{
		alert("Please select Patient");
		return false;
	}
	if(document.getElementById('primary_ins').value!='')
	{
		if(document.getElementById('demo_DOB').value=='')
		{
			alert("Patient's DOB is missing");
			return false;
		}
		/*if(document.getElementById('demo_ss').value=='')
		{
			alert("Patient's SSN is missing");
			return false;
		}*/
		if(document.getElementById('demo_address').value=='')
		{
			alert("Patient's address is missing");
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
	
	
	if(document.getElementsByName("ProviderID")[0].value==''){
	alert("Please select Rendering Provider.");
	document.getElementsByName("ProviderID")[0].focus();
	return false;
	}
	//alert(document.getElementsByName("SupervisorID")[0].value);
	
	//if(document.getElementsByName("SupervisorID")[0].value==''){
//	alert("Please select referring Provider.");
//	document.getElementsByName("SupervisorID")[0].focus();
//	return false;
//	}
	
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
	
	
	for(var i=5;i<10;i++){
	str="bill[" + i +"][total_charges]";
	code_str = "bill[" + i +"][code]";
	justify_str = "bill[" + i +"][justify]";
	frm_date = "bill[" + i +"][from_dos]";
	to_date = "bill[" + i +"][to_dos]";	
	if(document.getElementById(str).value=="" && document.getElementById(code_str).value!=""){
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
			if(document.getElementById(frm_date).value==''){
			alert("Please enter from date");
			document.getElementById(frm_date).focus();
			return false;
			}
			if(document.getElementById(to_date).value==''){
			alert("Please enter to date");
			document.getElementById(to_date).focus();
			return false;
			}
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
			for (var js = 0; js < just_value.length; js++) {
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
		if(document.getElementById('batch_id').value != 'DP' && document.getElementById('batch_id').value != 'IP' && document.getElementById('batch_id').value != 'Waiting for Enrollment' ){
		alert("Please enter correct batch as 'DP' or 'IP' or 'Waiting for Enrollment' ");
		document.getElementById('batch_id').focus();
		return false;
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
		alert("Please enter correct batch as 'DX Clarification' or 'CPT Clarification' or 'Location Clarification' or 'Provider Clarification' or 'DOS Clarification' or 'DOB Clarification' ");
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
 

  top.restoreSession();
  f.submit();
 }

$(document).ready(function(){
  enable_big_modals();
});
function bill_loc(){
var pid=<?php echo $pid;?>;
var dte=document.getElementById('form_date').value;
var facility=document.forms[0].facility_id.value;
ajax_bill_loc(pid,dte,facility);
}

function cpt_code_popup(code,lineno,pat_id){
if(lineno>1 && lineno<=4)
{
flag=0;
	for(i=1;i<lineno;i++){
	str = 'bill['+ i +'][code]';
	str1= 'bill['+ lineno +'][code]';
		if(document.getElementById(str).value == document.getElementById(str1).value)
		flag=1;
	}
	if(flag==1)
	{
		alert("Duplicate ICD9 not allowed");
		document.getElementById(str1).value='';
		document.getElementById(str1).focus();
	}
}
else if(lineno>5 && lineno<=10)
{
flag=0;
	for(i=5;i<lineno;i++){
	str = 'bill['+ i +'][code]';
	str1= 'bill['+ lineno +'][code]';
		if(document.getElementById(str).value == document.getElementById(str1).value)
		flag=1;
	}
	if(flag==1)
	{
		alert("Duplicate CPT4 not allowed");
		document.getElementById(str1).value='';
		document.getElementById(str1).focus();
	}
}

ajax_cpt_code_popup(code,lineno,pat_id);
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

function replacedate(obj){
	str=obj.value;
	//str=str.replace(/[^\d]/gi,'');
	
	for(var i=5;i<=10;i++){
	strfrmdate = 'bill[' + i + '][from_dos]';
	strtodate = 'bill[' + i + '][to_dos]';
		document.getElementById(strfrmdate).value=str;
		document.getElementById(strtodate).value=str;
	}	
	return;
}
function replacetodate(cnt,obj){
	str=obj.value;	
	strtodate = 'bill[' + cnt + '][to_dos]';
	document.getElementById(strtodate).value=str;	
	return;
}
</script>

</head>

<?php if ($viewmode) { ?>
<body class="body_top">
<?php } else { ?>
<body class="body_top" onload="javascript:document.new_encounter.facility_code.focus();">

<?php } ?>

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
$dres = sqlStatement("select hospice from form_encounter where encounter=$encounter");
  $drow = sqlFetchArray($dres);
   $hsp= $drow['hospice'];
   if($hsp != '0')
   {
   echo " <font color='red' size='3pt'>This encounter is marked as hospise, Please enter appropriate Modifier.</font>";
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
<input type="hidden" name="primary_ins" id="primary_ins" value="<?php echo $primary_insurance?>" />
<input type="hidden" name="pid" id="pid" value="<?php echo $pid?>" />
<input type="hidden" name="secondary_ins" id="secondary_ins" value="<?php echo $secondary_insurance?>" />

<input type="hidden" name="demo_DOB" id="demo_DOB" value="<?php echo $demo_DOB?>" />
<input type="hidden" name="demo_address" id="demo_address" value="<?php echo $demo_address?>" />
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

if($isActive)
{
	echo "<p><font color='red'>Patient status is In-Active</font></p>\n";
	$disabled = 'disabled';
}

if ($isBilled) {
  echo "<p><font color='green'>This encounter has been billed. If you " .
    "need to change it, it must be re-opened.</font></p>\n";
	
  $disabled = 'disabled';
}
//else { // the encounter is not yet billed
?>
<table width='100%' border="0" >
<tr><?php /*?><td width="25%"<?php if ($GLOBALS['athletic_team']) echo " style='visibility:hidden;'"; ?> class='bold' ><?php xl('Visit Category','e'); ?></td><?php */?>
<td width="25%" class='bold'><?php xl('Facility','e'); ?></td>
<?php /*?><td class='bold' width="25%"><?php echo htmlspecialchars( xl('Billing Facility'), ENT_NOQUOTES); ?></td>
<?php */?><?php
 $sensitivities = acl_get_sensitivities();
 if ($sensitivities && count($sensitivities)) {
  usort($sensitivities, "sensitivity_compare");
?>
     <?php } ?>
     <td class='bold' width="25%"><?php xl('Claim Status','e'); ?></td><td class='bold' nowrap><?php xl('Date of Service','e'); ?></td>
<?php /*?> <td<?php if ($GLOBALS['ippf_specific'] || $GLOBALS['athletic_team']) echo " style='visibility:hidden;'"; ?> class='bold' nowrap width="25%">
     <?php xl('Onset/hosp. date','e'); ?></td><?php */?>
</tr>

<?php  $visitCategory = sqlQuery("SELECT count(encounter) as count from form_encounter where pid=$pid");
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
$dres = sqlStatement("select facility.id,facility.name FROM facility, form_encounter where facility.id=form_encounter.facility_id and form_encounter.encounter=$encounter");
  $drow = sqlFetchArray($dres);
   $def_facility = $drow['id'];
  $facility_name = $drow['name'];
  }
  
?>
   <?php /*?><input type="hidden" id="hidden_ajax_close_value" value="<?php echo htmlspecialchars($def_facility);?>" /><input name='facility_code'  id='facility_code' class="text"  style="width:150px"   onKeyDown="PreventIt(event)"  value="<?php echo $facility_name;?>"  autocomplete="off"   />
   
   <div id='ajax_div_insurance_section'>
		  <div id='ajax_div_insurance_error'>
		  </div>
		  <div id="ajax_div_facility" style="display:none;"></div>
		  </div>
		 </div><?php */?>  
         
         <select name="facility_code" id="facility_code" <?php if ($isBilled || $isActive) echo " disabled"; ?>>
         <option value="0">- Select Facility - </option>
         <?php 
		 // Added by Gangeya : Bug ID 8302
		 if($viewmode)
		 {
		 	$dres = sqlStatement("select id,name FROM facility where service_location=1 order by name asc");
		 }
		 else
		 {
		 	$dres = sqlStatement("select id,name FROM facility where service_location=1 and active = 1 order by name asc");
		 }
  while($drow = sqlFetchArray($dres)){
  $selected='';
		  if($viewmode || $new_enc_mode==1)
		  {
			if( $def_facility == $drow[id] )
				$selected ="selected"; 
		  }			
	         echo "<option value='$drow[id]' $selected>$drow[name]</option>"; 
		  }		 
		 ?>
         </select>
                  
              <input type="hidden" id="facility_id" name="facility_id" value="<?php echo $def_facility;?>" >
              
      <?php /*?><select name='facility_id' id="facility_id" onChange="bill_loc()"> 
<?php

if ($viewmode) {
  $def_facility = $result['facility_id'];
} else {
  $dres = sqlStatement("select facility_id from users where username = '" . $_SESSION['authUser'] . "'");
  $drow = sqlFetchArray($dres);
  $def_facility = $drow['facility_id'];
}
$fres = sqlStatement("select * from facility where service_location != 0 order by name");
if ($fres) {
  $fresult = array();
  for ($iter = 0; $frow = sqlFetchArray($fres); $iter++)
    $fresult[$iter] = $frow;
  foreach($fresult as $iter) {
?>
       <option value="<?php echo $iter['id']; ?>" <?php if ($def_facility == $iter['id']) echo "selected";?>><?php echo $iter['name']; ?></option>
<?php
  }
 }
?>
      </select> <?php */?><!--<a id="f_cd" onClick="javascript:add_edit_facility()" >Add/Edit</a>-->
	<?php /*?> <input type="button" name="Add/Edit" onClick="javascript:add_edit_facility()" value="Add New" style="width:70px"><?php */?>

     </td>
     <?php 
	 $qsql = sqlStatement("SELECT id, name FROM facility WHERE billing_location = 1");
	 while ($facrow = sqlFetchArray($qsql)) {
				$billing_facility = $facrow['id'] ;
				}
				if($viewmode) $billing_facility = $result['billing_facility'];
				echo "<input type='hidden' name='billing_facility' id='billing_facility' value='$billing_facility' />";
	 
	 ?>
     <?php /*?><td class='text'  width="25%">
			<div id="ajaxdiv">
			<?php
			billing_facility('billing_facility',$result['billing_facility']);
			?>
			</div>
		</td><?php */?>
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
 $qsql = sqlStatement("SELECT id, status,iphone_status FROM claim_status");
  $selected1='';	
?>
<select name="Encounter_Status" id="Encounter_Status" <?php if ($isBilled ||  $isActive) echo " disabled"; ?>			 > 
<?php
  while ($statusrow = sqlFetchArray($qsql)) {
 $claim_status = $statusrow['status'];
 $claim_status_id = $statusrow['id'];
 
 
 if(!$viewmode) {
 if($claim_status=="Ready to send primary")
 $selected1 = "selected";
 else
 $selected1="";
 }
 else { if($St['Status'] == $claim_status)
 $selected1 = "selected";
 else  $selected1="";
 }
 // commented by sangram for preventing inserting null in form encounter table for //claim_statuys_id (Hot-fix)
// if($statusrow['iphone_status']== "true")
// $disabled1 = "disabled";
 //else
// $disabled1 = "";
 
 
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
$dos = $result['date'];
$view_dos=date('m/d/Y');
if($dos){
$newDate = date("m/d/Y", strtotime($dos));
$view_dos=$newDate;
}
?>

     <input type='text' size='10' name='form_date' id='form_date' <?php echo $disabled ?>
       value='<?php echo $view_dos; ?>'
       title='<?php xl('mm/dd/yyyy Date of service','e'); ?>'
       onkeyup='datekeyup(this)' onBlur="dateblur(this,mypcc,'<?php echo date("m/d/Y"); ?>'),replacedate(this)" <?php if ($isBilled ||  $isActive) echo " disabled"; ?> />
        <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
        id='img_form_date' border='0' alt='[?]' style='cursor:pointer;cursor:hand'
        title='<?php xl('Click here to choose a date','e'); ?>'>
        &nbsp;&nbsp;
        
		<?php 
		//added By Gangeya : PQRS BUG
		if($viewmode || $new_enc_type==1)
		{
		?>
        <a href="../../forms/newpatient/pqrs_popup.php" class='iframe small_modal' onclick='top.restoreSession()' style="font-weight:bold; background-color:#33CCFF; padding:3px">
		<?php echo htmlspecialchars(xl('PQRS'),ENT_NOQUOTES); ?></a>
        <?php 
		}
		?>
     </td>
      <?php /*?><td<?php if ($GLOBALS['ippf_specific'] || $GLOBALS['athletic_team']) echo " style='visibility:hidden;'"; ?> class='text' nowrap><!-- default is blank so that while generating claim the date is blank. -->
      <input type='text' size='10' name='form_onset_date' id='form_onset_date'
       value='<?php echo $viewmode && $result['onset_date']!='0000-00-00 00:00:00' ? substr($result['onset_date'], 0, 10) : ''; ?>' 
       title='<?php xl('yyyy-mm-dd Date of onset or hospitalization','e'); ?>'
       onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
        <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
        id='img_form_onset_date' border='0' alt='[?]' style='cursor:pointer;cursor:hand'
        title='<?php xl('Click here to choose a date','e'); ?>'>
     </td><?php */?>
    
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

<tr><td class='bold'><?php xl('Rendering Provider','e'); ?></td><td class='bold'><?php xl('Referring provider','e'); ?></td><td class='bold'><?php xl('Place of service','e'); ?></td>
<?php /*?><td class='bold'><?php xl('Batch#','e'); ?></td><td class='bold'><?php xl('Price level','e'); ?></td><?php */?></tr><tr>
<?php 

echo "<td valign='top'>";
genProviderSelect('ProviderID', '-- Please Select --', $encounter_provid, $isBilled,$isActive);
  echo "  </td>"; 
 
 echo "<td valign='top'>";
genProviderSelect('SupervisorID', '-- Please Select --', $encounter_supid, $isBilled,$isActive);
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
	
<td>
<select name="pos" id="pos" <?php if ($isBilled ||  $isActive) echo " disabled"; ?>>
<?php
 $prow = sqlStatement("SELECT id,pos_id,pos_name FROM pos_list order by pos_name");
 $fresult = array();
 if(!$viewmode)
 $result['pos_id']=24;
  for ($iter = 0; $frow = sqlFetchArray($prow); $iter++)
    $fresult[$iter] = $frow;
  foreach($fresult as $iter) {  
   $pos_id = $iter['id'];
   $pos_name = $iter['pos_id']." - ".$iter['pos_name'];
   $selected='';
   if($result['pos_id']==$pos_id) $selected = "selected";
   echo "<option value='$pos_id' $selected>$pos_name</option>";
   }
	
?>
</select>
</td>
<?php /*?> <td>
  <input type="text" name="batch_id" id="batch_id" value="<?php echo $batch_id?>" <?php if ($isBilled) echo " disabled"; ?>>
 </td><?php */?>
</tr>
<?php
if ($viewmode || $new_enc_type==1) {
$icd9_first =array();
$cpt4_first =array();
$copay_first =array();
for($i=0;$i<count($billresult);$i++){
if($billresult[$i]['code_type']=="COPAY"){
$copay_value=$billresult[$i]['code'];
$copay_first=$billresult[$i]['id'];
}

if($billresult[$i]['code_type']=="ICD9")
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
<td class='bold'><?php xl('Batch#','e'); ?>
<br>
<input type="text" name="batch_id" id="batch_id" value="<?php echo $batch_id?>" <?php if ($isBilled ||  $isActive) echo " disabled"; ?>></td>

<td><table  ><th class='bold'><?php xl('COPAY','e'); ?></th>
<?php 
echo "<tr>";
echo "<td class='bold'><input type='hidden' id='bill[11][code_type]' name='bill[11][code_type]' value='COPAY' /><input type='hidden' name='bill[11][id]' id='bill[11][id]' value='$copay_first' />Amount:</td>";
echo "<td class='bold'> $<input type='text' name='bill[11][price]' id='bill[11][price]' value='$copay_value' size='8' onchange='validateAmount(this.value)' ><input type='hidden' name='bill[11][code]' id='bill[11][code]' value='$copay_value' size='8' ></td>";

 ?>
</table></td>
<td>
<table>
<tr><th class='bold'><a onClick="javascript:icd9_codes('ICD9')" href="#"><?php xl('DX1','e'); ?></a></th><th class='bold'><?php xl('DX2','e'); ?></th><th class='bold'><?php xl('DX3','e'); ?></th><th class='bold'><?php xl('DX4','e'); ?></th></tr>
<?php  
echo "<tr>";
for($cnt=1; $cnt<=4;$cnt++){
$billid='';
$icnt = $cnt-1;
$billid=$icd9_first['id'][$icnt];
$icdcode=$icd9_first['code'][$icnt];
echo "<td> <input type='hidden' name='bill[$cnt][id]' id='bill[$cnt][id]' value='$billid' /><input type='text' name='bill[$cnt][code]' id='bill[$cnt][code]' value='$icdcode' onChange='cpt_code_popup(this.value,$cnt,$pid)' size='10' $disabled><input type='hidden' id='bill[$cnt][code_type]' name='bill[$cnt][code_type]' value='ICD9' /></td>";
}
echo "</tr>";
?>
</table></td></tr>
<tr>
<table width="100%" style="background-color:#3399CC">
<tr class="bold">
<th>From</th><th>To</th><th><a onClick="javascript:icd9_codes('CPT4')" href="#">CPT4<a></th><th>Mod1</th><th>Mod2</th><th>Mod3</th><th>Mod4</th><th>DX Pointer</th><th>TOS</th><th>Units</th><th>Units Charge</th><th>Total Charge</th>
</tr>
<?php  
//print_r($cpt4_first);
for($cnt=5; $cnt<=10;$cnt++){
$ccnt = $cnt-5;
$cpt_code=''; $units='';$fee='';$total_charge='';$tos='';$justify='';$from_dos='';$to_dos='';$billid='';$key='';$jarr='';
$mod1='';$mod2='';$mod3='';$mod4='';
$just_arr = array();
$justify_arr = array();

if ($viewmode || $new_enc_type==1) {
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
$justify_arr[$cnt] = implode('',$just_arr);

}
echo "<tr style='background-color:lightblue;'>";
echo "<td> <input type='text' size='8' name='bill[$cnt][from_dos]' id='bill[$cnt][from_dos]'
       value='$from_dos' 
       title='From Date of Service'
       onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc),replacetodate($cnt,this)' $disabled/></td>"; 
echo "<td>  <input type='text' size='8' name='bill[$cnt][to_dos]' id='bill[$cnt][to_dos]'
       value='$to_dos' title='To Date of Service'
       onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' $disabled/></td>"; 

   echo "<input type='hidden' name='bill[$cnt][id]' id='bill[$cnt][id]' value='$billid' />";
echo "<td><input type='text' name='bill[$cnt][code]' id='bill[$cnt][code]' size='10' value='$cpt_code' onKeyDown='PreventIt(event)' onblur='cpt_code_popup(this.value,$cnt,$pid)' autocomplete='off' $disabled><input type='hidden' id='bill[$cnt][code_type]' name='bill[$cnt][code_type]' value='CPT4' /></td>";
echo "<td><input type='text' name='bill[$cnt][mod1]' id='bill[$cnt][mod1]' value='$mod1' size='5' $disabled></td>";
echo "<td><input type='text' name='bill[$cnt][mod2]' id='bill[$cnt][mod2]' value='$mod2' size='5' $disabled></td>";
echo "<td><input type='text' name='bill[$cnt][mod3]' id='bill[$cnt][mod3]' value='$mod3' size='5' $disabled></td>";
echo "<td><input type='text' name='bill[$cnt][mod4]' id='bill[$cnt][mod4]' value='$mod4' size='5' $disabled></td>";
echo "<td><input type='text' name='bill[$cnt][justify]' id='bill[$cnt][justify]' onBlur='validatejustify(this.value,$cnt)' size=8' value='$justify_arr[$cnt]' $disabled></td>";
echo "<td><input type='text' name='bill[$cnt][tos]' id='bill[$cnt][tos]' value='$tos' size='8' $disabled></td>";
echo "<td><input type='text' name='bill[$cnt][units]' id='bill[$cnt][units]'  value='$units' onblur='total_charges_calc($cnt);' size='5' $disabled></td>";
echo "<td><input type='text' name='bill[$cnt][price]' id='bill[$cnt][price]'  value='$fee' onblur='total_charges_calc($cnt);' size='8' $disabled readonly></td>";
echo "<td><input type='text' name='bill[$cnt][total_charges]' id='bill[$cnt][total_charges]'  value='$total_charge' onblur='total_charges_calc($cnt);' size='8' $disabled readonly></td>";
echo "</tr>";
 } ?>

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
<?php if($viewmode || $new_enc_type==1){ echo $result['reason']; }   ?>
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
if($viewmode || $new_enc_type==1)
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
 <input type='button' onClick="javascript:saveClicked('draft');" value='<?php xl('Save as Draft','e');?>' style="width:150px" <?php if($viewmode) echo disabled ?> />
  <input type='button' onClick="javascript:saveClicked('review');" value='<?php xl('Save for Review','e');?>' style="width:150px" <?php if($viewmode) echo disabled ?> />
  <input type='button' onClick="javascript:saveClicked('<?php echo $mode; ?>');" value='<?php xl('Approve','e');?>' style="width:150px" <?php echo $disabled ?> />
   <input type='button'  onClick="javascript:saveClicked('clarification');" value='<?php xl('Save for Clarification','e');?>' style="width:150px" <?php if($viewmode) echo disabled ?> />
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

<?php //} ?>
<!-- END    ------------- -->

<input type='hidden' name='ajax_mode' id='ajax_mode' value='' />
<input type="hidden" name="facility_cd" id="facility_cd" value="<?php echo htmlspecialchars($facility_cd);?>" />

<input type="hidden" name="drafted" id="drafted" value="<?php if($new_enc_type==1) echo 1; else echo 0; ?>" />
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
