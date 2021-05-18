<?php

// Copyright (C) 2005-2010 Rod Roark <rod@sunsetsystems.com>
//
// Windows compatibility and statement downloading:
//     2009 Bill Cernansky and Tony McCormick [mi-squared.com]
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This is the first of two pages to support posting of EOBs.
// The second is sl_eob_invoice.php.




require_once("../globals.php");
require_once("$srcdir/patient.inc");
//require_once("$srcdir/sql-ledger.inc");
require_once("$srcdir/invoice_summary.inc.php");
require_once($GLOBALS['OE_SITE_DIR'] . "/statement.inc.php");
require_once("$srcdir/parse_era.inc.php");
require_once("$srcdir/sl_eob.inc.php");
require_once("$srcdir/formatting.inc.php");


require_once("$srcdir/mpdf/mpdf.php");
require_once("$srcdir/mpdf/PDFMerger.php");

$DEBUG = 0; // set to 0 for production, 1 to test

$INTEGRATED_AR = $GLOBALS['oer_config']['ws_accounting']['enabled'] === 2;

$alertmsg = '';
$where = '';
$eraname = '';
$eracount = 0;

global $pdf_files;
$pdf_files = array();
global $count;
$count=1;


$userID = $_SESSION['authId'];


// this function is added for getting pdf_get_invoice_summary
//updated by gangeya  query to tertiary payment paid and patient paid.
//updated by gangeya query to remove PQRS codes.

