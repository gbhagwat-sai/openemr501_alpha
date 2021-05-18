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
    echo '"' . addslashes($row['PID' ]) . '",';
	echo '"' . addslashes($row['PatientName' ]) . '",';
	echo '"' . addslashes($row['FirstName' ]) . '",';
	echo '"' . addslashes($row['MiddleName' ]) . '",';
	echo '"' . addslashes($row['LastName' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['DOB'  ])) . '",';
	echo '"' . addslashes($row['CaseName' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['DOS'  ])) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['created_date'  ])) . '",';
	echo '"' . addslashes($row['encounter' ]) . '",';
	echo '"' . addslashes($row['RenderingProviderName' ]) . '",';
	echo '"' . addslashes($row['ServiceLocation' ]) . '",';
	echo '"' . addslashes($row['CPT1' ]) . '",';
	echo '"' . addslashes($row['Unit1' ]) . '",';
	echo '"' . addslashes($row['Mod1' ]) . '",';
	echo '"' . addslashes($row['CPT2' ]) . '",';
	echo '"' . addslashes($row['Unit2' ]) . '",';
	echo '"' . addslashes($row['Mod2' ]) . '",';
	echo '"' . addslashes($row['CPT3' ]) . '",';
	echo '"' . addslashes($row['Unit3' ]) . '",';
	echo '"' . addslashes($row['Mod3' ]) . '",';
	echo '"' . addslashes($row['CPT4' ]) . '",';
	echo '"' . addslashes($row['Unit4' ]) . '",';
	echo '"' . addslashes($row['Mod4' ]) . '",';
	echo '"' . addslashes($row['CPT5' ]) . '",';
	echo '"' . addslashes($row['Unit5' ]) . '",';
	echo '"' . addslashes($row['Mod5' ]) . '",';
	echo '"' . addslashes($row['CPT6' ]) . '",';
	echo '"' . addslashes($row['Unit6' ]) . '",';
	echo '"' . addslashes($row['Mod6' ]) . '",';
	echo '"' . addslashes($row['DX1' ]) . '",';
	echo '"' . addslashes($row['DX2' ]) . '",';
	echo '"' . addslashes($row['DX3' ]) . '",';
	echo '"' . addslashes($row['DX4' ]) . '",';
	echo '"' . addslashes($row['TotalCharge' ]) . '",';
	echo '"' . addslashes($row['PrimaryInsuranceCompany' ]) . '",';
	echo '"' . addslashes($row['SecondaryInsuranceCompany' ]) . '",';
	echo '"' . addslashes($row['EncounterStatus' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['TransmittedDate' ])) . '"' . "\n";
  }
  else {
?>
 <tr bgcolor="#CEE4FF">
  <td class="detail"><?php echo $row['PID' ]; ?></td>
  <td class="detail"><?php echo $row['PatientName' ]; ?></td>
  <td class="detail"><?php echo $row['FirstName' ]; ?></td>
  <td class="detail"><?php echo $row['MiddleName' ]; ?></td>
  <td class="detail"><?php echo $row['LastName' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['DOB'  ]); ?></td>
  <td class="detail"><?php echo $row['CaseName' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['DOS'  ]); ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['created_date'  ]); ?></td>
  <td class="detail"><?php echo $row['encounter' ]; ?></td>
  <td class="detail"><?php echo $row['RenderingProviderName' ]; ?></td>
  <td class="detail"><?php echo $row['ServiceLocation' ]; ?></td>
  <td class="detail"><?php echo $row['CPT1' ]; ?></td>
  <td class="detail"><?php echo $row['Unit1' ]; ?></td>
  <td class="detail"><?php echo $row['Mod1' ]; ?></td>
  <td class="detail"><?php echo $row['CPT2' ]; ?></td>
  <td class="detail"><?php echo $row['Unit2' ]; ?></td>
  <td class="detail"><?php echo $row['Mod2' ]; ?></td>
  <td class="detail"><?php echo $row['CPT3' ]; ?></td>
  <td class="detail"><?php echo $row['Unit3' ]; ?></td>
  <td class="detail"><?php echo $row['Mod3' ]; ?></td>
  <td class="detail"><?php echo $row['CPT4' ]; ?></td>
  <td class="detail"><?php echo $row['Unit4' ]; ?></td>
  <td class="detail"><?php echo $row['Mod4' ]; ?></td>
  <td class="detail"><?php echo $row['CPT5' ]; ?></td>
  <td class="detail"><?php echo $row['Unit5' ]; ?></td>
  <td class="detail"><?php echo $row['Mod5' ]; ?></td>
  <td class="detail"><?php echo $row['CPT6' ]; ?></td>
  <td class="detail"><?php echo $row['Unit6' ]; ?></td>
  <td class="detail"><?php echo $row['Mod6' ]; ?></td>
  <td class="detail"><?php echo $row['DX1' ]; ?></td>
  <td class="detail"><?php echo $row['DX2' ]; ?></td>
  <td class="detail"><?php echo $row['DX3' ]; ?></td>
  <td class="detail"><?php echo $row['DX4' ]; ?></td>
  <td class="detail"><?php echo $row['TotalCharge' ]; ?></td>
  <td class="detail"><?php echo $row['PrimaryInsuranceCompany' ]; ?></td>
  <td class="detail"><?php echo $row['SecondaryInsuranceCompany' ]; ?></td>
  <td class="detail"><?php echo $row['EncounterStatus' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['TransmittedDate'  ]); ?></td>
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
  header("Content-Disposition: attachment; filename=Transmission_Report.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  echo '"' . xl('PID') . '",';
  echo '"' . xl('PatientName') . '",';
  echo '"' . xl('FirstName') . '",';
  echo '"' . xl('MiddleName') . '",';
  echo '"' . xl('LastName') . '",';
  echo '"' . xl('DOB') . '",';
  echo '"' . xl('CaseName') . '",';
  echo '"' . xl('DOS') . '",';
  echo '"' . xl('created_date') . '",';
  echo '"' . xl('encounter') . '",';
  echo '"' . xl('RenderingProviderName') . '",';
  echo '"' . xl('ServiceLocation') . '",';
  echo '"' . xl('CPT1') . '",';
  echo '"' . xl('Unit1') . '",';
  echo '"' . xl('Mod1') . '",';
  echo '"' . xl('CPT2') . '",';
  echo '"' . xl('Unit2') . '",';
  echo '"' . xl('Mod2') . '",';
  echo '"' . xl('CPT3') . '",';
  echo '"' . xl('Unit3') . '",';
  echo '"' . xl('Mod3') . '",';
  echo '"' . xl('CPT4') . '",';
  echo '"' . xl('Unit4') . '",';
  echo '"' . xl('Mod4') . '",';
  echo '"' . xl('CPT5') . '",';
  echo '"' . xl('Unit5') . '",';
  echo '"' . xl('Mod5') . '",';
  echo '"' . xl('CPT6') . '",';
  echo '"' . xl('Unit6') . '",';
  echo '"' . xl('Mod6') . '",';
  echo '"' . xl('DX1') . '",';
  echo '"' . xl('DX2') . '",';
  echo '"' . xl('DX3') . '",';
  echo '"' . xl('DX4') . '",';
  echo '"' . xl('TotalCharge') . '",';
  echo '"' . xl('PrimaryInsuranceCompany') . '",';
  echo '"' . xl('SecondaryInsuranceCompany') . '",';
  echo '"' . xl('EncounterStatus') . '",';
  echo '"' . xl('TransmittedDate') . '"' . "\n";

}
else { // not export
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Transmission Report','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('Transmission Report','e')?></h2>

<form method='post' action='transmission_report.php'>

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
	  <td class="dehead"><?php xl('PID','e'  ) ?></td>
	  <td class="dehead"><?php xl('PatientName','e'  ) ?></td>
	  <td class="dehead"><?php xl('FirstName','e'  ) ?></td>
	  <td class="dehead"><?php xl('MiddleName','e'  ) ?></td>
	  <td class="dehead"><?php xl('LastName','e'  ) ?></td>
	  <td class="dehead"><?php xl('DOB','e'  ) ?></td>
	  <td class="dehead"><?php xl('CaseName','e'  ) ?></td>
	  <td class="dehead"><?php xl('DOS','e'  ) ?></td>
	  <td class="dehead"><?php xl('created_date','e'  ) ?></td>
	  <td class="dehead"><?php xl('encounter','e'  ) ?></td>
	  <td class="dehead"><?php xl('RenderingProviderName','e'  ) ?></td>
	  <td class="dehead"><?php xl('ServiceLocation','e'  ) ?></td>
	  <td class="dehead"><?php xl('CPT1','e'  ) ?></td>
	  <td class="dehead"><?php xl('Unit1','e'  ) ?></td>
	  <td class="dehead"><?php xl('Mod1','e'  ) ?></td>
	  <td class="dehead"><?php xl('CPT2','e'  ) ?></td>
	  <td class="dehead"><?php xl('Unit2','e'  ) ?></td>
	  <td class="dehead"><?php xl('Mod2','e'  ) ?></td>
	  <td class="dehead"><?php xl('CPT3','e'  ) ?></td>
	  <td class="dehead"><?php xl('Unit3','e'  ) ?></td>
	  <td class="dehead"><?php xl('Mod3','e'  ) ?></td>
	  <td class="dehead"><?php xl('CPT4','e'  ) ?></td>
	  <td class="dehead"><?php xl('Unit4','e'  ) ?></td>
	  <td class="dehead"><?php xl('Mod4','e'  ) ?></td>
	  <td class="dehead"><?php xl('CPT5','e'  ) ?></td>
	  <td class="dehead"><?php xl('Unit5','e'  ) ?></td>
	  <td class="dehead"><?php xl('Mod5','e'  ) ?></td>
	  <td class="dehead"><?php xl('CPT6','e'  ) ?></td>
	  <td class="dehead"><?php xl('Unit6','e'  ) ?></td>
	  <td class="dehead"><?php xl('Mod6','e'  ) ?></td>
	  <td class="dehead"><?php xl('DX1','e'  ) ?></td>
	  <td class="dehead"><?php xl('DX2','e'  ) ?></td>
	  <td class="dehead"><?php xl('DX3','e'  ) ?></td>
	  <td class="dehead"><?php xl('DX4','e'  ) ?></td>
	  <td class="dehead"><?php xl('TotalCharge','e'  ) ?></td>
	  <td class="dehead"><?php xl('PrimaryInsuranceCompany','e'  ) ?></td>
	  <td class="dehead"><?php xl('SecondaryInsuranceCompany','e'  ) ?></td>
	  <td class="dehead"><?php xl('EncounterStatus','e'  ) ?></td>
	  <td class="dehead"><?php xl('TransmittedDate','e'  ) ?></td>

 </tr>
<?php
} // end not export

