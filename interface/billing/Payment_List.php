<?php
//ini_set('max_execution_time', 300);
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2010 Z&H Consultancy Services Private Limited <sam@zhservices.com>
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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// 
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Paul Simon K <paul@zhservices.com> 
//
// +------------------------------------------------------------------------------+
//===============================================================================
//Payments can be edited here.It includes deletion of an allocation,modifying the 
//same or adding a new allocation.Log is kept for the deleted ones.
//===============================================================================
require_once("../globals.php");
require_once("$srcdir/log.inc");
require_once("$srcdir/invoice_summary.inc.php");
require_once("$srcdir/sl_eob.inc.php");
require_once("$srcdir/parse_era.inc.php");
require_once("../../library/acl.inc");
require_once("$srcdir/sql.inc");
require_once("$srcdir/auth.inc");
require_once("$srcdir/formdata.inc.php");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/billrep.inc");
require_once(dirname(__FILE__) . "/../../library/classes/OFX.class.php");
require_once(dirname(__FILE__) . "/../../library/classes/X12Partner.class.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/payment.inc.php");
//===============================================================================
	$screen='edit_payment';
//===============================================================================
// deletion of payment distribution code
//===============================================================================
if (isset($_POST["mode"]))
 {
  if ($_POST["mode"] == "DeletePaymentDistribution")
   {
    $DeletePaymentDistributionId=trim(formData('DeletePaymentDistributionId' ));
	$DeletePaymentDistributionIdArray=split('_',$DeletePaymentDistributionId);
	$payment_id=$DeletePaymentDistributionIdArray[0];
	$PId=$DeletePaymentDistributionIdArray[1];
	$Encounter=$DeletePaymentDistributionIdArray[2];
	$Code=$DeletePaymentDistributionIdArray[3];
	$Modifier=$DeletePaymentDistributionIdArray[4];
	//delete and log that action
	row_delete("ar_activity", "session_id ='$payment_id' and  pid ='$PId' AND " .
	  "encounter='$Encounter' and  code='$Code' and modifier='$Modifier'");
	$Message='Delete';
	//------------------
    $_POST["mode"] = "searchdatabase";
   }
 }
//===============================================================================
//Modify Payment Code.
//===============================================================================

	//below added by sangram for copay issue on 18_june_2014
