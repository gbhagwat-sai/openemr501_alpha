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
    echo '"' . addslashes($row['ExternalID' ]) . '",';
    echo '"' . addslashes($row['PatientName' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['DOB'  ])) . '",';
    echo '"' . addslashes($row['RenderingProvider' ]) . '",';
    echo '"' . addslashes($row['ServiceLocation' ]) . '",';
    echo '"' . addslashes($row['Encounter' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['DOS'  ])) . '",';
    echo '"' . addslashes($row['InsuranceComp' ]) . '",';
    echo '"' . addslashes($row['PolicyNo' ]) . '",';
    echo '"' . addslashes($row['CPTCodes' ]) . '",';
    echo '"' . addslashes($row['DX' ]) . '",';
    echo '"' . addslashes($row['Diagnosis' ]) . '",';
    echo '"' . addslashes($row['TotalCharge' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['CreatedDate'  ])) . '",';
    echo '"' . addslashes($row['CreatedBy' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['TransmissionDate'  ])) . '",';
    echo '"' . addslashes($row['EncounterStatus' ]) . '"' . "\n";
  }
  else {
?>
 <tr bgcolor="#CEE4FF">
  <td class="detail"><?php echo $row['ExternalID' ]; ?></td>
  <td class="detail"><?php echo $row['PatientName' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['DOB'  ]); ?></td>
  <td class="detail"><?php echo $row['RenderingProvider' ]; ?></td>
  <td class="detail"><?php echo $row['ServiceLocation' ]; ?></td>
  <td class="detail"><?php echo $row['Encounter' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['DOS'  ]); ?></td>
  <td class="detail"><?php echo $row['InsuranceComp' ]; ?></td>
  <td class="detail"><?php echo $row['PolicyNo' ]; ?></td>
  <td class="detail"><?php echo $row['CPTCodes' ]; ?></td>
  <td class="detail"><?php echo $row['DX' ]; ?></td>
  <td class="detail"><?php echo $row['Diagnosis' ]; ?></td>
  <td class="detail"><?php echo $row['TotalCharge' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['CreatedDate'  ]); ?></td>
  <td class="detail"><?php echo $row['CreatedBy' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['TransmissionDate'  ]); ?></td>
  <td class="detail"><?php echo $row['EncounterStatus' ]; ?></td>
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
  header("Content-Disposition: attachment; filename=iPhone_Charge_Entry_Report.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  echo '"' . xl('External ID') . '",';
  echo '"' . xl('Patient Name') . '",';
  echo '"' . xl('DOB') . '",';
  echo '"' . xl('Rendering Provider') . '",';
  echo '"' . xl('Service Location') . '",';
  echo '"' . xl('Encounter') . '",';
  echo '"' . xl('DOS') . '",';
  echo '"' . xl('Insurance Company') . '",';
  echo '"' . xl('Policy No') . '",';
  echo '"' . xl('CPT Codes') . '",';
  echo '"' . xl('DX') . '",';
  echo '"' . xl('Diagnosis') . '",';
  echo '"' . xl('Total Charge') . '",';
  echo '"' . xl('Created Date') . '",';
  echo '"' . xl('Created By') . '",';
  echo '"' . xl('Transmission Date') . '",';
  echo '"' . xl('Encounter Status') . '"' . "\n";

}
else { // not export
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('iPhone Charge Entry Report','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('iPhone Charge Entry Report','e')?></h2>

<form method='post' action='iphone_charge_entry.php'>

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
	  <td class="dehead"><?php xl('ExternalID','e'  ) ?></td>
	  <td class="dehead"><?php xl('PatientName','e'  ) ?></td>
	  <td class="dehead"><?php xl('DOB','e'  ) ?></td>
	  <td class="dehead"><?php xl('RenderingProvider','e'  ) ?></td>
	  <td class="dehead"><?php xl('ServiceLocation','e'  ) ?></td>
	  <td class="dehead"><?php xl('Encounter','e'  ) ?></td>
	  <td class="dehead"><?php xl('DOS','e'  ) ?></td>
	  <td class="dehead"><?php xl('InsuranceComp','e'  ) ?></td>
	  <td class="dehead"><?php xl('PolicyNo','e'  ) ?></td>
	  <td class="dehead"><?php xl('CPTCodes','e'  ) ?></td>
	  <td class="dehead"><?php xl('DX','e'  ) ?></td>
	  <td class="dehead"><?php xl('Diagnosis','e'  ) ?></td>
	  <td class="dehead"><?php xl('TotalCharge','e'  ) ?></td>
	  <td class="dehead"><?php xl('CreatedDate','e'  ) ?></td>
	  <td class="dehead"><?php xl('CreatedBy','e'  ) ?></td>
	  <td class="dehead"><?php xl('TransmissionDate','e'  ) ?></td>
	  <td class="dehead"><?php xl('EncounterStatus','e'  ) ?></td>
 </tr>
<?php
} // end not export

// If generating a report.
//
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date;
  $to_date   = $form_to_date;
  
$query = "select pd.PUBPID as ExternalID,CONCAT(pd.fname,' ',pd.mname,' ',pd.lname) as PatientName, pd.DOB, CONCAT(u.fname,' ',u.mname,' ',u.lname) as RenderingProvider,
fe.facility as ServiceLocation,d.Encounter,date(d.DOS) as DOS,
(select ic.name from insurance_data id join insurance_companies ic 
on ic.id = id.provider where id.type = 'primary' and id.pid = fe.pid 
and id.id in (select MAX(id) from insurance_data where type = 'primary' and 
pid = fe.pid) limit 1) as InsuranceComp,
(select id.policy_number from insurance_data id where id.type = 'primary'  
and id.pid = fe.pid and id.id in (select MAX(id) from insurance_data where type = 'primary' and
pid = fe.pid) limit 1) as PolicyNo, group_concat(IF(b.code_type = 'CPT4', b.code,NULL) SEPARATOR ' , ') AS CPTCodes, group_concat(IF(b.code_type = 'ICD9',b.code,NULL) SEPARATOR ' , ') AS DX, CONCAT(Dx1,',',Dx2,',',Dx3,',',Dx4) as Diagnosis, SUM(b.fee) AS TotalCharge,
date(d.datetimecreated) as CreatedDate,
(select CONCAT(u.fname,' ',u.mname,' ',u.lname) from users where id = d.userID) 
as CreatedBy,Date(b.bill_date) as TransmissionDate, cs.status as EncounterStatus
from diagnosis d inner join form_encounter fe on fe.encounter = d.encounter
inner join billing b on b.encounter = d.encounter
inner join patient_data pd on pd.pid = d.pid
inner join users u on u.id = fe.provider_id
left join claim_status cs on cs.id = fe.claim_status_id 
where b.activity = 1 and d.datetimecreated >='$from_date 00:00:00'
and d.datetimecreated <='$to_date 23:59:59' group by d.encounter
 ";



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
