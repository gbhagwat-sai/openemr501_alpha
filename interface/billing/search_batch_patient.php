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
require_once("$srcdir/sql-ledger.inc");
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

// function added for check the patient responsible BUG ID 11239
function getPatientResponsible($patient_id, $encounter_id){

	$row = sqlQuery("SELECT date, last_level_billed, last_level_closed " .
    "FROM form_encounter WHERE " .
    "pid = ? AND encounter = ? " .
    "ORDER BY id DESC LIMIT 1", array($patient_id,$encounter_id) );
  if (empty($row)) return -1;
  $next_level = $row['last_level_closed'] + 1;
  if ($next_level <= $row['last_level_billed'])
    return $next_level;
  if (arGetPayerID($patient_id, substr($row['date'], 0, 10), $next_level))
    return $next_level;
  // There is no unclosed insurance, so see if there is an unpaid balance.
  // Currently hoping that form_encounter.balance_due can be discarded.
 /* $balance = 0;
  $codes = ar_get_invoice_summary($patient_id, $encounter_id);
  foreach ($codes as $cdata) $balance += $cdata['bal'];
  if ($balance > 0) return 0;
  return -1;*/
}


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
 code = b.code and encounter = b.encounter and  modifier = b.modifier and (pay_amount>'0.00' or reason_code='wo' ) and payer_type!='0'),0) as w_o