// If generating a report.
//
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date;
  $to_date   = $form_to_date;
  
$query = "select b.pid AS PID, ".
"Concat(pd.lname, ' ', pd.fname, ' ', pd.mname) AS PatientName, ".
"pd.fname as FirstName, pd.mname AS MiddleName, pd.lname AS LastName,Date(pd.DOB) AS DOB, ".
"(select ic.name from insurance_companies ic,insurance_data i ".
"where i.pid = pd.pid and i.provider = ic.id and i.type = 'primary') as CaseName, ".
"DATE(fe.date) AS DOS, DATE(b.date) AS created_date, b.encounter, ".
"Concat(u.lname, ' ', u.fname, ' ', u.mname) AS RenderingProviderName, ".
"fe.facility as ServiceLocation, ".
"(select code from billing where code_type = 'CPT4' and id = ".
"(select min(id) from billing where code_type='CPT4' and encounter=fe.encounter) ".
"and encounter=fe.encounter ) as CPT1, ".
"(select units from billing where code = CPT1 and encounter=fe.encounter) as Unit1, ".
"(SELECT modifier from billing where code = CPT1 and encounter = fe.encounter) AS Mod1, ".
"(select code from billing where code_type = 'CPT4' and id = ".
"((select min(id) from billing where code_type='CPT4' and encounter=fe.encounter )+1) ".
"and encounter=fe.encounter ) as CPT2, ".
"(select units from billing where code = CPT2 and encounter=fe.encounter) as Unit2, ".
"(select modifier from billing where code = CPT2 and encounter = fe.encounter) as Mod2, ".
"(select code from billing where code_type = 'CPT4' and id = ".
"((select min(id) from billing where code_type='CPT4' and encounter=fe.encounter )+2) ".
"and encounter=fe.encounter ) as CPT3, ".
"(select units from billing where code = CPT3 and encounter=fe.encounter) as Unit3, ".
"(select modifier from billing where code = CPT3 and encounter = fe.encounter) as Mod3, ".
"(select code from billing where code_type = 'CPT4' and id = ".
"((select min(id) from billing where code_type='CPT4' and encounter=fe.encounter )+3) ".
"and encounter=fe.encounter ) as CPT4, ".
"(select units from billing where code = CPT4 and encounter=fe.encounter) as Unit4, ".
"(select modifier from billing where code = CPT4 and encounter = fe.encounter) as Mod4, ".
"(select code from billing where code_type = 'CPT4' and id = ".
"((select min(id) from billing where code_type='CPT4' and encounter=fe.encounter )+4) ".
"and encounter=fe.encounter ) as CPT5, ".
"(select units from billing where code = CPT5 and encounter=fe.encounter) as Unit5, ".
"(select modifier from billing where code = CPT5 and encounter = fe.encounter) as Mod5, ".
"(select code from billing where code_type = 'CPT4' and id = ".
"((select min(id) from billing where code_type='CPT4' and encounter=fe.encounter )+5) ".
"and encounter=fe.encounter ) as CPT6, ".
"(select units from billing where code = CPT6 and encounter=fe.encounter) as Unit6, ".
"(select modifier from billing where code = CPT6 and encounter = fe.encounter) as Mod6, ".
"(select code from billing where code_type = 'ICD9' and id = ".
"(select min(id) from billing where code_type='ICD9' and encounter=fe.encounter )  ".
"and encounter=fe.encounter ) as DX1, ".
"(select code from billing where code_type = 'ICD9' and id = ".
"((select min(id) from billing where code_type='ICD9' and encounter=fe.encounter )+1) ".
"and encounter=fe.encounter ) as DX2, ".
"(select code from billing where code_type = 'ICD9' and id = ".
"((select min(id) from billing where code_type='ICD9' and encounter=fe.encounter )+2) ".
"and encounter=fe.encounter ) as DX3,".
"(select code from billing where code_type = 'ICD9' and id = ".
"((select min(id) from billing where code_type='ICD9' and encounter=fe.encounter )+3) ".
"and encounter=fe.encounter ) as DX4,".
"SUM(b.fee) AS TotalCharge,".
"(select ic.name from insurance_companies ic,insurance_data i ".
"where i.pid = pd.pid and i.provider = ic.id and i.type = 'primary') as PrimaryInsuranceCompany,".
"(select ic.name from insurance_companies ic,insurance_data i ".
"where i.pid = pd.pid and i.provider = ic.id and i.type = 'secondary') ".
"as  SecondaryInsuranceCompany,".
"es.status as EncounterStatus,Date(MAX(c.bill_time)) as TransmittedDate ". 
"from billing b inner join form_encounter fe on b.encounter = fe.encounter ".
"inner join users u on u.id = fe.provider_id ".
"inner join forms f on fe.encounter =  f.encounter ".
"inner join patient_data pd on fe.pid = pd.pid ".
"inner join encounter_status es ON fe.encounter = es.encounter ".
"inner join claims c on c.encounter_id= fe.encounter ".
"where es.Status_Date = ".
"( select max(Status_Date) from encounter_status where encounter = fe.encounter) ".
"AND b.date >= '$from_date 00:00:00' AND b.date <= '$to_date 23:59:59' ".
"group by b.encounter ";


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
