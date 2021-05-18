<?php
include_once("../../globals.php");
include_once("$srcdir/patient.inc");
include_once("$srcdir/acl.inc");
include_once("$srcdir/options.inc.php");

// Check authorization.
if ($pid) {
    if (!acl_check('patients', 'demo', '', 'write')) {
        die(xlt('Updating demographics is not authorized.'));
    }

    $tmp = getPatientData($pid, "squad");
    if ($tmp['squad'] && ! acl_check('squads', $tmp['squad'])) {
        die(xlt('You are not authorized to access this squad.'));
    }
} else {
    if (!acl_check('patients', 'demo', '', array('write','addonly'))) {
        die(xlt('Adding demographics is not authorized.'));
    }
}

foreach ($_POST as $key => $val) {
    if ($val == "MM/DD/YYYY") {
        $_POST[$key] = "";
    }
}

// Update patient_data and employer_data:
//
$newdata = array();
$newdata['patient_data']['id'] = $_POST['db_id'];
$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'DEM' AND uor > 0 AND field_id != '' " .
  "ORDER BY group_id, seq");
while ($frow = sqlFetchArray($fres)) {
    $data_type = $frow['data_type'];
    $field_id = $frow['field_id'];
    // $value  = '';
    $colname = $field_id;
    $table = 'patient_data';
    if (strpos($field_id, 'em_') === 0) {
        $colname = substr($field_id, 3);
        $table = 'employer_data';
    }
    //get value only if field exist in $_POST (prevent deleting of field with disabled attribute)
    if (isset($_POST["form_$field_id"])) {
    	$newdata[$table][$colname] = get_layout_form_value($frow);
    }
	 // code added for IP/DP and statement checkbox fields
    if ($colname =='IP_DP' || $colname =='stop_stmt' || $colname =='self_pay') {
       $newdata[$table][$colname] = get_layout_form_value($frow);
    }
}
// Sai custom code start
	//below condition added by sangram for Adding validation for bug id 8361

$rs1 = validateSSN($pid, $newdata['patient_data']);
$rs= validatePatient($pid, $newdata['patient_data']);
$rs2 = validateID($pid, $newdata['patient_data']);

