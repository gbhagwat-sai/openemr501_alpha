<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.


// WHEN CONVERT THIS TO NEW SECURITY MODEL, NEED TO REMOVE FOLLOWING
//   AT APPROXIMATELY LINE 377:
//     $_REQUEST = stripslashes_deep($_REQUEST);
// http://open-emr.org/wiki/index.php/Active_Projects#PLAN


require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("../../../library/acl.inc");
require_once("../../../custom/code_types.inc.php");
require_once("$srcdir/patient.inc");
include_once("../../reports/report.inc.php");//Criteria Section common php page
require_once("$srcdir/billrep.inc");
//require_once(dirname(__FILE__) . "/../../library/classes/OFX.class.php");
//require_once(dirname(__FILE__) . "/../../library/classes/X12Partner.class.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
//require_once("adjustment_reason_codes.php");

//$alertmsg = '';
 //echo "<pre>";print_r($_POST); echo "</pre>";//die;
// This is obsolete.
/*if ($_POST['mode'] == 'process') {
  if (exec("ps x | grep 'process_bills[.]php'")) {
    $alertmsg = xl('Request ignored - claims processing is already running!');
  }
  else {
    exec("cd $webserver_root/library/freeb;" .
      "php -q process_bills.php bill > process_bills.log 2>&1 &");
    $alertmsg = xl('Batch processing initiated; this may take a while.');
  }
}*/

//global variables:
if (!isset($_POST["mode"])) {
  $from_date = isset($_POST['from_date']) ? $_POST['from_date'] : date('Y-m-d');
  $to_date   = isset($_POST['to_date'  ]) ? $_POST['to_date'  ] : '';
  $code_type = isset($_POST['code_type']) ? $_POST['code_type'] : 'all';
  $unbilled  = isset($_POST['unbilled' ]) ? $_POST['unbilled' ] : 'on';
  $my_authorized = $_POST["authorized"];
} else {
  $from_date     = $_POST["from_date"];
  $to_date       = $_POST["to_date"];
  $code_type     = $_POST["code_type"];
  $unbilled      = $_POST["unbilled"];
  $my_authorized = $_POST["authorized"];
}

// This tells us if only encounters that appear to be missing a "25" modifier
// are to be reported.
$missing_mods_only = !empty($_POST['missing_mods_only']);

/*
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : date('Y-m-d');
$to_date   = empty($_POST['to_date'  ]) ? $from_date : $_POST['to_date'];
$code_type = isset($_POST['code_type']) ? $_POST['code_type'] : 'all';
$unbilled  = isset($_POST['unbilled' ]) ? $_POST['unbilled' ] : 'on';
$my_authorized = $_POST["authorized"];
*/

$left_margin = isset($_POST["left_margin"]) ? $_POST["left_margin"] : 24;
$top_margin  = isset($_POST["top_margin"] ) ? $_POST["top_margin" ] : 20;

$ofrom_date  = $from_date;
$oto_date    = $to_date;
$ocode_type  = $code_type;
$ounbilled   = $unbilled;
$oauthorized = $my_authorized;
?>

<html>
<head>
<?php if (function_exists(html_header_show)) html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
<style>
.subbtn { margin-top:3px; margin-bottom:3px; margin-left:2px; margin-right:2px }
</style>


<script>

function select_all() {
  for($i=0;$i < document.update_form.length;$i++) {
    $name = document.update_form[$i].name;	
    if ($name.substring(0,7) == "claims[" && $i && "]" ) {
      document.update_form[$i].checked = true;
    }
  }
  set_button_states();
}

function set_button_states() {
  var f = document.update_form;
  var count0 = 0; // selected and not billed or queued
  var count1 = 0; // selected and queued
  var count2 = 0; // selected and billed
  for($i = 0; $i < f.length; ++$i) {
    $name = f[$i].name;
    if ($name.substring(0, 7) == "claims[" && $i &&"]" && f[$i].checked == true) {
      if      (f[$i].value == '0') ++count0;
      else if (f[$i].value == '1' || f[$i].value == '5') ++count1;
      else ++count2;
    }
  }


}

