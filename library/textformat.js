// Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// Onkeyup handler for dates.  Converts dates that are keyed in to a
// consistent format, and helps to reduce typing errors.
//
// sai custom code start
function ssnLength(ssn)
{
  if (ssn.length >=1)
  {

    if(ssn.length != 11)
    {
         alert('Invalid SSN, SSN Format should be 999-99-9999');
          document.frmPatEditIns.txtSSN.focus();

    }
  }
}

function limitText(field, maxChar){
    var ref = $(field),
        val = ref.val();
    if ( val.length >= maxChar ){
        ref.val(function() {
            console.log(val.substr(0, maxChar))
            return val.substr(0, maxChar);       
        });
    }
}

function jm_ssnmask(t)
{
         
  
var patt = /(\d{3}).*(\d{2}).*(\d{4})/;
var donepatt = /^(\d{3})-(\d{2})-(\d{4})$/;
var str = t.value;
var result;
  if (!str.match(donepatt))
  {
    result = str.match(patt);
    if (result!= null)
    {
        t.value = t.value.replace(/[^\d]/gi,'');
      str = result[1] + '-' + result[2] + '-' + result[3];
      t.value = str;
    }else{
      if (t.value.match(/[^\d]/gi))
      t.value = t.value.replace(/[^\d]/gi,'');
    }
    }
}

function datekeyup(obj,xyz) {
	str=obj.value;
	str=str.replace(/[^\d]/gi,'');
	obj.value=str;
	obj.focus();
	return;
}

// Onblur handler to avoid incomplete entry of dates.
//

function dateblur(obj,pattern,curr_date) {
 //Pattern
 
 if(pattern == 1)
 {
	 pattern= 2;
	 }
	//1=(mm/dd/yyyy)(mm/yyyy)(yyyy)
	//2=(mm/dd/yyyy)
	//3=(mm/yyyy)
	//4=(yyyy)
	if(obj.value=="")
	{
		return;
	}
	var str = obj.value;
	//str=str.replace(/[^\d]/gi,'');
	//alert(":"+str);
	//CHECK FOR PATTERN 1
	if(str.length != 8 )
	{
		
		var validformat=/^\d{2}\/\d{2}\/\d{4}$/ //Basic check for format validity
		var returnval=false
		if (!validformat.test(obj.value))
		{
alert("Please enter Date in (MMDDYYYY) format.")
obj.value="";
obj.focus();
return;
		}
//else{ //Detailed check for valid date ranges
//var monthfield=input.value.split("/")[0]
//var dayfield=input.value.split("/")[1]
//var yearfield=input.value.split("/")[2]
//var dayobj = new Date(yearfield, monthfield-1, dayfield)
//if ((dayobj.getMonth()+1!=monthfield)||(dayobj.getDate()!=dayfield)||(dayobj.getFullYear()!=yearfield))
//alert("Invalid Day, Month, or Year range detected. Please correct and submit again.")
//		
//}
		//if (confirm('Please enter Date in (MM/DD/YYYY) format..........')) 
//		{
//   obj.value="";
//	obj.focus();
//} else {
//    alert('Why did you press cancel? You should have confirmed');
//}
		//alert("Please enter Date in (MMDDYYYY) format");
//		obj.value="";
//		obj.focus();
//		return;
		
		}
	
	//alert(str);
	if(pattern==1 && (str.length==5 || str.length>10 ||(!isNaN(str) && str.length>8)))
	{
		alert("Invalid Date");
		obj.value="";
		obj.focus();
		return;
	}
	else if((pattern==4 && str.length!=4) || (pattern==3 && (str.length<6 || str.length>7)) || (pattern==2 && (str.length<8 || str.length>10)))
	{
		alert("Invalid Date");
		obj.value="";
		obj.focus();
		return;
	}
	//check PATTERN wise LENGTH
	//var frmName=eval("document."+frmNM);
	//alert(frmNM+" : "+obj.value);
	var donepatt = /^(\d{2})\/(\d{2})\/(\d{4})$/;
	var patt = /(\d{2}).*(\d{2}).*(\d{4})/;		//mm-dd-yyyy
	var patt1= /(\d{2}).*(\d{4})/;			//mm-yyyy
	var patt2= /(\d{4})/;				//yyyy

	result0 = str.match(donepatt);
	//alert(result0);
	if (!result0!=null)
	{
		result = str.match(patt);
		result1 = str.match(patt1);
		result2 = str.match(patt2);
		//alert(result0+" : "+result+" : "+result1+" : "+result2);
		if (result!= null || result1!=null || result2!=null)
		{
			obj.value = obj.value.replace(/[^\d]/gi,'');
			if (result !=null)
			{
				str = result[1] + '/' + result[2] + '/' + result[3];

				//alert("MM/DD/YYYY "+str);
				if(!chkMM(result[1]))
				{
					obj.value = "";
					obj.focus();
					return;
				}
				
				if(!chkYYYY(result[3]))
				{
					obj.value = "";
					obj.focus();
					return;
				}
				
				if(!chkDD(result[1],result[2],result[3]))
				{
					obj.value = "";
					obj.focus();
					return;
				}
				var today = new Date(curr_date);
				var acceptedDate= new Date(str);
				if(today < acceptedDate)
				{
					alert('Future date is not acceptable');
					obj.value = "";
					obj.focus();
					return;
				}
				//alert(today);
//				alert(acceptedDate);
				obj.value = str;
			}
			else if (result1 !=null)
			{
				//alert("MM/YYYY");
				str = result1[1] + '/' + result1[2];

				if(!chkMM(result1[1]))
				{
					obj.value = "";
					obj.focus();
					return;
				}
				
				if(!chkYYYY(result1[2]))
				{
					obj.value = "";
					obj.focus();
					return;
				}
				obj.value = str;
			}
			else if (result2 !=null)
			{
				//alert("YYYY");
				str = result2[1]
				if(!chkYYYY(result2[1]))
				{
					obj.value = "";
					obj.focus();
					return;
				}
				obj.value = str;
			}
		}
	}
	else
	{
		//alert("R0");
		if (obj.value.match(/[^\d]/gi))
		str = obj.value.replace(/[^\d]/gi,'');
		if (result0 !=null)
		{
			if(!chkMM(result0[1]))
			{
				obj.value = "";
				obj.focus();
				return;
			}
			
			if(!chkYYYY(result0[3]))
			{
				obj.value = "";
				obj.focus();
				return;
			}
			
			if(!chkDD(result0[1],result0[2],result0[3]))
			{
				obj.value = "";
				obj.focus();
				return;
			}

			obj.value = str;
		}
	}
		if (result0== null && result== null && result1==null && result2==null)
		{
			//alert(result0+" : "+result+" : "+result1+" : "+result2);
			alert("Invalid Date");
			obj.value = "";
			obj.focus();
			return false
		}
	if((pattern==2 && str.length!=10) || (pattern==3 && str.length!=7))
	{
		alert("Invalid Date");
		obj.value="";
		obj.focus();
		return;
	}
	
}
function chkDD(mm,dd,yyyy)
{//leap year
	if (dd<1 || dd>31)//DATE
	{
		alert("Invalid Day");
		//obj.value = "";
		return false;
	}
	else
	{
		if ((yyyy % 4) == 0)
		{
			if (dd > 29 && parseInt(mm)==2)
			{
				alert("Invalid Day");
				return false;
			}
		}
		else
		{
			if (dd > 28 && parseInt(mm)==2)
			{
				alert("Invalid Day");
				return false;
			}
		}
		//MONTH VALIDATION FOR 30 & 31
		if(dd>31 && (mm==1 || mm==3 || mm==5 || mm==7 || mm==8 || mm==10 || mm==12))
		{
			alert("Invalid Day");
			return false;
		}
		if(dd>30 && (mm==4 || mm==6 || mm==9 || mm==11)) 
		{
			alert("Invalid Day");
			return false;
		}
	}
	return true;
}
function chkMM(mm)
{
	if(mm<1 || mm>12) //MONTH
	{
		alert("Invalid Month");
		return false;
	}
	return true;
}
function chkYYYY(yyyy)

