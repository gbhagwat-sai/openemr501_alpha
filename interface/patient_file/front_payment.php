<?php
/**
 * Front payment gui.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/payment.inc.php"); // Sai custom code
require_once("$srcdir/forms.inc");
require_once("$srcdir/sl_eob.inc.php");
require_once("$srcdir/invoice_summary.inc.php");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/formatting.inc.php"); // sai custom code
require_once("$srcdir/options.inc.php");
require_once("$srcdir/encounter_events.inc.php");



use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$pid = $_REQUEST['hidden_patient_code'] > 0 ? $_REQUEST['hidden_patient_code'] : $pid;

$facilityService = new FacilityService();

?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['opener']); ?>
    <?php
// sai custom code start
//add notes
function updateBillingNote($billing_note,$form_pid,$enc){
$user_id=$_SESSION['authUserID'];
sqlStatement("update form_encounter set billing_note= concat('[',DATE_FORMAT(NOW(),'%m/%d/%Y %h:%m:%s'),']:','[', (select concat(fname,' ',lname ) from users where id = ?),']:',?) where encounter=? and pid=? ",
							array($user_id,$billing_note,$enc,$form_pid));	
							
							
												
}
// sai custom code end
    // Format dollars for display.
    //
    function bucks($amount)
    {
        if ($amount) {
            $amount = oeFormatMoney($amount);
            return $amount;
        }

        return '';
    }

    function rawbucks($amount)
    {
        if ($amount) {
            $amount = sprintf("%.2f", $amount);
            return $amount;
        }

        return '';
    }

    // Display a row of data for an encounter.
    //
    $var_index = 0;
// Sai custom code start
function echoLine($iname, $date, $charges, $ptpaid, $inspaid,$w_o, $payment_method, $reference,  $duept,$encounter=0,$copay=0,$patcopay=0,$billing_note,$interest,$tot_adjustment,$takeback,$deposit_date,$encounter_status) {
  global $var_index;
  $var_index++;
  	//w_o added by sangram for copay issue on 18_june_2014
  //$balance = bucks($charges - ($ptpaid + $inspaid+$w_o-$patcopay));
  $balance = bucks($charges - ($ptpaid + $inspaid + $w_o + $tot_adjustment + $patcopay));
  $balance = (round($duept,2) != 0) ? 0 : $balance;//if balance is due from patient, then insurance balance is displayed as zero
  $encounter = $encounter ? $encounter : '';
 // $ptpaid = bucks($ptpaid)-bucks($patcopay);
  $ptpaid = bucks($ptpaid);
  echo " <tr id='tr_".attr($var_index)."' >\n";
  echo "  <td class='detail'>" . text(oeFormatShortDate($date)) . "</td>\n";
  echo "  <td class='detail' align='center'>" . htmlspecialchars($encounter, ENT_QUOTES) . "</td>\n";
  echo "  <td class='detail' align='left'>" . $encounter_status . "</td>\n";
  echo "  <td class='detail' align='center' id='td_charges_$var_index' >" . htmlspecialchars(bucks($charges), ENT_QUOTES) . "</td>\n";
  echo "  <td class='detail' align='center' id='td_copay_$var_index' >" . htmlspecialchars(bucks($patcopay), ENT_QUOTES) . "</td>\n";
  echo "  <td class='detail' align='center' id='td_inspaid_$var_index' >" . htmlspecialchars(bucks($inspaid), ENT_QUOTES) . "</td>\n";
  
  echo "  <td class='detail' align='center' id='td_ptpaid_$var_index' >" . htmlspecialchars(bucks($ptpaid), ENT_QUOTES) . "</td>\n";
    echo "  <td class='detail' align='center' id='td_wo_$var_index' >" . htmlspecialchars(bucks($w_o), ENT_QUOTES) . "</td>\n";
    echo "  <td class='detail' align='center' id='td_interest_$var_index' >" . htmlspecialchars(bucks($interest), ENT_QUOTES) . "</td>\n";
    
    $receipts = $patcopay + $inspaid + $ptpaid; 
     echo "  <td class='detail' align='center' id='td_interest_$var_index' >" . htmlspecialchars(bucks($receipts), ENT_QUOTES) . "</td>\n";
     $adjustment_amt1 = $tot_adjustment +$adjustment_amt;
      echo "  <td class='detail' align='center' id='td_interest_$var_index' >" . htmlspecialchars(bucks($adjustment_amt1), ENT_QUOTES) . "</td>\n";
       echo "  <td class='detail' align='center' id='td_interest_$var_index' >" . htmlspecialchars(bucks($takeback), ENT_QUOTES) . "</td>\n";
  
  

   
	 
echo "  <td class='detail' align='center' id='td_ptmethod_$var_index' >" . htmlspecialchars($payment_method, ENT_QUOTES) . "</td>\n";
echo "  <td class='detail' align='center' id='td_ptmethod_$var_index' >" . htmlspecialchars($reference, ENT_QUOTES) . "</td>\n";
echo "  <td class='detail' align='center' id='td_ptmethod_$var_index' >" .
htmlspecialchars($deposit_date, ENT_QUOTES) . "</td>\n";

//echo "  <td class='detail' align='center' id='td_copay_$var_index' >" . htmlspecialchars(bucks($patcopay), ENT_QUOTES) . "</td>\n";
 // echo "  <td class='detail' align='center' id='td_inspaid_$var_index' >" . htmlspecialchars(bucks($inspaid), ENT_QUOTES) . "</td>\n";
  
 /*  echo "  <td class='detail' align='center' id='td_patient_copay_$var_index' >" . htmlspecialchars(bucks($patcopay), ENT_QUOTES) . "</td>\n";
 echo "  <td class='detail' align='center' id='td_copay_$var_index' >" . htmlspecialchars(bucks($copay), ENT_QUOTES) . "</td>\n";*/
  