function pdf_get_invoice_summary($patient_id, $encounter_id){
	// query changed for BUG ID 10571
	$res1 = sqlStatement("select b.encounter,fe.stmt_count,fe.claim_status_id,DATE(fe.date) AS DOS, CONCAT(u.fname,' ',u.lname) as Provider,
fe.facility AS Location,b.modifier,b.code AS CPT,b.fee*b.units As Charge,

IFNULL((select SUM(pay_amount) from ar_activity 
where payer_type = 1 and code = b.code and encounter = b.encounter and  modifier = b.modifier
group by encounter,code),0) as PriPaid,

IFNULL((select SUM(pay_amount) from ar_activity 
where payer_type = 2 and code = b.code and encounter = b.encounter and  modifier = b.modifier
group by encounter,code),0) as SecPaid,

IFNULL((select SUM(pay_amount) from ar_activity 
where payer_type = 3 and code = b.code and encounter = b.encounter and  modifier = b.modifier
group by encounter,code),0) as TerPaid,

IFNULL((select SUM(pay_amount) from ar_activity 
where payer_type = 0 and code = b.code and encounter = b.encounter and  modifier = b.modifier
group by encounter,code),0) as PtPaid,

IFNULL((select SUM(adj_amount) from ar_activity 
where code = b.code and encounter = b.encounter and  modifier = b.modifier
group by encounter,code),0) as Adjustment,

IFNULL(( select sum(w_o) from ar_activity where
 code = b.code and encounter = b.encounter and  modifier = b.modifier and (pay_amount>'0.00' or reason_code='wo' ) and payer_type!='0'),0) as w_o,
 
(SELECT follow_up_note FROM ar_activity a WHERE follow_up = 'y' 
and a.pid = $patient_id AND a.encounter = $encounter_id and a.code = b.code limit 1) as Reason

from billing b 
inner join form_encounter fe  on fe.encounter = b.encounter
INNER JOIN users u ON u.id = fe.provider_id
where b.code_type = 'CPT4' and b.activity=1 and b.fee <> 0.01 and b.encounter = $encounter_id
and b.pid = $patient_id group by b.encounter,b.code,b.modifier ");

  	while ($row1 = sqlFetchArray($res1)) {
		$code[] = $row1;
	
  	}

  return $code;
//exit;
}


function bucks($amount) {
  if ($amount) echo oeFormatMoney($amount);
}



// code added for batch statement by Pawan BUG ID :8802
function zipFilesDownload($file_names,$archive_file_name,$file_path,$form_with)
{
	$pdf = new PDFMerger;

	$pdf_file_array=array_unique($file_names);

	$test2=$pdf_file_array;
	
	foreach($pdf_file_array as $files)
	{
		$myfile =$file_path.$files;
		$pdf->addPDF($myfile, 'all');
		
  	}
	
	$stmt_count=count($test2);
	$stmtdate = date("Ymd");
	$stmt_count1 = str_pad($stmt_count, 4, "0", STR_PAD_LEFT);
	$site_id = $_SESSION['site_id'];
	$stmt_str_lower = strtoupper($site_id);
	$stmt_str = $stmt_str_lower;
	 $batch_file = "$stmt_str$stmtdate$stmt_count1.pdf";
	 $pdf->merge('download', $batch_file);
	
	
}


$today = date('Y-m-d');
$pdate = date('YmdHis');

$my_today_date = date('Y-m-d H:i:s');
$stmtdate2 = date("Ymd");


// code for getting loggedin user name
if(isset($_SESSION['authUserID'])){
	$user_id = $_SESSION['authUserID'];
	$user_res = sqlStatement("select CONCAT(fname,' ',lname) as username from users where id='$user_id'");

	$user_row = sqlFetchArray($user_res);
	$user_name = $user_row['username'];
	
}



  if (($_POST['form_print'] || $_POST['form_download'] || $_POST['form_pdf']) && $_POST['form_cb']) {

	// code added for batch statement by Pawan BUG ID :8802
	if(!isset($_POST['form_pdf'])){
		$fhprint = fopen($STMT_TEMP_FILE, 'w');
		if($_POST['form_download'])
		$parameter = "text";
		else
		$parameter="";
	}
	
    $where = "";
    foreach ($_POST['form_cb'] as $key => $value) $where .= " OR f.id = $key";
    $where = substr($where, 4);

    $res = sqlStatement("SELECT " .
      "f.id, f.date, f.pid, f.encounter, f.stmt_count, f.last_stmt_date, " .
      "p.fname,p.DOB,p.pubpid, p.mname, p.lname, p.street, p.city, p.state, p.postal_code,p.phone_home " .
      "FROM form_encounter AS f, patient_data AS p " .
      "WHERE ( $where ) AND " .
      "p.pid = f.pid " .
      "ORDER BY p.lname, p.fname, f.pid, f.date, f.encounter");
	  
	  
	
	  
	 $count_res = sqlStatement("select account_no,date,patient_name,dob,SUM(balance) as balance from statement_log  where MONTH(CURDATE())=MONTH(date) group by date,account_no");
	 $statement_count = sqlNumRows($count_res); 

    $stmt = array();
    $stmt_count = 0;
	
	//$stmt['statement_count'] = $statement_count;

	$form_with =$_POST['form_with'];
	
	$site_id = $_SESSION['site_id'];
	
    while ($row = sqlFetchArray($res)) {
		$isBillable = 0;

    	// code added for Additional PHP invoices 
		$PHPdate = '2017-04-01 00:00:00';
		if(strtotime($row['date']) >= strtotime($PHPdate)){
			$isBillable =1;	
		}	
      

      $svcdate = substr($row['date'], 0, 10);
      $duedate = $svcdate; // TBD?
      $duncount = $row['stmt_count'];
	  
	  	// code for stattement no and PDF file Name
	  	$stmtdate = date("YmdHis");
	  	$stmtdate2 = date("Ym");
		$stmt_count1 = str_pad($duncount, 4, "0", STR_PAD_LEFT);
		$stmt_str_lower = strtoupper($site_id);
		$stmt_str = $stmt_str_lower;

      
	  	if ($stmt['cid'] != $row['pid']) {
			if (!empty($stmt)) ++$stmt_count;
			// code added for batch statement by Pawan BUG ID :8802
			if(!isset($_POST['form_pdf'])){
				fwrite($fhprint, create_statement($stmt,$parameter));
			}
			$stmt['cid'] = $row['pid'];
			$stmt['pid'] = $row['pid'];
			$stmt['patient'] = $row['fname'] . ' ' . $row['lname'];
			$stmt['to'] = array($row['fname'] . ' ' . $row['lname']);
			if ($row['street']) $stmt['to'][] = $row['street'];
			
			// code change for BUG ID 10746
			//$stmt['to'][] =  $row['city'] . ", " . $row['state'] . ", " . $row['postal_code'];
			$stmt['to'][] =  $row['city'] . ", " . $row['state'] . " " . $row['postal_code'];
			
			$stmt['to'][] =  $row['phone_home'] ;
			$stmt['lines'] = array();
			$stmt['amount'] = '0.00';
			$stmt['today'] = $today;
			$stmt['duedate'] = $duedate;
			$stmt['encounter'] =$row['encounter'];
			$stmt['dob'] =$row['DOB'];
			
			$stmt['statement_count'] = $statement_count;
			 $statement_count++;
		} 
		else {
			// Report the oldest due date.
		  	if ($duedate < $stmt['duedate']) {
			  $stmt['duedate'] = $duedate;
			}
		}
     
		// Recompute age at each invoice.
		$stmt['age'] = round((strtotime($today) - strtotime($stmt['duedate'])) /
			(24 * 60 * 60));
		
		$invlines = pdf_get_invoice_summary($row['pid'], $row['encounter']);
		$stmt['lines'][] = $invlines;
	
		
		$ac_no = $row['pubpid'];
			
		$statement_count1 = str_pad($statement_count, 4, "0", STR_PAD_LEFT);
		 
		//$pdf_file ="$stmt_str$stmtdate2$statement_count1-$ac_no.pdf";
		
		if($form_with==1){
			$pdf_file ="$stmt_str$pdate-".$ac_no.".pdf";
		}
		else{
			$pdf_file ="$stmt_str$pdate-".$ac_no."-del.pdf";
		}
		
		$pdf_files[] =$pdf_file;
		
	  
	  	if (! $DEBUG &&  $_POST['form_with']) {
	  		
			$encounter_id	= $row['encounter'];
			
			// query for getting status id 
			$status_result = sqlStatement("select id from claim_status where status='Billed to patient'");
			$status_row = sqlFetchArray($status_result);
			$status_id = $status_row['id'];
		 
		 	// query for updating statement count and claim status
			// code update for last modified date by pawan on 24-03-2017
			sqlStatement("UPDATE form_encounter SET " .
			  "last_stmt_date = '$today', stmt_count = stmt_count + 1 ," .
			  "claim_status_id=$status_id,  ".
			   " modified_date='$my_today_date', ".
			  " modified_by=$userID ".
			  "WHERE id = " . $row['id']);
				
		  	// query for getting encounter payment details for balance calculation
			$log_query="SELECT p.pid,f.id, f.pid, f.encounter, f.date, f.last_level_billed, f.last_level_closed, f.last_stmt_date, f.stmt_count,
					p.fname, p.mname, p.lname, p.pubpid,p.dob,
					SUM(b.fee*b.units) as charges,
					(SELECT SUM(a.pay_amount) FROM ar_activity AS a WHERE a.pid = f.pid AND a.encounter = f.encounter ) AS payments
					FROM form_encounter AS f
					JOIN billing As b ON b.encounter=f.encounter 
					JOIN patient_data AS p ON p.pid = f.pid 
					WHERE  b.encounter=$encounter_id 
					AND b.activity=1 AND b.code_type !='COPAY'
					GROUP BY f.encounter
					ORDER BY p.lname, p.fname, p.mname, f.pid, f.encounter  ";
					
			$log_res =sqlStatement($log_query);
					
			 while ($log_row = sqlFetchArray($log_res)) {
	
				$encounter_id =$log_row['encounter'];
				// query for fetching adjustment amount
				$adj_query="select SUM(adj_amount) as adjustment from ar_activity where encounter=$encounter_id";
				$adj_res = sqlStatement($adj_query);
				$adj_row = sqlFetchArray($adj_res);
				$adjustment = $adj_row['adjustment'];
				
				// query for fetching w_o amount
				$wo_query="select SUM(w_o) as wo from ar_activity where encounter=$encounter_id";
				$wo_res = sqlStatement($wo_query);
				$wo_row = sqlFetchArray($wo_res);
				$wo = $wo_row['wo'];
				
				// query for getting patient paid amount
				$ptpaid_query="select SUM(pay_amount) as ptpaid from ar_activity where payer_type = 0 AND encounter=$encounter_id";
				$ptpaid_res = sqlStatement($ptpaid_query);
				$ptpaid_row = sqlFetchArray($ptpaid_res);
				$ptpaid = $ptpaid_row['ptpaid'];
				
				
				//$balance = sprintf("%.2f",$row['charges']-($row['Adjustment']+$row['PriPaid'] + $row['SecPaid'] + $row['TerPaid'] + $row['PtPaid'] + $row['w_o']));
				$balance = sprintf("%.2f",$log_row['charges']-($adjustment+$log_row['payments'] + $wo));
				
				// query for getting insurance company detail
				
				
				$res1 = sqlStatement("SELECT idt.id,idt.pid,idt.type,idt.date,ics.id as insu_id,ics.name,idt.policy_number 
					FROM insurance_data idt INNER JOIN insurance_companies ics ON ics.id = idt.provider 
					WHERE idt.pid = '".$log_row['pid']."' AND 
					idt.date = (SELECT MAX(date) FROM insurance_data WHERE type = idt.type AND pid = idt.pid)
					GROUP BY idt.type");
				
				 while ($row1 = sqlFetchArray($res1)) {
					 $patient_insurance_id[] =$row1['insu_id'];
					 $patient_insurance_name[] =$row1['name'];
				 }
				
				 $patintFirtInsurance_id = $patient_insurance_id[0];
				 $patintFirtInsurance_name = $patient_insurance_name[0];
				
				
				// code for adding information in statement log table 
				$patient_name	= $log_row['fname'] . ', ' . $log_row['lname'];
				$account_no		= $log_row['pubpid'];
				$dob			= $log_row['dob'];
				//$encounter_id	= $logdetail['encounter'];
				$encounter_id	= $encounter_id;
				$balance		= $balance;
				$filename		= $pdf_file;
				$pri_insurance_name	= $patintFirtInsurance_name;
				$pri_insurance_id	=  $patintFirtInsurance_id;
					
					$status="sent";
					
					$sql="	INSERT INTO statement_log(
							`id` ,
							`patient_name` ,
							`account_no` ,
							`dob` ,
							`encounter_id` ,
							`balance` ,
							`date` ,
							`filename` ,
							`status`,
							`pri_insurance`,
							`insu_id`,
							`user`
							)
							VALUES (
							NULL , '$patient_name', '$account_no', '$dob', '$encounter_id', '$balance', '$my_today_date', '$filename','$status','$pri_insurance_name','$pri_insurance_id','$user_name'
							)";
	
				sqlStatement($sql);
					
			} // end of inner while loop
					
		}// end of inner if statement
		  
	  
		$mydata = my_statement($stmt,$parameter); // calling function from statement.inc.php
		
		//$myhtml =$mydata; // all html
		$myhtml =$mydata[0]; // all html
		// code added by pawan for storing statement page count in statement log table for PHP addtional invoices by
		$pageCount = $mydata[1];

		if (! $DEBUG &&  $_POST['form_with']) {
		 	$sql="update statement_log set pagecount=$pageCount,isBillable=$isBillable where filename='$pdf_file'";
			sqlStatement($sql);

		 }
		
		$mpdf=new mPDF();
		//echo $myhtml;
		
		$mpdf->WriteHTML($myhtml);
		
		$site_id = $_SESSION['site_id'];
		
		$file_path=$GLOBALS['OE_SITES_BASE']."/".$site_id ."/patient_statements/";
		
		$mpdf->Output($file_path.$pdf_file,'F');
		//$mpdf->Output();
	} // end of while loop

	
		
		// code added for batch statement by Pawan BUG ID :8802
		$zip_file_name='data.zip';
		zipFilesDownload($pdf_files,$zip_file_name,$file_path,$form_with);

		if (!empty($stmt)) ++$stmt_count;
	} // end statements requested


?>
<html>
<head>
<?php html_header_show(); ?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title><?php xl('Patient Statement - Search','e'); ?></title>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/sortable.js"></script>
<script language="JavaScript">

var mypcc = '1';

function checkAll(checked) {
 var f = document.forms[0];
 for (var i = 0; i < f.elements.length; ++i) {
  var ename = f.elements[i].name;
  if (ename.indexOf('form_cb[') == 0)
   f.elements[i].checked = checked;
 }
}



</script>

<style>
.selected{
font-size:18px;	
text-decoration: underline;
}
/* Sortable tables */
table.sortable thead {
    background-color:#eee;
    color:black;
    font-weight: bold;
    cursor: default;
    font-size: 14px;
}
table.sortable tbody {
    background-color:#eee;
    color:black;
   
    cursor: default;
    font-size: 14px;
}


/* code added for loading image */
.no-js #loader { display: none;  }
.js #loader { display: block; position: absolute; left: 100px; top: 0; }
.se-pre-con {
	position: fixed;
	left: 0px;
	top: 0px;
	width: 100%;
	height: 100%;
	z-index: 9999;
	background: url(preloader.gif) center no-repeat #fff;
}
</style>
<!-- code added for loading image -->
<script type="text/javascript" src="../../library/js/jquery.js"></script>
<script type="text/javascript" src="../../library/js/jquery-1.4.3.min"></script>
<script type="text/javascript" src="../../library/js/modernizr.js"></script>

<script type="text/javascript">
    function ShowLoading(e) {
        var div = document.createElement('div');
        var img = document.createElement('img');
        img.src = 'loading_bar.GIF';
		div.innerHTML ='Loading Patients...';
        div.style.cssText = 'position: fixed; top: 0px; left: 0px; z-index: 9999; width: 100%;height:100%; text-align: center; background: url(preloader.gif) center no-repeat #fff';

        div.appendChild(img);
        document.body.appendChild(div);

        return true;
        // These 2 lines cancel form submission, so only use if needed.
        //window.event.cancelBubble = true;
        //e.stopPropagation();
    }
</script>
<script type="text/javascript">
//paste this code under the head tag or in a separate js file.
	// Wait for window load
	$(window).load(function() {
		// Animate loader off screen
		$(".se-pre-con").fadeOut("slow");;
	});

</script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
	<!--<div class="se-pre-con"></div>-->
<center>

<form method='post' action='sl_eob_search_patient.php' enctype='multipart/form-data'>




<table border='0' cellpadding='5' cellspacing='0'>

 <tr bgcolor='#ddddff'>
  <td>
   <?php xl('Name:','e'); ?>
  </td>
  <td>
   <input type='text' name='form_name' size='10' value='<?php echo $_POST['form_name']; ?>'
    title='<?php xl("Any part of the patient name, or \"last,first\", or \"X-Y\"","e"); ?>'>
  </td>
  <td>
   <?php xl('Chart ID:','e'); ?>
  </td>
  <td>
  <!-- code change for comma separated value validation -->
   <input type='text' name='form_pid' size='10' pattern='^[0-9,+*]+[^,^.^*]$' title="Please input numeric characters only Ex : 100 Or 101,102" value='<?php echo $_POST['form_pid']; ?>'
    title='<?php xl("Patient chart ID","e"); ?>'>
  
  </td>
  <td>
   <?php xl('Encounter:','e'); ?>
  </td>
  <td>
   <input type='text' name='form_encounter' size='10' value='<?php echo $_POST['form_encounter']; ?>'
    title='<?php xl("Encounter number","e"); ?>'>
  </td>
  <td>
   <?php xl('Svc Date:','e'); ?>
  </td>
  <td>
   <input type='text' name='form_date' size='10' value='<?php echo $_POST['form_date']; ?>'
    title='<?php xl("Date of service mm/dd/yyyy","e"); ?>'>
  </td>
  <td>
   <?php xl('To:','e'); ?>
  </td>
  <td>
   <input type='text' name='form_to_date' size='10' value='<?php echo $_POST['form_to_date']; ?>'
    title='<?php xl("Ending DOS mm/dd/yyyy if you wish to enter a range","e"); ?>'>
  </td>
  
 	 <td>
   <?php xl('Billing Cycle','e'); ?>
  </td>
  <td>
  
  <select name="stmt_aging" value="<?php echo $_POST['stmt_aging'];?>">
                <option value="">--ALL--</option>
                <option value="0-30">0-30</option>
                <option value="31-60">31-60</option>
                <option value="61-90">61-90</option>
                <option value="91-120">91-120</option>
              </select>
  </td>
 
  <!--------Code is added for patient statement BUG ID:8802-------------------->
   <td>
   <?php xl('Aging','e'); ?>
  </td>
  <td>
   <select name="aging_type" value="<?php echo $_POST['aging_type'];?>">
               
             	<option value="dos">DOS</option>
                <option value="billed_date">Billed Date</option>
                
   </select>
              
  <select name="aging_value" value="<?php echo $_POST['aging_value'];?>">
                <option value="">--ALL--</option>
                <option value="0-30">0-30</option>
                <option value="31-60">31-60</option>
                <option value="61-90">61-90</option>
                <option value="91-120">91-120</option>
              </select>
  </td>
  
  
  <td>
   <input type='submit' name='form_search' value='<?php xl("Search","e"); ?>'>
   </td>
   
 </tr>
 


 <tr>
  <td height="1" colspan="10">
  </td>
 </tr>

</table>

<!--<a href="statement_search.php" target="_blank">Previous Statement Search</a>-->

<?php
$querystring ="";
if ($_POST['form_search'] || $_POST['form_print'] || $_GET['form_search']) {
	if($_POST['form_search']){
		$form_name      = trim($_POST['form_name']);
		$form_pid       = trim($_POST['form_pid']);
		$form_encounter = trim($_POST['form_encounter']);
		$form_date      = fixDate($_POST['form_date'], "");
		$form_to_date   = fixDate($_POST['form_to_date'], "");
		$form_stmt_aging= trim($_POST['stmt_aging']);
		$form_aging_type = trim($_POST['aging_type']);
		$form_aging_value = trim($_POST['aging_value']);
	}
	else{
	
		$form_name      = trim($_GET['form_name']);
		$form_pid       = trim($_GET['form_pid']);
		$form_encounter = trim($_GET['form_encounter']);
		$form_date      = fixDate($_GET['form_date'], "");
		$form_to_date   = fixDate($_GET['form_to_date'], "");
		$form_stmt_aging= trim($_GET['stmt_aging']);
		$form_aging_type = trim($_GET['aging_type']);
		$form_aging_value = trim($_GET['aging_value']);
	
	
	}
  
  	$where = "";
	
	

  	$rec_limit = 100;

  //	if ($INTEGRATED_AR) {
    	if ($eracount) {
      	// Note that parse_era() modified $eracount and $where.
      	if (! $where) $where = '1 = 2';
    	}
    	else {
      		if ($form_name) {
				$querystring .="&form_name=$form_name";
        		if ($where) $where .= " AND ";
        		// Allow the last name to be followed by a comma and some part of a first name.
        		if (preg_match('/^(.*\S)\s*,\s*(.*)/', $form_name, $matches)) {
          			$where .= "p.lname LIKE '" . $matches[1] . "%' AND p.fname LIKE '" . $matches[2] . "%'";
        		// Allow a filter like "A-C" on the first character of the last name.
        		} 
				else if (preg_match('/^(\S)\s*-\s*(\S)$/', $form_name, $matches)) {
          			$tmp = '1 = 2';
          	
					while (ord($matches[1]) <= ord($matches[2])) {
            			$tmp .= " OR p.lname LIKE '" . $matches[1] . "%'";
            			$matches[1] = chr(ord($matches[1]) + 1);
				  	}
				  
				  	$where .= "( $tmp ) ";
				} 
				else {
          			$where .= "p.lname LIKE '%$form_name%'";
        		}
      		}
      
	  		if ($form_pid) {
				$querystring .="&form_pid=$form_pid";
        		if ($where) $where .= " AND ";
        		//$where .= "f.pid = '$form_pid'";
				// code change for comma separated value validation 
				 $where .= "p.pubpid IN ($form_pid)";
      		}
      		if ($form_encounter) {
				$querystring .="&form_encounter=$form_encounter";
        		if ($where) $where .= " AND ";
        		$where .= "f.encounter = '$form_encounter'";
      		}
      		if ($form_date) {
				$querystring .="&form_date=$form_date";
        		if ($where) $where .= " AND ";
        		if ($form_to_date) {
					$querystring .="&form_to_date=$form_to_date";
          			$where .= "f.date >= '$form_date' AND f.date <= '$form_to_date'";
        		}
        		else {
          			$where .= "f.date = '$form_date'";
        		}
      		}
	  
		  // code added for aging by last statement date
		   if ($form_stmt_aging) {
			//if ($where) $where .= " AND ";
				if($form_stmt_aging =="0-30"){
					$querystring .="&form_stmt_aging=$form_stmt_aging";
					if ($where) $where .= " AND ";
					$where .= " DATEDIFF(NOW(),f.last_stmt_date) >= 0 AND DATEDIFF(NOW(),f.last_stmt_date) <= 30 ";
				}
				elseif($form_stmt_aging =="31-60"){
					$querystring .="&form_stmt_aging=$form_stmt_aging";
					if ($where) $where .= " AND ";
					$where .= "  DATEDIFF(NOW(),f.last_stmt_date) >= 31 AND DATEDIFF(NOW(),f.last_stmt_date) <= 60 ";
				}
				elseif($form_stmt_aging =="61-90"){
					$querystring .="&form_stmt_aging=$form_stmt_aging";
					if ($where) $where .= " AND ";
					$where .= " DATEDIFF(NOW(),f.last_stmt_date) >= 61 AND DATEDIFF(NOW(),f.last_stmt_date) <= 90 ";
				}
				elseif($form_stmt_aging =="91-120"){
					$querystring .="&form_stmt_aging=$form_stmt_aging";
					if ($where) $where .= " AND ";
					$where .= "  DATEDIFF(NOW(),f.last_stmt_date) >= 91 AND DATEDIFF(NOW(),f.last_stmt_date) <= 120 ";
				}
				else{
					//$where .= " AND DATEDIFF(NOW(),fe.date) > 120  ";
					$where .="";
				}
			//echo $where;
			//die;
			}
		
			// code added for aging by DOS date
		   if ($form_aging_type && !empty($form_aging_value)) {
			//if ($where) $where .= " AND ";
				if($form_aging_type=="dos"){
					$querystring .="&form_aging_type=$form_aging_type";
					$aging_date ="f.date";
				}
				else{
					$querystring .="&form_aging_type=$form_aging_type";
					$aging_date ="b.bill_date";
					
				}
								
				if($form_aging_value =="0-30"){
					$querystring .="&form_aging_value=$form_aging_value";
					if ($where) $where .= " AND ";
					$where .= " DATEDIFF(NOW(),$aging_date) >= 0 AND DATEDIFF(NOW(),$aging_date) <= 30 ";
				}
				elseif($form_aging_value =="31-60"){
					$querystring .="&form_aging_value=$form_aging_value";
					if ($where) $where .= " AND ";
					$where .= "  DATEDIFF(NOW(),$aging_date) >= 31 AND DATEDIFF(NOW(),$aging_date) <= 60 ";
				}
				elseif($form_aging_value =="61-90"){
					$querystring .="&form_aging_value=$form_aging_value";
					if ($where) $where .= " AND ";
					$where .= " DATEDIFF(NOW(),$aging_date) >= 61 AND DATEDIFF(NOW(),$aging_date) <= 90 ";
				}
				elseif($form_aging_value =="91-120"){
					$querystring .="&form_aging_value=$form_aging_value";
					if ($where) $where .= " AND ";
					$where .= "  DATEDIFF(NOW(),$aging_date) >= 91 AND DATEDIFF(NOW(),$aging_date) <= 120 ";
				}
				else{
					//$where .= " AND DATEDIFF(NOW(),fe.date) > 120  ";
					$where .="";
				}
			//echo $where;
			
			//die;
			}
	  // code added for patient statement BUG id 8802 end
	   
      if (! $where) {
        if ($_POST['form_category'] == 'All') {
          die(xl("At least one search parameter is required if you select All."));
        } else {
          $where = "1 = 1";
        }
      }
  //  }


$query2 = "SELECT p.pid,f.id, f.pid, f.encounter, f.date, f.last_level_billed, f.last_level_closed, f.last_stmt_date, f.stmt_count,
 				p.fname, p.mname, p.lname, p.pubpid,
				SUM(b.fee*b.units) as charges,
				(SELECT SUM(a.pay_amount) FROM ar_activity AS a WHERE a.pid = f.pid AND a.encounter = f.encounter ) AS payments
				FROM form_encounter AS f
				JOIN billing As b ON b.encounter=f.encounter 
				JOIN patient_data AS p ON p.pid = f.pid 
				WHERE  $where AND f.claim_status_id=10
				AND b.activity=1 AND b.code_type !='COPAY'
				GROUP BY f.encounter
				ORDER BY p.lname, p.fname, p.mname, f.pid, f.encounter";
	
		
$res = sqlStatement($query2);

	// code for pagination
	
	$rec_count = sqlNumRows($res);

	if( isset($_GET{'page'} ) )
	{
	   $page = $_GET{'page'} + 1;
	   $offset = $rec_limit * $page ;
	}
	else
	{
	   $page = 0;
	   $offset = 0;
	}
	$left_rec = $rec_count - ($page * $rec_limit);
	
	$query3="SELECT p.pid,f.id, f.pid, f.encounter, f.date, f.last_level_billed, f.last_level_closed, f.last_stmt_date, f.stmt_count,
 				p.fname, p.mname, p.lname, p.pubpid,
				SUM(b.fee*b.units) as charges,
				(SELECT SUM(a.pay_amount) FROM ar_activity AS a WHERE a.pid = f.pid AND a.encounter = f.encounter ) AS payments
				FROM form_encounter AS f
				JOIN billing As b ON b.encounter=f.encounter 
				JOIN patient_data AS p ON p.pid = f.pid 
				WHERE  $where AND f.claim_status_id=10
				AND b.activity=1 AND b.code_type !='COPAY'
				GROUP BY f.encounter
				ORDER BY p.lname, p.fname, p.mname, f.pid, f.encounter ";

				//ORDER BY p.lname, p.fname, p.mname, f.pid, f.encounter  LIMIT $offset, $rec_limit";
	

	$t_res = sqlStatement($query3);


    $num_invoices = sqlNumRows($t_res);

    if ($eracount && $num_invoices != $eracount) {
      $alertmsg .= "Of $eracount remittances, there are $num_invoices " .
        "matching encounters in OpenEMR. ";
    }
  } // end $INTEGRATED_AR


 

?>

<table  border='0' cellpadding='1' cellspacing='2' width='98%'>
<tr bgcolor="#ddddff"><td colspan="12">Total Records : <?php echo $rec_count;?></td></tr>
</table>

<table class="sortable" border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   &nbsp;<?php xl('Patient','e'); ?>
  </td>
  <td class="dehead">
   &nbsp;<?php xl('Insurance Name','e'); ?>
  </td>
  <td class="dehead" >
   <?php xl('External ID','e'); ?>&nbsp;
  </td>
  <td class="dehead" >
   <?php xl('Encounter ID','e'); ?>&nbsp;
  </td>
  <td class="dehead">
   &nbsp;<?php xl('Svc Date','e'); ?>
  </td>
  <td class="dehead">
   &nbsp;<?php xl($INTEGRATED_AR ? 'Last Stmt' : 'Due Date','e'); ?>
  </td>
  <td class="dehead" align="right">
   <?php xl('Charge','e'); ?>&nbsp;
  </td>
  
  <td class="dehead" align="right">
   <?php xl('Paid','e'); ?>&nbsp;
  </td>
 
  <td class="dehead" align="right">
   <?php xl('Balance','e'); ?>&nbsp;
  </td>
  <td class="dehead" align="center">
   <?php xl('Prv','e'); ?>
  </td>
  <!--- Added by Pawan for patient statement----------->
  <td class="dehead" align="right">
   <?php xl('St. Aging','e'); ?>&nbsp;
  </td>
<?php if (!$eracount) { ?>
  <td class="dehead" align="left">
   <?php xl('Sel','e'); ?>
  </td>
  
<?php } ?>
 </tr>

<?php

$demo_path = "../../interface/patient_file/summary/demographics.php";
//$demo_path = "../../interface/patient_file/summary/demographics_full.php";

$orow = -1;

$statement_array1 = array();
$statement_array2 = array();

  //if ($INTEGRATED_AR) {
    while ($row = sqlFetchArray($t_res)) {
	
		$encounter_id =$row['encounter'];
		// query for fetching adjustment amount
		$adj_query="select SUM(adj_amount) as adjustment from ar_activity where encounter=$encounter_id";
		$adj_res = sqlStatement($adj_query);
		$adj_row = sqlFetchArray($adj_res);
		$adjustment = $adj_row['adjustment'];
		
		// query for fetching w_o amount
		$wo_query="select SUM(w_o) as wo from ar_activity where encounter=$encounter_id";
		$wo_res = sqlStatement($wo_query);
		$wo_row = sqlFetchArray($wo_res);
		$wo = $wo_row['wo'];
		
		// query for getting patient paid amount
		$ptpaid_query="select SUM(pay_amount) as ptpaid from ar_activity where payer_type = 0 AND encounter=$encounter_id";
		$ptpaid_res = sqlStatement($ptpaid_query);
		$ptpaid_row = sqlFetchArray($ptpaid_res);
		$ptpaid = $ptpaid_row['ptpaid'];
		
		//$balance = sprintf("%.2f",$row['charges']-($row['Adjustment']+$row['PriPaid'] + $row['SecPaid'] + $row['TerPaid'] + $row['PtPaid'] + $row['w_o']));
		$balance = sprintf("%.2f",$row['charges']-($adjustment+$row['payments'] + $wo));
		$statement_array1['balance'] = $balance;
     
      	$duncount = $row['stmt_count'];
      	$statement_array1['stmt_count'] =  $row['stmt_count'];

     	$isdueany = ($balance > 0);

    	$isduept = " checked";

		$bgcolor = ((++$orow & 1) ? "#ffdddd" : "#ddddff");

      	$svcdate = substr($row['date'], 0, 10);
     	 $last_stmt_date = empty($row['last_stmt_date']) ? '-' : $row['last_stmt_date'];
	  
		   // Recompute age at each invoice.
		  // added by Pawan for patient statement Bug id 8802
		  if($last_stmt_date=="-"){
			$stmt_age ="0";	
		  }
		  else{
			$stmt_age = round((strtotime($today) - strtotime($last_stmt_date)) /
					(24 * 60 * 60));
		  }
		 
		 // query for insurance name
		 $inspid =$row['pid'];
		 $insu_query = "SELECT provider,ic.name FROM insurance_data as isd
						INNER join insurance_companies as ic 
						ON isd.provider=ic.id
						where  isd.pid=$inspid and isd.type='primary' order by isd.date desc LIMIT 1";
		 $insu_res = sqlStatement($insu_query);
		 $insu_row = sqlFetchArray($insu_res);
		 $insu_name = $insu_row['name']; 	
		 
		/*$tes=" SELECT idt.id,idt.pid,idt.type,idt.date,ics.id as insu_id,ics.name,idt.policy_number 
					FROM insurance_data idt INNER JOIN insurance_companies ics ON ics.id = idt.provider 
					WHERE idt.pid = '". $inspid."' AND 
					idt.date = (SELECT MAX(date) FROM insurance_data WHERE type = idt.type AND pid = idt.pid)
					GROUP BY idt.type";*/
		//echo $tes;
		 
		/* $insu_res = sqlStatement("SELECT idt.id,idt.pid,idt.type,idt.date,ics.id as insu_id,ics.name,idt.policy_number 
					FROM insurance_data idt INNER JOIN insurance_companies ics ON ics.id = idt.provider 
					WHERE idt.pid = '". $inspid."' AND 
					idt.date = (SELECT MAX(date) FROM insurance_data WHERE type = idt.type AND pid = idt.pid)
					GROUP BY idt.type");
				
				 while ($insu_row = sqlFetchArray($insu_res)) {
					 $patient_insurance_id[] =$insu_row['insu_id'];
					 $patient_insurance_name[] =$insu_row['name'];
				 }
				
				// $patintFirtInsurance_id = $patient_insurance_id[0];
				 $insu_name = $patient_insurance_name[0];*/

				 $statement_array1['encounter_id'] =$row['encounter'];
				 $statement_array1['patient'] = $row['fname'] . ', ' . $row['lname'];
				 $statement_array1['insu_name'] = $insu_name;
				 $statement_array1['external_id'] = $row['pubpid'] ;
				 $statement_array1['svcdate'] = $svcdate;
				 $statement_array1['last_stmt_date'] = $last_stmt_date;
				 $statement_array1['charge'] = $row['charges'];
				 $statement_array1['paid'] = $row['payments'] - $row['copays'];
				 $statement_array1['balance'] = $balance;
				 $statement_array1['stmt_count'] = $duncount ? $duncount : "0";
				 $statement_array1['stmt_age'] = $stmt_age;
				 $statement_array1['id']  = $row['id'];

				 $statement_array2[] = $statement_array1;
	  
?>
 
 
<?php
    } // end while
	
	include 'pagination.class.php';
    $_GET['form_search']='form_search';
	//echo "<pre>";
	//print_r($statement_array2);


	/*****************************************/
	// If we have an array with items
        if (count($statement_array2)) {
          // Create the pagination object
          $pagination = new pagination($statement_array2, (isset($_GET['page']) ? $_GET['page'] : 1), 100);
          // Decide if the first and last links should show
          $pagination->setShowFirstAndLast(false);
          // You can overwrite the default seperator
          $pagination->setMainSeperator(' | ');
          // Parse through the pagination class
          $productPages = $pagination->getResults();



          // If we have items 
          if (count($productPages) != 0) {
            // Create the page numbers
            echo $pageNumbers = '<div class="numbers">'.$pagination->getLinks($_GET).'</div>';
           // echo "<a href=\"$_PHP_SELF?page=$last&form_search=form_search$querystring\">Last 100 Records</a>"
            // Loop through all the items in the array
             
             
            foreach ($productPages as $patientArray) {
              // Show the information about the item
              //echo '<p><b>'.$productArray['Product'].'</b> &nbsp; &pound;'.$productArray['Price'].'</p>';

            	
				$patient_name = $patientArray['patient'];
				$insu_name = $patientArray['insu_name'];
				$external_id = $patientArray['external_id'];
				$encounter = $patientArray['encounter_id'];
				$svcdate = $patientArray['svcdate'];
				$last_stmt_date = $patientArray['last_stmt_date'];
				$charge = $patientArray['charge'];
				$paid = $patientArray['paid'];
				$balance = $patientArray['balance'];
				$stmt_count = $patientArray['stmt_count'];
				$stmt_age = $patientArray['stmt_age'];
				$encounter_id = $patientArray['id']; // primary id from form_encounter table
				
				
				
				?>
				<tr bgcolor='<?php echo $bgcolor ?>'>
				
                        <td class="detail" align="right">&nbsp;<?php echo $patient_name; ?></td>
                        <td class="detail" align="left">&nbsp;<?php echo $insu_name; ?></td>
                       
                        <td class="detail" align="right">&nbsp;<?php echo $external_id; ?></td>
                        <td class="detail" align="right">&nbsp;<?php echo $encounter; ?></td>
                        <td class="detail" align="right"><?php echo oeFormatShortDate($svcdate); ?>&nbsp;</td>
                 		
                        <td class="detail" align="left"><?php echo $last_stmt_date;?></td>
                        <td class="detail" align="left"><?php echo bucks($charge);?></td>
                        <td class="detail" align="left"><?php echo  bucks($paid);?></td>
                        <!--<td class="detail" align="left">&nbsp;<?php echo $insu_name; ?></td>-->
                        <td class="detail" align="right">&nbsp;<?php echo  bucks($balance); ?></td>
                        <td class="detail" align="right"><?php echo $stmt_count;?> </td>
                        <td class="detail" align="right"><?php echo $stmt_age; ?>&nbsp;</td>
                        <td class="detail" align="left"><input type='checkbox' name='form_cb[<?php echo $encounter_id ?>]'<?php echo $isduept ?> />
                        <input type="hidden" name="pageno" value="<?php echo $page ?>">
                        </td>
                    </tr>
				
				
				<?php
            }
            // print out the page numbers beneath the results
            //echo $pageNumbers;
          }
        }


 // } // end $INTEGRATED_AR

} // end search/print logic

//if (!$INTEGRATED_AR) SLClose();
?>

</table>

<p>

<input type='button' value='<?php xl('Select All','e')?>' onclick='checkAll(true)' /> &nbsp;
<input type='button' value='<?php xl('Clear All','e')?>' onclick='checkAll(false)' /> &nbsp;

<input type='submit' name='form_pdf' value='<?php xl('PDF Download Selected Statements','e'); ?>' /> &nbsp;

<input type='checkbox' name='form_with' value='1' /> <?php xl('With Update','e'); ?>
</p>

</form>
</center>

</body>
</html>
