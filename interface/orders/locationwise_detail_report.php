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
  

  if ($_POST['form_csvexport']) {
   echo '"' . addslashes(oeFormatShortDate($row['CreatedDate'])) . '",';
   echo '"' . addslashes($row['encounterID']) . '",';
   echo '"' . addslashes($row['patientID']) . '",';
   echo '"' . addslashes($row['PatientName']) . '",';
   echo '"' . addslashes($row['FirstName']) . '",';
   echo '"' . addslashes($row['MiddleName']) . '",';
   echo '"' . addslashes($row['LastName']) . '",';
   echo '"' . addslashes(oeFormatShortDate($row['DOB'])) . '",';
   echo '"' . addslashes($row['CaseName']) . '",';
   echo '"' . addslashes(oeFormatShortDate($row['ServiceStartDate'])) . '",';
   echo '"' . addslashes(oeFormatShortDate($row['ServiceEndDate'])) . '",';
   echo '"' . addslashes(oeFormatShortDate($row['PostingDate'])) . '",';
   echo '"' . addslashes($row['BatchNumber']) . '",';
   echo '"' . addslashes($row['RendarringProvider']) . '",';
   echo '"' . addslashes($row['ServiceLocation']) . '",';
   echo '"' . addslashes($row['CPT']) . '",';
   echo '"' . addslashes($row['EAA']) . '",';
   echo '"' . addslashes($row['Modifier']) . '",';
   echo '"' . addslashes($row['DX']) . '",';
   echo '"' . addslashes($row['Units']) . '",';
   echo '"' . addslashes($row['TotalCharge']) . '",';
   echo '"' . addslashes($row['AdjustedCharges']) . '",';
   echo '"' . addslashes($row['PatientBalance']) . '",';
   echo '"' . addslashes($row['InsuranceBalance']) . '",';
   echo '"' . addslashes($row['TotalBalance']) . '",';
   echo '"' . addslashes($row['PaidAmount']) . '",';
   echo '"' . addslashes($row['PrimaryInsuranceCompany']) . '",';
   echo '"' . addslashes($row['PrimaryInsurancePlanName']) . '",';
   echo '"' . addslashes($row['SecondaryInsuranceCompany']) . '",';
   echo '"' . addslashes($row['SecondaryInsurancePlanName']) . '",';
   echo '"' . addslashes($row['EncounterStatus']) . '",';
   echo '"' . addslashes($row['Status']) . '"' . "\n";
  }
  else {
?>
 <tr style="font-family:Verdana, Arial, Helvetica, sans-serif ; font-size:10px" bgcolor="#C1EEFF">
 <td class="detail" ><?php echo oeFormatShortDate($row['CreatedDate']); ?></td>
  <td class="detail"><?php echo $row['encounterID']; ?></td>
  <td class="detail"><?php echo $row['patientID']; ?></td>
  <td class="detail"><?php echo $row['PatientName']; ?></td>
  <td class="detail"><?php echo $row['FirstName']; ?></td>
  <td class="detail"><?php echo $row['MiddleName']; ?></td>
  <td class="detail"><?php echo $row['LastName']; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['DOB']); ?></td>
  <td class="detail"><?php echo $row['CaseName']; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['ServiceStartDate']); ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['ServiceEndDate']); ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['PostingDate']); ?></td>
  <td class="detail"><?php echo $row['BatchNumber']; ?></td>
  <td class="detail"><?php echo $row['RendarringProvider']; ?></td>
  <td class="detail"><?php echo $row['ServiceLocation']; ?></td>
  <td class="detail"><?php echo $row['CPT']; ?></td>
  <td class="detail"><?php echo $row['EAA']; ?></td>
  <td class="detail"><?php echo $row['Modifier']; ?></td>
  <td class="detail"><?php echo $row['DX']; ?></td>
  <td class="detail"><?php echo $row['Units']; ?></td>
  <td class="detail"><?php echo $row['TotalCharge']; ?></td>
  <td class="detail"><?php echo $row['AdjustedCharges']; ?></td>
  <td class="detail"><?php echo $row['PatientBalance']; ?></td>
  <td class="detail"><?php echo $row['InsuranceBalance']; ?></td>
  <td class="detail"><?php echo $row['TotalBalance']; ?></td>
  <td class="detail"><?php echo $row['PaidAmount']; ?></td>
  <td class="detail"><?php echo $row['PrimaryInsuranceCompany']; ?></td>
  <td class="detail"><?php echo $row['PrimaryInsurancePlanName']; ?></td>
  <td class="detail"><?php echo $row['SecondaryInsuranceCompany']; ?></td>
  <td class="detail"><?php echo $row['SecondaryInsurancePlanName']; ?></td>
  <td class="detail"><?php echo $row['EncounterStatus'  ]; ?></td>
  <td class="detail"><?php echo $row['Status']; ?></td>
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
  header("Content-Disposition: attachment; filename=Location_Wise_Detail_Report.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
 echo '"' . xl('CreatedDate') . '",';
 echo '"' . xl('encounterID') . '",';
 echo '"' . xl('patientID') . '",';
 echo '"' . xl('PatientName') . '",';
 echo '"' . xl('FirstName') . '",';
 echo '"' . xl('MiddleName') . '",';
 echo '"' . xl('LastName') . '",';
 echo '"' . xl('DOB') . '",';
 echo '"' . xl('CaseName') . '",';
 echo '"' . xl('ServiceStartDate') . '",';
 echo '"' . xl('ServiceEndDate') . '",';
 echo '"' . xl('PostingDate') . '",';
 echo '"' . xl('BatchNumber') . '",';
 echo '"' . xl('RendarringProvider') . '",';
 echo '"' . xl('ServiceLocation') . '",';
 echo '"' . xl('CPT') . '",';
 echo '"' . xl('EAA') . '",';
 echo '"' . xl('Modifier') . '",';
 echo '"' . xl('DX') . '",';
 echo '"' . xl('Units') . '",';
 echo '"' . xl('TotalCharge') . '",';
 echo '"' . xl('AdjustedCharges') . '",';
 echo '"' . xl('PatientBalance') . '",';
 echo '"' . xl('InsuranceBalance') . '",';
 echo '"' . xl('TotalBalance') . '",';
 echo '"' . xl('PaidAmount') . '",';
 echo '"' . xl('PrimaryInsuranceCompany') . '",';
 echo '"' . xl('PrimaryInsurancePlanName') . '",';
 echo '"' . xl('SecondaryInsuranceCompany') . '",';
 echo '"' . xl('SecondaryInsurancePlanName') . '",';
 echo '"' . xl('EncounterStatus') . '",';
 echo '"' . xl('Status') . '"' . "\n";
}
else { // not export
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Locationwise Detail Report','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('Locationwise Detail Report','e')?></h2>

<form method='post' action='locationwise_detail_report.php'>

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
 <tr bgcolor="#5AD1E7" style="font-family:Verdana, Arial, Helvetica, sans-serif ; font-size:12px">
  <td class="dehead"><?php xl('CreatedDate','e'  ) ?></td>
  <td class="dehead"><?php xl('encounterID','e'       ) ?></td>
  <td class="dehead"><?php xl('patientID','e'  ) ?></td>
  <td class="dehead"><?php xl('PatientName','e'     ) ?></td>
  <td class="dehead"><?php xl('FirstName','e') ?></td>
  <td class="dehead"><?php xl('MiddleName','e' ) ?></td>
  <td class="dehead"><?php xl('LastName','e' ) ?></td>
  <td class="dehead"><?php xl('DOB','e'   ) ?></td>
  <td class="dehead"><?php xl('CaseName','e'  ) ?></td>
  <td class="dehead"><?php xl('ServiceStartDate','e'       ) ?></td>
  <td class="dehead"><?php xl('ServiceEndDate','e'  ) ?></td>
  <td class="dehead"><?php xl('PostingDate','e'     ) ?></td>
  <td class="dehead"><?php xl('BatchNumber','e') ?></td>
  <td class="dehead"><?php xl('RendarringProvider','e' ) ?></td>
  <td class="dehead"><?php xl('ServiceLocation','e' ) ?></td>
  <td class="dehead"><?php xl('CPT','e'   ) ?></td>
  <td class="dehead"><?php xl('EAA','e'  ) ?></td>
  <td class="dehead"><?php xl('Modifier','e'       ) ?></td>
  <td class="dehead"><?php xl('DX','e'  ) ?></td>
  <td class="dehead"><?php xl('Units','e'     ) ?></td>
  <td class="dehead"><?php xl('TotalCharge','e') ?></td>
  <td class="dehead"><?php xl('AdjustedCharges','e' ) ?></td>
  <td class="dehead"><?php xl('PatientBalance','e' ) ?></td>
  <td class="dehead"><?php xl('InsuranceBalance','e'   ) ?></td>
  <td class="dehead"><?php xl('TotalBalance','e'  ) ?></td>
  <td class="dehead"><?php xl('PaidAmount','e'       ) ?></td>
  <td class="dehead"><?php xl('PrimaryInsuranceCompany','e'  ) ?></td>
  <td class="dehead"><?php xl('PrimaryInsurancePlanName','e'     ) ?></td>
  <td class="dehead"><?php xl('SecondaryInsuranceCompany','e') ?></td>
  <td class="dehead"><?php xl('SecondaryInsurancePlanName','e' ) ?></td>
  <td class="dehead"><?php xl('EncounterStatus','e' ) ?></td>
  <td class="dehead"><?php xl('Status','e'   ) ?></td>
 </tr>
<?php
} // end not export