echo "  <td class='detail' align='center' id='balance_$var_index'>" . htmlspecialchars(bucks($balance), ENT_QUOTES) . "</td>\n";
  echo "  <td class='detail' align='center' id='duept_$var_index'>" . htmlspecialchars(bucks(round($duept,2)*1), ENT_QUOTES) . "</td>\n";
    
  if($duept)
  $duetot =   bucks($balance) + bucks(round($duept,2)*1);
  else
    $duetot =    bucks($balance) + bucks(round($duept,2)*1);
	
	$duetot_final = htmlspecialchars(bucks(round($duetot,2)*1), ENT_QUOTES);
	if($duetot_final=="")
	$duetot_final="0.00";
  
   echo "  <td class='detail' align='center' id='duetot_$var_index'>" . $duetot_final . "</td>\n";
 /* echo "  <td class='detail' align='right' ><input type='text' name='".attr($iname)."'  id='paying_".attr($var_index)."' " .
    " value='" .  '' . "' onchange='coloring();calctotal()'  autocomplete='off' " .
    "onkeyup='calctotal()'  style='width:50px' readonly/></td>\n";
	 echo "  <td class='detail' align='right' ><input type='text' name='notes[".$encounter."]'  id='notes[".$encounter."]' " .
    " value='" . $billing_note . "'  autocomplete='off' " .
    "style='width:300px' readonly/></td>\n";*/
  echo " </tr>\n";
  // Sai custom code end
    }

    // We use this to put dashes, colons, etc. back into a timestamp.
    //
    function decorateString($fmt, $str)
    {
        $res = '';
        while ($fmt) {
            $fc = substr($fmt, 0, 1);
            $fmt = substr($fmt, 1);
            if ($fc == '.') {
                $res .= substr($str, 0, 1);
                $str = substr($str, 1);
            } else {
                $res .= $fc;
            }
        }

        return $res;
    }

    // Compute taxes from a tax rate string and a possibly taxable amount.
    //
    function calcTaxes($row, $amount)
    {
        $total = 0;
        if (empty($row['taxrates'])) {
            return $total;
        }

        $arates = explode(':', $row['taxrates']);
        if (empty($arates)) {
            return $total;
        }

        foreach ($arates as $value) {
            if (empty($value)) {
                continue;
            }

            $trow = sqlQuery("SELECT option_value FROM list_options WHERE " .
                "list_id = 'taxrate' AND option_id = ? AND activity = 1 LIMIT 1", array($value));
            if (empty($trow['option_value'])) {
                echo "<!-- Missing tax rate '" . text($value) . "'! -->\n";
                continue;
            }

            $tax = sprintf("%01.2f", $amount * $trow['option_value']);
            // echo "<!-- Rate = '$value', amount = '$amount', tax = '$tax' -->\n";
            $total += $tax;
        }

        return $total;
    }

    $now = time();
    $today = date('Y-m-d', $now);
    $timestamp = date('Y-m-d H:i:s', $now);


    // $patdata = getPatientData($pid, 'fname,lname,pubpid');

    $patdata = sqlQuery("SELECT " .
        "p.fname, p.mname, p.lname, p.pubpid,p.pid, i.copay " .
        "FROM patient_data AS p " .
        "LEFT OUTER JOIN insurance_data AS i ON " .
        "i.pid = p.pid AND i.type = 'primary' " .
        "WHERE p.pid = ? ORDER BY i.date DESC LIMIT 1", array($pid));

    $alertmsg = ''; // anything here pops up in an alert box

    // If the Save button was clicked...
    if ($_POST['form_save']) {
        $form_pid = $_POST['form_pid'];
        $form_method = trim($_POST['form_method']);
        $form_source = trim($_POST['form_source']);
        $patdata = getPatientData($form_pid, 'fname,mname,lname,pubpid');
        $NameNew = $patdata['fname'] . " " . $patdata['lname'] . " " . $patdata['mname'];

        if ($_REQUEST['radio_type_of_payment'] == 'pre_payment') {
	 // sai custom ce start	
	 if($_REQUEST['deposit_date'])
				  $deposit_date = date('Y-m-d',strtotime($_REQUEST['deposit_date']));
				  else
				  $deposit_date=date('Y-m-d');
	 // Sai custom code end
            $payment_id = idSqlStatement(
                "insert into ar_session set " .
                "payer_id = ?" .
                ", patient_id = ?" .
                ", user_id = ?" .
                ", closed = ?" .
                ", reference = ?" .
               	", check_date =  now() , deposit_date = ? "	.
                ",  pay_total = ?" .
                ", payment_type = 'patient'" .
                ", description = ?" .
                ", adjustment_code = 'pre_payment'" .
                ", post_to_date = now() " .
                ", payment_method = ?",
               array(0, $form_pid, $_SESSION['authUserID'], 0, $form_source,$deposit_date, $_REQUEST['form_prepayment'], $NameNew, $form_method));
            // Sai custom code start

            frontPayment($form_pid, 0, $form_method, $form_source, $_REQUEST['form_prepayment'], 0, $timestamp);//insertion to 'payments' table.
        }

        if ($_POST['form_upay'] && $_REQUEST['radio_type_of_payment'] != 'pre_payment') {
            foreach ($_POST['form_upay'] as $enc => $payment) {
	// Sai custom code start
	$final_note = $_POST['notes'][$enc];	
	if($_REQUEST['deposit_date'])
				  $deposit_date = date('Y-m-d',strtotime($_REQUEST['deposit_date']));
				  else
				  $deposit_date=date('Y-m-d');
	// Sai custom code end			 
                if ($amount = 0 + $payment) {
                    $zero_enc = $enc;
                    if ($_REQUEST['radio_type_of_payment'] == 'invoice_balance') {
                        if (!$enc) {
                            $enc = calendar_arrived($form_pid);
                        }
                    } else {
                        if (!$enc) {
                            $enc = calendar_arrived($form_pid);
                        }
                    }

                    //----------------------------------------------------------------------------------------------------
                    //Fetching the existing code and modifier
                    $ResultSearchNew = sqlStatement(
                        "SELECT * FROM billing LEFT JOIN code_types ON billing.code_type=code_types.ct_key " .
                        "WHERE code_types.ct_fee=1 AND billing.activity!=0 AND billing.pid =? AND encounter=? ORDER BY billing.code,billing.modifier",
                        array($form_pid, $enc)
                    );
                    if ($RowSearch = sqlFetchArray($ResultSearchNew)) {
                        $Codetype = $RowSearch['code_type'];
                        $Code = $RowSearch['code'];
                        $Modifier = $RowSearch['modifier'];
                    } else {
                        $Codetype = '';
                        $Code = '';
                        $Modifier = '';
                    }

                    //----------------------------------------------------------------------------------------------------
                    if ($_REQUEST['radio_type_of_payment'] == 'copay') {//copay saving to ar_session and ar_activity tables
                        $session_id = sqlInsert(
                            "INSERT INTO ar_session (payer_id,user_id,reference,check_date,deposit_date,pay_total," .
                            " global_amount,payment_type,description,patient_id,payment_method,adjustment_code,post_to_date) " .
                            " VALUES ('0',?,?,now(),now(),?,'','patient','COPAY',?,?,'patient_payment',now())",
                            array($_SESSION['authId'], $form_source,$deposit_date,$amount, $form_pid, $form_method)// Sai custom code 
                        );
			$memo = "Copay Adjust: $".$amount;// Sai custom code 
                        sqlBeginTrans();
                        $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array($form_pid, $enc));
                        $insrt_id = sqlInsert(
                            "INSERT INTO ar_activity (pid,encounter,sequence_no,code_type,code,modifier,payer_type,post_time,post_user,session_id,pay_amount,account_code,memo)" .
                            " VALUES (?,?,?,?,?,?,0,now(),?,?,?,'PCP')",
                            array($form_pid, $enc, $sequence_no['increment'], $Codetype, $Code, $Modifier, $_SESSION['authId'], $session_id, $amount,$memo) //sai custom code
                        );
                        sqlCommitTrans();

                        frontPayment($form_pid, $enc, $form_method, $form_source, $amount, 0, $timestamp);//insertion to 'payments' table.
                    }

                    if ($_REQUEST['radio_type_of_payment'] == 'invoice_balance' || $_REQUEST['radio_type_of_payment'] == 'cash') {                //Payment by patient after insurance paid, cash patients similar to do not bill insurance in feesheet.
                        if ($_REQUEST['radio_type_of_payment'] == 'cash') {
                            sqlStatement(
                                "update form_encounter set last_level_closed=? where encounter=? and pid=? ",
                                array(4, $enc, $form_pid)
                            );
                            sqlStatement(
                                "update billing set billed=? where encounter=? and pid=?",
                                array(1, $enc, $form_pid)
                            );
                        }

                        $adjustment_code = 'patient_payment';
			// Sai custom code start
			if($_REQUEST['deposit_date'])
				$deposit_date = date('Y-m-d',strtotime($_REQUEST['deposit_date']));
			else
				$deposit_date=date('Y-m-d');
			// Sai custom code end
			
                        $payment_id = idSqlStatement(
                            "insert into ar_session set " .
                            "payer_id = ?" .
                            ", patient_id = ?" .
                            ", user_id = ?" .
                            ", closed = ?" .
                            ", reference = ?" .
                            ", check_date =  now() , deposit_date = now() " .
                            ",  pay_total = ?" .
                            ", payment_type = 'patient'" .
                            ", description = ?" .
                            ", adjustment_code = ?" .
                            ", post_to_date = now() " .
                            ", payment_method = ?",
                            array(0, $form_pid, $_SESSION['authUserID'], 0, $form_source,$deposit_date,$amount, $NameNew, $adjustment_code, $form_method)
                        );// Sai custom code 

                        //--------------------------------------------------------------------------------------------------------------------

                        frontPayment($form_pid, $enc, $form_method, $form_source, 0, $amount, $timestamp);//insertion to 'payments' table.

                        //--------------------------------------------------------------------------------------------------------------------

                        $resMoneyGot = sqlStatement(
                            "SELECT sum(pay_amount) as PatientPay FROM ar_activity where pid =? and " .
                            "encounter =? and payer_type=0 and account_code='PCP'",
                            array($form_pid, $enc)
                        );//new fees screen copay gives account_code='PCP'
                        $rowMoneyGot = sqlFetchArray($resMoneyGot);
                        $Copay = $rowMoneyGot['PatientPay'];

                        //--------------------------------------------------------------------------------------------------------------------

                        //Looping the existing code and modifier
                        $ResultSearchNew = sqlStatement(
                            "SELECT * FROM billing LEFT JOIN code_types ON billing.code_type=code_types.ct_key WHERE code_types.ct_fee=1 " .
                            "AND billing.activity!=0 AND billing.pid =? AND encounter=? ORDER BY billing.code,billing.modifier",
                            array($form_pid, $enc)
                        );
                        while ($RowSearch = sqlFetchArray($ResultSearchNew)) {
                            $Codetype = $RowSearch['code_type'];
                            $Code = $RowSearch['code'];
                            $Modifier = $RowSearch['modifier'];
                            $Fee = $RowSearch['fee'];

                            $resMoneyGot = sqlStatement(
                                "SELECT sum(pay_amount) as MoneyGot FROM ar_activity where pid =? " .
                                "and code_type=? and code=? and modifier=? and encounter =? and !(payer_type=0 and account_code='PCP')",
                                array($form_pid, $Codetype, $Code, $Modifier, $enc)
                            );
                            //new fees screen copay gives account_code='PCP'
                            $rowMoneyGot = sqlFetchArray($resMoneyGot);
                            $MoneyGot = $rowMoneyGot['MoneyGot'];

                            $resMoneyAdjusted = sqlStatement(
                                "SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where " .
                                "pid =? and code_type=? and code=? and modifier=? and encounter =?",
                                array($form_pid, $Codetype, $Code, $Modifier, $enc)
                            );
                            $rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
                            $MoneyAdjusted = $rowMoneyAdjusted['MoneyAdjusted'];

                            $Remainder = $Fee - $Copay - $MoneyGot - $MoneyAdjusted;
                            $Copay = 0;
                            if (round($Remainder, 2) != 0 && $amount != 0) {
                                if ($amount - $Remainder >= 0) {
                                    $insert_value = $Remainder;
                                    $amount = $amount - $Remainder;
                                } else {
                                    $insert_value = $amount;
                                    $amount = 0;
                                }
				$memo = "Patient Paid: $".$insert_value;// Sai custom code 
                                sqlBeginTrans();
                                $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array($form_pid, $enc));
                                sqlStatement(
                                    "insert into ar_activity set " .
                                    "pid = ?" .
                                    ", encounter = ?" .
                                    ", sequence_no = ?" .
                                    ", code_type = ?" .
                                    ", code = ?" .
                                    ", modifier = ?" .
                                    ", payer_type = ?" .
                                    ", post_time = now() " .
                                    ", post_user = ?" .
                                    ", session_id = ?" .
                                    ", pay_amount = ?" .
                                    ", adj_amount = ?" .
				    ", memo = ?"    .
                                    ", account_code = 'PP'",
                                    array($form_pid, $enc, $sequence_no['increment'], $Codetype, $Code, $Modifier, 0, $_SESSION['authUserID'], $payment_id, $insert_value, 0,$memo)// Sai custom code start
                                );
                                sqlCommitTrans();
                            }//if
                        }//while
                        if ($amount != 0) {//if any excess is there.
			 $memo = "Patient Paid: $".$amount; // Sai custom code 
                            sqlBeginTrans();
                            $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array($form_pid, $enc));
                            sqlStatement(
                                "insert into ar_activity set " .
                                "pid = ?" .
                                ", encounter = ?" .
                                ", sequence_no = ?" .
                                ", code_type = ?" .
                                ", code = ?" .
                                ", modifier = ?" .
                                ", payer_type = ?" .
                                ", post_time = now() " .
                                ", post_user = ?" .
                                ", session_id = ?" .
                                ", pay_amount = ?" .
                                ", adj_amount = ?" .
				", memo = ?"    .
                                ", account_code = 'PP'",
                                array($form_pid, $enc, $sequence_no['increment'], $Codetype, $Code, $Modifier, 0, $_SESSION['authUserID'], $payment_id, $amount, 0,$memo)
                            );// Sai custom code
                            sqlCommitTrans();
                        }

                        //--------------------------------------------------------------------------------------------------------------------
                    }//invoice_balance
			updateBillingNote($final_note,$form_pid,$enc);// Sai custom code 
                }//if ($amount = 0 + $payment)
            }//foreach
        }//if ($_POST['form_upay'])
    }//if ($_POST['form_save'])

    if ($_POST['form_save'] || $_REQUEST['receipt']) {
        if ($_REQUEST['receipt']) {
            $form_pid = $_GET['patient'];
            $timestamp = decorateString('....-..-.. ..:..:..', $_GET['time']);
        }

    // Get details for what we guess is the primary facility.
        $frow = $facilityService->getPrimaryBusinessEntity(array("useLegacyImplementation" => true));

    // Get the patient's name and chart number.
        $patdata = getPatientData($form_pid, 'fname,mname,lname,pubpid');

    // Re-fetch payment info.
        $payrow = sqlQuery("SELECT " .
        "SUM(amount1) AS amount1, " .
        "SUM(amount2) AS amount2, " .
        "MAX(method) AS method, " .
        "MAX(source) AS source, " .
        "MAX(dtime) AS dtime, " .
        // "MAX(user) AS user " .
        "MAX(user) AS user, " .
        "MAX(encounter) as encounter " .
        "FROM payments WHERE " .
        "pid = ? AND dtime = ?", array($form_pid, $timestamp));

    // Create key for deleting, just in case.
        $ref_id = ($_REQUEST['radio_type_of_payment'] == 'copay') ? $session_id : $payment_id;
        $payment_key = $form_pid . '.' . preg_replace('/[^0-9]/', '', $timestamp) . '.' . $ref_id;

        if ($_REQUEST['radio_type_of_payment'] != 'pre_payment') {
            // get facility from encounter
            $tmprow = sqlQuery("SELECT `facility_id` FROM `form_encounter` WHERE `encounter` = ?", array($payrow['encounter']));
            $frow = $facilityService->getById($tmprow['facility_id']);
        } else {
            // if pre_payment, then no encounter yet, so get main office address
            $frow = $facilityService->getPrimaryBillingLocation();
        }

    // Now proceed with printing the receipt.
    ?>

    <title><?php echo xlt('Receipt for Payment'); ?></title>

    <?php Header::setupHeader(['jquery-ui']); ?>

    <script language="JavaScript">

        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

        $(document).ready(function () {
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));
        });

        function closeHow(e) {
            if (top.tab_mode) {
                top.activateTabByName('pat', true);
                top.tabCloseByName(window.name);
            } else {
                if (opener) {
                    if (opener.name === "left_nav") {
                        dlgclose();
                        return;
                    }
                }
                window.history.back();
            }
        }

        // This is action to take before printing and is called from restoreSession.php.
        function printlog_before_print() {
            let divstyle = document.getElementById('hideonprint').style;
            divstyle.display = 'none';
            // currently exit is not hidden by default in case receipt print is not needed
            // and left here for future option to force users to print via global etc..
            // can still print later via reports.
            divstyle = document.getElementById('showonprint').style;
            divstyle.display = '';
        }

        // Process click on Delete button.
        function deleteme() {
            dlgopen('deleter.php?payment=<?php echo attr($payment_key); ?>', '_blank', 500, 450);
            return false;
        }

        // Called by the deleteme.php window on a successful delete.
        function imdeleted() {
            if (opener) {
                dlgclose(); // we're in reports/leftnav and callback reloads.
            } else {
                window.history.back(); // this is us full screen.
            }
 }
 function toaddcopay(){
 window.location.assign('front_payment.php');
 }// Sai custom code 

        // Called to switch to the specified encounter having the specified DOS.
        // This also closes the popup window.
        function toencounter(enc, datestr, topframe) {
            topframe.restoreSession();
            // Hard-coding of RBot for this purpose is awkward, but since this is a
            // pop-up and our openemr is left_nav, we have no good clue as to whether
            // the top frame is more appropriate.
            if(!top.tab_mode) {
                topframe.left_nav.forceDual();
                topframe.left_nav.setEncounter(datestr, enc, '');
  topframe.left_nav.setRadio('RTop', 'enc');// Sai custom code 
  topframe.left_nav.loadFrame('enc2', 'RTop', 'patient_file/encounter/encounter_top.php?set_encounter=' + enc);// Sai custom code 
            } else {
                top.goToEncounter(enc);
            }
            if (opener) dlgclose();
        }

    </script>
