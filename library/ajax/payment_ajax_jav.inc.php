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
//This section handles ajax functions for insurance,patient and for encounters.
//===============================================================================
?>
<script type="text/javascript">
$(document).ready(function(){	
  $("#type_code").keyup(function(e){
      if (e.which == 9 || e.which == 13)
         {//tab key,enter key.Prevent ajax activity.
          return false;
         }
        else
         {//Both insurance or patient can come.The drop down value in 'type_name' decides which one to process.
           ajaxFunction('non','Simple',document.getElementById('type_code'));
           return;
         }
  });   
  $("#patient_code").keyup(function(e){
      if (e.which == 9 || e.which == 13)
         {//tab key,enter key.Prevent ajax activity.
          return false;
         }
        else
         {
           ajaxFunction('patient','Submit',document.getElementById('patient_code'));
           return;
         }
  });   
  // Sai custom code start
   $("#facility_code").keyup(function(e){
	  if (e.which == 9 || e.which == 13)
		 {//tab key,enter key.Prevent ajax activity.
		  return false;
		 }
		else
		 {
		   ajaxFunction('facility','Submit',document.getElementById('facility_code'));
		   return;
		 }
  });
  $("#cpt_code").keyup(function(e){
	  if (e.which == 9 || e.which == 13)
		 {//tab key,enter key.Prevent ajax activity.
		  return false;
		 }
		else
		 {
		   ajaxFunction('cpt','Submit',document.getElementById('cpt_code'));
		   return;
		 }
  });
  $("#icd9_code").keyup(function(e){
	  if (e.which == 9 || e.which == 13)
		 {//tab key,enter key.Prevent ajax activity.
		  return false;
		 }
		else
		 {
		   ajaxFunction('icd9','Submit',document.getElementById('icd9_code'));
		   return;
		 }
  });
   // Sai custom code end
  $("#form_pt_name").keyup(function(e){
      if (e.which == 9 || e.which == 13)
         {//tab key,enter key.Prevent ajax activity.
          return false;
         }
        else
         {
           ajaxFunction('patient','Simple',document.getElementById('form_pt_name'));
           return;
         }
  });   
  $("#encounter_no").keyup(function(e){
      if (e.which == 9 || e.which == 13)
         {//tab key,enter key.Prevent ajax activity.
          return false;
         }
        else
         {
		   ajaxFunction('encounter','Submit',document.getElementById('enc_number')); // Sai custom code
           return;
         }
  });   
 
 
   function clearpaymentFields(){
// Code chnage by MK to fix edit payment issue
if(document.getElementById('CountIndexAbove') != null){
		for(var i= 1; i<= document.getElementById('CountIndexAbove').value;i++){
		var HiddenEncounter = 'HiddenEncounter';
		document.getElementById(HiddenEncounter+i).remove();
		
		var HiddenCodetype = 'HiddenCodetype';
		document.getElementById(HiddenCodetype+i).remove();
		
		var HiddenCode = 'HiddenCode';
		document.getElementById(HiddenCode+i).remove();
		
		var HiddenModifier = 'HiddenModifier';
		document.getElementById(HiddenModifier+i).remove();
		
		var HiddenChargeAmount = 'HiddenChargeAmount';
		document.getElementById(HiddenChargeAmount+i).remove();
		
		var HiddenCopayAmount = 'HiddenCopayAmount';
		document.getElementById(HiddenCopayAmount+i).remove();
		
		var Allowed = 'Allowed';
		document.getElementById(Allowed+i).remove();
		
		var Payment = 'Payment';
		document.getElementById(Payment+i).remove();
		
		var AdjAmount = 'AdjAmount';
		document.getElementById(AdjAmount+i).remove();
		
		var Takeback = 'Takeback';
		document.getElementById(Takeback+i).remove();
		
		var HiddenReasonCode = 'HiddenReasonCode';
		document.getElementById(HiddenReasonCode+i).remove();
		
		var ReasonCode = 'ReasonCode';
		document.getElementById(ReasonCode+i).remove();
		
		var FollowUpReason = 'FollowUpReason';
		document.getElementById(FollowUpReason+i).remove();
		
		var HiddenRemainderTd = 'HiddenRemainderTd';
		document.getElementById(HiddenRemainderTd+i).remove();
		
		var Deductible = 'Deductible';
		document.getElementById(Deductible+i).remove();
		
		var payment_ins = 'payment_ins';
		document.getElementById(payment_ins+i).remove();
		
		var HiddenIns = 'HiddenIns';
		document.getElementById(HiddenIns+i).remove();
		
		var HiddenPId = 'HiddenPId';
		document.getElementById(HiddenPId+i).remove();
	}
	}
  }
  	// code changes end.
   // Sai custom code start
  $("#enc_search").click(function(e){	
	// Code chnage by MK to fix edit payment issue
	clearpaymentFields();
	ajaxFunction('enc_search','Submit',document.getElementById('enc_number'));		   
	// code changes end.
  });
   // Sai custom code end
 
 
  function ajaxFunction(Source,SubmitOrSimple,SourceObject) {
	  //alert2
  if(Source=='encounter')
   {
      document.getElementById('ajax_mode').value='encounter';
   }
  else if(Source=='patient')
   {
      if(SourceObject.value.length<3)
       return false;
      document.getElementById('ajax_mode').value='set_patient';
 // Sai custom code start
	  
   }
   else if(Source=='facility')
   {
	  if(SourceObject.value.length<1)
	   return false;
	  document.getElementById('ajax_mode').value='set_facility';
   }
   else if(Source=='cpt')
   {
	  if(SourceObject.value.length<1)
	   return false;
	  document.getElementById('ajax_mode').value='set_cpt';
   }
   else if(Source=='icd9')
   {
	  if(SourceObject.value.length<1)
	   return false;
	  document.getElementById('ajax_mode').value='set_icd9';
   }
   else if(Source=='enc_search'){  
	//alert("alert3");
    document.getElementById('ajax_mode').value='set_enc_search';
  // Sai custom code end
   }
   //For the below two cases, same text box is used for both insurance and patient.
  else if(document.getElementById('type_name') && document.getElementById('type_name').options[document.getElementById('type_name').selectedIndex].value=='patient')
   {//Patient
      if(document.getElementById('type_code').value.length<3)
       return false;
      document.getElementById('ajax_mode').value='set_patient';
   }
  else
   {//Insurance
      if(document.getElementById('type_code').value.length<3)
       return false;
      document.getElementById('ajax_mode').value='set';
   }
//Send ajax request
 // Sai custom code start
   $.ajax({
    type: "POST",
    url:  Source=='facility' || Source=='cpt' || Source=='icd9' ? "../../../library/ajax/payment_ajax.php" : "../../library/ajax/payment_ajax.php",
    dataType: "html",
    data: {
     ajax_mode: document.getElementById('ajax_mode').value,
     patient_code: Source=='patient' ? SourceObject.value : '',
    insurance_text_ajax: document.getElementById('type_code') ? document.getElementById('type_code').value : '',
    encounter_patient_code:Source=='encounter' ? document.getElementById('hidden_patient_code').value : '',
	facility_code:Source=='facility' ? document.getElementById('facility_code').value : '',
	cpt_code:Source=='cpt' ? document.getElementById('cpt_code').value : '',
	icd9_code:Source=='icd9' ? document.getElementById('icd9_code').value : '',
	submit_or_simple_type:SubmitOrSimple,
	ins_code:document.getElementById('ins_code') ? document.getElementById('ins_code').value : '',
	enc_search:document.getElementById('enc_number') ? document.getElementById('enc_number').value : '',
   },
   //async: false,
    success: function(thedata){
    if(Source=='encounter')
     {
         ;
     }
    else
     {
		// alert("alert4");
        ThedataArray=thedata.split('~`~`');
        thedata=ThedataArray[1];
		if(Source=='patient' || Source=='facility' || Source=='cpt' || Source=='icd9' )
         {
           if(ThedataArray[0]!=SourceObject.value.length)
            {
             return;//To deal with speedy typing.
            }
		 }	
		 else if(Source == 'enc_search')	{
		  //  alert("alert5");
		   thedata=ThedataArray[0];
		   thedata1=ThedataArray[1];
			 //return;//To deal with speedy typing.
 // Sai custom code end			
         }
        else
         {
           if(ThedataArray[0]!=document.getElementById('type_code').value.length)
            {
             return;//To deal with speedy typing.
            }
         }
     }
    document.getElementById('ajax_mode').value='';
      if(Source=='encounter')
       {
         if(document.getElementById('SelFacility'))
          {
            document.getElementById('SelFacility').style.display='none';//In Internet explorer this drop down comes over the ajax listing.
          }
         $("#ajax_div_encounter_error").empty();
         $("#ajax_div_encounter").empty();
         $("#ajax_div_encounter").html(thedata);
         $("#ajax_div_encounter").show();
       }
      else if(Source=='patient')
       {
         if(document.getElementById('SelFacility'))
          {
            document.getElementById('SelFacility').style.display='none';//In Internet explorer this drop down comes over the ajax listing.
          }
         $("#ajax_div_patient_error").empty();
         $("#ajax_div_patient").empty();
         $("#ajax_div_insurance_error").empty();
         $("#ajax_div_insurance").empty();
         $("#ajax_div_patient").html(thedata);
         $("#ajax_div_patient").show();
  // Sai custom code start
	   }
	    else if(Source=='facility')
	   {
		 if(document.getElementById('facility_code'))
		  {
			 document.getElementById('facility_code').style.display='block';//In Internet explorer this drop down comes over the ajax listing.
		  }
	
		 $("#ajax_div_insurance_error").empty();
		 $("#ajax_div_insurance").empty();
		 $("#ajax_div_facility").html(thedata);
		 $("#ajax_div_facility").show();
	   }
	   else if(Source=='cpt')
	   {
		 if(document.getElementById('cpt_code'))
		  {
			 document.getElementById('cpt_code').style.display='block';//In Internet explorer this drop down comes over the ajax listing.
		  }
		
		 $("#ajax_div_cpt").html(thedata);
		 $("#ajax_div_cpt").show();
	   }
	   else if(Source=='icd9')
	   {
		 if(document.getElementById('icd9_code'))
		  {
			 document.getElementById('icd9_code').style.display='block';//In Internet explorer this drop down comes over the ajax listing.
		  }
		
		 $("#ajax_div_icd9").html(thedata);
		 $("#ajax_div_icd9").show();
	   }
	   else if(Source=='enc_search'){
		  console.log('enc search');
		  document.getElementById('patient_code').value=thedata;
		  document.getElementById('hidden_ajax_patient_close_value').value=thedata;
		  document.getElementById('hidden_patient_code').value=thedata1;
		  document.getElementById('patient_name').innerHTML=thedata1;
		  document.getElementById('ajax_div_patient').style.display='none';
		  document.getElementById('patient_name').focus();
		  document.getElementById('mode').value='search';
		  top.restoreSession();
		  document.forms[0].submit();
   // Sai custom code end
       }
      else
       {//Patient or Insurance
         $("#ajax_div_patient_error").empty();
         $("#ajax_div_patient").empty();
         $("#ajax_div_insurance_error").empty();
         $("#ajax_div_insurance").empty();
         $("#ajax_div_insurance").html(thedata);
         $("#ajax_div_insurance").show();
       }
    if(document.getElementById('anchor_insurance_code_1'))
        document.getElementById('anchor_insurance_code_1').focus();
    if(document.getElementById('tr_insurance_1'))
        document.getElementById('tr_insurance_1').bgColor='#94D6E7'//selected color
    },
    error:function(){
    }   
   });
   return;      
  }
 });