from billing b 
inner join form_encounter fe  on fe.encounter = b.encounter
INNER JOIN users u ON u.id = fe.provider_id
where b.code_type = 'CPT4' and b.activity=1 and b.fee <> 0.01 and b.encounter = $encounter_id
and b.pid = $patient_id group by b.encounter,b.code,b.modifier ");



	while ($row1 = sqlFetchArray($res1)) {
		$code[] = $row1;
		//echo "hello";
		//echo $row1['DOS'];
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



  if (($_POST['form_print'] || $_POST['form_download'] || ($_POST['form_pdf']) && $_POST['form_cb'])) {

	// code added for batch statement by Pawan BUG ID :8802
	if(!isset($_POST['form_pdf'])){
		$fhprint = fopen($STMT_TEMP_FILE, 'w');
		if($_POST['form_download'])
		$parameter = "text";
		else
		$parameter="";
	}
	
    $where = "";
    //foreach ($_POST['form_cb'] as $key => $value) $where .= " OR f.id IN( $key)";
	$feids = implode(",", array_keys($_POST['form_cb']));
	$where .= " f.id IN($feids) ";
	
	

    $res = sqlStatement("SELECT " .
      "f.id, f.date, f.pid, f.encounter, f.stmt_count, f.last_stmt_date, " .
      "p.fname,p.DOB,p.pubpid, p.mname, p.lname, p.street, p.city, p.state, p.postal_code,p.phone_home,p.last_stmt_date as patient_last_statement,p.statement_count " .
      "FROM form_encounter AS f, patient_data AS p " .
      "WHERE ( $where ) AND " .
      "p.pid = f.pid " .
      "ORDER BY p.lname, p.fname, f.pid, f.date, f.encounter");
	  
	  
	  
	$count_res = sqlStatement("select account_no,date,patient_name,dob,SUM(balance) as balance from statement_log  where MONTH(CURDATE())=MONTH(date) group by date,account_no");
	 $statement_count = sqlNumRows($count_res); 

    $stmt = array();
    $stmt_count = 0;
	
	$form_with =$_POST['form_with'];
	
	$site_id = $_SESSION['site_id'];

	$patient_array= array();
	
    while ($row = sqlFetchArray($res)) {
		$isBillable = 0;
	
		$PHPdate = '2017-04-01 00:00:00';
		if(strtotime($row['date']) >= strtotime($PHPdate)){
			$isBillable =1;	
		}	
      
	  $svcdate = substr($row['date'], 0, 10);
      $duedate = $svcdate; // TBD?
      $duncount = $row['stmt_count'];
	  
	  $patient_stmt_count = $row['statement_count'];
	  $patient_last_stmt_date = $row['patient_last_statement'];
	  
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
			  "claim_status_id=$status_id , ".
			  " modified_date='$my_today_date' ,".
			  " modified_by=$userID ".
			  "WHERE id = " . $row['id']);
			  
			
			  
			  
			  
			  
				
		  	// query for getting encounter payment details for balance calculation
			$payment_query="SELECT p.pid,f.id, f.pid, f.encounter, f.date, f.last_level_billed, f.last_level_closed, f.last_stmt_date, f.stmt_count,
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
					
			$payment_res =sqlStatement($payment_query);
					
			while ($payment_row = sqlFetchArray($payment_res)) {
	
				$encounter_id =$payment_row['encounter'];
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
				$ptpaid = $ptpaid_query['ptpaid'];
				
				
				//$balance = sprintf("%.2f",$row['charges']-($row['Adjustment']+$row['PriPaid'] + $row['SecPaid'] + $row['TerPaid'] + $row['PtPaid'] + $row['w_o']));
				$balance = sprintf("%.2f",$payment_row['charges']-($adjustment+$payment_row['payments'] + $wo));
				
				// query for getting insurance company detail
				
				
				$res1 = sqlStatement("SELECT idt.id,idt.pid,idt.type,idt.date,ics.id as insu_id,ics.name,idt.policy_number 
					FROM insurance_data idt INNER JOIN insurance_companies ics ON ics.id = idt.provider 
					WHERE idt.pid = '".$payment_row['pid']."' AND 
					idt.date = (SELECT MAX(date) FROM insurance_data WHERE type = idt.type AND pid = idt.pid)
					GROUP BY idt.type");
				
				 while ($row1 = sqlFetchArray($res1)) {
					 $patient_insurance_id[] =$row1['insu_id'];
					 $patient_insurance_name[] =$row1['name'];
				 }
				
				 $patintFirtInsurance_id = $patient_insurance_id[0];
				 $patintFirtInsurance_name = $patient_insurance_name[0];
				
				
				// code for adding information in statement log table 
				$patient_name	= $payment_row['fname'] . ', ' . $payment_row['lname'];
				$account_no		= $payment_row['pubpid'];
				$dob			= $payment_row['dob'];
				//$encounter_id	= $logdetail['encounter'];
				$encounter_id	= $encounter_id;
				$balance		= $balance;
				$filename		= $pdf_file;
				$pri_insurance_name	= $patintFirtInsurance_name;
				$pri_insurance_id	=  $patintFirtInsurance_id;
				$mode = "Batch";
					
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
							`user`,
							`print_mode`
							)
							VALUES (
							NULL , '$patient_name', '$account_no', '$dob', '$encounter_id', '$balance', '$my_today_date', '$filename','$status','$pri_insurance_name','$pri_insurance_id','$user_name','$mode'
							)";
	
				sqlStatement($sql);
					
			} // end of inner while loop
					
		}// end of inner if statement
		
		if(isset($_POST['form_with'])){
			$patient_array[] = $row['pid']; 
		}
		
				
	  
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
		
		$mpdf->WriteHTML($myhtml);
		
		$site_id = $_SESSION['site_id'];
		
		$file_path=$GLOBALS['OE_SITES_BASE']."/".$site_id ."/patient_statements/";
		
		$mpdf->Output($file_path.$pdf_file,'F');
		
		// code added for refresh URL after PDF download to avoid resubmit the form
		header( "refresh:0;url=search_batch_patient.php" );
		//$mpdf->Output();
	} // end of while loop

	
	if(isset($_POST['form_with'])){
		$patient_update =array_unique($patient_array);
		// print_r($patient_update);
		 
		 foreach($patient_update as $value){
		   // query for updating statement count and statement date to patient
				sqlStatement("UPDATE patient_data SET " .
				  "last_stmt_date = '$today', statement_count = statement_count + 1 " .
				  "WHERE pid = " . $value);
				  
		}
	}
		
		// code added for batch statement by Pawan BUG ID :8802
		$zip_file_name='data.zip';
		zipFilesDownload($pdf_files,$zip_file_name,$file_path,$form_with);

		if (!empty($stmt)) ++$stmt_count;
	} // end statements requested




if ($_POST['form_csvexport'] || $_POST['export_all']) {
	
	$csv_file = "Statement_Batch_".$pdate.".csv";
	header("Pragma: public");
  	header("Expires: 0");
  	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  	header("Content-Type: application/force-download");
  	header("Content-Disposition: attachment; filename=$csv_file");
  	header("Content-Description: File Transfer");
  
  	// CSV headers:
  
  	echo '"' . xl('External ID') . '",';
  	echo '"' . xl('Patient Name') . '",';
  
  	echo '"' . xl('Last Staement Date') . '",';
  	echo '"' . xl('Statement Since pmt') . '",';
  	echo '"' . xl('Patient Balance') . '",';
  	echo '"' . xl('Patient Alert') . '",';
  	echo '"' . xl('Collection Alert') . '",';
  	echo '"' . xl('General Reason') . '",'. "\n";
  	
				
}
else{
?>
<html>
<head>
<?php html_header_show(); ?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title><?php xl('Batch Printing - Search','e'); ?></title>
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
    text-align: left;
}