// Process a click to go to an encounter.
function toencounter(pid, pubpid, pname, enc, datestr, dobstr,note) {
 top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']) { ?>
 var othername = (window.name == 'RTop') ? 'RTop' : 'RTop';
 parent.left_nav.setPatient(pname,pid,pubpid,'',dobstr,note);
 parent.left_nav.setEncounter(datestr, enc, othername);
 parent.left_nav.setRadio(othername, 'enc'); 
 parent.frames[othername].location.href =
  '../../patient_file/encounter/encounter_top.php?set_encounter='
  + enc + '&pid=' + pid;
<?php } else { ?>
 location.href = '../../patient_file/encounter/patient_encounter.php?set_encounter='
  + enc + '&pid=' + pid;
<?php } ?>
}
// Process a click to go to an patient.
function topatient(pid, pubpid, pname, enc, datestr, dobstr,note) {
 top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']) { ?>
 var othername = (window.name == 'RTop') ? 'RTop' : 'RTop';
 parent.left_nav.setPatient(pname,pid,pubpid,'',dobstr,note);
 parent.frames[othername].location.href =
  '../../patient_file/summary/demographics_full.php?pid=' + pid;
<?php } else { ?>
 location.href = '../../patient_file/summary/demographics_full.php?pid=' + pid;
<?php } ?>
}
</script>
<script language="javascript" type="text/javascript">
EncounterDateArray=new Array;
CalendarCategoryArray=new Array;
EncounterIdArray=new Array;
function SubmitTheScreen()
 {//Action on Update List link
 if(!ProcessBeforeSubmitting())
   return false;
  top.restoreSession();
  document.the_form.mode.value='change';
  document.the_form.target='_self';
  document.the_form.action='encounter_summary.php';
  document.the_form.submit();
  return true;
 }
 function divtoggle(spanid, divid) {//Called in the Expand, Collapse links(This is for a single item)
    var ele = document.getElementById(divid);
    if(ele)
     {
        var text = document.getElementById(spanid);
        if(ele.style.display == "inline") {
            ele.style.display = "none";
            text.innerHTML = "<?php echo htmlspecialchars(xl('Expand'), ENT_QUOTES); ?>";
        }
        else 
		{
            ele.style.display = "inline";
            text.innerHTML = "<?php echo htmlspecialchars(xl('Collapse'), ENT_QUOTES); ?>";
        }
     }
}
 function MarkAsCleared(Type)
 { 
  CheckBoxBillingCount=0;
  for (var CheckBoxBillingIndex =0; ; CheckBoxBillingIndex++)
   {
    CheckBoxBillingObject=document.getElementById('CheckBoxBilling'+CheckBoxBillingIndex);
    if(!CheckBoxBillingObject)
     break;
    if(CheckBoxBillingObject.checked)
     {
       ++CheckBoxBillingCount;
     }
   }
   if(Type==1)
    {
	moveto="Approve";
     Message='<?php echo htmlspecialchars( xl('Are you sure to move the selected claims to Approve.'), ENT_QUOTES); ?>';
    }
   if(Type==2)
    {
	moveto="Review";
     Message='<?php echo htmlspecialchars( xl('Are you sure to move the selected claims to Review.'), ENT_QUOTES); ?>';
    }
   if(Type==3)
    {
	moveto="Clarification";
     Message='<?php echo htmlspecialchars( xl('Are you sure to move the selected claims to Clarification.'), ENT_QUOTES); ?>';
    }
	if(CheckBoxBillingCount>0){
  if(confirm(Message + "\n\n\n<?php echo htmlspecialchars( xl('Total'), ENT_QUOTES); ?>" + ' ' + CheckBoxBillingCount + ' ' +  "<?php echo htmlspecialchars( xl('Selected'), ENT_QUOTES); ?>"))
   {
    document.getElementById('HiddenMarkAsCleared').value='yes';
  }
  else
   {
    document.getElementById('HiddenMarkAsCleared').value='';
   }
   }
   else
   {
   alert("Please select at least one claim");   
   }
 }
</script>