//==============================================================================================================================================
//Following functions are needed for other tasks related to ajax.
//Html retured from the ajax above, contains list of either insurance,patient or encounter.
//On click or 'enter key' press over any one item the listing vanishes and the clicked one gets listed in the parent page's text box.
//List of functions starts
//===========================================================
function PutTheValuesClick(Code,Name)
 {//Used while -->CLICK<-- over list in the insurance/patient portion.
  document.getElementById('type_code').value=Name;
  document.getElementById('hidden_ajax_close_value').value=Name;
  document.getElementById('description').value=Name;
  document.getElementById('hidden_type_code').value=Code;
  document.getElementById('div_insurance_or_patient').innerHTML=Code;
  document.getElementById('ajax_div_insurance').style.display='none';
     $("#ajax_div_patient_error").empty();
     $("#ajax_div_patient").empty();
     $("#ajax_div_insurance_error").empty();
     $("#ajax_div_insurance").empty();
  document.getElementById('type_code').focus();
 }
  // Sai custom code start
 function PutTheValuesClickFacility(Code,Name)
 {//Used while -->CLICK<-- over list in the facility portion.
 document.getElementById('facility_code').value=Name;
 document.getElementById('hidden_ajax_close_value').value=Name;
  document.getElementById('facility_id').value=Code;
	/*var dd = document.getElementById('facility_id');
        for (var i = 0; i < dd.options.length; i++) 
        {
		//alert(dd.options[i].text+"===="+Name); 
            if (dd.options[i].text == Name) 
            {            			    
				 dd.options[i].defaultSelected = true;
				 dd.selectedIndex = i;
                break;
            }
        }*/

  document.getElementById('ajax_div_facility').style.display='none';
   $("#ajax_div_insurance_error").empty();
 // $("#ajax_div_insurance").empty();
 document.getElementById('facility_code').focus();
 }
 function PutTheValuesClickCPT(Code,Key)
 {//Used while -->CLICK<-- over list in the facility portion. 
 document.getElementById('cpt_code').value=Code;
 document.getElementById('hidden_ajax_close_value').value=Code;
  document.getElementById('ajax_div_cpt').style.display='none';
  //$("#ajax_div_insurance_error").empty();
  //$("#ajax_div_insurance").empty();
 document.getElementById('cpt_code').focus();
 }
 function PutTheValuesClickICD9(Code,Key)
 {//Used while -->CLICK<-- over list in the facility portion. 
 document.getElementById('icd9_code').value=Code;
 document.getElementById('hidden_ajax_close_value').value=Code;
  document.getElementById('ajax_div_icd9').style.display='none';
  //$("#ajax_div_insurance_error").empty();
  //$("#ajax_div_insurance").empty();
 document.getElementById('icd9_code').focus();
 }
 function check_het_enc()
 {
 user_id= document.getElementById('enc_number').value;
  ajaxFunction('enc_search',user_id);
 }
  // Sai custom code end