table.sortable tbody {
    background-color:#eee;
    color:black;
   
    cursor: default;
    font-size: 14px;
    text-align: left;
}

table.sortable td{
  	
    color:black;
   	border: 1px solid burlywood;
    cursor: default;
    font-size: 14px;
    text-align: left;
}

/* code added for loading image */
.no-js #loader {/* display: none; */  }
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
	/*$(window).load(function() {
		// Animate loader off screen
		$(".se-pre-con").fadeOut("slow");;
	});*/

	

</script>


<!-- Code For hiding Print Statement -->
<script type='text/javascript' src='http://code.jquery.com/jquery.min.js'></script>
<script type="application/javascript">
$(document).ready(function() {
    $('#download_pdf').click(function(e) {
        //e.preventDefault();
        $('#download_pdf').hide();
        //$("#batchPrinting").submit();   
    });
});
</script>
<!-- End -->
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'  bgcolor="#ddddff">
<!--	<div class="se-pre-con"></div>-->
<center>

<form method='post' action='search_batch_patient.php' name="batchPrinting" id="batchPrinting" enctype='multipart/form-data'>
    

<?php
	// code for getting details of last  batch of statement
	$last_batch_res = sqlStatement("select COUNT(distinct(account_no)) as No_of_patient,date,user from statement_log where print_mode='Batch' group by date order by date desc limit 1");
	$last_batch_row = sqlFetchArray($last_batch_res);
	$no_of_patient =  $last_batch_row['No_of_patient'];
	$last_batch_date1 =  $last_batch_row['date'];
	$last_bach_user = $last_batch_row['user'];
	
	
	//$date = '08/04/2010 22:15:00';
	$last_batch_date = date('l, F d, Y,  h:i:s A', strtotime($last_batch_date1));
?>
<table border='0' cellpadding='5' cellspacing='0'>
	<?php if(empty($last_batch_date1)){
	?>
   		<tr><td>No Batch Printed.</td></tr>
    <?php 
	}
	else{ ?>
    	<tr><td>The last batch of <?php echo $no_of_patient;?> patient statements was sent on <?php echo $last_batch_date; ?> by <?php echo $last_bach_user;?></td></tr>
    <?php } ?>    
 	<tr><td align="center"><input type='submit' name='form_search' id="form_search" value='<?php xl("Show Patients","e"); ?>' onclick="ShowLoading()"></td> </tr>
	<tr><td height="1" colspan="10"></td></tr>

</table>

