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
    echo '"' . addslashes($row['PID'  ]) . '",';
    echo '"' . addslashes($row['Patient_Name' ]) . '",';
	echo '"' . addslashes($row['Encounter' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['DOS'  ])) . '",';
    echo '"' . addslashes(oeFormatShortDate($row['Entered_Date'  ])) . '",';
    echo '"' . addslashes($row['Person_Entered'  ]) . '",';
	echo '"' . addslashes($row['Provider'  ]) . '",';
	echo '"' . addslashes($row['Location'  ]) . '",';
	echo '"' . addslashes($row['CPT1'  ]) . '",';
	echo '"' . addslashes($row['CPT2'  ]) . '",';
	echo '"' . addslashes($row['CPT3'  ]) . '",';
	echo '"' . addslashes($row['CPT4'  ]) . '",';
	echo '"' . addslashes($row['CPT5'  ]) . '",';
	echo '"' . addslashes($row['CPT6'  ]) . '",';
	echo '"' . addslashes($row['DX1'  ]) . '",';
	echo '"' . addslashes($row['DX2'  ]) . '",';
	echo '"' . addslashes($row['DX3'  ]) . '",';
	echo '"' . addslashes($row['DX4'  ]) . '",';
    echo '"' . addslashes(oeFormatMoney($row['Charge'])) . '"' . "\n";
  }
  else {
?>
 <tr bgcolor="#CEE4FF">
  <td class="detail"><?php echo $row['PID'  ]; ?></td>
  <td class="detail"><?php echo $row['Patient_Name' ]; ?></td>
  <td class="detail"><?php echo $row['Encounter'  ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['DOS'  ]); ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['Entered_Date'  ]); ?></td>
  <td class="detail"><?php echo $row['Person_Entered']; ?></td>
  <td class="detail"><?php echo $row['Provider' ]; ?></td>
  <td class="detail"><?php echo $row['Location'   ]; ?></td>
  <td class="detail"><?php echo $row['CPT1']; ?></td>
  <td class="detail"><?php echo $row['CPT2']; ?></td>
  <td class="detail"><?php echo $row['CPT3']; ?></td>
  <td class="detail"><?php echo $row['CPT4']; ?></td>
  <td class="detail"><?php echo $row['CPT5']; ?></td>
  <td class="detail"><?php echo $row['CPT6']; ?></td>
  <td class="detail"><?php echo $row['DX1']; ?></td>
  <td class="detail"><?php echo $row['DX2']; ?></td>
  <td class="detail"><?php echo $row['DX3']; ?></td>
  <td class="detail"><?php echo $row['DX4']; ?></td>
  <td class="detail"><?php echo oeFormatMoney($row['Charge']); ?></td>
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
  header("Content-Disposition: attachment; filename=Providerwise_Summary_Report.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  echo '"' . xl('PID') . '",';
  echo '"' . xl('Patient_Name') . '",';
  echo '"' . xl('Encounter') . '",';
  echo '"' . xl('DOS') . '",';
  echo '"' . xl('Entered_Date') . '",';
  echo '"' . xl('Person_Entered') . '",';
  echo '"' . xl('Provider') . '",';
  echo '"' . xl('Location') . '",';
  echo '"' . xl('CPT1') . '",';
  echo '"' . xl('CPT2') . '",';
  echo '"' . xl('CPT3') . '",';
  echo '"' . xl('CPT4') . '",';
  echo '"' . xl('CPT5') . '",';
  echo '"' . xl('CPT6') . '",';
  echo '"' . xl('DX1') . '",';
  echo '"' . xl('DX2') . '",';
  echo '"' . xl('DX3') . '",';
  echo '"' . xl('DX4') . '",';
  echo '"' . xl('Charge') . '"' . "\n";
}
else { // not export
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Providerwise Summary Report','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('Providerwise Summary Report','e')?></h2>

<form method='post' action='pproviderwise_summary_report.php'>

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
  <td class="dehead"><?php xl('PID','e'  ) ?> </td>
  <td class="dehead"><?php xl('Patient_Name','e'       ) ?></td>
  <td class="dehead"><?php xl('Encounter','e'  ) ?></td>
  <td class="dehead"><?php xl('DOS','e'     ) ?></td>
  <td class="dehead"><?php xl('Entered_Date','e') ?></td>
  <td class="dehead"><?php xl('Person_Entered','e' ) ?></td>
  <td class="dehead"><?php xl('Provider','e' ) ?></td>
  <td class="dehead"><?php xl('Location','e') ?></td>
  <td class="dehead"><?php xl('CPT1','e' ) ?></td>
  <td class="dehead"><?php xl('CPT2','e' ) ?></td>
  <td class="dehead"><?php xl('CPT3','e' ) ?></td>
  <td class="dehead"><?php xl('CPT4','e' ) ?></td>
  <td class="dehead"><?php xl('CPT5','e' ) ?></td>
  <td class="dehead"><?php xl('CPT6','e' ) ?></td>
  <td class="dehead"><?php xl('DX1','e' ) ?></td>
  <td class="dehead"><?php xl('DX2','e' ) ?></td>
  <td class="dehead"><?php xl('DX3','e' ) ?></td>
  <td class="dehead"><?php xl('DX4','e' ) ?></td>
  <td class="dehead"><?php xl('Charge','e'   ) ?></td>
 </tr>
<?php
} // end not export

