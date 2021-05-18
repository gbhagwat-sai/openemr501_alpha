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
?>
<script type="text/javascript">
// Sai custom code start
function displayInsCode(patientID,InsPatDropDownValue,CountIndex)
{
if(InsPatDropDownValue=='1')
{
InsPatDropDownValue='primary';
}
else if(InsPatDropDownValue=='2')
{
InsPatDropDownValue='secondary';
}
else if(InsPatDropDownValue=='3')
{
InsPatDropDownValue='tertiary';
}
else
{
document.getElementById('InsCo'+CountIndex).value = 'Patient';
return;
}

top.restoreSession();
$.ajax({
type: "POST",
url: "../../library/ajax/facility_ajax_code.php",
dataType: "html",
data: {
patientID: patientID,
InsPatDropDownValue: InsPatDropDownValue,
CountIndex : CountIndex
},
success: function(thedata3){ 

document.getElementById('InsCo'+CountIndex).value=thedata3;
document.getElementById('InsCo'+CountIndex).title=thedata3;

},
error:function(){
}
});
return;

}
function ajax_bill_loc(pid,date,facility){
top.restoreSession();
$.ajax({
type: "POST",
url: "../../../library/ajax/facility_ajax_code.php",
dataType: "html",
data: {
pid: pid,
date: date,
facility: facility
},
success: function(thedata){//alert(thedata)
$("#ajaxdiv").html(thedata);
},
error:function(){
}
});
return;

}
function ajax_code_display(code,bill_lino){
top.restoreSession();
$.ajax({
type: "POST",
url: "../../../library/ajax/facility_ajax_code.php",
dataType: "html",
data: {
code: code,
bill_lino: bill_lino
},
success: function(thedata1){ //alert(thedata1)
//$("#ajaxCPTdiv").html(thedata1);
$('#ajaxCPTdiv').append(thedata1); 
},
error:function(){
}
});
return;
}

function ajaxaddnewdiv(newrow,patid,code_type){
top.restoreSession();
$.ajax({
type: "POST",
url: "../../../library/ajax/facility_ajax_code.php",
dataType: "html",
data: {
newrow: newrow,
patid: patid,
code_type: code_type
},
success: function(thedata4){ //alert(thedata1)
//$("#ajaxCPTdiv").html(thedata1);
 $("#bale_data").append(thedata4); 
},
error:function(){
}
});
return;
}

function ajax_cpt_code_popup(cpt_code,lineno,pat_id,cd_type,modr, selctedFeeSchedule){
    top.restoreSession();
    $.ajax({
        type: "POST",
        url: "../../../library/ajax/facility_ajax_code.php",
        dataType: "html",
        data: {
            cpt_code: cpt_code,
            lineno: lineno,
            pat_id: pat_id,
            modr: modr,
            cd_type: cd_type,
            selctedFeeSchedule: selctedFeeSchedule
        },
        success: function(thedata2){   //alert(thedata2);
            n = thedata2.split(":");
            if(isNaN(n[0])){
                alert(n[0]);
                str =  "bill[" + n[1] +"][code]";
                mdr =  "bill[" + n[1] +"][mod1]";
                document.getElementById(str).value='';

                if(n[1]>104)
                document.getElementById(mdr).value='';
                document.getElementById(str).focus();
            }
            else if(n[0]>4){
                str = "bill[" + n[0] +"][units]";
                str1 = "bill[" + n[0] +"][price]";
                str2 = "bill[" + n[0] +"][total_charges]";
                str3 = "bill[" + n[0] +"][tos]";
                str4 = "bill[" + n[0] +"][cpt_eaa]"; // code added for cpt eaa amount

                document.getElementById(str).value=n[1];
                document.getElementById(str1).value=n[2];
                document.getElementById(str2).value=(n[1]*n[2]);
                document.getElementById(str3).value=(n[3]);
                
                document.getElementById(str4).value=(n[4]); // code added for cpt eaa amount
            }
        },
        error:function(){
        }
    });
    return;
}

function ajax_check_balance(chk_num,session_id){
top.restoreSession();
$.ajax({
type: "POST",
url: "../../../library/ajax/facility_ajax_code.php",
dataType: "html",
data: {
chk_num: chk_num,
session_id: session_id
},
success: function(thedata3){   //alert(thedata2);
$('#check_balance').html(thedata3); 
},
error:function(){
}
});
return;
}

function ajax_add_payment(payment_type,pt_id){
top.restoreSession();
$.ajax({
type: "POST",
url: "../../../library/ajax/facility_ajax_code.php",
dataType: "html",
data: {
payment_type: payment_type,
pt_id: pt_id
},
success: function(thedata5){  //alert(thedata5);
$('#check_number').html(thedata5); 
},
error:function(){
}
});
return;
}
// Sai custom code start
</script>