<?php include_once("../../reports/report.script.php"); ?><!-- Criteria Section common javascript page-->
<!-- ================================================== -->
<!-- =============Included for Insurance ajax criteria==== -->
<!-- ================================================== -->
<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>
<?php
 include_once("../../../library/ajax/payment_ajax_jav.inc.php"); ?>
<script type="text/javascript" src="../../../library/js/common.js"></script>
<style>
#ajax_div_insurance {
    position: absolute;
    z-index:10;
    background-color: #FBFDD0;
    border: 1px solid #ccc;
    padding: 10px;
}
</style>
<?php /*?><script language="javascript" type="text/javascript">
document.onclick=TakeActionOnHide;
</script><?php */?>
<!-- ================================================== -->
<!-- =============Included for Insurance ajax criteria==== -->
<!-- ================================================== -->
</head>
<body class="body_top" onLoad="">

<p style='margin-top:5px;margin-bottom:5px;margin-left:5px'>

<?php if ($GLOBALS['concurrent_layout']) { ?>
<font class='title'><?php xl('Encounter Summary','e') ?></font>
<br>
<hr size="2" color="#000000">
<?php } else if ($userauthorized) { ?>
<a href="../../main/main.php" target='Main' onclick='top.restoreSession()'><font class=title><?php xl('Encounter Summary','e') ?></font><font class=more> <?php echo $tback; ?></font></a>
<?php } else { ?>
<a href="../../main/onotes/office_comments.php" target='Main' onclick='top.restoreSession()'><font class=title><?php xl('Encounter Summary','e') ?></font><font class=more><?php echo $tback; ?></font></a>
<?php } ?>

</p>

<form name='the_form' method='post' action='encounter_summary.php' onsubmit='return top.restoreSession()' style="display:inline">

<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script language='JavaScript'>
 var mypcc = '1';
</script>

<input type='hidden' name='mode' value='change'>
<!-- ============================================================================================================================================= -->
                                                        <!-- Criteria section Starts -->
<!-- ============================================================================================================================================= -->
<?php
//The following are the search criteria per page.All the following variable which ends with 'Master' need to be filled properly.
//Each item is seperated by a comma(,).
//$ThisPageSearchCriteriaDisplayMaster ==>It is the display on screen for the set of criteria.
//$ThisPageSearchCriteriaKeyMaster ==>Corresponding database fields in the same order.
//$ThisPageSearchCriteriaDataTypeMaster ==>Corresponding data type in the same order.
$_REQUEST = stripslashes_deep($_REQUEST);//To deal with magic quotes on.
$ThisPageSearchCriteriaDisplayRadioMaster=array();
$ThisPageSearchCriteriaRadioKeyMaster=array();
$ThisPageSearchCriteriaQueryDropDownMaster=array();
$ThisPageSearchCriteriaQueryDropDownMasterDefault=array();
$ThisPageSearchCriteriaQueryDropDownMasterDefaultKey=array();
$ThisPageSearchCriteriaIncludeMaster=array();

$ThisPageSearchCriteriaDisplayMaster="DOS,Claim Created Date,Claim Last Billed Date,Provider Name,Service Location,Insurance Name,Primary Insurance Company,Claim Status,Batch";
$ThisPageSearchCriteriaKeyMaster="fe.date,b.date,b.bill_date,u.fname,fe.facility,ic.id,id.id,fe.final_status,fe.batch_id";
$ThisPageSearchCriteriaDataTypeMaster="datetime,datetime,datetime,query_drop_down1,query_drop_down1,query_drop_down1,query_drop_down1,query_drop_down1,text_like";

$ThisPageSearchCriteriaQueryDropDownMaster[1]="select concat(fname,lname) as name ,id from users order by name;";
$ThisPageSearchCriteriaQueryDropDownMaster[2]="select name ,id from facility order by name;";

$ThisPageSearchCriteriaQueryDropDownMaster[3]="select name ,id from insurance_companies where active=1 order by name;";
$ThisPageSearchCriteriaQueryDropDownMaster[4]="select name ,id from insurance_companies where active=1 order by name;";
$ThisPageSearchCriteriaQueryDropDownMaster[5]="SELECT final_status as name  , final_status as id  FROM form_encounter_draft group by final_status order by name;";