// If generating a report.
//
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date;
  $to_date   = $form_to_date;
  
$query = "select b.pid As PID, Concat(pd.lname, ' ', pd.fname) AS Patient_Name, ".
		"b.encounter as Encounter, Date(fe.date) AS DOS, ".
		"Date(b.date) AS Entered_Date, ".
		"f.user AS Person_Entered,Concat(u.lname, ' ', u.fname) AS Provider, ".
		"fe.facility as Location,".
		"(select code from billing where code_type = 'CPT4' and id = ".
		"(select min(id) from billing where code_type='CPT4' and encounter=fe.encounter) ".
		"and encounter=fe.encounter ) as CPT1, ".
		"(select code from billing where code_type = 'CPT4' and id = ".
		"((select min(id) from billing where code_type='CPT4' and encounter=fe.encounter )+1) ".
		"and encounter=fe.encounter ) as CPT2, ".
		"(select code from billing where code_type = 'CPT4' and id = ".
		"((select min(id) from billing where code_type='CPT4' and encounter=fe.encounter )+2) ".
		"and encounter=fe.encounter ) as CPT3,".
		"(select code from billing where code_type = 'CPT4' and id = ".
		"((select min(id) from billing where code_type='CPT4' and encounter=fe.encounter )+3) ".
		"and encounter=fe.encounter ) as CPT4,".
		"(select code from billing where code_type = 'CPT4' and id = " .
		"((select min(id) from billing where code_type='CPT4' and encounter=fe.encounter )+4) ".
		"and encounter=fe.encounter ) as CPT5,".
		"(select code from billing where code_type = 'CPT4' and id =".
		"((select min(id) from billing where code_type='CPT4' and encounter=fe.encounter )+5)".
		"and encounter=fe.encounter ) as CPT6,".
		"(select code from billing where code_type = 'ICD9' and id =".
		"(select min(id) from billing where code_type='ICD9' and encounter=fe.encounter ) ".
		"and encounter=fe.encounter ) as DX1, ".
		"(select code from billing where code_type = 'ICD9' and id = ".
		"((select min(id) from billing where code_type='ICD9' and encounter=fe.encounter )+1)".
		"and encounter=fe.encounter ) as DX2, ".
		"(select code from billing where code_type = 'ICD9' and id = ".
		"((select min(id) from billing where code_type='ICD9' and encounter=fe.encounter )+2) ".
		"and encounter=fe.encounter ) as DX3, ".
		"(select code from billing where code_type = 'ICD9' and id = ".
		"((select min(id) from billing where code_type='ICD9' and encounter=fe.encounter )+3) ".
		"and encounter=fe.encounter ) as DX4, ".
		"SUM(b.fee) AS Charge from billing b  ".
		"inner join form_encounter fe on fe.encounter = b.encounter ".
		"inner join users u on u.id =  fe.provider_id ".
		"inner join forms f on fe.encounter =  f.encounter ".
		"inner join patient_data pd on fe.pid = pd.pid ".
		"where b.date >= '2013-04-10 00:00:00'  ".
		"and  b.date <= '2013-04-19 23:59:59' ".
		"group by b.encounter order by Patient_Name";

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