// If generating a report.
//
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date;
  $to_date   = $form_to_date;

  $query = 	
	"select  Date(b.date) as CreatedDate,b.Encounter as encounterID, pd.PID as patientID,".
	"Concat(pd.lname, ' ', pd.fname, ' ', pd.mname) AS PatientName,".
	"pd.fname as FirstName, pd.mname AS MiddleName, pd.lname AS LastName, Date(pd.DOB) AS DOB,".
	"(select ic.name from insurance_companies ic,insurance_data i where i.pid = pd.pid and 	".
	"i.provider = ic.id and i.type = 'primary') as CaseName,".
	"Date(fe.date) as ServiceStartDate, Date(fe.date) as ServiceEndDate,".
	"DATE(MAX(post_time)) as PostingDate,".
	"fe.batch_id as BatchNumber,".
	"Concat(u.lname, ' ', u.fname, ' ', u.mname) AS RendarringProvider,".
	"fe.facility AS ServiceLocation, b.code AS CPT,".
	"(select NP_EAA from cpt_eaa where Code=b.code) as EAA,".
 	"b.Modifier as Modifier, b.justify as DX, b.Units as Units, b.fee as TotalCharge,".
	"IFNULL((select SUM(adj_amount) from ar_activity where encounter = fe.encounter ".
	"and code = b.code group by code),0) as AdjustedCharges,".
	"IFNULL((b.fee-(select SUM(pay_amount) from ar_activity where encounter = b.encounter ".
	"and code = b.code and payer_type = 1 AND ".
	"b.pid IN (SELECT PID from insurance_data where type = 'secondary'and TRIM(provider) = '')".
	"group by code)),0) as PatientBalance,".
	"IFNULL((b.fee-(select SUM(pay_amount) from ar_activity where encounter = b.encounter ".
	"and code = b.code and payer_type = 1 AND ".
	"b.pid IN (SELECT PID from insurance_data where type = 'secondary'and TRIM(provider) <> '')".
	"group by code)),0) as InsuranceBalance,".
	"((select sum(fee) as fees from billing where encounter=fe.encounter)".
	"- IFNULL((select sum(pay_amount) as pay from ar_activity where ".
	"encounter=fe.encounter),0)) as TotalBalance,".
	"IFNULL((select SUM(pay_amount) from ar_activity where encounter = b.encounter ".
	"and code = b.code and (payer_type = 1 OR payer_type = 2) group by code),0) as PaidAmount,".
	"(select ic.name from insurance_companies ic,insurance_data i ". 
	"where i.pid = pd.pid and i.provider = ic.id and i.type = 'primary') as 	".	 
	"PrimaryInsuranceCompany, ".