</head>
<body bgcolor='#ffffff'>
<center>

    <p>
    <h2><?php echo xlt('Receipt for Payment'); ?></h2>

    <p><?php echo text($frow['name']) ?>
        <br><?php echo text($frow['street']) ?>
        <br><?php echo text($frow['city'] . ', ' . $frow['state']) . ' ' .
            text($frow['postal_code']) ?>
        <br><?php echo text($frow['phone']) ?>

    <p>
    <table border='0' cellspacing='8'>
        <tr>
            <td><?php echo xlt('Date'); ?>:</td>
            <td><?php echo text(oeFormatSDFT(strtotime($payrow['dtime']))) ?></td>
        </tr>
        <tr>
            <td><?php echo xlt('Patient'); ?>:</td>
            <td><?php echo text($patdata['fname']) . " " . text($patdata['mname']) . " " .
                    text($patdata['lname']) . " (" . text($patdata['pubpid']) . ")" ?></td>
        </tr>
        <tr>
            <td><?php echo xlt('Paid Via'); ?>:</td>
            <td><?php echo generate_display_field(array('data_type' => '1', 'list_id' => 'payment_method'), $payrow['method']); ?></td>
        </tr>
        <tr>
            <td><?php echo xlt('Check/Ref Number'); ?>:</td>
            <td><?php echo text($payrow['source']) ?></td>
        </tr>
        <tr>
            <td><?php echo xlt('Amount for This Visit'); ?>:</td>
            <td><?php echo text(oeFormatMoney($payrow['amount1'])) ?></td>
        </tr>
        <tr>
            <td>
                <?php
                if ($_REQUEST['radio_type_of_payment'] == 'pre_payment') {
                    echo xlt('Pre-payment Amount');
                } else {
                    echo xlt('Amount for Past Balance');
                }
                ?>
                :
            </td>
            <td><?php echo text(oeFormatMoney($payrow['amount2'])) ?></td>
        </tr>
        <tr>
            <td><?php echo xlt('Received By'); ?>:</td>
            <td><?php echo text($payrow['user']) ?></td>
        </tr>
    </table>

    <div id='hideonprint'>
        <p>
            <input type='button' value='<?php echo xla('Print'); ?>' id='printbutton'/>

            <?php
            $todaysenc = todaysEncounterIf($pid);
            if ($todaysenc && $todaysenc != $encounter) {
  // Sai custom code start
    /*echo "&nbsp;<input type='button' " .
      "value='" . xla('Open Today`s Visit') . "' " .
      "onclick='toencounter($todaysenc,\"$today\",opener.top)' />\n"; */
    // Sai custom code end
            }
            ?>

            <?php if (acl_check('admin', 'super')) { ?>
                <input type='button' value='<?php echo xla('Delete'); ?>' style='color:red' onclick='deleteme()'/>
            <?php } ?>
    <input type='button' value='<?php xl('Back','e'); ?>' onclick='toaddcopay()' /><!-- Sai custom code  -->
    </div>
    <div id='showonprint'>
        <input type='button' value='<?php echo xla('Exit'); ?>' id='donebutton' onclick="closeHow(event)"/>
    </div>
</center>
</body>

<?php
//
// End of receipt printing logic.
//
    } else {
        //
        // Here we display the form for data entry.
        //
        ?>
        <title><?php echo xlt('Record Payment'); ?></title>

    <style type="text/css">
        body {
            font-family: sans-serif;
            font-size: 10pt;
            font-weight: normal
        }

        .dehead {
            color: #000000;
            font-family: sans-serif;
            font-size: 10pt;
            font-weight: bold
        }

        .detail {
            color: #000000;
            font-family: sans-serif;
            font-size: 10pt;
            font-weight: normal
        }

        #ajax_div_patient {
            position: absolute;
            z-index: 10;
            background-color: #FBFDD0;
            border: 1px solid #ccc;
            padding: 10px;
        }

        .table.top_table > tbody > tr > td {
            border-top: 0;
            padding: 0;
        }
    </style>
    <!--Removed standard dependencies 12/29/17 as not needed any longer since moved to a tab/frame not popup.-->

    <script language='JavaScript'>
        var mypcc = '1';
    </script>
    <?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>
    <script language="javascript" type="text/javascript">
        document.onclick = HideTheAjaxDivs;
    </script>

    <script type="text/javascript" src="../../library/topdialog.js"></script>

    <script language="JavaScript">
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

        function closeHow(e) {
            if (top.tab_mode) {
                top.activateTabByName('pat', true);
                top.tabCloseByName(window.name);
            } else {
                if (opener) {
                    if (opener.name === "left_nav") {
                        dlgclose();
                        return;
                    }
                }
                window.history.back();
            }
        }