//$ThisPageSearchCriteriaQueryDropDownMasterDefault[1]="All";//Only one item will be here
//$ThisPageSearchCriteriaQueryDropDownMasterDefaultKey[1]="all";//Only one item will be here

/*$ThisPageSearchCriteriaQueryDropDownMasterDefault[1]="save";//Only one item will be here
$ThisPageSearchCriteriaQueryDropDownMasterDefaultKey[1]="1";//Only one item will be here
$ThisPageSearchCriteriaQueryDropDownMasterDefault[2]="save";//Only one item will be here
$ThisPageSearchCriteriaQueryDropDownMasterDefaultKey[2]="2";//Only one item will be here*/

//The below section is needed if there is any 'include' type in the $ThisPageSearchCriteriaDataTypeMaster
//Function name is added here.Corresponding include files need to be included in the respective pages as done in this page.
//It is labled(Included for Insurance ajax criteria)(Line:-279-299).
$ThisPageSearchCriteriaIncludeMaster[1]="InsuranceCompanyDisplay";//This is php function defined in the file 'report.inc.php'

if(!isset($_REQUEST['mode']))//default case
 {
  $_REQUEST['final_this_page_criteria'][0]="(fe.date between '".date("Y-m-d 00:00:00")."' and '".date("Y-m-d 23:59:59")."')";
  // $_REQUEST['final_this_page_criteria'][1]="fe.final_status = 'save' ";
  // $_REQUEST['final_this_page_criteria'][1]="b.activity = '1'";
  
  $_REQUEST['final_this_page_criteria_text'][0]=htmlspecialchars(xl("DOS = Today"), ENT_QUOTES);
  //$_REQUEST['final_this_page_criteria_text'][1]=htmlspecialchars(xl("Claim Status = 'save'"), ENT_QUOTES);
   //$_REQUEST['final_this_page_criteria_text'][1]=htmlspecialchars(xl("All Draft claims"), ENT_QUOTES);
  
  /*$_REQUEST['date_master_criteria_form_encounter_date']="today";
  $_REQUEST['master_from_date_form_encounter_date']=date("Y-m-d");
  $_REQUEST['master_to_date_form_encounter_date']=date("Y-m-d");
  
   $_REQUEST['radio_billing_billed']=0;*/
 
 } 
?>
<table width='100%' border="0" cellspacing="0" cellpadding="0">
 <tr>
      <td width="25%">&nbsp;</td>
      <td width="50%">
            <?php include_once("$srcdir/../interface/reports/criteria.tab1.php"); ?>      
      </td>
      <td width="25%">
<?php
// ============================================================================================================================================= -->
                                                        // Criteria section Ends -->
// ============================================================================================================================================= -->
?>
      
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="15%">&nbsp;</td>
            <td width="85%"><span class='text'><a onClick="javascript:return SubmitTheScreen();" href="#" class=link_submit>[<?php echo htmlspecialchars(xl('Update List'), ENT_QUOTES) ?>]</a>
                </td>
          </tr>          
          
          <tr>
            <td>&nbsp;</td>
            <td><a href="javascript:select_all()" class="link_submit"><?php  echo htmlspecialchars(xl('[Select All]','e'), ENT_QUOTES) ?></a></td>
          </tr>
      </table>

      
      </td>
 </tr>
</table>
<table width='100%' border="0" cellspacing="0" cellpadding="0" >
    <tr>
        <td>
            <hr color="#000000">
        </td>
    </tr>
</table>
</form>
<form name='update_form' method='post' action='encounter_process.php' onsubmit='return top.restoreSession()' style="display:inline">
<center>
<span class='text' style="display:inline">
<input type="submit" class="subbtn" name="bn_approve" value="<?php xl('Approve','e')?>"
 title="<?php xl('Approve Encounter','e')?>"
 onclick="MarkAsCleared(1)">
 
 <input type="submit" class="subbtn" name="bn_review" value="<?php xl('Review','e')?>"
 title="<?php xl('Review','e')?>"
 onclick="MarkAsCleared(2)">
 
 <input type="submit" class="subbtn" name="bn_clarification" value="<?php xl('Clarification','e')?>"
 title="<?php xl('Clarification','e')?>"
 onclick="MarkAsCleared(3)">
 
 <?php