if($rs >=1)
{

echo '<script language="Javascript">';
  echo "alert('Unable to save changes, Patient alredy exist with same name, DOB, Gender and SSN');\n";
  echo '</script>';
}
elseif($rs1 >=1)
{
echo '<script language="Javascript">';
  echo "alert('Unable to save changes, Duplicate SSN');\n";
  echo '</script>';
}
elseif($rs2 >=1)
{
echo '<script language="Javascript">';
  echo "alert('Unable to save changes, Duplicate External ID');\n";
  echo '</script>';
}
else
{

    updatePatientData($pid, $newdata['patient_data']);
    updateEmployerData($pid, $newdata['employer_data']);

    $i1dob = fixDate(formData("i1subscriber_DOB"));
    $i1date = fixDate(formData("i1effective_date"), date('m/d/Y'));
    //$i1termination_date = fixDate(formData("i1termination_date"), date('m/d/Y'));
    //8297: V5 UAT bugs
    $blank_ins1=1;
    $blank_ins2=1;
    $blank_ins3=1;
    // Code commented by pawan avoid address search in insurance company name
   /* if(formData("i1provider")!=''){
        $ins_str1 = split("[[]",formData("i1provider"));
        $ins_name1 = rtrim(ltrim($ins_str1[0]))."%";
        $ins_addr1 = str_replace("]","",$ins_str1[1]);
        $ins_add_arr1 = split(",",$ins_addr1);
        // updated by Gangeya : BUG 8616 for additional address line in insurance DD.
        $ins_line1 = rtrim(ltrim($ins_add_arr1[0]));
        $ins_city1 = rtrim(ltrim($ins_add_arr1[1]));
        $ins_state1 = rtrim(ltrim($ins_add_arr1[2]));
        $ins_country1 = rtrim(ltrim($ins_add_arr1[3]));

        $sql_qry1 ='';
        if($ins_line1=='' && $ins_city1=='' && $ins_state1=='' && $ins_country1=='')
        {
                $sql_qry1 ='';
                $ins_name1 = rtrim(ltrim($ins_str1[0]));
        }
        else
        {
                // updated by Gangeya : BUG 8616 for additional address line in insurance DD.
                if($ins_line1 !='')
                $sql_qry1 .= "and line1 like '%$ins_line1%' ";
                else
                $sql_qry1 .= "and line1 like '' ";

                if($ins_city1 !='')
                $sql_qry1 .= "and city like '%$ins_city1%' ";
                else
                $sql_qry1 .= "and city like '' ";
                if($ins_state1 !='')
                $sql_qry1 .= "and state like '%$ins_state1%' ";
                else
                $sql_qry1 .= "and state like '' ";
                if($ins_country1 !='')
                $sql_qry1 .= "and country like '%$ins_country1%' ";
                else
                $sql_qry1 .= "and country like '' ";
        }			
        $frow1 = sqlQuery("SELECT insurance_companies.id as id FROM insurance_companies
        left join addresses on insurance_companies.id=addresses.foreign_id  WHERE name like '$ins_name1' $sql_qry1");
         $ins_id1 = $frow1['id'];
         //8297: V5 UAT bugs
         if($ins_id1=='')
            $blank_ins1=0;
    }
    else
        $ins_id1='';
     */  
       
         // code added by pawan for validation 
    /*if(formData("i1provider")==''){
        $blank_ins1 = 0;
    } */
if(!($_POST['i1provider'] == '' || empty($_POST['i1provider']))) {
        newInsuranceData(
            $pid,
            "primary",
           //$ins_id1, 
            filter_input(INPUT_POST, "i1provider"),
            filter_input(INPUT_POST, "i1policy_number"),
            filter_input(INPUT_POST, "i1group_number"),
            filter_input(INPUT_POST, "i1plan_name"),
            filter_input(INPUT_POST, "i1subscriber_lname"),
            filter_input(INPUT_POST, "i1subscriber_mname"),
            filter_input(INPUT_POST, "i1subscriber_fname"),
            filter_input(INPUT_POST, "form_i1subscriber_relationship"),
            filter_input(INPUT_POST, "i1subscriber_ss"),
            $i1dob,
            filter_input(INPUT_POST, "i1subscriber_street"),
            filter_input(INPUT_POST, "i1subscriber_postal_code"),
            filter_input(INPUT_POST, "i1subscriber_city"),
            filter_input(INPUT_POST, "form_i1subscriber_state"),
            filter_input(INPUT_POST, "form_i1subscriber_country"),
            filter_input(INPUT_POST, "i1subscriber_phone"),
            filter_input(INPUT_POST, "i1subscriber_employer"),
            filter_input(INPUT_POST, "i1subscriber_employer_street"),
            filter_input(INPUT_POST, "i1subscriber_employer_city"),
            filter_input(INPUT_POST, "i1subscriber_employer_postal_code"),
            filter_input(INPUT_POST, "form_i1subscriber_employer_state"),
            filter_input(INPUT_POST, "form_i1subscriber_employer_country"),
            filter_input(INPUT_POST, 'i1copay'),
            filter_input(INPUT_POST, 'form_i1subscriber_sex'),
            $i1date,
            filter_input(INPUT_POST, 'i1accept_assignment'),
            filter_input(INPUT_POST, 'i1policy_type')
        );
    }
    //  $i1termination_date,
    $i2dob = fixDate(formData("i2subscriber_DOB"));
    $i2date = fixDate(formData("i2effective_date"), date('m/d/Y'));
    //$i2termination_date = fixDate(formData("i2termination_date"), date('m/d/Y'));
        // Code commented by pawan avoid address search in insurance company name
    /*if(formData("i2provider")!=''){
        $ins_str2 = split("[[]",formData("i2provider"));
        $ins_name2 = rtrim(ltrim($ins_str2[0]))."%";
        $ins_addr2 = str_replace("]","",$ins_str2[1]);
        $ins_add_arr2 = split(",",$ins_addr2);
        // updated by Gangeya : BUG 8616 for additional address line in insurance DD.
        $ins_line2 = rtrim(ltrim($ins_add_arr2[0]));
        $ins_city2 = rtrim(ltrim($ins_add_arr2[1]));
        $ins_state2 = rtrim(ltrim($ins_add_arr2[2]));
        $ins_country2 = rtrim(ltrim($ins_add_arr2[3]));

        $sql_qry2 ='';
        if($ins_line2=='' && $ins_city2=='' && $ins_state2=='' && $ins_country2=='')
        {
            $sql_qry2 ='';
            $ins_name2 = rtrim(ltrim($ins_str2[0]));
        }
        else
        {
            // updated by Gangeya : BUG 8616 for additional address line in insurance DD.
            if($ins_line2 !='')
            $sql_qry1 .= "and line1 like '%$ins_line2%' ";
            else
            $sql_qry1 .= "and line1 like '' ";

            if($ins_city2 !='')
            $sql_qry2 .= "and city like '%$ins_city2%' ";
            else
            $sql_qry2 .= "and city like '' ";
            if($ins_state2 !='')
            $sql_qry2 .= "and state like '%$ins_state2%' ";
            else
            $sql_qry2 .= "and state like '' ";
            if($ins_country2 !='')
            $sql_qry2 .= "and country like '%$ins_country2%' ";
            else
            $sql_qry2 .= "and country like '' ";
        }			
        $frow2 = sqlQuery("SELECT insurance_companies.id as id FROM insurance_companies
        left join addresses on insurance_companies.id=addresses.foreign_id  WHERE name like '$ins_name2'  $sql_qry2");
        $ins_id2 = $frow2['id'];
         //8297: V5 UAT bugs
         if($ins_id2=='')
         $blank_ins2=0;
    }
    else
     $ins_id2=''; 
    */
          // code added by pawan for validation 
        //echo "<pre>";
          //  echo "Secondary ===";
          //  print_r($_POST);
    
   /* if(formData("i2provider")==''){
        $blank_ins2 = 0;

    } */ 
     //8297: V5 UAT bugs
	 
	 $inc2data = sqlQuery("select count(*) as count from insurance_data where pid='$pid' and type = 'secondary'");
        $inc2count = $inc2data['count'];
	 
    if(($_POST['i2provider'] == '' || empty($_POST['i2provider']))&& $inc2count == 0) {
      //  echo " i am her i22";
      // exit;
/*
     echo '<script language="javascript" type="text/javascript">';
      echo "alert('Please enter correct Secondary Insurance Provider');"; 
      echo "window.location.href='demographics_full.php'";
      echo '</script>'; 
*/
    }
    else{
        newInsuranceData(
            $pid,
            "secondary",
          //  $ins_id2,
            filter_input(INPUT_POST, "i2provider"),
            filter_input(INPUT_POST, "i2policy_number"),
            filter_input(INPUT_POST, "i2group_number"),
            filter_input(INPUT_POST, "i2plan_name"),
            filter_input(INPUT_POST, "i2subscriber_lname"),
            filter_input(INPUT_POST, "i2subscriber_mname"),
            filter_input(INPUT_POST, "i2subscriber_fname"),
            filter_input(INPUT_POST, "form_i2subscriber_relationship"),
            filter_input(INPUT_POST, "i2subscriber_ss"),
            $i2dob,
            filter_input(INPUT_POST, "i2subscriber_street"),
            filter_input(INPUT_POST, "i2subscriber_postal_code"),
            filter_input(INPUT_POST, "i2subscriber_city"),
            filter_input(INPUT_POST, "form_i2subscriber_state"),
            filter_input(INPUT_POST, "form_i2subscriber_country"),
            filter_input(INPUT_POST, "i2subscriber_phone"),
            filter_input(INPUT_POST, "i2subscriber_employer"),
            filter_input(INPUT_POST, "i2subscriber_employer_street"),
            filter_input(INPUT_POST, "i2subscriber_employer_city"),
            filter_input(INPUT_POST, "i2subscriber_employer_postal_code"),
            filter_input(INPUT_POST, "form_i2subscriber_employer_state"),
            filter_input(INPUT_POST, "form_i2subscriber_employer_country"),
            filter_input(INPUT_POST, 'i2copay'),
            filter_input(INPUT_POST, 'form_i2subscriber_sex'),
            $i2date,
            filter_input(INPUT_POST, 'i2accept_assignment'),
            filter_input(INPUT_POST, 'i2policy_type')
        );
    }

    $i3dob  = fixDate(formData("i3subscriber_DOB"));
    $i3date = fixDate(formData("i3effective_date"), date('m/d/Y'));
    
    //$i3termination_date = fixDate(formData("i3termination_date"), date('m/d/Y'));
      // Code commented by pawan avoid address search in insurance company name
    /*if(formData("i3provider")!=''){
        $ins_str3 = split("[[]",formData("i3provider"));
        $ins_name3 = ltrim(rtrim($ins_str3[0]))."%";
        $ins_addr3= str_replace("]","",$ins_str3[1]);
        $ins_add_arr3 = split(",",$ins_addr3);
        // updated by Gangeya : BUG 8616 for additional address line in insurance DD.
        $ins_line3 = ltrim(rtrim($ins_add_arr3[0]));

        $ins_city3 = ltrim(rtrim($ins_add_arr3[1]));
        $ins_state3 = ltrim(rtrim($ins_add_arr3[2]));
        $ins_country3 = ltrim(rtrim($ins_add_arr3[3]));

        $sql_qry3 ='';
        if($ins_line3=='' && $ins_city3=='' && $ins_state3=='' && $ins_country3=='')
        {
            $sql_qry3 ='';
            $ins_name3 = rtrim(ltrim($ins_str3[0]));
        }
        else
        {
            // updated by Gangeya : BUG 8616 for additional address line in insurance DD.
            if($ins_line3 !='')
            $sql_qry1 .= "and line1 like '%$ins_line3%' ";
            else
            $sql_qry1 .= "and line1 like '' ";

            if($ins_city3 !='')
            $sql_qry3 .= "and city like '%$ins_city3%' ";
            else
            $sql_qry3 .= "and city like '' ";
            if($ins_state3 !='')
            $sql_qry3 .= "and state like '%$ins_state3%' ";
            else
            $sql_qry3 .= "and state like '' ";
            if($ins_country3 !='')
            $sql_qry3 .= "and country like '%$ins_country3%' ";
            else
            $sql_qry3 .= "and country like '' ";
        }			
        $frow3 = sqlQuery("SELECT insurance_companies.id as id FROM insurance_companies
        left join addresses on insurance_companies.id=addresses.foreign_id  WHERE name like '$ins_name3'");
         $ins_id3 = $frow3['id'];
         //8297: V5 UAT bugs
         if($ins_id3=='')
         $blank_ins3=0;
     }
     else
     $ins_id3='';*/
	
	$inc3data = sqlQuery("select count(*) as count from insurance_data where pid='$pid' and type = 'tertiary'");
        $inc3count = $inc3data['count'];
	 
    if(($_POST['i3provider'] == '' || empty($_POST['i3provider']))&& $inc3count == 0) {
    // if($_POST['i3provider'] == '' || empty($_POST['i3provider'])) {
       
 /*   
     echo '<script language="javascript" type="text/javascript">';
      echo "alert('Please enter correct Tertiary Insurance Provider');"; 
      echo "window.location.href='demographics_full.php'";
      echo '</script>'; 
*/
    }
    else{
        newInsuranceData(
            $pid,
            "tertiary",
          //  $ins_id3,
            filter_input(INPUT_POST, "i3provider"),
            filter_input(INPUT_POST, "i3policy_number"),
            filter_input(INPUT_POST, "i3group_number"),
            filter_input(INPUT_POST, "i3plan_name"),
            filter_input(INPUT_POST, "i3subscriber_lname"),
            filter_input(INPUT_POST, "i3subscriber_mname"),
            filter_input(INPUT_POST, "i3subscriber_fname"),
            filter_input(INPUT_POST, "form_i3subscriber_relationship"),
            filter_input(INPUT_POST, "i3subscriber_ss"),
            $i3dob,
            filter_input(INPUT_POST, "i3subscriber_street"),
            filter_input(INPUT_POST, "i3subscriber_postal_code"),
            filter_input(INPUT_POST, "i3subscriber_city"),
            filter_input(INPUT_POST, "form_i3subscriber_state"),
            filter_input(INPUT_POST, "form_i3subscriber_country"),
            filter_input(INPUT_POST, "i3subscriber_phone"),
            filter_input(INPUT_POST, "i3subscriber_employer"),
            filter_input(INPUT_POST, "i3subscriber_employer_street"),
            filter_input(INPUT_POST, "i3subscriber_employer_city"),
            filter_input(INPUT_POST, "i3subscriber_employer_postal_code"),
            filter_input(INPUT_POST, "form_i3subscriber_employer_state"),
            filter_input(INPUT_POST, "form_i3subscriber_employer_country"),
            filter_input(INPUT_POST, 'i3copay'),
            filter_input(INPUT_POST, 'form_i3subscriber_sex'),
            $i3date,
            filter_input(INPUT_POST, 'i3accept_assignment'),
            filter_input(INPUT_POST, 'i3policy_type')
        );
    }

} // end of else
// Sai custom code end
 include_once("demographics.php");
