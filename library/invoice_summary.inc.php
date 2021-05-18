<?php
// Copyright (C) 2005-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This returns an associative array keyed on procedure code, representing
// all charge items for one invoice.  This array's values are themselves
// associative arrays having the following keys:
//
//  chg - the sum of line items, including adjustments, for the code
//  bal - the unpaid balance
//  adj - the (positive) sum of inverted adjustments
//  ins - the id of the insurance company that was billed (obsolete)
//  dtl - associative array of details, if requested
//
// Where details are requested, each dtl array is keyed on a string
// beginning with a date in yyyy-mm-dd format, or blanks in the case
// of the original charge items.  The value array is:
//
//  pmt - payment amount as a positive number, only for payments
//  src - check number or other source, only for payments
//  chg - invoice line item amount amount, only for charges or
//        adjustments (adjustments may be zero)
//  rsn - adjustment reason, only for adjustments
//  plv - provided for "integrated A/R" only: 0=pt, 1=Ins1, etc.
//  dsc - for tax charges, a description of the tax
//  arseq - ar_activity.sequence_no when it applies.

require_once("sl_eob.inc.php");
require_once(dirname(__FILE__) . "/../custom/code_types.inc.php");


// for Integrated A/R.
//
function ar_get_invoice_summary($patient_id, $encounter_id, $with_detail = false)
{
	$codes = array();
	$keysuff1 = 1000;
	$keysuff2 = 5000;
	
	// Get charges from services.
	// Sai custom code start
	//below query modified by sangram to get correct fee.. multiplied by units
	$res = sqlStatement("SELECT " .
	"billing.date, billing.code_type, billing.code, billing.modifier, billing.code_text, (billing.fee*billing.units) as fee, form_encounter.billing_note " .
	" FROM billing LEFT JOIN form_encounter on billing.pid=form_encounter.pid and billing.encounter=form_encounter.encounter WHERE " .
	"billing.pid = ? AND billing.encounter = ? AND " .
	"activity = 1 AND fee != 0.00 ORDER BY billing.id", array($patient_id,$encounter_id) );
	// Sai custom code end	
	
	while ($row = sqlFetchArray($res)) {
		$amount = sprintf('%01.2f', $row['fee']);
		$billing_note = $row['billing_note'];// Sai custom code
		$code = strtoupper($row['code']);   // Sai custom code 
		if (! $code) {
			$code = "Unknown";
		}
		
		if ($row['modifier']) {
			$code .= ':' . strtoupper($row['modifier']);   // Sai custom code start
		}
		
		$codes[$code]['chg'] += $amount;
		$codes[$code]['bal'] += $amount;
		
		// Pass the code type, code and code_text fields
		// Although not all used yet, useful information
		// to improve the statement reporting etc.
		$codes[$code]['code_type'] = $row['code_type'];
		$codes[$code]['code_value'] = $row['code'];
		$codes[$code]['modifier'] = $row['modifier'];
		$codes[$code]['code_text'] = $row['code_text'];
		
		// Add the details if they want 'em.
		if ($with_detail) {
			if (! $codes[$code]['dtl']) {
				$codes[$code]['dtl'] = array();
			}
			
			$tmp = array();
			$tmp['chg'] = $amount;
			$tmpkey = "          " . $keysuff1++;
			$codes[$code]['dtl'][$tmpkey] = $tmp;
		}
		$codes[$code]['note'] = $billing_note;   // Sai custom code 
	}
	
	// Get charges from product sales.
	$query = "SELECT s.drug_id, s.sale_date, s.fee, s.quantity " .
	"FROM drug_sales AS s " .
	"WHERE " .
	"s.pid = ? AND s.encounter = ? AND s.fee != 0 " .
	"ORDER BY s.sale_id";
	$res = sqlStatement($query, array($patient_id,$encounter_id));
	while ($row = sqlFetchArray($res)) {
		$amount = sprintf('%01.2f', $row['fee']);
		$code = 'PROD:' . $row['drug_id'];
		$codes[$code]['chg'] += $amount;
		$codes[$code]['bal'] += $amount;
		// Add the details if they want 'em.
		if ($with_detail) {
			if (! $codes[$code]['dtl']) {
				$codes[$code]['dtl'] = array();
			}
			
			$tmp = array();
			$tmp['chg'] = $amount;
			$tmpkey = "          " . $keysuff1++;
			$codes[$code]['dtl'][$tmpkey] = $tmp;
		}
	}
	
	// Get payments and adjustments. (includes copays)
	$res = sqlStatement("SELECT " .
	"a.code_type, a.code, a.modifier, a.memo, a.payer_type, a.adj_amount,a.w_o, a.pay_amount, a.reason_code, " .
	"a.post_time, a.session_id, a.sequence_no, a.account_code, " .
	"s.payer_id, s.reference, s.check_date, s.deposit_date " .
	",i.name " .
	"FROM ar_activity AS a " .
	"LEFT OUTER JOIN ar_session AS s ON s.session_id = a.session_id " .
	"LEFT OUTER JOIN insurance_companies AS i ON i.id = s.payer_id " .
	"WHERE a.pid = ? AND a.encounter = ? AND a.code!='claim'" . 
	"ORDER BY s.check_date, a.sequence_no", array($patient_id,$encounter_id));
	while ($row = sqlFetchArray($res)) {
		// sai custom code start
		//below by sangram for Bug 8559	
		if($row['payer_type']==2 && $row['pay_amount']!=0)
		{
			$checkres = sqlStatement("SELECT EXISTS(SELECT * FROM ar_activity WHERE encounter=$encounter_id and code='claim') as claim_exist");
			$checkrow = sqlFetchArray($checkres);
			if($checkrow['claim_exist'])
			{
				$claim_memo=$row['memo'];
				$claim_payment_res = sqlStatement("select pay_amount from ar_activity where code='claim' and pay_amount<0 and encounter=$encounter_id and payer_type=2 and memo='$claim_memo' ");
				$claim_payment_row = sqlFetchArray($claim_payment_res);
				$row['pay_amount']+=$claim_payment_row['pay_amount'];
			}
		}
		
		// sai custom code start	
		$code = $row['code'];
		if (! $code) $code = "Unknown";
		if ($row['modifier']) $code .= ':' . $row['modifier'];
		$ins_id = 0 + $row['payer_id'];
		$codes[$code]['bal'] -= $row['pay_amount'];
		$codes[$code]['bal'] -= $row['adj_amount'];
		$codes[$code]['chg'] -= $row['adj_amount'];
		$codes[$code]['adj'] += $row['adj_amount'];
		// sai custom code start	
		$only_code = substr($code, 0, strpos($code, ':'));
		
		//BUG ID 10287 and 10255
		//Upadted query to get proper writeoff amount in Balance calculation. 
		$modifier = $row['modifier'];
		
		
		if(strlen($only_code)==0)
		{
			$woa = sqlStatement("select sum(w_o) as wrt_off from ar_activity where encounter='$encounter_id' and (pay_amount!='0.00' or reason_code='wo'  or account_code = 'Takeback') and  code='$code' and modifier='$modifier' ");
		}
		else
		{
			$woa = sqlStatement("select sum(w_o) as wrt_off from ar_activity where encounter='$encounter_id' and (pay_amount!='0.00' or reason_code='wo' or account_code = 'Takeback') and  code='$only_code' and modifier='$modifier' ");
		}
		
		
		
		
		while ($woar = sqlFetchArray($woa)) {
			$codes[$code]['w_o']=$woar['wrt_off'];
		}
		
		//below added by Sangram for  Bug 9117 
		$cia = sqlStatement("select co_ins as co_ins  from ar_activity where encounter='$encounter_id' and  code='$code' and co_ins>0  order by post_time desc limit 1");
		
		while ($ciar = sqlFetchArray($cia)) {
			$codes[$code]['co_ins']=$ciar['co_ins'];
		}
		
		//below added by Sangram for  Bug 9117 
		$ia = sqlStatement("select sum(interest) as interest  from ar_activity where encounter='$encounter_id' and  code='$code' and interest>0 and pay_amount>0 ");
		
		while ($iar = sqlFetchArray($ia)) {
			$codes[$code]['interest']=$iar['interest'];
		}
		// sai custom code end
		if ($ins_id) $codes[$code]['ins'] = $ins_id;
		// Add the details if they want 'em.
		if ($with_detail) {
			if (! $codes[$code]['dtl']) $codes[$code]['dtl'] = array();
			$tmp = array();
			$paydate = empty($row['deposit_date']) ? substr($row['post_time'], 0, 10) : $row['deposit_date'];
			
			//Code changes by Mahesh Kunta
			// sai custom code start
			if ($row['pay_amount'] != 0) 
			{
				$tmp['pmt'] = $row['pay_amount'];
				//  $tmp['wr_off']= $row['w_o'];
			}
			//if ($row['w_o'] != 0) 
			//  {
			//  $tmp['wr_off']= $row['w_o'];
			//  }
			$tmp['wr_off']= $row['w_o'];
			// sai custom code end
			// Code changes end
			if (isset($row['reason_code'])) {
				$tmp['msp'] = $row['reason_code'];
			}
			
			if ($row['adj_amount'] != 0 || $row['pay_amount'] >= 0) { // sai custom code 
				$tmp['chg'] = 0 - $row['adj_amount'];
				// $tmp['rsn'] = (empty($row['memo']) || empty($row['session_id'])) ? 'Unknown adjustment' : $row['memo'];
				$tmp['rsn'] = empty($row['memo']) ? 'Unknown adjustment' : $row['memo'];
				$tmpkey = $paydate . $keysuff1++;
				} else {
				$tmpkey = $paydate . $keysuff2++;
			}
			
			if ($row['account_code'] == "PCP") {
				//copay
				$tmp['src'] = 'Pt Paid';
				} else {
				$tmp['src'] = empty($row['session_id']) ? $row['memo'] : $row['reference'];
			}
			
			$tmp['insurance_company'] = substr($row['name'], 0, 10);
			if ($ins_id) { $tmp['ins'] = $ins_id;}
			$tmp['plv'] = $row['payer_type'];
			$tmp['arseq'] = $row['sequence_no'];
			$codes[$code]['dtl'][$tmpkey] = $tmp;
		}
	}
	
	return $codes;
}
// sai custom code start
function get_invoice_encounter_list_status($patient_id,$encounter_id,$status,$with_detail = false){
	
	$total_bal = 0;
	$codes = array();
	$keysuff1 = 1000;
	$keysuff2 = 5000;
	
	// Get charges from services.
	$res = sqlStatement("SELECT " .
	"billing.date, billing.code_type, billing.code, billing.modifier, billing.code_text, (billing.fee*billing.units) as fee, form_encounter.billing_note " .
	" FROM billing LEFT JOIN form_encounter on billing.pid=form_encounter.pid and billing.encounter=form_encounter.encounter WHERE " .
	"billing.pid = ? AND billing.encounter = ? AND " .
	"activity = 1 AND fee != 0.00 ORDER BY billing.id", array($patient_id,$encounter_id) );
	
	while ($row = sqlFetchArray($res)) {
		$amount = sprintf('%01.2f', $row['fee']);
		$billing_note = $row['billing_note'];
		
		
		$code = strtoupper($row['code']);
		if (! $code) $code = "Unknown";
		if ($row['modifier']) $code .= ':' . strtoupper($row['modifier']);
		$codes[$code]['chg'] += $amount;
		$codes[$code]['bal'] += $amount;
		$codes['code']['bal'] += $amount;
		// }
		// Add the details if they want 'em.
		if ($with_detail) {
			if (! $codes[$code]['dtl']) $codes[$code]['dtl'] = array();
			$tmp = array();
			
			$tmp['chg'] = $amount;
			$tmpkey = "          " . $keysuff1++;
			// }
			$codes[$code]['dtl'][$tmpkey] = $tmp;
		}
		$codes[$code]['note'] = $billing_note;
	}
	
	// Get charges from product sales.
	$query = "SELECT s.drug_id, s.sale_date, s.fee, s.quantity " .
	"FROM drug_sales AS s " .
	"WHERE " .
	"s.pid = ? AND s.encounter = ? AND s.fee != 0 " .
	"ORDER BY s.sale_id";
	$res = sqlStatement($query, array($patient_id,$encounter_id) );
	while ($row = sqlFetchArray($res)) {
		$amount = sprintf('%01.2f', $row['fee']);
		$code = 'PROD:' . $row['drug_id'];
		$codes[$code]['chg'] += $amount;
		$codes[$code]['bal'] += $amount;
		$codes['code']['bal'] += $amount;
		// Add the details if they want 'em.
		if ($with_detail) {
			if (! $codes[$code]['dtl']) $codes[$code]['dtl'] = array();
			$tmp = array();
			$tmp['chg'] = $amount;
			$tmpkey = "          " . $keysuff1++;
			$codes[$code]['dtl'][$tmpkey] = $tmp;
		}
	}
	
	
	$res = sqlStatement("SELECT " .
	"a.code, a.modifier, a.memo, a.payer_type, a.adj_amount, a.pay_amount, a.reason_code, " .
	"a.post_time, a.session_id, a.sequence_no,a.w_o, " .
	"s.payer_id, s.reference, s.check_date, s.deposit_date " .
	",i.name " .
	"FROM ar_activity AS a " .
	"LEFT OUTER JOIN ar_session AS s ON s.session_id = a.session_id " .
	"LEFT OUTER JOIN insurance_companies AS i ON i.id = s.payer_id " .
	"WHERE a.pid = ? AND a.encounter = ? AND a.code!='claim'" .
	"ORDER BY s.check_date, a.sequence_no", array($patient_id,$encounter_id) );
	
	
	//added AND a.code!=claim by sangram for Bug 8559
	
	while ($row = sqlFetchArray($res)) {
		
		//below by sangram for Bug 8559
		
		if($row['payer_type']==2 && $row['pay_amount']!=0)
		{
			$checkres = sqlStatement("SELECT EXISTS(SELECT * FROM ar_activity WHERE encounter=$encounter_id and code='claim') as claim_exist");
			$checkrow = sqlFetchArray($checkres);
			if($checkrow['claim_exist'])
			{
				$claim_memo=$row['memo'];
				$claim_payment_res = sqlStatement("select pay_amount from ar_activity where code='claim' and pay_amount<0 and encounter=$encounter_id and payer_type=2 and memo='$claim_memo' ");
				$claim_payment_row = sqlFetchArray($claim_payment_res);
				$row['pay_amount']+=$claim_payment_row['pay_amount'];
				
			}
			
		}
		
		
		
		$code = strtoupper($row['code']);
		if (! $code) $code = "Unknown";
		if ($row['modifier']) $code .= ':' . strtoupper($row['modifier']);
		$ins_id = 0 + $row['payer_id'];
		$codes[$code]['bal'] -= $row['pay_amount'];
		$codes['code']['bal'] -= $row['pay_amount'];
		$codes[$code]['bal'] -= $row['adj_amount'];
		$codes['code']['bal'] -= $row['adj_amount'];
		$codes[$code]['chg'] -= $row['adj_amount'];
		$codes[$code]['adj'] += $row['adj_amount'];
		
		$only_code = substr($code, 0, strpos($code, ':'));
		
		//BUG ID 10287 & 10255
		//Upadted query to get proper writeoff amount in Balance calculation.
		
		$modifier = $row['modifier'];
		if(strlen($only_code)==0)
		{
			$woa = sqlStatement("select sum(w_o) as wrt_off from ar_activity where encounter='$encounter_id' and (pay_amount>'0.00' or reason_code='wo' or account_code = 'Takeback') and payer_type!='0' and code='$code' and modifier='$modifier' ");
		}
		else
		{
			$woa = sqlStatement("select sum(w_o) as wrt_off from ar_activity where encounter='$encounter_id' and (pay_amount>'0.00' or reason_code='wo' or account_code = 'Takeback') and payer_type!='0' and code='$only_code' and modifier='$modifier' ");
		} 
		while ($woar = sqlFetchArray($woa)) {
			$codes[$code]['w_o']=$woar['wrt_off'];
		}   
		if ($with_detail) {
			if (! $codes[$code]['dtl']) $codes[$code]['dtl'] = array();
			$tmp = array();
			$paydate = empty($row['deposit_date']) ? substr($row['post_time'], 0, 10) : $row['deposit_date'];
			if ($row['pay_amount'] != 0) 
			{
				$tmp['pmt'] = $row['pay_amount'];
				$tmp['wr_off']= $row['w_o'];
			}
			if ($row['w_o'] != 0) 
			{
				$tmp['wr_off']= $row['w_o'];
			}
			if ( isset($row['reason_code'] ) ) {
				$tmp['msp'] = $row['reason_code'];
			}
			if ($row['adj_amount'] != 0 || $row['pay_amount'] >= 0) {
				$tmp['chg'] = 0 - $row['adj_amount'];
				// $tmp['rsn'] = (empty($row['memo']) || empty($row['session_id'])) ? 'Unknown adjustment' : $row['memo'];
				$tmp['rsn'] = empty($row['memo']) ? 'Unknown adjustment' : $row['memo'];
				$tmpkey = $paydate . $keysuff1++;
			}
			else {
				$tmpkey = $paydate . $keysuff2++;
			}
			$tmp['src'] = empty($row['session_id']) ? $row['memo'] : $row['reference'];
			$tmp['insurance_company'] = substr($row['name'], 0, 10);
			if ($ins_id) $tmp['ins'] = $ins_id;
			$tmp['plv'] = $row['payer_type'];
			$tmp['arseq'] = $row['sequence_no'];
			$codes[$code]['dtl'][$tmpkey] = $tmp;
		}
		
	}
	$total_bal =  htmlspecialchars( oeFormatMoney($codes['code']['bal'] - $codes[$code]['w_o']), ENT_NOQUOTES);
	
	if($total_bal==0 && $status=="Close")
	return $encounter_id;  
	if($total_bal>0 && $status=="Open")
	return $encounter_id; 
	if($total_bal < 0 && $status=="Excess") 
	return $encounter_id;  
	// return $encounter_id;
}
// sai custom code end

// This determines the party from whom payment is currently expected.
// Returns: -1=Nobody, 0=Patient, 1=Ins1, 2=Ins2, 3=Ins3.
// for Integrated A/R.
//
function ar_responsible_party($patient_id, $encounter_id)
{
	$row = sqlQuery("SELECT date, last_level_billed, last_level_closed " .
	"FROM form_encounter WHERE " .
	"pid = ? AND encounter = ? " .
	"ORDER BY id DESC LIMIT 1", array($patient_id,$encounter_id));
	if (empty($row)) {
		return -1;
	}
	
	$next_level = $row['last_level_closed'] + 1;
	if ($next_level <= $row['last_level_billed']) {
		return $next_level;
	}
	
	if (arGetPayerID($patient_id, substr($row['date'], 0, 10), $next_level)) {
		return $next_level;
	}
	
	// There is no unclosed insurance, so see if there is an unpaid balance.
	// Currently hoping that form_encounter.balance_due can be discarded.
	$balance = 0;
	$codes = ar_get_invoice_summary($patient_id, $encounter_id);
	foreach ($codes as $cdata) {
		$balance += $cdata['bal'];
	}
	
	if ($balance > 0) {
		return 0;
	}
	
	return -1;
}
