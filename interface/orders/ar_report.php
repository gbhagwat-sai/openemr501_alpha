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

function thisLineItem($row) {
  $provname = $row['provider_lname'];
  if (!empty($row['provider_fname'])) {
    $provname .= ', ' . $row['provider_fname'];
    if (!empty($row['provider_mname'])) {
      $provname .= ' ' . $row['provider_mname'];
    }
  }

  if ($_POST['form_csvexport']) {
    echo '"' . addslashes($row['Encounter' ]) . '",';
	echo '"' . addslashes($row['InsuranceName' ]) . '",';
	echo '"' . addslashes($row['ProvierID' ]) . '",';
	echo '"' . addslashes($row['RendarringProvider' ]) . '",';
	echo '"' . addslashes($row['ExternalID' ]) . '",';
	echo '"' . addslashes($row['PID' ]) . '",';
	echo '"' . addslashes($row['PatientName' ]) . '",';
	echo '"' . addslashes($row['Gender' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['DOB'  ])) . '",';
	echo '"' . addslashes($row['ServiceLocation' ]) . '",';
	echo '"' . addslashes($row['CPT' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['DOS'  ])) . '",';
	echo '"' . addslashes($row['Charge' ]) . '",';
	echo '"' . addslashes($row['InsurancePayment' ]) . '",';
	echo '"' . addslashes($row['PatientPayment' ]) . '",';
	echo '"' . addslashes($row['Adjustment' ]) . '",';
	echo '"' . addslashes($row['Balence' ]) . '"' . "\n";
  }
  else {
?>
 <tr bgcolor="#CEE4FF">
  <td class="detail"><?php echo $row['Encounter' ]; ?></td>
  <td class="detail"><?php echo $row['InsuranceName' ]; ?></td>
  <td class="detail"><?php echo $row['ProvierID' ]; ?></td>
  <td class="detail"><?php echo $row['RendarringProvider' ]; ?></td>
  <td class="detail"><?php echo $row['ExternalID' ]; ?></td>
  <td class="detail"><?php echo $row['PID' ]; ?></td>
  <td class="detail"><?php echo $row['PatientName' ]; ?></td>
  <td class="detail"><?php echo $row['Gender' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['DOB'  ]); ?></td>
  <td class="detail"><?php echo $row['ServiceLocation' ]; ?></td>
  <td class="detail"><?php echo $row['CPT' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['DOS'  ]); ?></td>
  <td class="detail"><?php echo $row['Charge' ]; ?></td>
  <td class="detail"><?php echo $row['InsurancePayment' ]; ?></td>
  <td class="detail"><?php echo $row['PatientPayment' ]; ?></td>
  <td class="detail"><?php echo $row['Adjustment' ]; ?></td>
  <td class="detail"><?php echo $row['Balence' ]; ?></td>
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
  header("Content-Disposition: attachment; filename=AR_Report.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  echo '"' . xl('Encounter') . '",';
  echo '"' . xl('Insurance Name') . '",';
  echo '"' . xl('Provier ID') . '",';
  echo '"' . xl('Rendarring Provider') . '",';
  echo '"' . xl('External ID') . '",';
  echo '"' . xl('PID') . '",';
  echo '"' . xl('Patient Name') . '",';
  echo '"' . xl('Gender') . '",';
  echo '"' . xl('DOB') . '",';
  echo '"' . xl('Service Location') . '",';
  echo '"' . xl('CPT') . '",';
  echo '"' . xl('DOS') . '",';
  echo '"' . xl('Charge') . '",';
  echo '"' . xl('Insurance Payment') . '",';
  echo '"' . xl('Patient Payment') . '",';
  echo '"' . xl('Adjustment') . '",';
  echo '"' . xl('Open Balence') . '"' . "\n";

}
else { // not export
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('AR Report','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('AR Report','e')?></h2>

<form method='post' action='ar_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   <?php /*?><?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', false); ?><?php */?>
   <?php xl('From:','e')?>
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
 <tr bgcolor="#6CAEFF" style="font-weight:bold">
	  <td class="dehead"><?php xl('Encounter','e'  ) ?></td>
	  <td class="dehead"><?php xl('InsuranceName','e'  ) ?></td>
	  <td class="dehead"><?php xl('ProvierID','e'  ) ?></td>
	  <td class="dehead"><?php xl('RendarringProvider','e'  ) ?></td>
	  <td class="dehead"><?php xl('ExternalID','e'  ) ?></td>
	  <td class="dehead"><?php xl('PID','e'  ) ?></td>
	  <td class="dehead"><?php xl('PatientName','e'  ) ?></td>
	  <td class="dehead"><?php xl('Gender','e'  ) ?></td>
	  <td class="dehead"><?php xl('DOB','e'  ) ?></td>
	  <td class="dehead"><?php xl('ServiceLocation','e'  ) ?></td>
	  <td class="dehead"><?php xl('CPT','e'  ) ?></td>
	  <td class="dehead"><?php xl('DOS','e'  ) ?></td>
	  <td class="dehead"><?php xl('Charge','e'  ) ?></td>
	  <td class="dehead"><?php xl('InsurancePayment','e'  ) ?></td>
	  <td class="dehead"><?php xl('PatientPayment','e'  ) ?></td>
	  <td class="dehead"><?php xl('Adjustment','e'  ) ?></td>
	  <td class="dehead"><?php xl('Balence','e'  ) ?></td>

 </tr>
<?php
} // end not export

// If generating a report.
//
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date;
  $to_date   = $form_to_date;
  
$query = "select b.Encounter,ic.name as InsuranceName, ".
		"fe.provider_id As ProvierID, concat(u.lname, ' ', u.fname) as RendarringProvider, ".
		"pd.pubpid as ExternalID, ".
		"b.PID,concat(pd.lname, ' ', pd.fname,' ', pd.mname) AS PatientName, ".
		"pd.sex as Gender,pd.DOB, fe.facility as ServiceLocation, ".
		"b.code AS CPT, Date(fe.date) as DOS,b.fee as Charge, ".
		"IF((aa.payer_type = 1 OR aa.payer_type = 2 OR aa.payer_type = 3), ".
		"aa.pay_amount, 0.00) as InsurancePayment, ".
		"IF(aa.payer_type = 0, pay_amount,0.00) AS PatientPayment, ".
		"(select SUM(adj_amount) from ar_activity where encounter = b.encounter ".
		"and code = b.code group by code) as Adjustment, ".
		"(b.fee - (IF((aa.payer_type = 1 OR aa.payer_type = 2 OR aa.payer_type = 3), ".
		"aa.pay_amount, 0.00) + IF(aa.payer_type = 0, pay_amount,0.00))) as Balence ".
		"from form_encounter fe ".
		"left join billing b on fe.encounter = b.encounter ".
		"inner join users u on fe.provider_id = u.id ".
		"inner join patient_data pd on b.pid = pd.pid ".
		"left join ar_activity aa on aa.encounter = b.encounter ".
		"left join ar_session ar on ar.session_id = aa.session_id ".
		"left join insurance_companies ic on ic.id = ar.payer_id ".
		"where b.activity = 1 and b.code_type = 'CPT4' ".
		"AND b.date >= '$from_date 00:00:00' AND b.date <= '$to_date 23:59:59' ".
		"group by b.encounter, b.code ";


  $res = sqlStatement($query);
  while ($row = sqlFetchArray($res)) {
    thisLineItem($row);
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
