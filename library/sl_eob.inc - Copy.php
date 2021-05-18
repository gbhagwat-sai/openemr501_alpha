<?php
  // Copyright (C) 2005-2009 Rod Roark <rod@sunsetsystems.com>
  //
  // This program is free software; you can redistribute it and/or
  // modify it under the terms of the GNU General Public License
  // as published by the Free Software Foundation; either version 2
  // of the License, or (at your option) any later version.

  include_once("patient.inc");
  include_once("billing.inc");
// Sai custom code start  
// added for BUG 10521
$todays_date = date('Y-m-d H:i:s');
$device_type = "OE";
// Sai custom code end
  include_once("invoice_summary.inc.php");

  $chart_id_cash   = 0;
  $chart_id_ar     = 0;
  $chart_id_income = 0;
  $services_id     = 0;


  // Try to figure out our invoice number (pid.encounter) from the
  // claim ID and other stuff in the ERA.  This should be straightforward
  // except that some payers mangle the claim ID that we give them.
  //
function slInvoiceNumber(&$out)
{
    $invnumber = $out['our_claim_id'];
    $atmp = preg_split('/[ -]/', $invnumber);
    $acount = count($atmp);

    $pid = 0;
    $encounter = 0;
    if ($acount == 2) {
        $pid = $atmp[0];
        $encounter = $atmp[1];
    } else if ($acount == 3) {
        $pid = $atmp[0];
        $brow = sqlQuery("SELECT encounter FROM billing WHERE " .
        "pid = '$pid' AND encounter = '" . $atmp[1] . "' AND activity = 1");
        
        $encounter = $brow['encounter'];
    } else if ($acount == 1) {
    // Sai custom code start  
    //% is added by Sangram for Bug 8659
      $pres = sqlStatement("SELECT pid FROM patient_data WHERE " .
        "lname LIKE '%" . addslashes($out['patient_lname']) . "%' AND " .
        "fname LIKE '%" . addslashes($out['patient_fname']) . "%' " .
        "ORDER BY pid DESC");
      // Sai custom code end  
        while ($prow = sqlFetchArray($pres)) {
            if (strpos($invnumber, $prow['pid']) === 0) {
                $pid = $prow['pid'];
                $encounter = substr($invnumber, strlen($pid));
                break;
            }
        }
    }

    if ($pid && $encounter) {
        $invnumber = "$pid.$encounter";
    }

    return array($pid, $encounter, $invnumber);
}

  // This gets a posting session ID.  If the payer ID is not 0 and a matching
  // session already exists, then its ID is returned.  Otherwise a new session
  // is created.
  //
function arGetSession($payer_id, $reference, $check_date, $deposit_date = '', $pay_total = 0)
{
    if (empty($deposit_date)) {
        $deposit_date = $check_date;
    }

    if ($payer_id) {
        $row = sqlQuery("SELECT session_id FROM ar_session WHERE " .
        "payer_id = '$payer_id' AND reference = '$reference' AND " .
        "check_date = '$check_date' AND deposit_date = '$deposit_date' " .
        "ORDER BY session_id DESC LIMIT 1");
        if (!empty($row['session_id'])) {
            return $row['session_id'];
        }
    }
	// Sai custom code start  
	$lastRes = mysql_query("INSERT INTO ar_session ( " .
      "payer_id, user_id, reference, check_date, deposit_date, pay_total " .
      ") VALUES ( " .
      "'$payer_id', " .
      "'" . $_SESSION['authUserID'] . "', " .
      "'$reference', " .
      "'$check_date', " .
      "'$deposit_date', " .
      "'$pay_total' " .
      ")");	
	  
	  // code added for getting last inserted id of encounter by pawan 25-01-2017
	  $result = mysql_query("SELECT LAST_INSERT_ID() "); 
	  $row =  mysql_fetch_row($result);
	  $lastid = $row[0];
	  
	return $lastid;
	// Sai custom code end
}
  //writing the check details to Session Table on ERA proxcessing
