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

//number_format($number, 2, '.', '')

function thisLineItem($row) {
	
	$provname = $row['provider_lname'];
	
	if (!empty($row['provider_fname'])) {
		$provname .= ', ' . $row['provider_fname'];
		
		if (!empty($row['provider_mname'])) {
			$provname .= ' ' . $row['provider_mname'];
		}
	}
	
	if ($_POST['form_csvexport']) {  
		
		if($row['device_type'] =='')
		$device_type="OpenEMR";
		else	
		$device_type=$row['device_type' ]; 
		
		echo '"' . addslashes(oeFormatShortDate($row['CreatedDate'  ])) . '",';
		echo '"' . addslashes($row['ExternalID' ]) . '",';
		echo '"' . addslashes($row['PatientName' ]) . '",';
		echo '"' . addslashes(oeFormatShortDate($row['DOB'  ])) . '",';
		echo '"' . addslashes($row['RenderingProvider' ]) . '",';
		echo '"' . addslashes($row['ReferringProvider' ]) . '",';
		echo '"' . addslashes($row['ServicingProvider' ]) . '",';
		echo '"' . addslashes($row['SupervisingProvider' ]) . '",';
		echo '"' . addslashes($row['ServiceLocation' ]) . '",';
		echo '"' . addslashes($row['Encounter' ]) . '",';
		echo '"' . addslashes(oeFormatShortDate($row['DOS'  ])) . '",';
		echo '"' . addslashes($row['BatchNo' ]) . '",';
		echo '"' . addslashes($row['InsuranceComp' ]) . '",';
		echo '"' . addslashes($row['PolicyNo' ]) . '",';
		echo '"' . addslashes($row['IP_DP' ]) . '",';
		echo '"' . addslashes($row['CPT' ]) . '",';
		echo '"' . addslashes($row['Modifier' ]) . '",';
		echo '"=""' . addslashes($row['DX' ]) . '""",';
		echo '"' . addslashes($row['POS' ]) . '",';
		echo '"' . addslashes($row['TotalCharge' ]) . '",';
		echo '"' . addslashes($row['EncounterStatus' ]) . '",';
		echo '"' . addslashes($row['billEHRNotes' ]) . '",';
		echo '"' . addslashes($row['isHospice' ]) . '",';
		echo '"' . addslashes($row['AlertNote' ]) . '",';
		echo '"' . addslashes($row['AdmissionDate' ]) . '",';
		echo '"' . addslashes($row['PriorAuthNumber' ]) . '",';
		echo '"' . addslashes($row['VerifiedDate' ]) . '",';
		echo '"' . addslashes($row['CreatedBy' ]) . '",';
		echo '"' . addslashes($row['LastProcessedFile' ]) . '",';
		echo '"' . addslashes(oeFormatShortDate($row['TransmissionDate'  ])) . '",';
		echo '"' . addslashes($device_type) . '",';
		
		//added By Gangeya : BUG ID : 10775, 10894, 10898, 10902
		if($row['claim_status'] !='')
		echo '"' . addslashes($row['claim_status' ]) . '",' . "\n";
		else
		echo '"' . "Saved" . '",' . "\n";
		
	}
	else{
	?>
	<tr bgcolor="#CEE4FF">
		<td class="detail"><?php echo oeFormatShortDate($row['CreatedDate'  ]); ?></td>
		<td class="detail"><?php echo $row['ExternalID' ]; ?></td>
		<td class="detail"><?php echo $row['PatientName' ]; ?></td>
		<td class="detail"><?php echo oeFormatShortDate($row['DOB'  ]); ?></td>
		<td class="detail"><?php echo $row['RenderingProvider' ]; ?></td>
		<td class="detail"><?php echo $row['ReferringProvider' ]; ?></td>
		<td class="detail"><?php echo $row['ServicingProvider' ]; ?></td>
		<td class="detail"><?php echo $row['SupervisingProvider' ]; ?></td>
		<td class="detail"><?php echo $row['ServiceLocation' ]; ?></td>
		<td class="detail"><?php echo $row['Encounter' ]; ?></td>
		<td class="detail"><?php echo oeFormatShortDate($row['DOS'  ]); ?></td>
		<td class="detail"><?php echo $row['BatchNo' ]; ?></td>
		<td class="detail"><?php echo $row['InsuranceComp' ]; ?></td>
		<td class="detail"><?php echo $row['PolicyNo' ]; ?></td>
		<td class="detail"><?php echo $row['IP_DP' ]; ?></td>
		<td class="detail"><?php echo $row['CPT' ]; ?></td>
		<td class="detail"><?php echo $row['Modifier' ]; ?></td>
		<td class="detail"><?php echo $row['DX' ]; ?></td>
		<td class="detail"><?php echo $row['POS' ]; ?></td>
		<td class="detail"><?php echo $row['TotalCharge' ]; ?></td>
		<td class="detail"><?php echo $row['EncounterStatus' ]; ?></td>
		<td class="detail"><?php echo $row['billEHRNotes' ]; ?></td>
		<td class="detail"><?php echo $row['isHospice' ]; ?></td>
		<td class="detail"><?php echo $row['AlertNote' ]; ?></td>
		<td class="detail"><?php echo $row['AdmissionDate' ]; ?></td>
		<td class="detail"><?php echo $row['PriorAuthNumber' ]; ?></td>
		<td class="detail"><?php echo $row['VerifiedDate' ]; ?></td>
		<td class="detail"><?php echo $row['CreatedBy' ]; ?></td>
		<td class="detail"><?php echo $row['LastProcessedFile' ]; ?></td>
		<td class="detail"><?php echo oeFormatShortDate($row['TransmissionDate'  ]); ?></td>
		<td class="detail"><?php 
			if($row['device_type'] =='')
			echo "OpenEMR";
			else	
		echo $row['device_type' ]; ?>
		</td>
		
		<!-- Added By Gangeya : BUG ID : 10775, 10894, 10898, 10902 -->
		<td class="detail"><?php 
			if($row['claim_status'] !='')
			echo $row['claim_status' ];
			else	
		echo "Saved"; ?>
		</td>
		
	</tr>
	<?php
	} 
} // end of function

