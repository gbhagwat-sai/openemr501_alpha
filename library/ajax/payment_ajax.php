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
//This section handles ajax for insurance,patient and for encounters.
//===============================================================================
require_once("../../interface/globals.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formatting.inc.php");
//=================================
if (isset($_REQUEST["ajax_mode"])) {
    AjaxDropDownCode();
}

//=================================
function AjaxDropDownCode()
{
    if ($_REQUEST["ajax_mode"] == "set") {//insurance
        $CountIndex=1;
        $StringForAjax="<div id='AjaxContainerInsurance'><table width='552' border='1' cellspacing='0' cellpadding='0'>
	  <tr class='text' bgcolor='#dddddd'>
		<td width='50'>".htmlspecialchars(xl('Code'), ENT_QUOTES)."</td>
		<td width='300'>".htmlspecialchars(xl('Name'), ENT_QUOTES)."</td>
	    <td width='200'>".htmlspecialchars(xl('Address'), ENT_QUOTES)."</td>
		<td width='200'>".htmlspecialchars( xl('CMS/Payer ID'), ENT_QUOTES)."</td><!-- Sai custom code -->
	  </tr>".
        //ProcessKeyForColoring(event,$CountIndex)==>Shows the navigation in the listing by change of colors and focus.Happens when down or up arrow is pressed.
        //PlaceValues(event,'&nbsp;','')==>Used while -->KEY PRESS<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        //PutTheValuesClick('&nbsp;','')==>Used while -->CLICK<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        "<tr class='text' height='20'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
	  onkeydown=\"ProcessKeyForColoring(event,$CountIndex);PlaceValues(event,'&nbsp;','')\"   onclick=\"PutTheValuesClick('&nbsp;','')\">
			<td colspan='4' align='center'><a id='anchor_insurance_code_$CountIndex' href='#'></a></td><!-- Sai custom code -->
	  </tr>";
        $insurance_text_ajax=formData('insurance_text_ajax', '', true);
	$res = sqlStatement("SELECT insurance_companies.id,name,city,state,country,cms_id FROM insurance_companies
			left join addresses on insurance_companies.id=addresses.foreign_id  where insurance_companies.active=1 AND name like '$insurance_text_ajax%' or  insurance_companies.id like '$insurance_text_ajax%' or cms_id like '$insurance_text_ajax%'  ORDER BY name");// Sai custom code -->
        while ($row = sqlFetchArray($res)) {
            if ($CountIndex%2==1) {
                $bgcolor='#ddddff';
            } else {
                $bgcolor='#ffdddd';
            }

                $CountIndex++;
                $Id=$row['id'];
                $Name=$row['name'];
                $City=$row['city'];
                $State=$row['state'];
                $Country=$row['country'];
		$CMS_Id=$row['cms_id'];// Sai custom code
                $Address=$City.', '.$State.', '.$Country;
                $StringForAjax.="<tr class='text'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
		onkeydown='ProcessKeyForColoring(event,$CountIndex);PlaceValues(event,\"".htmlspecialchars($Id, ENT_QUOTES)."\",\"".htmlspecialchars($Name, ENT_QUOTES)."\")'
			   onclick='PutTheValuesClick(\"".htmlspecialchars($Id, ENT_QUOTES)."\",\"".htmlspecialchars($Name, ENT_QUOTES)."\")'>
			<td><a id='anchor_insurance_code_$CountIndex' href='#'>".htmlspecialchars($Id)."</a></td>
			<td><a href='#'>".htmlspecialchars($Name)."</a></td>
		    <td><a href='#'>".htmlspecialchars($Address)."</a></td>
			<td><a href='#'>".htmlspecialchars($CMS_Id)."</a></td><!-- Sai custom code -->
</tr>";
        }

        $StringForAjax.="</table></div>";
        echo strlen($_REQUEST['insurance_text_ajax']).'~`~`'.$StringForAjax;
        die;
    }

//===============================================================================
    if ($_REQUEST["ajax_mode"] == "set_patient") {//patient.
    //From 2 areas this ajax is called.So 2 pairs of functions are used.
        //PlaceValues==>Used while -->KEY PRESS<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        //PutTheValuesClick==>Used while -->CLICK<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        //PlaceValuesDistribute==>Used while -->KEY PRESS<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        //PutTheValuesClickDistribute==>Used while -->CLICK<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        if (isset($_REQUEST['patient_code']) && $_REQUEST['patient_code']!='') {
            $patient_code=formData('patient_code', '', true);
		$ins_code=formData('ins_code','',true);	 // Sai custom code -->	
            if (isset($_REQUEST['submit_or_simple_type']) && $_REQUEST['submit_or_simple_type']=='Simple') {
                $StringToAppend="PutTheValuesClickPatient";
                $StringToAppend2="PlaceValuesPatient";
            } else {
                $StringToAppend="PutTheValuesClickDistribute";
                $StringToAppend2="PlaceValuesDistribute";
            }

            $patient_code_complete=$_REQUEST['patient_code'];//we need the spaces here
        } elseif (isset($_REQUEST['insurance_text_ajax']) && $_REQUEST['insurance_text_ajax']!='') {
            $patient_code=formData('insurance_text_ajax', '', true);
            $StringToAppend="PutTheValuesClick";
            $StringToAppend2="PlaceValues";
            $patient_code_complete=$_REQUEST['insurance_text_ajax'];//we need the spaces here
        }

        $CountIndex=1;
        $StringForAjax="<div id='AjaxContainerPatient'><table width='452' border='1' cellspacing='0' cellpadding='0'>
	  <tr class='text' bgcolor='#dddddd'>
		<td width='50'>".htmlspecialchars(xl('Code'), ENT_QUOTES)."</td>
		<td width='100'>".htmlspecialchars(xl('Last Name'), ENT_QUOTES)."</td>
	    <td width='100'>".htmlspecialchars(xl('First Name'), ENT_QUOTES)."</td>
	    <td width='100'>".htmlspecialchars(xl('Middle Name'), ENT_QUOTES)."</td>
	    <td width='100'>".htmlspecialchars(xl('Date of Birth'), ENT_QUOTES)."</td>
		<td width='100'>".htmlspecialchars( xl('S.S.'), ENT_QUOTES)."</td><!-- Sai custom code -->
	  </tr>".
        //ProcessKeyForColoring(event,$CountIndex)==>Shows the navigation in the listing by change of colors and focus.Happens when down or up arrow is pressed.
        "<tr class='text' height='20'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
	  onkeydown=\"ProcessKeyForColoring(event,$CountIndex);$StringToAppend2(event,'&nbsp;','')\"   onclick=\"$StringToAppend('&nbsp;','')\">
			<td colspan='6' align='center'><a id='anchor_insurance_code_$CountIndex' href='#'></a></td><!-- Sai custom code -->
	  </tr>

	  ";
	   $patient_code_date = str_replace("/","-",$patient_code); // Sai custom code -->
	$res = sqlStatement("SELECT pid as id,fname,lname,mname,DOB,ss FROM patient_data
			 where  fname like '$patient_code%' or lname like '$patient_code%' or mname like '$patient_code%' or
			 CONCAT(lname,' ',fname,' ',mname) like '$patient_code%' or pid like '$patient_code%' or DOB like '%$patient_code_date%' or ss like '$patient_code%' ORDER BY lname");// Sai custom code -->
        while ($row = sqlFetchArray($res)) {
            if ($CountIndex%2==1) {
                $bgcolor='#ddddff';
            } else {
                $bgcolor='#ffdddd';
            }

                $CountIndex++;
                $Id=$row['id'];
                $fname=$row['fname'];
                $lname=$row['lname'];
                $mname=$row['mname'];
                $Name=$lname.' '.$fname.' '.$mname;
                $DOB=oeFormatShortDate($row['DOB']);
		$ss=$row['ss']; // Sai custom code -->
                $StringForAjax.="<tr class='text'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
		 onkeydown='ProcessKeyForColoring(event,$CountIndex);$StringToAppend2(event,\"".htmlspecialchars($Id, ENT_QUOTES)."\",\"".htmlspecialchars($Name, ENT_QUOTES)."\")' onclick=\"$StringToAppend('".addslashes($Id)."','".htmlspecialchars(addslashes($Name), ENT_QUOTES)."')\">
			<td><a id='anchor_insurance_code_$CountIndex' href='#' >".htmlspecialchars($Id)."</a></td>
			<td><a href='#'>".htmlspecialchars($lname)."</a></td>
		    <td><a href='#'>".htmlspecialchars($fname)."</a></td>
            <td><a href='#'>".htmlspecialchars($mname)."</a></td>
            <td><a href='#'>".htmlspecialchars($DOB)."</a></td>
			<td><a href='#'>".htmlspecialchars($ss)."</a></td><!-- Sai custom code -->
  </tr>";
        }

        $StringForAjax.="</table></div>";
        echo strlen($patient_code_complete).'~`~`'.$StringForAjax;
        die;
    }

//===============================================================================
    if ($_REQUEST["ajax_mode"] == "encounter") {//encounter
    //PlaceValuesEncounter==>Used while -->KEY PRESS<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        //PutTheValuesClickEncounter==>Used while -->CLICK<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
        if (isset($_REQUEST['encounter_patient_code'])) {
            $patient_code=formData('encounter_patient_code', '', true);
            $StringToAppend="PutTheValuesClickEncounter";
            $StringToAppend2="PlaceValuesEncounter";
        }

        $CountIndex=1;
        $StringForAjax="<div id='AjaxContainerEncounter'><table width='202' border='1' cellspacing='0' cellpadding='0'>
	  <tr class='text' bgcolor='#dddddd'>
		<td width='100'>".htmlspecialchars(xl('Encounter'), ENT_QUOTES)."</td>
		<td width='100'>".htmlspecialchars(xl('Date'), ENT_QUOTES)."</td>
	  </tr>".
        //ProcessKeyForColoring(event,$CountIndex)==>Shows the navigation in the listing by change of colors and focus.Happens when down or up arrow is pressed.
        "<tr class='text' height='20'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
	  onkeydown=\"ProcessKeyForColoring(event,$CountIndex);$StringToAppend2(event,'&nbsp;','')\"   onclick=\"$StringToAppend('&nbsp;','')\">
			<td colspan='2' align='center'><a id='anchor_insurance_code_$CountIndex' href='#'></a></td>
	  </tr>

	  ";
        $res = sqlStatement("SELECT date,encounter FROM form_encounter
			 where pid ='$patient_code' ORDER BY encounter");
        while ($row = sqlFetchArray($res)) {
            if ($CountIndex%2==1) {
                $bgcolor='#ddddff';
            } else {
                $bgcolor='#ffdddd';
            }

                $CountIndex++;
                $Date=$row['date'];
                $Date=explode(' ', $Date);
                $Date=oeFormatShortDate($Date[0]);
                $Encounter=$row['encounter'];
                $StringForAjax.="<tr class='text'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
		 onkeydown=\"ProcessKeyForColoring(event,$CountIndex);$StringToAppend2(event,'".htmlspecialchars($Encounter, ENT_QUOTES)."','".htmlspecialchars($Date, ENT_QUOTES)."')\"   onclick=\"$StringToAppend('".htmlspecialchars($Encounter, ENT_QUOTES)."','".htmlspecialchars($Date, ENT_QUOTES)."')\">
			<td><a id='anchor_insurance_code_$CountIndex' href='#' >".htmlspecialchars($Encounter)."</a></td>
			<td><a href='#'>".htmlspecialchars($Date)."</a></td>
  </tr>";
        }

        $StringForAjax.="</table></div>";
        echo $StringForAjax;
        die;
   }
// Sai custom code start
   //===============================================================================
 if ($_REQUEST["ajax_mode"] == "set_facility")//facility.
   {
	//PlaceValues==>Used while -->KEY PRESS<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
	//PutTheValuesClick==>Used while -->CLICK<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.

	if(isset($_REQUEST['facility_code']) && $_REQUEST['facility_code']!='')
	 {
			$facility_code=formData('facility_code','',true);		
			$StringToAppend="PutTheValuesClickFacility";
			$StringToAppend2="PlaceValuesFacility";
		
		$facility_code_complete=$_REQUEST['facility_code'];//we need the spaces here
	 }	
	$CountIndex=1;

	  
	  $res = sqlStatement("SELECT id,name FROM facility where service_location != 0 and (id like '$facility_code%' or name like '$facility_code%') order by name");
	
	while ($row = sqlFetchArray($res))
	 {
		if($CountIndex%2==1)
		 {
			$bgcolor='#ddddff';
		 }
		else
		 {
			$bgcolor='#ffdddd';
		 }
		$CountIndex++;
		$Id=$row['id'];
		$Name=$row['name'];
		
		$StringForAjax.="<tr class='text'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
		 onkeydown='ProcessKeyForColoring(event,$CountIndex);$StringToAppend2(event,\"".htmlspecialchars($Id,ENT_QUOTES)."\",\"".htmlspecialchars($Name,ENT_QUOTES)."\")' onclick=\"$StringToAppend('".addslashes($Id)."','".htmlspecialchars(addslashes($Name),ENT_QUOTES)."')\">
			<td><a id='anchor_insurance_code_$CountIndex' href='#' >".htmlspecialchars($Id)."</a></td>
			<td><a href='#'>".htmlspecialchars($Name)."</a></td>		   
  </tr>";
	 }
	$StringForAjax.="</table></div>";
	echo strlen($facility_code_complete).'~`~`'.$StringForAjax;
	//echo $StringForAjax;
	die;
   } 
      //===============================================================================
 if ($_REQUEST["ajax_mode"] == "set_cpt")//cpt.
   {
	//PlaceValues==>Used while -->KEY PRESS<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
	//PutTheValuesClick==>Used while -->CLICK<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.

	if(isset($_REQUEST['cpt_code']) && $_REQUEST['cpt_code']!='')
	 {
			$cpt_code=formData('cpt_code','',true);		
			$StringToAppend="PutTheValuesClickCPT";
			$StringToAppend2="PlaceValuesCPT";
		
		$cpt_code_complete=$_REQUEST['cpt_code'];//we need the spaces here
	 }	
	$CountIndex=1;
	$StringForAjax="<div id='AjaxContainerCPT'><table width='200' border='1' cellspacing='0' cellpadding='0'>
	  <tr class='text' bgcolor='#dddddd'>
		<td width='100'>".htmlspecialchars( xl('Code'), ENT_QUOTES)."</td>	
		<td width='100'>".htmlspecialchars( xl('Type'), ENT_QUOTES)."</td>			
		<td width='100'>".htmlspecialchars( xl('Description'), ENT_QUOTES)."</td>	    
	  </tr>";
	//ProcessKeyForColoring(event,$CountIndex)==>Shows the navigation in the listing by change of colors and focus.Happens when down or up arrow is pressed.
	
	  	$StringForAjax.="<tr class='text' height='20'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
	  onkeydown=\"ProcessKeyForColoring(event,$CountIndex);PlaceValuesCPT(event,'&nbsp;','')\"   onclick=\"PutTheValuesClickCPT('&nbsp;','')\">
			<td colspan='3' align='center'><a id='anchor_insurance_code_$CountIndex' href='#'></a></td>
	  </tr>";
	  
	  $res = sqlStatement("SELECT code ,ct_key ,code_text_short  from codes cs, code_types ct where ct.ct_id=cs.code_type and (ct.ct_key='CPT4' or ct.ct_key='ICD9') and code like '$cpt_code%' order by cs.code");
	
	while ($row = sqlFetchArray($res))
	 {
		if($CountIndex%2==1)
		 {
			$bgcolor='#ddddff';
		 }
		else
		 {
			$bgcolor='#ffdddd';
		 }
		$CountIndex++;
		$Code=$row['code'];
		$Key=$row['ct_key'];
		$Description=$row['code_text_short'];
		
		$StringForAjax.="<tr class='text'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
		 onkeydown='ProcessKeyForColoring(event,$CountIndex);$StringToAppend2(event,\"".htmlspecialchars($Code,ENT_QUOTES)."\",\"".htmlspecialchars($Description,ENT_QUOTES)."\")' onclick=\"$StringToAppend('".addslashes($Code)."','".htmlspecialchars(addslashes($Key),ENT_QUOTES)."')\">
			<td><a id='anchor_insurance_code_$CountIndex' href='#' >".htmlspecialchars($Code)."</a></td>			
			<td><a href='#'>".htmlspecialchars($Key)."</a></td>	
			<td><a href='#'>".htmlspecialchars($Description)."</a></td>		   
  </tr>";
	 }
	$StringForAjax.="</table></div>";
	echo strlen($cpt_code_complete).'~`~`'.$StringForAjax;
	
	//echo $StringForAjax;
	die;
   } 
   
   if ($_REQUEST["ajax_mode"] == "set_icd9")//icd9.
   {
	if(isset($_REQUEST['icd9_code']) && $_REQUEST['icd9_code']!='')
	 {
			$icd9_code=formData('icd9_code','',true);		
			$StringToAppend="PutTheValuesClickICD9";
			$StringToAppend2="PlaceValuesICD9";
		
		$icd9_code_complete=$_REQUEST['icd9_code'];//we need the spaces here
	 }	
	$CountIndex=1;
	$StringForAjax="<div id='AjaxContainerICD9'><table width='200' border='1' cellspacing='0' cellpadding='0'>
	  <tr class='text' bgcolor='#dddddd'>
		<td width='100'>".htmlspecialchars( xl('ICD9 Code'), ENT_QUOTES)."</td>		
		<td width='100'>".htmlspecialchars( xl('Description'), ENT_QUOTES)."</td>	    
	  </tr>";
	//ProcessKeyForColoring(event,$CountIndex)==>Shows the navigation in the listing by change of colors and focus.Happens when down or up arrow is pressed.
	
	$StringForAjax.="<tr class='text' height='20'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
	  onkeydown=\"ProcessKeyForColoring(event,$CountIndex);PlaceValuesICD9(event,'&nbsp;','')\"   onclick=\"PutTheValuesClickICD9('&nbsp;','')\">
			<td colspan='3' align='center'><a id='anchor_insurance_code_$CountIndex' href='#'></a></td>
	  </tr>";
	  
	  $res = sqlStatement("SELECT code ,ct_key ,code_text_short from codes cs, code_types ct where ct.ct_id=cs.code_type and ct.ct_key='ICD9' and code like '$icd9_code%' order by cs.code");
	
	while ($row = sqlFetchArray($res))
	 {
		if($CountIndex%2==1)
		 {
			$bgcolor='#ddddff';
		 }
		else
		 {
			$bgcolor='#ffdddd';
		 }
		$CountIndex++;
		$Code=$row['code'];
		$Key=$row['ct_key'];
		$Description=$row['code_text_short'];
		
		$StringForAjax.="<tr class='text'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
		 onkeydown='ProcessKeyForColoring(event,$CountIndex);$StringToAppend2(event,\"".htmlspecialchars($Code,ENT_QUOTES)."\",\"".htmlspecialchars($Description,ENT_QUOTES)."\")' onclick=\"$StringToAppend('".addslashes($Code)."','".htmlspecialchars(addslashes($Key),ENT_QUOTES)."')\">
			<td><a id='anchor_insurance_code_$CountIndex' href='#' >".htmlspecialchars($Code)."</a></td>			
			<td><a href='#'>".htmlspecialchars($Description)."</a></td>		   
  </tr>";
	 }
	$StringForAjax.="</table></div>";
	echo strlen($icd9_code_complete).'~`~`'.$StringForAjax;
	die;
	
	
	
   } 
   if ($_REQUEST["ajax_mode"] == "set_enc_search")//enc_serach.
   {
   	$encounter = $_REQUEST['enc_search'];
	//	$encounter=formData('enc_number','',true);	
	$result = sqlStatement("select concat(lname,' ',fname,' ',mname) as pname ,pid from patient_data where pid=(select pid from form_encounter where encounter='$encounter')");
	while ($row = sqlFetchArray($result))
	 {
		$pname = $row['pname'];
		$pid=$row['pid'];
	}
	//echo $pname.'~`~`'.$pid;
	echo $pname.'~`~`'.$pid;
	die;
   }
 }
 // Sai custom code end