if (isset($_POST["mode"]))
 {
  if ($_POST["mode"] == "ModifyPayments" || $_POST["mode"] == "FinishPayments")
   {   
  
	 $payment_id=$_REQUEST['payment_id']; 
	//ar_session Code
	//===============================================================================
	if(trim(formData('type_name'   ))=='insurance')
	 {
		$QueryPart="payer_id = '"       . trim(formData('hidden_type_code' )) .
		"', patient_id = '"   . 0 ;
	 }
	elseif(trim(formData('type_name'   ))=='patient')
	 {
		$QueryPart="payer_id = '"       . 0 .
		"', patient_id = '"   . trim(formData('hidden_type_code'   )) ;
	 }
      $user_id=$_SESSION['authUserID'];
	  $closed=0;
	  $modified_time = date('Y-m-d H:i:s');
	  $check_date=DateToYYYYMMDD(formData('check_date'));
	  $deposit_date=DateToYYYYMMDD(formData('deposit_date'));
	  $post_to_date=DateToYYYYMMDD(formData('post_to_date'));
	  if($post_to_date=='')
	   $post_to_date=date('Y-m-d');
	  if(formData('deposit_date')=='')
	   $deposit_date=$post_to_date;   
		
		

	  sqlStatement("update ar_session set "    .
	  	$QueryPart .
        "', user_id = '"     . trim($user_id                  )  .
        "', closed = '"      . trim($closed                   )  .
        "', reference = '"   . trim(formData('check_number'   )) .
        "', check_date = '"  . trim($check_date					) .
        "', deposit_date = '" . trim($deposit_date            )  .
        "', pay_total = '"    . trim(formData('payment_amount')) .
        "', modified_time = '" . trim($modified_time            )  .
        "', payment_type = '"   . trim(formData('type_name'   )) .
        "', description = '"   . trim(formData('description'   )) .
        "', adjustment_code = '"   . trim(formData('adjustment_code'   )) .
        "', post_to_date = '" . trim($post_to_date            )  .
        "', payment_method = '"   . trim(formData('payment_method'   )) .

        "'	where session_id='$payment_id'");
//===============================================================================
	$CountIndexAbove=$_REQUEST['CountIndexAbove']; 
	$CountIndexBelow=$_REQUEST['CountIndexBelow'];
	$hidden_patient_code=$_REQUEST['hidden_patient_code'];
	$user_id=$_SESSION['authUserID'];
	$created_time = date('Y-m-d H:i:s');
	//==================================================================
	//UPDATION
	//It is done with out deleting any old entries.
	//==================================================================
	//echo "here....".$CountRow."#####".$CountIndexAbove; die;

	for($CountRow=1;$CountRow<=$CountIndexAbove;$CountRow++)
	 {
	 
	  if (isset($_POST["HiddenEncounter$CountRow"]))
	   {
		  if (isset($_POST["Payment$CountRow"]) && strlen($_POST["Payment$CountRow"])>0 && $_POST["Payment$CountRow"]*1>=0)
		   {
				if(trim(formData('type_name'   ))=='insurance')
				 {
				  if(trim(formData("HiddenIns$CountRow"   ))==1)
				   {
					  $AccountCode="IPP";
				   }
				  if(trim(formData("HiddenIns$CountRow"   ))==2)
				   {
					  $AccountCode="ISP";
				   }
				  if(trim(formData("HiddenIns$CountRow"   ))==3)
				   {
					  $AccountCode="ITP";
				   }
				 }
				elseif(trim(formData('type_name'   ))=='patient')
				 {
				  $AccountCode="PP";
				 }
				$resPayment = sqlStatement("SELECT  * from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and pay_amount>0");
				if(sqlNumRows($resPayment)>0)
				 {
				  sqlStatement("update ar_activity set "    .
					"   post_user = '" . trim($user_id            )  .
					"', modified_time = '"  . trim($created_time					) .
					"', pay_amount = '" . trim(formData("Payment$CountRow"   ))  .
					"', account_code = '" . "$AccountCode"  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
				    "', reason_code = '"   . trim(formData("ReasonCode$CountRow"   )) .
					"' where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and  Adj_code = '" . trim(formData("Adj_code$CountRow"   ))  .
					"' and  w_o = '" . trim(formData("wo$CountRow" ))   .
					"' and  allowed_amt = '" . trim(formData("Allowed$CountRow"))  .
					"' and pay_amount>0");
				 }
				else
				 {
				  sqlStatement("insert into ar_activity set "    .
					"pid = '"       . trim(formData("HiddenPId$CountRow"   )) .
					"', encounter = '"     . trim(formData("HiddenEncounter$CountRow"   ))  .
					"', code = '"      . trim(formData("HiddenCode$CountRow"   ))  .
					"', modifier = '"      . trim(formData("HiddenModifier$CountRow"   ))  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
				    "', reason_code = '"   . trim(formData("ReasonCode$CountRow"   )) .
					"', post_time = '"  . trim($created_time					) .
					"', post_user = '" . trim($user_id            )  .
					"', session_id = '"    . trim(formData('payment_id')) .
					"', modified_time = '"  . trim($created_time					) .
					"', pay_amount = '" . trim(formData("Payment$CountRow"   ))  .
					"', adj_amount = '"    . 0 .
					"', account_code = '" . "$AccountCode"  .
					"', w_o = '" . trim(formData("wo$CountRow" ))   .
					"', allowed_amt = '" . trim(formData("Allowed$CountRow"))  .
					"', Adj_code = '" . trim(formData("Adj_code$CountRow"   ))  .
					"'");
				 }
		   }
		  else
		   {
		    sqlStatement("delete from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and pay_amount>0");
		   }
//==============================================================================================================================
		  if (isset($_POST["AdjAmount$CountRow"]) && $_POST["AdjAmount$CountRow"]*1!=0)
		   {
				if(trim(formData('type_name'   ))=='insurance')
				 {
				  $AdjustString="Ins adjust Ins".trim(formData("HiddenIns$CountRow"   ));
				  $AccountCode="IA";
				 }
				elseif(trim(formData('type_name'   ))=='patient')
				 {
				  $AdjustString="Pt adjust";
				  $AccountCode="PA";
				 }
				$resPayment = sqlStatement("SELECT  * from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and adj_amount!=0");
				if(sqlNumRows($resPayment)>0)
				 {
				  sqlStatement("update ar_activity set "    .
					"   post_user = '" . trim($user_id            )  .
					"', modified_time = '"  . trim($created_time					) .
					"', adj_amount = '"    . trim(formData("AdjAmount$CountRow"   )) .
					"', memo = '" . "$AdjustString"  .
					"', account_code = '" . "$AccountCode"  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"' where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and Adj_code = '" . trim(formData("Adj_code$CountRow"   ))  .
					"' and adj_amount!=0");
				 }
				else
				 {
				  sqlStatement("insert into ar_activity set "    .
					"pid = '"       . trim(formData("HiddenPId$CountRow" )) .
					"', encounter = '"     . trim(formData("HiddenEncounter$CountRow"   ))  .
					"', code = '"      . trim(formData("HiddenCode$CountRow"   ))  .
					"', modifier = '"      . trim(formData("HiddenModifier$CountRow"   ))  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"', post_time = '"  . trim($created_time					) .
					"', post_user = '" . trim($user_id            )  .
					"', session_id = '"    . trim(formData('payment_id')) .
					"', modified_time = '"  . trim($created_time					) .
					"', pay_amount = '" . 0  .
					"', adj_amount = '"    . trim(formData("AdjAmount$CountRow"   )) .
					"', memo = '" . "$AdjustString"  .
					"', account_code = '" . "$AccountCode"  .
					"', Adj_code = '" . trim(formData("Adj_code$CountRow"   ))  .
					"'");
				 }

		   }
		  else
		   {
		    sqlStatement("delete from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and adj_amount!=0");
		   }
//==============================================================================================================================
		  if (isset($_POST["Deductible$CountRow"]) && $_POST["Deductible$CountRow"]*1>0)
		   {

				$resPayment = sqlStatement("SELECT  * from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and (memo like 'Deductable%' or memo like '%dedbl%')");
				if(sqlNumRows($resPayment)>0)
				 {
				  sqlStatement("update ar_activity set "    .
					"   post_user = '" . trim($user_id            )  .
					"', modified_time = '"  . trim($created_time					) .
					"', memo = '"    . "Deductable $".trim(formData("Deductible$CountRow"   )) .
					"', account_code = '" . "Deduct"  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"' where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and  Adj_code = '" . trim(formData("Adj_code$CountRow"   ))  .
					"' and (memo like 'Deductable%' or memo like '%dedbl%')");
				 }
				else
				 {
				  sqlStatement("insert into ar_activity set "    .
					"pid = '"       . trim(formData("HiddenPId$CountRow" )) .
					"', encounter = '"     . trim(formData("HiddenEncounter$CountRow"   ))  .
					"', code = '"      . trim(formData("HiddenCode$CountRow"   ))  .
					"', modifier = '"      . trim(formData("HiddenModifier$CountRow"   ))  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"', post_time = '"  . trim($created_time					) .
					"', post_user = '" . trim($user_id            )  .
					"', session_id = '"    . trim(formData('payment_id')) .
					"', modified_time = '"  . trim($created_time					) .
					"', pay_amount = '" . 0  .
					"', adj_amount = '"    . 0 .
					"', memo = '"    . "Deductable $".trim(formData("Deductible$CountRow"   )) .
					"', Adj_code = '" . trim(formData("Adj_code$CountRow"   ))  .
					"', account_code = '" . "Deduct"  .
					"'");
				 }
		   }
		  else
		   {
		    sqlStatement("delete from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and (memo like 'Deductable%' or memo like '%dedbl%')");
		   }
//==============================================================================================================================
		  if (isset($_POST["Takeback$CountRow"]) && $_POST["Takeback$CountRow"]*1>0)
		   {
	   
				$resPayment = sqlStatement("SELECT  * from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and pay_amount < 0");
				if(sqlNumRows($resPayment)>0)
				 {
				  sqlStatement("update ar_activity set "    .
					"   post_user = '" . trim($user_id            )  .
					"', modified_time = '"  . trim($created_time					) .
					"', pay_amount = '" . trim(formData("Takeback$CountRow"   ))*-1  .
					"', account_code = '" . "Takeback"  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"' where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and  Adj_code = '" . trim(formData("Adj_code$CountRow"   ))  .
					"' and pay_amount < 0");
				 }
				else
				 {
				  sqlStatement("insert into ar_activity set "    .
					"pid = '"       . trim(formData("HiddenPId$CountRow" )) .
					"', encounter = '"     . trim(formData("HiddenEncounter$CountRow"   ))  .
					"', code = '"      . trim(formData("HiddenCode$CountRow"   ))  .
					"', modifier = '"      . trim(formData("HiddenModifier$CountRow"   ))  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"', post_time = '"  . trim($created_time					) .
					"', post_user = '" . trim($user_id            )  .
					"', session_id = '"    . trim(formData('payment_id')) .
					"', modified_time = '"  . trim($created_time					) .
					"', pay_amount = '" . trim(formData("Takeback$CountRow"   ))*-1  .
					"', adj_amount = '"    . 0 .
					"', Adj_code = '" . trim(formData("Adj_code$CountRow"   ))  .
					"', account_code = '" . "Takeback"  .
					"'");
				 }
		   }
		  else
		   {
		    sqlStatement("delete from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and pay_amount < 0");
		   }
//==============================================================================================================================
		  if (isset($_POST["FollowUp$CountRow"]) && $_POST["FollowUp$CountRow"]=='y')
		   {

				$resPayment = sqlStatement("SELECT  * from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and follow_up ='y'");
				if(sqlNumRows($resPayment)>0)
				 {
				  sqlStatement("update ar_activity set "    .
					"   post_user = '" . trim($user_id            )  .
					"', modified_time = '"  . trim($created_time					) .
					"', follow_up = '"    . "y" .
					"', follow_up_note = '"    . trim(formData("FollowUpReason$CountRow"   )) .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"' where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and  Adj_code = '" . trim(formData("Adj_code$CountRow"   ))  .
					"' and follow_up ='y'");
				 }
				else
				 {
				  sqlStatement("insert into ar_activity set "    .
					"pid = '"       . trim(formData("HiddenPId$CountRow" )) .
					"', encounter = '"     . trim(formData("HiddenEncounter$CountRow"   ))  .
					"', code = '"      . trim(formData("HiddenCode$CountRow"   ))  .
					"', modifier = '"      . trim(formData("HiddenModifier$CountRow"   ))  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"', post_time = '"  . trim($created_time					) .
					"', post_user = '" . trim($user_id            )  .
					"', session_id = '"    . trim(formData('payment_id')) .
					"', modified_time = '"  . trim($created_time					) .
					"', pay_amount = '" . 0  .
					"', adj_amount = '"    . 0 .
					"', follow_up = '"    . "y" .
					"', follow_up_note = '"    . trim(formData("FollowUpReason$CountRow"   )) .
					"', Adj_code = '" . trim(formData("Adj_code$CountRow"   ))  .
					"'");
				 }
		   }
		  else
		   {
		    sqlStatement("delete from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and follow_up ='y'");
		   }
//==============================================================================================================================

if ($_POST["Payment$CountRow"]*1<=0 && ($_POST["wo$CountRow"]*1 >0 || $_POST["Allowed$CountRow"]*1 >0))
		   {
		   $reason_code="";
		   if($_POST["wo$CountRow"]*1 >0)
		   $reason_code="wo";
		   
				
				$resPayment = sqlStatement("SELECT  * from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and (w_o!=0 || allowed_amt!=0) and payment<=0");
				if(sqlNumRows($resPayment)>0)
				 {
				  sqlStatement("update ar_activity set "    .
					"   post_user = '" . trim($user_id            )  .
					"', modified_time = '"  . trim($created_time					) .
					"', w_o = '"    . trim(formData("wo$CountRow"   )) .
					"', allowed_amt = '"    . trim(formData("Allowed$CountRow"   )) .
					"' , reason_code = '" . trim($reason_code)  .				
					"' where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .					
					"' and (w_o!=0 || allowed_amt!=0) and payment<=0 ");
				 }
				else
				 {
				  sqlStatement("insert into ar_activity set "    .
					"pid = '"       . trim(formData("HiddenPId$CountRow" )) .
					"', encounter = '"     . trim(formData("HiddenEncounter$CountRow"   ))  .
					"', code = '"      . trim(formData("HiddenCode$CountRow"   ))  .
					"', modifier = '"      . trim(formData("HiddenModifier$CountRow"   ))  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"', post_time = '"  . trim($created_time					) .
					"', post_user = '" . trim($user_id            )  .
					"', session_id = '"    . trim(formData('payment_id')) .
					"', modified_time = '"  . trim($created_time					) .
					"', pay_amount = '" . 0  .
					"', w_o = '"    .trim(formData("wo$CountRow")) .
					"', allowed_amt = '" . trim(formData("Allowed$CountRow")) .					
					"', reason_code = '" . trim($reason_code)  .
					"'");
				 }

		   }
		  else
		   {
		    sqlStatement("delete from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and pay_amount < 0 and (w_o!=0 || allowed_amt!=0) ");
		   }
//==============================================================================================================================
	   }
	  else
	   break;
	 }
	//=========
	//INSERTION of new entries,continuation of modification.
	//=========
	for($CountRow=$CountIndexAbove+1;$CountRow<=$CountIndexAbove+$CountIndexBelow;$CountRow++)
	 {
	  if (isset($_POST["HiddenEncounter$CountRow"]))
	   {
	    DistributionInsert($CountRow,$created_time,$user_id);
	   }
	  else
	   break;
	 }
	if($_REQUEST['global_amount']=='yes')
		sqlStatement("update ar_session set global_amount=".trim(formData("HidUnappliedAmount"   ))*1 ." where session_id ='$payment_id'");
	if($_POST["mode"]=="FinishPayments")
	 {
	  $Message='Finish';
	 }
    $_POST["mode"] = "searchdatabase";
	$Message='Modify';
   }
 }
