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
	echo '"' . addslashes(oeFormatShortDate($row['RecievedDate'  ])) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['CheckDate'  ])) . '",';
	echo '"' . addslashes($row['InsuranceName' ]) . '",';
	echo '"' . addslashes($row['CheckNo' ]) . '",';
	echo '"' . addslashes($row['CheckAmount' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['PostingDate' ])) . '"' . "\n";
  }
  else {
?>
 <tr bgcolor="#CEE4FF">
  <td class="detail"><?php echo oeFormatShortDate($row['RecievedDate'  ]); ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['CheckDate'  ]); ?></td>
  <td class="detail"><?php echo $row['InsuranceName' ]; ?></td>
  <td class="detail"><?php echo $row['CheckNo' ]; ?></td>
  <td class="detail"><?php echo $row['CheckAmount' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['PostingDate'  ]); ?></td>
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
  header("Content-Disposition: attachment; filename=Invoice_Report.csv");
  header("Content-Description: File Transfer");
  // CSV headers:

  echo '"' . xl('Recieved Date') . '",';
  echo '"' . xl('Check Date') . '",';
  echo '"' . xl('Insurance Name') . '",';
  echo '"' . xl('Check Number') . '",';
  echo '"' . xl('Check Amount') . '",';
  echo '"' . xl('Posting Date') . '"' . "\n";

}
else { // not export
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Invoice Report','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('Invoice Report','e')?></h2>

<form method='post' action='invoice_report.php'>

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
	  <td class="dehead"><?php xl('Recieved Date','e'  ) ?></td>
	  <td class="dehead"><?php xl('Check Date','e'  ) ?></td>
	  <td class="dehead"><?php xl('Insurance Name','e'  ) ?></td>
	  <td class="dehead"><?php xl('Check No','e'  ) ?></td>
	  <td class="dehead"><?php xl('Check Amount','e'  ) ?></td>
	  <td class="dehead"><?php xl('Posting Date','e'  ) ?></td>

 </tr>
<?php
} // end not export

// If generating a report.
//
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date;
  $to_date   = $form_to_date;
  
$query = "select ar.deposit_date as RecievedDate,ar.check_date as CheckDate, ".
		"ic.name AS InsuranceName,ar.reference as CheckNo, ".
		"ar.pay_total AS CheckAmount,DATE(MAX(aa.post_time)) as PostingDate ".
		"from ar_session ar ".
		"left join ar_activity  aa ON ar.session_id = aa.session_id ".
		"inner Join insurance_companies ic on ar.payer_id = ic.id ".
		"group by ar.session_id";


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
