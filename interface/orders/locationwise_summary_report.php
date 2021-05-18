<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";

function thisLineItem($NoOfDemorow,$Proceduresquery,$Providerquery,$DOSWiseReceiptquery,$MonthWiseReceiptquery,$ECEAA) 
{
 // $provname = $row['provider_lname'];
//  if (!empty($row['provider_fname'])) {
//    $provname .= ', ' . $row['provider_fname'];
//    if (!empty($row['provider_mname'])) {
//      $provname .= ' ' . $row['provider_mname'];
//    }
//  }

  if ($_POST['form_csvexport']) {
    echo '"' . addslashes($NoOfDemorow['NoOfDemo']) . '",';
	echo '"' . addslashes($Proceduresquery['procedures']) . '",';
	echo '"' . addslashes($Providerquery['Location']) . '",';
	echo '"' . addslashes($Proceduresquery['EnteredBilledAmount']) . '",';
	echo '"' . addslashes($ECEAA) . '",';
	echo '"' . addslashes($DOSWiseReceiptquery['DOSWiseReceipt']) . '",';
	echo '"' . addslashes($MonthWiseReceiptquery['MonthWiseReceipt']) . '",';

//echo '"' . addslashes($row['fname'        ]) . '",';
//	echo '"' . addslashes($row['lname'        ]) . '",';
//	echo '"' . addslashes($row['mname'        ]) . '",';
//    echo '"' . addslashes(oeFormatShortDate($row['DOB'  ])) . '",';
//    echo '"' . addslashes($row['street'  ]) . '",';
//    echo '"' . addslashes($row['city']) . '",';
//    echo '"' . addslashes($row['state'   ]) . '"' . "\n";
  }
  else {
?>
 <tr style="font-family:Verdana;font-size:10px" bgcolor="#C1EEFF" >
  <td class="detail"><?php echo $NoOfDemorow['NoOfDemo']; ?></td>
  <td class="detail"><?php echo $Proceduresquery['procedures']; ?></td>
    <td class="detail"><?php echo $Providerquery['Location']; ?></td>
    <td class="detail"><?php echo $Proceduresquery['EnteredBilledAmount']; ?></td>
    <td class="detail"><?php echo $ECEAA; ?></td>
    <td class="detail"><?php echo $DOSWiseReceiptquery['DOSWiseReceipt']; ?></td>
    <td class="detail"><?php echo $MonthWiseReceiptquery['MonthWiseReceipt']; ?></td>

 </tr>
<?php
  } // End not csv export
}

if (! acl_check('acct', 'rep')) die(xl("Unauthorized access."));

$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
$form_facility  = $_POST['form_facility'];

if ($_POST['form_csvexport']) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=Locationwise_Summary_Report.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  echo '"' . xl('NoOfDemo') . '",';
  echo '"' . xl('procedures') . '",';
  echo '"' . xl('Providerquery') . '"';
  echo '"' . xl('EnteredBilledAmount') . '"';
  echo '"' . xl('EnteredCharges-EAA') . '"';
  echo '"' . xl('DOSWiseReceipt') . '"' ;
  echo '"' . xl('MonthWiseReceipt') . '"'."\n";
}
else { // not export
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Locationwise Summary Report','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('Locationwise Summary Report','e')?></h2>

<form method='post' action='locationwise_summary_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   <?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', false); ?>
   &nbsp;<?xl('From:','e')?>
   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc,'<?php echo date("m/d/Y"); ?>')' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;To:
   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc,'<?php echo date("m/d/Y"); ?>')' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;
   <input type='submit' name='form_refresh' value="<?php xl('Refresh','e') ?>">
   &nbsp;
   <input type='submit' name='form_csvexport' value="<?php xl('Export to CSV','e') ?>">
   &nbsp;
   <input type='button' value='<?php xl('Print','e'); ?>' onclick='window.print()' />
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>
 <tr bgcolor="#5AD1E7">
  <td class="dehead"><?php xl('NoOfDemo','e'  ) ?></td>
  <td class="dehead"><?php xl('Procedures','e'  ) ?></td>
  <td class="dehead"><?php xl('Location','e'  ) ?></td>
  <td class="dehead"><?php xl('EnteredBilledAmount','e'  ) ?></td>
  <td class="dehead"><?php xl('EnteredCharges-EAA','e'  ) ?></td>
  <td class="dehead"><?php xl('DOSWiseReceipt','e'  ) ?></td>
  <td class="dehead"><?php xl('MonthWiseReceipt','e'  ) ?></td>

 </tr>
