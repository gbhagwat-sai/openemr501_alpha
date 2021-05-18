<?php
//While creating new encounter this code is used to change the "Billing Facility:".
//This happens on change of the "Facility:" field.

// +-----------------------------------------------------------------------------+
// Copyright (C) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
//
// Author: Eldho Chacko <eldho@zhservices.com>
// Jacob T.Paul <jacob@zhservices.com>
//
// +------------------------------------------------------------------------------+



//
require_once("../../interface/globals.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
// Sai custom code start
if($_REQUEST['patientID'])
{
$patientID = $_REQUEST['patientID'];
$InsPatDropDownValue = $_REQUEST['InsPatDropDownValue'];
$q=sqlStatement("select ins_code from insurance_companies where id = (
select provider from insurance_data where type=? and pid=? and 
date <  NOW() order by date desc limit 1) ", array($InsPatDropDownValue,$patientID));
$row=sqlFetchArray($q);
echo $row['ins_code'];
}

if($_REQUEST['pid']){
$pid=$_REQUEST['pid'];
$facility=$_REQUEST['facility'];
$date=$_REQUEST['date'];
$q=sqlStatement("SELECT pc_billing_location FROM openemr_postcalendar_events WHERE pc_pid=? AND pc_eventDate=? AND pc_facility=?", array($pid,$date,$facility));
$row=sqlFetchArray($q);
billing_facility('billing_facility', $row['pc_billing_location']);
}
if($_REQUEST['code']){
//global $Code_arr;
$code=$_REQUEST['code'];

//$icd9_code=$_REQUEST['icd9_code'];
$bill_lino=$_REQUEST['bill_lino'];

$q1=sqlStatement("select id,code, units, code_text_short,modifier,ct_key from codes ,code_types
where ct_id=code_type and (ct_key='ICD9' or ct_key='CPT4') and code = ?", array($code) );
$row1=sqlFetchArray($q1);
if($row1){
$newtype=$row1['ct_key'];
$modifier = $row1['modifier'];
echoLine11($bill_lino, $newtype, $code, $modifier);
}
else
{
InvalidCode();
}
}

if($_REQUEST['newrow']){
//global $Code_arr;
$code_type = "CPT4";
$newrow=$_REQUEST['newrow'];
$pid = $_REQUEST['patid'];

$cnt = $newrow;

echocptline($cnt,$billid,$cpt_code,$pid);
}


if($_REQUEST['cpt_code']){
//global $Code_arr;
$code=$_REQUEST['cpt_code'];
$pat_id=$_REQUEST['pat_id'];
$lineno=$_REQUEST['lineno'];
$cd_type=$_REQUEST['cd_type'];
$modr = $_REQUEST['modr'];
$selctedFeeSchedule = $_REQUEST['selctedFeeSchedule'];

if ($cd_type=='ICD9')
$code_type=2;

if ($cd_type=='CPT4')
$code_type=1;

if ($cd_type=='ICD10')
$code_type=102;

/*if($lineno>=1 && $lineno<=4)
$code_type=2;
if($lineno>=5 && $lineno<=10)
$code_type=1;*/

// modified by sonali for bug 9551
// modified by Gangeya for bug 10670
//if($modr!="0")
//$qry = sqlStatement("select code from codes where code = ? and code_type=? and modifier = ?  ",array($code,$code_type,$modr));
//else

$qry = sqlStatement("select code from codes where code = ? and code_type=? and active = '1' ",array($code,$code_type));

$row1=sqlFetchArray($qry);

if(!$row1)
{

if ($cd_type=='ICD9')
echo 'Please enter correct ICD9 code'.':'.$lineno;
 
if ($cd_type=='CPT4')
echo 'Please enter correct CPT4 code'.':'.$lineno;
//echo 'Please enter correct CPT4 code & modifier'.':'.$lineno;

if ($cd_type=='ICD10')
echo 'Please enter correct ICD10 code'.':'.$lineno;

}


else if($lineno>104){
$qry = sqlStatement("select tos_list,tos from tos_list where ? BETWEEN cpt_from AND cpt_to",array($code));
$row1=sqlFetchArray($qry);
$tos_list = $row1['tos_list'];
$tos = $row1['tos'];
$string='';

// modified by Gangeya for bug 10670

if($modr!=""){
	$query = "select feeScheduleID, code, modifier, fee, EAA from feeMaster where code = '".$code."' and modifier = '".$modr."' and feeScheduleID = ".$selctedFeeSchedule;
}
else{
	$query = "select feeScheduleID, code, modifier, fee, EAA from feeMaster where code = '".$code."' and feeScheduleID = ".$selctedFeeSchedule;
}
	$units = 1;	
      
	$prrow = sqlQuery($query);
	$fee = empty($prrow) ? 0 : $prrow['fee'];
	$cpt_eaa=$prrow['EAA'];

	if (empty($units)) $units = max(1, intval($row1['units']));
		echo $string=$lineno.":".$units.":".$fee.":".$tos.":".$cpt_eaa;
}
}
if($_REQUEST['chk_num'])
{
$chk_num = $_REQUEST['chk_num'];
$session_id = $_REQUEST['session_id'];
$q=sqlStatement("select sum(pay_total) as total_pay from ar_session where session_id=? ", array($session_id));
$row=sqlFetchArray($q);
$amt_paid =  $row['total_pay'];

$q=sqlStatement("select sum(pay_amount) as pay_amount from ar_activity where session_id=? ", array($session_id));
$row=sqlFetchArray($q);
$pay_amount =  $row['pay_amount'];

$balance = $amt_paid - $pay_amount;
echo $balance;
}
if($_REQUEST['payment_type'])
{
$pid=$_REQUEST['pt_id'];
$payment_type= $_REQUEST['payment_type'];
$chk_num="";
$qry_check_number = "select session_id,reference,check_date,pay_total from ar_session where patient_id=? and pay_total>0 and payment_type='patient' 
and payment_method=? ";
 $chk_num_res = sqlStatement($qry_check_number,array($pid,$payment_type));
echo " <option value=0>-Select -</option>";
  while ($chk_num_row = sqlFetchArray($chk_num_res)) 
   {
   
    /*if($chk_num_row['reference'] == $reference_value)
	 $selected = "selected";
	 else
	 $selected =''; */
	 if($payment_type=="check_payment"){
	 $chk_num = htmlspecialchars($chk_num_row['session_id'], ENT_QUOTES)."@".htmlspecialchars($chk_num_row['reference'], ENT_QUOTES);
	 echo "<option value='".$chk_num."' $selected>".htmlspecialchars(xl_list_label($chk_num_row['reference']."[".$chk_num_row['check_date']."]"), ENT_QUOTES)."</option>";  
	 }
	 else{
	  $chk_num = htmlspecialchars($chk_num_row['session_id'], ENT_QUOTES)."@".htmlspecialchars($chk_num_row['pay_total'], ENT_QUOTES);
	  echo "<option value='".$chk_num."' $selected>".htmlspecialchars(xl_list_label($chk_num_row['pay_total']."[".$chk_num_row['check_date']."]"), ENT_QUOTES)."</option>";  
	  }
	  
   
   }
  
}
// Sai custom code start