function arPostSession($payer_id, $check_number, $check_date, $pay_total, $post_to_date, $deposit_date, $debug)
{
      $query = "INSERT INTO ar_session( " .
      "payer_id,user_id,closed,reference,check_date,pay_total,post_to_date,deposit_date,patient_id,payment_type,adjustment_code,payment_method " .
      ") VALUES ( " .
      "'$payer_id'," .
      $_SESSION['authUserID']."," .
      "0," .
      "'ePay - $check_number'," .
      "'$check_date', " .
      "$pay_total, " .
      "'$post_to_date','$deposit_date', " .
      "0,'insurance','insurance_payment','electronic'" .
        ")";
    if ($debug) {
      //echo $query . "<br>\n"; // Sai custom code   
    } else {
        $sessionId=sqlInsert($query);
 // Sai custom code start  
 //By Gangeya 
 $highest_id = mysql_result(mysql_query("select MAX(session_id) from ar_session"), 0);
  
 return $sessionId;
 // Sai custom code end  
    }
}
  
  // Post a payment, new style.
  //
// Sai custom code start  
// Added by Gangeya  
// Function to update the status automatically while ERA payment posting.
function arUpdateStatus($patient_id, $encounter_id, $era_status)
{

//echo "Patient ID ".$patient_id."<br>";
//echo "Encounter ID ".$encounter_id."<br>";
//PAYEHR-288 queryy updated to include coinsunrance amount
  $res1 = sqlStatement("select b.encounter,fe.stmt_count,DATE(fe.date) AS DOS,
		CONCAT(u.fname,' ',u.lname) as Provider,
		fe.facility AS Location,fe.claim_status_id as status,b.code AS CPT,SUM(IF(b.fee <> 0.01, b.fee*b.units, 0.00)) As Charge,
		IFNULL((select SUM(pay_amount) from ar_activity 
		where payer_type = 1 and code = b.code and encounter = b.encounter 
		group by encounter,code),0) as PriPaid,
		IFNULL((select SUM(pay_amount) from ar_activity 
		where payer_type = 2 and code = b.code and encounter = b.encounter 
		group by encounter,code),0) as SecPaid,
		IFNULL((select SUM(pay_amount) from ar_activity 
		where payer_type = 3 and code = b.code and encounter = b.encounter 
		group by encounter,code),0) as TerPaid,
		IFNULL((select SUM(adj_amount) from ar_activity 
		where code = b.code and encounter = b.encounter 
		group by encounter,code),0) as Adjustment,
		IFNULL((select FORMAT(SUBSTRING_INDEX(memo,'dedbl:',-1),2) from ar_activity 
		where payer_type = 1 and code = b.code and encounter = b.encounter  and memo like 'Ins1 dedbl%'
		group by encounter,code),0) as PriDeductible,
		IFNULL((select FORMAT(SUBSTRING_INDEX(memo,'dedbl',-1),2) from ar_activity 
		where payer_type = 2 and code = b.code and encounter = b.encounter  and memo like 'Ins2 dedbl%' 
		group by encounter,code),0) as SecDeductible,
		IFNULL((select FORMAT(SUBSTRING_INDEX(memo,'dedbl',-1),2) from ar_activity 
		where payer_type = 3 and code = b.code and encounter = b.encounter  and memo like 'Ins3 dedbl%'
		group by encounter,code),0) as TerDeductible,
		IFNULL((select FORMAT(SUBSTRING_INDEX(memo,'coins:',-1),2) from ar_activity 
		where payer_type = 1 and code = b.code and encounter = b.encounter  and memo like 'Ins1 coins%'
		group by encounter,code),0) as PriCoins,
		IFNULL((select FORMAT(SUBSTRING_INDEX(memo,'coins',-1),2) from ar_activity 
		where payer_type = 2 and code = b.code and encounter = b.encounter  and memo like 'Ins2 coins%' 
		group by encounter,code),0) as SecCoins,
		IFNULL((select FORMAT(SUBSTRING_INDEX(memo,'coins',-1),2) from ar_activity 
		where payer_type = 3 and code = b.code and encounter = b.encounter  and memo like 'Ins3 coins%'
		group by encounter,code),0) as TerCoins,
		IFNULL(( select sum(w_o) from ar_activity where
		code = b.code and encounter = b.encounter  and (pay_amount>'0.00' or reason_code='wo' ) and payer_type!='0'),0) as writeoff,
		IFNULL((select SUM(pay_amount) from ar_activity 
		where payer_type = 0 and code = b.code and encounter = b.encounter 
		group by encounter,code),0) as CoPay
		from billing b 
		inner join form_encounter fe  on fe.encounter = b.encounter
		INNER JOIN users u ON u.id = fe.provider_id
		where b.code_type = 'CPT4' and b.encounter = '$encounter_id'
		and b.pid = '$patient_id' group by b.encounter");

	$row= sqlFetchArray($res1);
	
	$encounter = $row['encounter'];
	$currentstat = $row['status'];
	$charge = $row['Charge'];
	$pri = $row['PriPaid'];
	$sec = $row['SecPaid'];
	$ter = $row['TerPaid'];
	$copay = $row['CoPay'];
	$adj = $row['Adjustment'];
	$writeoff = $row['writeoff'];
	$prideduct = $row['PriDeductible'];
	$secdeduct = $row['SecDeductible'];
	$terdeduct = $row['TerDeductible'];
//PAYEHR-288 coinsunrance amount
	$pricoins = $row['PriCoins'];
	$seccoins = $row['SecCoins'];
	$tercoins = $row['TerCoins'];

//for getting patient insurance information START
	$patintFirtInsurance = "";
	$patintSecondInsurance="";
	$patintThirdInsurance="";
 
	$res = sqlStatement("SELECT idt.id,idt.pid,idt.type,idt.date,ics.name,idt.policy_number 
	FROM insurance_data idt INNER JOIN insurance_companies ics ON ics.id = idt.provider 
	WHERE idt.pid = '$patient_id' AND idt.date = (SELECT MAX(date) FROM insurance_data WHERE type = idt.type AND pid = idt.pid)
	GROUP BY idt.type");

	while ($row = sqlFetchArray($res)) 
	{
		$patient_insurance[] = $row['name'].' - '.$row['policy_number'];
	}

	if(count($patient_insurance)>0)
	{
		$patintFirtInsurance = $patient_insurance[0];
		$patintSecondInsurance = $patient_insurance[1];
		$patintThirdInsurance = $patient_insurance[2];
	}

	$insuCount = 0;
	
	if(strlen($patintFirtInsurance) != 0)
	{
		$insuCount = $insuCount + 1;
	}
	if(strlen($patintSecondInsurance) != 0)
	{
		$insuCount = $insuCount + 1;
	}
	if(strlen($patintThirdInsurance) != 0)
	{
		$insuCount = $insuCount + 1;
	}
	
//Logic for updating the status.
	$balance = $charge - ($adj + $pri + $sec + $ter + $writeoff);
//	echo '<br>';
//	echo 'PID : ' . $patient_id . 'Ins_Count : ' . $insuCount . 'Bal : ' .$balance ."& w/o : ". $writeoff;
//	echo '<br>';

/*Status and ID
1	Unbilled Demo Pending
2	Unbilled Insurance Pending
3	Unbilled rejected
4	Ready to send primary
5	Billed to primary
6	Ready to send secondary
7	Billed to secondary
8	Ready to send tertiary
9	Billed to tertiary
10	Ready to send patient
11	Billed to patient
12	Claim settled

era_status == 19; means claim is crossed over to next payer.
*/

// code update for last modified date by pawan on 03/24/2017
// code update for last_level_billed by Gangeya on 04/30/2018
$todays_date = date('Y-m-d H:i:s');
$modified_by= $_SESSION{'authUserID'};
	
//Logic to update encounter status and last level closed
//Changed by Gangeya for PAYEHR-288
//PAYEHR-288 change start here
	if($era_status == 19)
	{		
		if($insuCount == 2 && $balance != $charge && $balance > 0.00 && $pri > 0.00) //Billed to secondary
		{
			
			mysql_query("update form_encounter set claim_status_id = '7', last_level_billed = 2, last_level_closed = 1, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			
		}
		else if($insuCount == 3 && $balance != $charge && $balance > 0.00 && $pri > 0.00) //Billed to secondary
		{
			
			mysql_query("update form_encounter set claim_status_id = '7', last_level_billed = 2, last_level_closed = 1, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
		}
		else if($insuCount == 2 && $balance != $charge && $balance > 0.00 && $pri == 0.00 && $prideduct > 0.00) //Billed to secondary
		{
			
			mysql_query("update form_encounter set claim_status_id = '7', last_level_billed = 2, last_level_closed = 1, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			
		}
		else if($insuCount == 3 && $balance != $charge && $balance > 0.00 && $pri == 0.00 && $prideduct > 0.00) //Billed to secondary
		{
			
			mysql_query("update form_encounter set claim_status_id = '7', last_level_billed = 2, last_level_closed = 1, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
		}
	}
	else
	{
		if($insuCount == 1 && ($pri != 0 || $pricoins != 0 || $writeoff != 0 || $prideduct != 0)){
			if($insuCount == 1 && $balance == 0.00) // claim settled
			{
				
				mysql_query("update form_encounter set claim_status_id = '12', last_level_billed = 1, last_level_closed = 1, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			}
			else if($insuCount == 1 && $balance < 0.00) //Excess Payment
			{

				mysql_query("update form_encounter set claim_status_id = '15', last_level_billed = 1, last_level_closed = 1, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			}
			else if($insuCount == 1 && $balance != $charge && $pri == 0.00 && $prideduct > 0.00) //Ready to send patient
			{
				
				mysql_query("update form_encounter set claim_status_id = '10', last_level_billed = 1, last_level_closed = 1, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			}
			else if($insuCount == 1 && $balance != $charge && $balance > 0.00  && $pri > 0.00)  //Ready to send patient
			{
				
				mysql_query("update form_encounter set claim_status_id = '10', last_level_billed = 1, last_level_closed = 1, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			}
		}
		else if($insuCount == 2 && ($sec != 0 || $seccoins != 0 || $secdeduct != 0 || $writeoff != 0)){
			if($insuCount == 2 && $balance == 0.00) // claim settled
			{
				
				mysql_query("update form_encounter set claim_status_id = '12', last_level_billed = 2, last_level_closed = 2, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			}
			else if($insuCount == 2 && $balance < 0.00) // Excess Payment
			{
				
				mysql_query("update form_encounter set claim_status_id = '15', last_level_billed = 2, last_level_closed = 2, modified_date = '$todays_date',modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			}
			else if($insuCount == 2 && $balance > 0.00 && $pri > 0.00 && $sec == 0.00) //Ready to send secondary
			{
				mysql_query("update form_encounter set claim_status_id = '6',modified_date='$todays_date', last_level_billed = 1, last_level_closed = 1, modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			}
			else if($insuCount == 2 && $balance != $charge && $balance > 0.00 && $pri == 0.00 && $prideduct > 0.00) //Ready to send secondary
			{
				
				mysql_query("update form_encounter set claim_status_id = '6', last_level_billed = 1, last_level_closed = 1, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			}
			else if($insuCount == 2 && $balance != $charge && $balance > 0.00 && $pri > 0.00 && $sec == 0.00 && $secdeduct > 0.00 ) //Ready to send patient
			{
				
				mysql_query("update form_encounter set claim_status_id = '10', last_level_billed = 2, last_level_closed = 2, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			}
		}
		else if($insuCount == 3 && ($ter != 0 || $tercoins != 0 || $terdeduct != 0|| $writeoff != 0)){
			if($insuCount == 3 && $balance == 0.00) // claim settled
			{
				
				mysql_query("update form_encounter set claim_status_id = '12', last_level_billed = 3, last_level_closed = 3, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			}	
			else if($insuCount == 3 && $balance != $charge && $balance < 0.00) // Excess Payment
			{
				
				mysql_query("update form_encounter set claim_status_id = '15', last_level_billed = 3, last_level_closed = 3, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			}
			else if($insuCount == 3 && $balance != $charge && $pri > 0.00 && $sec == 0.00 && $secdeduct > 0.00) //Ready to send tertiary
			{
				
				mysql_query("update form_encounter set claim_status_id = '8', last_level_billed = 2, last_level_closed = 2, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			}
			else if($insuCount == 3 && $balance != $charge && $balance > 0.00 && $pri > 0.00 && $sec > 0.00 && $ter == 0.00) //Ready to send tertiary
			{
				
				mysql_query("update form_encounter set claim_status_id = '8', last_level_billed = 2, last_level_closed = 2, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			}
			else if($insuCount == 3 && $balance != $charge && $balance > 0.00 && $pri > 0.00 && $sec > 0.00 && $ter == 0.00 && $terdeduct > 0.00) //Ready to send patient
			{
				mysql_query("update form_encounter set claim_status_id = '10', last_level_billed = 3, last_level_closed = 3, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			}
			else if($insuCount == 3 && $pri > 0.00 && $sec > 0.00 && $ter > 0.00 && $balance > 0.00) //Ready to send patient
			{
				
					mysql_query("update form_encounter set claim_status_id = '10', last_level_billed = 3, last_level_closed = 3, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
			}
			else if($insuCount == 3 && $balance != $charge && $balance > 0.00 && $pri == 0.00 && $prideduct > 0.00 && $sec == 0.00 && $secdeduct > 0.00 && $ter == 0.00 && $terdeduct > 0.00) //Ready to send patient
			{
				
				mysql_query("update form_encounter set claim_status_id = '10', last_level_billed = 3, last_level_closed = 3, modified_date = '$todays_date', modified_by = $modified_by where encounter = '$encounter' and pid = '$patient_id'");
				
			}
		}
	}
//PAYEHR-288 change end here
	
}
// Sai custom code end
function arPostPayment($patient_id, $encounter_id, $session_id, $amount, $code, $payer_type, $memo, $debug,$allowed_amt, $w_o, $co_ins, $time = '', $codetype = '')
{
    $codeonly = $code;
    $modifier = '';
    $tmp = strpos($code, ':');
    if ($tmp) {
        $codeonly = substr($code, 0, $tmp);
        $modifier = substr($code, $tmp+1);
    }

    if (empty($time)) {
        $time = date('Y-m-d H:i:s');
    }

    sqlBeginTrans();
    $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array($patient_id, $encounter_id));
    $query = "INSERT INTO ar_activity ( " .
    "pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_time, post_user, " .
      "session_id, memo, pay_amount, allowed_amt, w_o, co_ins" .
    ") VALUES ( " .
    "'$patient_id', " .
    "'$encounter_id', " .
    "'{$sequence_no['increment']}', " .
    "'$codetype', " .
    "'$codeonly', " .
    "'$modifier', " .
    "'$payer_type', " .
    "'$time', " .
    "'" . $_SESSION['authUserID'] . "', " .
    "'$session_id', " .
    "'$memo', " .
    "'$amount', " .
    "'$allowed_amt', " .
    "'0.00', " .
    "'$co_ins' " .
     ")"; // Sai custom code  

    sqlStatement($query);
    sqlCommitTrans();
    return;
}

  // Post a charge.  This is called only from sl_eob_process.php where
  // automated remittance processing can create a new service item.
  // Here we add it as an unauthorized item to the billing table.
  //
function arPostCharge($patient_id, $encounter_id, $session_id, $amount, $units, $thisdate, $code, $description, $debug, $codetype = '')
{
    /*****************************************************************
    // Select an existing billing item as a template.
    $row= sqlQuery("SELECT * FROM billing WHERE " .
    "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
    "code_type = 'CPT4' AND activity = 1 " .
    "ORDER BY id DESC LIMIT 1");
    $this_authorized = 0;
    $this_provider = 0;
    if (!empty($row)) {
    $this_authorized = $row['authorized'];
    $this_provider = $row['provider_id'];
    }
    *****************************************************************/

    if (empty($codetype)) {
      // default to CPT4 if empty, which is consistent with previous functionality.
        $codetype="CPT4";
    }

    $codeonly = $code;
    $modifier = '';
    $tmp = strpos($code, ':');
    if ($tmp) {
        $codeonly = substr($code, 0, $tmp);
        $modifier = substr($code, $tmp+1);
    }

    addBilling(
        $encounter_id,
        $codetype,
        $codeonly,
        $description,
        $patient_id,
        0,
        0,
        $modifier,
        $units,
        $amount,
        '',
        '');
    }

  // Post an adjustment, new style.
  //
function arPostAdjustment($patient_id, $encounter_id, $session_id, $amount, $code, $payer_type, $reason, $debug,$w_o, $co_ins, $reason_code='', $time = '', $codetype = '',$allowed_amt=0){
  //Added by sangram for Bug : 8779
  if(strlen($allowed_amt)==0)
  {
  $reason = $reason . ' $'.$amount;
  $amount=0;
  
  }
  // Sai custom code end  
    $codeonly = $code;
    $modifier = '';
    $tmp = strpos($code, ':');
    if ($tmp) {
        $codeonly = substr($code, 0, $tmp);
        $modifier = substr($code, $tmp+1);
    }

    if (empty($time)) {
        $time = date('Y-m-d H:i:s');
    }
    // Sai custom code start
    sqlBeginTrans();
    $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array($patient_id, $encounter_id));

    $query = "INSERT INTO ar_activity ( " .
    "pid, encounter, sequence_no, code, modifier, payer_type, post_user, post_time, " .
      "session_id, memo, adj_amount,reason_code,w_o, co_ins, allowed_amt" .
    ") VALUES ( " .
    "'$patient_id', " .
    "'$encounter_id', " .
    "'$sequence_no', " .
    "'$codeonly', " .
    "'$modifier', " .
    "'$payer_type', " .
    "'" . $_SESSION['authUserID'] . "', " .
    "'$time', " .
    "'$session_id', " .
    "'$reason', " .
    "'$amount', " .
    "'$reason_code',  " . 
    "'$w_o', " .
    "'$co_ins', " .
    "'$allowed_amt'" .
      ")"; 
      // sai custom code end 
      
    sqlStatement($query);
    sqlCommitTrans();
    return;
}

function arGetPayerID($patient_id, $date_of_service, $payer_type)
{
    if ($payer_type < 1 || $payer_type > 3) {
        return 0;
    }

    $tmp = array(1 => 'primary', 2 => 'secondary', 3 => 'tertiary');
    $value = $tmp[$payer_type];
    $query = "SELECT provider FROM insurance_data WHERE " .
    "pid = ? AND type = ? AND date <= ? " .
    "ORDER BY date DESC LIMIT 1";
    $nprow = sqlQuery($query, array($patient_id,$value,$date_of_service));
    if (empty($nprow)) {
        return 0;
    }

    return $nprow['provider'];
}

  // Make this invoice re-billable, new style.
  //
function arSetupSecondary($patient_id, $encounter_id, $debug, $crossover = 0)
{
    if ($crossover==1) {
    //if claim forwarded setting a new status
        $status=6;
    } else {
        $status=1;
    }

    // Determine the next insurance level to be billed.
    $ferow = sqlQuery("SELECT date, last_level_billed " .
    "FROM form_encounter WHERE " .
    "pid = '$patient_id' AND encounter = '$encounter_id'");
    $date_of_service = substr($ferow['date'], 0, 10);
    $new_payer_type = 0 + $ferow['last_level_billed'];
    if ($new_payer_type < 3 && !empty($ferow['last_level_billed']) || $new_payer_type == 0) {
        ++$new_payer_type;
    }

    $new_payer_id = arGetPayerID($patient_id, $date_of_service, $new_payer_type);

    if ($new_payer_id) {
        // Queue up the claim.
        if (!$debug) {
            updateClaim(true, $patient_id, $encounter_id, $new_payer_id, $new_payer_type, $status, 5, '', 'hcfa', '', $crossover);
        }
    } else {
      // Just reopen the claim.
        if (!$debug) {
            updateClaim(true, $patient_id, $encounter_id, -1, -1, $status, 0, '', '', '', $crossover);
	    }
    }
    return xl("Encounter ") . $encounter . xl(" is ready for re-billing.");
    
    }
