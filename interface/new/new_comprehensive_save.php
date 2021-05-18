<?php
// Copyright (C) 2009, 2017 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");

// Validation for non-unique external patient identifier.
$alertmsg = '';
if (!empty($_POST["form_pubpid"])) {
    $form_pubpid = trim($_POST["form_pubpid"]);
    $result = sqlQuery("SELECT count(*) AS count FROM patient_data WHERE " .
    "pubpid = ?", array($form_pubpid));
    if ($result['count']) {
        // Error, not unique.
        $alertmsg = xl('Warning: Patient ID is not unique!');
// Sai custom code start	
	$fname = trim($_POST["form_fname"]);
	$lname = trim($_POST["form_lname"]);
	$gender = trim($_POST["form_sex"]);
	$dob = trim($_POST["form_DOB"]);
	$ssn = trim($_POST["form_ss"]);


$result = sqlQuery("SELECT pid FROM patient_data WHERE " .
    "pubpid = '$form_pubpid' limit 1");
	
$pid=$result['pid'];
    }
}
else 
{

	
	//below condition added by sangram for Adding validation for bug id 8361

$fname = trim($_POST["form_fname"]);
	$lname = trim($_POST["form_lname"]);
	$gender = trim($_POST["form_sex"]);
$dobpart = explode('/', trim($_POST["form_DOB"]));
$dob = $dobpart[2].'-'.$dobpart[0].'-'.$dobpart[1];

$ssn=trim($_POST["form_ss"]);
	$ss = str_replace ("-","",$ssn);



if((strlen( trim($_POST["form_DOB"])))!=0 || (strlen(trim($_POST["form_ss"])))!=0)
{

		  $result = sqlQuery("SELECT count(*) AS count FROM patient_data WHERE " .
    "fname = '$fname' and lname='$lname' and sex='$gender' and (ss='$ssn' or ss='$ss') and dob='$dob'");
	
	}
	else
	{
	$result['count']=0;
	}
	
	
	  if ($result['count']) {
    // Error, not unique.
    $alertmsg = xl('This patient alredy exists');
		
		$result = sqlQuery("SELECT pid FROM patient_data WHERE " .
    "fname = '$fname' and lname='$lname' and sex='$gender' and (ss='$ssn' or ss='$ss')");
	
$pid=$result['pid'];
		
	}
	else 
	{
	$ssn=trim($_POST["form_ss"]);
	$ss = str_replace ("-","",$ssn);
	if((strlen(trim($_POST["form_ss"])))!=0)
	{
	 $result = sqlQuery("SELECT count(*) AS count FROM patient_data WHERE ss='$ssn' or ss='$ss'");
	 }
	 else
	 {
	 	$result['count']=0;
	 }
  if ($result['count']) {
    // Error, not unique.
    $alertmsg = xl('Duplicate SSN, Moving to the patient having entered SSN');
	
			$result = sqlQuery("SELECT pid FROM patient_data WHERE ss='$ssn' or ss='$ss'");
	
$pid=$result['pid'];

}
else
{
require_once("$srcdir/pid.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

// here, we lock the patient data table while we find the most recent max PID
// other interfaces can still read the data during this lock, however
// sqlStatement("lock tables patient_data read");

//BUG ID 10346 BY Gangeya
$result = sqlQuery("SELECT MAX(pid)+1 AS pid, MAX(CONVERT(SUBSTRING_INDEX(pubpid,'-',-1),UNSIGNED INTEGER))+1 as pubpid FROM patient_data");

$newpid = 1;
$newpubpid = 1; //BUG ID 10346 BY Gangeya

if ($result['pid'] > 1) {
    $newpid = $result['pid'];
}
//BUG ID 10346 BY Gangeya
if ($result['pubpid'] > 1)
  $newpubpid = $result['pubpid'];
setpid($newpid);
setpubpid($newpubpid); //BUG ID 10346 BY Gangeya

// Sai custom code end	
if (empty($pid)) {
  // sqlStatement("unlock tables");
    die("Internal error: setpid(" .text($newpid) . ") failed!");
}

// Update patient_data and employer_data:
//
$newdata = array();
$newdata['patient_data' ] = array();
$newdata['employer_data'] = array();
$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'DEM' AND (uor > 0 OR field_id = 'pubpid') AND field_id != '' " .
  "ORDER BY group_id, seq");
while ($frow = sqlFetchArray($fres)) {
    $data_type = $frow['data_type'];
    $field_id  = $frow['field_id'];
  // $value     = '';
    $colname   = $field_id;
    $tblname   = 'patient_data';
    if (strpos($field_id, 'em_') === 0) {
        $colname = substr($field_id, 3);
        $tblname = 'employer_data';
    }

  //get value only if field exist in $_POST (prevent deleting of field with disabled attribute)
    if (isset($_POST["form_$field_id"]) || $field_id == "pubpid") {
        $value = get_layout_form_value($frow);
	// Sai custom code start	
	//BUG ID 10346 BY Gangeya
	if ($field_id == 'pubpid' && empty($value)) 
		$value = $pubpid;
        }

        $newdata[$tblname][$colname] = $value;
    }
}

updatePatientData($pid, $newdata['patient_data'], true);
updateEmployerData($pid, $newdata['employer_data'], true);

//$i1dob = DateToYYYYMMDD(filter_input(INPUT_POST, "i1subscriber_DOB"));
//$i1date = DateToYYYYMMDD(filter_input(INPUT_POST, "i1effective_date"));
$i1dob = fixDate(formData("i1subscriber_DOB"));
$i1date = fixDate(formData("i1effective_date"));
// sqlStatement("unlock tables");
// end table lock

newHistoryData($pid);
/*if(formData("i1provider")!=''){
$ins_str1 = split("[[]",formData("i1provider"));
$ins_name1 = rtrim(ltrim($ins_str1[0]));
$ins_addr1 = str_replace("]","",$ins_str1[1]);
$ins_add_arr1 = split(",",$ins_addr1);
// updated by Gangeya : BUG 8616 for additional address line in insurance DD.
$ins_line1 = rtrim(ltrim($ins_add_arr1[0]));
$ins_city1 = rtrim(ltrim($ins_add_arr1[1]));
$ins_state1 = rtrim(ltrim($ins_add_arr1[2]));
$ins_country1 = rtrim(ltrim($ins_add_arr1[3]));

$sql_qry1 ='';
// updated by Gangeya : BUG 8616 for additional address line in insurance DD.
if($ins_line1 !='')
$sql_qry1 .= "and line1 like '%$ins_line1%' ";
if($ins_city1 !='')
$sql_qry1 .= "and city like '%$ins_city1%' ";
if($ins_state1 !='')
$sql_qry1 .= "and state like '%$ins_state1%' ";
if($ins_country1 !='')
$sql_qry1 .= "and country like '%$ins_country1%' ";
			
$frow1 = sqlQuery("SELECT insurance_companies.id as id FROM insurance_companies
			left join addresses on insurance_companies.id=addresses.foreign_id  WHERE name like '%$ins_name1%' $sql_qry1 ");
 $ins_id1 = $frow1['id'];
 }
 else
 $ins_id1='';
*/


        newInsuranceData(
            $pid,
            "primary",
           //  $ins_id1, 
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
            filter_input(INPUT_POST, 'i1accept_assignment')
        );


//$i2dob = DateToYYYYMMDD(filter_input(INPUT_POST, "i2subscriber_DOB"));
//$i2date = DateToYYYYMMDD(filter_input(INPUT_POST, "i2effective_date"));
$i2dob = fixDate(formData("i2subscriber_DOB"));
$i2date = fixDate(formData("i2effective_date"));

/*if(formData("i2provider")!=''){
$ins_str2 = split("[[]",formData("i2provider"));
$ins_name2 = rtrim(ltrim($ins_str2[0]));
$ins_addr2 = str_replace("]","",$ins_str2[1]);
$ins_add_arr2 = split(",",$ins_addr2);
// updated by Gangeya : BUG 8616 for additional address line in insurance DD.
$ins_line2 = rtrim(ltrim($ins_add_arr2[0]));
$ins_city2 = rtrim(ltrim($ins_add_arr2[1]));
$ins_state2 = rtrim(ltrim($ins_add_arr2[2]));
$ins_country2 = rtrim(ltrim($ins_add_arr2[3]));

$sql_qry2 ='';
// updated by Gangeya : BUG 8616 for additional address line in insurance DD.
if($ins_line2 !='')
$sql_qry2 .= "and line1 like '%$ins_line2%' ";
if($ins_city2 !='')
$sql_qry2 .= "and city like '%$ins_city2%' ";
if($ins_state2 !='')
$sql_qry2 .= "and state like '%$ins_state2%' ";
if($ins_country2 !='')
$sql_qry2 .= "and country like '%$ins_country2%' ";


			
$frow2 = sqlQuery("SELECT insurance_companies.id as id FROM insurance_companies
			left join addresses on insurance_companies.id=addresses.foreign_id  WHERE name like '%$ins_name2%' $sql_qry2 ");
$ins_id2 = $frow2['id'];
 }
 else
 $ins_id2='';
*/

    newInsuranceData(
        $pid,
        "secondary",
      //   $ins_id2,
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
        filter_input(INPUT_POST, 'i2accept_assignment')
    );

//$i3dob  = DateToYYYYMMDD(filter_input(INPUT_POST, "i3subscriber_DOB"));
//$i3date = DateToYYYYMMDD(filter_input(INPUT_POST, "i3effective_date"));
$i3dob  = fixDate(formData("i3subscriber_DOB"));
$i3date = fixDate(formData("i3effective_date"));

    newInsuranceData(
        $pid,
        "tertiary",
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
        filter_input(INPUT_POST, 'i3accept_assignment')
    );

}
}
 
  // Sai custom code end	
?>
<html>
<body>
<script language="Javascript">
<?php
if ($alertmsg) {
    echo "alert('" . addslashes($alertmsg) . "');\n";
}

  echo "window.location='$rootdir/patient_file/summary/demographics.php?" .
    "set_pid=" . attr($pid) . "&is_new=1';\n";
?>
</script>

</body>
</html>