//==============================================================================
//Search Code
//===============================================================================
$payment_id=$payment_id*1 > 0 ? $payment_id : $_REQUEST['payment_id'];
/*$ResultSearchSub = sqlStatement("SELECT  distinct encounter,code,modifier, pid from ar_activity where  session_id ='$payment_id' order by pid,encounter,code,modifier");*/

/*********code change by pawan for hide and show*****************/
/*$ResultSearchSub = sqlStatement("SELECT distinct (ar.encounter),ar.code,ar.modifier,ar.pid,pd.fname,pd.lname,pd.mname,pd.pubpid from ar_activity as ar left join patient_data as pd ON ar.pid = pd.pid where ar.session_id ='$payment_id' and ar.payer_type=1 order by ar.pid");*/

$ResultSearchSub = sqlStatement("SELECT distinct (ar.encounter),ar.code,ar.modifier,ar.pid,pd.fname,pd.lname,pd.mname,pd.pubpid from ar_activity as ar left join patient_data as pd ON ar.pid = pd.pid where ar.session_id ='$payment_id' order by ar.pid");


//==============================================================================
$DateFormat=DateFormatRead();
//==============================================================================
//===============================================================================
?>
<html>
	<head>
		<?php if (function_exists('html_header_show')) html_header_show(); ?>
		<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
		<?php /*<!-- supporting javascript code -->*/ ?>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
        <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
		<link rel="stylesheet" type="text/css" href="../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
		<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
        <script type="text/javascript" src="../../library/textformat.js"></script>
        <script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
        <?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
        <script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
        <script type="text/javascript" src="../../library/dialog.js"></script>
        <script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>
        <script type="text/javascript" src="../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
        <script language='JavaScript'>
         var mypcc = '1';
        </script>
        <?php include_once("{$GLOBALS['srcdir']}/payment_jav.inc.php"); ?>
        <?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>
        <script type="text/javascript" src="../../library/js/common.js"></script>
        <script LANGUAGE="javascript" TYPE="text/javascript"> 
		function OnloadAction()
		 {//Displays message while loading after some action.
		  after_value=document.getElementById('ActionStatus').value;
		  if(after_value=='Delete')
		   {
			alert("<?php echo htmlspecialchars( xl('Successfully Deleted'), ENT_QUOTES) ?>")
			
			return true;
		   }
		  if(after_value=='Modify' || after_value=='Finish')
		   {
			alert("<?php echo htmlspecialchars( xl('Successfully Modified'), ENT_QUOTES) ?>")
			return true;
		   }
		  after_value=document.getElementById('after_value').value;
		  payment_id=document.getElementById('payment_id').value;
		  if(after_value=='distribute')
		   {
		   }
		  else if(after_value=='new_payment')
		   {
			if(document.getElementById('TablePatientPortion'))
			 {
				document.getElementById('TablePatientPortion').style.display='none';
			 }
			if(confirm("<?php echo htmlspecialchars( xl('Successfully Saved.Would you like to Distribute?'), ENT_QUOTES) ?>"))
			 {
				if(document.getElementById('TablePatientPortion'))
				 {
					document.getElementById('TablePatientPortion').style.display='';
				 }
			 }
		   }
		
		 }
		function DeletePaymentDistribution(DeleteId)
		 {//Confirms deletion of payment distribution.
			if(confirm("<?php echo htmlspecialchars( xl('Would you like to Delete Payment Distribution?'), ENT_QUOTES) ?>"))
			 {
				document.getElementById('mode').value='DeletePaymentDistribution';
				document.getElementById('DeletePaymentDistributionId').value=DeleteId;
				top.restoreSession();
				
				document.forms[0].submit();
			 }
			else
			 return false;
		 }
		//========================================================================================
		</script>
		<script language="javascript" type="text/javascript">
			document.onclick=HideTheAjaxDivs;
		</script>
		<style>
		.class1{width:125px;}
		.class2{width:250px;}
		.class3{width:100px;}
		.bottom{border-bottom:1px solid black;}
		.top{border-top:1px solid black;}
		.left{border-left:1px solid black;}
		.right{border-right:1px solid black;}
		#ajax_div_insurance {
			position: absolute;
			z-index:10;
			background-color: #FBFDD0;
			border: 1px solid #ccc;
			padding: 10px;
		}
		#ajax_div_patient {
			position: absolute;
			z-index:10;
			background-color: #FBFDD0;
			border: 1px solid #ccc;
			padding: 10px;
		}
		</style>
		<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
	</head>
	<body class="body_top" onLoad="OnloadAction()"  >
		<form name='new_payment' method='post'  action="Payment_List.php" onsubmit='<?php if($payment_id==0){?>top.restoreSession();return SavePayment();<?php }else{?>return false;<?php }?>' style="display:inline" >
		<?php
        if($payment_id > 0){//Distribution rows already in the database are displayed.?>
            <table width="1024" border="0" cellspacing="0" cellpadding="10" bgcolor="#c1eafa">
                <tr>
                    <td>
                        <table width="1004" border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td colspan="13" align="left" >
							<?php 
                            /*$resCount = sqlStatement("SELECT distinct encounter,code,modifier from ar_activity where  session_id ='$payment_id' ");
							$TotalRows=sqlNumRows($resCount);*/
                            $CountPatient=0;
                            $CountIndex=0;
                            $CountIndexAbove=0;
                            $paymenttot=0;
                            $adjamttot=0;
                            $deductibletot=0;
                            $takebacktot=0;
							$wotot=0;
                            $allowedtot=0;
							
							$TotalRows1=sqlNumRows($ResultSearchSub);
							
                            if($RowSearchSub = sqlFetchArray($ResultSearchSub))
                             {
                                do 
                                 {
                                    $CountPatient++;
                                    $PId=$RowSearchSub['pid'];
									$PubPID=$RowSearchSub['pubpid'];
                                    $EncounterMaster=$RowSearchSub['encounter'];
                                    $CodeMaster=$RowSearchSub['code'];
                                    $ModifierMaster=$RowSearchSub['modifier'];
									$NameDB=$RowSearchSub['lname'].' '.$RowSearchSub['fname'].' '.$RowSearchSub['mname'];
                                    /*$res = sqlStatement("SELECT fname,lname,mname FROM patient_data	where pid ='$PId'");
                                    $row = sqlFetchArray($res);
                                    //$fname=$row['fname'];
                                    //$lname=$row['lname'];
                                    //$mname=$row['mname'];
                                    $NameDB=$row['lname'].' '.$row['fname'].' '.$row['mname'];*/
																	 
                                   /* $ResultSearch = sqlStatement("SELECT billing.id,last_level_closed,billing.encounter,form_encounter.`date`,billing.code,billing.modifier,fee,units FROM billing ,form_encounter where billing.encounter=form_encounter.encounter and billing.pid=form_encounter.pid and  code_type!='ICD9' and  code_type!='COPAY' and billing.activity!=0 and  form_encounter.pid ='$PId' and billing.pid ='$PId' and billing.encounter ='$EncounterMaster' and billing.code ='$CodeMaster'  ORDER BY form_encounter.`date`,form_encounter.encounter,billing.code,billing.modifier");*/
									
									$ResultSearch = sqlStatement("SELECT billing.id,last_level_closed,billing.encounter,form_encounter.`date`,billing.code,billing.modifier,fee,units FROM billing ,form_encounter where billing.encounter=form_encounter.encounter and billing.pid=form_encounter.pid and  code_type='CPT4' and billing.activity!=0 and  form_encounter.pid ='$PId' and billing.pid ='$PId' and billing.encounter ='$EncounterMaster' and billing.code ='$CodeMaster'  ORDER BY form_encounter.`date`,form_encounter.encounter,billing.code,billing.modifier");
									
									
                                    if(sqlNumRows($ResultSearch)>0)
                                     {
                                    if($CountPatient==1)
                                     {
                                     $Table='yes';
                                    ?>
						<table width="1004"  border="0" cellpadding="0" cellspacing="0" align="center" id="TableDistributePortion">
						  <tr class="text" bgcolor="#eafac1">
						    <td width="25" class="left top" >&nbsp;</td>
						    <td width="144" class="left top" ><?php echo htmlspecialchars( xl('Patient Name'), ENT_QUOTES) ?></td>
                             <td width="144" class="left top" ><?php echo htmlspecialchars( xl('External ID'), ENT_QUOTES) ?></td>
							<td width="55" class="left top" ><?php echo htmlspecialchars( xl('Post For'), ENT_QUOTES) ?></td>
							<td width="70" class="left top" ><?php echo htmlspecialchars( xl('Srv Date'), ENT_QUOTES) ?></td>
							<td width="50" class="left top" ><?php echo htmlspecialchars( xl('Encnter'), ENT_QUOTES) ?></td>
							<td width="65" class="left top" ><?php echo htmlspecialchars( xl('CPT Code'), ENT_QUOTES) ?></td>
							<td width="50" class="left top" ><?php echo htmlspecialchars( xl('Charge'), ENT_QUOTES) ?></td>
							<td width="40" class="left top" ><?php echo htmlspecialchars( xl('Copay'), ENT_QUOTES) ?></td>
							<td width="40" class="left top" ><?php echo htmlspecialchars( xl('Balance'), ENT_QUOTES) ?></td>
							<td width="60" class="left top" ><?php echo htmlspecialchars( xl('Allowed(c)'), ENT_QUOTES) ?></td><!-- (c) means it is calculated.Not stored one. -->
							<td width="60" class="left top" ><?php echo htmlspecialchars( xl('Payment'), ENT_QUOTES) ?></td>
							<td width="70" class="left top" ><?php echo htmlspecialchars( xl('Adj Amount'), ENT_QUOTES) ?></td>
							<td width="60" class="left top" ><?php echo htmlspecialchars( xl('Deductible'), ENT_QUOTES) ?></td>
							<td width="60" class="left top" ><?php echo htmlspecialchars( xl('Takeback'), ENT_QUOTES) ?></td>
                            <?php /*?> Added by sangram babar for bug id 7960, 8033<?php */?>
                <td width="60" class="left top" ><?php echo htmlspecialchars( xl('W/O'), ENT_QUOTES) ?></td>
               <td width="60" class="left top" ><?php echo htmlspecialchars( xl('co-ins'), ENT_QUOTES) ?></td>
               <td width="60" class="left top" ><?php echo htmlspecialchars( xl('interest'), ENT_QUOTES) ?></td> 
               <td width="60" class="left top" ><?php echo htmlspecialchars( xl('Receipts'), ENT_QUOTES) ?></td>
           <?php /*?>    	//commented by sangram for rollbacking Balance amount column bug id 8326<?php */?>
              <?php /*?> <td width="60" class="left top" ><?php echo htmlspecialchars( xl('Open Balance'), ENT_QUOTES) ?></td> <?php */?>
                			<?php /*?> -------------------- <?php */?>
                            <td width="70" class="left top" ><?php echo htmlspecialchars( xl('Adj code'), ENT_QUOTES) ?></td>
							<td width="60" class="left top" ><?php echo htmlspecialchars( xl('MSP Code'), ENT_QUOTES) ?></td>
							<td width="40" class="left top" ><?php echo htmlspecialchars( xl('Resn / Note'), ENT_QUOTES) ?></td>
							<td width="110" class="left top right" ><?php echo htmlspecialchars( xl('Follow Up Reason / Note'), ENT_QUOTES) ?></td>
						  </tr>
						  <?php
						  }						
							while ($RowSearch = sqlFetchArray($ResultSearch))
							 {
								$CountIndex++;
								$CountIndexAbove++;
								$ServiceDateArray=split(' ',$RowSearch['date']);
								$ServiceDate=oeFormatShortDate($ServiceDateArray[0]);
								$Code=$RowSearch['code'];
								$Modifier =$RowSearch['modifier'];
								if($Modifier!='')
								 $ModifierString=", $Modifier";
								else
								 $ModifierString="";
								$Fee=$RowSearch['fee']* $RowSearch['units'];;
								
								$Encounter=$RowSearch['encounter'];
								
								$resPayer=sqlStatement("SELECT  Adj_code,payer_type,reason_code from ar_activity where  session_id ='$payment_id' and pid ='$PId' and  encounter  ='$Encounter' and  code='$Code' and modifier='$Modifier' ");
								$adj = sqlFetchArray($resPayer);
								$Ins=$adj['payer_type'];
								$ReasonCodeDB=$adj['reason_code'];
	
								$lres = sqlStatement("SELECT * FROM list_options WHERE list_id = 'AdjCode' ORDER BY seq");
								while ($lrow = sqlFetchArray($lres)) {
								  $AdjCodes[$lrow['option_id']] = $lrow['title'];
								}
								  								
								/*
								$resPayer = sqlStatement("SELECT  payer_type from ar_activity where  session_id ='$payment_id' and
								pid ='$PId' and  encounter  ='$Encounter' and  code='$Code' and modifier='$Modifier' ");
								$rowPayer = sqlFetchArray($resPayer);
								$Ins=$rowPayer['payer_type'];*/
					
								//Always associating the copay to a particular charge.
								$BillingId=$RowSearch['id'];
								
								/*$resId = sqlStatement("SELECT id  FROM billing where code_type!='ICD9' and  code_type!='COPAY'  and
								pid ='$PId' and  encounter  ='$Encounter' and billing.activity!=0 order by id");*/
								
								$resId = sqlStatement("SELECT id  FROM billing where code_type='CPT4'  and
								pid ='$PId' and  encounter  ='$Encounter' and billing.activity!=0 order by id");
								$rowId = sqlFetchArray($resId);
								$Id=$rowId['id'];
			
								if($BillingId!=$Id)//multiple cpt in single encounter
								 {
									$Copay=0.00;
								 }
								else{
									$resCopay = sqlStatement("SELECT sum(fee) as copay FROM billing where
									code_type='COPAY' and  pid ='$PId' and  encounter  ='$Encounter' and billing.activity!=0");
									$rowCopay = sqlFetchArray($resCopay);
									$Copay=$rowCopay['copay']*-1;

									$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as PatientPay FROM ar_activity where pid ='$PId'  and  encounter  ='$Encounter' and  payer_type=0 and (code='CO-PAY' or account_code='PCP')");
									//new fees screen copay gives account_code='PCP'
									//openemr payment screen copay gives code='CO-PAY'
	/*	$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as PatientPay FROM ar_activity where pid ='$PId'  and  encounter  ='$Encounter' and  !(payer_type=0 and (code='CO-PAY' or account_code='PCP'))");*/


									$rowMoneyGot = sqlFetchArray($resMoneyGot);
									$PatientPay=$rowMoneyGot['PatientPay'];
									
									$Copay=$Copay+$PatientPay;
								  }
// ==================== Commented following code as it is duplicate START===================================
//
//									//For calculating Remainder
//									if($Ins==0)
//									 {//Fetch all values
//										$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as MoneyGot,sum(adj_amount) as MoneyAdjusted FROM ar_activity where pid ='$PId' and  code='$Code' and modifier='$Modifier'  and  encounter  ='$Encounter' and  !(payer_type=0 and (code='CO-PAY' or account_code='PCP'))");
//										
//										//new fees screen copay gives account_code='PCP'
//										//openemr payment screen copay gives code='CO-PAY'
//										$rowMoneyGot = sqlFetchArray($resMoneyGot);
//										$MoneyGot=$rowMoneyGot['MoneyGot'];
//										$MoneyAdjusted=$rowMoneyGot['MoneyAdjusted'];
//	
//										/*$resMoneyAdjusted = sqlStatement("SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where pid ='$PId' and  code='$Code' and modifier='$Modifier'  and  encounter  ='$Encounter'");
//										$rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
//										$MoneyAdjusted=$rowMoneyAdjusted['MoneyAdjusted'];*/
//									 }
//									else//Fetch till that much got
//									 {
//										//Fetch the HIGHEST sequence_no till this session.
//										//Used maily in  the case if primary/others pays once more.
//										$resSequence = sqlStatement("SELECT  sequence_no from ar_activity where  session_id ='$payment_id' and pid ='$PId' and  encounter  ='$Encounter' order by sequence_no desc ");
//										$rowSequence = sqlFetchArray($resSequence);
//										$Sequence=$rowSequence['sequence_no'];
//
//										$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as MoneyGot,sum(adj_amount) as MoneyAdjusted FROM ar_activity where pid ='$PId' and  code='$Code' and modifier='$Modifier'  and  encounter  ='$Encounter' and  payer_type > 0 and payer_type <='$Ins' and sequence_no<='$Sequence'");
//										$rowMoneyGot = sqlFetchArray($resMoneyGot);
//										$MoneyGot=$rowMoneyGot['MoneyGot'];
//										$MoneyAdjusted=$rowMoneyGot['MoneyAdjusted'];
//	
//										/*$resMoneyAdjusted = sqlStatement("SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where pid ='$PId' and  code='$Code' and modifier='$Modifier'   and  encounter  ='$Encounter' and payer_type > 0 and payer_type <='$Ins' and sequence_no<='$Sequence'");
//										$rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
//										$MoneyAdjusted=$rowMoneyAdjusted['MoneyAdjusted'];*/
//									 }
//									$Remainder=$Fee-$Copay-$MoneyGot-$MoneyAdjusted;
// ==================== Commented following code as it is duplicate END===================================

									
									//For calculating RemainderJS.Used while restoring back the values.
								
									/*if($Ins==0)
									 {*///Got just before Patient
										
										$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as MoneyGot,sum(adj_amount) as MoneyAdjusted FROM ar_activity where pid ='$PId' and  code='$Code' and modifier='$Modifier' and  encounter  ='$Encounter' and payer_type!='0'");
										$rowMoneyGot = sqlFetchArray($resMoneyGot);
										$MoneyGot=$rowMoneyGot['MoneyGot'];
										$MoneyAdjusted=$rowMoneyGot['MoneyAdjusted'];
										//$W_O=$rowMoneyGot['w_o'];
										//$allowed_amt=$rowMoneyGot['allowed_amt'];
										
										$resReceipt = sqlStatement("SELECT sum(pay_amount) as receipts FROM ar_activity where code='$Code' and modifier='$Modifier' and  encounter  ='$Encounter' ");
									
									$rowReceipt=sqlFetchArray($resReceipt);
									$receipts=$rowReceipt['receipts'];
									
									//By GANGEYA : BUG ID 10345
									//Negative writeoff not reflecting in Balance Calculations
									//Added condition as ()
									$resReceipt = sqlStatement("select sum(w_o) as wrt_off from ar_activity where encounter='$Encounter' and (pay_amount>'0.00' or reason_code='wo' or account_code = 'Takeback') and  code='$Code' and modifier = '$Modifier' and  payer_type!='0'");
									$rowReceipt=sqlFetchArray($resReceipt);
									$W_O=$rowReceipt['wrt_off'];
					
					
									 $resReceipt = sqlStatement("select allowed_amt as allowed_amt  from ar_activity where encounter='$Encounter' and  payer_type!='0' and  code='$Code'  and modifier='$Modifier' and allowed_amt!='0' limit 1");
									$rowReceipt=sqlFetchArray($resReceipt);
									$allowed_amt1=$rowReceipt['allowed_amt']; 
									
				//commented by sangram for rollbacking Balance amount column bug id 8326
										//$open_balance=$rowMoneyGot['open_balance'];
	
										/*$resMoneyAdjusted = sqlStatement("SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where pid ='$PId' and  code='$Code' and modifier='$Modifier'  and  encounter  ='$Encounter' and payer_type !=0");
										$rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
										$MoneyAdjusted=$rowMoneyAdjusted['MoneyAdjusted'];*/
									/* }
									else
									 {*///Got just before the previous
										//Fetch the LOWEST sequence_no till this session.
										//Used maily in  the case if primary/others pays once more.
										/*$resSequence = sqlStatement("SELECT  sequence_no,session_id,w_o from ar_activity where  session_id ='$payment_id' and pid ='$PId' and  encounter  ='$Encounter' order by sequence_no  ");
										$rowSequence = sqlFetchArray($resSequence);
										$Sequence=$rowSequence['sequence_no'];
										$Session_id=$rowSequence['session_id'];
										
										echo 'three';							
										$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as MoneyGot,sum(adj_amount) as MoneyAdjusted,w_o,allowed_amt FROM ar_activity where pid ='$PId' and  code='$Code' and modifier='$Modifier'   and  encounter  ='$Encounter' 
										and payer_type > 0  and payer_type <='$Ins' and session_id='$Session_id'  ");
										$rowMoneyGot = sqlFetchArray($resMoneyGot);
										$MoneyGot=$rowMoneyGot['MoneyGot'];
										$MoneyAdjusted=$rowMoneyGot['MoneyAdjusted'];
										$W_O=$rowMoneyGot['w_o'];
										$allowed_amt=$rowMoneyGot['allowed_amt'];
									
										$resReceipt = sqlStatement("SELECT sum(pay_amount) as receipts FROM ar_activity where code='$Code' and  encounter  ='$Encounter'");
									
									$rowReceipt=sqlFetchArray($resReceipt);
									$receipts=$rowReceipt['receipts'];*/
								
			//commented by sangram for rollbacking Balance amount column bug id 8326
										//$open_balance=$rowMoneyGot['open_balance'];
						
						
								/*$resMoneyAdjusted = sqlStatement("SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where pid ='$PId' and  code='$Code' and modifier='$Modifier'   and  encounter  ='$Encounter' 
										and payer_type <='$Ins' and sequence_no<'$Sequence' ");
										$rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
										$MoneyAdjusted=$rowMoneyAdjusted['MoneyAdjusted'];*/
									// }
									//Stored in hidden so that can be used while restoring back the values.
							//  echo "===Formula===".$Fee."-".$Copay."-".$MoneyGot."-".$MoneyAdjusted."-".$W_O;
								
								
									//$RemainderJS=$Fee-$Copay-$MoneyGot-$MoneyAdjusted-$W_O;
									$RemainderJS = $Fee - ($Copay + $MoneyGot + $MoneyAdjusted + $W_O);
									
									$resPayment = sqlStatement("SELECT  pay_amount,memo,w_o,co_ins,interest,allowed_amt from ar_activity where  session_id ='$payment_id' and pid ='$PId' and  encounter  ='$Encounter' and  code='$Code' and modifier='$Modifier'  and pay_amount>0");
									$rowPayment = sqlFetchArray($resPayment);
									$PaymentDB=$rowPayment['pay_amount']*1;
									$PaymentDB=$PaymentDB == 0 ? '0' : $PaymentDB;
									$writeoff=$rowPayment['w_o'];
									$coinsurance=$rowPayment['co_ins'];
									$interest=$rowPayment['interest'];
									$allowed_amt=$rowPayment['allowed_amt'];
	//below added by sangram for bug 8777								
	if($Ins==2 && $PaymentDB!=0)
	{
	
  $checkres = sqlStatement("SELECT EXISTS(SELECT * FROM ar_activity WHERE encounter=$Encounter and code='claim') as claim_exist");
  $checkrow = sqlFetchArray($checkres);
   if($checkrow['claim_exist'])
   {
   
	
	
	
   $claim_memo=$rowPayment['memo'];
  $claim_payment_res = sqlStatement("select pay_amount from ar_activity where code='claim' and pay_amount<0 and encounter=$Encounter and payer_type=2 and memo='$claim_memo' ");
  $claim_payment_row = sqlFetchArray($claim_payment_res);
  $PaymentDB += $claim_payment_row['pay_amount'];
  
  $claim_payment_row['pay_amount'] *= -1;
  $RemainderJS += $claim_payment_row['pay_amount'];
  

   }

	}
									$resPayment = sqlStatement("SELECT  pay_amount from ar_activity where  session_id ='$payment_id' and pid ='$PId' and  encounter  ='$Encounter' and  code='$Code' and modifier='$Modifier'  and pay_amount<0");
									$rowPayment = sqlFetchArray($resPayment);
									$TakebackDB=$rowPayment['pay_amount']*-1;
									$TakebackDB=$TakebackDB == 0 ? '' : $TakebackDB;


									$resPayment = sqlStatement("SELECT  sum(adj_amount) as adj_amount,w_o from ar_activity where  session_id ='$payment_id' and pid ='$PId' and  encounter  ='$Encounter' and  code='$Code' and modifier='$Modifier' ");
									$rowPayment = sqlFetchArray($resPayment);
									$AdjAmountDB=$rowPayment['adj_amount']*1;
									//$WOAmountDB=$rowPayment['w_o']*1;
									$AdjAmountDB=$AdjAmountDB == 0 ? '0' : $AdjAmountDB;
									//$WOAmountDB=$WOAmountDB == 0 ? '0' : $WOAmountDB;

									$resPayment = sqlStatement("SELECT  memo from ar_activity where  session_id ='$payment_id' and pid ='$PId' and  encounter  ='$Encounter' and  code='$Code' and modifier='$Modifier'  and (memo like 'Deductable%' or memo like '%dedbl%')");
									// changes made by sangram for bug 8773	ERA - Deductible issue
									$rowPayment = sqlFetchArray($resPayment);
									$DeductibleDB=$rowPayment['memo'];
									
									//By GANGEYA : BUG ID 10345
									//Negative writeoff not reflecting in Balance Calculations
									//Added condition as ()
									$resReceipt = sqlStatement("select sum(w_o) as wrt_off from ar_activity where encounter='$Encounter' and (pay_amount>'0.00' or reason_code='wo' or account_code = 'Takeback') and  code='$Code' and modifier = '$Modifier' and session_id ='$payment_id'");
									$rowReceipt=sqlFetchArray($resReceipt);
									$W_O=$rowReceipt['wrt_off'];
																		
									
								// changes made by sangram for bug 8773	ERA - Deductible issue
									if (strpos($DeductibleDB,'Deductable $') !== false) {
									  $DeductibleDB=str_replace('Deductable $','',$DeductibleDB);		
									}
									// changes made by sangram for bug 8773	ERA - Deductible issue
									if (strpos($DeductibleDB,'Ins1 dedbl:') !== false) {
									  $DeductibleDB=str_replace('Ins1 dedbl:','',$DeductibleDB);		
									}
										// changes made by sangram for bug 8773	ERA - Deductible issue
									if (strpos($DeductibleDB,'Ins2 dedbl:') !== false) {
  											  $DeductibleDB=str_replace('Ins2 dedbl:','',$DeductibleDB);		
									}
										// changes made by sangram for bug 8773	ERA - Deductible issue
									if (strpos($DeductibleDB,'Ins3 dedbl:') !== false) {
  										  $DeductibleDB=str_replace('Ins3 dedbl:','',$DeductibleDB);		
									}

$resReceipt = sqlStatement("select allowed_amt as allowed_amt  from ar_activity where encounter='$Encounter' and  payer_type!='0' and  code='$Code' and modifier='$Modifier' and allowed_amt!='0' and session_id ='$payment_id' limit 1");
									$rowReceipt=sqlFetchArray($resReceipt);
									$allowed_amt=$rowReceipt['allowed_amt']; 
									
									$resPayment = sqlStatement("SELECT  follow_up,follow_up_note from ar_activity where  session_id ='$payment_id' and pid ='$PId' and  encounter  ='$Encounter' and  code='$Code' and modifier='$Modifier'  and follow_up = 'y'");
									$rowPayment = sqlFetchArray($resPayment);
									$FollowUpDB=$rowPayment['follow_up'];
									$FollowUpReasonDB=$rowPayment['follow_up_note'];
									
									/*$resPayment = sqlStatement("SELECT reason_code from ar_activity where  session_id ='$payment_id' and pid ='$PId' and  encounter  ='$Encounter' and  code='$Code' and modifier='$Modifier'");
									$rowPayment = sqlFetchArray($resPayment);
									$ReasonCodeDB=$rowPayment['reason_code'];*/

									/*if($Ins==1)
									 {
										$AllowedDB=number_format($Fee-($PaymentDB+$WOAmountDB),2);
									 }
									else
									 {
									  	$AllowedDB = 0;
									 }
									$AllowedDB=$AllowedDB == 0 ? '0' : $AllowedDB;*/

									if($CountIndex==$TotalRows)
									 {
										$StringClass=' bottom left top ';
									 }
									else
									 {
										$StringClass=' left top ';
									 }
	
									if($Ins==1)
									 {
										$bgcolor='#dce0f6';
									 }
									elseif($Ins==2)
									 {
										$bgcolor='#ffdddd';
									 }
									elseif($Ins==3)
									 {
										$bgcolor='#F2F1BC';
									 }
									elseif($Ins==0)
									 {
										$bgcolor='#AAFFFF';
									 }
									 $paymenttot=$paymenttot+$PaymentDB;
									 $adjamttot=$adjamttot+$AdjAmountDB;
									  $wotot = $wotot+$W_O;
									 $deductibletot=$deductibletot+$DeductibleDB;
									 $takebacktot=$takebacktot+$TakebackDB;
									 $allowedtot=$allowed_amt1+$allowedtot;
							  ?>
                              <tr class="text"  bgcolor='<?php echo $bgcolor; ?>' id="trCharges<?php echo $CountIndex; ?>">
                                <td align="left" class="<?php echo $StringClass; ?>" ><a href="#" onClick="javascript:return DeletePaymentDistribution('<?php echo  htmlspecialchars($payment_id.'_'.$PId.'_'.$Encounter.'_'.$Code.'_'.$Modifier); ?>');" ><img src="../pic/Delete.gif" border="0"/></a></td>
                                <td align="left" class="<?php echo $StringClass; ?>" ><?php echo htmlspecialchars($NameDB); ?><input name="HiddenPId<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($PId); ?>" type="hidden"/></td>
                                 <td align="left" class="<?php echo $StringClass; ?>" ><?php echo htmlspecialchars($PubPID); ?><input name="HiddenPId<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($PId); ?>" type="hidden"/></td>
                                <td align="left" class="<?php echo $StringClass; ?>" ><input name="HiddenIns<?php echo $CountIndex; ?>" id="HiddenIns<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars($Ins); ?>" type="hidden"/><?php echo generate_select_list("payment_ins$CountIndex", "payment_ins", "$Ins", "Insurance/Patient",'','','ActionOnInsPat("'.$CountIndex.'")'); ?> </td>
                                <td class="<?php echo $StringClass; ?>" ><?php echo htmlspecialchars($ServiceDate); ?></td>
                                <td align="right" class="<?php echo $StringClass; ?>" ><input name="HiddenEncounter<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($Encounter); ?>" type="hidden"/><?php echo htmlspecialchars($Encounter); ?></td>
                                <td class="<?php echo $StringClass; ?>" ><input name="HiddenCode<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($Code); ?>" type="hidden"/><?php echo htmlspecialchars($Code.$ModifierString); ?><input name="HiddenModifier<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($Modifier); ?>" type="hidden"/></td>
                                <td align="right" class="<?php echo $StringClass; ?>" ><input name="HiddenChargeAmount<?php echo $CountIndex; ?>" id="HiddenChargeAmount<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars($Fee); ?>" type="hidden"/><?php echo htmlspecialchars($Fee); ?></td>
                                <td align="right" class="<?php echo $StringClass; ?>" ><input name="HiddenCopayAmount<?php echo $CountIndex; ?>" id="HiddenCopayAmount<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars($Copay); ?>" type="hidden"/><?php echo htmlspecialchars(number_format($Copay,2)); ?></td>
<?php /*?><!--  this is remove for making free text entry for allowed amount    onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);UpdateTotalValues(1,<?php echo $TotalRows; ?>,'Allowed','allowtotal');UpdateTotalValues(1,<?php echo $TotalRows; ?>,'Payment','paymenttotal');UpdateTotalValues(1,<?php echo $TotalRows; ?>,'AdjAmount','AdjAmounttotal');RestoreValues(<?php echo $CountIndex; ?>)"    --><?php */?>                       
                            
                                <td align="right"   id="RemainderTd<?php echo $CountIndex; ?>"  class="<?php echo $StringClass; ?>" ><?php echo htmlspecialchars(round($RemainderJS,2)); ?></td>
                                <input name="HiddenRemainderTd<?php echo $CountIndex; ?>" id="HiddenRemainderTd<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars(round($RemainderJS,2)); ?>" type="hidden"/>
                                <?php /*?><td class="<?php echo $StringClass; ?>" ><input  name="Allowed<?php echo $CountIndex; ?>" id="Allowed<?php echo $CountIndex; ?>"  onKeyDown="PreventIt(event)"  autocomplete="off"  value="<?php echo htmlspecialchars($AllowedDB); ?>"  onChange=""   type="text"   style="width:60px;text-align:right; font-size:12px" readonly/></td><?php */?>
                                
                                
                                <td class="<?php echo $StringClass; ?>" ><input  name="Allowed<?php echo $CountIndex; ?>"  id="Allowed<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($allowed_amt1); ?>" onKeyDown="PreventIt(event)"  onChange="ValidateNumeric(this);UpdateTotalValues(<?php echo $CountIndexAbove*1+1; ?>,<?php echo $TotalRows; ?>,'Deductible','initialdeductibletotal');"   autocomplete="off"   type="text" style="width:60px;text-align:right; font-size:12px" /></td>
                                
                                <td class="<?php echo $StringClass; ?>" ><input   type="text"  name="Payment<?php echo $CountIndex; ?>"  onKeyDown="PreventIt(event)"   autocomplete="off"  id="Payment<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($PaymentDB); ?>"  onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);UpdateTotalValues(1,<?php echo $TotalRows; ?>,'Payment','paymenttotal');RestoreValues(<?php echo $CountIndex; ?>)"  style="width:60px;text-align:right; font-size:12px"  readonly/></td>
                                <td class="<?php echo $StringClass; ?>" ><input  name="AdjAmount<?php echo $CountIndex; ?>"  onKeyDown="PreventIt(event)"   autocomplete="off"  id="AdjAmount<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars($AdjAmountDB); ?>"   onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);UpdateTotalValues(1,<?php echo $TotalRows; ?>,'AdjAmount','AdjAmounttotal');RestoreValues(<?php echo $CountIndex; ?>)"  type="text"   style="width:70px;text-align:right; font-size:12px" readonly/></td>
                            
                                <td class="<?php echo $StringClass; ?>" ><input  name="Deductible<?php echo $CountIndex; ?>"  id="Deductible<?php echo $CountIndex; ?>"  onKeyDown="PreventIt(event)"  onChange="ValidateNumeric(this);UpdateTotalValues(1,<?php echo $TotalRows; ?>,'Deductible','deductibletotal');" value="<?php echo htmlspecialchars($DeductibleDB); ?>"   autocomplete="off"   type="text"   style="width:60px;text-align:right; font-size:12px" readonly/></td>
    <!--// Allowed(i)=payment(i)+deductible(i)   -->                        
                                
                                <td class="<?php echo $StringClass; ?>" ><input  name="Takeback<?php echo $CountIndex; ?>"  onKeyDown="PreventIt(event)"   autocomplete="off"   id="Takeback<?php echo $CountIndex; ?>"   value="<?php echo htmlspecialchars($TakebackDB); ?>"   onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);UpdateTotalValues(1,<?php echo $TotalRows; ?>,'Takeback','takebacktotal');RestoreValues(<?php echo $CountIndex; ?>)"   type="text"   style="width:60px;text-align:right; font-size:12px" readonly /></td>
                                
                 <?php /*?> below two td Added by sangram babar for bug id 7960, 8033<?php */?>
                    
                  <td class="<?php echo $StringClass; ?>" ><input  name="wo<?php echo $CountIndex; ?>"  id="wo<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($W_O); ?>"
				 onKeyDown="PreventIt(event)"  onChange="ValidateNumeric(this);UpdateTotalValues(1,<?php echo $TotalRows; ?>,'wo','WOAmounttotal');RestoreValues(<?php echo $CountIndex; ?>)"   autocomplete="off"   type="text"   
				 style="width:60px;text-align:right; font-size:12px" /></td>      

                 <td class="<?php echo $StringClass; ?>" ><input  name="coins<?php echo $CountIndex; ?>"  id="coins<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($coinsurance); ?>" onKeyDown="PreventIt(event)"  onChange="ValidateNumeric(this);UpdateTotalValues(<?php echo $CountIndexAbove*1+1; ?>,<?php echo $TotalRows; ?>,'Deductible','initialdeductibletotal');"   autocomplete="off"   type="text"   
				 style="width:60px;text-align:right; font-size:12px" /></td>
                  <td class="<?php echo $StringClass; ?>" ><input  name="interset<?php echo $CountIndex; ?>"  id="interset<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($interest); ?>" onKeyDown="PreventIt(event)"  onChange="ValidateNumeric(this);UpdateTotalValues(<?php echo $CountIndexAbove*1+1; ?>,<?php echo $TotalRows; ?>,'Deductible','initialdeductibletotal');"   autocomplete="off"   type="text"   
				 style="width:60px;text-align:right; font-size:12px" /></td>
                 
                  <td class="<?php echo $StringClass; ?>" ><input  name="HiddenReceipts<?php echo $CountIndex; ?>"  id="HiddenReceipts<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($receipts); ?>" autocomplete="off"   type="text"   
				 style="width:60px;text-align:right; font-size:12px" readonly/></td>
                <?php /*?> 	//commented by sangram for rollbacking Balance amount column bug id 8326<?php */?>
                 <?php /*?> <td class="<?php echo $StringClass; ?>" ><input  name="HiddenOpenbalance<?php echo $CountIndex; ?>"  id="HiddenOpenbalance<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($open_balance); ?>" autocomplete="off"   type="text"   
				 style="width:60px;text-align:right; font-size:12px" readonly /></td><?php */?>
                 

                                <td class="<?php echo $StringClass; ?>" >
                                <select name='Adj_code<?php echo $CountIndex; ?>' id="Adj_code<?php echo $CountIndex; ?>" disabled>
      								<option value='' selected='selected'>--</option>
									 <?php
										$adjc=$adj['Adj_code'];
										foreach ($AdjCodes as $key => $value){
										if ($adjc==$key){
										$s="selected='selected'";
										}
										else{
										$s="";
										}
										echo "<option value='$key' $s>$value</option>";	
										}
										?>
                                 </select>
                            </td>
       
							<td align="left" class="<?php echo $StringClass; ?>" ><input name="HiddenReasonCode<?php echo $CountIndex; ?>" id="HiddenReasonCode<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars($ReasonCodeDB); ?>" type="hidden"/><?php echo generate_select_list( "ReasonCode$CountIndex", "msp_remit_codes", "$ReasonCodeDB", "MSP Code" ); ?></td>							
							<td align="center" class="<?php echo $StringClass; ?>" ><input type="checkbox" id="FollowUp<?php echo $CountIndex; ?>"  name="FollowUp<?php echo $CountIndex; ?>" value="y" onClick="ActionFollowUp(<?php echo $CountIndex; ?>)" <?php echo $FollowUpDB=='y' ? ' checked ' : ''; ?> disabled /></td>
							<td class="<?php echo $StringClass; ?> right" ><input  onKeyDown="PreventIt(event)" id="FollowUpReason<?php echo $CountIndex; ?>"    name="FollowUpReason<?php echo $CountIndex; ?>"  <?php echo $FollowUpDB=='y' ? '' : ' readonly '; ?>  type="text"  value="<?php echo htmlspecialchars($FollowUpReasonDB); ?>"    style="width:110px;font-size:12px" readonly /></td>
						  </tr>
						<?php
							 }//while ($RowSearch = sqlFetchArray($ResultSearch))
						?>
						<?php
						 }//if(sqlNumRows($ResultSearch)>0)

						 }while ($RowSearchSub = sqlFetchArray($ResultSearchSub));
						if($Table=='yes')
						 {
						?>
						 <tr class="text">
						    <td align="left" colspan="10">&nbsp;</td>
					        <td class="left bottom" bgcolor="#6699FF" id="allowtotal" align="right" ><?php echo htmlspecialchars(number_format($allowedtot,2)); ?></td>
					        <td class="left bottom" bgcolor="#6699FF" id="paymenttotal" align="right" ><?php echo htmlspecialchars(number_format($paymenttot,2)); ?></td>
	  						<td class="left bottom" bgcolor="#6699FF" id="AdjAmounttotal" align="right" ><?php echo htmlspecialchars(number_format($adjamttot,2)); ?></td>						
			      			<td class="left bottom" bgcolor="#6699FF" id="deductibletotal" align="right"><?php echo htmlspecialchars(number_format($deductibletot,2)); ?></td>
						    <td class="left bottom right" bgcolor="#6699FF" id="takebacktotal" align="right"><?php echo htmlspecialchars(number_format($takebacktot,2)); ?></td>
                            <td class="left bottom" bgcolor="#6699FF" id="WOAmounttotal" align="right" ><?php echo htmlspecialchars(number_format($wotot,2)); ?></td>
						    <td  align="center">&nbsp;</td>
						    <td  align="center">&nbsp;</td>
				          </tr>
						</table>
						<?php
						}
						?>
						<?php

						echo '<br/>';

				}//if($RowSearchSub = sqlFetchArray($ResultSearchSub))
				?>		    </td>
		  </tr>
		  <tr>
		    <td colspan="13" align="left" >
				
			</td>
	      </tr>
		  <tr>
			<td colspan="13" align="left" >
		<?php if  ($CountIndex == 0 )
				{ 
                    echo "No payment Exists";
                  }

		 }//if($payment_id*1>0)
		?>		</td>
	  </tr>
	</table>
	</td></tr></table>
</form>
</body>
</html>