function PutTheValuesClickDistribute(Code,Name)
 {//Used while -->CLICK<-- over list in the patient portion before the start of distribution of amount.
 if(document.getElementById('SelFacility'))
  {
    document.getElementById('SelFacility').style.display='';//In Internet explorer this drop down comes over the ajax listing.
  }
  document.getElementById('patient_code').value=Name;
  document.getElementById('hidden_ajax_patient_close_value').value=Name;
  document.getElementById('hidden_patient_code').value=Code;
  document.getElementById('patient_name').innerHTML=Code;
  document.getElementById('ajax_div_patient').style.display='none';
  document.getElementById('patient_name').focus();
    document.getElementById('mode').value='search';
    top.restoreSession();
    document.forms[0].submit();
 }
function PutTheValuesClickPatient(Code,Name)//Non submission patient ajax.
 {
  document.getElementById('form_pt_name').value=Name;
  document.getElementById('hidden_ajax_patient_close_value').value=Name;
  document.getElementById('hidden_patient_code').value=Code;
  document.getElementById('ajax_div_patient').style.display='none';
  document.getElementById('form_pt_code').innerHTML=Code;
  document.getElementById('form_pt_name').focus();
 }
function PutTheValuesClickEncounter(Code,Name)
 {//Used while -->CLICK<-- over list in the encounter portion.
 if(document.getElementById('SelFacility'))
  {
    document.getElementById('SelFacility').style.display='';//In Internet explorer this drop down comes over the ajax listing.
  }
  document.getElementById('encounter_no').value=Code;
  document.getElementById('hidden_ajax_encounter_close_value').value=Code;
  document.getElementById('hidden_encounter_no').value=Code;
  document.getElementById('encounter_date').innerHTML=Name;
  document.getElementById('ajax_div_encounter').style.display='none';
  document.getElementById('encounter_date').focus();
    document.getElementById('mode').value='search_encounter';
    top.restoreSession();
    document.forms[0].submit();
 }
