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
  

  if ($_POST['form_csvexport']) 
  {
     echo '"' . addslashes($row['billEHRPatientID' ]) . '",';
     echo '"' . addslashes($row['ExternalID' ]) . '",';
     echo '"' . addslashes($row['PatientName' ]) . '",';
     echo '"' . addslashes(oeFormatShortDate($row['DOB'])). '",';
     echo '"' . addslashes(oeFormatShortDate($row['UploadedDate'  ])) . '",';
     echo '"' . addslashes($row['UploadedBy' ])  . '"' . "\n";
    
  }
  else 
  {
?>
 <tr bgcolor="#CEE4FF">
   <td class="detail"><?php echo $row['billEHRPatientID' ]; ?></td>
   <td class="detail"><?php echo $row['ExternalID' ]; ?></td>
   <td class="detail"><?php echo $row['PatientName' ]; ?></td>
   <td class="detail"><?php echo oeFormatShortDate($row['DOB']); ?></td>
   <td class="detail"><?php echo oeFormatShortDate($row['UploadedDate']); ?></td>
   <td class="detail"><?php echo $row['UploadedBy' ]; ?></td>
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
  header("Content-Disposition: attachment; filename=Facesheet_Report.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
 
  echo '"' . xl('billEHR PatientID') . '",';
  echo '"' . xl('ExternalID') . '",';
  echo '"' . xl('PatientName') . '",';
  echo '"' . xl('DOB') . '",';
  echo '"' . xl('UploadedDate'). '",';
  echo '"' . xl('UploadedBy').'"'. "\n";
}
else { // not export
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Facesheet Upload Report','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('Facesheet Upload Report','e')?></h2>

<form method='post' action='facesheet_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   <?php /*?><?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', false); ?><?php */?>
   <?php xl('Upload Date From:','e')?>
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
   <input type='button' value='<?php xl('Print','e'); ?>' onclick='window.print()' hidden/>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>
 <tr bgcolor="#6CAEFF" style="font-weight:bold">  
	  <td class="dehead"><?php xl('billEHR PatientID','e') ?></td>
          <td class="dehead"><?php xl('ExternalID','e') ?></td>
          <td class="dehead"><?php xl('PatientName','e') ?></td>
	  <td class="dehead"><?php xl('DOB','e') ?></td>
          <td class="dehead"><?php xl('UploadedDate','e') ?></td>
	  <td class="dehead"><?php xl('UploadedBy','e') ?></td> 
 </tr>
<?php
} // end not export

// If generating a report.
//
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date;
  $to_date   = $form_to_date;
  
  
  //$query = "CALL usp_get_Facesheet_Report('".$form_from_date."', '".$form_to_date."');";
  $query = "SELECT date(f.createdDate) as UploadedDate,
       p.pubpid as ExternalID,
       f.billEHRPatientID as billEHRPatientID,
       Concat(p.lname, ' ', p.fname, ' ', p.mname) AS PatientName,
       p.DOB,
	   concat(u.fname,'  ',u.lname) as UploadedBy FROM facesheet as f 
left join patient_data as p on f.billEHRPatientID = p.billEHRPatientID
left join users as u on f.createdBy = u.id where f.isActive=1   
AND f.createdDate >= '$form_from_date 00:00:00' AND f.createdDate <= '$form_to_date 23:59:59'";

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
