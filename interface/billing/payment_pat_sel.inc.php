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
//===============================================================================
//Patient ajax section and listing of charges..Used in New Payment and Edit Payment screen.
//===============================================================================
require_once("$srcdir/forms.inc"); // Sai custom code

if (isset($_POST["mode"])) {
    if (($_POST["mode"] == "search" || $_POST["default_search_patient"] == "default_search_patient") && $_REQUEST['hidden_patient_code'] * 1 > 0) {
        $hidden_patient_code = $_REQUEST['hidden_patient_code'];
        $RadioPaid           = $_REQUEST['RadioPaid'];
        if ($RadioPaid == 'Show_Paid') {
            $StringForQuery = '';
        } elseif ($RadioPaid == 'Non_Paid') {
            $StringForQuery = " and last_level_closed = 0 ";
        } elseif ($RadioPaid == 'Show_Primary_Complete') {
            $StringForQuery = " and last_level_closed >= 1 ";
        }
        // Sai custom code start
        // code added by pawan for search by encounter
        $enc_number = $_POST['enc_number'];
        
        if (isset($enc_number) && !empty($enc_number)) {
            
            $StringForQuery .= " and form_encounter.encounter= $enc_number ";
        }
        
        
        
        /******* code added by pawan for hide and show**************/
        $ResultSearchNew = sqlStatement("SELECT billing.id,last_level_closed,billing.encounter,form_encounter.`date`,billing.code_type,billing.code,billing.modifier,fee,units,billing_note  FROM billing ,form_encounter where billing.encounter=form_encounter.encounter and code_type='CPT4' and billing.activity!=0 and  form_encounter.pid ='$hidden_patient_code' and billing.pid ='$hidden_patient_code'  $StringForQuery ORDER BY form_encounter.`date`, 
             form_encounter.encounter,billing.code,billing.modifier");
        // Sai custom code end        
        $res             = sqlStatement("SELECT fname,lname,mname FROM patient_data
                         where pid ='" . $_REQUEST['hidden_patient_code'] . "'");
        $row             = sqlFetchArray($res);
        $fname           = $row['fname'];
        $lname           = $row['lname'];
        $mname           = $row['mname'];
        $NameNew         = $lname . ' ' . $fname . ' ' . $mname;
        // Sai custom code start
        
        $lres = sqlStatement("SELECT * FROM list_options " . "WHERE list_id = 'AdjCode' ORDER BY seq");
        
        while ($lrow = sqlFetchArray($lres)) {
            $AdjCodes[$lrow['option_id']] = $lrow['title'];
        }
    }
}
//===============================================================================
?>
<script language='JavaScript'>
function CheckallEncounter(enc,cnt)
{

var ThisCheckbox= "FollowUp" + cnt;

var inputs = document.getElementsByTagName('input');

for (i=0; i<inputs.length; i++){

    if (inputs[i].type == 'hidden'){
            if(inputs[i].value.localeCompare(enc)== 0)
            {
            var checkbox= "FollowUp" + inputs[i].name.charAt( inputs[i].name.length-1 );
                 if(document.getElementById(ThisCheckbox).checked == true)
                 {
                 document.getElementById(checkbox).checked = true;
                 }
                 else
                 {
                      document.getElementById(checkbox).checked = false;
                     var ImputBox= "FollowUpReason" + inputs[i].name.charAt( inputs[i].name.length-1 );

                 document.getElementById(ImputBox).value = "";
                 }
            }
    }
}



}

function CopyContent(enc,cnt)
{

var ThisTnputbox= "BillNote" + cnt;

var inputs = document.getElementsByTagName('input');
for (i=0; i<inputs.length; i++){

        if(inputs[i].value.localeCompare(enc)== 0)
        {
         var ImputBox= "BillNote" + inputs[i].name.charAt( inputs[i].name.length-1 );

         document.getElementById(ImputBox).value =  document.getElementById(ThisTnputbox).value
        
        }
    
}


}

/***************************************************/

function myAjaxBillingNotes(cnt) {

      $.ajax({
           type: "POST",
           url: 'get_encounter_notes_ajax.php',
           data:{action:'get_billing_notes',id:cnt},
           success:function(html) {
             alert(html);
             // var e = document.getElementById("billing_ps_expand_table");
              
                  //e.style.display = 'block';
            // document.getElementById("billing_ps_expand").innerHTML = html;
            //  $('#loadingmessage').hide();
           }

      });
 }

/****************************************************/
function view(cnt)
{
var ThisTnputbox= "BillNote" + cnt;
alert(document.getElementById(ThisTnputbox).value);

//var fetchedNote = document.getElementById(ThisTnputbox).value;
//var note = prompt("Billing Note",document.getElementById(ThisTnputbox).value);
//document.getElementById(ThisTnputbox).value = note;

}

function validateAmount(amount,index,oldcopay){
    if(isNaN(amount)){
    alert("Please enter correct amount.\nCharacters not allowed.");
    document.getElementById('HiddenCopayAmount'+index).value=oldcopay;
    document.getElementById('HiddenCopayAmount'+index).focus();
    return false;
    }
    else{
    var balance = document.getElementById("check_balance").value;
    var balance_amt = parseInt(balance);
    UnappliedAmount=document.getElementById('TdUnappliedAmount').innerHTML*1;

    // Bug 10525 Copay issue
    if(balance_amt=="" || balance_amt==0 || isNaN(balance_amt))
    {
     alert("Please enter copay amount first ");
      document.getElementById('HiddenCopayAmount'+index).value=oldcopay;
    document.getElementById('HiddenCopayAmount'+index).focus();
    return false;
    }
    else if(amount>UnappliedAmount)
      {
       alert("Copay amount should not be greater than "+UnappliedAmount);
       document.getElementById('HiddenCopayAmount'+index).value=oldcopay;
    document.getElementById('HiddenCopayAmount'+index).focus();
    return false;
      }
    }
}
 
</script>
<!-- // Sai custom code end -->
<table width="1004" border="0" cellspacing="0" cellpadding="0"  id="TablePatientPortion">
      <tr height="5">
        <td colspan="13" align="left" >
            <table width="705" border="0" cellspacing="0" cellpadding="0" bgcolor="#c1eafa"> <!-- Sai custom code -->
              <tr height="5">
                <td class='title' width="700" ></td>
              </tr>
              <tr>
              <!-- Sai custom code start -->
                <td  class='text'><table width="850" border="0" cellspacing="0" cellpadding="0" style="border:1px solid black" >
              <tr>
                <td width="45" align="left" class="text">&nbsp;<?php
echo htmlspecialchars(xl('Encounter'), ENT_QUOTES) . ':';
?>  
                </td>
                <td width="265" align="justify">
          
           <!-- Code change by pawan for search by encounter -->
                 <input type="text" name="enc_number" id="enc_number" width="100" value="<?php
echo $Message == '' ? htmlspecialchars(formData('enc_number')) : '';
?>" /> 
                  <input type="button" id="enc_search" value="Get Encounter" /> 
                
                     <br />
                </td>
               <td colspan="6" align="left">
                
                </td>
              
                </tr>
        
              <tr>
                <td width="45" align="left" class="text">&nbsp;<?php
echo htmlspecialchars(xl('Patient'), ENT_QUOTES) . ':';
?>
               </td>
                <td width="265"><input type="hidden" id="hidden_ajax_patient_close_value" value="<?php
echo $Message == '' ? htmlspecialchars($NameNew) : '';
?>" />
                <input name='patient_code'  style="width:265px"   id='patient_code' class="text"  onKeyDown="PreventIt(event)"  
                value="<?php
echo $Message == '' ? htmlspecialchars($NameNew) : '';
?>"  autocomplete="off" />
        <input type="hidden" id="ins_code" name="ins_code" value="<?php
echo $_POST['hidden_type_code'];
?>"  /><!--Sai custom code -->
        </td> <!--onKeyUp="ajaxFunction(event,'patient','edit_payment.php');" -->
        <!-- Sai custom code end -->
                <td width="55" colspan="2" style="padding-left:5px;" ><div  class="text" name="patient_name" id="patient_name"  
                style="border:1px solid black; ; padding-left:5px; width:55px; height:17px;"><?php
echo $Message == '' ? htmlspecialchars(formData('hidden_patient_code')) : '';
?></div>
                </td>
                <td width="84" class="text">&nbsp;<input type="radio" name="RadioPaid" onClick="SearchOnceMore()" <?php
echo $_REQUEST['RadioPaid'] == 'Non_Paid' || $_REQUEST['RadioPaid'] == '' ? 'checked' : '';
?>  value="Non_Paid" id="Non_Paid"  /><?php
echo htmlspecialchars(xl('Non Paid'), ENT_QUOTES);
?></td>
                <td width="168" class="text"><input type="radio" name="RadioPaid" onClick="SearchOnceMore()" 
                <?php
echo $_REQUEST['RadioPaid'] == 'Show_Primary_Complete' ? 'checked' : '';
?>  value="Show_Primary_Complete" 
                id="Show_Primary_Complete" /><?php
echo htmlspecialchars(xl('Show Primary Complete'), ENT_QUOTES);
?></td>
                <td width="157" class="text"><input type="radio" name="RadioPaid" onClick="SearchOnceMore()" 
                <?php
echo $_REQUEST['RadioPaid'] == 'Show_Paid' ? 'checked' : '';
?>  value="Show_Paid" id="Show_Paid" /><?php
echo htmlspecialchars(xl('Show All Transactions'), ENT_QUOTES);
?>
               </td>
              </tr>
              <tr>
                <td align="left" class="text"></td>
                <td><div id='ajax_div_patient_section'>
                      <div id='ajax_div_patient_error'>
                      </div>
                      <div id="ajax_div_patient" style="display:none;"></div>
                      </div>
                     </div>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text"></td>
                </tr>
            </table>        </td>
              </tr>
            </table>

        </td>
      </tr>
    <tr>
    <td colspan="13" align="left" >
            
            <?php //New distribution section
//$CountIndex=0;
$CountIndexBelow   = 0;
$PreviousEncounter = 0;
$PreviousPID       = 0;
if ($RowSearch = sqlFetchArray($ResultSearchNew)) {
?>
           <table width="1004"  border="0" cellpadding="0" cellspacing="0" align="center" id="TableDistributePortion">
              <tr class="text" height="10">
                <td colspan="14"></td>
              </tr>
              <!-- Sai Custom code start -->
              <tr class="text" bgcolor="#eafac1">
                <td width="55" class="left top" ><?php
    echo htmlspecialchars(xl('Post For'), ENT_QUOTES);
?></td>
                <td width="55" class="left top" ><?php
    echo htmlspecialchars(xl('Ins Co.'), ENT_QUOTES);
?></td>
                <td width="80" class="left top" ><?php
    echo htmlspecialchars(xl('Service Date'), ENT_QUOTES);
?></td>
                <td width="65" class="left top" ><?php
    echo htmlspecialchars(xl('Encounter'), ENT_QUOTES);
?></td>
                <td width="70" class="left top" ><?php
    echo htmlspecialchars(xl('CPT Code'), ENT_QUOTES);
?></td>
                <td width="55" class="left top" ><?php
    echo htmlspecialchars(xl('Charge'), ENT_QUOTES);
?></td>
                <td width="40" class="left top" ><?php
    echo htmlspecialchars(xl('Copay Paid'), ENT_QUOTES);
?></td>
                <td width="45" class="left top" ><?php
    echo htmlspecialchars(xl('Balance'), ENT_QUOTES);
?></td>
                <td width="45" class="left top" ><?php
    echo htmlspecialchars(xl('Allowed'), ENT_QUOTES);
?></td>
                <td width="45" class="left top" ><?php
    echo htmlspecialchars(xl('Payment'), ENT_QUOTES);
?></td>
                <td width="45" class="left top" ><?php
    echo htmlspecialchars(xl('Adj Amt'), ENT_QUOTES);
?></td>
                <td width="45" class="left top" ><?php
    echo htmlspecialchars(xl('Dedctbl'), ENT_QUOTES);
?></td>
                <td width="45" class="left top" ><?php
    echo htmlspecialchars(xl('Takebck'), ENT_QUOTES);
?></td>
              <?php
    /*?> Added by sangram babar for bug id 7960, 8033<?php */
?>
               <td width="45" class="left top" ><?php
    echo htmlspecialchars(xl('W/O'), ENT_QUOTES);
?></td>
               <td width="45" class="left top" ><?php
    echo htmlspecialchars(xl('co-ins'), ENT_QUOTES);
?></td>
               <td width="45" class="left top" ><?php
    echo htmlspecialchars(xl('interest'), ENT_QUOTES);
?></td>  
               <td width="45" class="left top" ><?php
    echo htmlspecialchars(xl('Receipts'), ENT_QUOTES);
?></td>
<?php
    /*?>    //commented by sangram for rollbacking Balance amount column bug id 8326<?php */
?>
          <?php
    /*?>  <td width="45" class="left top" ><?php echo htmlspecialchars( xl('Open Balance'), ENT_QUOTES) ?></td> <?php */
?>                
                <td width="70" class="left top" ><?php
    echo htmlspecialchars(xl('Adj code'), ENT_QUOTES);
?></td>
                <td width="60" class="left top" ><?php
    echo htmlspecialchars(xl('MSP Code'), ENT_QUOTES);
?></td>
                <td width="100" class="left top" ><?php
    echo htmlspecialchars(xl('Status'), ENT_QUOTES);
?></td>
                <td width="60" class="left top" ><?php
    echo htmlspecialchars(xl('Resn / Note'), ENT_QUOTES);
?></td>
                <td width="209" class="left top right" ><?php
    echo htmlspecialchars(xl('Follow Up Reason / Note'), ENT_QUOTES);
?></td>
                <td width="209" class="left top right" ><?php
    echo htmlspecialchars(xl('Billing Note'), ENT_QUOTES);
?></td>
              </tr>
        <!-- Sai custom code end -->
                <?php
    $enc_arr = array(); // Sai custom code
    do {
        $CountIndex++;
        $CountIndexBelow++;
        $Ins             = 0;
        // Determine the next insurance level to be billed.
        // Sai custom code
        $ferow           = sqlQuery("SELECT id,date, last_level_closed " . "FROM form_encounter WHERE " . "pid = '$hidden_patient_code' AND encounter = '" . $RowSearch['encounter'] . "'");
        $date_of_service = substr($ferow['date'], 0, 10);
        $new_payer_type  = 0 + $ferow['last_level_closed'];
        if ($new_payer_type <= 3 && !empty($ferow['last_level_closed']) || $new_payer_type == 0) {
            ++$new_payer_type;
        }
        
        $new_payer_id = arGetPayerID($hidden_patient_code, $date_of_service, $new_payer_type);
        
        if ($new_payer_id == 0) {
            $Ins = 0;
        } elseif ($new_payer_id > 0) {
            $Ins = $new_payer_type;
        }
        
        
        $ServiceDateArray = explode(' ', $RowSearch['date']);
        $ServiceDate      = oeFormatShortDate($ServiceDateArray[0]);
        $Codetype         = $RowSearch['code_type'];
        $Code             = $RowSearch['code'];
        $Modifier         = $RowSearch['modifier'];
        if ($Modifier != '') {
            $ModifierString = ", $Modifier";
        } else {
            $ModifierString = "";
        }
        // Sai custom code start
        $Fee         = $RowSearch['fee'] * $RowSearch['units'];
        $Encounter   = $RowSearch['encounter'];
        $BillingNote = $RowSearch['billing_note'];
        
        // added by sangram babar for implementing encounter status functionality
        /*$St = sqlStatement("select Status from encounter_status where encounter='$Encounter' and Status_Date = (select max(Status_Date) from encounter_status where encounter='$Encounter')");
        $rowId = sqlFetchArray($St);
        $EncStatus = $rowId['Status'];*/
        
        $St        = CheckEncounterStatus($Encounter);
        $EncStatus = $St['Status'];
        
        // added by sangram babar for bug id 8044
        /* $St = sqlStatement("select ins_code from insurance_companies where id = (
        select payer_id from billing where encounter='$Encounter' limit 1)");
        $rowId = sqlFetchArray($St);
        $InsCo = $rowId['attn']; */
        
        
        
        $enctCount = sqlStatement("SELECT count(billing.encounter) FROM billing ");
        // Sai custom code end
        //Always associating the copay to a particular charge.
        $BillingId = $RowSearch['id'];
        // Sai custom code
        $resId     = sqlStatement("SELECT id  FROM billing where code_type='CPT4'  and
                    pid ='$hidden_patient_code' and  encounter  ='$Encounter' and billing.activity!=0 order by id");
        $rowId     = sqlFetchArray($resId);
        $Id        = $rowId['id'];
        
        if ($BillingId != $Id) { //multiple cpt in single encounter
            $Copay          = 0.00;
            $editable_copay = ' type="hidden" '; // Sai custom code
        } else {
            $editable_copay = ' style="width:45px;text-align:right; font-size:12px" type="text" '; // Sai custom code
            $resCopay       = sqlStatement("SELECT sum(fee) as copay FROM billing where  code_type='COPAY'  and
                        pid ='$hidden_patient_code' and  encounter  ='$Encounter' and billing.activity!=0");
            $rowCopay       = sqlFetchArray($resCopay);
            $Copay          = $rowCopay['copay'] * -1;
            
            $resMoneyGot = sqlStatement("SELECT sum(pay_amount) as PatientPay FROM ar_activity where
                        pid ='$hidden_patient_code'  and  encounter  ='$Encounter' and  payer_type=0 and 
                        account_code='PCP'"); //new fees screen copay gives account_code='PCP'
            $rowMoneyGot = sqlFetchArray($resMoneyGot);
            $PatientPay  = $rowMoneyGot['PatientPay'];
            
            $Copay = $Copay + $PatientPay;
        }
        
        //payer_type!=0, supports both mapped and unmapped code_type in ar_activity
        $resMoneyGot = sqlStatement("SELECT sum(pay_amount) as MoneyGot FROM ar_activity where
                        pid ='$hidden_patient_code' and (code_type='$Codetype' or code_type='') and code='$Code' and modifier='$Modifier'  and  encounter  ='$Encounter' and  !(payer_type=0 and 
                        account_code='PCP')"); //new fees screen copay gives account_code='PCP'
        $rowMoneyGot = sqlFetchArray($resMoneyGot);
        $MoneyGot    = $rowMoneyGot['MoneyGot'];
        // Sai custom code start
        //commented by sangram for rollbacking Balance amount column bug id 8326
        //$open_balance=$rowMoneyGot['open_balance'];
        $resReceipt  = sqlStatement("SELECT sum(pay_amount) as receipts FROM ar_activity where code='$Code' and modifier='$Modifier' and  encounter  ='$Encounter'");
        
        $rowReceipt = sqlFetchArray($resReceipt);
        $receipts   = $rowReceipt['receipts'];
        
        if ($receipts == '') {
            if ($MoneyGot == '')
                $receipts = 0;
            else
                $receipts = $MoneyGot;
        }
        
        // Sai custom code end
        $resMoneyAdjusted = sqlStatement("SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where
                        pid ='$hidden_patient_code' and (code_type='$Codetype' or code_type='') and code='$Code' and modifier='$Modifier'  and  encounter  ='$Encounter'");
        $rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
        $MoneyAdjusted    = $rowMoneyAdjusted['MoneyAdjusted'];
        // Sai custom code start
        $resReceipt       = sqlStatement("select sum(w_o) as wrt_off from ar_activity where encounter='$Encounter' and (pay_amount>'0.00' or reason_code='wo' ) and payer_type!='0' and code='$Code' and modifier='$Modifier' ");
        $rowReceipt       = sqlFetchArray($resReceipt);
        $w_o              = $rowReceipt['wrt_off'];
        
        
        // echo "===Formula===".$Fee."-".$Copay."-".$MoneyGot."-".$MoneyAdjusted."-".$W_O;
        $Remainder = $Fee - $Copay - $MoneyGot - $MoneyAdjusted - $w_o;
        // Sai custom code end
        $TotalRows = sqlNumRows($ResultSearchNew);
        if ($CountIndexBelow == sqlNumRows($ResultSearchNew)) {
            $StringClass = ' bottom left top ';
        } else {
            $StringClass = ' left top ';
        }
        
        
        if ($Ins == 1) {
            $bgcolor = '#ddddff';
            // Sai custom code start
            $St      = sqlStatement("select ins_code from insurance_companies where id = (
select provider from insurance_data where type='primary' and pid='$hidden_patient_code' and 
date <  NOW() order by date desc limit 1) ");
            $rowId   = sqlFetchArray($St);
            $InsCo   = $rowId['ins_code'];
            // Sai custom code end
        } elseif ($Ins == 2) {
            $bgcolor = '#ffdddd';
            // Sai custom code start
            $St      = sqlStatement("select ins_code from insurance_companies where id = (
select provider from insurance_data where type='secondary' and pid='$hidden_patient_code' and 
date <  NOW() order by date desc limit 1)");
            $rowId   = sqlFetchArray($St);
            $InsCo   = $rowId['ins_code'];
            // Sai custom code end    
        } elseif ($Ins == 3) {
            $bgcolor = '#F2F1BC';
            // Sai custom code start
            $St      = sqlStatement("select ins_code from insurance_companies where id = (
select provider from insurance_data where type='tertiary' and pid='$hidden_patient_code' and 
date <  NOW() order by date desc limit 1)");
            $rowId   = sqlFetchArray($St);
            $InsCo   = $rowId['ins_code'];
            // Sai custom code end
        } elseif ($Ins == 0) {
            $InsCo   = 'Patient'; // Sai custom code
            $bgcolor = '#AAFFFF';
        }
?>
<tr class="text"  bgcolor='<?php echo $bgcolor;?>' id="trCharges<?php echo $CountIndex;?>">
   <td align="left" class="<?php echo $StringClass;?>" >
      <input name="HiddenIns<?php echo $CountIndex;?>" id="HiddenIns<?php echo $CountIndex;?>" value="<?php echo htmlspecialchars($Ins);?>" type="hidden"/>
      <?php echo generate_select_list("payment_ins$CountIndex", "payment_ins", "$Ins", "Insurance/Patient", '', '', 'ActionOnInsPat("' . $CountIndex . '","' . $hidden_patient_code . '","' . $CountIndex . '")');
         ?>
   </td>
   <!-- Sai custom code -->
   <!-- Sai custom code start -->
   <td class="<?php echo $StringClass;?>" > 
      <input type="text" value="<?php echo htmlspecialchars($InsCo);?>" autocomplete="off" name="InsCo<?php echo $CountIndex;?>" id="InsCo<?php echo $CountIndex;?>"  onKeyDown="PreventIt(event)"  style="width:45px;text-align:right;font-size:12px" autocomplete="off" />
   </td>
   <!-- Sai custom code end -->
   <td class="<?php echo $StringClass;?>" >
      <input name="HiddenEncounterDOS<?php echo $CountIndex;?>" id="HiddenEncounterDOS<?php echo $CountIndex;?>" value="<?php echo htmlspecialchars($ServiceDate);?>" type="hidden"/>
      <?php echo htmlspecialchars($ServiceDate);?>
   </td>
   <td align="right" class="<?php echo $StringClass;?>" ><input name="HiddenEncounter<?php echo $CountIndex;?>" value="<?php echo htmlspecialchars($Encounter);?>" 
      type="hidden"/><?php
      echo htmlspecialchars($Encounter);
      ?></td>

   <td class="<?php
      echo $StringClass;
      ?>" ><input name="HiddenCode<?php
      echo $CountIndex;
      ?>" value="<?php
      echo htmlspecialchars($Code);
      ?>"
      type="hidden"/><?php
      echo htmlspecialchars($Code . $ModifierString);
      ?><input name="HiddenModifier<?php
      echo $CountIndex;
      ?>" value="<?php
      echo htmlspecialchars($Modifier);
      ?>"
      type="hidden"/></td>

   <!-- Sai custom code -->
  <!-- <td class="<?php
      echo $StringClass;
      ?>" ><input name="HiddenCodetype<?php
      echo $CountIndex;
      ?>" value="<?php
      echo htmlspecialchars($Codetype);
      ?>" type="hidden"/><input name="HiddenCode<?php
      echo $CountIndex;
      ?>" value="<?php
      echo htmlspecialchars($Code);
      ?>"
      type="hidden"/><?php
      echo htmlspecialchars($Codetype . "-" . $Code . $ModifierString);
      ?><input name="HiddenModifier<?php
      echo $CountIndex;
      ?>" value="<?php
      echo htmlspecialchars($Modifier);
      ?>"
      type="hidden"/></td>-->

   <td align="right" class="<?php
      echo $StringClass;
      ?>" ><input name="HiddenChargeAmount<?php
      echo $CountIndex;
      ?>"
      id="HiddenChargeAmount<?php
         echo $CountIndex;
         ?>"  value="<?php
         echo htmlspecialchars($Fee);
         ?>" type="hidden"/><?php
      echo htmlspecialchars($Fee);
      ?></td>
   <!-- Sai custom code start -->
   <?php
      if ($_POST['type_name'] == "patient" && ($_POST['adjustment_code'] == "pre_payment" || $_POST['adjustment_code'] == "patient_payment"))
          $copay_editable = 1;
      else
          $copay_editable = 0;
      if ($copay_editable == 0) {
      ?> 
   <td align="right" class="<?php
      echo $StringClass;
      ?>" ><input name="HiddenCopayAmountOld<?php
      echo $CountIndex;
      ?>"
      id="HiddenCopayAmountOld<?php
         echo $CountIndex;
         ?>"  value="<?php
         echo htmlspecialchars($Copay);
         ?>" type="hidden"/><?php
      echo htmlspecialchars(number_format($Copay, 2));
      ?></td>
                
                 <?php
        } else {
            $query                 = "SELECT SUM(pay_total) AS unapplied_pre_payment,sum(global_amount) as global_amount FROM ar_session WHERE patient_id = ? and payment_type='patient'  ";
            $bres                  = sqlStatement($query, array(
                $hidden_patient_code
            ));
            $brow                  = sqlFetchArray($bres);
            $global_amount         = $brow['global_amount'];
            $unapplied_pre_payment = $brow['unapplied_pre_payment'] - $global_amount;
            
            
            $query1              = "select SUM(pay_amount) as applied_pre_payment from ar_activity as act join ar_session as ars on ars.session_id=act.session_id and ars.patient_id=?  ";
            $bres1               = sqlStatement($query1, array(
                $hidden_patient_code
            ));
            $brow1               = sqlFetchArray($bres1);
            $applied_pre_payment = $brow1['applied_pre_payment'];
            
            $pre_payment = $unapplied_pre_payment - $applied_pre_payment;
            echo "<input type='hidden' name='check_balance' id='check_balance' value='$pre_payment' >";
?>


<!-- sai custom code end -->
<td align="right" class="<?php
   echo $StringClass;
   ?>" ><input name="HiddenCopayAmount<?php
   echo $CountIndex;
   ?>"
   id="HiddenCopayAmount<?php
      echo $CountIndex;
      ?>"  value="<?php
      echo htmlspecialchars(number_format($Copay, 2));
      ?>" onchange="validateAmount(this.value,<?php
      echo $CountIndex;
      ?>,<?php
      echo $Copay;
      ?>);" <?php
      echo $editable_copay;
      ?> /></td>
<!-- Sai custom code -->
<!-- Sai custom code start-->
<?php
   }
   ?>
<?php
   /*?><!--   this is remove for making free text entry for allowed amount              onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);UpdateTotalValues(<?php echo $CountIndexAbove*1+1; ?>,<?php echo $TotalRows; ?>,'Allowed','initialallowtotal');UpdateTotalValues(<?php echo $CountIndexAbove*1+1; ?>,<?php echo $TotalRows; ?>,'Payment','initialpaymenttotal');UpdateTotalValues(<?php echo $CountIndexAbove*1+1; ?>,<?php echo $TotalRows; ?>,'AdjAmount','initialAdjAmounttotal');RestoreValues(<?php echo $CountIndex; ?>)"--><?php */
   ?> 
<td align="right"   id="RemainderTd<?php
   echo $CountIndex;
   ?>"  class="<?php
   echo $StringClass;
   ?>" ><?php
   echo htmlspecialchars(round($Remainder, 2));
   ?></td>
<input name="HiddenRemainderTd<?php
   echo $CountIndex;
   ?>" id="HiddenRemainderTd<?php
   echo $CountIndex;
   ?>" 
   value="<?php
      echo htmlspecialchars(round($Remainder, 2));
      ?>" type="hidden"/>
<?php
   /*?>    
<td class="<?php echo $StringClass; ?>" ><input  name="Allowed<?php echo $CountIndex; ?>" id="Allowed<?php echo $CountIndex; ?>" 
   onKeyDown="PreventIt(event)"  autocomplete="off"  
   onChange="" 
   type="text"   style="width:45px;text-align:right; font-size:12px" onblur="UpdateWO(<?php echo $CountIndex; ?>)"  /></td>
<?php */
   ?>
<?php
   // Modified by Sonali: 8870 Secondary payment manual posting and patient payment issue
   $res_allowed  = sqlStatement("Select max(allowed_amt) as allowed_amt from ar_activity where encounter='$Encounter'  and pid='$hidden_patient_code' ");
   $rows_allowed = sqlFetchArray($res_allowed);
   $prev_allowed = $rows_allowed['allowed_amt'];
   if ($prev_allowed > 0)
       $allowed_edit = 'readonly';
   else
       $allowed_edit = '';
   ?>
<td class="<?php
   echo $StringClass;
   ?>" ><input  name="Allowed<?php
   echo $CountIndex;
   ?>"  id="Allowed<?php
   echo $CountIndex;
   ?>" 
   onKeyDown="PreventIt(event)"  onChange="ValidateNumeric(this);UpdateTotalValues(<?php
      echo $CountIndexAbove * 1 + 1;
      ?>,<?php
      echo $TotalRows;
      ?>,'Deductible','initialdeductibletotal');" onblur="UpdateWO(<?php
      echo $CountIndex;
      ?>);"   autocomplete="off"   type="text"  
   style="width:45px;text-align:right; font-size:12px" <?php
      echo $allowed_edit;
      ?> /></td>
<td class="<?php
   echo $StringClass;
   ?>" ><input   type="text"  name="Payment<?php
   echo $CountIndex;
   ?>" 
   onKeyDown="PreventIt(event)"   autocomplete="off"  id="Payment<?php
      echo $CountIndex;
      ?>" 
   onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php
      echo $CountIndex;
      ?>);UpdateTotalValues(<?php
      echo $CountIndexAbove * 1 + 1;
      ?>,<?php
      echo $TotalRows;
      ?>,'Payment','initialpaymenttotal');RestoreValues(<?php
      echo $CountIndex;
      ?>)" 
   style="width:60px;text-align:right; font-size:12px" /></td>
<td class="<?php
   echo $StringClass;
   ?>" ><input  name="AdjAmount<?php
   echo $CountIndex;
   ?>"  onKeyDown="PreventIt(event)" 
   autocomplete="off"  id="AdjAmount<?php
      echo $CountIndex;
      ?>"  
   onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php
      echo $CountIndex;
      ?>);UpdateTotalValues(<?php
      echo $CountIndexAbove * 1 + 1;
      ?>,<?php
      echo $TotalRows;
      ?>,'AdjAmount','initialAdjAmounttotal');RestoreValues(<?php
      echo $CountIndex;
      ?>)"  
   type="text"   style="width:70px;text-align:right; font-size:12px" /></td>
<td class="<?php
   echo $StringClass;
   ?>" ><input  name="Deductible<?php
   echo $CountIndex;
   ?>"  id="Deductible<?php
   echo $CountIndex;
   ?>" 
   onKeyDown="PreventIt(event)"  onChange="ValidateNumeric(this);UpdateTotalValues(<?php
      echo $CountIndexAbove * 1 + 1;
      ?>,<?php
      echo $TotalRows;
      ?>,'Deductible','initialdeductibletotal');"   autocomplete="off"   type="text"  
   style="width:60px;text-align:right; font-size:12px" /></td>
<td class="<?php
   echo $StringClass;
   ?>" ><input  name="Takeback<?php
   echo $CountIndex;
   ?>"  onKeyDown="PreventIt(event)"   autocomplete="off"  
   id="Takeback<?php
      echo $CountIndex;
      ?>"  
   onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php
      echo $CountIndex;
      ?>);UpdateTotalValues(<?php
      echo $CountIndexAbove * 1 + 1;
      ?>,<?php
      echo $TotalRows;
      ?>,'Takeback','initialtakebacktotal');RestoreValues(<?php
      echo $CountIndex;
      ?>)"  
   type="text"   style="width:60px;text-align:right; font-size:12px" /></td>
<?php
   /*?> below two td Added by sangram babar for bug id 7960, 8033<?php */
   ?>
<td class="<?php
   echo $StringClass;
   ?>" ><input  name="wo<?php
   echo $CountIndex;
   ?>"  id="wo<?php
   echo $CountIndex;
   ?>" 
   onKeyDown="PreventIt(event)"  onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php
      echo $CountIndex;
      ?>);"   autocomplete="off"   type="text" style="width:45px;text-align:right; font-size:12px" /></td>
<td class="<?php
   echo $StringClass;
   ?>" ><input  name="coins<?php
   echo $CountIndex;
   ?>"  id="coins<?php
   echo $CountIndex;
   ?>" 
   onKeyDown="PreventIt(event)"  onChange="ValidateNumeric(this);UpdateTotalValues(<?php
      echo $CountIndexAbove * 1 + 1;
      ?>,<?php
      echo $TotalRows;
      ?>,'Deductible','initialdeductibletotal');"   autocomplete="off"   type="text"  
   style="width:45px;text-align:right; font-size:12px" /></td>
<td class="<?php
   echo $StringClass;
   ?>" ><input  name="interest<?php
   echo $CountIndex;
   ?>"  id="interest<?php
   echo $CountIndex;
   ?>" 
   onKeyDown="PreventIt(event)"  onChange="ValidateNumeric(this);UpdateTotalValues(<?php
      echo $CountIndexAbove * 1 + 1;
      ?>,<?php
      echo $TotalRows;
      ?>,'Deductible','initialdeductibletotal');"   autocomplete="off"   type="text"  
   style="width:45px;text-align:right; font-size:12px" /></td>
<td class="<?php
   echo $StringClass;
   ?>"  ><input type="text"  id="HiddenReceipts<?php
   echo $CountIndex;
   ?>" name="HiddenReceipts<?php
   echo $CountIndex;
   ?>" value="<?php
   echo $receipts;
   ?>" style="width:45px;text-align:right; font-size:12px" autocomplete="off"   type="text" readonly /></td>
<input type="hidden" id="receipts<?php
   echo $CountIndex;
   ?>" name="receipts<?php
   echo $CountIndex;
   ?>" value="<?php
   echo $receipts;
   ?>"  />
<?php
   /*?>
<td align="right" class="<?php echo $StringClass; ?>"><input type="text" id="HiddenOpenbalance<?php echo $CountIndex; ?>"  name="HiddenOpenbalance<?php echo $CountIndex; ?>" value="<?php echo $open_balance; ?>" style="width:45px;text-align:right; font-size:12px" readonly /></td>
<input  name="open_balance<?php echo $CountIndex; ?>"  id="open_balance<?php echo $CountIndex; ?>" value="<?php echo $open_balance; ?>"  type="hidden"/><?php */
   ?>
<td class="<?php
   echo $StringClass;
   ?>" >   <select name='Adj_code<?php
   echo $CountIndex;
   ?>' id="Adj_code<?php
   echo $CountIndex;
   ?>">
   <?php
      foreach ($AdjCodes as $key => $value) {
          echo " <option value='$key'>$value</option>\n";
      }
      ?>
   </select>
</td>
<td align="left" class="<?php
   echo $StringClass;
   ?>" ><input name="HiddenReasonCode<?php
   echo $CountIndex;
   ?>" id="HiddenReasonCode<?php
   echo $CountIndex;
   ?>"  value="<?php
   echo htmlspecialchars($ReasonCodeDB);
   ?>" type="hidden"/><?php
   echo generate_select_list("ReasonCode$CountIndex", "msp_remit_codes", "", "MSP Code", "--");
   ?></td>
<td class="<?php
   echo $StringClass;
   ?>" >
   <select name='Encounter_Status<?php
      echo $CountIndex;
      ?>' id="Encounter_Status<?php
      echo $CountIndex;
      ?>">


 <?php
        $qsql      = sqlStatement("SELECT id, status,iphone_status FROM claim_status");
        $selected1 = '';
        while ($statusrow = sqlFetchArray($qsql)) {
            $claim_status    = $statusrow['status'];
            $claim_status_id = $statusrow['id'];
            
            
            if ($EncStatus == $claim_status)
                $selected1 = "selected";
            else
                $selected1 = "";
            
            
            if ($statusrow['iphone_status'] == "true")
                $disabled1 = "disabled";
            else
                $disabled1 = "";
            
            echo "<option value='$claim_status' $selected1 $disabled1>$claim_status</option>";
        }
?>
 </select>
 
<?php
        /*?><option value="Unbilled Demo Pending" <?php if ($EncStatus == 'Unbilled Demo Pending') echo ' selected="selected"'; ?> >Unbilled Demo Pending</option>
        <option value="Unbilled Insurance Pending" <?php if ($EncStatus == 'Unbilled Insurance Pending') echo ' selected="selected"'; ?> >Unbilled Insurance Pending</option>
        <option value="Unbilled rejected" <?php if ($EncStatus == 'Unbilled rejected') echo ' selected="selected"'; ?> >Unbilled rejected</option>
        <option value="Ready to send primary" <?php if ($EncStatus == 'Ready to send primary') echo ' selected="selected"'; ?> >Ready to send primary</option>
        <option value="Billed to primary" <?php if ($EncStatus == 'Billed to primary') echo ' selected="selected"'; ?> >Billed to primary</option>
        <option value="Ready to send secondary" <?php if ($EncStatus == 'Ready to send secondary') echo ' selected="selected"'; ?> >Ready to send secondary</option>
        <option value="Billed to secondary" <?php if ($EncStatus == 'Billed to secondary') echo ' selected="selected"'; ?> >Billed to secondary</option>
        <option value="Ready to send tertiary" <?php if ($EncStatus == 'Ready to send tertiary') echo ' selected="selected"'; ?> >Ready to send tertiary</option>
        <option value="Billed to tertiary" <?php if ($EncStatus == 'Billed to tertiary') echo ' selected="selected"'; ?> >Billed to tertiary</option>
        <option value="Ready to send patient" <?php if ($EncStatus == 'Ready to send patient') echo ' selected="selected"'; ?> >Ready to send patient</option>
        <option value="Billed to patient" <?php if ($EncStatus == 'Billed to patient') echo ' selected="selected"'; ?> >Billed to patient</option>
        <option value="Claim settled" <?php if ($EncStatus == 'Claim settled') echo ' selected="selected"'; ?> >Claim settled</option><?php */
?>

</td>
                <td align="center" class="<?php
        echo $StringClass;
?>" ><input type="checkbox" id="FollowUp<?php
        echo $CountIndex;
?>" 
                 name="FollowUp<?php
        echo $CountIndex;
?>" value="y" onClick="ActionFollowUp(<?php
        echo $CountIndex;
?>)"  /></td> 
                <td class="<?php
        echo $StringClass;
?> right" ><input  onKeyDown="PreventIt(event)" id="FollowUpReason<?php
        echo $CountIndex;
?>"  
                 name="FollowUpReason<?php
        echo $CountIndex;
?>"  readonly=""  type="text"   style="width:209px;font-size:12px" /></td>
                 <!--Added by gangeya to display Bill note-->
                
                 <?php
        // code change for BUG ID 10899
        $id_encounter = $ferow['id'];
?>
                <td class="<?php
        echo $StringClass;
?>" align="left" >
                 <input onkeydown="PreventIt(event)" id="BillNote<?php
        echo $CountIndex;
?>"  
                 name="BillNote<?php
        echo $CountIndex;
?>" 
                 type="text" value="" 
                 onblur="CopyContent(<?php
        echo $Encounter;
?>,<?php
        echo $CountIndex;
?>)" 
                 style="width:100px;font-size:12px" ondblclick="myAjaxBillingNotes(<?php
        echo $id_encounter;
?>)"/>
                 </td>
              </tr>
            <?php
    } while ($RowSearch = sqlFetchArray($ResultSearchNew));
?>
            <tr class="text">
                <td align="left" colspan="8">&nbsp;</td>
                <td class="left bottom" bgcolor="#6699FF" id="initialallowtotal" align="right" >0</td>
                <td class="left bottom" bgcolor="#6699FF" id="initialpaymenttotal" align="right" >0</td>
                <td class="left bottom" bgcolor="#6699FF" id="initialAdjAmounttotal" align="right" >0</td>                      
                <td class="left bottom" bgcolor="#6699FF" id="initialdeductibletotal" align="right">0</td>
                <td class="left bottom right" bgcolor="#6699FF" id="initialtakebacktotal" align="right">0</td>
                <td  align="center">&nbsp;</td>
                <td  align="center">&nbsp;</td>
              </tr>
            </table>
            <?php
} //if($RowSearch = sqlFetchArray($ResultSearchNew))
?>
           <?php
echo ($countenct);
?>
     </td>
      </tr>
      <tr>
            <td colspan="13" align="left" >
        <?php
if ($CountIndex == 0) {
    // echo "No payment Exists....";
}
?>        </td>
      </tr>
</table>
<!-- Sai custom code end -->