"(select i.plan_name from insurance_companies ic,insurance_data i ".
"where i.pid = pd.pid and i.provider = ic.id and i.type = 'primary') ".
"as PrimaryInsurancePlanName,". 
"(select ic.name from insurance_companies ic,insurance_data i ".
"where i.pid = pd.pid and i.provider = ic.id and i.type = 'secondary') as "."SecondaryInsuranceCompany, ".
"(select i.plan_name from insurance_companies ic,insurance_data i ".
"where i.pid = pd.pid and i.provider = ic.id and i.type = 'secondary') as ".
"SecondaryInsurancePlanName ,".
"(select status from encounter_status where encounter = b.encounter ".
"order by idEncounter_status desc limit 1) AS EncounterStatus,".
"'saved' as Status ".
"from patient_data pd ".
"inner join billing b on b.pid = pd.pid ".
"inner join form_encounter fe on fe.encounter = b.encounter ".
"inner join users u on fe.provider_id = u.id ".
"left join ar_activity aa on aa.encounter = b.encounter ".
"left join ar_session ar on ar.session_id = aa.session_id ".
"where b.code_type = 'CPT4' and activity = 1 ".
"AND b.date >= '$from_date' AND b.date <= '$to_date' ";
	
	
	
	

  // TBD: What if preliminary and final reports for the same order?

  if ($form_facility) {
    $query .= " AND fe.facility_id = '$form_facility' ";

  }
  $query .= " group by b.encounter, b.code ";



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