// Sai custom code start
/* function calctotal() {
            var f = document.forms[0];
            var total = 0;
            for (var i = 0; i < f.elements.length; ++i) {
                var elem = f.elements[i];
                var ename = elem.name;
                if (ename.indexOf('form_upay[') == 0 || ename.indexOf('form_bpay[') == 0) {
                    if (elem.value.length > 0) total += Number(elem.value);
                }
            }
            f.form_paytotal.value = Number(total).toFixed(2);
            return true;
}*/
// Sai custom code end
        function coloring() {
            for (var i = 1; ; ++i) {
                if (document.getElementById('paying_' + i)) {
                    paying = document.getElementById('paying_' + i).value * 1;
                    patient_balance = document.getElementById('duept_' + i).innerHTML * 1;
                    //balance=document.getElementById('balance_'+i).innerHTML*1;
                    if (patient_balance > 0 && paying > 0) {
                        if (paying > patient_balance) {
                            document.getElementById('paying_' + i).style.background = '#FF0000';
                        }
                        else if (paying < patient_balance) {
                            document.getElementById('paying_' + i).style.background = '#99CC00';
                        }
                        else if (paying == patient_balance) {
                            document.getElementById('paying_' + i).style.background = '#ffffff';
                        }
                    }
                    else {
                        document.getElementById('paying_' + i).style.background = '#ffffff';
                    }
                }
                else {
                    break;
                }
            }
        }

        function CheckVisible(MakeBlank) {//Displays and hides the check number text box.
            if (document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value == 'check_payment' ||
                document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value == 'bank_draft') {
                document.getElementById('check_number').disabled = false;
            }
            else {
                document.getElementById('check_number').disabled = true;
            }
        }

        function validate() {
            var f = document.forms[0];
            ok = -1;
            top.restoreSession();
            issue = 'no';
            // prevent an empty form submission
            let flgempty = true;
            for (let i = 0; i < f.elements.length; ++i) {
                let ename = f.elements[i].name;
                if (f.elements[i].value == 'pre_payment' && f.elements[i].checked === true) {
                    if (Number(f.elements.namedItem("form_prepayment").value) !== 0) {
                        flgempty = false;
                    }
                    break;
                }
                if (ename.indexOf('form_upay[') === 0 || ename.indexOf('form_bpay[') === 0) {
                    if (Number(f.elements[i].value) !== 0) flgempty = false;
                }
            }
            if (flgempty) {
                alert("<?php echo xls('A Payment is Required!. Please input a payment line item entry.') ?>");
                return false;
            }
            // continue validation.
            if (((document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value == 'check_payment' ||
                    document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value == 'bank_draft') &&
                    document.getElementById('check_number').value == '')) {
                alert("<?php echo addslashes(xl('Please Fill the Check/Ref Number')) ?>");
                document.getElementById('check_number').focus();
                return false;
            }
   // Sai custom code start
    if(document.getElementById('deposit_date').value=='')
   {
   	alert("Please enter date");
	// Sai custom code end
	return false;
   }
            if (document.getElementById('radio_type_of_payment_self1').checked == false &&
                document.getElementById('radio_type_of_payment1').checked == false &&
                document.getElementById('radio_type_of_payment2').checked == false &&
                document.getElementById('radio_type_of_payment4').checked == false) {
                alert("<?php echo addslashes(xl('Please Select Type Of Payment.')) ?>");
                return false;
            }

            if (document.getElementById('radio_type_of_payment_self1').checked == true ||
                document.getElementById('radio_type_of_payment1').checked == true) {
                for (var i = 0; i < f.elements.length; ++i) {
                    var elem = f.elements[i];
                    var ename = elem.name;
                    if (ename.indexOf('form_upay[0') == 0) //Today is this text box.
                    {
                        if (elem.value * 1 > 0) {//A warning message, if the amount is posted with out encounter.
                            if (confirm("<?php echo addslashes(xlt('If patient has appointment click OK to create encounter otherwise, cancel this and then create an encounter for today visit.')) ?>")) {
                                ok = 1;
                            }
                            else {
                                elem.focus();
                                return false;
                            }
                        }
                        break;
                    }
                }
            }

            if (document.getElementById('radio_type_of_payment1').checked == true)//CO-PAY
            {
                var total = 0;
                for (var i = 0; i < f.elements.length; ++i) {
                    var elem = f.elements[i];
                    var ename = elem.name;
                    if (ename.indexOf('form_upay[0') == 0) //Today is this text box.
                    {
                        if (f.form_paytotal.value * 1 != elem.value * 1)//Total CO-PAY is not posted against today
                        {//A warning message, if the amount is posted against an old encounter.
                            if (confirm("<?php echo addslashes(xl('You are posting against an old encounter?')) ?>")) {
                                ok = 1;
                            }
                            else {
                                elem.focus();
                                return false;
                            }
                        }
                        break;
                    }
                }
            }//Co Pay
            else if (document.getElementById('radio_type_of_payment2').checked == true)//Invoice Balance
            {
                for (var i = 0; i < f.elements.length; ++i) {
                    var elem = f.elements[i];
                    var ename = elem.name;
                    if (ename.indexOf('form_upay[0') == 0) {
                        if (elem.value * 1 > 0) {
                            alert("<?php echo addslashes(xl('Invoice Balance cannot be posted. No Encounter is created.')) ?>");
                            return false;
                        }
                        break;
                    }
                }
            }
            if (ok == -1) {
                if (confirm("<?php echo addslashes(xl('Would you like to save?')) ?>")) {
                    return true;
                }
                else {
                    return false;
                }
            }
        }

        function cursor_pointer() {//Point the cursor to the latest encounter(Today)
            var f = document.forms[0];
            var total = 0;
            for (var i = 0; i < f.elements.length; ++i) {
                var elem = f.elements[i];
                var ename = elem.name;
                if (ename.indexOf('form_upay[') == 0) {
                    elem.focus();
                    break;
                }
            }
        }

        //=====================================================
        function make_it_hide_enc_pay() {
            document.getElementById('td_head_insurance_payment').style.display = "none";
            document.getElementById('td_head_patient_co_pay').style.display = "none";
            document.getElementById('td_head_co_pay').style.display = "none";
            document.getElementById('td_head_insurance_balance').style.display = "none";
            for (var i = 1; ; ++i) {
                var td_inspaid_elem = document.getElementById('td_inspaid_' + i)
                var td_patient_copay_elem = document.getElementById('td_patient_copay_' + i)
                var td_copay_elem = document.getElementById('td_copay_' + i)
                var balance_elem = document.getElementById('balance_' + i)
                if (td_inspaid_elem) {
                    td_inspaid_elem.style.display = "none";
                    td_patient_copay_elem.style.display = "none";
                    td_copay_elem.style.display = "none";
                    balance_elem.style.display = "none";
                }
                else {
                    break;
                }
            }
            document.getElementById('td_total_4').style.display = "none";
            document.getElementById('td_total_7').style.display = "none";
            document.getElementById('td_total_8').style.display = "none";
            document.getElementById('td_total_6').style.display = "none";

            document.getElementById('table_display').width = "420px";
        }

        //=====================================================
        function make_visible() {
            document.getElementById('td_head_rep_doc').style.display = "";
            document.getElementById('td_head_description').style.display = "";
            document.getElementById('td_head_total_charge').style.display = "none";
            document.getElementById('td_head_insurance_payment').style.display = "none";
            document.getElementById('td_head_patient_payment').style.display = "none";
            document.getElementById('td_head_patient_co_pay').style.display = "none";
            document.getElementById('td_head_co_pay').style.display = "none";
            document.getElementById('td_head_insurance_balance').style.display = "none";
            document.getElementById('td_head_patient_balance').style.display = "none";
            for (var i = 1; ; ++i) {
                var td_charges_elem = document.getElementById('td_charges_' + i)
                var td_inspaid_elem = document.getElementById('td_inspaid_' + i)
                var td_ptpaid_elem = document.getElementById('td_ptpaid_' + i)
                var td_patient_copay_elem = document.getElementById('td_patient_copay_' + i)
                var td_copay_elem = document.getElementById('td_copay_' + i)
                var balance_elem = document.getElementById('balance_' + i)
                var duept_elem = document.getElementById('duept_' + i)
                if (td_charges_elem) {
                    td_charges_elem.style.display = "none";
                    td_inspaid_elem.style.display = "none";
                    td_ptpaid_elem.style.display = "none";
                    td_patient_copay_elem.style.display = "none";
                    td_copay_elem.style.display = "none";
                    balance_elem.style.display = "none";
                    duept_elem.style.display = "none";
                }
                else {
                    break;
                }
            }
            document.getElementById('td_total_7').style.display = "";
            document.getElementById('td_total_8').style.display = "";
            document.getElementById('td_total_1').style.display = "none";
            document.getElementById('td_total_2').style.display = "none";
            document.getElementById('td_total_3').style.display = "none";
            document.getElementById('td_total_4').style.display = "none";
            document.getElementById('td_total_5').style.display = "none";
            document.getElementById('td_total_6').style.display = "none";

            document.getElementById('table_display').width = "505px";
        }

        function make_it_hide() {
            document.getElementById('td_head_rep_doc').style.display = "none";
            document.getElementById('td_head_description').style.display = "none";
            document.getElementById('td_head_total_charge').style.display = "";
            document.getElementById('td_head_insurance_payment').style.display = "";
            document.getElementById('td_head_patient_payment').style.display = "";
            document.getElementById('td_head_patient_co_pay').style.display = "";
            document.getElementById('td_head_co_pay').style.display = "";
            document.getElementById('td_head_insurance_balance').style.display = "";
            document.getElementById('td_head_patient_balance').style.display = "";
            for (var i = 1; ; ++i) {
                var td_charges_elem = document.getElementById('td_charges_' + i)
                var td_inspaid_elem = document.getElementById('td_inspaid_' + i)
                var td_ptpaid_elem = document.getElementById('td_ptpaid_' + i)
                var td_patient_copay_elem = document.getElementById('td_patient_copay_' + i)
                var td_copay_elem = document.getElementById('td_copay_' + i)
                var balance_elem = document.getElementById('balance_' + i)
                var duept_elem = document.getElementById('duept_' + i)
                if (td_charges_elem) {
                    td_charges_elem.style.display = "";
                    td_inspaid_elem.style.display = "";
                    td_ptpaid_elem.style.display = "";
                    td_patient_copay_elem.style.display = "";
                    td_copay_elem.style.display = "";
                    balance_elem.style.display = "";
                    duept_elem.style.display = "";
                }
                else {
                    break;
                }
            }
            document.getElementById('td_total_1').style.display = "";
            document.getElementById('td_total_2').style.display = "";
            document.getElementById('td_total_3').style.display = "";
            document.getElementById('td_total_4').style.display = "";
            document.getElementById('td_total_5').style.display = "";
            document.getElementById('td_total_6').style.display = "";
            document.getElementById('td_total_7').style.display = "";
            document.getElementById('td_total_8').style.display = "";

            document.getElementById('table_display').width = "635px";
        }

        function make_visible_radio() {
            document.getElementById('tr_radio1').style.display = "";
            document.getElementById('tr_radio2').style.display = "none";
        }

        function make_hide_radio() {
            document.getElementById('tr_radio1').style.display = "none";
            document.getElementById('tr_radio2').style.display = "";
        }

        function make_visible_row() {
            document.getElementById('table_display').style.display = "";
            document.getElementById('table_display_prepayment').style.display = "none";
        }

        function make_hide_row() {
            document.getElementById('table_display').style.display = "none";
            document.getElementById('table_display_prepayment').style.display = "";
        }

        function make_self() {
            make_visible_row();
            make_it_hide();
            make_it_hide_enc_pay();
            document.getElementById('radio_type_of_payment_self1').checked = true;
            cursor_pointer();
        }

        function make_insurance() {
            make_visible_row();
            make_it_hide();
            cursor_pointer();
            document.getElementById('radio_type_of_payment1').checked = true;
        }
    </script>

    </head>

    <body class="body_top" onLoad="cursor_pointer();">
    <div class="container well well-sm">
        <div class="col-md-8 col-md-offset-2">
            <span class='text'>
                <h4 class="text-center"><?php echo htmlspecialchars(xl('Accept Payment for'), ENT_QUOTES); ?>
                    <?php echo htmlspecialchars($patdata['fname'], ENT_QUOTES) . " " .
                        htmlspecialchars($patdata['lname'], ENT_QUOTES) . " " . htmlspecialchars($patdata['mname'], ENT_QUOTES) . " (" . htmlspecialchars($patdata['pid'], ENT_QUOTES) . ")" ?>
                        <?php $NameNew = $patdata['fname'] . " " . $patdata['lname'] . " " . $patdata['mname']; ?></h4>
                </span>
                <form class="form-inline" method='post'
                      action='front_payment.php<?php echo ($payid) ? "?payid=" . attr($payid) : ""; ?>'
                      onsubmit='return validate();'>
                    <input type='hidden' name='form_pid' value='<?php echo attr($pid) ?>'/>