if (! acl_check('acct', 'rep')) die(xl("Unauthorized access."));

$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
$form_batch_id = $_POST['form_batch_id'];
$form_cpt = $_POST['form_cpt'];
$form_provider  = $_POST['form_provider'];
$form_facility  = $_POST['form_facility'];
$form_status  = $_POST['form_status'];
$form_insurance  = $_POST['form_insurance'];
$form_user  = $_POST['form_user'];
$form_device_type = $_POST['device_type'];
$form_claim_status = $_POST['claim_status'];


if ($_POST['form_csvexport']) {
	
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=Day_Sheet_Report.csv");
	header("Content-Description: File Transfer");
	
	// CSV headers:
	echo '"' . xl('Charge Created Date') . '",';
	echo '"' . xl('External ID') . '",';
	echo '"' . xl('Patient Name') . '",';
	echo '"' . xl('DOB') . '",';
	echo '"' . xl('Rendering Provider') . '",';
	echo '"' . xl('Referring Provider') . '",';
	echo '"' . xl('Servicing Provider') . '",';
	echo '"' . xl('Supervising Provider') . '",';
	echo '"' . xl('Service Location') . '",';
	echo '"' . xl('Encounter ID') . '",';
	echo '"' . xl('DOS') . '",';
	echo '"' . xl('Batch No') . '",';
	echo '"' . xl('Primary Insurance Name') . '",';
	echo '"' . xl('Policy No') . '",';
	echo '"' . xl('IP-DP Status') . '",';
	echo '"' . xl('CPT�') . '",';
	echo '"' . xl('Modifier') . '",';  	
	echo '"' . xl('DX') . '",';
	echo '"' . xl('POS') . '",';
	echo '"' . xl('Total Charge') . '",';
	echo '"' . xl('Encounter Status') . '",';
	echo '"' . xl('billEHR Notes') . '",';
	echo '"' . xl('is Hospice') . '",';
	echo '"' . xl('Alert Note') . '",';
	echo '"' . xl('Admission Date') . '",';
	echo '"' . xl('Prior Auth No') . '",';
	echo '"' . xl('Verified Date') . '",';
	echo '"' . xl('Charge Created By') . '",';
	echo '"' . xl('Last Processed File') . '",';
	echo '"' . xl('TransmissionDate') . '",';
	echo '"' . xl('Device Type') . '",';
	
	//added By Gangeya : BUG ID : 10775, 10894, 10898, 10902
	echo '"' . xl('Claim Status') . '"' . "\n";
	
}
else { // not export
?>
<html>
	<head>
		<?php html_header_show();?>
		<title><?php xl('Day Sheet Report','e') ?></title>
	</head>
	
	<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
		<center>
			
			<h2><?php xl('Day Sheet Report','e')?></h2>
			
			
			<form method='post' action='daysheet_report.php'>
				
				<table border='0' cellpadding='3'>
					<?php /*?><tr>
						<td><?php xl('Batch ID : ','e')?></td><td><input type="text" name="form_batch_id" id="form_batch_id" size="10" '<?php echo $form_batch_id ?>'></td>
						<td><?php xl('CPT : ','e')?></td><td><input type="text" name="form_cpt" id="form_cpt" size="10" '<?php echo $form_cpt ?>'></td>
					</tr><?php */?>
					<tr>
						<td class='label'>
							<?php xl('Rendering Provider : ','e'); ?>
							</td><td>
							<?php
								// Build a drop-down list of providers.
								
								$query = "SELECT id, lname, fname FROM users WHERE ".
								"authorized = 1 $provider_facility_filter and newcrop_user_role = 'erxrenderingProvider' ORDER BY lname, fname"; //(CHEMED) facility filter
								
								$ures = sqlStatement($query);
								
								echo "   <select name='form_provider' style='width:241px'>\n";
								echo "    <option value=''>-- " . xl('All') . " --\n";
								
								while ($urow = sqlFetchArray($ures)) {
									$provid = $urow['id'];
									echo "    <option value='$provid'";
									if ($provid == $_POST['form_provider']) echo " selected";
									echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
								}
								
								echo "   </select>\n";
								
							?>
						</td>
						<td>
							<?php xl('Facility : ','e'); ?>
						</td>
						<td>
							<?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', true); ?>
						</td>
					</tr>
					<tr>
						<td class='label'>
							<?php xl('Status : ','e'); ?>
						</td>
						<td>
							<?php
								
								// Build a drop-down list of encounter status.
								
								$query = "select id,status from claim_status"; 
								
								$ures = sqlStatement($query);
								
								echo "   <select name='form_status' style='width:241px'>\n";
								echo "    <option value=''>-- " . xl('All') . " --\n";
								
								while ($urow = sqlFetchArray($ures)) {
									$statid = $urow['id'];
									echo "    <option value='$statid'";
									if ($statid == $_POST['form_status']) echo " selected";
									echo ">" . $urow['status'] . "\n";
								}
								
								echo "   </select>\n";
								
							?>
						</td>
						
						<td class='label'>
							<?php xl('Insurance Company : ','e'); ?>
						</td>
						<td>       
							<?php
								
								// Build a drop-down list of encounter status.
								
								$query = "select id, name from insurance_companies order by name"; 
								
								$ures = sqlStatement($query);
								
								echo "   <select name='form_insurance' style='width:299px'>\n";
								echo "    <option value=''>-- " . xl('All') . " --\n";
								
								while ($urow = sqlFetchArray($ures)) {
									$insuid = $urow['id'];
									echo "    <option value='$insuid'";
									if ($insuid == $_POST['form_insurance']) echo " selected";
									echo ">" . $urow['name'] . "\n";
								}
								
								echo "   </select>\n";
								
							?>
						</td>
					</tr>
					
					<tr>
						<td class='label'>
							<?php xl('Created By : ','e'); ?>
							</td><td>
							<?php
								
								// Build a drop-down list of providers.
								//
								
								$query = "SELECT id, lname, fname FROM users WHERE ".
								"authorized = 0 ORDER BY lname, fname"; //(CHEMED) facility filter
								
								$ures = sqlStatement($query);
								
								echo "   <select name='form_user' style='width:241px'>\n";
								echo "    <option value=''>-- " . xl('All') . " --\n";
								
								while ($urow = sqlFetchArray($ures)) {
									$userid = $urow['id'];
									echo "    <option value='$userid'";
									if ($userid == $_POST['form_user']) echo " selected";
									echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
								}
								
								echo "   </select>\n";
								
							?>
						</td>  
						<td><?php xl('Batch ID : ','e')?></td><td><input type="text" name="form_batch_id" id="form_batch_id" size="48" value="<?php echo $form_batch_id ?>"></td>
						
					</tr>
					<!-- code added for BUG 10521---->					
					
					<tr>
						<td><?php xl('Device Type : ','e')?></td>
						<td><select name="device_type" >
							<option value="All" <?php if(isset($_POST['device_type']) && $_POST['device_type'] == "All" ){ echo "selected"; }?>>-- All  --</option>
							<option value="iPhone" <?php if(isset($_POST['device_type']) && $_POST['device_type'] == "iPhone" ){ echo "selected"; }?>>iPhone</option>
							<option value="Android" <?php if(isset($_POST['device_type']) && $_POST['device_type'] == "Android" ){ echo "selected"; }?>>Android</option>
							<option value="OpenEMR" <?php if(isset($_POST['device_type']) && $_POST['device_type'] == "OpenEMR" ){ echo "selected"; }?>>OpenEMR</option>
						</select>
						</td>
						
						<td><?php xl('Claim Status : ','e')?></td>
						<td><select name="claim_status" >
							<option value="Saved" <?php if(isset($_POST['claim_status']) && $_POST['claim_status'] == "Saved" ){ echo "selected"; }?>>Saved</option>
							<option value="Unsaved" <?php if(isset($_POST['claim_status']) && $_POST['claim_status'] == "Unsaved" ){ echo "selected"; }?>>Unsaved</option>
						</select>
						</td>
						
					</tr>
					
					<!------------------------------->
					<tr>
						<td colspan="2" align="right">
							<?php xl('DOS / DOE /DOV: ','e')?>
							<select name="Mode"> 
								<option  value="DOS" <?php if(isset($_POST['Mode']) && $_POST['Mode'] == "DOS" ){ echo "selected"; }?>>DOS</option>
								<option value="DOE" <?php if(isset($_POST['Mode']) && $_POST['Mode'] == "DOE" ){ echo "selected"; }?>>DOE</option>
								<option value="DOV" <?php if(isset($_POST['Mode']) && $_POST['Mode'] == "DOV" ){ echo "selected"; }?>>DOV</option>
							</select>
						</td>
						<td colspan="2">
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
							
						</td>
						
						<tr><td colspan="4"></td></tr>
						
						<tr align="center">
							<td colspan="4">
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
							<td class="dehead"><?php xl('CreatedDate','e'  ) ?></td>
							<td class="dehead"><?php xl('ExternalID','e'  ) ?></td>
							<td class="dehead"><?php xl('PatientName','e'  ) ?></td>
							<td class="dehead"><?php xl('DOB','e'  ) ?></td>
							<td class="dehead"><?php xl('RenderingProvider','e'  ) ?></td>
							<td class="dehead"><?php xl('ReferringProvider','e'  ) ?></td>
							<td class="dehead"><?php xl('ServicingProvider','e'  ) ?></td>
							<td class="dehead"><?php xl('SupervisingProvider','e'  ) ?></td>
							<td class="dehead"><?php xl('ServiceLocation','e'  ) ?></td>
							<td class="dehead"><?php xl('Encounter','e'  ) ?></td>
							<td class="dehead"><?php xl('DOS','e'  ) ?></td>
							<td class="dehead"><?php xl('BatchNo','e'  ) ?></td>
							<td class="dehead"><?php xl('InsuranceComp','e'  ) ?></td>
							<td class="dehead"><?php xl('PolicyNo','e'  ) ?></td>
							<td class="dehead"><?php xl('IP/DP','e'  ) ?></td>
							<td class="dehead"><?php xl('CPT<sup>&reg;</sup>','e'  ) ?></td>
							<td class="dehead"><?php xl('Modifier','e'  ) ?></td>
							<td class="dehead"><?php xl('DX','e'  ) ?></td>
							<td class="dehead"><?php xl('POS','e'  ) ?></td>
							<td class="dehead"><?php xl('TotalCharge','e'  ) ?></td>
							<td class="dehead"><?php xl('EncounterStatus','e'  ) ?></td>
							<td class="dehead"><?php xl('billEHRNotes','e'  ) ?></td>
							<td class="dehead"><?php xl('isHospice','e'  ) ?></td>
							<td class="dehead"><?php xl('AlertNote','e'  ) ?></td>
							<td class="dehead"><?php xl('AdmissionDate','e'  ) ?></td>
							<td class="dehead"><?php xl('Prior Auth No','e'  ) ?></td>
							<td class="dehead"><?php xl('VerifiedDate','e'  ) ?></td>
							<td class="dehead"><?php xl('CreatedBy','e'  ) ?></td>
							<td class="dehead"><?php xl('LastProcessedFile','e'  ) ?></td>
							<td class="dehead"><?php xl('TransmissionDate','e'  ) ?></td>
							<td class="dehead"><?php xl('Device Type','e'	)  ?></td>
							
							<!-- Added By Gangeya : BUG ID : 10775, 10894, 10898, 10902 -->
							<td class="dehead"><?php xl('Claim Status','e'	)  ?></td>
						</tr>
						<?php
						} // end of else  export
						
						// If generating a report.
						
						if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
							
							$from_date = $form_from_date;
							$to_date   = $form_to_date;
							$batch_id = $form_batch_id;
							$cpt = $form_cpt;
							$form_provider  = $_POST['form_provider'];
							$form_facility  = $_POST['form_facility'];
							$form_status  = $_POST['form_status'];
							$form_insurance  = $_POST['form_insurance'];
							$form_user  = $_POST['form_user'];
							$form_device_type = $_POST['device_type'];
							$form_claim_status = $_POST['claim_status'];
							
							if($_POST['Mode'] == "DOE")
							{
								$where .= " AND fe.created_date >= '$from_date 00:00:00' AND fe.created_date <= '$to_date 23:59:59' "; 
							}
							if($_POST['Mode'] == "DOS")
							{
								$where .= " AND fe.date >= '$from_date 00:00:00' AND fe.date <= '$to_date 23:59:59' ";
							}
							if($_POST['Mode'] == "DOV")
							{
								$where .= " AND fe.verifiedDate >= '$from_date 00:00:00' AND fe.verifiedDate <= '$to_date 23:59:59' ";
							}
							if ($form_batch_id) 
							{
								$where .= "AND fe.batch_id = '$batch_id' ";
							}
							if ($form_provider) 
							{
								$where .= "AND fe.provider_id = '$form_provider' ";
							}
							if ($form_facility) 
							{
								$where .= "AND fe.facility_id = '$form_facility' ";
							}
							if ($form_status) 
							{
								$where .= "AND fe.claim_status_id = '$form_status' ";
							}
							/***********code added for consoludated report BUG 10521 & 10569****************/
							if ($form_device_type) 
							{
								if($form_device_type =="iPhone")
								//$query = "AND fe.device_type like 'iP%' ";
								$where .= "AND (fe.device_type like 'iP%' OR fe.device_type like 'iO%') ";
								elseif($form_device_type =="OpenEMR")
								$where .= "AND fe.device_type='OE'  ";
								elseif($form_device_type =="Android")
								//$query = "AND (fe.device_type!='OE' AND fe.device_type NOT like 'iP%' )";
								
								
								$where .= " AND ((fe.device_type!='OE' AND fe.device_type NOT like 'iO%' ) OR (fe.device_type!='OE' AND fe.device_type NOT like 'iO%' )) ";
							}
							/******************************************************************************/
							
							
							if ($form_insurance) 
							{
								$where .= "AND (select ic.id from insurance_data id join insurance_companies ic on ic.id = id.provider where id.type = 'primary' and id.pid = fe.pid and id.id in (select MAX(id) from insurance_data where type = 'primary' and pid = fe.pid) limit 1) = '$form_insurance' ";
							}
							if ($form_user) 
							{
								$where .= "AND (select ID from users where id = b.user) = '$form_user' ";
							}
							
							//Updated By Gangeya : BUG ID : 10775, 10894, 10898, 10902
							if($form_claim_status == "Saved"){
								$query ="select d.*,p.pos_name AS POS from (SELECT  CAST(`fe`.`created_date` AS DATE) AS `CreatedDate`,
								`fe`.`facility` AS `ServiceLocation`, `fe`.`encounter` AS `Encounter`,
								CAST(`fe`.`date` AS DATE) AS `DOS`, `fe`.`batch_id` AS `BatchNo`, `pd`.`pubpid` AS `ExternalID`,
								CONCAT(`pd`.`lname`, ' ', `pd`.`fname`, ' ', `pd`.`mname`) AS `PatientName`, CAST(`pd`.`DOB` AS DATE) AS `DOB`,
								`u`.`renderingProvider` AS `RenderingProvider`, `u`.`referringProvider` AS `ReferringProvider`,
								`u`.`servicingProvider` AS `ServicingProvider`, `u`.`supervisingProvider` AS `SupervisingProvider`,
								(SELECT `inc`.`name`FROM (`insurance_companies` `inc` JOIN `insurance_data` `ind`) 
								WHERE ((`ind`.`provider` = `inc`.`id`) AND (`ind`.`pid` = `fe`.`pid`)
								AND (`ind`.`type` = 'primary')) ORDER BY `ind`.`date` DESC LIMIT 1) AS `InsuranceComp`,
								(SELECT `ind`.`policy_number` FROM (`insurance_companies` `inc` JOIN `insurance_data` `ind`)
								WHERE ((`ind`.`provider` = `inc`.`id`) AND (`ind`.`pid` = `fe`.`pid`) AND (`ind`.`type` = 'primary'))
								ORDER BY `ind`.`date` DESC LIMIT 1) AS `PolicyNo`,
								GROUP_CONCAT(IF((`b`.`code_type` = 'CPT4'), `b`.`code`, NULL) SEPARATOR ' , ') AS `CPT`, 
								GROUP_CONCAT(IF((`b`.`modifier` <> ''), `b`.`modifier`, NULL) SEPARATOR ' , ') AS `Modifier`, 
								GROUP_CONCAT(IF(((`b`.`code_type` = 'ICD9') OR (`b`.`code_type` = 'ICD10')), `b`.`code`,
								NULL) ORDER BY `b`.`id` ASC SEPARATOR ' , ') AS `DX`,
								SUM((`b`.`units` * `b`.`fee`)) AS `TotalCharge`, `cs`.`status` AS `EncounterStatus`,
								IF((`fe`.`hospice` = 1), 'Yes', 'No') AS `isHospice`, `u`.`createdBy` AS `CreatedBy`,
								`b`.`process_file` AS `LastProcessedFile`, CAST(`b`.`bill_date` AS DATE) AS `TransmissionDate`,
								`fe`.`device_type` AS `device_type`, CAST(`fe`.`verifiedDate` AS DATE) AS `VerifiedDate`,
								`pd`.`Alert_note` AS `AlertNote`, `fe`.`billEHRNotes` AS `billEHRNotes`,
								`vo`.`admissionDAte` AS `AdmissionDate`, `vo`.`prior_auth_number` AS `PriorAuthNumber`, 
								`vo`.`comments` AS `comments`, `pd`.`IP_DP` AS `IP_DP`,
								`f`.`name` AS `name`, `fe`.`pos_id` AS `pos_id`
								FROM
								((((((`form_encounter` `fe`
								JOIN `patient_data` `pd` ON ((`fe`.`pid` = `pd`.`pid`)))
								JOIN `vw_user_data` `u` ON ((`fe`.`encounter` = `u`.`encounter`)))
								JOIN `billing` `b` ON ((`fe`.`encounter` = `b`.`encounter`)))
								JOIN `claim_status` `cs` ON ((`fe`.`claim_status_id` = `cs`.`id`)))
								JOIN `vw_misc_billing_option` `vo` ON ((`vo`.`encounter` = `fe`.`encounter`)))
								LEFT JOIN `facility` `f` ON ((`f`.`id` = `fe`.`billing_facility`)))
								WHERE (`b`.`activity` = 1) ".$where."
								GROUP BY `fe`.`encounter` 
								UNION ALL 
								SELECT 
								CAST(`fe`.`created_date` AS DATE) AS `CreatedDate`, `fe`.`facility`AS `ServiceLocation`, `fe`.`encounter` AS `Encounter`,
								CAST(`fe`.`date` AS DATE) AS `DOS`, `fe`.`batch_id` AS `BatchNo`, `pd`.`pubpid` AS `ExternalID`,
								CONCAT(`pd`.`lname`, ' ', `pd`.`fname`, ' ', `pd`.`mname`) AS `PatientName`,
								CAST(`pd`.`DOB` AS DATE) AS `DOB`, `u`.`renderingProvider` AS `RenderingProvider`,
								`u`.`referringProvider` AS `ReferringProvider`, `u`.`servicingProvider` AS `ServicingProvider`,
								`u`.`supervisingProvider` AS `SupervisingProvider`,
								(SELECT `inc`.`name` FROM (`insurance_companies` `inc` JOIN `insurance_data` `ind`)
								WHERE ((`ind`.`provider` = `inc`.`id`) AND (`ind`.`pid` = `fe`.`pid`)
								AND (`ind`.`type` = 'primary')) ORDER BY `ind`.`date` DESC LIMIT 1) AS `InsuranceComp`,
								(SELECT `ind`.`policy_number` FROM (`insurance_companies` `inc` JOIN `insurance_data` `ind`)
								WHERE ((`ind`.`provider` = `inc`.`id`) AND (`ind`.`pid` = `fe`.`pid`)
								AND (`ind`.`type` = 'primary')) ORDER BY `ind`.`date` DESC LIMIT 1) AS `PolicyNo`,
								GROUP_CONCAT(IF((`b`.`code_type` = 'CPT4'), `b`.`code`, NULL) SEPARATOR ' , ') AS `CPT`,
								GROUP_CONCAT(IF((`b`.`modifier` <> ''), `b`.`modifier`, NULL) SEPARATOR ' , ') AS `Modifier`,
								GROUP_CONCAT(IF(((`b`.`code_type` = 'ICD9') OR (`b`.`code_type` = 'ICD10')), `b`.`code`,
								NULL) ORDER BY `b`.`id` ASC SEPARATOR ' , ') AS `DX`,
								SUM((`b`.`units` * `b`.`fee`)) AS `TotalCharge`, `cs`.`status` AS `EncounterStatus`,
								IF((`fe`.`hospice` = 1), 'Yes', 'No') AS `isHospice`, `u`.`createdBy` AS `CreatedBy`,
								`b`.`process_file` AS `LastProcessedFile`, CAST(`b`.`bill_date` AS DATE) AS `TransmissionDate`,
								`fe`.`device_type` AS `device_type`, CAST(`fe`.`verifiedDate` AS DATE) AS `VerifiedDate`, `pd`.`Alert_note` AS `AlertNote`,
								`fe`.`billEHRNotes` AS `billEHRNotes`, '' AS `AdmissionDate`,
								'' AS `PriorAuthNumber`, '' AS `comments`,
								`pd`.`IP_DP` AS `IP_DP`, `f`.`name` AS `name`, `fe`.`pos_id` AS `pos_id`
								FROM
								(((((`form_encounter` `fe`
								JOIN `patient_data` `pd` ON ((`fe`.`pid` = `pd`.`pid`)))
								JOIN `vw_user_data` `u` ON ((`fe`.`encounter` = `u`.`encounter`)))
								JOIN `billing` `b` ON ((`fe`.`encounter` = `b`.`encounter`)))
								JOIN `claim_status` `cs` ON ((`fe`.`claim_status_id` = `cs`.`id`)))
								LEFT JOIN `facility` `f` ON ((`f`.`id` = `fe`.`billing_facility`)))
								WHERE
								((`b`.`activity` = 1)
								AND (NOT (`fe`.`encounter` IN (SELECT `vw_misc_billing_option`.`encounter` FROM `vw_misc_billing_option`)))) 
								".$where." GROUP BY `fe`.`encounter` )d left join pos_list p on d.pos_id =p.id order by CreatedDate";
							}
							else{
								$query ="select date(fe.created_date) as CreatedDate, fe.facility as ServiceLocation, fe.Encounter, DATE(fe.date) AS DOS, 
								fe.batch_id as BatchNo, pd.pubpid as ExternalID, Concat(pd.lname, ' ', pd.fname, ' ', pd.mname) AS PatientName, Date(pd.DOB) AS DOB,
								u.renderingProvider AS RenderingProvider, u.referringProvider AS ReferringProvider,
								u.servicingProvider AS ServicingProvider, u.supervisingProvider AS SupervisingProvider,
								(select inc.name from insurance_companies inc,insurance_data as ind where ind.provider=inc.id and ind.pid=fe.pid and ind.type = 'primary' order by date desc limit 1) as InsuranceComp,
								(select ind.policy_number from insurance_companies inc,insurance_data as ind where ind.provider=inc.id and ind.pid=fe.pid and ind.type = 'primary' order by date desc limit 1) as PolicyNo,
								group_concat(IF(b.code_type = 'CPT4', b.code,NULL) SEPARATOR ' , ') AS CPT, 
								group_concat(IF(b.modifier <> '', b.modifier,NULL) SEPARATOR ' , ') AS Modifier, 
								group_concat(IF(b.code_type = 'ICD9',b.code,NULL) order by b.id SEPARATOR ' , ') AS DX,
								pl.pos_name as POS,
								SUM(b.units * b.fee) AS TotalCharge, cs.status as EncounterStatus, 
								u.createdBy as CreatedBy, 
								b.process_file as LastProcessedFile, Date(b.bill_date) as TransmissionDate, 
								fe.device_type as device_type, fe.final_status as claim_status,'' AS `AdmissionDate`,
								'' AS `PriorAuthNumber`, '' AS `comments`, '' AS `billEHRNotes`
								from form_encounter_draft fe 
								INNER JOIN patient_data pd on fe.pid =pd.pid 
								INNER JOIN vw_user_data u on fe.encounter =u.encounter
								INNER JOIN billing_draft b on fe.id = b.draft_id
								LEFT JOIN diagnosis d on fe.encounter = d.encounter 
								JOIN pos_list pl on fe.pos_id=pl.id
								join claim_status cs on fe.claim_status_id =cs.id 	
								where b.activity = 1 AND final_status !='save'".$where;
							}
							
							//echo $query;die;
							
							$res = sqlStatement($query);
							
							while ($row = sqlFetchArray($res)) 
							{
								thisLineItem($row);
							}
							
						} // end report generation
						
						if (! $_POST['form_csvexport']) {
						?>
						
					</table>
					
					<div class="footer">
						<p align="center">
							CPT<sup>&reg;</sup> 2020 American Medical Association. All rights reserved.<br>
							CPT<sup>&reg;</sup> is a registered trademark of the American Medical Association
						</p>
					</div>
					
					
				</form>
			</center>
		</body>
		<style>
			.footer {
			position: relative;
			left: 0;
			bottom: 0;
			width: 100%;
			background-color: none;
			color: black;
			text-align: center;
			}
		</style>
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
