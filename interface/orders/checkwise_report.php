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
  	echo '"' . addslashes(oeFormatShortDate($row['CreatedDate'  ])) . '",';
    echo '"' . addslashes($row['Encounter' ]) . '",';
    echo '"' . addslashes($row['PID' ]) . '",';
	echo '"' . addslashes($row['PatientName' ]) . '",';
	echo '"' . addslashes($row['FirstName' ]) . '",';
	echo '"' . addslashes($row['MiddleName' ]) . '",';
	echo '"' . addslashes($row['LastName' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['DOB'  ])) . '",';
	echo '"' . addslashes($row['CaseName' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['ServiceStartDate'  ])) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['ServiceEndDate'  ])) . '",';
	echo '"' . addslashes($row['provider_id' ]) . '",';
	echo '"' . addslashes($row['RendarringProvider' ]) . '",';
	echo '"' . addslashes($row['ServiceLocation' ]) . '",';
	echo '"' . addslashes($row['CPT' ]) . '",';
	echo '"' . addslashes($row['Modifier' ]) . '",';
	echo '"' . addslashes($row['Units' ]) . '",';
	echo '"' . addslashes($row['UnitCharge' ]) . '",';
	echo '"' . addslashes($row['TotalCharge' ]) . '",';
	echo '"' . addslashes($row['AdjustedCharges' ]) . '",';
	echo '"' . addslashes($row['PrimaryInsuranceCheckNo' ]) . '",';
	echo '"' . addslashes($row['PrimaryInsuranceCheckAmount' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['PrimaryInsurancePostingDate'  ])) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['PrimaryInsuranceAdjudicationDate'  ])) . '",';
	echo '"' . addslashes($row['PrimaryInsurancePayment' ]) . '",';
	echo '"' . addslashes($row['PrimaryInsuranceAdjustment' ]) . '",';
	echo '"' . addslashes($row['SecondaryInsuranceCheckNo' ]) . '",';
	echo '"' . addslashes($row['SecondaryInsuranceCheckAmount' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['SecondaryInsurancePostingDate'  ])) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['SecondaryInsuranceAdjudicationDate'  ])) . '",';
	echo '"' . addslashes($row['SecondaryInsurancePayment' ]) . '",';
	echo '"' . addslashes($row['SecondaryInsuranceAdjustment' ]) . '",';
	echo '"' . addslashes($row['TertiaryInsuranceCheckNo' ]) . '",';
	echo '"' . addslashes($row['TertiaryInsuranceCheckAmount' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['TertiaryInsurancePostingDate'  ])) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['TertiaryInsuranceAdjudicationDate'  ])) . '",';
	echo '"' . addslashes($row['TertiaryInsurancePayment' ]) . '",';
	echo '"' . addslashes($row['TertiaryInsuranceAdjustment' ]) . '",';
	echo '"' . addslashes(oeFormatShortDate($row['PatientPostingDate'  ])) . '",';
	echo '"' . addslashes($row['PatientPayment' ]) . '"' . "\n";
  }
  else {
?>
 <tr bgcolor="#CEE4FF">
  <td class="detail"><?php echo oeFormatShortDate($row['CreatedDate'  ]); ?></td>
  <td class="detail"><?php echo $row['Encounter' ]; ?></td>
  <td class="detail"><?php echo $row['PID' ]; ?></td>
  <td class="detail"><?php echo $row['PatientName' ]; ?></td>
  <td class="detail"><?php echo $row['FirstName' ]; ?></td>
  <td class="detail"><?php echo $row['MiddleName' ]; ?></td>
  <td class="detail"><?php echo $row['LastName' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['DOB'  ]); ?></td>
  <td class="detail"><?php echo $row['CaseName' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['ServiceStartDate'  ]); ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['ServiceEndDate'  ]); ?></td>
  <td class="detail"><?php echo $row['provider_id' ]; ?></td>
  <td class="detail"><?php echo $row['RendarringProvider' ]; ?></td>
  <td class="detail"><?php echo $row['ServiceLocation' ]; ?></td>
  <td class="detail"><?php echo $row['CPT' ]; ?></td>
  <td class="detail"><?php echo $row['Modifier' ]; ?></td>
  <td class="detail"><?php echo $row['Units' ]; ?></td>
  <td class="detail"><?php echo $row['UnitCharge' ]; ?></td>
  <td class="detail"><?php echo $row['TotalCharge' ]; ?></td>
  <td class="detail"><?php echo $row['AdjustedCharges' ]; ?></td>
  <td class="detail"><?php echo $row['PrimaryInsuranceCheckNo' ]; ?></td>
  <td class="detail"><?php echo $row['PrimaryInsuranceCheckAmount' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['PrimaryInsurancePostingDate'  ]); ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['PrimaryInsuranceAdjudicationDate'  ]); ?></td>
  <td class="detail"><?php echo $row['PrimaryInsurancePayment' ]; ?></td>
  <td class="detail"><?php echo $row['PrimaryInsuranceAdjustment' ]; ?></td>
  <td class="detail"><?php echo $row['SecondaryInsuranceCheckNo' ]; ?></td>
  <td class="detail"><?php echo $row['SecondaryInsuranceCheckAmount' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['SecondaryInsurancePostingDate'  ]); ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['SecondaryInsuranceAdjudicationDate'  ]); ?></td>
  <td class="detail"><?php echo $row['SecondaryInsurancePayment' ]; ?></td>
  <td class="detail"><?php echo $row['SecondaryInsuranceAdjustment' ]; ?></td>
  <td class="detail"><?php echo $row['TertiaryInsuranceCheckNo' ]; ?></td>
  <td class="detail"><?php echo $row['TertiaryInsuranceCheckAmount' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['TertiaryInsurancePostingDate'  ]); ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['TertiaryInsuranceAdjudicationDate'  ]); ?></td>
  <td class="detail"><?php echo $row['TertiaryInsurancePayment' ]; ?></td>
  <td class="detail"><?php echo $row['TertiaryInsuranceAdjustment' ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['PatientPostingDate'  ]); ?></td>
  <td class="detail"><?php echo $row['PatientPayment' ]; ?></td>
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
  header("Content-Disposition: attachment; filename=Cheque_Wise_Report.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  echo '"' . xl('CreatedDate') . '",';
  echo '"' . xl('Encounter') . '",';
  echo '"' . xl('PID') . '",';
  echo '"' . xl('PatientName') . '",';
  echo '"' . xl('FirstName') . '",';
  echo '"' . xl('MiddleName') . '",';
  echo '"' . xl('LastName') . '",';
  echo '"' . xl('DOB') . '",';
  echo '"' . xl('CaseName') . '",';
  echo '"' . xl('ServiceStartDate') . '",';
  echo '"' . xl('ServiceEndDate') . '",';
  echo '"' . xl('provider_id') . '",';
  echo '"' . xl('RendarringProvider') . '",';
  echo '"' . xl('ServiceLocation') . '",';
  echo '"' . xl('CPT') . '",';
  echo '"' . xl('Modifier') . '",';
  echo '"' . xl('Units') . '",';
  echo '"' . xl('UnitCharge') . '",';
  echo '"' . xl('TotalCharge') . '",';
  echo '"' . xl('AdjustedCharges') . '",';
  echo '"' . xl('PrimaryInsuranceCheckNo') . '",';
  echo '"' . xl('PrimaryInsuranceCheckAmount') . '",';
  echo '"' . xl('PrimaryInsurancePostingDate') . '",';
  echo '"' . xl('PrimaryInsuranceAdjudicationDate') . '",';
  echo '"' . xl('PrimaryInsurancePayment') . '",';
  echo '"' . xl('PrimaryInsuranceAdjustment') . '",';
  echo '"' . xl('SecondaryInsuranceCheckNo') . '",';
  echo '"' . xl('SecondaryInsuranceCheckAmount') . '",';
  echo '"' . xl('SecondaryInsurancePostingDate') . '",';
  echo '"' . xl('SecondaryInsuranceAdjudicationDate') . '",';
  echo '"' . xl('SecondaryInsurancePayment') . '",';
  echo '"' . xl('SecondaryInsuranceAdjustment') . '",';
  echo '"' . xl('TertiaryInsuranceCheckNo') . '",';
  echo '"' . xl('TertiaryInsuranceCheckAmount') . '",';
  echo '"' . xl('TertiaryInsurancePostingDate') . '",';
  echo '"' . xl('TertiaryInsuranceAdjudicationDate') . '",';
  echo '"' . xl('TertiaryInsurancePayment') . '",';
  echo '"' . xl('TertiaryInsuranceAdjustment') . '",';
  echo '"' . xl('PatientPostingDate') . '",';
  echo '"' . xl('PatientPayment') . '"' . "\n";
}
else { // not export
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Cheque-Wise Report','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('Cheque-Wise Report','e')?></h2>

<form method='post' action='CheckWise_report.php'>

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
  <td class="dehead"><?php xl('CreatedDate','e'  ) ?></td>
  <td class="dehead"><?php xl('Encounter','e'  ) ?></td>
  <td class="dehead"><?php xl('PID','e'  ) ?></td>
  <td class="dehead"><?php xl('PatientName','e'  ) ?></td>
  <td class="dehead"><?php xl('FirstName','e'  ) ?></td>
  <td class="dehead"><?php xl('MiddleName','e'  ) ?></td>
  <td class="dehead"><?php xl('LastName','e'  ) ?></td>
  <td class="dehead"><?php xl('DOB','e'  ) ?></td>
  <td class="dehead"><?php xl('CaseName','e'  ) ?></td>
  <td class="dehead"><?php xl('ServiceStartDate','e'  ) ?></td>
  <td class="dehead"><?php xl('ServiceEndDate','e'  ) ?></td>
  <td class="dehead"><?php xl('provider_id','e'  ) ?></td>
  <td class="dehead"><?php xl('RendarringProvider','e'  ) ?></td>
  <td class="dehead"><?php xl('ServiceLocation','e'  ) ?></td>
  <td class="dehead"><?php xl('CPT','e'  ) ?></td>
  <td class="dehead"><?php xl('Modifier','e'  ) ?></td>
  <td class="dehead"><?php xl('Units','e'  ) ?></td>
  <td class="dehead"><?php xl('UnitCharge','e'  ) ?></td>
  <td class="dehead"><?php xl('TotalCharge','e'  ) ?></td>
  <td class="dehead"><?php xl('AdjustedCharges','e'  ) ?></td>
  <td class="dehead"><?php xl('PrimaryInsuranceCheckNo','e'  ) ?></td>
  <td class="dehead"><?php xl('PrimaryInsuranceCheckAmount','e'  ) ?></td>
  <td class="dehead"><?php xl('PrimaryInsurancePostingDate','e'  ) ?></td>
  <td class="dehead"><?php xl('PrimaryInsuranceAdjudicationDate','e'  ) ?></td>
  <td class="dehead"><?php xl('PrimaryInsurancePayment','e'  ) ?></td>
  <td class="dehead"><?php xl('PrimaryInsuranceAdjustment','e'  ) ?></td>
  <td class="dehead"><?php xl('SecondaryInsuranceCheckNo','e'  ) ?></td>
  <td class="dehead"><?php xl('SecondaryInsuranceCheckAmount','e'  ) ?></td>
  <td class="dehead"><?php xl('SecondaryInsurancePostingDate','e'  ) ?></td>
  <td class="dehead"><?php xl('SecondaryInsuranceAdjudicationDate','e'  ) ?></td>
  <td class="dehead"><?php xl('SecondaryInsurancePayment','e'  ) ?></td>
  <td class="dehead"><?php xl('SecondaryInsuranceAdjustment','e'  ) ?></td>
  <td class="dehead"><?php xl('TertiaryInsuranceCheckNo','e'  ) ?></td>
  <td class="dehead"><?php xl('TertiaryInsuranceCheckAmount','e'  ) ?></td>
  <td class="dehead"><?php xl('TertiaryInsurancePostingDate','e'  ) ?></td>
  <td class="dehead"><?php xl('TertiaryInsuranceAdjudicationDate','e'  ) ?></td>
  <td class="dehead"><?php xl('TertiaryInsurancePayment','e'  ) ?></td>
  <td class="dehead"><?php xl('TertiaryInsuranceAdjustment','e'  ) ?></td>
  <td class="dehead"><?php xl('PatientPostingDate','e'  ) ?></td>
  <td class="dehead"><?php xl('PatientPayment','e'  ) ?></td>
 </tr>
<?php
} // end not export