function PlaceValues(evt,Code,Name)
 {//Used while -->KEY PRESS<-- over list in the insurance/patient portion.
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode == 13)//enter key
     {//Vanish the list and populate the parent text box
      PutTheValuesClick(Code,Name);
      PreventIt(evt)  //For browser chorome.It gets submitted,to prevent it the PreventIt(evt) is written
     }
    else if(!((charCode == 38) || (charCode == 40)))
     {//if non arrow keys, focus on the parent text box(ie he again types and wants ajax to activate)
      document.getElementById('type_code').focus();
     }
 }
function PlaceValuesDistribute(evt,Code,Name)
 {//Used while -->KEY PRESS<-- over list in the patient portion before the start of distribution of amount.
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode == 13)//enter key
     {//Vanish the list and populate the parent text box
      PutTheValuesClickDistribute(Code,Name);
      PreventIt(evt)  //For browser chorome.It gets submitted,to prevent it the PreventIt(evt) is written  
     }
    else if(!((charCode == 38) || (charCode == 40)))
     {//if non arrow keys, focus on the parent text box(ie he again types and wants ajax to activate)
      document.getElementById('patient_code').focus();
     }
 }
function PlaceValuesPatient(evt,Code,Name)
 {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode == 13)//enter key
     {//Vanish the list and populate the parent text box
      PutTheValuesClickPatient(Code,Name);
      PreventIt(evt)  //For browser chorome.It gets submitted,to prevent it the PreventIt(evt) is written  
     }
    else if(!((charCode == 38) || (charCode == 40)))
     {//if non arrow keys, focus on the parent text box(ie he again types and wants ajax to activate)
      document.getElementById('form_pt_name').focus();
     }
 }