{
	if(yyyy<=0 || yyyy.length!=4) //YEAR
	{
		alert("Invalid Year");
		return false;
	}
	return true;
}



// Private subroutine for US phone number formatting.
function usphone(v) {
 if (v.length > 0 && v.charAt(0) == '-') v = v.substring(1);
 var oldlen = v.length;
 for (var i = 0; i < v.length; ++i) {
  var c = v.charAt(i);
  if (c < '0' || c > '9') {
   v = v.substring(0, i) + v.substring(i + 1);
   --i;
  }
 }
 if (oldlen > 3 && v.length >= 3) {
  v = v.substring(0, 3) + '-' + v.substring(3);
  if (oldlen > 7 && v.length >= 7) {
   v = v.substring(0, 7) + '-' + v.substring(7);
   if (v.length > 12) v = v.substring(0, 12);
  }
 }
 return v;
}

// Private subroutine for non-US phone number formatting.
function nonusphone(v) {
 for (var i = 0; i < v.length; ++i) {
  var c = v.charAt(i);
  if (c < '0' || c > '9') {
   v = v.substring(0, i) + v.substring(i + 1);
   --i;
  }
 }
 return v;
}

// Telephone country codes that are exactly 2 digits.
var twodigitccs = '/20/30/31/32/33/34/36/39/40/41/43/44/45/46/47/48/49/51/52/53/54/55/56/57/58/60/61/62/63/64/65/66/81/82/84/86/90/91/92/93/94/95/98/';