if ($ret = getEncountersBetween("%"))
//if ($ret = getBillsBetween($fstart,$sqllimit,"%"))
{
//print_r($ret);
  $loop = 0;
  $oldcode = "";
  $last_encounter_id = "";
  $lhtml = "";
  $rhtml = "";
  $lcount = 0;
  $rcount = 0;
  $bgcolor = "";
  $skipping = FALSE;

  $mmo_empty_mod = false;
  $mmo_num_charges = 0;
  $divnos=0;
  echo "<table><tr bgcolor='#6CAEFF' style='font-weight:bold;font-size:12'><th>Paient Id</th><th>Patient Name</th><th>DOB</th><th>Provider Name</th><th>Location</th><th>Encounter Id</th><th>Batch #</th><th>DOS</th><th>CPT</th><th>POS</th><th>Last Billed To</th><th>Primary Insurance</th><th>Amount</th><th>Claim Status</th></tr>";
  // print_r($ret); die;
   $cnt=0;
 foreach ($ret as $iter) {

  $name = getPatientData($iter['pid'], "fname, mname, lname, pubpid, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD,Alert_note");
   $ptname = $name['fname'] . " " . $name['lname'];  
  $raw_encounter_date = date("Y-m-d", strtotime($iter['DOS']));  
  
if($iter['Encounter']){
$approved= "Approved";
$check="";
}
else
{

if(strlen($iter[id])!=0)
{
$status_sql=sqlStatement("Select final_status from form_encounter_draft where id=$iter[id]");
$row=sqlFetchArray($status_sql);
$approved=$row['final_status'];
}

$check="<input type='checkbox' value=" . $iter['id'] . " name='claims[".$cnt."]' onclick='set_button_states()' id='CheckBoxBilling" . $CheckBoxBilling*1 . "'>";
$CheckBoxBilling++;
$cnt++;
}
$DOB= date("m-d-Y", strtotime($iter['DOB']));				 
$DOS= date("m-d-Y", strtotime($iter['DOS']));



 echo " <tr bgcolor='#CEE4FF' style='font-weight:bold;font-size:10'><td>$iter[ExternalID]</td><td>$iter[PatientName]</td><td>$DOB</td><td>$iter[RenderingProvider]</td><td>$iter[ServiceLocation]</td><td>".
				" <a class=\"link_submit\" " .
        "href=\"javascript:window.toencounter(" . $iter['pid'] .
        ",'" . addslashes($name['pubpid']) .
        "','" . addslashes($ptname) . "'," . $iter['Encounter'] .
        ",'" . oeFormatShortDate($raw_encounter_date) . "',' " . 
        xl('DOB') . ": " . oeFormatShortDate($name['DOB_YMD']) . " " . xl('Age') . ": " . getPatientAge($name['DOB_YMD']) . "','" .addslashes($name['Alert_note']) ."');
                 top.window.parent.left_nav.setPatientEncounter(EncounterIdArray[" . $iter['pid'] . "],EncounterDateArray[" . $iter['pid'] . 
                 "], CalendarCategoryArray[" . $iter['pid'] . "])\">".
				 "$iter[Encounter]</a></td><td>$iter[BatchNo]</td><td>$DOS</td><td>$iter[CPT]</td><td>$iter[POS]</td><td>$iter[LastBilledTo]</td><td>$iter[InsuranceComp]</td><td>$iter[TotalCharge]</td><td>$approved</td><td>$check</td></tr>";
 }
 echo "</table>";
 }
 ?>
</span>
</center>
</form>

<script>
set_button_states();
<?php
if ($alertmsg) {
  echo "alert('$alertmsg');\n";
}
?>
</script>
<input type="hidden" name="divnos"  id="divnos" value="<?php echo $divnos ?>"/>
<input type='hidden' name='ajax_mode' id='ajax_mode' value='' />
</body>
</html>