function PlaceValuesEncounter(evt,Code,Name)
 {//Used while -->KEY PRESS<-- over list in the encounter portion.
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode == 13)//enter key
     {//Vanish the list and populate the parent text box
      PutTheValuesClickEncounter(Code,Name);
      PreventIt(evt)  //For browser chorome.It gets submitted,to prevent it the PreventIt(evt) is written
     }
    else if(!((charCode == 38) || (charCode == 40)))
     {//if non arrow keys, focus on the parent text box(ie he again types and wants ajax to activate)
      document.getElementById('encounter_no').focus();
  // Sai custom code start
	 }
 }
 function PlaceValuesFacility(evt,Code,Name)
 {//Used while -->KEY PRESS<-- over list in the encounter portion.
	evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode == 13)//enter key
	 {//Vanish the list and populate the parent text box
	  PutTheValuesClickFacility(Code,Name);
	  PreventIt(evt)  //For browser chorome.It gets submitted,to prevent it the PreventIt(evt) is written
	 }
	else if(!((charCode == 38) || (charCode == 40)))
	 {//if non arrow keys, focus on the parent text box(ie he again types and wants ajax to activate)
	  document.getElementById('facility_code').focus();
	 }
 }
 function PlaceValuesCPT(evt,Code,Name)
 {//Used while -->KEY PRESS<-- over list in the encounter portion.
	evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode == 13)//enter key
	 {//Vanish the list and populate the parent text box
	  PutTheValuesClickCPT(Code,Name);
	  PreventIt(evt)  //For browser chorome.It gets submitted,to prevent it the PreventIt(evt) is written
	 }
	else if(!((charCode == 38) || (charCode == 40)))
	 {//if non arrow keys, focus on the parent text box(ie he again types and wants ajax to activate)
	  document.getElementById('cpt_code').focus();
	 }
 }
 function PlaceValuesICD9(evt,Code,Name)
 {//Used while -->KEY PRESS<-- over list in the encounter portion.
	evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode == 13)//enter key
	 {//Vanish the list and populate the parent text box
	  PutTheValuesClickICD9(Code,Name);
	  PreventIt(evt)  //For browser chorome.It gets submitted,to prevent it the PreventIt(evt) is written
	 }
	else if(!((charCode == 38) || (charCode == 40)))
	 {//if non arrow keys, focus on the parent text box(ie he again types and wants ajax to activate)
	  document.getElementById('icd9_code').focus();
  // Sai custom code end
     }
 }