<!-- Sai custom code start -->
<?php
$result3 = getInsuranceData($pid, "primary", "copay, provider, DATE_FORMAT(`date`,'%m-%d-%Y') as effdate");
   if ($result3['provider']) { //Use provider in case there is an ins record w/ unassigned insco
     $insco_name = getInsuranceProvider($result3['provider']);
 }
 //$effective_date =  htmlspecialchars(oeFormatShortDate($result3['effdate'],ENT_NOQUOTES));
  $effective_date =  htmlspecialchars($result3['effdate'],ENT_NOQUOTES);
 
 
$query="SELECT SUM(pay_total) AS unapplied_pre_payment,sum(global_amount) as global_amount FROM ar_session WHERE patient_id = ? and payment_type='patient'  ";	
  	 $bres = sqlStatement($query,array($pid));
	  $brow = sqlFetchArray($bres);
	  $global_amount = $brow['global_amount'];
	 $unapplied_pre_payment = $brow['unapplied_pre_payment'] - $global_amount;
	 
	  
	  $query1="select SUM(pay_amount) as applied_pre_payment from ar_activity as act join ar_session as ars on ars.session_id=act.session_id and ars.patient_id=?  ";	
  	  $bres1 = sqlStatement($query1,array($pid));
	  $brow1 = sqlFetchArray($bres1);
	  $applied_pre_payment = $brow1['applied_pre_payment'];
	  
	  $pre_payment = $unapplied_pre_payment - $applied_pre_payment;
	// Modified by sonali: 10413 Patient balance customization  
		 
		 
		 $draftbalance = get_draft_charges($pid);
		 $draft_bal_arr = explode("@",$draftbalance);		 
		 $ins_draft_bal =$draft_bal_arr['1']; 
		 $pat_draft_bal = $draft_bal_arr['0'] - $draft_bal_arr['1'];
		 
		 
		/* $patbalance =   get_patient_balance($pid, "true");
		 $patientbalance = $patbalance - $pre_payment + $pat_draft_bal;
		 
		//Debit the patient balance from insurance balance
		$insbal = get_patient_balance($pid, "false");
		$insurancebalance = $insbal + $ins_draft_bal;
		$overallpatientbalance =  $patientbalance;
		//$overallpatientbalance = $pre_payment;
	   $totalbalance=$patientbalance + $insurancebalance ;*/
	   /*Sonali: Patient balance issue */
	   $newbalance=getBalanceData($pid);

		  $bal_arr = explode("~",$newbalance);  
        ///  print_r($bal_arr);
		  $insurancebalance = $bal_arr[0]+$bal_arr[1]+$bal_arr[2]+ $ins_draft_bal;
         // echo $insurancebalance."+++".$ins_draft_bal;
		  $patientbalance = $bal_arr[3]- $pre_payment + $pat_draft_bal;;
		  $totalbalance = $insurancebalance+$patientbalance;
	   
	   if($overallpatientbalance<0)
	   $overallpatientbalance = "($".htmlspecialchars(text(oeFormatMoney($patientbalance))).")";
	   else
	   $overallpatientbalance = "$".htmlspecialchars(text(oeFormatMoney($patientbalance)));
	   
	   if($insurancebalance<0)
	   $insurancebalance = "($".htmlspecialchars(text(oeFormatMoney($insurancebalance))).")";
	   else
	   $insurancebalance = "$".htmlspecialchars(text(oeFormatMoney($insurancebalance)));
	   
	   if($totalbalance<0)
	   $totalbalance = "($".htmlspecialchars(text(oeFormatMoney($totalbalance))).")";
	   else
	   $totalbalance = "$".htmlspecialchars(text(oeFormatMoney($totalbalance)));
	   
	   /*if($draft_bal_arr['0'])
	   $unbilled_insurance =  "$".htmlspecialchars(text(oeFormatMoney($draft_bal_arr['0'])));
	   else
	   $unbilled_insurance =  "$0";*/
	   
