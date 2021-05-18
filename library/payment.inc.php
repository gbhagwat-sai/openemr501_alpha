<?php
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
// Sai custom code start
// Post a payment to the payments table.
//
$my_today_date = date('Y-m-d H:i:s');
function frontPayment($patient_id, $encounter, $method, $source, $amount1, $amount2, $timestamp, $auth = "")
{

    if (empty($auth)) {
        $auth=$_SESSION['authUser'];
    }

    $tmprow = sqlQuery(
        "SELECT date FROM form_encounter WHERE " .
        "encounter=? and pid=?",
        array($encounter,$patient_id)
    );
        //the manipulation is done to insert the amount paid into payments table in correct order to show in front receipts report,
        //if the payment is for today's encounter it will be shown in the report under today field and otherwise shown as previous
    $tmprowArray=explode(' ', $tmprow['date']);
    if (date('Y-m-d')==$tmprowArray[0]) {
        if ($amount1==0) {
              $amount1=$amount2;
              $amount2=0;
        }
    } else {
        if ($amount2==0) {
              $amount2=$amount1;
              $amount1=0;
        }
    }

    $payid = sqlInsert("INSERT INTO payments ( " .
    "pid, encounter, dtime, user, method, source, amount1, amount2 " .
    ") VALUES ( ?, ?, ?, ?, ?, ?, ?, ?)", array($patient_id,$encounter,$timestamp,$auth,$method,$source,$amount1,$amount2));
    return $payid;
}