<?php
} // end of else  export
$querystring ="";
if ($_POST['form_search'] ||  $_POST['form_csvexport'] || $_POST['export_all'] || $_GET['form_search']) {
	
	
	
	// code for getting patient statement configurations
 	$stmt_query="select * from statement_config";
	$stmt_res = sqlStatement($stmt_query);
	$stmt_row = sqlFetchArray($stmt_res);
	$cycle_days =  $stmt_row['day_bet_stmt'];
	// code added on 04/11/2018 for avoiding error when statement configuration form is not filled.
	if(empty($cycle_days)){
			$cycle_days="30";
	}
	$page_limit = $stmt_row['page_limit'];
	if(empty($stmt_row['page_limit'])){
		$page_limit = "30";
	}
	$min_balance = 	$stmt_row['min_bal_stmt'];
	
		
	/*$query3="select group_concat(distinct(fe.id)) as feid,group_concat(distinct(fe.encounter)) as encounter,fe.pid, fe.date, 
				p.last_stmt_date, p.statement_count,sum(ar.pay_amount) as payments,
				CONCAT(p.fname,',',p.lname) AS patient_name, p.pubpid,p.stop_stmt,p.last_stmt_date,p.statement_count,p.Alert_note as patient_alert,p.col_alert,p.stop_stmt
				from form_encounter as fe 
				JOIN patient_data AS p ON p.pid = fe.pid
				LEFT JOIN ar_activity as ar on ar.encounter = fe.encounter
				where (p.stop_stmt IS NULL OR p.stop_stmt NOT like '%stop_stmt%') and fe.claim_status_id IN('10','11') and  (DATEDIFF(NOW(),p.last_stmt_date) >= 				
				$cycle_days OR p.last_stmt_date	IS NULL)  group by fe.pid  ORDER BY p.lname, p.fname, fe.pid, fe.encounter";*/
				
	$query3="select group_concat(distinct(fe.id)) as feid,group_concat(distinct(fe.encounter)) as encounter,fe.pid, fe.date, 
				p.last_stmt_date, p.statement_count,sum(ar.pay_amount) as payments,SUM(adj_amount) as adjustment,SUM(w_o) as wo,
				CONCAT(p.fname,',',p.lname) AS patient_name, p.pubpid,p.stop_stmt,p.last_stmt_date,p.statement_count,p.Alert_note as patient_alert,p.col_alert,p.general_reason,p.stop_stmt
				from form_encounter as fe 
				JOIN patient_data AS p ON p.pid = fe.pid
				LEFT JOIN ar_activity as ar on ar.encounter = fe.encounter
				where  (p.stop_stmt IS NULL OR p.stop_stmt NOT like '%stop_stmt%') and fe.claim_status_id IN('10','11') and  (DATEDIFF(NOW(),p.last_stmt_date) >= 				
				$cycle_days OR p.last_stmt_date	IS NULL)  group by fe.pid  ORDER BY p.lname, p.fname, fe.pid, fe.encounter  ";
					
	//echo $query3;
		
	$t_res = sqlStatement($query3);
	
	$rec_count = sqlNumRows($t_res);

	$demo_path = "../../interface/patient_file/summary/demographics.php";
	//$demo_path = "../../interface/patient_file/summary/demographics_full.php";
	$patinet_sub_array = array();
	$patinet_array = array();
	$filtered_array = array();
  
  
    while ($row = sqlFetchArray($t_res)) {
		
		$patient_id = $row['pid'];
		$encounter_id =$row['encounter'];
		$stop_stmt = $row['stop_stmt'];
		//code added for BUG 11242 self pay validation
		$encounter_array = explode(",",$encounter_id);
		$encounter_no = array();
		//$fe_id_array = array();
		
		
		/*foreach($encounter_array as $value){
			
			$self_pay = $stop_stmt;
			$self_payArray = explode("|",$self_pay);
			$self_pay = $self_payArray[0];
			
			if($sel_pay=="self_pay"){
			
				$encounter_no[]=$value;
				$fe_id_res = sqlQuery("select id from form_encounter where encounter=$value");
				$feid =$fe_id_res['id'];
				$fe_id_array[] = $feid;
			
			}
			else{
				$pat_res=getPatientResponsible($patient_id,$value);
				if($pat_res==0){
					$encounter_no[]=$value;
					$fe_id_res = sqlQuery("select id from form_encounter where encounter=$value");
					$feid =$fe_id_res['id'];
					$fe_id_array[] = $feid;
				}
			}
			
			
		}*/
		
		if(!empty($encounter_array)){
		
			$encounter_ids = implode(",",$encounter_array);
			
		
		
			//echo $patient_id."-----";
			//echo $encounter_ids;
			//echo "????????";
			
			$fe_id =$row['feid'];
			$fe_ids = $row['feid'];
			
			$charge_query="select SUM(b.fee*b.units) as charges from billing as b where b.encounter IN(".$encounter_ids.")
	AND b.activity=1 AND b.code_type !='COPAY'";

			//echo $charge_query;

			$charge_res = sqlStatement($charge_query);
			$charge_row = sqlFetchArray($charge_res);
			$charges = $charge_row['charges'];
			
			/*$adj_query="select SUM(adj_amount) as adjustment,SUM(w_o) as wo from ar_activity where encounter IN(".$encounter_ids.")";
			$adj_res = sqlStatement($adj_query);
			$adj_row = sqlFetchArray($adj_res);
			$adjustment = $adj_row['adjustment'];
			$wo = $adj_row['wo'];*/
			
			//$balance = sprintf("%.2f",$row['charges']-($row['Adjustment']+$row['PriPaid'] + $row['SecPaid'] + $row['TerPaid'] + $row['PtPaid'] + $row['w_o']));
			 $balance = sprintf("%.2f",$charges-($row['adjustment']+$row['payments'] + $row['wo']));
			//$balance = sprintf("%.2f",$row['charges']-($adjustment+$row['payments'] + $wo));
			//echo $charges."==".$row['adjustment']."===".$row['payments']."====".$row['wo'];
			
		
			
			$patient_statement_count = $row['statement_count'];
			//$svcdate = substr($row['date'], 0, 10);
			$last_stmt_date = empty($row['last_stmt_date']) ? '-' : date('m/d/Y', strtotime($row['last_stmt_date']));
			
			
		  
			$isduept = " checked";
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
			$insu_query = "SELECT provider,ic.name,ic.statement_limit FROM insurance_data as isd
							INNER join insurance_companies as ic 
							ON isd.provider=ic.id
							where  isd.pid=$inspid and isd.type='primary' order by isd.date desc LIMIT 1";
			$insu_res = sqlStatement($insu_query);
			$insu_row = sqlFetchArray($insu_res);
			$insu_name = $insu_row['name'];
			$payer_limit = $insu_row['statement_limit'];
			
			// query for getting patient last payment date
			$patpayres = sqlStatement("select date(deposit_date) as post_date,SUM(pay_total) as paid from ar_session where payer_id=0 and patient_id=$inspid  and 						
			pay_total!='0.00' group by post_date 
			order by post_date desc limit 1		");
		$patpayrow = sqlFetchArray($patpayres);
		
		
		
			$last_payment_date = $patpayrow['post_date'];
		
			if(empty($last_payment_date)){
				$last_patient_payment_date ="";
			}
			else{	
				$time = strtotime($last_payment_date);
				$last_patient_payment_date = date('m/d/Y',$time);
			}
		
		
			
			$patinet_sub_array['pid'] =$row['pid'];
			$patinet_sub_array['patient_name'] =$row['patient_name'];
			$patinet_sub_array['external_id'] =$row['pubpid'];
			$patinet_sub_array['insu_name'] =$insu_name;
			$patinet_sub_array['payer_limit'] =$payer_limit;
			$patinet_sub_array['statement_count'] =$patient_statement_count;
			$patinet_sub_array['last_statement_date'] =$last_stmt_date;
			$patinet_sub_array['last_payment_date'] =$last_patient_payment_date;
			$patinet_sub_array['patient_balance'] =$balance;
			//$patinet_sub_array['patient_balance'] =$balance;
			$patinet_sub_array['stmt_age'] =$stmt_age;
			$patinet_sub_array['patient_alert'] = $row['patient_alert'];
			$patinet_sub_array['collection_alert'] =$row['col_alert'];
			$patinet_sub_array['general_reason'] =$row['general_reason'];
			$patinet_sub_array['encounter_ids'] =$fe_ids;
			$patinet_sub_array['stop_stmt']= $row['stop_stmt'];
			
			$patinet_array[] =$patinet_sub_array;
		
		}// end of not empty encounter no
	}// end of while  

	
	$array_count=count($patinet_array);

	// this loop is used to filter the patient array
	for($i=0;$i < $array_count;$i++){
		//print_r($patinet_array[$i]);
		//echo "<br>";
		$payer_stmt_limit =$patinet_array[$i]['payer_limit'];
		$pat_prev_stmt_count =$patinet_array[$i]['statement_count'];
		$pat_balance = $patinet_array[$i]['patient_balance'];
		$stop_statement = $patinet_array[$i]['stop_stmt'];
		
		$self_pay = $stop_statement;
		$self_payArray = explode("|",$self_pay);
		$self_pay = $self_payArray[0];
	
		// checking if patient statement count is greate than or equal to payer statement limit and 
		//patient balance greater tahn configurable balance in statement configuration page.
		if($self_pay !="self_pay"){
			if($pat_prev_stmt_count >= $payer_stmt_limit || $pat_balance < $min_balance){
				unset($patinet_array[$i]);
			}
		}
		else{
		
			if($pat_balance < $min_balance){
				unset($patinet_array[$i]);
			}
		
		}
		
		// checking if self pay or stop statement selected in demographics page
		/*if(!empty($stop_statement)){
			unset($patinet_array[$i]);
		}*/
	}
	//echo "<pre>";
	//print_r($patinet_array);
	
	$filtered_array=array_values($patinet_array);
	$patient_count=count($filtered_array);
	
	
	
	if($_POST['export_all']){
	
		for($i=0;$i < $patient_count ;$i++){
		
			if(empty($filtered_array[$i]))
				break;
				
			$pid = $filtered_array[$i]['pid'];
			$patient_name = $filtered_array[$i]['patient_name'];
			$external_id = $filtered_array[$i]['external_id'];
			$insu_name = $filtered_array[$i]['insu_name'];
			$payer_limit = $filtered_array[$i]['payer_limit'];
			$statement_count = $filtered_array[$i]['statement_count'];
			$last_statement_date = $filtered_array[$i]['last_statement_date'];
			$last_payment_date = $filtered_array[$i]['last_payment_date'];
			$patient_balance = $filtered_array[$i]['patient_balance'];
			$stmt_age = $filtered_array[$i]['stmt_age'];
			$patient_alert = $filtered_array[$i]['patient_alert'];
			$collection_alert = $filtered_array[$i]['collection_alert'];
			$general_reason = $filtered_array[$i]['general_reason'];
			$encounter_ids = $filtered_array[$i]['encounter_ids'];
			
			$bgcolor = ((++$orow & 1) ? "#ffdddd" : "#ddddff");
				
			
		
				echo ''  . addslashes($external_id) . ',';
				echo '"' . addslashes($patient_name) . '",';
				echo '"' . addslashes($last_statement_date) . '",';
				echo '"' . addslashes($last_payment_date) . '",';
				echo '"' . addslashes($patient_balance) . '",';
				echo '"' . addslashes($patient_alert) . '",';
				echo '"' . addslashes($collection_alert) . '",';
				echo '"' . addslashes($general_reason) . '",'. "\n";
		
		}
			
	} // end of if($_POST['export_all'])
	?>

	
	
	
	


	<?php 
	if (! $_POST['form_csvexport'] && ! $_POST['export_all']) 
	{ ?>
    <table  border='0' cellpadding='1' cellspacing='2' width='98%'>
        <tr bgcolor="#ddddff"><td colspan="12">Total Patients : <?php echo $patient_count;?></td></tr>
    </table>
    
    <table class="sortable" border='0' cellpadding='1' cellspacing='2' width='98%'>
        
        <tr bgcolor="#dddddd">
        
            <td class="dehead" ><?php xl('External ID','e'); ?>&nbsp;</td>
            <td class="dehead">&nbsp;<?php xl('Patient','e'); ?></td>
         	
            <td class="dehead" align="right">&nbsp;<?php xl($INTEGRATED_AR ? 'Last Statement' : 'Due Date','e'); ?></td>
            <td class="dehead" align="right"><?php xl('Statement since pmt','e'); ?>&nbsp;</td>
      
            <td class="dehead" align="right"><?php xl('Patient Balance','e'); ?>&nbsp;</td>
           
            <td class="dehead" align="left"><?php xl('Patient Alert','e'); ?>&nbsp;</td>
            <td class="dehead" align="left"><?php xl('Collection Alert','e'); ?>&nbsp;</td>
            <td class="dehead" align="left"><?php xl('General Reason','e'); ?>&nbsp;</td>
            <!--<td class="dehead">&nbsp;<?php xl('Insurance Name','e'); ?></td>-->
            <td class="dehead">&nbsp;<?php xl('Payer Limit','e'); ?></td>
             <td class="dehead" align="right"><?php xl('Prv','e'); ?></td>
             <td class="dehead" align="right"><?php xl('St. Aging','e'); ?>&nbsp;</td>
            <td class="dehead" align="left"><?php xl('Sel','e'); ?></td>
            
        </tr>
	<?php 
	} ?>
	<?php 

	
	$rec_count = $patient_count;
	
	// code for pagination
	/*if(isset($_POST['pageno']) && isset($_POST['form_csvexport'])){
		$page = ! empty( $_POST['pageno'] ) ? (int) $_POST['pageno'] : 1;
	}
	else{
		$page = ! empty( $_GET['page'] ) ? (int) $_GET['page'] : 1;
	}
	$total = count( $filtered_array ); //total items in array    
	$limit = $page_limit; //per page    
	$totalPages = ceil( $total/ $limit ); //calculate total pages
	$page = max($page, 1); //get 1 page when $_GET['page'] <= 0
	$page = min($page, $totalPages); //get last page when $_GET['page'] > $totalPages
	$offset = ($page - 1) * $limit;
	if( $offset < 0 ) $offset = 0;
	
	$limit2 = $offset + $limit;
		
	//echo $offset."---".$limit2."----".$total;
	$filtered_array = array_slice( $filtered_array, $offset, $limit,true );
	//print_r($filtered_array1);
		
	$orow = -1;
	
	//echo $page."-----".$offset."-------".$limit2;*/
	

	//echo "<pre>";
	//print_r($filtered_array);
	// printing the patients
	for($i=0;$i < $rec_count ;$i++){
		
		if(empty($filtered_array[$i]))
			break;
			
		$pid = $filtered_array[$i]['pid'];
		$patient_name = $filtered_array[$i]['patient_name'];
		$external_id = $filtered_array[$i]['external_id'];
		$insu_name = $filtered_array[$i]['insu_name'];
		$payer_limit = $filtered_array[$i]['payer_limit'];
		$statement_count = $filtered_array[$i]['statement_count'];
		$last_statement_date = $filtered_array[$i]['last_statement_date'];
		$last_payment_date = $filtered_array[$i]['last_payment_date'];
		$patient_balance = $filtered_array[$i]['patient_balance'];
		$stmt_age = $filtered_array[$i]['stmt_age'];
		$patient_alert = $filtered_array[$i]['patient_alert'];
		$collection_alert = $filtered_array[$i]['collection_alert'];
		$general_reason = $filtered_array[$i]['general_reason'];
		$encounter_ids = $filtered_array[$i]['encounter_ids'];
		
			$bgcolor = ((++$orow & 1) ? "#ffdddd" : "#ddddff");
			
		if ($_POST['form_csvexport']) { 
	
			echo ''  . addslashes($external_id) . ',';
			echo '"' . addslashes($patient_name) . '",';
			echo '"' . addslashes($last_statement_date) . '",';
			echo '"' . addslashes($last_payment_date) . '",';
			echo '"' . addslashes($patient_balance) . '",';
			echo '"' . addslashes($patient_alert) . '",';
			echo '"' . addslashes($collection_alert) . '",';
			echo '"' . addslashes($general_reason) . '",'. "\n";
	
	
		}
		else{
			if (!$_POST['export_all']) { /*
		?>
			 
     
                    <tr bgcolor='<?php echo $bgcolor ?>'>
                        <td class="detail" align="right">&nbsp;<?php echo $external_id; ?></td>
                        <td class="detail" align="left">&nbsp;<?php echo $patient_name; ?></td>
                       
                        <td class="detail" align="right">&nbsp;<?php echo $last_statement_date; ?></td>
                        <td class="detail" align="right">&nbsp;<?php echo $last_payment_date; ?></td>
                        <td class="detail" align="right"><?php bucks($patient_balance); ?>&nbsp;</td>
                 		
                        <td class="detail" align="left"><?php echo $patient_alert;?></td>
                        <td class="detail" align="left"><?php echo $collection_alert;?></td>
                        <!--<td class="detail" align="left">&nbsp;<?php echo $insu_name; ?></td>-->
                        <td class="detail" align="right">&nbsp;<?php echo $payer_limit; ?></td>
                        <td class="detail" align="right"><?php echo $statement_count ? $statement_count : "0" ?></td>
                        <td class="detail" align="right"><?php echo $stmt_age; ?>&nbsp;</td>
                        <td class="detail" align="left"><input type='checkbox' name='form_cb[<?php echo $encounter_ids ?>]'<?php echo $isduept ?> />
                        <input type="hidden" name="pageno" value="<?php echo $page ?>">
                        </td>
                    </tr>
 				
 	<?php
	*/
			}
		}// end of exopt else
 	}// end of for loop
	if (! $_POST['form_csvexport'] && ! $_POST['export_all'] ) { 
		// code for pagination
		/*$link = 'search_batch_patient.php?&form_search=form_search&page=%d';
		$pagerContainer = '<div style="width: 300px;">';   
		if( $totalPages != 0 ) 
		{
		  if( $page == 1 ) 
		  { 
			$pagerContainer .= ''; 
		  } 
		  else 
		  { 
			$pagerContainer .= sprintf( '<a href="' . $link . '" style="color: #c00"> &#171; prev page</a>', $page - 1 ); 
		  }
		  $pagerContainer .= ' <span> page <strong>' . $page . '</strong> from ' . $totalPages . '</span>'; 
		  if( $page == $totalPages ) 
		  { 
			$pagerContainer .= ''; 
		  }
		  else 
		  { 
			$pagerContainer .= sprintf( '<a href="' . $link . '" style="color: #c00"> next page &#187; </a>', $page + 1 ); 
		  }           
		}                   
		$pagerContainer .= '</div>';
		
		echo $pagerContainer; ?>*/
		
		


      include 'pagination.class.php';
      $_GET['form_search']='form_search';
        

     // print_r($my_array);
        // If we have an array with items
        if (count($filtered_array)) {
          // Create the pagination object
          $pagination = new pagination($filtered_array, (isset($_GET['page']) ? $_GET['page'] : 1), 30);
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

            	$pid = $productArray['pid'];
				$patient_name = $patientArray['patient_name'];
				$external_id = $patientArray['external_id'];
				$insu_name = $patientArray['insu_name'];
				$payer_limit = $patientArray['payer_limit'];
				$statement_count = $patientArray['statement_count'];
				$last_statement_date = $patientArray['last_statement_date'];
				$last_payment_date = $patientArray['last_payment_date'];
				$patient_balance = $patientArray['patient_balance'];
				$stmt_age = $patientArray['stmt_age'];
				$patient_alert = $patientArray['patient_alert'];
				$collection_alert = $patientArray['collection_alert'];
				$general_reason = $patientArray['general_reason'];
				$encounter_ids = $patientArray['encounter_ids'];
				
				?>
				<tr bgcolor='<?php echo $bgcolor ?>'>
				
                        <td class="detail" align="right">&nbsp;<?php echo $external_id; ?></td>
                        <td class="detail" align="left">&nbsp;<?php echo $patient_name; ?></td>
                       
                        <td class="detail" align="right">&nbsp;<?php echo $last_statement_date; ?></td>
                        <td class="detail" align="right">&nbsp;<?php echo $last_payment_date; ?></td>
                        <td class="detail" align="right"><?php bucks($patient_balance); ?>&nbsp;</td>
                 		
                        <td class="detail" align="left"><?php echo $patient_alert;?></td>
                        <td class="detail" align="left"><?php echo $collection_alert;?></td>
                        <td class="detail" align="left"><?php echo $general_reason;?></td>
                        <!--<td class="detail" align="left">&nbsp;<?php echo $insu_name; ?></td>-->
                        <td class="detail" align="right">&nbsp;<?php echo $payer_limit; ?></td>
                        <td class="detail" align="right"><?php echo $statement_count ? $statement_count : "0" ?></td>
                        <td class="detail" align="right"><?php echo $stmt_age; ?>&nbsp;</td>
                        <td class="detail" align="left"><input type='checkbox' name='form_cb[<?php echo $encounter_ids ?>]'<?php echo $isduept ?> />
                        <input type="hidden" name="pageno" value="<?php echo $page ?>">
                        </td>
                    </tr>
				
				
				<?php
            }
            // print out the page numbers beneath the results
            //echo $pageNumbers;
          }
        }
/********************************************************************/
        

        
	
	}
}// end of main if


 	?>
    <?php if (! $_POST['form_csvexport'] && ! $_POST['export_all'] ) { ?>
	</table>

	<p>

    <!--<input type='button' value='<?php xl('Select All','e')?>' onclick='checkAll(true)' /> &nbsp;-->
    <input type='button' value='<?php xl('Clear All','e')?>' onclick='checkAll(false)' /> &nbsp;
    
    <input type='submit' name='form_pdf' value='<?php xl('Send patient statements ','e'); ?>' disabled /> &nbsp;
    
    <input type='submit' name='form_csvexport' value='<?php xl('Save To File','e'); ?>'  /> &nbsp;
     <input type='submit' name='export_all' value='<?php xl('Export All','e'); ?>'  /> &nbsp;
    <input type='submit' name='form_pdf' value='<?php xl('Print Patient Statements','e'); ?>'  id="download_pdf"  /> &nbsp;
   <!-- <input type='button' value='<?php xl('Cancel','e')?>' onclick='checkAll(true)' /> &nbsp;
    <input type='button' value='<?php xl('Clear All','e')?>' onclick='checkAll(false)' />-->
    <input type='checkbox' name='form_with' value='1' /> <?php xl('With Update','e'); ?>
    </p>
    
    </form>
</center>

</body>
</html>
<?php
} // End not csv export
?>