?>
<!-- Sai custom code end -->
<div>
<table class="table table-condensed top_table">
 <tr height="10">
 	<td colspan="3">&nbsp;</td>
 </tr>

 <tr>
  <td colspan='3' align='left' class='text' width="40%"><!-- Sai custom code -->
   <b><?php echo htmlspecialchars(xl('Accept Payment for'), ENT_QUOTES); ?>&nbsp;:&nbsp;&nbsp;<?php echo htmlspecialchars($patdata['fname'], ENT_QUOTES) . " " .
    htmlspecialchars($patdata['lname'], ENT_QUOTES) . " " .htmlspecialchars($patdata['mname'], ENT_QUOTES). " (" . htmlspecialchars($patdata['pid'], ENT_QUOTES) . ")" ?></b>
	<?php $NameNew=$patdata['fname'] . " " .$patdata['lname']. " " .$patdata['mname'];?>
  </td>
   <!-- sai custom code  start-->
  <td width="10%"></td>
  <td width="10%"></td>
  <td width="40%" class='text'><b>Primary Insurance: </b><?php echo htmlspecialchars($insco_name,ENT_NOQUOTES); ?> <b>Effective date: </b><?php echo $effective_date; ?></td>
 </tr>

 <tr height="15"><td colspan='3' width="40%"></td>
  <td width="10%"></td>
  <td width="10%"></td>
  <td width="40%" class='text'><b>Patient Balance Due: </b><?php echo $overallpatientbalance;?></td>
 </tr>
 <!-- sai custom code  end-->
                            <tr>
                                <td class='text'>
                                    <label><?php echo xlt('Payment Method'); ?>:</label>
                                </td>
                                <td colspan='2'>
                                    <select name="form_method" id="form_method" class="text form-control" onChange='CheckVisible("yes")'>
                                        <?php
                                        $query1112 = "SELECT * FROM list_options where list_id=?  ORDER BY seq, title ";
                                        $bres1112 = sqlStatement($query1112, array('payment_method'));
                                        while ($brow1112 = sqlFetchArray($bres1112)) {
                                            if ($brow1112['option_id'] == 'electronic' || $brow1112['option_id'] == 'bank_draft') {
                                                continue;
                                            }

                                            echo "<option value='" . htmlspecialchars($brow1112['option_id'], ENT_QUOTES) . "'>" . htmlspecialchars(xl_list_label($brow1112['title']), ENT_QUOTES) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
		   <!-- sai custom code  start-->
		  <td width="10%"></td>
		  <td width="10%"></td>
		  <td width="40%" class='text'><b>Insurance Balance Due: </b><?php echo $insurancebalance;?></td>
                            </tr>

                            <tr height="5">
		 <tr height="5"><td colspan='3'></td>
 
		 <td width="10%"></td>
		  <td width="10%"></td>
		  <td width="40%" class='text'><b>Total Balance Due: </b><?php echo $totalbalance;?></td>
		 </tr>
		<?php /*?> <tr height="5"><td colspan='3'></td> 
		 <td width="10%"></td>
		  <td width="10%"></td>
		  <td width="40%" class='text'><font color='#ee6600'>Unbilled Insurance: <?php echo $unbilled_insurance;?></font></td>
		 </tr><?php */?>
		 <!-- sai custom code  end-->
                            <tr>
                                <td class='text'>
                                    <label><?php echo xla('Check/Ref Number'); ?>:</label>
                                </td>
                                <td colspan='2'>
                                    <div id="ajax_div_patient" style="display:none;"></div>
                                    <input class="input-sm" type='text' id="check_number" name='form_source'
                                       style="width:120px"
                                       value='<?php echo htmlspecialchars($payrow['source'], ENT_QUOTES); ?>'>
                                </td>
                            </tr>
                            <tr height="5">
                                <td colspan='3'></td>
                            </tr>

                            <tr>
						<!-- sai custom code start -->
				  <td class='text' valign="middle" colspan='2'>
				   <?php echo htmlspecialchars(xl('Deposit date'), ENT_QUOTES); ?>:
				  </td>
				  <td class='text' colspan="2" ><input type='text' size=10 maxlength='10' id='deposit_date' name='deposit_date' value='<?php echo htmlspecialchars($payrow['deposit_date'], ENT_QUOTES); ?>' onBlur="dateblur(this,mypcc,'<?php echo date("m/d/Y"); ?>'),replacedate(this)" onkeyup='datekeyup(this)' >(mm/dd/yyyy) </td>
				 </tr>

				 <tr style="visibility:hidden">
				  <td class='text' valign="middle"  colspan="2">
				  <!-- sai custom code end -->
                                <td class='text' valign="middle">
                                    <label><?php echo htmlspecialchars(xl('Patient Coverage'), ENT_QUOTES); ?>:</label>
                                </td>
                                <td class='text' colspan="2">
                                    <label class="radio-inline">
                                    <input type="radio" name="radio_type_of_coverage" id="radio_type_of_coverage1"
                                           value="self"
                                           onClick="make_visible_radio();make_self();"/><?php echo htmlspecialchars(xl('Self'), ENT_QUOTES); ?>
                                    </label>
                                    <label class="radio-inline">
                                    <input type="radio" name="radio_type_of_coverage" id="radio_type_of_coverag2"
                                           value="insurance"
                                           checked="checked"
                                           onClick="make_hide_radio();make_insurance();"/><?php echo htmlspecialchars(xl('Insurance'), ENT_QUOTES); ?>
                                    </label>
                                </td>
                            </tr>

                            <tr height="5">
                                <td colspan='3'></td>
                            </tr>

                            <tr id="tr_radio1" style="display:none"><!-- For radio Insurance -->
                                <td class='text' valign="top">
                                    <label><?php echo htmlspecialchars(xl('Payment against'), ENT_QUOTES); ?>:</label>
                                </td>
                                <td class='text' colspan="2">
                                    <label class="radio-inline">
                                    <input type="radio" name="radio_type_of_payment" id="radio_type_of_payment_self1"
                                           value="cash"
                                           onClick="make_visible_row();make_it_hide_enc_pay();cursor_pointer();"/><?php echo htmlspecialchars(xl('Encounter Payment'), ENT_QUOTES); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr id="tr_radio2"><!-- For radio self -->
                                <td class='text' valign="top">
                                    <label><?php echo htmlspecialchars(xl('Payment against'), ENT_QUOTES); ?>:</label>
                                </td>
				<td class='text' colspan="2">
				<!--<label class="radio-inline">
                                    <input type="radio" name="radio_type_of_payment"
                                           id="radio_type_of_payment1" value="copay" checked="checked"
                                           onClick="make_visible_row();cursor_pointer();"/><?php echo htmlspecialchars(xl('Co Pay'), ENT_QUOTES); ?>
                                    </label>
                                    <label class="radio-inline">
                                    <input type="radio" name="radio_type_of_payment" id="radio_type_of_payment2"
                                           value="invoice_balance"
                                           onClick="make_visible_row();"/><?php echo htmlspecialchars(xl('Invoice Balance'), ENT_QUOTES); ?>
                                    </label>-->
                                    <label class="radio-inline">
                                    <!--<input type="radio" name="radio_type_of_payment" id="radio_type_of_payment4"
                                           value="pre_payment"
                                           onClick="make_hide_row();"/><?php echo htmlspecialchars(xl('Pre Pay'), ENT_QUOTES); ?>-->
									 <input type="radio" name="radio_type_of_payment" id="radio_type_of_payment4"
                                           value="pre_payment" checked
                                         /><?php echo htmlspecialchars(xl('Pre Pay'), ENT_QUOTES); ?>	   
                                    </label>
                                </td>
                            </tr>

                            <tr height="15">
                                <td colspan='3'></td>
                            </tr>

                        </table>
                       <!-- <table width="200" border="0" cellspacing="0" cellpadding="0" id="table_display_prepayment"
                               style="display:none">-->
						<table width="200" border="0" cellspacing="0" cellpadding="0" id="table_display_prepayment">
                            <tr>
                                <td class='detail'><?php echo htmlspecialchars(xl('Pre Payment'), ENT_QUOTES); ?></td>
                                <td><input type='text' name='form_prepayment' style='width:100px'/></td>
                            </tr>
                        </table>
                    </div>
                    <table class="table table-condensed" id="table_display" border='1' cellpadding='1' cellspacing='1'>
                        <thead>
                        <tr bgcolor="#cccccc" id="tr_head">
                            <th class="dehead">
                                <?php echo htmlspecialchars(xl('DOS'), ENT_QUOTES) ?>
                            </th>
                            <th class="dehead">
                                <?php echo htmlspecialchars(xl('Encounter'), ENT_QUOTES) ?>
                            </th>
                             <th class="dehead">
                                <?php echo htmlspecialchars(xl('Status'), ENT_QUOTES) ?>
                            </th>
                            <th class="dehead" align="center" id="td_head_total_charge">
                                <?php echo htmlspecialchars(xl('Total Charge'), ENT_QUOTES) ?>
                            </th>
                              <th class="dehead" align="center" width="80" id="td_head_total_charge" >
                               <?php echo htmlspecialchars( xl('Copay'), ENT_QUOTES) ?><!-- sai custom code -->
                              </th>
                            <th class="dehead" align="center" id="td_head_rep_doc" style='display:none'>
                                <?php echo htmlspecialchars(xl('Report/ Form'), ENT_QUOTES) ?>
                            </th>
                            <th class="dehead" align="center" id="td_head_description" style='display:none'>
                                <?php echo htmlspecialchars(xl('Description'), ENT_QUOTES) ?>
                            </th>
                            <th class="dehead" align="center" id="td_head_insurance_payment">
                                <?php echo htmlspecialchars(xl('Insurance Payment'), ENT_QUOTES) ?>
                            </th>
                            <th class="dehead" align="center" id="td_head_patient_payment">
                                <?php echo htmlspecialchars(xl('Patient Payment'), ENT_QUOTES) ?>
                            </th>
                           
            					<!-- Sai custom code start -->
            			   <td class="dehead" align="center" width="60" id="td_head_wo_payment" >
            			   <?php echo htmlspecialchars( xl('WriteOff'), ENT_QUOTES) ?>
            			  </td>
            			  <td class="dehead" align="center" width="80" id="td_head_patient_payment" >
            			   <?php echo htmlspecialchars( xl('Interest'), ENT_QUOTES) ?>
            			  </td>
            			  <td class="dehead" align="center" width="80" id="td_head_patient_payment" >
            			   <?php echo htmlspecialchars( xl('Receipts'), ENT_QUOTES) ?>
            			  </td>
            			  <td class="dehead" align="center" width="80" id="td_head_patient_payment" >
            			   <?php echo htmlspecialchars( xl('Adjustment'), ENT_QUOTES) ?>
            			  </td>
            			  <td class="dehead" align="center" width="80" id="td_head_patient_payment" >
            			   <?php echo htmlspecialchars( xl('Takeback'), ENT_QUOTES) ?>
            			  </td>
            			   <td class="dehead" align="center" width="80" id="td_head_patient_payment" >
            			   <?php echo htmlspecialchars( xl('Patient Payment Method'), ENT_QUOTES) ?>
            			  </td>
            			   <td class="dehead" align="center" width="80" id="td_head_patient_payment" >
            			   <?php echo htmlspecialchars( xl('Check/Reference #'), ENT_QUOTES) ?>
            			  </td>
            			   <td class="dehead" align="center" width="80" id="td_head_patient_payment" >
            			   <?php echo htmlspecialchars( xl('Payment /Deposite Date'), ENT_QUOTES) ?>
            			  </td>
                          <td class="dehead" align="center" width="80" id="td_head_insurance_balance" >
                           <?php echo htmlspecialchars( xl('Insurance Balance'), ENT_QUOTES) ?>
                          </td>
                          <td class="dehead" align="center" width="80" id="td_head_patient_balance" >
                           <?php echo htmlspecialchars( xl('Patient Balance'), ENT_QUOTES) ?>
                          </td>
                          <!-- Sai custom code start -->
                           <td class="dehead" align="center" width="80" id="td_head_balance" >
                           <?php echo htmlspecialchars( xl('Balance'), ENT_QUOTES) ?>
                          </td>
			 <?php /*?> 
			 <!-- Sai custom code end -->
   <?php echo htmlspecialchars( xl('Patient Payment Method'), ENT_QUOTES) ?>
  </td>
   <td class="dehead" align="center" width="80" id="td_head_patient_payment" >
   <?php echo htmlspecialchars( xl('Check/Reference #'), ENT_QUOTES) ?>
  </td>
   <td class="dehead" align="center" width="80" id="td_head_patient_payment" >
   <?php echo htmlspecialchars( xl('Payment /Deposite Date'), ENT_QUOTES) ?>
  </td>
 <?php /*?> 
 <!-- Sai custom code end -->
                            <th class="dehead" align="center" id="td_head_co_pay">
                                <?php echo htmlspecialchars(xl('Required Co Pay'), ENT_QUOTES) ?>
                            </th>
                            <th class="dehead" align="center" id="td_head_insurance_balance">
                                <?php echo htmlspecialchars(xl('Insurance Balance'), ENT_QUOTES) ?>
                            </th>
                            <th class="dehead" align="center" id="td_head_patient_balance">
                                <?php echo htmlspecialchars(xl('Patient Balance'), ENT_QUOTES) ?>
                            </th>
                            <th class="dehead" align="center">
                                <?php echo htmlspecialchars(xl('Paying'), ENT_QUOTES) ?>
                            </th>
				  <!-- Sai custom code start -->
				   <td class="dehead" align="center" width="80" id="td_head_balance" >
				   <?php echo htmlspecialchars( xl('Balance'), ENT_QUOTES) ?>
				  </td>
				  <?php /*?><td class="dehead" align="center" width="50">
				   <?php echo htmlspecialchars( xl('Paying'), ENT_QUOTES) ?>
				  </td>
  <td class="dehead" align="center" width="300">
   <?php echo htmlspecialchars( xl('Notes'), ENT_QUOTES) ?>
  </td><?php */?>
  <!-- Sai custom code end -->
                        </tr>
                        </thead>
                        <?php
                        $encs = array();

                        // Get the unbilled service charges and payments by encounter for this patient.
                        //
                        $query = "SELECT fe.encounter, fe.claim_status_id,b.code_type, b.code, b.modifier, b.fee, " .
                        "LEFT(fe.date, 10) AS encdate ,fe.last_level_closed,fe.billing_note, b.units " .
                        "FROM  form_encounter AS fe left join billing AS b  on " .
                        "b.pid = ? AND b.activity = 1  AND " .//AND b.billed = 0
                        "b.code_type != 'TAX' AND b.fee != 0 " .
                        "AND fe.pid = b.pid AND fe.encounter = b.encounter " .
                        "where fe.pid = ? " .
    			"ORDER BY b.encounter";// Sai custom code start -->
                        $bres = sqlStatement($query, array($pid, $pid));
                        //
                        while ($brow = sqlFetchArray($bres)) {
                            $key = 0 - $brow['encounter'];
                            if (empty($encs[$key])) {
                                $encs[$key] = array(
                                'encounter' => $brow['encounter'],
				'claim_status_id' => $brow['claim_status_id'],// Sai custom code 
                                'date' => $brow['encdate'],
                                'last_level_closed' => $brow['last_level_closed'],
                                'charges' => 0,
        			'payments' => 0,
				'billing_note' => $brow['billing_note']);// Sai custom code 
                            }

                            if ($brow['code_type'] === 'COPAY') {
                                //$encs[$key]['payments'] -= $brow['fee'];
                            } else {
      				$encs[$key]['charges']  += ( $brow['fee'] * $brow['units']); // Sai custom code  -->
                                // Add taxes.
                                $sql_array = array();
                                $query = "SELECT taxrates FROM codes WHERE " .
                                "code_type = ? AND " .
                                "code = ? AND ";
                                array_push($sql_array, $code_types[$brow['code_type']]['id'], $brow['code']);
                                if ($brow['modifier']) {
                                    $query .= "modifier = ?";
                                    array_push($sql_array, $brow['modifier']);
                                } else {
                                    $query .= "(modifier IS NULL OR modifier = '')";
                                }

                                $query .= " LIMIT 1";
                                $trow = sqlQuery($query, $sql_array);
      				$encs[$key]['charges'] += calcTaxes($trow, ($brow['fee']*$brow['units']));// Sai custom code start -->
                            }
                        }

                        // Do the same for unbilled product sales.
                        //
		   $query = "SELECT fe.encounter,fe.claim_status_id, s.drug_id, s.fee, " .
		    "LEFT(fe.date, 10) AS encdate,fe.last_level_closed,fe.billing_note " .
		    "FROM form_encounter AS fe left join drug_sales AS s " .
		    "on s.pid = ? AND s.fee != 0 " .//AND s.billed = 0 
		    "AND fe.pid = s.pid AND fe.encounter = s.encounter " .
			"where fe.pid = ? " .
		    "ORDER BY fe.encounter asc";// Sai custom code start -->

                        $dres = sqlStatement($query, array($pid, $pid));
                        //
                        while ($drow = sqlFetchArray($dres)) {
                            $key = 0 - $drow['encounter'];
                            if (empty($encs[$key])) {
                                $encs[$key] = array(
                                'encounter' => $drow['encounter'],
				'claim_status_id' => $drow['claim_status_id'],// Sai custom code start -->
                                'date' => $drow['encdate'],
                                'last_level_closed' => $drow['last_level_closed'],
                                'charges' => 0,
        			'payments' => 0,
				'billing_note' => $drow['billing_note']);// Sai custom code start -->
                            }

                            $encs[$key]['charges'] += $drow['fee'];
                            // Add taxes.
                            $trow = sqlQuery("SELECT taxrates FROM drug_templates WHERE drug_id = ? " .
                            "ORDER BY selector LIMIT 1", array($drow['drug_id']));
                            $encs[$key]['charges'] += calcTaxes($trow, $drow['fee']);
                        }

                        ksort($encs, SORT_NUMERIC);
                        $gottoday = false;
                        //Bringing on top the Today always
                        foreach ($encs as $key => $value) {
                            $dispdate = $value['date'];
                            if (strcmp($dispdate, $today) == 0 && !$gottoday) {
                                $gottoday = true;
                                break;
                            }
                        }

                        // If no billing was entered yet for today, then generate a line for
                        // entering today's co-pay.
                        //
                        //if (!$gottoday) {
                          //  echoLine("form_upay[0]", date("Y-m-d"), 0, 0, 0, 0 /*$duept*/);//No encounter yet defined.
                        //} 

                        $gottoday = false;
                        foreach ($encs as $key => $value) {
                            $enc = $value['encounter'];
                            $dispdate = $value['date'];
			    $dos = substr($dispdate, 0, 10);// Sai custom code start -->
                            if (strcmp($dispdate, $today) == 0 && !$gottoday) {
                                $dispdate = date("Y-m-d");
                                $gottoday = true;
                            }

                            //------------------------------------------------------------------------------------
                            $inscopay = getCopay($pid, $dispdate);
                            $patcopay = getPatientCopay($pid, $enc);
				// Sai custom code start -->
				$encounter_stat_arr = getEncounterStatus($value['claim_status_id']);
				$encounter_status = $encounter_stat_arr['status'];
                            //Insurance Payment
                            //-----------------
                           
			//below added by sangram for copay issue on 18_june_2014
			$drow = sqlQuery("select sum(pay_amount) as payments FROM ar_activity where ".
			" payer_type!='0' and  pid = ? and  encounter= ? ",array($pid,$enc));
			$dpayment=$drow['payments'];
	
			$drow = sqlQuery("select sum(adj_amount) as adjustments FROM ar_activity where ".
			"  pid = ? and  encounter= ? ",array($pid,$enc));
			$dadjustment=$drow['adjustments'];
	
			$drow = sqlQuery("select sum(w_o) as writeoff from ar_activity where (pay_amount!='0.00' or reason_code='wo' ) " .
			" and pid = ? and  encounter= ? ",array($pid,$enc));
			$w_o = $drow['writeoff'];
                            //Patient Payment
                            //---------------
                            $drow = sqlQuery(
                                "SELECT  SUM(pay_amount) AS payments, " .
                                "SUM(adj_amount) AS adjustments  FROM ar_activity WHERE " .
                                "pid = ? and encounter = ? and " .
                                "payer_type = 0 and account_code!='PCP' ",
                                array($pid, $enc)
                            );
                            $dpayment_pat = $drow['payments'];

				$qry = sqlQuery("select payment_method,reference,deposit_date from ar_session as ars join ar_activity as act on ars.session_id=act.session_id and ars.patient_id=act.pid where pid=? and encounter=? and payer_type = 0 order by ars.session_id desc limit 1",array($pid,$enc));
				$payment_method = $qry['payment_method'];
				$reference = $qry['reference'];
				$deposit_date = $qry['deposit_date'];
                            //------------------------------------------------------------------------------------
                            //NumberOfInsurance
                            $ResultNumberOfInsurance = sqlStatement("SELECT COUNT( DISTINCT TYPE ) NumberOfInsurance FROM insurance_data
			where pid = ? and provider>0  and '$dos' >= (select date from insurance_data where pid = ? and provider>0 order by date desc limit 1) ",array($pid,$pid));
                            $RowNumberOfInsurance = sqlFetchArray($ResultNumberOfInsurance);
                            $NumberOfInsurance = $RowNumberOfInsurance['NumberOfInsurance'] * 1;
                            //------------------------------------------------------------------------------------
                            $duept = 0;
	$takeback=0;
	$tot_adjustment=0;$tot_w_o=0;$tot_payment=0;$interest=0;
	if($NumberOfInsurance==0 || $value['last_level_closed']==4 || $NumberOfInsurance== $value['last_level_closed'])
	 {//Patient balance
	  $brow = sqlQuery("SELECT SUM(fee * units) AS amount FROM billing WHERE " .
	  "pid = ? and encounter = ? AND activity = 1",array($pid,$enc));
	  $srow = sqlQuery("SELECT SUM(fee) AS amount FROM drug_sales WHERE " .
	  "pid = ? and encounter = ? ",array($pid,$enc));
	  /*$drow = sqlQuery("SELECT SUM(pay_amount) AS payments, " .
	  "SUM(adj_amount) AS adjustments, SUM(w_o) AS writeoff FROM ar_activity WHERE " .
	  "pid = ? and encounter = ? ",array($pid,$enc)); */

	//below added by sangram for copay issue on 18_june_2014
	  $drow = sqlQuery("select sum(pay_amount) as payments FROM ar_activity where ".
	"  pid = ? and  encounter= ? ",array($pid,$enc));
	$tot_payment=$drow['payments'];
	
	$drow = sqlQuery("select sum(adj_amount) as adjustments FROM ar_activity where ".
	"  pid = ? and  encounter= ? ",array($pid,$enc));
	$tot_adjustment=$drow['adjustments'] ;
	
	$drow = sqlQuery("select sum(w_o) as writeoff from ar_activity where (pay_amount!='0.00' or reason_code='wo' ) " .
	"and pid = ? and  encounter= ? ",array($pid,$enc));
	$tot_w_o = $drow['writeoff'] ;
	  
	  $drow = sqlQuery("select sum(interest) as interest  from ar_activity where encounter=?  and interest>0 and pay_amount>0  ",array($enc));
	$interest = $drow['interest'];
	
	
	$drow = sqlQuery("SELECT  sum(pay_amount) as takeback from ar_activity where   pid =? and  encounter  =?  and pay_amount<0 and account_code='Takeback'  ",array($pid,$enc));
	$takeback = $drow['takeback'];
	  
	  
	  $duept= $brow['amount'] + $srow['amount'] - $tot_payment - $tot_adjustment - $tot_w_o;
	 }
	 $bnote = sqlQuery("SELECT billing_note from form_encounter where encounter=$enc and pid=$pid");
	 $billing_note = $bnote['billing_note'];
	 
    echoLine("form_upay[$enc]", $dispdate, $value['charges'],
      $dpayment_pat, ($dpayment), $w_o, $payment_method, $reference, $duept,$enc,$inscopay,$patcopay, $billing_note,$interest,$dadjustment,$takeback,$deposit_date,$encounter_status);
  }
// Sai custom code end -->

  // Now list previously billed visits.

  if ($INTEGRATED_AR) {

 } // end $INTEGRATED_AR
  else {
  $interest=0;$tot_adjustment=0;$takeback=0;$deposit_date='';$payment_method=''; $reference='';// Sai custom code start -->
    // Query for all open invoices.
   /* $query = "SELECT ar.id, ar.invnumber, ar.amount, ar.paid, " .
      "ar.intnotes, ar.notes, ar.shipvia, " .
      "(SELECT SUM(invoice.sellprice * invoice.qty) FROM invoice WHERE " .
      "invoice.trans_id = ar.id AND invoice.sellprice > 0) AS charges, " .
      "(SELECT SUM(invoice.sellprice * invoice.qty) FROM invoice WHERE " .
      "invoice.trans_id = ar.id AND invoice.sellprice < 0) AS adjustments, " .
      "(SELECT SUM(acc_trans.amount) FROM acc_trans WHERE " .
      "acc_trans.trans_id = ar.id AND acc_trans.chart_id = ? " .
      "AND acc_trans.source NOT LIKE 'Ins%') AS ptpayments " .
      "FROM ar WHERE ar.invnumber LIKE ? AND " .
      "ar.amount != ar.paid " .
      "ORDER BY ar.invnumber";
    $ires = SLQuery($query, array($chart_id_cash,$pid."%") );
    if ($sl_err) die($sl_err);
    $num_invoices = SLRowCount($ires);

    for ($ix = 0; $ix < $num_invoices; ++$ix) {
      $irow = SLGetRow($ires, $ix);

      // Get encounter ID and date of service.
      list($patient_id, $enc) = explode(".", $irow['invnumber']);
     // Sai custom code start -->
      $tmp = sqlQuery("SELECT LEFT(date, 10) AS encdate,billing_note,claim_status_id FROM form_encounter " .
        "WHERE encounter = ?", array($enc) );
      $svcdate = $tmp['encdate'];
	  $billing_note = $tmp['billing_note'];
	  $encounter_status = getEncounterStatus($tmp['claim_status_id']);
	  
// Sai custom code start -->
      // Compute $duncount as in sl_eob_search.php to determine if
      // this invoice is at patient responsibility.
      $duncount = substr_count(strtolower($irow['intnotes']), "statement sent");
      if (! $duncount) {
        $insgot = strtolower($irow['notes']);
        $inseobs = strtolower($irow['shipvia']);
        foreach (array('ins1', 'ins2', 'ins3') as $value) {
          if (strpos($insgot, $value) !== false &&
              strpos($inseobs, $value) === false)
            --$duncount;
        }
      }


      $inspaid = $irow['paid'] + $irow['ptpayments'] - $irow['adjustments'];
      $balance = $irow['amount'] - $irow['paid'];
      // Sai custom code start -->
	  $w_o = $irow['writeoff'];
	  $tot_adjustment = $irow['adjustments'];
	  $payment_method = $irow['payment_method'];
	  $reference = $irow['reference'];
	  $deposit_date = $irow['deposit_date'];
      $duept  = ($duncount < 0) ? 0 : $balance;
	  $takeback = $irow['takeback'];
	  $interest = $irow['interest'];

      echoLine("form_bpay[$enc]", $svcdate, $irow['charges'],
        0 - $irow['ptpayments'], $inspaid, $w_o, $payment_method, $reference, $duept,'','','', $billing_note,$interest,$tot_adjustment,$takeback,$deposit_date,$encounter_status);
    }*/
  } // end not $INTEGRATED_AR
    if (! $gottoday) {
   //echoLine("form_upay[0]", date("Y-m-d"), 0, 0, 0, 0,'','',0,'','','', $encs[$key]['billing_note'] ,$interest,$tot_adjustment,$takeback,$deposit_date/*$duept*/);//No encounter yet defined.
  }
//Sai custom code end 
                        // Continue with display of the data entry form.
                        ?>
			<!-- Sai custom code start -->
			 <?php /*?><tr bgcolor="#cccccc">
                        <td class="dehead" id='td_total_1'></td>
                        <td class="dehead" id='td_total_2'></td>
                        <td class="dehead" id='td_total_3'></td>
                        <td class="dehead" id='td_total_4'></td>
                        <td class="dehead" id='td_total_5'></td>
                        <td class="dehead" id='td_total_6'></td>
                        <td class="dehead" id='td_total_7'></td>
                        <td class="dehead" id='td_total_8'></td>
			    <td class="dehead" id='td_total_9'></td>
			     <td class="dehead" id='td_total_10'></td>
                        <td class="dehead" align="right">
                            <?php echo htmlspecialchars(xl('Total'), ENT_QUOTES); ?>
                        </td>
                        <td class="dehead" align="right">
                            <input type='text' name='form_paytotal' value=''
                                   style='color:#00aa00;width:50px' readonly/>
                        </td>
		 </tr><?php */?>
		<!-- Sai custom code end -->
                    </table>

                    <span class="text-center">
    <input type='submit' name='form_save' value='<?php echo htmlspecialchars(xl('Generate Invoice'), ENT_QUOTES); ?>'/> &nbsp;
    <input type='button' value='<?php echo xla('Cancel'); ?>' onclick='closeHow(event)'/>

    <input type="hidden" name="hidden_patient_code" id="hidden_patient_code" value="<?php echo attr($pid); ?>"/>
    <input type='hidden' name='ajax_mode' id='ajax_mode' value=''/>
    <input type='hidden' name='mode' id='mode' value=''/>
    </span>
                </form>
                <script language="JavaScript">
 //calctotal(); // Sai custom code 
                </script>
            </div>
        </div>
        </body>

        <?php
    }
?>
</html>