// Onkeyup handler for phone numbers.  Helps to ensure a consistent
// format and to reduce typing errors.  defcc is the default telephone
// country code as a string.
//
function phonekeyup(e, defcc) {
 var v = e.value;
 var oldlen = v.length;

 // Deal with international formatting.
 if (v.length > 0 && v.charAt(0) == '+') {
  var cc = '';
  for (var i = 1; i < v.length; ++i) {
   var c = v.charAt(i);
   if (c < '0' || c > '9') {
    v = v.substring(0, i) + v.substring(i + i);
    --i;
    continue;
   }
   cc += c;
   if (i == 1 && oldlen > 2) {
    if (cc == '1') { // USA
     e.value = '+1-' + usphone(v.substring(2));
     return;
    }
    if (cc == '7') { // USSR
     e.value = '+7-' + nonusphone(v.substring(2));
     return;
    }
   }
   else if (i == 2 && oldlen > 3) {
    if (twodigitccs.indexOf(cc) >= 0) {
     e.value = v.substring(0, 3) + '-' + nonusphone(v.substring(3));
     return;
    }
   }
   else if (i == 3 && oldlen > 4) {
    e.value = v.substring(0, 4) + '-' + nonusphone(v.substring(4));
    return;
   }
  }
  e.value = v;
  return;
 }

 if (defcc == '1') {
  e.value = usphone(v);
 } else {
  e.value = nonusphone(v);
 }

 return;
}

// onKeyUp handler for mask-formatted fields.
// This feature is experimental.
function maskkeyup(elem, mask) {
 if (!mask || mask.length == 0) return;
 var i = 0; // elem and mask index
 var v = elem.value;
 for (; i < mask.length && i < v.length; ++i) {
  var ec = v.charAt(i);
  var mc = mask.charAt(i);
  if (mc == '#' && (ec < '0' || ec > '9')) {
   // digit required but this is not one
   break;
  }
  if (mc == '@' && ec.toLowerCase() == ec.toUpperCase()) {
   // alpha character required but this is not one
   break;
  }
 }
 v = v.substring(0, i);
 while (i < mask.length) {
  var mc = mask.charAt(i++);
  if (mc == '*' || mc == '#' || mc == '@') break;
  v += mc;
 }
 elem.value = v;
}

// onBlur handler for mask-formatted fields.
// This feature is experimental.
function maskblur(elem, mask) {
 var v = elem.value;
 var i = mask.length;
 if (i > 0 && v.length > 0 && v.length != i) {
  // there is a mask and a value but the value is not long enough
  for (; i > 0 && mask.charAt(i-1) == '#'; --i);
  // i is now index to first # in # string at end of mask
  if (i > v.length) {
   // value is too short even if trailing digits in the mask are ignored
   if (confirm('Field entry is incomplete! Try again?'))
    elem.focus();
   else
    elem.value = '';
   return;
  }
  // if the mask ends with digits then right-justify them in the value
  while (v.length < mask.length) {
   v = v.substring(0, i) + '0' + v.substring(i, v.length);
  }
  elem.value = v;
 }
}

// sai custom code start
function masterkeypress(evt)
{
  evt = evt || window.event;
    if (evt.keyCode == 78 && evt.altKey) {
        top.window.parent.left_nav.loadFrame2('new0','RTop','new/new.php');
    }
  else if (evt.keyCode == 110 && evt.altKey) 
  {
        top.window.parent.left_nav.loadFrame2('new0','RTop','new/new.php');
    }
  else if (evt.keyCode == 85 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','usergroup/usergroup_admin.php');
  }
  else if (evt.keyCode == 117 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','usergroup/usergroup_admin.php');
  }
  else if (evt.keyCode == 73 && evt.altKey)
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','../controller.php?practice_settings&insurance_company&action=edit');
  }
  else if (evt.keyCode ==  105 && evt.altKey)
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','../controller.php?practice_settings&insurance_company&action=edit');
  }
  else if (evt.keyCode == 74 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/new.php?autoloaded=1&calenc=');
  }
  else if (evt.keyCode == 106 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/new.php?autoloaded=1&calenc=');
  } 
  else if (evt.keyCode == 75 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('adm0','RTop','usergroup/facilities.php');
  } 
  else if (evt.keyCode == 107 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('adm0','RTop','usergroup/facilities.php');
  }
  else if (evt.keyCode == 76 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('ens1','RTop','patient_file/history/encounters.php');
  }
  else if (evt.keyCode == 108 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('ens1','RTop','patient_file/history/encounters.php');
  }
  else if (evt.keyCode == 77 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('cal0','RTop','main/main_info.php');
  }
  else if (evt.keyCode == 109 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('cal0','RTop','main/main_info.php');
  }
  else if (evt.keyCode == 79 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','billing/search_payments.php');
  }
  else if (evt.keyCode == 111 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','billing/search_payments.php');
  }
  else if (evt.keyCode == 80 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','billing/new_payment.php');
  }
  else if (evt.keyCode == 112 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','billing/new_payment.php');
  }
  else if (evt.keyCode == 81 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('dem1','RTop','patient_file/summary/demographics.php');
  }
  else if (evt.keyCode == 113 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('dem1','RTop','patient_file/summary/demographics.php');
  }
  else if (evt.keyCode == 82 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('bil0','RTop','billing/billing_report.php');
  }
  else if (evt.keyCode == 114 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('bil0','RTop','billing/billing_report.php');
  }
  if (evt.keyCode == 88 && evt.altKey) 
  {
    deleteme();
  } 
  if (evt.keyCode == 120 && evt.altKey) 
  {
    deleteme();
  }
  else 
  {
    return true; // it's not a key we recognize, move on...
  }
  return false;
}

// sai custom code end