<?php
/*
 * This program saves data from the misc_billing_form
 *
 * @package OpenEMR
 * @author Terry Hill <terry@lilysystems.com>
 * @author Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2007 Bo Huynh
 * @copyright Copyright (C) 2016 Terry Hill <terry@lillysystems.com>
 * @link http://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General P
 */
require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

if (! $encounter) { // comes from globals.php
    die(xlt("Internal error: we do not seem to be in an encounter!"));
}

if ($_POST["off_work_from"] == "0000-00-00" || $_POST["off_work_from"] == "") {
    $_POST["is_unable_to_work"] = "0";
    $_POST["off_work_to"] = "";
} else {
    $_POST["is_unable_to_work"] = "1";
}

if ($_POST["hospitalization_date_from"] == "0000-00-00" || $_POST["hospitalization_date_from"] == "") {
    $_POST["is_hospitalized"] = "0";
    $_POST["hospitalization_date_to"] = "";
} else {
    $_POST["is_hospitalized"] = "1";
}

        $id = formData('id', 'G') + 0;
// Sai custom code start
/* Modified By : Sonali
8846: Needs to update Date format to MM-DD-YYYY on Misc Billing Options for HCFA-1500 page 	  	  
*/
if(formData("date_initial_treatment"))
	$date_initial_treatment = date('Y-m-d',strtotime(formData("date_initial_treatment")));
else
	$date_initial_treatment ="";

if(formData("off_work_from"))
	$off_work_from = date('Y-m-d',strtotime(formData("off_work_from")));
else
	$off_work_from ="";

if(formData("off_work_to"))
	$off_work_to = date('Y-m-d',strtotime(formData("off_work_to")));
else
	$off_work_to = "";

if(formData("hospitalization_date_from"))
	$hospitalization_date_from = date('Y-m-d',strtotime(formData("hospitalization_date_from")));
else
	$hospitalization_date_from = "";

if(formData("hospitalization_date_to"))
	$hospitalization_date_to = date('Y-m-d',strtotime(formData("hospitalization_date_to")));
else
	$hospitalization_date_to = "";

//Added By Gangeya : BUG ID 10717 : Misc billing option for BOX 14, 19
if(formData("date_current_illiness_injury"))
	$date_current_injury = date('Y-m-d',strtotime(formData("date_current_illiness_injury")));
else
	$date_current_injury = "";

if(formData("other_date"))
	$other_date = date('Y-m-d',strtotime(formData("other_date")));
else
	$other_date = "";
// Sai custom code end

        $sets = "pid = {$_SESSION["pid"]},
  groupname = '" . $_SESSION["authProvider"] . "',
  user = '" . $_SESSION["authUser"] . "',
  authorized = $userauthorized, activity=1, date = NOW(),
  employment_related          = '" . formData("employment_related") . "',
  auto_accident               = '" . formData("auto_accident") . "',
  accident_state              = '" . formData("accident_state") . "',
  other_accident              = '" . formData("other_accident") . "',
  outside_lab                 = '" . formData("outside_lab") . "',
  medicaid_referral_code      = '" . formData("medicaid_referral_code") . "',
  epsdt_flag                  = '" . formData("epsdt_flag") . "',
  provider_id                 = '" . formData("provider_id")  . "',
  provider_qualifier_code     = '" . formData("provider_qualifier_code") . "',
  lab_amount                  = '" . formData("lab_amount") . "',
  is_unable_to_work           = '" . formData("is_unable_to_work") . "',
  onset_date                  = '" . formData("onset_date") . "',
  date_initial_treatment      = '" . formData("date_initial_treatment") . "',
  off_work_from               = '" . formData("off_work_from") . "',
  off_work_to                 = '" . formData("off_work_to") . "',
  is_hospitalized             = '" . formData("is_hospitalized") . "',
  hospitalization_date_from   = '" . formData("hospitalization_date_from") . "',
  hospitalization_date_to     = '" . formData("hospitalization_date_to") . "',
  medicaid_resubmission_code  = '" . formData("medicaid_resubmission_code") . "',
  medicaid_original_reference = '" . formData("medicaid_original_reference") . "',
  prior_auth_number           = '" . formData("prior_auth_number") . "',
  replacement_claim           = '" . formData("replacement_claim") . "',
  icn_resubmission_number     = '" . formData("icn_resubmission_number") . "',
  date_current_injury		  = '" . $date_current_injury . "', 
  box_14_date_qual            = '" . formData("box_14_date_qual") . "',
  other_date				  = '" . $other_date . "', 
  box_15_date_qual            = '" . formData("box_15_date_qual") . "',
   qualifier_current_injury	  = '" . formData("quali_current_ill_date") . "',
   qualifier_other_date 		  = '" . formData("quali_current_ill_othr_date") . "', 
  comments                    = '" . formData("comments") . "'";

if (empty($id)) {
// Sai custom code start
	$lastRes = sqlStatement("INSERT INTO form_misc_billing_options SET $sets");	
	  
	  // code added for getting last inserted id of encounter by pawan 25-01-2017
	 // $result = sqlQuery("SELECT LAST_INSERT_ID() "); 
	 // $row =  mysql_fetch_row($result);

   
	 // $newid = $result[0];
	
  $newid = sqlInsert("INSERT INTO form_misc_billing_options SET $sets");
 //  print_r($newid);
  //  exit;
// Sai custom code end
    addForm($encounter, "Misc Billing Options", $newid, "misc_billing_options", $pid, $userauthorized);
} else {
    sqlStatement("UPDATE form_misc_billing_options SET $sets WHERE id = $id");
}

        formHeader("Redirecting....");
        formJump();
        formFooter();