function ProcessKeyForColoring(evt,Location)
 {//Shows the navigation in the listing by change of colors and focus.Happens when down or up arrow is pressed.
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode == 38)//Up key press
     {
        Location--;
        if(document.getElementById('tr_insurance_' + (Location)))
         {
            //restore color in below row
            if((Location+1)%2==1)
             {
             document.getElementById('tr_insurance_' + (Location+1)).bgColor='#ddddff';
             }
            else
             {
             document.getElementById('tr_insurance_' + (Location+1)).bgColor='#ffdddd';
             }
            document.getElementById('tr_insurance_' + (Location)).bgColor='#94D6E7';
            document.getElementById('anchor_insurance_code_' + (Location)).focus();
         }
     }
    else if (charCode == 40)//Down key press
     {
        Location++;
        if(document.getElementById('tr_insurance_' + (Location)))
         {
            //restore color in above row
             if((Location-1)%2==1)
             {
             document.getElementById('tr_insurance_' + (Location-1)).bgColor='#ddddff';
             }
            else
             {
             document.getElementById('tr_insurance_' + (Location-1)).bgColor='#ffdddd';
             }
            document.getElementById('tr_insurance_' + (Location)).bgColor='#94D6E7';
            document.getElementById('anchor_insurance_code_' + (Location)).focus();
         }
     }
 }
function HideTheAjaxDivs()
 {//Starts working when clicking on the body.Hides the ajax and restores the codes back, as he may have changed it in the text box.
  if(document.getElementById('ajax_div_insurance'))
   {
      if(document.getElementById('ajax_div_insurance').style.display!='none')
       {
          document.getElementById('type_code').value=document.getElementById('hidden_ajax_close_value').value;
         $("#ajax_div_patient_error").empty();
         $("#ajax_div_patient").empty();
         $("#ajax_div_insurance_error").empty();
         $("#ajax_div_insurance").empty();
         $("#ajax_div_insurance").hide();
       }
   }
  if(document.getElementById('ajax_div_patient'))
   {
      if(document.getElementById('ajax_div_patient').style.display!='none')
       {
         if(document.getElementById('SelFacility'))
          {
            document.getElementById('SelFacility').style.display='';//In Internet explorer this drop down comes over the ajax listing.
          }
          if(document.getElementById('patient_code'))
            document.getElementById('patient_code').value=document.getElementById('hidden_ajax_patient_close_value').value;
          else if(document.getElementById('form_pt_name'))
            document.getElementById('form_pt_name').value=document.getElementById('hidden_ajax_patient_close_value').value;
         $("#ajax_div_patient_error").empty();
         $("#ajax_div_patient").empty();
         $("#ajax_div_insurance_error").empty();
         $("#ajax_div_insurance").empty();
         $("#ajax_div_patient").hide();
       }
   }
  if(document.getElementById('ajax_div_encounter'))
   {
      if(document.getElementById('ajax_div_encounter').style.display!='none')
       {
         if(document.getElementById('SelFacility'))
          {
            document.getElementById('SelFacility').style.display='';//In Internet explorer this drop down comes over the ajax listing.
          }
          document.getElementById('encounter_no').value=document.getElementById('hidden_ajax_encounter_close_value').value;
         $("#ajax_div_encounter_error").empty();
         $("#ajax_div_encounter").empty();
         $("#ajax_div_encounter").hide();
       }
   }
 }
//===========================================================
//List of functions ends
//==============================================================================================================================================
</script>