// If generating a report.
//
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date;
  $to_date   = $form_to_date;
  
$query = "select Date(b.date) as CreatedDate, b.Encounter, pd.PID, ".
"Concat(pd.lname, ' ', pd.fname, ' ', pd.mname) AS PatientName, ".
"pd.fname as FirstName, pd.mname AS MiddleName, pd.lname AS LastName, ".
"Date(pd.DOB) AS DOB, (select ic.name from insurance_companies ic,insurance_data i ".
"where i.pid = pd.pid and i.provider = ic.id and i.type = 'primary') as CaseName, ".
"Date(fe.date) as ServiceStartDate,Date(fe.date) as ServiceEndDate, ".
"fe.provider_id,Concat(u.lname, ' ', u.fname, ' ', u.mname) AS RendarringProvider, ".
"fe.facility AS ServiceLocation, ".
"b.code AS CPT,b.Modifier, ".
"b.Units, Round((b.fee/b.units),2) as UnitCharge, b.fee as TotalCharge, ".
"(select SUM(adj_amount) from ar_activity where encounter = fe.encounter ".
"and code = b.code group by code) as AdjustedCharges, ".
"(select reference from ar_session where session_id = (select session_id from ar_activity ".
"where encounter = aa.encounter and payer_type =1 ".
"and code = aa.code limit 1)) as PrimaryInsuranceCheckNo, ".
"(select pay_total from ar_session where session_id = (select session_id from ar_activity ".
"where encounter = aa.encounter and payer_type =1 ".
"and code = aa.code limit 1)) as PrimaryInsuranceCheckAmount, ".
"(select DATE(MAX(post_time)) from ar_activity where encounter = fe.encounter ".
"and code = b.code and payer_type = 1 group by code) as PrimaryInsurancePostingDate, ".
"(select check_date from ar_session where session_id = (select session_id from ar_activity ".
"where encounter = aa.encounter and payer_type =1 ".
"and code = aa.code limit 1)) as PrimaryInsuranceAdjudicationDate, ".
"IF(aa.payer_type = 1,aa.pay_amount, 0.00) as PrimaryInsurancePayment, ".
"(select SUM(adj_amount) from ar_activity where encounter = fe.encounter ".
"and code = b.code and payer_type = 1 group by code) as PrimaryInsuranceAdjustment, ".
"(select reference from ar_session where session_id = (select session_id from ar_activity ".
"where encounter = aa.encounter and payer_type =2 ".
"and code = aa.code limit 1)) as SecondaryInsuranceCheckNo, ".
"(select pay_total from ar_session where session_id = (select session_id from ar_activity ".
"where encounter = aa.encounter and payer_type =2 ".
"and code = aa.code limit 1)) as SecondaryInsuranceCheckAmount, ".
"(select DATE(MAX(post_time)) from ar_activity where encounter = fe.encounter ".
"and code = b.code and payer_type = 2 group by code) as SecondaryInsurancePostingDate, ".
"(select check_date from ar_session where session_id = (select session_id from ar_activity ".
"where encounter = aa.encounter and payer_type =2 ".
"and code = aa.code limit 1)) as SecondaryInsuranceAdjudicationDate, ".
"IF(aa.payer_type = 2,aa.pay_amount, 0.00) as SecondaryInsurancePayment, ".
"(select SUM(adj_amount) from ar_activity where encounter = fe.encounter ".
"and code = b.code and payer_type = 2 group by code) as SecondaryInsuranceAdjustment, ".
"(select reference from ar_session where session_id = (select session_id from ar_activity ".
"where encounter = aa.encounter and payer_type =3 ".
"and code = aa.code limit 1)) as TertiaryInsuranceCheckNo, ".
"(select pay_total from ar_session where session_id = (select session_id from ar_activity ". 
"where encounter = aa.encounter and payer_type =3  ".
"and code = aa.code limit 1)) as TertiaryInsuranceCheckAmount, ".
"(select DATE(MAX(post_time)) from ar_activity where encounter = fe.encounter ".
"and code = b.code and payer_type = 3 group by code) as TertiaryInsurancePostingDate, ".
"(select check_date from ar_session where session_id = (select session_id from ar_activity ".
"where encounter = aa.encounter and payer_type =3 ".
"and code = aa.code limit 1)) as TertiaryInsuranceAdjudicationDate, ".
"IF(aa.payer_type = 3,aa.pay_amount, 0.00) as TertiaryInsurancePayment, ".
"(select SUM(adj_amount) from ar_activity where encounter = fe.encounter ".
"and code = b.code and payer_type = 3 group by code) as TertiaryInsuranceAdjustment, ".
"(select DATE(MAX(post_time)) from ar_activity where encounter = fe.encounter ".
"and code = b.code and payer_type = 0 group by code) as PatientPostingDate, ".
"IF(aa.payer_type = 0, pay_amount,0.00) AS PatientPayment ".
"from patient_data pd ".
"inner join billing b on b.pid = pd.pid ".
"inner join form_encounter fe on fe.encounter = b.encounter ".
"inner join users u on fe.provider_id = u.id ".
"left join ar_activity aa on aa.encounter = b.encounter ".
"left join ar_session ar on ar.session_id = aa.session_id ".
"where b.code_type = 'CPT4' and activity = 1 ".
"and  b.date >= '$from_date 00:00:00' AND b.date <= '$to_date 23:59:59' ".
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