//===============================================================================
//This section handles the common functins of payment screens.
//===============================================================================
function DistributionInsert($CountRow, $created_time, $user_id)
{
//Function inserts the distribution.Payment,Adjustment,Deductible,Takeback & Follow up reasons are inserted as seperate rows.
 //It automatically pushes to next insurance for billing.
 //In the screen a drop down of Ins1,Ins2,Ins3,Pat are given.The posting can be done for any level.
	$modifier=$_SESSION{"authUser"};
	sqlStatement("INSERT INTO `encounter_status`(`Encounter`,`Status`,`Status_Date`,`modifier`)VALUES(
'". trim(formData("HiddenEncounter$CountRow"   ))  ."','". trim(formData("Encounter_Status$CountRow"   ))  ."',NOW(),'$modifier')");
$status = trim(formData("Encounter_Status$CountRow"   ));
$encounter = trim(formData("HiddenEncounter$CountRow"   ));
$qsql = sqlStatement("SELECT id, status,iphone_status FROM claim_status where status='$status'");
	$statusrow = sqlFetchArray($qsql);
	$claim_status_id = $statusrow['id'];

	// code update for last modified date by pawan on 24-03-2017
	$modified_by= $_SESSION{'authUserID'};
	$my_today_date = date('Y-m-d H:i:s');
	if($claim_status_id){
	
    sqlQuery("Update form_encounter set claim_status_id=$claim_status_id,modified_date='$my_today_date',modified_by=$modified_by where encounter='$encounter' ");
	}
    $Affected='no';
	$Write_off = trim(formData("wo$CountRow"));
	$Co_insurance = trim(formData("coins$CountRow"));
	$interest_amt = trim(formData("interest$CountRow"));
	$allowed_amt = trim(formData("Allowed$CountRow")); 
	$receipts = trim(formData("HiddenReceipts$CountRow")); 
	$copay = trim(formData("HiddenCopayAmount$CountRow")); 
	//commented by sangram for rollbacking Balance amount column bug id 8326
	//$open_balance = trim(formData("HiddenOpenbalance$CountRow")); 
	
	
	if(strlen($Write_off) == 0)
	{
	$Write_off = 0;
	}
	if(strlen($Co_insurance) == 0)
	{
	$Co_insurance = 0;
	}
	if(strlen($interest_amt) == 0)
	{
	$interest_amt = 0;
	}
	if(strlen($copay) == 0)
	{
	$copay = 0;
	}
	  if (isset($_POST["Payment$CountRow"]) && strlen($_POST["Payment$CountRow"])>0 && $_POST["Payment$CountRow"]*1>=0)
    //  if (isset($_POST["Payment$CountRow"]) && $_POST["Payment$CountRow"]*1>0)
        if (trim(formData('type_name'))=='insurance') {
            if (trim(formData("HiddenIns$CountRow"))==1) {
                $AccountCode="IPP";
            }

            if (trim(formData("HiddenIns$CountRow"))==2) {
                $AccountCode="ISP";
            }

            if (trim(formData("HiddenIns$CountRow"))==3) {
                $AccountCode="ITP";
            }
        } elseif (trim(formData('type_name'))=='patient') {
            $AccountCode="PP";
		  $AdjustString = "Patient Paid: $".formData("Payment$CountRow"   );
        }
	 if($Co_insurance>0)
		 $AdjustString .= " Co-ins $: $Co_insurance";	 
    // Sai custom code end	
        sqlBeginTrans();
        $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array(trim(formData('hidden_patient_code')), trim(formData("HiddenEncounter$CountRow"))));
        sqlStatement("insert into ar_activity set "    .
        "pid = '"       . trim(formData('hidden_patient_code')) .
        "', encounter = '"     . trim(formData("HiddenEncounter$CountRow"))  .
        "', sequence_no = '" . $sequence_no['increment'] .
                "', code_type = '"      . trim(formData("HiddenCodetype$CountRow"))  .
        "', code = '"      . trim(formData("HiddenCode$CountRow"))  .
        "', modifier = '"      . trim(formData("HiddenModifier$CountRow"))  .
        "', payer_type = '"   . trim(formData("HiddenIns$CountRow")) .
        "', post_time = '"  . trim($created_time) .
        "', post_user = '" . trim($user_id)  .
        "', session_id = '"    . trim(formData('payment_id')) .
        "', modified_time = '"  . trim($created_time) .
        "', pay_amount = '" . trim(formData("Payment$CountRow"))  .
        "', adj_amount = '"    . 0 .
	"', memo = '" . "$AdjustString"  .
        "', account_code = '" . "$AccountCode"  .
        "', reason_code = '" . trim(formData("ReasonCode$CountRow"))  .
	    //"', reason_code = '" . "$reason_code"  .
		"', Adj_code = '" . trim(formData("Adj_code$CountRow"   ))  .		
		"', co_ins = '" . "$Co_insurance"  .
		"', interest = '" . "$interest_amt"  .
		"', allowed_amt = '" . "$allowed_amt"  .
		"', receipts = '" . "$receipts"  .
		//"', open_balance = '" . "$open_balance"  .
			//commented by sangram for rollbacking Balance amount column bug id 8326
		"'");// Sai custom code 
	  $Affected='yes';
 // Sai custom code start  
   $resCopay = sqlStatement("SELECT sum(fee) as copay FROM billing where  code_type='COPAY'  and pid= '". trim(formData('hidden_patient_code' )) ."' and encounter='". trim(formData("HiddenEncounter$CountRow"))."' and billing.activity!=0");
						$rowCopay = sqlFetchArray($resCopay);
						$Copay_billing=$rowCopay['copay']*-1;
						
   $qsql = sqlStatement("SELECT sum(pay_amount) as pay_amount FROM ar_activity where pid='". trim(formData('hidden_patient_code' )) ."' and encounter='". trim(formData("HiddenEncounter$CountRow"))."' and code='". trim(formData("HiddenCode$CountRow"))."' and account_code='PCP' ");
	$statusrow = sqlFetchArray($qsql);
	$copay_amount = $statusrow['pay_amount'];
	
	$copay_amount = $Copay_billing + $copay_amount;
	 $post_copay_amount = $_POST["HiddenCopayAmount$CountRow"];
	 
	
    if (isset($_POST["HiddenCopayAmount$CountRow"]) && $_POST["HiddenCopayAmount$CountRow"]*1!=0)
   {
   
   if($copay_amount != $post_copay_amount && $copay_amount==0){
    $AdjustString="Copay : $".formData("HiddenCopayAmount$CountRow" );
    $AccountCode="PCP";
	
	
	if($Co_insurance>0)
		 $AdjustString .= "  ,Co-ins $: $Co_insurance";
	
	idSqlStatement("insert into ar_activity set "    .
		"pid = '"       . trim(formData('hidden_patient_code' )) .
		"', encounter = '"     . trim(formData("HiddenEncounter$CountRow"   ))  .
		"', code = '"      . trim(formData("HiddenCode$CountRow"   ))  .
		"', modifier = '"      . trim(formData("HiddenModifier$CountRow"   ))  .
		"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
		"', post_time = '"  . trim($created_time					) .
		"', post_user = '" . trim($user_id            )  .
		"', session_id = '"    . trim(formData('payment_id')) .
		"', modified_time = '"  . trim($created_time					) .
		"', pay_amount = '" . trim(formData("HiddenCopayAmount$CountRow"   ))  .
		"', memo = '" . "$AdjustString"  .
		"', account_code = '" . "$AccountCode"  .	
	    "', reason_code = '" . trim(formData("ReasonCode$CountRow"))  .	
		"', co_ins = '" . "$Co_insurance"  .
		"', interest = '" . "$interest_amt"  .
        "'");
          sqlCommitTrans();
          $Affected='yes';
    }
	else if($copay_amount>0 && $copay_amount != $post_copay_amount){
	$AdjustString="Copay updated: $".formData("HiddenCopayAmount$CountRow" );
    $AccountCode="PCP";
	sqlStatement("UPDATE ar_activity set pay_amount= '".trim(formData("HiddenCopayAmount$CountRow"))."', modified_time = '". trim($created_time) ."', post_time = '" . trim($created_time) ."', post_user = '" . trim($user_id)."',memo = '"."$AdjustString"  ."' where pid='". trim(formData('hidden_patient_code' ))."' and  encounter = '" . trim(formData("HiddenEncounter$CountRow"))."' and session_id='"    . trim(formData('payment_id')) ."' and code='" . trim(formData("HiddenCode$CountRow" ))  ."' and account_code='PCP'   ");
	}
   }
   // Sai custom code end
    if (isset($_POST["AdjAmount$CountRow"]) && $_POST["AdjAmount$CountRow"]*1!=0) {
        if (trim(formData('type_name'))=='insurance') {
            $AdjustString="Ins adjust Ins".trim(formData("HiddenIns$CountRow"));
            $AccountCode="IA";
        } elseif (trim(formData('type_name'))=='patient') {
            $AdjustString="Pt adjust";
            $AccountCode="PA";
        }

        sqlBeginTrans();
        $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array(trim(formData('hidden_patient_code')), trim(formData("HiddenEncounter$CountRow"))));
        sqlInsert("insert into ar_activity set "    .
        "pid = '"       . trim(formData('hidden_patient_code')) .
        "', encounter = '"     . trim(formData("HiddenEncounter$CountRow"))  .
        "', sequence_no = '"     . $sequence_no['increment']  .
        "', code_type = '"      . trim(formData("HiddenCodetype$CountRow"))  .
        "', code = '"      . trim(formData("HiddenCode$CountRow"))  .
        "', modifier = '"      . trim(formData("HiddenModifier$CountRow"))  .
        "', payer_type = '"   . trim(formData("HiddenIns$CountRow")) .
        "', post_time = '"  . trim($created_time) .
        "', post_user = '" . trim($user_id)  .
        "', session_id = '"    . trim(formData('payment_id')) .
        "', modified_time = '"  . trim($created_time) .
        "', pay_amount = '" . 0  .
        "', adj_amount = '"    . trim(formData("AdjAmount$CountRow")) .
        "', memo = '" . "$AdjustString"  .
        "', account_code = '" . "$AccountCode"  .
	    "', reason_code = '" . "$reason_code"  .
       	"', Adj_code = '" . trim(formData("Adj_code$CountRow"   ))  .			
	    "', interest = '" . "$interest_amt"  .			
	    //"', open_balance = '" . 0  .
	    //commented by sangram for rollbacking Balance amount column bug id 8326
	    "'");// Sai custom code 

        sqlCommitTrans();
        $Affected='yes';
    }

    if (isset($_POST["Deductible$CountRow"]) && $_POST["Deductible$CountRow"]*1>0) {

	   if($Co_insurance>0)
		 $AdjustString = "  ,Co-ins $: $Co_insurance";		 // Sai custom code 

         sqlBeginTrans();
         $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array(trim(formData('hidden_patient_code')), trim(formData("HiddenEncounter$CountRow"))));
         sqlInsert("insert into ar_activity set "    .
        "pid = '"       . trim(formData('hidden_patient_code')) .
        "', encounter = '"     . trim(formData("HiddenEncounter$CountRow"))  .
        "', sequence_no = '"     . $sequence_no['increment']  .
                "', code_type = '"      . trim(formData("HiddenCodetype$CountRow"))  .
        "', code = '"      . trim(formData("HiddenCode$CountRow"))  .
        "', modifier = '"      . trim(formData("HiddenModifier$CountRow"))  .
        "', payer_type = '"   . trim(formData("HiddenIns$CountRow")) .
        "', post_time = '"  . trim($created_time) .
        "', post_user = '" . trim($user_id)  .
        "', session_id = '"    . trim(formData('payment_id')) .
        "', modified_time = '"  . trim($created_time) .
        "', pay_amount = '" . 0  .
        "', adj_amount = '"    . 0 .
        "', memo = '"    . "Deductible $".trim(formData("Deductible$CountRow")) .
        "', account_code = '" . "Deduct"  .
	    "', reason_code = '" . "$reason_code"  .
       	"', Adj_code = '" . trim(formData("Adj_code$CountRow"   ))  .
	"', co_ins = '" . "$Co_insurance"  .
	"', interest = '" . "$interest_amt"  .
	//"', open_balance = '" . 0  .
	//commented by sangram for rollbacking Balance amount column bug id 8326
	"'");// Sai custom code end

           sqlCommitTrans();
          $Affected='yes';
    }
    if (isset($_POST["Takeback$CountRow"]) && $_POST["Takeback$CountRow"]*1>0) {
   // Sai custom code start
   $AdjustString = "Takeback Amount: $".formData("Takeback$CountRow"   )*-1;
    if($Co_insurance>0)
		 $AdjustString .= "  ,Co-ins $: $Co_insurance";		 
		
		  $reason_code="";
   if(($_POST["wo$CountRow"]*1 >0) || ($_POST["wo$CountRow"]*1 <0))
   	$reason_code = "wo";
// Sai custom code end		
         sqlBeginTrans();
         $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array(trim(formData('hidden_patient_code')), trim(formData("HiddenEncounter$CountRow"))));
         sqlInsert("insert into ar_activity set "    .
        "pid = '"       . trim(formData('hidden_patient_code')) .
        "', encounter = '"     . trim(formData("HiddenEncounter$CountRow"))  .
        "', sequence_no = '"     . $sequence_no['increment']  .
        "', code_type = '"      . trim(formData("HiddenCodetype$CountRow"))  .
        "', code = '"      . trim(formData("HiddenCode$CountRow"))  .
        "', modifier = '"      . trim(formData("HiddenModifier$CountRow"))  .
        "', payer_type = '"   . trim(formData("HiddenIns$CountRow")) .
        "', post_time = '"  . trim($created_time) .
        "', post_user = '" . trim($user_id)  .
        "', session_id = '"    . trim(formData('payment_id')) .
        "', modified_time = '"  . trim($created_time) .
        "', pay_amount = '" . trim(formData("Takeback$CountRow"))*-1  .
	    "', memo = '"    . $AdjustString.
        "', adj_amount = '"    . 0 .
        "', account_code = '" . "Takeback"  .
       	"', Adj_code = '" . trim(formData("Adj_code$CountRow"   ))  .
	    "', w_o = '" . "$Write_off"  .
	    "', co_ins = '" . "$Co_insurance"  .
	    "', interest = '" . "$interest_amt"  .
	    "', reason_code = '" . "$reason_code"  .	
	    //"', open_balance = '" . 0  .
	    //commented by sangram for rollbacking Balance amount column bug id 8326
	    "'");// Sai custom code 

           sqlCommitTrans();
          $Affected='yes';
    }

    if (isset($_POST["FollowUp$CountRow"]) && $_POST["FollowUp$CountRow"]=='y') {
         sqlBeginTrans();
         $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array(trim(formData('hidden_patient_code')), trim(formData("HiddenEncounter$CountRow"))));
         sqlInsert("insert into ar_activity set "    .
        "pid = '"       . trim(formData('hidden_patient_code')) .
        "', encounter = '"     . trim(formData("HiddenEncounter$CountRow"))  .
        "', sequence_no = '"     . $sequence_no['increment']  .
        "', code_type = '"      . trim(formData("HiddenCodetype$CountRow"))  .
        "', code = '"      . trim(formData("HiddenCode$CountRow"))  .
        "', modifier = '"      . trim(formData("HiddenModifier$CountRow"))  .
        "', payer_type = '"   . trim(formData("HiddenIns$CountRow")) .
        "', post_time = '"  . trim($created_time) .
        "', post_user = '" . trim($user_id)  .
        "', session_id = '"    . trim(formData('payment_id')) .
        "', modified_time = '"  . trim($created_time) .
        "', pay_amount = '" . 0  .
        "', adj_amount = '"    . 0 .
        "', follow_up = '"    . "y" .
        "', follow_up_note = '"    . trim(formData("FollowUpReason$CountRow")) .
	"'");
	sqlCommitTrans();
	$Affected='yes';
    }
   // Sai custom code start
   //if(($_POST["Payment$CountRow"]*1==0 || (!($_POST["Payment$CountRow"]*1))) && ($_POST["wo$CountRow"]*1 >0 || $_POST["Allowed$CountRow"]*1 >0) )
   if(($_POST["Payment$CountRow"]!='' && $_POST["Payment$CountRow"]*1!=0) && (isset($_POST["wo$CountRow"]) || $_POST["wo$CountRow"]*1 >0)  || $_POST["Allowed$CountRow"]*1 >0 )
   {
  // echo "<pre>"; print_r($_POST); echo "</pre>";
   $AdjustString=" W/O Adjusted ";   
//   if($Co_insurance)
//   $AdjustString .= "  ,Co-ins $: $Co_insurance";
//   $reason_code="";
  
   if((($_POST["wo$CountRow"]*1 >0) || ($_POST["wo$CountRow"]*1 <0)) && $_POST["wo$CountRow"]!='' ){
   	$reason_code = "wo";
   
   		idSqlStatement("insert into ar_activity set "    .
		"pid = '"       . trim(formData('hidden_patient_code' )) .
		"', encounter = '"     . trim(formData("HiddenEncounter$CountRow"   ))  .
		"', code = '"      . trim(formData("HiddenCode$CountRow"   ))  .
		"', modifier = '"      . trim(formData("HiddenModifier$CountRow"   ))  .
		"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
		"', post_time = '"  . trim($created_time					) .
		"', post_user = '" . trim($user_id            )  .
		"', session_id = '"    . trim(formData('payment_id')) .
		"', modified_time = '"  . trim($created_time					) .
		"', pay_amount = '" . 0  .
		"', memo = '"    . $AdjustString.				
		"', w_o = '" . "$Write_off"  .
		"', interest = '" . "$interest_amt"  .
		"', allowed_amt = '" . "$allowed_amt"  .
		"', reason_code = '" . "$reason_code"  .		
		"'");
	  $Affected='yes';	
	  }
   
   }
   //Gangeya to update BillingNote.
    if ($Affected=='yes') {
  		// code change for BUG 10899
		// code added for adding notes in billing_notes table
		$sql_get_id = "select id from form_encounter where encounter=".trim(formData("HiddenEncounter$CountRow" ));
		//echo $sql_get_id;
		$feidrow = sqlQuery($sql_get_id);
		
		$feid=$feidrow['id'];
		$fenote = trim(formData("BillNote$CountRow"   ));
	   
		$sql="INSERT into billing_notes(encounter,notes,user_id,username,created_date,last_updated,last_user) 				values($feid,'$fenote',$user_id,'$modifier','$created_time','$created_time','$modifier')" ;
			sqlStatement($sql);

  
		sqlStatement("update form_encounter set 
		billing_note= concat('[',DATE_FORMAT(NOW(),'%m/%d/%Y %h:%m:%s'),']:','[',
		(select concat(fname,' ',lname )from users where id = '".trim($user_id) ."'),']:','".trim(formData("BillNote$CountRow"   ))."') where pid ='".trim(formData('hidden_patient_code' ))."' and encounter='".trim(formData("HiddenEncounter$CountRow" ))."'");
   }
  // Sai custom code end 
  if($Affected=='yes')
   {
	//Changes made by Gangeya for PAYEHR-288 manual auto flipping 
	if(trim(formData('type_name'))!='patient' && (trim(formData("Payment$CountRow")) > 0 || $Co_insurance > 0|| trim(formData("Deductible$CountRow")) > 0 || $Write_off > 0 || $Write_off != null))// Sai custom code 
	 {
            $ferow = sqlQuery("select last_level_closed from form_encounter  where
		pid ='".trim(formData('hidden_patient_code'))."' and encounter='".trim(formData("HiddenEncounter$CountRow"))."'");
              //multiple charges can come.
            if ($ferow['last_level_closed']<trim(formData("HiddenIns$CountRow"))) {
                  sqlStatement("update form_encounter set last_level_closed='".trim(formData("HiddenIns$CountRow"))."' where
			pid ='".trim(formData('hidden_patient_code'))."' and encounter='".trim(formData("HiddenEncounter$CountRow"))."'");
                  //last_level_closed gets increased.
                  //-----------------------------------
                  // Determine the next insurance level to be billed.
                  $ferow = sqlQuery("SELECT date, last_level_closed " .
                    "FROM form_encounter WHERE " .
                    "pid = '".trim(formData('hidden_patient_code'))."' AND encounter = '".trim(formData("HiddenEncounter$CountRow"))."'");
                  $date_of_service = substr($ferow['date'], 0, 10);
                  $new_payer_type = 0 + $ferow['last_level_closed'];
                if ($new_payer_type <= 3 && !empty($ferow['last_level_closed']) || $new_payer_type == 0) {
                    ++$new_payer_type;
                }

                  $new_payer_id = arGetPayerID(trim(formData('hidden_patient_code')), $date_of_service, $new_payer_type);
                if ($new_payer_id>0) {
                        arSetupSecondary(trim(formData('hidden_patient_code')), trim(formData("HiddenEncounter$CountRow")), 0);
                }

                    //-----------------------------------
            }
        }
    }
// Sai custom code start   
     //updated by sangram - function for updating status automatically
   
   $encounter= trim(formData("HiddenEncounter$CountRow"   ));

  
  $modifier=$_SESSION{"authUser"};
  
  $BillingNote = trim(formData("BillNote$CountRow"    ));

$row = sqlFetchArray(sqlStatement("select pid from form_encounter where encounter='$encounter'"));
$patient_id = $row['pid'];
$row = sqlFetchArray(sqlStatement("select sum(fee) as fees from billing where encounter='$encounter'"));
$fees = $row['fees'];
$row = sqlFetchArray(sqlStatement("select sum(pay_amount) as pay from ar_activity where encounter='$encounter'"));
$pay = $row['pay'];
$row= sqlFetchArray(sqlStatement("select payer_type from ar_activity where encounter='$encounter' and post_time = (select max(post_time) from ar_activity where encounter='$encounter')"));
 $payer_type= $row['payer_type'];

if($payer_type == 1)
{
$row= sqlFetchArray(sqlStatement("select provider from insurance_data where pid=".$patient_id." and type='secondary'"));
$provider= $row['provider'];

}
if($payer_type == 2)
{
$row= sqlFetchArray(sqlStatement("select provider from insurance_data where pid=".$patient_id." and type='tertiary'"));
 $provider= $row['provider'];

}
if($payer_type == 3)
{
$provider = 0;
}


if($pay == $fees)
{
$status="Claim settled";

sqlStatement( "INSERT INTO `encounter_status`(`Encounter`,`Status`,`Status_Date`,`modifier`)VALUES(
'$encounter','$status',NOW(),'$modifier')" );

$qsql = sqlStatement("SELECT id, status,iphone_status FROM claim_status where status='$status'");
	$statusrow = sqlFetchArray($qsql);
	$claim_status_id = $statusrow['id'];
	
	// code update for last modified date by pawan on 24-03-2017
	$modified_by= $_SESSION{'authUserID'};
	$my_today_date = date('Y-m-d H:i:s');
	if($claim_status_id){
	
		sqlQuery("Update form_encounter set claim_status_id=$claim_status_id,modified_date='$my_today_date',modified_by=$modified_by where encounter='$encounter' ");
	}
    }
}
// Sai custom code end   

//===============================================================================
  // Delete rows, with logging, for the specified table using the
  // specified WHERE clause.  Borrowed from deleter.php.
  //
function row_delete($table, $where)
{
    $tres = sqlStatement("SELECT * FROM $table WHERE $where");
    $count = 0;
    while ($trow = sqlFetchArray($tres)) {
        $logstring = "";
        foreach ($trow as $key => $value) {
            if (! $value || $value == '0000-00-00 00:00:00') {
                continue;
            }

            if ($logstring) {
                $logstring .= " ";
            }

            $logstring .= $key . "='" . addslashes($value) . "'";
        }

        newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$table: $logstring");
        ++$count;
    }

    if ($count) {
        $query = "DELETE FROM $table WHERE $where";
        sqlStatement($query);
    }
}
//===============================================================================