<?php
} // end not export

// If generating a report.
//
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date;
  $to_date   = $form_to_date;
//$query = "SELECT id,fname,lname,mname,DOB,street,city,state from patient_data";
  $NoOfDemoquery = "select count(pd.pid) as NoOfDemo from form_encounter fe, patient_data pd ".
"where fe.pid=pd.pid and pd.date >= '$from_date' AND pd.date <= '$to_date'";
   //TBD: What if preliminary and final reports for the same order?
  if ($form_facility) {
    $NoOfDemoquery .= " AND fe.facility_id='$form_facility'";
  }
  
 $Proceduresquery = "select count(b.code) as procedures , sum(b.fee) as EnteredBilledAmount from billing b, form_encounter fe, "."patient_data pd where fe.pid=pd.pid and fe.encounter=b.encounter  ".
"and pd.date >= '$from_date' AND pd.date <= '$to_date'";
   //TBD: What if preliminary and final reports for the same order?
  if ($form_facility) {
    $Proceduresquery .= " AND fe.facility_id='$form_facility'";
  }

$Providerquery = "select distinct(name) as Location from billing b,".
				"form_encounter fe, patient_data pd,facility f ".
				"where fe.pid=pd.pid and fe.encounter=b.encounter and f.id=fe.facility_id";
  
    if ($form_facility) {
    $Providerquery .= " AND fe.facility_id='$form_facility'";
  }

    $Providerres = sqlStatement($Providerquery);
	
	 $DOSWiseReceiptquery = "select sum(b.fee) as DOSWiseReceipt from form_encounter fe,".
	  " billing b ".
		"where fe.encounter=b.encounter ".
		"and fe.date >= '$from_date' AND fe.date <= '$to_date'";
   //TBD: What if preliminary and final reports for the same order?
  if ($form_facility) {
    $DOSWiseReceiptquery .= " AND fe.facility_id='$form_facility'";
  }
   $MonthWiseReceiptquery = "select sum(aa.pay_amount) as MonthWiseReceipt from ar_activity aa,".
   "form_encounter fe where aa.encounter=fe.encounter and ".
		" aa.post_time >= '$from_date' AND aa.post_time <= '$to_date'";
   //TBD: What if preliminary and final reports for the same order?
  if ($form_facility) {
    $MonthWiseReceiptquery .= " AND fe.facility_id='$form_facility'";
  }
   
    $CPTList = "select code as CPT from billing b, form_encounter ".
"fe, patient_data pd where fe.pid=pd.pid and fe.encounter=b.encounter and b.code_type='CPT4'  ".
"and pd.date >= '$from_date' AND pd.date <= '$to_date'";
   //TBD: What if preliminary and final reports for the same order?
  if ($form_facility) {
    $CPTList .= " AND fe.facility_id='$form_facility'";
  }
  $sum = 0;
$CPTres = sqlStatement($CPTList);
  while ($CPTrow = sqlFetchArray($CPTres)) {
   $CPT = $CPTrow['CPT'];
    $EAArow=sqlFetchArray(sqlStatement("select NP_EAA from cpt_eaa where Code='$CPT'"));
	
	$sum = $sum + $EAArow['NP_EAA'];
  }
  while ($Providerrow = sqlFetchArray($Providerres)) {
    thisLineItem(sqlFetchArray(sqlStatement($NoOfDemoquery)),sqlFetchArray(sqlStatement($Proceduresquery)),$Providerrow,sqlFetchArray(sqlStatement($DOSWiseReceiptquery))
	,sqlFetchArray(sqlStatement($MonthWiseReceiptquery)),$sum);
  }
  

} // end report generation

if (! $_POST['form_csvexport']) {
?>

</table>
</form>
</center>
</body>

<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</html>
<?php
} // End not csv export
?>
